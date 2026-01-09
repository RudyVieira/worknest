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
        
        $space = Space::factory()->create();
        $hoursBooked = ($endDatetime->getTimestamp() - $startDatetime->getTimestamp()) / 3600;
        $totalPrice = $space->price_per_hour * $hoursBooked;

        return [
            'user_id' => User::factory(),
            'space_id' => $space->id,
            'zap_appointment_id' => fake()->numberBetween(1, 1000),
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'status' => $status,
            'stripe_payment_intent_id' => $status === 'PAID' ? 'pi_' . fake()->uuid() : null,
            'total_price' => round($totalPrice, 2),
            'paid_at' => $status === 'PAID' ? fake()->dateTimeBetween($startDatetime, 'now') : null,
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
