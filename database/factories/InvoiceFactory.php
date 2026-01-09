<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reservation_id' => Reservation::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'invoice_pdf_url' => fake()->boolean(70) ? 'invoices/' . fake()->uuid() . '.pdf' : null,
        ];
    }

    /**
     * Indicate that the invoice has a PDF.
     */
    public function withPdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_pdf_url' => 'invoices/' . fake()->uuid() . '.pdf',
        ]);
    }

    /**
     * Indicate that the invoice does not have a PDF.
     */
    public function withoutPdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_pdf_url' => null,
        ]);
    }
}
