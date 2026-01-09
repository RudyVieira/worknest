<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SpaceEquipment extends Pivot
{
    protected $table = 'space_equipment';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'space_id',
        'equipment_type_id',
        'quantity',
    ];
}
