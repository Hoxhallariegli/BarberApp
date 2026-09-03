<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmim Rezervimi | THE STATION</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Space Grotesk', sans-serif; }
        .bg-brass { background-color: #C6A15B; }
        .text-brass { color: #C6A15B; }
    </style>
</head>
<body class="bg-[#121212] text-white h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full">
        {{-- Logo Section --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center overflow-hidden border-2 border-brass/50">
                     <img src="{{ asset('images/STATION.jpg') }}" class="w-full h-full object-cover">
                </div>
                <div class="text-left">
                    <h1 class="text-xl font-bold tracking-tighter leading-none">THE STATION</h1>
                    <p class="text-brass text-[10px] font-black tracking-[0.2em] uppercase">Barbershop</p>
                </div>
            </div>
        </div>

        {{-- Content Card --}}
        <div class="bg-[#1E1E1E] p-8 rounded-[2.5rem] shadow-2xl border border-white/5 text-center relative overflow-hidden">
            {{-- Decorative Element --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-brass/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

            @if(session('success') || $booking->status === 'confirmed')
                <div class="py-6 space-y-4">
                    <div class="w-20 h-20 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold italic uppercase tracking-tight">{{ __('Confirmed!') }}</h2>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        {{ __('Thank you') }} {{ $booking->customer_name }}.<br>
                        {{ __('Your appointment for') }} <span class="text-white font-bold">{{ $booking->appointment_datetime->format('H:i') }}</span> {{ __('is ready.') }}
                    </p>
                    <div class="pt-6">
                        <a href="/" class="text-brass text-xs font-black uppercase tracking-[0.2em] hover:text-white transition">{{ __('Back to home') }}</a>
                    </div>
                </div>

            @elseif(session('info') || $booking->status === 'cancelled')
                <div class="py-6 space-y-4">
                    <div class="w-20 h-20 bg-white/5 text-white/40 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold italic uppercase tracking-tight text-white/60">{{ __('Cancelled') }}</h2>
                    <p class="text-gray-500 text-sm leading-relaxed italic">
                        {{ __('Booking deleted successfully.') }}<br>{{ __('See you next time!') }}
                    </p>
                    <div class="pt-6">
                        <a href="/" class="text-brass text-xs font-black uppercase tracking-[0.2em] hover:text-white transition">{{ __('Back to home') }}</a>
                    </div>
                </div>

            @else
                {{-- Default State: Confirmation --}}
                <div class="space-y-6">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-brass/10 border border-brass/20 text-brass text-[10px] font-black uppercase tracking-widest mb-2">
                        {{ __('Appointment Verification') }}
                    </div>

                    <h2 class="text-3xl font-bold leading-tight italic uppercase tracking-tight">
                        {{ __('Hello') }}<br>
                        <span class="text-brass not-italic">{{ explode(' ', $booking->customer_name)[0] }}</span>
                    </h2>

                    <p class="text-gray-400 text-sm leading-relaxed">
                        {{ __('You have a booking today at') }} <span class="text-white font-bold">{{ $booking->appointment_datetime->format('H:i') }}</span>.<br>
                        {{ __('Will you attend the appointment?') }}
                    </p>

                    <div class="flex flex-col gap-3 pt-4" x-data="{ submitting: false }">
                        <form action="{{ url('/confirm/'.$booking->token.'/yes') }}" method="POST" @submit="submitting = true">
                            @csrf
                            <button type="submit"
                                    :disabled="submitting"
                                    class="w-full bg-brass hover:bg-brass/90 text-black font-black uppercase tracking-widest py-4 rounded-2xl transition shadow-lg shadow-brass/20 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!submitting">{{ __('Yes, I will come') }} ✅</span>
                                <span x-show="submitting">{{ __('Processing...') }}</span>
                            </button>
                        </form>

                        <form action="{{ url('/confirm/'.$booking->token.'/no') }}" method="POST" @submit="submitting = true">
                            @csrf
                            <button type="submit"
                                    :disabled="submitting"
                                    class="w-full bg-white/5 hover:bg-white/10 text-white font-black uppercase tracking-widest py-4 rounded-2xl transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!submitting">{{ __('No, I cannot') }} ❌</span>
                                <span x-show="submitting">{{ __('Processing...') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="text-center mt-10">
             <p class="text-[10px] text-gray-600 font-bold uppercase tracking-[0.3em]">The Station Barbers • Tirana</p>
        </div>
    </div>
</body>
</html>
