@props([
    'data' => [],
    'placeholder' => __('Search...'),
    'label' => '',
    'required' => false,
    'disabled' => false,
])

@php
    $modelName = $attributes->wire('model')->value();

    $list = [];
    foreach($data as $id => $name) {
        $list[] = ['v' => (string)$id, 'l' => (string)$name];
    }
@endphp

<div {{ $attributes->merge(['class' => 'relative w-full space-y-1 mb-6']) }}
     data-list="{{ json_encode($list) }}"
     x-data="{
         search: '',
         isOpen: false,
         selectedId: @entangle($attributes->wire('model')),

         // VËMENDJE: $el pa 'this.' — 'this' s'është i lidhur ende këtu.
         options: JSON.parse($el.getAttribute('data-list') || '[]'),

         get selectedLabel() {
             if (!this.selectedId) return '';
             let item = this.options.find(o => o.v == this.selectedId);
             return item ? item.l : '';
         },

         get filtered() {
             if (!this.search) return this.options;
             return this.options.filter(o =>
                 o && o.l && o.l.toLowerCase().includes(this.search.toLowerCase())
             );
         },

         init() {
             this.search = this.selectedLabel;
             this.$watch('selectedId', value => {
                 this.search = this.selectedLabel;
             });

             const observer = new MutationObserver(() => {
                 this.options = JSON.parse(this.$el.getAttribute('data-list') || '[]');
             });
             observer.observe(this.$el, { attributes: true, attributeFilter: ['data-list'] });
         },

         select(id) {
             this.selectedId = id;
             this.isOpen = false;
             this.search = this.selectedLabel;
         }
     }"
     @click.away="isOpen = false; search = selectedLabel">

    @if($label != 'none' && $label != '')
        <x-form.label :$label :$required :name="$modelName" />
    @endif

    <div class="relative" wire:ignore>
        <input type="text"
               x-model="search"
               @focus="if (!{{ $disabled ? 'true' : 'false' }}) { isOpen = true; search = ''; }"
               class="w-full p-3 text-sm font-bold bg-gray-50 dark:bg-gray-900 border rounded-2xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm dark:text-white @error($modelName) border-red-500 @else border-gray-200 dark:border-gray-700 @enderror"
               :class="{'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-800/50': {{ $disabled ? 'true' : 'false' }}}"
               placeholder="{{ $placeholder }}"
               {{ $disabled ? 'readonly' : '' }}>

        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
            <svg class="w-4 h-4 transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>

        <div class="absolute bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 w-full mt-2 rounded-2xl shadow-2xl max-h-60 overflow-y-auto z-[100] custom-scrollbar"
             x-show="isOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <ul class="p-1">
                <template x-for="opt in filtered" :key="opt.v">
                    <li @click="select(opt.v)"
                        class="p-3 text-xs font-bold rounded-xl cursor-pointer transition-colors flex items-center justify-between"
                        :class="selectedId == opt.v ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/30'">
                        <span x-text="opt.l"></span>
                        <template x-if="selectedId == opt.v">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
