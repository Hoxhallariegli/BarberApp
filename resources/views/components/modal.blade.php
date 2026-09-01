@props([
    'modalTitle' => '',
    'content' => '',
    'footer' => '',
    'height' => 'sm:w-full md:w-1/2'
])

<div
    x-data="{
        on: false,
        open() { this.on = true },
        close() { this.on = false }
    }"
    x-on:close-modal.window="close()"
>
    <div @click="open()" class="cursor-pointer">
        {{ $trigger }}
    </div>

    <template x-teleport="body">
        <div class="fixed z-[9999] inset-0 flex items-center justify-center p-4 bg-gray-900/60"
             x-show="on"
             x-cloak>

            <div class="bg-white dark:bg-gray-800 dark:text-gray-200 rounded-lg shadow-xl w-full max-w-xl relative"
                 role="dialog"
                 aria-modal="true"
                 x-show="on">

                <div class="flex flex-col max-h-[90vh]">
                    <header class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-lg font-bold">{{ $modalTitle ?? '' }}</h2>
                    </header>

                    <main class="p-6 overflow-y-auto">
                        {{ $content ?? '' }}
                    </main>

                    @if($footer)
                        <footer class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-b-lg border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                            {{ $footer }}
                        </footer>
                    @endif

                    <button type="button" @click="on = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
