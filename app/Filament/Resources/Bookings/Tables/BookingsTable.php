<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable(['users.firstname', 'users.lastname'])
                    ->sortable(),
                TextColumn::make('space.name')
                    ->label('Espace')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->space->owner ? 'Propriétaire: ' . $record->space->owner->name : null),
                TextColumn::make('start_datetime')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('end_datetime')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label('Prix total')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'CONFIRMED' => 'success',
                        'CANCELLED' => 'danger',
                        'COMPLETED' => 'info',
                        'PAID' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'PENDING' => 'En attente',
                        'CONFIRMED' => 'Confirmé',
                        'CANCELLED' => 'Annulé',
                        'COMPLETED' => 'Terminé',
                        'PAID' => 'Payé',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Payé le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non payé')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('stripe_payment_intent_id')
                    ->label('Paiement Stripe')
                    ->placeholder('N/A')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'PENDING' => 'En attente',
                        'CONFIRMED' => 'Confirmé',
                        'CANCELLED' => 'Annulé',
                        'COMPLETED' => 'Terminé',
                        'PAID' => 'Payé',
                    ]),
                SelectFilter::make('space_id')
                    ->label('Espace')
                    ->relationship('space', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label('Client')
                    ->relationship('user', 'firstname')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ])
            ->defaultSort('start_datetime', 'desc');
    }
}
