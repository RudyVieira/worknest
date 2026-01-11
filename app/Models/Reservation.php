<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'space_id',
        'number_of_people',
        'zap_appointment_id',
        'start_datetime',
        'end_datetime',
        'status',
        'stripe_payment_intent_id',
        'total_price',
        'paid_at',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'paid_at' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function zapAppointment(): BelongsTo
    {
        return $this->belongsTo(\Zap\Models\SchedulePeriod::class, 'zap_appointment_id');
    }
}
