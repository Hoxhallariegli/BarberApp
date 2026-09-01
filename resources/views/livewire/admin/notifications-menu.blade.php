<div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @can('view_notifications')
        <div x-data="{ isOpen: false }"
             x-on:keydown.escape.window="isOpen = false"
             class="relative mr-3">

            <!-- Trigger Button -->
            <button @click="isOpen = true"
                    wire:click="open"
                    class="focus:outline-none">
                <a href="#">

                    @if ($unseenCount > 0)
                        <span class="bg-red-500 absolute top-0 left-6 block h-4 w-4 rounded-full ring-2 ring-white text-xs text-white">{{ $unseenCount }}</span>
                    @endif

                    <svg
                        class="size-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5"
                        />
                    </svg>
                </a>
            </button>

            <!-- Slide-over Panel -->
            <div x-show="isOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-hidden"
                 aria-labelledby="slide-over-title"
                 role="dialog"
                 aria-modal="true">

                <!-- Overlay me click-to-close -->
                <div class="absolute inset-0 bg-gray-500/75 transition-opacity"
                     @click="isOpen = false">
                </div>

                <!-- Slide-over Content -->
                <div class="fixed inset-y-0 right-0 max-w-full flex">
                    <div class="relative w-screen max-w-md"
                         x-show="isOpen"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="transform translate-x-full"
                         x-transition:enter-end="transform translate-x-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="transform translate-x-0"
                         x-transition:leave-end="transform translate-x-full"
                         @click.away="false">

                        <div class="h-full flex flex-col bg-white dark:bg-gray-800 dark:text-gray-300 shadow-xl overflow-y-auto">

                            <!-- Header -->
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-300">
                                        {{ __('Notifications') }}
                                    </h2>
                                    <button @click="isOpen = false"
                                            class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <span class="sr-only">{{ __('Close panel') }}</span>
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="border-b border-gray-200 dark:border-gray-700"></div>

                            <!-- Notifications List -->
                            <div class="flex-1 overflow-y-auto">
                                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @if (count($notifications) === 0)
                                        <li class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            {{ __('No notifications yet.') }}
                                        </li>
                                    @else
                                        @foreach($notifications as $notification)
                                            <li wire:key="{{ $notification->id }}" class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                <div class="flex justify-between items-start">
                                                    @if (!empty($notification->link))
                                                        <a href="{{ $notification->link }}" class="flex-1 flex items-start min-w-0">
                                                            @endif

                                                            <span class="flex-shrink-0 inline-block relative mr-3">
                                                            @if (!empty($notification->assignedFrom->image))
                                                                    <img class="h-10 w-10 rounded-full"
                                                                         src="{{ storage_url($notification->assignedFrom->image) }}"
                                                                         alt="{{ $notification->assignedFrom->name }}">
                                                                @else
                                                                    <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                    </svg>
                                                                </div>
                                                                @endif
                                                        </span>

                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                                    {{ $notification->title }}
                                                                </p>
                                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                                    {{ $notification->body }}
                                                                </p>

                                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                                    {{ $notification->created_at->diffForHumans() }}
                                                                </p>
                                                            </div>

                                                            @if (!empty($notification->link))
                                                        </a>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>

                            <!-- Footer ose Clear All button (optional) -->
                            @if (count($notifications) > 0)
                                <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                                    <button wire:click="markAllAsRead"
                                            class="w-full text-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                        {{ __('Mark all as read') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
</div>

