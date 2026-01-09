<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EquipmentType extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function spaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class, 'space_equipment')
            ->withPivot('quantity');
    }
}
