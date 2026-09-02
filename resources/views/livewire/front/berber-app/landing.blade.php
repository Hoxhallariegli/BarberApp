<div
    class="relative min-h-screen bg-paper dark:bg-ink text-ink dark:text-paper selection:bg-brass/30 selection:text-ink overflow-x-hidden"
    x-data="{ showBooking: @entangle('showBookingModal'), scrolled: false }"
    x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 40)"
>

    {{-- Figma / Canva Style Floating Nav Bar --}}
    <header class="fixed top-4 inset-x-0 z-[110] px-6">
        <div class="max-w-6xl mx-auto h-16 px-6 rounded-full flex items-center justify-between"
             :class="scrolled ? 'bg-paper-soft/90 dark:bg-ink-elevated/90 backdrop-blur-md border border-gray-200 dark:border-gray-800 shadow-sm' : 'bg-transparent'">

            <a href="#top" class="flex items-center gap-3 cursor-pointer">
                <img src="{{ asset('images/STATION.jpg') }}" class="size-10 rounded-full object-cover border border-brass/30">
                <div class="font-display text-xl tracking-wider leading-none uppercase">
                    <span class="text-ink dark:text-white">THE STATION</span>
                    <span class="text-brass">BARBERS</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-xs font-bold uppercase tracking-widest text-ink/70 dark:text-paper/70">
                <a href="#services" class="cursor-pointer hover:text-brass">{{ __('front/berber-app.our_services') }}</a>
                <a href="#team" class="cursor-pointer hover:text-brass">{{ __('front/berber-app.meet_team') }}</a>
            </nav>

            <div class="flex items-center gap-3">
                <!-- Language Switcher -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="size-10 rounded-full border border-gray-200 dark:border-gray-800 flex items-center justify-center text-ink/70 dark:text-paper/70 bg-paper-soft dark:bg-ink-elevated cursor-pointer text-[10px] font-black uppercase">
                        {{ app()->getLocale() }}
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-24 bg-paper dark:bg-ink-elevated rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 z-50 overflow-hidden">
                        <a href="{{ route('language.switch', 'en') }}" class="block px-4 py-2 text-[10px] font-black uppercase hover:bg-brass/10 {{ app()->getLocale() == 'en' ? 'text-brass' : '' }}">English</a>
                        <a href="{{ route('language.switch', 'sq') }}" class="block px-4 py-2 text-[10px] font-black uppercase hover:bg-brass/10 {{ app()->getLocale() == 'sq' ? 'text-brass' : '' }}">Shqip</a>
                    </div>
                </div>

                <a href="#services" class="hidden sm:inline-flex items-center px-5 py-2 rounded-full bg-ink dark:bg-brass text-paper dark:text-ink font-semibold text-xs uppercase tracking-wider active:scale-95 cursor-pointer">
                    {{ __('front/berber-app.book_now') }}
                </a>

                <button id="theme-toggle-front" aria-label="Ndrysho temën" class="size-10 rounded-full border border-gray-200 dark:border-gray-800 flex items-center justify-center text-ink/70 dark:text-paper/70 bg-paper-soft dark:bg-ink-elevated cursor-pointer">
                    <x-heroicon-o-sun class="size-4 dark:hidden" />
                    <x-heroicon-o-moon class="size-4 hidden dark:block" />
                </button>
            </div>
        </div>
    </header>

    {{-- ============ HERO ============ --}}
    <section id="top" class="relative pt-36 pb-24 lg:pt-48 lg:pb-32 grain overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_rgba(198,161,91,0.12),transparent_65%)]"></div>

        <div class="max-w-7xl mx-auto px-6">
            <div class="grid lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brass/10 border border-brass/20 mb-8">
                        <span class="size-1.5 rounded-full bg-brass"></span>
                        <span class="font-serif italic text-xs tracking-wide text-brass-deep dark:text-brass font-medium">{{ __('front/berber-app.welcome_to') }} The Station Barbers</span>
                    </div>

                    <h1 class="font-display text-[4rem] leading-[0.9] sm:text-[5.5rem] lg:text-[6.5rem] tracking-wide mb-6">
                        THE STATION<br>
                        <span class="text-brass">BARBERS</span><br>
                        TIRANA
                    </h1>

                    <p class="font-serif italic text-lg sm:text-xl text-ink/60 dark:text-paper/60 max-w-xl mb-10 leading-relaxed">
                        {{ __('front/berber-app.hero_subtitle') }}
                    </p>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                        <a href="#services" class="inline-flex items-center justify-center gap-2.5 px-8 py-4 bg-ink dark:bg-brass text-paper dark:text-ink rounded-2xl font-bold text-sm uppercase tracking-wider active:scale-95 shadow-sm cursor-pointer">
                            {{ __('front/berber-app.book_now') }}
                            <x-heroicon-o-arrow-up-right class="size-4"/>
                        </a>
                        <a href="https://www.instagram.com/thestationbarbers/" target="_blank" class="inline-flex items-center justify-center px-8 py-4 bg-paper-soft dark:bg-ink-soft border border-gray-200 dark:border-gray-800 rounded-2xl font-bold text-sm uppercase tracking-wider cursor-pointer">
                            Instagram
                        </a>
                    </div>

                    <div class="grid grid-cols-3 gap-6 mt-14 pt-10 border-t border-gray-200 dark:border-gray-800 max-w-lg">
                        <div>
                            <p class="font-display text-3xl sm:text-4xl text-brass">{{ $barbers->count() ?: '—' }}</p>
                            <p class="text-[11px] uppercase tracking-widest text-ink/40 dark:text-paper/40 font-bold mt-1">{{ __('front/berber-app.barber') }}s</p>
                        </div>
                        <div>
                            <p class="font-display text-3xl sm:text-4xl text-brass">{{ $services->count() ?: '—' }}</p>
                            <p class="text-[11px] uppercase tracking-widest text-ink/40 dark:text-paper/40 font-bold mt-1">{{ __('front/berber-app.our_services') }}</p>
                        </div>
                        <div>
                            <p class="font-display text-3xl sm:text-4xl text-brass">4.9</p>
                            <p class="text-[11px] uppercase tracking-widest text-ink/40 dark:text-paper/40 font-bold mt-1">Rating</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 relative">
                    <div class="relative w-full aspect-[4/5] rounded-[2.5rem] bg-paper-soft dark:bg-ink-soft border border-gray-200 dark:border-gray-800 p-4 shadow-xl overflow-hidden flex flex-col justify-between">
                        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <span class="size-3 rounded-full bg-rose-500/80"></span>
                                <span class="size-3 rounded-full bg-amber-500/80"></span>
                                <span class="size-3 rounded-full bg-emerald-500/80"></span>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-ink/40 dark:text-paper/40"></span>
                        </div>

                        <div class="my-auto p-6 text-center space-y-4">
                            <div class="size-20 mx-auto rounded-3xl bg-brass/10 text-brass flex items-center justify-center">
                                <x-heroicon-o-scissors class="size-10"/>
                            </div>
                            <p class="font-serif italic text-xl text-ink/80 dark:text-paper/80 leading-snug">"{{ __('front/berber-app.footer_text') }}"</p>
                        </div>

                        <div class="p-5 rounded-2xl bg-paper dark:bg-ink-elevated border border-gray-200 dark:border-gray-800 flex items-center justify-between text-xs font-semibold">
                            <span class="text-ink/50 dark:text-paper/50 uppercase tracking-wider">Status</span>
                            <span class="flex items-center gap-1.5 text-emerald-500"><span class="size-2 rounded-full bg-emerald-500"></span> Open</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <div class="blade-stripe"></div>

    {{-- ============ SERVICES GRID ============ --}}
    <section id="services" class="py-24 px-6 bg-paper-soft dark:bg-ink-soft/50">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
                <div>
                    <span class="font-serif italic text-brass-deep dark:text-brass text-base">{{ __('front/berber-app.our_services') }}</span>
                    <h2 class="font-display text-5xl sm:text-6xl tracking-wide mt-1 uppercase">{{ __('front/berber-app.our_services') }}</h2>
                </div>
                <p class="text-ink/55 dark:text-paper/55 max-w-sm text-sm leading-relaxed">
                    {{ __('front/berber-app.services_subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($services as $item)
                    <button
                        type="button"
                        wire:click="selectService({{ $item->id }})"
                        wire:loading.attr="disabled"
                        class="tap-card group relative text-left p-8 rounded-[2rem] bg-paper dark:bg-ink-elevated border border-gray-200 dark:border-gray-800 overflow-hidden active:scale-[0.98] cursor-pointer hover:border-brass/50"
                    >
                        <div class="flex items-start justify-between mb-8">
                            <div class="size-12 rounded-2xl bg-brass/10 text-brass-deep dark:text-brass flex items-center justify-center">
                                <x-heroicon-o-scissors class="size-5"/>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-ink/5 dark:bg-paper/5 text-11px font-bold uppercase tracking-widest text-ink/60 dark:text-paper/60">
                                {{ $item->duration_minutes }} min
                            </span>
                        </div>

                        <h3 class="font-display text-2xl tracking-wide mb-2 uppercase">{{ $item->name }}</h3>
                        <p class="text-ink/55 dark:text-paper/55 text-sm leading-relaxed mb-8">
                            {{ __('front/berber-app.services_subtitle') }}
                        </p>

                        <div class="flex items-center gap-2 text-xs font-bold text-brass-deep dark:text-brass uppercase tracking-widest">
                            {{ __('front/berber-app.book_now') }}
                            <x-heroicon-o-arrow-right class="size-4"/>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ TEAM SECTION ============ --}}
    <section id="team" class="py-24 px-6 bg-paper-elevated dark:bg-ink-soft relative grain transition-colors duration-300">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <span class="font-serif italic text-brass-deep dark:text-brass text-lg">{{ __('front/berber-app.meet_team') }}</span>
                <h2 class="font-display text-6xl tracking-wider mt-2 mb-4 text-ink dark:text-white uppercase">{{ __('front/berber-app.meet_team') }}</h2>
                <div class="flex flex-col items-center gap-2">
                    <p class="text-ink/50 dark:text-paper/50 text-[10px] font-black uppercase tracking-[0.3em]">{{ __('front/berber-app.team_subtitle') }}</p>
                    <div class="h-1 w-12 bg-brass rounded-full"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
                @foreach($barbers as $item)
                    <div class="group text-center p-10 rounded-[3rem] bg-paper dark:bg-ink-elevated border border-gray-200 dark:border-gray-800 hover:border-brass/40 transition-all duration-300 shadow-sm hover:shadow-xl">
                        <div class="relative size-44 mx-auto mb-8 rounded-[2.5rem] overflow-hidden ring-1 ring-gray-200 dark:ring-gray-800 group-hover:ring-brass/50 transition-all duration-500">
                            @if($item->photo)
                                <img src="{{ asset($item->photo) }}" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-700 scale-105 group-hover:scale-100">
                            @else
                                <div class="w-full h-full bg-paper-soft dark:bg-ink-soft flex items-center justify-center font-display text-7xl text-brass/40">{{ substr($item->name, 0, 1) }}</div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-ink/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        </div>

                        <h3 class="font-display text-3xl tracking-wider mb-2 text-ink dark:text-white group-hover:text-brass transition-colors">{{ $item->name }}</h3>
                        <p class="text-brass-deep dark:text-brass font-black text-[10px] uppercase tracking-[0.25em] leading-relaxed max-w-[200px] mx-auto opacity-80">{{ $item->specialization }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="blade-stripe"></div>

    {{-- ============ FOOTER ============ --}}
    <footer class="py-12 px-6 bg-paper-elevated dark:bg-ink-soft border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="flex items-center gap-3 text-left">
                    <img src="{{ asset('images/STATION.jpg') }}" class="size-9 rounded-full object-cover">
                    <div class="font-display text-lg tracking-wider uppercase">
                        <span class="text-ink dark:text-white">THE STATION</span>
                        <span class="text-brass">BARBERS</span>
                    </div>
                </div>
                <a href="https://maps.app.goo.gl/w1fP4XX5uehTN6t68" target="_blank" class="text-[11px] font-bold uppercase tracking-[0.1em] text-ink/50 dark:text-paper/50 hover:text-brass transition-colors">
                    📍 Rruga e Kavajës, Tiranë
                </a>
            </div>
            <p class="text-xs text-ink/50 dark:text-paper/50 font-medium">
                © {{ date('Y') }} THE STATION BARBERS.
            </p>
        </div>
    </footer>

    {{-- ============ BOOKING MODAL (Zero Delay, Pure Instant) ============ --}}
    <div x-show="showBooking"
         x-cloak
         class="fixed inset-0 z-[200] flex items-end md:items-center justify-center p-0 md:p-4">

        <div x-show="showBooking" class="absolute inset-0 bg-ink/80 backdrop-blur-sm" @click="showBooking = false"></div>

        <div x-show="showBooking"
             class="relative w-full md:max-w-xl max-h-[92vh] md:max-h-[85vh] overflow-y-auto custom-scrollbar bg-paper-soft dark:bg-ink-elevated rounded-t-[2.5rem] md:rounded-[2.5rem] shadow-2xl border border-gray-200 dark:border-gray-800 z-10">

            <div class="p-8">
                <div wire:loading.flex wire:target="selectService" class="absolute inset-0 z-[60] bg-paper-soft/80 dark:bg-ink-elevated/80 backdrop-blur-sm items-center justify-center flex-col gap-4">
                    <div class="size-12 border-4 border-brass/20 border-t-brass rounded-full animate-spin"></div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brass-deep">{{ __('front/berber-app.loading_slots') }}</p>
                </div>

                <div class="flex items-center justify-between pb-6 mb-6 border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <span class="font-serif italic text-brass-deep dark:text-brass text-xs">{{ __('front/berber-app.booking_system') }}</span>
                        <h2 class="font-display text-3xl tracking-wide mt-0.5 uppercase">{{ __('front/berber-app.your_appointment') }}</h2>
                    </div>
                    <button @click="showBooking = false" class="size-10 rounded-full bg-ink/5 dark:bg-paper/5 flex items-center justify-center cursor-pointer hover:bg-ink/10 dark:hover:bg-paper/10">
                        <x-heroicon-o-x-mark class="size-5 text-ink/60 dark:text-paper/60"/>
                    </button>
                </div>

                {{-- Step 2: Date & Time Selection --}}
                @if($step == 2)
                    <div class="space-y-6">
                        {{-- Custom Notification Prompt --}}
                        @if(!$fcmToken)
                            <div class="bg-brass/10 p-6 rounded-3xl border border-brass/20 flex items-center justify-between gap-6">
                                <div class="flex items-center gap-4">
                                    <div class="size-12 bg-brass/20 text-brass-deep dark:text-brass rounded-2xl flex items-center justify-center shrink-0">
                                        <x-heroicon-o-bell-alert class="size-6"/>
                                    </div>
                                    <div>
                                        <p class="text-ink dark:text-white font-bold text-sm leading-tight italic">{{ __('front/berber-app.allow_notifications') }}</p>
                                        <p class="text-ink/50 dark:text-gray-400 text-xs mt-1">{{ __('front/berber-app.notification_description') }}</p>
                                    </div>
                                </div>
                                <button type="button" onclick="window.requestNotificationPermission()" class="px-6 py-3 bg-brass text-ink dark:text-paper rounded-2xl text-xs font-black uppercase tracking-widest hover:scale-105 transition-all">Lejo</button>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-ink/45 dark:text-paper/45 mb-2 ml-1">{{ __('front/berber-app.date') }}</label>
                                <input type="date" wire:model.live="selectedDate" min="{{ date('Y-m-d') }}" class="w-full p-3.5 bg-paper dark:bg-ink-soft border border-gray-200 dark:border-gray-800 rounded-2xl font-semibold text-sm cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-ink/45 dark:text-paper/45 mb-2 ml-1">{{ __('front/berber-app.barber') }}</label>
                                <select wire:model.live="selectedBarberId" class="w-full p-3.5 bg-paper dark:bg-ink-soft border border-gray-200 dark:border-gray-800 rounded-2xl font-semibold text-sm cursor-pointer">
                                    <option value="">{{ __('front/berber-app.any_barber') }}</option>
                                    @foreach($barbers as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-widest text-ink/45 dark:text-paper/45 mb-2 ml-1">{{ __('front/berber-app.available_slots') }}</label>
                            <div class="grid grid-cols-3 gap-2.5 max-h-52 overflow-y-auto pr-1 custom-scrollbar">
                                @forelse($this->availableSlots as $time)
                                    <button type="button" wire:click="confirmTime('{{ $time }}')" class="p-3 bg-paper dark:bg-ink-soft border border-gray-200 dark:border-gray-800 font-bold text-sm rounded-xl cursor-pointer hover:border-brass transition-all">
                                        {{ $time }}
                                    </button>
                                @empty
                                    <div class="col-span-full py-8 text-center text-xs text-ink/40 dark:text-paper/40 italic">{{ __('front/berber-app.no_slots') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 3: Customer Information Input --}}
                @if($step == 3 && $selectedService)
                    <div class="space-y-6">
                        <div class="p-4 rounded-2xl bg-brass/10 border border-brass/20 flex items-center gap-4">
                            <div class="size-10 bg-ink dark:bg-brass text-brass dark:text-ink rounded-xl flex items-center justify-center shrink-0">
                                <x-heroicon-o-calendar class="size-5"/>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm uppercase">{{ $selectedService->name }}</h4>
                                <p class="text-ink/60 dark:text-paper/60 text-xs">{{ Carbon\Carbon::parse($selectedDate)->format('d M Y') }} - {{ $selectedTime }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-ink/45 dark:text-paper/45 mb-2 ml-1">{{ __('front/berber-app.full_name') }}</label>
                                <input type="text" wire:model="customerName" placeholder="sh. Filan Fisteku" class="w-full p-3.5 bg-paper dark:bg-ink-soft border border-gray-200 dark:border-gray-800 rounded-2xl text-sm font-semibold">
                                @error('customerName') <span class="text-rose-500 text-xs mt-1 ml-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-ink/45 dark:text-paper/45 mb-2 ml-1">{{ __('front/berber-app.phone_number') }}</label>
                                <input type="tel" wire:model="customerPhone" placeholder="069 XX XX XXX" class="w-full p-3.5 bg-paper dark:bg-ink-soft border border-gray-200 dark:border-gray-800 rounded-2xl text-sm font-semibold">
                                @error('customerPhone') <span class="text-rose-500 text-xs mt-1 ml-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="button" wire:click="$set('step', 2)" class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-ink/50 cursor-pointer">{{ __('front/berber-app.back') }}</button>
                            <button type="button" wire:click="submitBooking" class="flex-1 py-3.5 bg-ink dark:bg-brass text-paper dark:text-ink rounded-2xl font-bold text-xs uppercase tracking-wider cursor-pointer shadow-lg active:scale-95 transition-all">{{ __('front/berber-app.confirm_booking') }}</button>
                        </div>
                    </div>
                @endif

                {{-- Step 4: Success State --}}
                @if($step == 4)
                    <div class="text-center py-6 space-y-6">
                        <div class="size-20 bg-emerald-500/10 text-emerald-500 rounded-3xl flex items-center justify-center mx-auto">
                            <x-heroicon-o-check-circle class="size-10"/>
                        </div>
                        <div>
                            <h2 class="font-display text-3xl tracking-wide mb-2 uppercase">{{ __('front/berber-app.booking_completed') }}</h2>
                            <p class="text-sm text-ink/60 dark:text-paper/60 max-w-sm mx-auto">{{ __('front/berber-app.thank_you', ['name' => $customerName]) }}</p>
                        </div>
                        <button type="button" wire:click="resetBooking" class="px-8 py-3.5 bg-ink dark:bg-brass text-paper dark:text-ink rounded-full font-bold text-xs uppercase tracking-wider cursor-pointer">{{ __('front/berber-app.close_window') }}</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--brass); opacity: 0.3; border-radius: 10px; }
        .tap-card { -webkit-tap-highlight-color: transparent; }
    </style>

    <script>
        document.getElementById('theme-toggle-front')?.addEventListener('click', function() {
            let isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</div>
