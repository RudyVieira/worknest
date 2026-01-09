<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;
use Zap\Enums\ScheduleTypes;
use Zap\Models\SchedulePeriod;

class ReservationController extends Controller
{
    /**
     * Display available time slots for a space.
     */
    public function availableSlots(Space $space, Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        // Get all available periods for the space
        $availableSchedule = $space->schedules()
            ->where('schedule_type', ScheduleTypes::AVAILABILITY)
            ->where('is_active', true)
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->first();

        if (!$availableSchedule) {
            return response()->json([
                'message' => 'Aucun horaire disponible pour cet espace',
                'slots' => [],
            ]);
        }

        // Get available periods for the date
        $periods = $availableSchedule->periods()
            ->where('date', $date)
            ->where('is_available', true)
            ->orderBy('start_time')
            ->get();

        // Check for existing appointments to exclude booked slots
        $bookedPeriods = SchedulePeriod::whereHas('schedule', function ($query) use ($space) {
                $query->where('schedulable_type', Space::class)
                    ->where('schedulable_id', $space->id)
                    ->where('schedule_type', ScheduleTypes::APPOINTMENT);
            })
            ->where('date', $date)
            ->where('is_available', false)
            ->get(['start_time', 'end_time']);

        // Filter out booked slots
        $availableSlots = $periods->filter(function ($period) use ($bookedPeriods) {
            foreach ($bookedPeriods as $booked) {
                // Check if period overlaps with booked slot
                if ($period->start_time < $booked->end_time && $period->end_time > $booked->start_time) {
                    return false;
                }
            }
            return true;
        });

        return response()->json([
            'date' => $date,
            'space' => $space->name,
            'slots' => $availableSlots->map(function ($slot) use ($space) {
                $hours = (strtotime($slot->end_time) - strtotime($slot->start_time)) / 3600;
                return [
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'price' => $space->price_per_hour * $hours,
                ];
            }),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservations = Reservation::with(['space', 'user'])
            ->where('user_id', auth()->id())
            ->orderBy('start_datetime', 'desc')
            ->paginate(15);

        return response()->json($reservations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Space $space)
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Verify the slot is available
        $isAvailable = $this->checkAvailability(
            $space,
            $validated['date'],
            $validated['start_time'],
            $validated['end_time']
        );

        if (!$isAvailable) {
            return response()->json([
                'message' => 'Ce créneau n\'est pas disponible',
            ], 422);
        }

        // Create or get appointment schedule
        $appointmentSchedule = $space->schedules()->firstOrCreate([
            'schedule_type' => ScheduleTypes::APPOINTMENT,
            'start_date' => $validated['date'],
        ], [
            'name' => 'Réservations',
            'description' => 'Créneaux réservés par les clients',
            'is_active' => true,
        ]);

        // Calculate price
        $startDateTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $endDateTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
        $hours = $endDateTime->diffInHours($startDateTime, true);
        $totalPrice = $space->price_per_hour * $hours;

        // Create the appointment period
        $period = $appointmentSchedule->periods()->create([
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_available' => false, // Mark as booked
            'metadata' => [
                'user_id' => auth()->id(),
                'price' => $totalPrice,
            ],
        ]);

        // Create the reservation in PENDING status
        $reservation = Reservation::create([
            'user_id' => auth()->id(),
            'space_id' => $space->id,
            'zap_appointment_id' => $period->id,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'status' => 'PENDING',
            'total_price' => $totalPrice,
        ]);

        return response()->json([
            'message' => 'Réservation créée en attente de paiement',
            'reservation' => $reservation,
            'payment_required' => true,
        ], 201);
    }

    /**
     * Confirm payment and update reservation.
     */
    public function confirmPayment(Reservation $reservation, Request $request)
    {
        $validated = $request->validate([
            'stripe_payment_intent_id' => 'required|string',
        ]);

        if ($reservation->status !== 'PENDING') {
            return response()->json([
                'message' => 'Cette réservation ne peut pas être confirmée',
            ], 422);
        }

        // Update reservation
        $reservation->update([
            'status' => 'PAID',
            'stripe_payment_intent_id' => $validated['stripe_payment_intent_id'],
            'paid_at' => now(),
        ]);

        // Create invoice
        $reservation->invoice()->create([
            'user_id' => $reservation->user_id,
            'amount' => $reservation->total_price,
        ]);

        // Log activity
        auth()->user()->activityLogs()->create([
            'action' => 'Réservation confirmée pour ' . $reservation->space->name,
        ]);

        return response()->json([
            'message' => 'Paiement confirmé',
            'reservation' => $reservation->load('space', 'invoice'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        return response()->json(
            $reservation->load(['space', 'user', 'invoice', 'zapAppointment'])
        );
    }

    /**
     * Cancel a reservation.
     */
    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Non autorisé',
            ], 403);
        }

        if ($reservation->status === 'CANCELLED') {
            return response()->json([
                'message' => 'Cette réservation est déjà annulée',
            ], 422);
        }

        // Update reservation
        $reservation->update([
            'status' => 'CANCELLED',
        ]);

        // Free up the period
        if ($reservation->zapAppointment) {
            $reservation->zapAppointment->update([
                'is_available' => true,
            ]);
        }

        // Log activity
        auth()->user()->activityLogs()->create([
            'action' => 'Réservation annulée pour ' . $reservation->space->name,
        ]);

        return response()->json([
            'message' => 'Réservation annulée',
        ]);
    }

    /**
     * Check if a time slot is available.
     */
    private function checkAvailability(Space $space, string $date, string $startTime, string $endTime): bool
    {
        // Check if the space has availability schedule for this date
        $hasAvailability = $space->schedules()
            ->where('schedule_type', ScheduleTypes::AVAILABILITY)
            ->where('is_active', true)
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->whereHas('periods', function ($query) use ($date, $startTime, $endTime) {
                $query->where('date', $date)
                    ->where('start_time', '<=', $startTime)
                    ->where('end_time', '>=', $endTime)
                    ->where('is_available', true);
            })
            ->exists();

        if (!$hasAvailability) {
            return false;
        }

        // Check if there's no conflicting appointment
        $hasConflict = $space->schedules()
            ->where('schedule_type', ScheduleTypes::APPOINTMENT)
            ->whereHas('periods', function ($query) use ($date, $startTime, $endTime) {
                $query->where('date', $date)
                    ->where('is_available', false)
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->whereBetween('start_time', [$startTime, $endTime])
                            ->orWhereBetween('end_time', [$startTime, $endTime])
                            ->orWhere(function ($q2) use ($startTime, $endTime) {
                                $q2->where('start_time', '<=', $startTime)
                                    ->where('end_time', '>=', $endTime);
                            });
                    });
            })
            ->exists();

        return !$hasConflict;
    }
}
