<div class="space-y-8">
    <div class="flex items-center justify-between px-1">
        <div>
            <x-h1>Trip Details #{{ $trip->id }}</x-h1>
            <x-short-description>Review trip history and route visualization.</x-short-description>
        </div>
        <x-back-btn route="admin.trips.index" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Details Card -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700">
                <h3 class="text-[10px] font-black uppercase text-blue-600 tracking-[0.2em] mb-6">Informacioni</h3>

                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="size-12 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center text-blue-600">
                            <x-heroicon-o-user class="size-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Shoferi</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $trip->driver?->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="size-12 bg-green-50 dark:bg-green-900/20 rounded-2xl flex items-center justify-center text-green-600">
                            <x-heroicon-o-truck class="size-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mjeti</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $trip->vehicle?->name }} ({{ $trip->vehicle?->license_plate }})</p>
                        </div>
                    </div>

                    <div class="w-full h-px bg-gray-100 dark:bg-gray-700"></div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Distanca</p>
                            <p class="text-xl font-black text-gray-900 dark:text-white">{{ $trip->distance_km }} KM</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kohëzgjatja</p>
                            <p class="text-xl font-black text-gray-900 dark:text-white">{{ $trip->duration_minutes }} Min</p>
                        </div>
                    </div>

                    <div class="pt-4 space-y-4">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nisja</p>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-300 leading-tight">{{ $trip->start_point }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Destinacioni</p>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-300 leading-tight">{{ $trip->end_point }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest
                            {{ $trip->status === 'completed' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' }}">
                            {{ $trip->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Card -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 p-2 rounded-[3rem] shadow-sm border border-gray-100 dark:border-gray-700 h-[600px] relative overflow-hidden">
                <div id="trip-view-map" class="w-full h-full rounded-[2.5rem]" wire:ignore></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <script>
        function initTripMap() {
            const container = document.getElementById('trip-view-map');
            if (!container) return;

            const map = L.map('trip-view-map', { zoomControl: false }).setView([{{ $trip->start_lat }}, {{ $trip->start_lng }}], 13);

            const isDark = document.documentElement.classList.contains('dark');
            const theme = isDark ? 'dark_all' : 'rastertiles/voyager';
            L.tileLayer('https://{s}.basemaps.cartocdn.com/' + theme + '/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; CARTO'
            }).addTo(map);

            const sLat = {{ $trip->start_lat ?? 0 }};
            const sLng = {{ $trip->start_lng ?? 0 }};
            const eLat = {{ $trip->end_lat ?? 0 }};
            const eLng = {{ $trip->end_lng ?? 0 }};

            if (sLat && eLat) {
                // Draw Markers
                L.circleMarker([sLat, sLng], { radius: 10, fillColor: '#3b82f6', color: '#fff', weight: 3, fillOpacity: 1 }).addTo(map).bindPopup('Nisja');
                L.circleMarker([eLat, eLng], { radius: 10, fillColor: '#ef4444', color: '#fff', weight: 3, fillOpacity: 1 }).addTo(map).bindPopup('Destinacioni');

                // Draw Route
                const routing = L.Routing.control({
                    waypoints: [L.latLng(sLat, sLng), L.latLng(eLat, eLng)],
                    router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1', useHints: false }),
                    lineOptions: { styles: [{color: '#f97316', opacity: 0.8, weight: 7}] },
                    show: false, addWaypoints: false, draggableWaypoints: false,
                    createMarker: () => null
                }).addTo(map);

                setTimeout(() => {
                    map.fitBounds(L.latLngBounds([L.latLng(sLat, sLng), L.latLng(eLat, eLng)]), {padding: [50, 50]});
                }, 500);
            }

            setTimeout(() => map.invalidateSize(), 200);
        }

        window.addEventListener('load', initTripMap);
        document.addEventListener('livewire:navigated', initTripMap);
    </script>
    @endpush
</div>
