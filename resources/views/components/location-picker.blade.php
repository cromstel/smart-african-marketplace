@props(['latitude' => null, 'longitude' => null, 'address' => '', 'height' => '400px'])

<div class="space-y-3" x-data="locationPicker{{ Str::random(8) }}({{ $latitude ?? 'null' }}, {{ $longitude ?? 'null' }})">
    <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">Search Location</label>
        <div class="flex gap-2">
            <input 
                type="text" 
                x-model="searchQuery"
                @keydown.enter.prevent="searchLocation"
                placeholder="Enter address or place name..."
                class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            >
            <button
                type="button"
                @click="searchLocation"
                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition"
                aria-label="Search location"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <button 
                type="button"
                @click="getCurrentLocation"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition"
                title="Use my current location"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
        </div>
        <p class="text-xs text-gray-500 mt-1">Click on the map to set location or search for an address</p>
    </div>

    <div id="map-{{ Str::random(8) }}" class="rounded-lg overflow-hidden border border-gray-600" style="height: {{ $height }}; position: relative; z-index: 0;"></div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Latitude</label>
            <input 
                type="text" 
                name="latitude" 
                x-model="lat"
                readonly
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
            >
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Longitude</label>
            <input 
                type="text" 
                name="longitude" 
                x-model="lng"
                readonly
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
            >
        </div>
    </div>

    <div x-show="address" class="p-3 bg-gray-700 rounded-lg border border-gray-600">
        <p class="text-sm text-gray-300">
            <strong>Selected Location:</strong>
            <span x-text="address"></span>
        </p>
    </div>
</div>

@push('scripts')
<script>
function locationPicker{{ Str::random(8) }}(initialLat, initialLng) {
    return {
        map: null,
        marker: null,
        lat: initialLat || '',
        lng: initialLng || '',
        searchQuery: '{{ $address }}',
        address: '',

        init() {
            const mapId = this.$el.querySelector('[id^="map-"]').id;
            const defaultLat = initialLat || 6.5244; // Lagos, Nigeria
            const defaultLng = initialLng || 3.3792;
            
            this.map = L.map(mapId).setView([defaultLat, defaultLng], initialLat ? 13 : 10);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(this.map);

            if (initialLat && initialLng) {
                this.addMarker(initialLat, initialLng);
            }

            this.map.on('click', (e) => {
                this.addMarker(e.latlng.lat, e.latlng.lng);
                this.reverseGeocode(e.latlng.lat, e.latlng.lng);
            });
        },

        addMarker(lat, lng) {
            if (this.marker) {
                this.map.removeLayer(this.marker);
            }

            this.marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(this.map);

            this.lat = lat.toFixed(6);
            this.lng = lng.toFixed(6);

            this.marker.on('dragend', (e) => {
                const position = e.target.getLatLng();
                this.lat = position.lat.toFixed(6);
                this.lng = position.lng.toFixed(6);
                this.reverseGeocode(position.lat, position.lng);
            });

            this.map.setView([lat, lng], 13);
        },

        async searchLocation() {
            if (!this.searchQuery) {
                alert('Please enter a location to search');
                return;
            }

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();

                if (data.length > 0) {
                    const result = data[0];
                    this.addMarker(parseFloat(result.lat), parseFloat(result.lon));
                    this.address = result.display_name;
                } else {
                    alert('Location not found. Please try a different search term.');
                }
            } catch (error) {
                console.error('Geocoding error:', error);
                alert('Failed to search location. Please try again.');
            }
        },

        async reverseGeocode(lat, lng) {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await response.json();
                this.address = data.display_name || '';
            } catch (error) {
                console.error('Reverse geocoding error:', error);
            }
        },

        getCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.addMarker(position.coords.latitude, position.coords.longitude);
                    this.reverseGeocode(position.coords.latitude, position.coords.longitude);
                },
                (error) => {
                    alert('Unable to retrieve your location: ' + error.message);
                }
            );
        }
    };
}
</script>
@endpush

