<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDatetime = fake()->dateTimeBetween('-2 months', '+1 month');
        $endDatetime = (clone $startDatetime)->modify('+' . fake()->numberBetween(1, 8) . ' hours');

        $status = fake()->randomElement(['PENDING', 'PAID', 'PAID', 'PAID', 'CANCELLED']);
        
        // Use an existing space instead of creating a new one
        $space = Space::inRandomOrder()->first() ?? Space::factory()->create();
        $numberOfPeople = fake()->numberBetween(1, min($space->capacity, 4));
        $hoursBooked = ($endDatetime->getTimestamp() - $startDatetime->getTimestamp()) / 3600;
        $totalPrice = $space->price_per_hour * $hoursBooked * $numberOfPeople;

        // Pour les réservations PAID, la date de paiement doit être avant la date de début ou maintenant
        $paidAt = null;
        if ($status === 'PAID') {
            $maxPaidDate = $startDatetime < now() ? $startDatetime : now();
            $paidAt = fake()->dateTimeBetween('-2 months', $maxPaidDate);
        }

        return [
            'user_id' => User::factory(),
            'space_id' => $space->id,
            'number_of_people' => $numberOfPeople,
            'zap_appointment_id' => fake()->numberBetween(1, 1000),
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'status' => $status,
            'stripe_payment_intent_id' => $status === 'PAID' ? 'pi_' . fake()->uuid() : null,
            'total_price' => round($totalPrice, 2),
            'paid_at' => $paidAt,
        ];
    }

    /**
     * Indicate that the reservation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PENDING',
            'stripe_payment_intent_id' => null,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the reservation is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PAID',
            'stripe_payment_intent_id' => 'pi_' . fake()->uuid(),
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the reservation is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'CANCELLED',
        ]);
    }
}
