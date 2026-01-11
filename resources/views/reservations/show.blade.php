@extends('layouts.app-public')

@section('title', 'Détails de la réservation - WorkNest')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Back button -->
    <div class="mb-4">
        <a href="{{ route('reservations.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour aux réservations
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-8 py-6">
            <h1 class="text-3xl font-bold text-white">Réservation #{{ $reservation->id }}</h1>
            <div class="mt-2">
                @if($reservation->status === 'PAID')
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Payée et confirmée
                    </span>
                @elseif($reservation->status === 'PENDING')
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        En attente de paiement
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Annulée
                    </span>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="p-8">
            <!-- Space Info -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Espace réservé</h2>
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-2xl font-semibold text-indigo-600 mb-2">{{ $reservation->space->name }}</h3>
                    <p class="text-gray-600 mb-4">{{ $reservation->space->description }}</p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center text-gray-700">
                            <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Capacité: {{ $reservation->space->capacity }} personnes
                        </div>
                        <div class="flex items-center text-gray-700">
                            <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $reservation->space->latitude }}, {{ $reservation->space->longitude }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Détails de la réservation</h2>
                <div class="bg-gray-50 rounded-lg p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Date</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $reservation->start_datetime->isoFormat('dddd DD MMMM YYYY') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Horaire</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $reservation->start_datetime->format('H:i') }} - {{ $reservation->end_datetime->format('H:i') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Durée</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $reservation->start_datetime->diffInHours($reservation->end_datetime) }} heure(s)
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Nombre de personnes</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $reservation->number_of_people }} personne{{ $reservation->number_of_people > 1 ? 's' : '' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Montant total</dt>
                            <dd class="text-2xl font-bold text-indigo-600">
                                {{ number_format($reservation->total_price, 2) }}€
                            </dd>
                        </div>
                        @if($reservation->paid_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Date de paiement</dt>
                                <dd class="text-lg font-semibold text-gray-900">
                                    {{ $reservation->paid_at->isoFormat('DD MMMM YYYY à HH:mm') }}
                                </dd>
                            </div>
                        @endif
                        @if($reservation->stripe_payment_intent_id)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">ID de transaction</dt>
                                <dd class="text-sm text-gray-900 font-mono">
                                    {{ $reservation->stripe_payment_intent_id }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Invoice -->
            @if($reservation->invoice)
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Facture</h2>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Facture #{{ $reservation->invoice->id }}</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($reservation->invoice->amount, 2) }}€</p>
                                <p class="text-sm text-gray-500">Créée le {{ $reservation->invoice->created_at->isoFormat('DD MMMM YYYY') }}</p>
                            </div>
                            @if($reservation->invoice->invoice_pdf_url)
                                <a href="#" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                    <svg class="inline h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Télécharger PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex gap-4">
                @if($reservation->status === 'PENDING')
                    <a href="{{ route('payment.show', $reservation) }}" 
                        class="flex-1 bg-green-600 text-white py-3 px-6 rounded-md hover:bg-green-700 font-semibold text-center">
                        Procéder au paiement
                    </a>
                @endif
                
                @if($reservation->status !== 'CANCELLED')
                    <form method="POST" action="{{ route('reservations.cancel', $reservation) }}" class="{{ $reservation->status === 'PENDING' ? 'flex-1' : 'w-full' }}">
                        @csrf
                        <button type="submit" 
                            onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')"
                            class="w-full bg-red-600 text-white py-3 px-6 rounded-md hover:bg-red-700 font-semibold">
                            Annuler la réservation
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
