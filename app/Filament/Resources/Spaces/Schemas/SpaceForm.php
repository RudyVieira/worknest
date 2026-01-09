<?php

namespace App\Filament\Resources\Spaces\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\EquipmentType;

class SpaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->required()
                            ->numeric(),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->required()
                            ->numeric(),
                        TextInput::make('capacity')
                            ->label('Capacité')
                            ->required()
                            ->numeric(),
                        TextInput::make('price_per_hour')
                            ->label('Prix par heure')
                            ->required()
                            ->numeric()
                            ->prefix('€'),
                        Select::make('owner_id')
                            ->label('Propriétaire')
                            ->relationship(
                                name: 'owner',
                                titleAttribute: 'firstname',
                                modifyQueryUsing: fn ($query) => $query->orderBy('firstname')->orderBy('lastname')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->firstname} {$record->lastname}")
                            ->searchable(['firstname', 'lastname', 'email'])
                            ->required(),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'AVAILABLE' => 'Disponible',
                                'MAINTENANCE' => 'Maintenance',
                                'DISABLED' => 'Désactivé',
                            ])
                            ->default('AVAILABLE')
                            ->required(),
                    ])
                    ->columns(2),
                
                Section::make('Équipements')
                    ->schema([
                        Repeater::make('space_equipment')
                            ->schema([
                                Select::make('equipment_type_id')
                                    ->label('Type d\'équipement')
                                    ->options(EquipmentType::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TextInput::make('quantity')
                                    ->label('Quantité')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Ajouter un équipement')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                EquipmentType::find($state['equipment_type_id'])?->name . ' (x' . ($state['quantity'] ?? 1) . ')'
                            ),
                    ])
                    ->collapsible(),
            ]);
    }
}
