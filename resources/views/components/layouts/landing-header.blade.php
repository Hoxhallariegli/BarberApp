<nav class="fixed top-0 inset-x-0 z-50 border-b border-slate-200/70 dark:border-white/[0.06] bg-slate-50/80 dark:bg-[#05070d]/80 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="size-9 rounded-lg bg-gradient-to-br from-indigo-600 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <span class="text-white font-bold text-sm tracking-tight">E4</span>
            </div>
            <span class="font-bold text-lg tracking-tight text-slate-900 dark:text-white">ProTech <span class="text-slate-400 dark:text-slate-500 font-medium">Ecosystem</span></span>
        </div>

        <div class="flex items-center gap-4">
            <!-- Language Switcher -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-1 text-sm font-bold uppercase focus:outline-none text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    <span>{{ app()->getLocale() }}</span>
                    <x-heroicon-o-chevron-down class="size-3" />
                </button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-24 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-100 dark:border-slate-700 z-50 overflow-hidden">
                    @php
                        $languages = ['en'];
                        if (File::exists(lang_path())) {
                            foreach (File::directories(lang_path()) as $dir) {
                                $lang = basename($dir);
                                if (strlen($lang) <= 5 && !in_array($lang, $languages)) $languages[] = $lang;
                            }
                            foreach (File::files(lang_path()) as $file) {
                                if ($file->getExtension() === 'json') {
                                    $lang = str_replace('.json', '', $file->getFilename());
                                    if (strlen($lang) <= 5 && !in_array($lang, $languages)) $languages[] = $lang;
                                }
                            }
                        }
                        sort($languages);
                    @endphp
                    @foreach($languages as $lang)
                        <a href="{{ route('language.switch', $lang) }}" class="block px-4 py-2 text-xs font-bold uppercase hover:bg-slate-50 dark:hover:bg-slate-700 {{ app()->getLocale() == $lang ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-300' }}">{{ $lang }}</a>
                    @endforeach
                </div>
            </div>

            @auth
                @php
                    $path = request()->path();
                    $modules = [
                        'berber-app', 'clinic-management', 'auto-repair-management', 'construction-e-r-p',
                        'warehouse-management', 'restaurant-p-o-s', 'school-management', 'real-estate-c-r-m',
                        'c-r-m', 'hotel-management', 'human-resources', 'e--commerce', 'fleet-management',
                        'gym-management', 'finance', 'legal-management', 'pharmacy-management',
                        'event-management', 'travel-agency', 'facility-management', 'agriculture-management'
                    ];
                    $activeModule = null;
                    foreach ($modules as $m) {
                        if (str_contains($path, $m)) {
                            $activeModule = $m;
                            break;
                        }
                    }
                    $dashboardRoute = $activeModule ? "admin.{$activeModule}.dashboard" : "dashboard";
                    if (!\Illuminate\Support\Facades\Route::has($dashboardRoute)) {
                        $dashboardRoute = "dashboard";
                    }
                @endphp
                <a href="{{ route($dashboardRoute) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-semibold hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors">
                    <x-heroicon-o-squares-2x2 class="size-4" />
                    {{ __('Admin Panel') }}
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    Login
                </a>
            @endauth
        </div>
    </div>
</nav>
