<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de la réservation')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'PENDING' => 'warning',
                                'CONFIRMED' => 'success',
                                'CANCELLED' => 'danger',
                                'COMPLETED' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('user.name')
                            ->label('Client'),
                        TextEntry::make('space.name')
                            ->label('Espace'),
                        TextEntry::make('start_datetime')
                            ->label('Date et heure de début')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('end_datetime')
                            ->label('Date et heure de fin')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('total_price')
                            ->label('Prix total')
                            ->money('EUR'),
                        TextEntry::make('paid_at')
                            ->label('Payé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Non payé'),
                    ]),
                Section::make('Informations techniques')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('stripe_payment_intent_id')
                            ->label('ID Paiement Stripe')
                            ->placeholder('Non disponible'),
                        TextEntry::make('zap_appointment_id')
                            ->label('ID Rendez-vous Zap')
                            ->placeholder('Non disponible'),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
