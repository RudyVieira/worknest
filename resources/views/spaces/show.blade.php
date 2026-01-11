@extends('layouts.app-public')

@section('title', $space->name . ' - WorkNest')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Back button -->
    <div class="mb-4">
        <a href="{{ route('spaces.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour aux espaces
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Space Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Hero Image -->
                @if($space->image)
                    <div class="h-64 overflow-hidden">
                        <img src="{{ Storage::url($space->image) }}" alt="{{ $space->name }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="h-64 bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center">
                        <svg class="h-32 w-32 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                @endif

                <!-- Details -->
                <div class="p-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $space->name }}</h1>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="text-gray-700">{{ $space->capacity }} personnes</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-6 w-6 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-gray-700 font-semibold">{{ number_format($space->price_per_hour, 2) }}€ / heure</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Description</h2>
                        <p class="text-gray-600">{{ $space->description }}</p>
                    </div>

                    @if($space->equipmentTypes->count() > 0)
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-3">Équipements disponibles</h2>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($space->equipmentTypes as $equipment)
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-gray-700">{{ $equipment->name }} (x{{ $equipment->pivot->quantity }})</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="border-t pt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Propriétaire</h2>
                        <p class="text-gray-600">{{ $space->owner->firstname }} {{ $space->owner->lastname }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Panel -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Réserver cet espace</h2>

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Day selector -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choisir une date</label>
                    <div class="grid grid-cols-7 gap-1">
                        @foreach($nextDays as $day)
                            <a href="{{ route('spaces.show', ['space' => $space, 'date' => $day['date']]) }}" 
                                class="text-center p-2 rounded {{ $date == $day['date'] ? 'bg-indigo-600 text-white' : ($day['has_availability'] ? 'bg-gray-100 hover:bg-gray-200' : 'bg-gray-50 text-gray-400 cursor-not-allowed') }}">
                                <div class="text-xs">{{ $day['day_name'] }}</div>
                                <div class="text-sm font-bold">{{ $day['day_number'] }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Available slots -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Créneaux disponibles le {{ \Carbon\Carbon::parse($date)->isoFormat('DD MMMM YYYY') }}
                    </label>

                    @if(count($availableSlots) > 0)
                        <form method="POST" action="{{ route('spaces.book', $space) }}" class="space-y-2" id="booking-form">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="end_time" id="end_time">
                            
                            <!-- Number of people selector -->
                            <div class="mb-4">
                                <label for="number_of_people" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre de personnes
                                </label>
                                <select name="number_of_people" id="number_of_people" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                    @for($i = 1; $i <= $space->capacity; $i++)
                                        <option value="{{ $i }}" {{ old('number_of_people', 1) == $i ? 'selected' : '' }}>
                                            {{ $i }} personne{{ $i > 1 ? 's' : '' }}
                                        </option>
                                    @endfor
                                </select>
                                <p class="mt-1 text-xs text-gray-500">
                                    Le tarif est multiplié par le nombre de personnes
                                </p>
                            </div>

                            @foreach($availableSlots as $slot)
                                <label class="block">
                                    <input type="radio" name="start_time" value="{{ substr($slot['start_time'], 0, 5) }}" 
                                        data-end-time="{{ substr($slot['end_time'], 0, 5) }}"
                                        data-base-price="{{ $slot['price'] }}"
                                        data-remaining-capacity="{{ $slot['remaining_capacity'] ?? $space->capacity }}"
                                        required
                                        class="sr-only peer slot-radio">
                                    <div class="border-2 border-gray-200 rounded-lg p-3 cursor-pointer hover:border-indigo-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ substr($slot['start_time'], 0, 5) }} - {{ substr($slot['end_time'], 0, 5) }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $slot['duration'] }} heure(s)
                                                    @if(isset($slot['remaining_capacity']) && $slot['remaining_capacity'] < $space->capacity)
                                                        • {{ $slot['remaining_capacity'] }} place(s) restante(s)
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-bold text-indigo-600 slot-price" data-base-price="{{ $slot['price'] }}">{{ number_format($slot['price'], 2) }}€</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const form = document.getElementById('booking-form');
                                    const radios = document.querySelectorAll('.slot-radio');
                                    const endTimeInput = document.getElementById('end_time');
                                    const numberOfPeopleSelect = document.getElementById('number_of_people');
                                    
                                    // Function to update number of people options based on selected slot
                                    function updatePeopleOptions() {
                                        const selectedSlot = document.querySelector('.slot-radio:checked');
                                        if (selectedSlot) {
                                            const remainingCapacity = parseInt(selectedSlot.getAttribute('data-remaining-capacity'));
                                            const currentValue = parseInt(numberOfPeopleSelect.value);
                                            
                                            // Update options
                                            Array.from(numberOfPeopleSelect.options).forEach(option => {
                                                const value = parseInt(option.value);
                                                if (value > remainingCapacity) {
                                                    option.disabled = true;
                                                    option.text = option.text.replace(' personne', ' personne (non disponible)');
                                                } else {
                                                    option.disabled = false;
                                                    option.text = value + ' personne' + (value > 1 ? 's' : '');
                                                }
                                            });
                                            
                                            // Reset selection if current value exceeds capacity
                                            if (currentValue > remainingCapacity) {
                                                numberOfPeopleSelect.value = Math.min(currentValue, remainingCapacity);
                                            }
                                        }
                                    }
                                    
                                    // Function to update prices based on number of people
                                    function updatePrices() {
                                        const numberOfPeople = parseInt(numberOfPeopleSelect.value);
                                        
                                        document.querySelectorAll('.slot-price').forEach(priceEl => {
                                            const basePrice = parseFloat(priceEl.getAttribute('data-base-price'));
                                            const adjustedPrice = basePrice * numberOfPeople;
                                            priceEl.textContent = adjustedPrice.toFixed(2) + '€';
                                        });
                                    }
                                    
                                    // Update prices when number of people changes
                                    numberOfPeopleSelect.addEventListener('change', updatePrices);
                                    
                                    // Update end_time and people options when a slot is selected
                                    radios.forEach(radio => {
                                        radio.addEventListener('change', function() {
                                            endTimeInput.value = this.getAttribute('data-end-time');
                                            updatePeopleOptions();
                                            console.log('Créneau sélectionné:', this.value, '-', endTimeInput.value);
                                        });
                                    });

                                    // Validate form submission
                                    form.addEventListener('submit', function(e) {
                                        const selectedSlot = document.querySelector('.slot-radio:checked');
                                        if (!selectedSlot) {
                                            e.preventDefault();
                                            alert('Veuillez sélectionner un créneau horaire.');
                                            return false;
                                        }
                                        if (!endTimeInput.value) {
                                            e.preventDefault();
                                            alert('Erreur: heure de fin non définie. Veuillez sélectionner à nouveau un créneau.');
                                            return false;
                                        }
                                        
                                        // Validate number of people doesn't exceed remaining capacity
                                        const remainingCapacity = parseInt(selectedSlot.getAttribute('data-remaining-capacity'));
                                        const numberOfPeople = parseInt(numberOfPeopleSelect.value);
                                        if (numberOfPeople > remainingCapacity) {
                                            e.preventDefault();
                                            alert(`Le nombre de personnes (${numberOfPeople}) dépasse la capacité restante (${remainingCapacity}).`);
                                            return false;
                                        }
                                        
                                        console.log('Formulaire soumis avec:', {
                                            date: '{{ $date }}',
                                            start_time: selectedSlot.value,
                                            end_time: endTimeInput.value,
                                            number_of_people: numberOfPeopleSelect.value
                                        });
                                    });
                                });
                            </script>

                            @auth
                                <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 font-semibold mt-4">
                                    Réserver maintenant
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="block w-full bg-gray-400 text-white py-3 px-4 rounded-md text-center font-semibold mt-4">
                                    Connectez-vous pour réserver
                                </a>
                            @endauth
                        </form>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2">Aucun créneau disponible pour cette date</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
