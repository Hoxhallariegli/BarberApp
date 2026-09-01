<div class="space-y-6">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <h1>{{ __('languages.Languages Management') }}</h1>
            <p>{{ __('languages.Manage system languages and translations') }}</p>
        </div>

        <div x-data="{ showAdd: false }">
            <div x-show="showAdd" x-cloak class="flex items-center gap-2">
                <input type="text" wire:model="newLangCode" placeholder="sq, it, de..." class="w-20 p-2 text-sm border border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <x-button wire:click="addLanguage" size="sm" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="addLanguage">{{ __('languages.Add & Translate') }}</span>
                    <span wire:loading wire:target="addLanguage">{{ __('languages.Translating...') }}</span>
                </x-button>
                <button @click="showAdd = false" class="text-gray-400 hover:text-red-500">
                    <x-heroicon-o-x-mark class="size-5" />
                </button>
            </div>
            <x-button @click="showAdd = !showAdd" variant="secondary" x-show="!showAdd">
                <x-heroicon-o-plus class="size-4 mr-1" />
                {{ __('languages.New Language') }}
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Selection -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card !my-0">
                <label class="block text-xs font-bold uppercase tracking-wider mb-3 text-gray-500">{{ __('languages.Select Language') }}</label>
                <div class="space-y-2">
                    @foreach($languages as $lang)
                        <div class="flex items-center gap-2">
                            <button wire:click="$set('selectedLang', '{{ $lang }}')"
                                    class="flex-1 text-left px-4 py-2 text-sm font-bold uppercase rounded transition-all {{ $selectedLang === $lang ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                {{ strtoupper($lang) }}
                            </button>
                            @if($lang !== 'en')
                                <button wire:click="sync('{{ $lang }}')"
                                        wire:loading.attr="disabled"
                                        title="{{ __('Sync with English') }}"
                                        class="p-2 text-gray-400 hover:text-primary transition-colors">
                                    <x-heroicon-o-arrow-path wire:loading.class="animate-spin" wire:target="sync('{{ $lang }}')" class="size-4" />
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card !my-0">
                <label class="block text-xs font-bold uppercase tracking-wider mb-3 text-gray-500">{{ __('languages.Select File') }}</label>
                <div class="space-y-1">
                    @foreach($files as $file)
                        <button wire:click="$set('selectedFile', '{{ $file }}')"
                                class="w-full text-left px-4 py-2 text-sm rounded transition-all {{ $selectedFile === $file ? 'bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 font-bold border-l-4 border-sky-600' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            {{ str_replace('.php', '', $file) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Translation Grid -->
        <div class="lg:col-span-3">
            @if($selectedFile)
                <div class="card !my-0">
                    <div class="flex items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                        <div>
                            <h2 class="!py-0 text-xl font-bold text-gray-900 dark:text-white uppercase">{{ str_replace('.php', '', $selectedFile) }}</h2>
                            <small class="text-gray-500 uppercase tracking-widest">{{ $selectedLang }} / {{ $selectedFile }}</small>
                        </div>
                        <x-button wire:click="saveTranslations" size="sm">
                            <x-heroicon-o-check class="size-4 mr-1" />
                            {{ __('languages.Save All') }}
                        </x-button>
                    </div>

                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                        @foreach($translations as $key => $value)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center border-b border-gray-50 dark:border-gray-700/50 pb-4 last:border-0">
                                <div class="md:col-span-1">
                                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $key }}</label>
                                </div>
                                <div class="md:col-span-2">
                                    <input type="text" wire:model="translations.{{ $key }}"
                                           class="w-full p-2 text-sm border border-gray-200 rounded dark:bg-gray-900 dark:border-gray-700 dark:text-white focus:ring-1 focus:ring-primary outline-none">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-button wire:click="saveTranslations">
                            {{ __('languages.Save Changes') }}
                        </x-button>
                    </div>
                </div>
            @else
                <div class="card !my-0 flex flex-col items-center justify-center text-center h-96 border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-900/30 shadow-none">
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-full shadow-sm mb-4">
                        <x-heroicon-o-language class="size-10 text-gray-400" />
                    </div>
                    <h3 class="!py-0 text-lg font-bold text-gray-700 dark:text-gray-300 uppercase">{{ __('languages.Select a file to begin') }}</h3>
                    <p class="text-sm text-gray-500 max-w-xs mt-2">{{ __('languages.Choose a language and a translation file from the sidebar to edit its content.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
