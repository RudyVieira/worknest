<?php

namespace App\Filament\Resources\Spaces\Pages;

use App\Filament\Resources\Spaces\SpaceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSpace extends EditRecord
{
    protected static string $resource = SpaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Charger les équipements de l'espace
        $data['space_equipment'] = $this->record->equipmentTypes()
            ->get()
            ->map(fn($equipment) => [
                'equipment_type_id' => $equipment->id,
                'quantity' => $equipment->pivot->quantity,
            ])
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extraire les équipements avant la sauvegarde
        $equipments = $data['space_equipment'] ?? [];
        unset($data['space_equipment']);

        // Stocker temporairement pour la sauvegarde après
        $this->cachedEquipments = $equipments;

        return $data;
    }

    protected function afterSave(): void
    {
        // Synchroniser les équipements avec leurs quantités
        $syncData = [];
        foreach ($this->cachedEquipments ?? [] as $equipment) {
            $syncData[$equipment['equipment_type_id']] = ['quantity' => $equipment['quantity']];
        }

        $this->record->equipmentTypes()->sync($syncData);
        
        // Assigner automatiquement le rôle 'owner' au propriétaire de l'espace
        if ($this->record->owner && !$this->record->owner->hasRole('owner')) {
            $this->record->owner->assignRole('owner');
        }
    }
}
