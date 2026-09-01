<div class="space-y-6">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>🍎 AI System Architect</x-h1>
            <x-short-description>Describe your entire system. I will plan and generate the scaffolding commands for multiple modules at once.</x-short-description>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card !p-8 border-blue-100 dark:border-blue-900/30 bg-blue-50/10">
                <form wire:submit.prevent="process" class="space-y-4">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-blue-600 mb-2 ml-1">Describe your System / Modules</label>
                    <textarea
                        wire:model="prompt"
                        class="w-full h-40 p-6 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-[2rem] shadow-sm focus:ring-4 focus:ring-blue-500/10 transition-all outline-none dark:text-white"
                        placeholder="e.g. Create a Brand module and a Car module. Link Cars to Brands using brand_id. Enable API for both."
                    ></textarea>

                    <div class="flex justify-end">
                        <x-button type="submit" variant="blue" class="!px-10 !py-4 !rounded-2xl" wire:loading.attr="disabled">
                            <span wire:loading.remove>Generate Commands</span>
                            <span wire:loading>Planning System...</span>
                        </x-button>
                    </div>
                </form>
            </div>

            @if(count($history) > 0)
                <div class="space-y-4">
                    @foreach(array_reverse($history) as $item)
                        <div class="card {{ $item['type'] === 'success' ? 'border-green-100 dark:border-green-900/20' : 'border-red-100 dark:border-red-900/20' }}">
                            <div class="flex items-start gap-4">
                                <div class="size-10 rounded-full flex items-center justify-center {{ $item['type'] === 'success' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    @if($item['type'] === 'success') <x-heroicon-o-check class="size-6" /> @else <x-heroicon-o-x-mark class="size-6" /> @endif
                                </div>
                                <div class="flex-1 space-y-6">
                                    <p class="font-bold text-gray-900 dark:text-white">{{ $item['message'] }}</p>

                                    @if(isset($item['results']))
                                        <div class="space-y-4">
                                            @foreach($item['results'] as $res)
                                                <div class="space-y-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-black rounded-lg uppercase tracking-tighter">{{ $res['model'] }}</span>
                                                        <div class="h-px flex-1 bg-gray-100 dark:bg-gray-800"></div>
                                                    </div>
                                                    <div class="relative group">
                                                        <code class="block p-4 bg-gray-900 text-green-400 rounded-xl text-xs break-all font-mono border border-gray-800">
                                                            {{ $res['command'] }}
                                                        </code>
                                                        <button class="absolute top-3 right-3 text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"
                                                                onclick="navigator.clipboard.writeText(`{{ $res['command'] }}`); window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Command copied!', type: 'success' } }))">
                                                            <x-heroicon-o-clipboard class="size-5" />
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card">
                <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4 ml-1">Multi-Module Mode</h3>
                <p class="text-xs text-gray-500 leading-relaxed italic">
                    "You can now request multiple tables at once. I will understand relationships and order the commands correctly."
                </p>
            </div>

            <div class="card bg-zinc-900 text-white border-none">
                <h3 class="text-xs font-black uppercase tracking-widest text-zinc-400 mb-4 ml-1">Example Command</h3>
                <p class="text-xs text-zinc-300 leading-relaxed mb-4">
                    "Build a CarRental system with Brands, Models and Customers. Models should link to Brands. Enable API for all."
                </p>
                <div class="p-4 bg-white/5 rounded-2xl border border-white/10 text-[10px] text-zinc-400 font-mono">
                    I will generate 3 separate commands for you to paste.
                </div>
            </div>
        </div>
    </div>
</div>
