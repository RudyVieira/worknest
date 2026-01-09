<?php

namespace App\Filament\Resources\Spaces\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpaceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('latitude')
                            ->label('Latitude')
                            ->numeric(),
                        TextEntry::make('longitude')
                            ->label('Longitude')
                            ->numeric(),
                        TextEntry::make('capacity')
                            ->label('Capacité')
                            ->numeric(),
                        TextEntry::make('price_per_hour')
                            ->label('Prix par heure')
                            ->money('EUR')
                            ->numeric(),
                        TextEntry::make('owner.name')
                            ->label('Propriétaire'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'AVAILABLE' => 'Disponible',
                                'MAINTENANCE' => 'Maintenance',
                                'DISABLED' => 'Désactivé',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'AVAILABLE' => 'success',
                                'MAINTENANCE' => 'warning',
                                'DISABLED' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Équipements')
                    ->schema([
                        RepeatableEntry::make('equipmentTypes')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Équipement'),
                                TextEntry::make('pivot.quantity')
                                    ->label('Quantité')
                                    ->numeric(),
                            ])
                            ->columns(2)
                            ->contained(false),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record?->equipmentTypes->isNotEmpty()),
            ]);
    }
}
