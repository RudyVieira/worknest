<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Select::make('reservation_id')
                    ->relationship('reservation', 'id'),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('invoice_pdf_url')
                    ->url(),
            ]);
    }
}
