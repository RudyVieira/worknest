<?php

namespace App\Filament\Resources\Spaces\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
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
                        FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->disk('public')
                            ->directory('spaces')
                            ->visibility('public')
                            ->maxSize(5120) // 5MB
                            ->columnSpanFull()
                            ->helperText('Format acceptés: JPG, PNG. Taille maximale: 5MB'),
                        TextInput::make('address')
                            ->label('Adresse')
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
