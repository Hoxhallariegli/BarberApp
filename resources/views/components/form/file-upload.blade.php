@props([
    'label' => '',
    'id' => 'file-upload',
    'isEditing' => false,
    'preview' => null, // Mund të jetë URL-ja e fotos ekzistuese ose TemporaryFile
])

<div class="space-y-3" x-cloak>
    @if($label)
        <label for="{{ $id }}" class="block mb-1.5 text-[10px] font-black uppercase tracking-[0.2em] ml-1 text-gray-400">
            {{ $label }}
        </label>
    @endif

    <div
        x-data="{ isUploading: false, progress: 0 }"
        x-on:livewire-upload-start="isUploading = true"
        x-on:livewire-upload-finish="isUploading = false"
        x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
        class="flex flex-col lg:flex-row items-center lg:items-stretch gap-4 sm:gap-6"
    >
        {{-- Preview Section (Left) --}}
        <div class="shrink-0 w-full sm:w-auto flex justify-center">
            @php
                $modelName = $attributes->wire('model')->value();
                // Përdorim data_get që të funksionojë edhe me array (p.sh. uploads.1)
                $tempFile = data_get($this, $modelName);
                $hasPreview = false;
                $previewUrl = '';

                // 1. Kontrollojmë nëse kemi një TemporaryUploadedFile (sapo është zgjedhur fotoja)
                if ($tempFile && !is_string($tempFile) && !is_array($tempFile) && method_exists($tempFile, 'temporaryUrl')) {
                    try {
                        $previewUrl = $tempFile->temporaryUrl();
                        $hasPreview = true;
                    } catch (\Exception $e) {}
                }
                // 2. Kontrollojmë nëse kemi një URL ekzistuese (në edit mode)
                elseif ($preview || (is_string($tempFile) && !empty($tempFile))) {
                    $finalPreview = $preview ?: (is_string($tempFile) ? $tempFile : null);

                    if ($finalPreview) {
                        $previewUrl = (str_starts_with($finalPreview, 'http') || str_starts_with($finalPreview, 'data:'))
                            ? $finalPreview
                            : asset($finalPreview);
                        $hasPreview = true;
                    }
                }
            @endphp

            <div class="relative group w-fit">
                <div class="size-28 sm:size-32 lg:size-40 rounded-[2rem] lg:rounded-[2.5rem] bg-gray-50 dark:bg-gray-900 border-2 border-dashed border-gray-200 dark:border-gray-700 overflow-hidden flex items-center justify-center transition-all group-hover:border-blue-500/30">
                    @if($hasPreview)
                        <img src="{{ $previewUrl }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <x-heroicon-o-eye class="size-6 text-white" />
                        </div>
                    @else
                        <div class="flex flex-col items-center text-gray-300 dark:text-gray-700 p-4">
                            <x-heroicon-o-photo class="size-8 lg:size-10" />
                            <span class="text-[7px] lg:text-[8px] font-black uppercase mt-2 text-center">No Preview</span>
                        </div>
                    @endif
                </div>

                {{-- Uploading Overlay --}}
                <div x-show="isUploading" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-[2rem] lg:rounded-[2.5rem] flex flex-col items-center justify-center p-4">
                    <div class="relative size-10 lg:size-12 flex items-center justify-center">
                        <svg class="size-full -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-gray-200 dark:text-gray-700" stroke-width="2"></circle>
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-blue-600" stroke-width="2" stroke-dasharray="100" :stroke-dashoffset="100 - progress" stroke-linecap="round"></circle>
                        </svg>
                        <span class="absolute text-[8px] lg:text-[10px] font-black text-blue-600" x-text="progress + '%'"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upload Section (Right) --}}
        <div class="flex-1 w-full min-w-0">
            <label class="flex flex-col items-center justify-center w-full h-28 sm:h-32 lg:h-40 border-2 border-dashed {{ $errors->has($attributes->get('wire:model')) ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }} dark:border-gray-700 rounded-[2rem] lg:rounded-[2.5rem] cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all group relative">
                <div class="flex flex-col items-center justify-center text-center px-4 sm:px-6">
                    <div class="p-2 sm:p-3 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-500 mb-2 lg:mb-3 group-hover:scale-110 transition-transform">
                        <x-heroicon-o-cloud-arrow-up class="size-5 lg:size-6" />
                    </div>
                    <p class="text-[10px] lg:text-xs text-gray-700 dark:text-gray-300 font-black uppercase tracking-tight line-clamp-2">
                        {{ $isEditing ? __('admin.Click to replace current file') : __('admin.Click or Drag to Upload') }}
                    </p>
                    <p class="text-[7px] lg:text-[9px] text-gray-400 font-bold uppercase mt-1 lg:mt-2 italic">Max 15MB • JPG, PNG, WEBP</p>
                </div>
                <input type="file" {{ $attributes }} class="hidden" id="{{ $id }}" />
            </label>
            @error($attributes->get('wire:model')) <p class="text-[9px] text-red-500 font-bold uppercase mt-2 ml-4">{{ $message }}</p> @enderror
        </div>
    </div>

    {{ $slot }}
</div>
