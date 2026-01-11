@extends('layouts.app-public')

@section('title', 'Paiement - WorkNest')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Finaliser votre réservation</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Payment Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Informations de paiement</h2>

                <form id="payment-form">
                    @csrf
                    
                    <!-- Card Element -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Carte bancaire
                        </label>
                        <div id="card-element" class="p-3 border border-gray-300 rounded-md bg-white">
                            <!-- Stripe Card Element will be inserted here -->
                        </div>
                        <div id="card-errors" class="text-red-600 text-sm mt-2" role="alert"></div>
                    </div>

                    <!-- Billing Name -->
                    <div class="mb-6">
                        <label for="billing-name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom sur la carte
                        </label>
                        <input type="text" id="billing-name" name="billing_name" 
                            value="{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submit-button"
                        class="w-full bg-indigo-600 text-white py-3 px-6 rounded-md hover:bg-indigo-700 font-semibold transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <span id="button-text">Payer {{ number_format($reservation->total_price, 2) }}€</span>
                        <span id="spinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Traitement en cours...
                        </span>
                    </button>

                    <div class="mt-4 text-center">
                        <a href="{{ route('reservations.show', $reservation) }}" 
                            class="text-sm text-gray-600 hover:text-gray-800">
                            Annuler et revenir à la réservation
                        </a>
                    </div>
                </form>

                <!-- Payment Info -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="h-5 w-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Paiement sécurisé par Stripe</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Vos informations de paiement sont cryptées et sécurisées. Nous ne stockons pas vos données bancaires.
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Récapitulatif</h2>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Espace</p>
                        <p class="font-semibold text-gray-900">{{ $reservation->space->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="font-semibold text-gray-900">
                            {{ $reservation->start_datetime->isoFormat('dddd DD MMMM YYYY') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Horaire</p>
                        <p class="font-semibold text-gray-900">
                            {{ $reservation->start_datetime->format('H:i') }} - {{ $reservation->end_datetime->format('H:i') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Durée</p>
                        <p class="font-semibold text-gray-900">
                            {{ $reservation->start_datetime->diffInHours($reservation->end_datetime) }} heure(s)
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Nombre de personnes</p>
                        <p class="font-semibold text-gray-900">
                            {{ $reservation->number_of_people }} personne{{ $reservation->number_of_people > 1 ? 's' : '' }}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500">Tarif horaire</p>
                            <p class="font-semibold text-gray-900">{{ number_format($reservation->space->price_per_hour, 2) }}€</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t-2 border-gray-900">
                        <div class="flex justify-between items-center">
                            <p class="text-lg font-bold text-gray-900">Total à payer</p>
                            <p class="text-2xl font-bold text-indigo-600">{{ number_format($reservation->total_price, 2) }}€</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Initialize Stripe
    const stripe = Stripe('{{ $stripeKey }}');
    const elements = stripe.elements();

    // Create card element
    const cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#dc2626',
                iconColor: '#dc2626'
            }
        }
    });

    // Mount card element
    cardElement.mount('#card-element');

    // Handle real-time validation errors
    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Handle form submission
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');

    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        // Disable submit button
        submitButton.disabled = true;
        buttonText.classList.add('hidden');
        spinner.classList.remove('hidden');

        // Confirm payment
        const { error } = await stripe.confirmCardPayment('{{ $clientSecret }}', {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: document.getElementById('billing-name').value,
                }
            }
        });

        if (error) {
            // Show error to customer
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;

            // Re-enable submit button
            submitButton.disabled = false;
            buttonText.classList.remove('hidden');
            spinner.classList.add('hidden');
        } else {
            // Payment succeeded, redirect to success page
            window.location.href = '{{ route('payment.success', $reservation) }}';
        }
    });
</script>
@endsection
