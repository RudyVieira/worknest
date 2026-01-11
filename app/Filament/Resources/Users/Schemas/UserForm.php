<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('firstname')
                    ->label('Prénom')
                    ->required(),
                TextInput::make('lastname')
                    ->label('Nom')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label('Email vérifié le'),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->required(fn ($context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state)),
                Select::make('status')
                    ->label('Statut')
                    ->options([
                        'ACTIVE' => 'Actif', 
                        'SUSPENDED' => 'Suspendu'
                    ])
                    ->default('ACTIVE')
                    ->required(),
                Select::make('roles')
                    ->label('Rôles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => match($record->name) {
                        'admin' => 'Administrateur',
                        'owner' => 'Propriétaire',
                        'user' => 'Utilisateur',
                        default => $record->name,
                    })
                    ->helperText('Sélectionnez un ou plusieurs rôles pour cet utilisateur'),
            ]);
    }
}
