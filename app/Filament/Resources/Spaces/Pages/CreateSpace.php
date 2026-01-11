<?php

namespace App\Filament\Resources\Spaces\Pages;

use App\Filament\Resources\Spaces\SpaceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSpace extends CreateRecord
{
    protected static string $resource = SpaceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extraire les équipements avant la création
        $equipments = $data['space_equipment'] ?? [];
        unset($data['space_equipment']);

        // Stocker temporairement pour la sauvegarde après
        $this->cachedEquipments = $equipments;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Attacher les équipements avec leurs quantités
        $attachData = [];
        foreach ($this->cachedEquipments ?? [] as $equipment) {
            $attachData[$equipment['equipment_type_id']] = ['quantity' => $equipment['quantity']];
        }

        $this->record->equipmentTypes()->attach($attachData);
        
        // Assigner automatiquement le rôle 'owner' au propriétaire de l'espace
        if ($this->record->owner && !$this->record->owner->hasRole('owner')) {
            $this->record->owner->assignRole('owner');
        }
    }
}
