<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">{{ __('barbers.Configure Hours') }} {{ __('admin.for') }}: {{ $barber->name }}</h2>
        <a href="{{ route('admin.barbers.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
            {{ __('barbers.Go Back') }}
        </a>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="save">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('barbers.Day') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('barbers.Working?') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('barbers.Start') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('barbers.End') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('barbers.Break Start') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('barbers.Break End') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($schedules as $dayNum => $data)
                        <tr class="{{ !$schedules[$dayNum]['is_working'] ? 'bg-gray-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $data['day_name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="checkbox" wire:model.live="schedules.{{ $dayNum }}.is_working" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="time" wire:model="schedules.{{ $dayNum }}.start_time" class="p-2 border rounded {{ !$schedules[$dayNum]['is_working'] ? 'opacity-50' : '' }}" {{ !$schedules[$dayNum]['is_working'] ? 'disabled' : '' }}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="time" wire:model="schedules.{{ $dayNum }}.end_time" class="p-2 border rounded {{ !$schedules[$dayNum]['is_working'] ? 'opacity-50' : '' }}" {{ !$schedules[$dayNum]['is_working'] ? 'disabled' : '' }}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="time" wire:model="schedules.{{ $dayNum }}.break_start_time" class="p-2 border rounded {{ !$schedules[$dayNum]['is_working'] ? 'opacity-50' : '' }}" {{ !$schedules[$dayNum]['is_working'] ? 'disabled' : '' }}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="time" wire:model="schedules.{{ $dayNum }}.break_end_time" class="p-2 border rounded {{ !$schedules[$dayNum]['is_working'] ? 'opacity-50' : '' }}" {{ !$schedules[$dayNum]['is_working'] ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    {{ __('barbers.Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
