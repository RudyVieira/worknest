@extends('layouts.app-public')

@section('title', 'Espaces disponibles - WorkNest')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Espaces disponibles</h1>
        <p class="mt-2 text-gray-600">Trouvez l'espace de travail idéal pour vos besoins</p>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <form method="GET" action="{{ route('spaces.index') }}" class="flex gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700">Rechercher</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                    placeholder="Nom ou description..." 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="w-48">
                <label for="capacity" class="block text-sm font-medium text-gray-700">Capacité minimum</label>
                <input type="number" name="capacity" id="capacity" value="{{ request('capacity') }}" 
                    min="1" placeholder="Ex: 10"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                    Rechercher
                </button>
            </div>
        </form>
    </div>

    <!-- Map Section -->
    @if($allSpaces->count() > 0)
        <div class="bg-white p-4 rounded-lg shadow mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Carte des espaces</h2>
            <div id="map" style="height: 500px; width: 100%;"></div>
        </div>
    @endif

    <!-- Spaces Grid -->
    @if($spaces->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($spaces as $space)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    @if($space->image)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url($space->image) }}" alt="{{ $space->name }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="h-48 bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center">
                            <svg class="h-24 w-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    @endif
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 line-clamp-1">{{ $space->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $space->description }}</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                Capacité : {{ $space->capacity }} personnes
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                {{ number_format($space->price_per_hour, 2) }}€ / heure
                            </div>
                        </div>

                        @if($space->equipmentTypes->count() > 0)
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 mb-1">Équipements:</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($space->equipmentTypes->take(3) as $equipment)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $equipment->name }}
                                        </span>
                                    @endforeach
                                    @if($space->equipmentTypes->count() > 3)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            +{{ $space->equipmentTypes->count() - 3 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('spaces.show', $space) }}" 
                            class="block w-full text-center bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors">
                            Voir les disponibilités
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $spaces->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun espace trouvé</h3>
            <p class="mt-1 text-sm text-gray-500">Essayez de modifier vos critères de recherche.</p>
        </div>
    @endif
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
    crossorigin=""/>
<style>
    .leaflet-popup-content {
        margin: 13px 19px;
        line-height: 1.4;
    }
    .space-popup h3 {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 8px;
    }
    .space-popup p {
        font-size: 13px;
        color: #4B5563;
        margin: 4px 0;
    }
    .space-popup a {
        display: inline-block;
        margin-top: 8px;
        padding: 6px 12px;
        background-color: #4F46E5;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 13px;
        transition: background-color 0.2s;
    }
    .space-popup a:hover {
        background-color: #4338CA;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
    crossorigin=""></script>
<script>
    @if($allSpaces->count() > 0)
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map - center on France by default
        var map = L.map('map').setView([46.603354, 1.888334], 6);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        var markers = [];
        
        // Add markers for each space
        @foreach($allSpaces as $space)
            @if($space->latitude && $space->longitude)
                var marker = L.marker([{{ $space->latitude }}, {{ $space->longitude }}])
                    .addTo(map)
                    .bindPopup(`
                        <div class="space-popup">
                            <h3>{{ addslashes($space->name) }}</h3>
                            <p><strong>Adresse:</strong> {{ addslashes($space->address) }}</p>
                            <p><strong>Prix:</strong> {{ number_format($space->price_per_hour, 2) }}€/heure</p>
                            <p><strong>Capacité:</strong> {{ $space->capacity }} personnes</p>
                            <a href="{{ route('spaces.show', $space) }}">Voir les disponibilités</a>
                        </div>
                    `);
                markers.push(marker);
            @endif
        @endforeach
        
        // Fit map to show all markers
        if (markers.length > 0) {
            var group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    });
    @endif
</script>
@endpush
@endsection
