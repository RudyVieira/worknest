<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Zap\Models\Concerns\HasSchedules;

class Space extends Model
{
    use HasFactory, HasSchedules;

    protected $fillable = [
        'name',
        'description',
        'image',
        'latitude',
        'longitude',
        'address',
        'capacity',
        'price_per_hour',
        'owner_id',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'price_per_hour' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function equipmentTypes(): BelongsToMany
    {
        return $this->belongsToMany(EquipmentType::class, 'space_equipment')
            ->withPivot('quantity')
            ->using(SpaceEquipment::class);
    }
}
