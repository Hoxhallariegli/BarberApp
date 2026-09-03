<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Customer;
use App\Models\BerberApp\Payment;
use App\Models\BerberApp\Reminder;
use App\Models\BerberApp\Service;
use App\Models\SmsTemplate;
use App\Models\SmsLog;
use App\Models\SmsDevice;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\AvailabilityService;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Domain\BerberApp\Booking\Actions\CreateBookingAction;

class MobileDashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        return response()->json([
            'stats' => [
                'bookings_today' => Booking::whereDate('appointment_datetime', $today)->count(),
                'total_customers' => Customer::count(),
                'total_revenue' => (float) Payment::sum('amount'),
                'pending_reminders' => Reminder::where('status', 'pending')->count(),
            ],
            'recent_bookings' => Booking::with(['customer', 'barber', 'service'])
                ->latest()
                ->take(5)
                ->get()
        ]);
    }

    public function barbers()
    {
        $barbers = Barber::with('schedules')->get()->map(function($barber) {
            $barber->photo_url = $barber->photo ? url('uploads/' . $barber->photo) : null;
            return $barber;
        });
        return response()->json($barbers);
    }

    public function storeBarber(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric',
        ]);

        $barber = Barber::create($validated);
        return response()->json(['success' => true, 'barber' => $barber]);
    }

    public function updateBarber(Request $request, $id)
    {
        $barber = Barber::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string',
            'specialization' => 'nullable|string',
            'phone' => 'nullable|string',
            'commission_rate' => 'nullable|numeric',
        ]);

        $barber->update($validated);

        if ($request->has('schedules')) {
            foreach ($request->schedules as $schedData) {
                $barber->schedules()->updateOrCreate(
                    ['day_of_week' => $schedData['day_of_week']],
                    collect($schedData)->except(['id', 'created_at', 'updated_at', 'barber_id'])->toArray()
                );
            }
        }

        return response()->json(['success' => true, 'barber' => $barber->load('schedules')]);
    }

    public function bookings()
    {
        $today = Carbon::today();

        return response()->json(
            Booking::with(['customer', 'barber', 'service'])
                ->orderByRaw("CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'completed' AND DATE(appointment_datetime) = '{$today->toDateString()}' THEN 2
                    ELSE 3
                END")
                ->orderBy('appointment_datetime', 'asc')
                ->paginate(50)
        );
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:ba_barbers,id',
            'service_id' => 'required|exists:ba_services,id',
            'date' => 'required|date',
        ]);

        $barber = Barber::findOrFail($request->barber_id);
        $service = Service::findOrFail($request->service_id);

        $availabilityService = app(AvailabilityService::class);
        $slots = $availabilityService->getAvailableSlots(
            $barber,
            Carbon::parse($request->date),
            $service->duration_minutes ?: 30
        );

        return response()->json($slots);
    }

    public function storeBooking(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:ba_customers,id',
            'barber_id' => 'required|exists:ba_barbers,id',
            'service_id' => 'required|exists:ba_services,id',
            'appointment_datetime' => 'required|date',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        $dto = BookingDTO::fromArray([
            'customer_id' => $customer->id,
            'barber_id' => $request->barber_id,
            'service_id' => $request->service_id,
            'appointment_datetime' => Carbon::parse($request->appointment_datetime)->toDateTimeString(),
            'status' => 'pending',
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'locale' => 'sq',
            'reminder_enabled' => true,
        ]);

        $action = app(CreateBookingAction::class);
        $booking = $action->execute($dto);

        return response()->json(['success' => true, 'booking' => $booking->load(['customer', 'barber', 'service'])]);
    }

    public function updateBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $validated = $request->validate([
            'barber_id' => 'required|exists:ba_barbers,id',
            'service_id' => 'required|exists:ba_services,id',
            'appointment_datetime' => 'required|date',
            'status' => 'required|string',
        ]);

        $booking->update($validated);
        return response()->json(['success' => true, 'booking' => $booking->load(['customer', 'barber', 'service'])]);
    }

    public function customers()
    {
        return response()->json(Customer::latest()->paginate(50));
    }

    public function storeCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $customer = Customer::create($validated);
        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $customer->update($validated);
        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function services()
    {
        return response()->json(Service::all());
    }

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'names' => 'required|array',
            'price' => 'required|numeric',
            'duration_minutes' => 'required|integer',
        ]);

        $service = Service::create([
            'name' => $validated['names'], // Spatie Translatable handles array
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
        ]);

        return response()->json(['success' => true, 'service' => $service]);
    }

    public function updateService(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $validated = $request->validate([
            'names' => 'required|array',
            'price' => 'required|numeric',
            'duration_minutes' => 'required|integer',
        ]);

        $service->update([
            'name' => $validated['names'],
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
        ]);

        return response()->json(['success' => true, 'service' => $service]);
    }

    public function completePayment(Request $request, $bookingId)
    {
        $request->validate(['amount' => 'required|numeric']);

        try {
            $booking = Booking::findOrFail($bookingId);
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $request->amount,
                'status' => 'completed',
            ]);
            $booking->update(['status' => 'completed']);
            return response()->json(['success' => true, 'payment' => $payment]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reminders()
    {
        return response()->json(Reminder::with(['booking.customer'])->latest()->paginate(20));
    }

    public function smsTemplates()
    {
        return response()->json(SmsTemplate::all());
    }

    public function smsSettings()
    {
        return response()->json([
            'device' => SmsDevice::where('is_active', true)->first(),
            'logs_count' => SmsLog::count(),
            'failed_count' => SmsLog::where('status', 'failed')->count(),
        ]);
    }
}
