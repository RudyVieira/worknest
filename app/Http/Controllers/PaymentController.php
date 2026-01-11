<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Show payment page for a reservation.
     */
    public function show(Reservation $reservation)
    {
        // Verify authorization
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'Non autorisé');
        }

        // Verify reservation is pending
        if ($reservation->status !== 'PENDING') {
            return redirect()->route('reservations.show', $reservation)
                ->with('error', 'Cette réservation a déjà été traitée.');
        }

        $reservation->load(['space', 'user']);

        // Create or retrieve Payment Intent
        try {
            if ($reservation->stripe_payment_intent_id) {
                // Retrieve existing payment intent
                $paymentIntent = PaymentIntent::retrieve($reservation->stripe_payment_intent_id);
                
                // If payment intent is not in correct state, create a new one
                if (!in_array($paymentIntent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action'])) {
                    $paymentIntent = $this->createPaymentIntent($reservation);
                    $reservation->update(['stripe_payment_intent_id' => $paymentIntent->id]);
                }
            } else {
                // Create new payment intent
                $paymentIntent = $this->createPaymentIntent($reservation);
                $reservation->update(['stripe_payment_intent_id' => $paymentIntent->id]);
            }

            return view('payment.checkout', [
                'reservation' => $reservation,
                'clientSecret' => $paymentIntent->client_secret,
                'stripeKey' => config('services.stripe.key'),
            ]);

        } catch (ApiErrorException $e) {
            return back()->with('error', 'Erreur lors de la création du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Create a Stripe Payment Intent.
     */
    private function createPaymentIntent(Reservation $reservation): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $reservation->total_price * 100, // Convert to cents
            'currency' => 'eur',
            'metadata' => [
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'space_id' => $reservation->space_id,
                'number_of_people' => $reservation->number_of_people,
            ],
            'description' => "Réservation de {$reservation->space->name} pour le {$reservation->start_datetime->format('d/m/Y')} - {$reservation->number_of_people} personne(s)",
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);
    }

    /**
     * Handle payment success callback.
     */
    public function success(Request $request, Reservation $reservation)
    {
        // Verify authorization
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'Non autorisé');
        }

        try {
            // Retrieve the payment intent
            $paymentIntent = PaymentIntent::retrieve($reservation->stripe_payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                // Update reservation
                $reservation->update([
                    'status' => 'PAID',
                    'paid_at' => now(),
                ]);

                // Create invoice
                Invoice::create([
                    'user_id' => $reservation->user_id,
                    'reservation_id' => $reservation->id,
                    'amount' => $reservation->total_price,
                ]);

                // Log activity
                auth()->user()->activityLogs()->create([
                    'action' => "Paiement confirmé pour la réservation #{$reservation->id}",
                ]);

                return redirect()->route('reservations.show', $reservation)
                    ->with('success', 'Paiement réussi ! Votre réservation est confirmée.');
            }

            return redirect()->route('payment.show', $reservation)
                ->with('error', 'Le paiement n\'a pas été confirmé. Veuillez réessayer.');

        } catch (ApiErrorException $e) {
            return redirect()->route('payment.show', $reservation)
                ->with('error', 'Erreur lors de la vérification du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment cancellation.
     */
    public function cancel(Reservation $reservation)
    {
        // Verify authorization
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'Non autorisé');
        }

        return redirect()->route('reservations.show', $reservation)
            ->with('info', 'Paiement annulé. Vous pouvez réessayer quand vous voulez.');
    }

    /**
     * Handle Stripe webhook.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook.secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $reservationId = $paymentIntent->metadata->reservation_id ?? null;

            if ($reservationId) {
                $reservation = Reservation::find($reservationId);

                if ($reservation && $reservation->status === 'PENDING') {
                    $reservation->update([
                        'status' => 'PAID',
                        'paid_at' => now(),
                    ]);

                    // Create invoice if not exists
                    if (!$reservation->invoice) {
                        Invoice::create([
                            'user_id' => $reservation->user_id,
                            'reservation_id' => $reservation->id,
                            'amount' => $reservation->total_price,
                        ]);
                    }
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
