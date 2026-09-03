<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('SMS Templates') }}</x-h1>
            <x-short-description>{{ __('Manage message templates for reminders, promotions, and automated responses.') }}</x-short-description>
        </div>
        <div>
            <x-btn wire:click="create" variant="blue" icon="plus">
                {{ __('Add New Template') }}
            </x-btn>
        </div>
    </div>

    <x-card padding="p-0 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="size-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                    <x-heroicon-o-document-duplicate class="size-4" />
                </div>
                <h3 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-xs">{{ __('SMS Templates') }}</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">{{ __('Template Type') }}</th>
                        <th class="px-6 py-4">{{ __('Message Body (SQ)') }}</th>
                        <th class="px-6 py-4">{{ __('Message Body (EN)') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('Status') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[9px] font-bold uppercase tracking-tighter">
                                    {{ $template->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-gray-600 dark:text-gray-400 text-xs truncate">{{ $template->body['sq'] ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-gray-600 dark:text-gray-400 text-xs truncate">{{ $template->body['en'] ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <x-badge :variant="$template->is_active ? 'success' : 'danger'">
                                    {{ $template->is_active ? __('Active') : __('Inactive') }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-btn wire:click="edit({{ $template->id }})" variant="blue" size="xs" icon="pencil-square" />
                                    <x-btn wire:click="delete({{ $template->id }})"
                                           wire:confirm="{{ __('Delete this template?') }}"
                                           variant="danger" size="xs" icon="trash" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-xs font-bold uppercase tracking-widest italic">
                                {{ __('No templates found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{-- Modal for Create/Edit --}}
    @if($showingModal)
        <div class="fixed z-[9999] inset-0 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl w-full max-w-xl relative overflow-hidden animate-in fade-in zoom-in duration-200"
                 role="dialog"
                 aria-modal="true">

                <header class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-black uppercase italic text-gray-900 dark:text-white">
                        {{ $templateId ? __('Edit Template') : __('Create New Template') }}
                    </h2>
                    <button wire:click="$set('showingModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </header>

                <main class="p-8 space-y-6" x-data="{ activeTab: 'sq' }">
                    <x-form.group label="Template Type" for="type">
                        <x-form.input wire:model="type" id="type" placeholder="e.g. booking_confirmation, reminder" />
                        <small class="text-gray-400 text-[9px] font-black uppercase tracking-widest italic">{{ __('Use "booking_confirmation" for new bookings and "reminder" for reminders.') }}</small>
                    </x-form.group>

                    <div class="space-y-4">
                        <div class="flex gap-2 border-b border-gray-100 dark:border-gray-700">
                            <button @click="activeTab = 'sq'" :class="activeTab === 'sq' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400'" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest border-b-2 transition-colors">Albanian (SQ)</button>
                            <button @click="activeTab = 'en'" :class="activeTab === 'en' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400'" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest border-b-2 transition-colors">English (EN)</button>
                        </div>

                        <div x-show="activeTab === 'sq'" class="animate-in fade-in slide-in-from-left-2 duration-200">
                            <x-form.group label="Message Body (SQ)" for="body_sq">
                                <x-form.textarea wire:model="body.sq" id="body_sq" rows="4" placeholder="Teksti në shqip..." />
                            </x-form.group>
                        </div>

                        <div x-show="activeTab === 'en'" class="animate-in fade-in slide-in-from-right-2 duration-200">
                            <x-form.group label="Message Body (EN)" for="body_en">
                                <x-form.textarea wire:model="body.en" id="body_en" rows="4" placeholder="Text in English..." />
                            </x-form.group>
                        </div>

                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach(['{name}', '{time}', '{link_confirm}'] as $tag)
                                <button type="button" @click="if(activeTab === 'sq') $wire.set('body.sq', $wire.get('body.sq') + '{{ $tag }}'); else $wire.set('body.en', $wire.get('body.en') + '{{ $tag }}')" class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-[8px] font-black uppercase text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                    {{ $tag }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <x-form.group label="Active Status">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="is_active" class="text-xs text-gray-600 dark:text-gray-400">{{ __('Active') }}</label>
                        </div>
                    </x-form.group>
                </main>

                <footer class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                    <x-btn wire:click="$set('showingModal', false)" variant="secondary">
                        {{ __('Cancel') }}
                    </x-btn>
                    <x-btn wire:click="save" variant="blue">
                        {{ __('Save Template') }}
                    </x-btn>
                </footer>
            </div>
        </div>
    @endif
</div>
