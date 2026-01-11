<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Zap\Enums\ScheduleTypes;
use Carbon\Carbon;

class SpaceController extends Controller
{
    /**
     * Display a listing of available spaces.
     */
    public function index(Request $request)
    {
        $query = Space::with('owner', 'equipmentTypes')
            ->where('status', 'AVAILABLE');

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Capacity filter
        if ($request->has('capacity') && is_numeric($request->input('capacity'))) {
            $query->where('capacity', '>=', $request->input('capacity'));
        }

        $spaces = $query->paginate(9);
        
        // Get all spaces for map (without pagination)
        $allSpaces = Space::with('owner', 'equipmentTypes')
            ->where('status', 'AVAILABLE')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('spaces.index', compact('spaces', 'allSpaces'));
    }

    /**
     * Display the specified space with availability calendar.
     */
    public function show(Space $space)
    {
        $space->load('owner', 'equipmentTypes');

        // Get the date from request or use today
        $date = request('date', now()->toDateString());
        $carbonDate = Carbon::parse($date);

        // Get available slots for the selected date
        $availableSlots = $this->getAvailableSlotsForDate($space, $carbonDate);

        // Get next 7 days for calendar navigation
        $nextDays = collect();
        for ($i = 0; $i < 7; $i++) {
            $day = now()->addDays($i);
            $nextDays->push([
                'date' => $day->toDateString(),
                'day_name' => $day->isoFormat('ddd'),
                'day_number' => $day->day,
                'has_availability' => $this->hasAvailability($space, $day),
            ]);
        }

        return view('spaces.show', compact('space', 'availableSlots', 'date', 'nextDays'));
    }

    /**
     * Get available slots for a specific date.
     */
    private function getAvailableSlotsForDate(Space $space, Carbon $date): array
    {
        $slots = [];

        // Get availability schedule
        $availabilitySchedule = $space->schedules()
            ->where('schedule_type', ScheduleTypes::AVAILABILITY)
            ->where('is_active', true)
            ->where('start_date', '<=', $date->toDateString())
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date->toDateString());
            })
            ->first();

        if (!$availabilitySchedule) {
            return $slots;
        }

        // Get all available periods for this date
        $periods = $availabilitySchedule->periods()
            ->where('date', $date->toDateString())
            ->where('is_available', true)
            ->orderBy('start_time')
            ->get();

        // Check capacity for each slot
        foreach ($periods as $period) {
            // Get all overlapping reservations for this time slot
            $overlappingReservations = Reservation::where('space_id', $space->id)
                ->where('status', '!=', 'CANCELLED')
                ->where(function ($query) use ($date, $period) {
                    $startDateTime = $date->toDateString() . ' ' . $period->start_time;
                    $endDateTime = $date->toDateString() . ' ' . $period->end_time;
                    $query->where(function ($q) use ($startDateTime, $endDateTime) {
                        $q->where('start_datetime', '<', $endDateTime)
                            ->where('end_datetime', '>', $startDateTime);
                    });
                })
                ->sum('number_of_people');

            // Calculate remaining capacity
            $remainingCapacity = $space->capacity - $overlappingReservations;

            // Only show slot if there's capacity remaining
            if ($remainingCapacity > 0) {
                $startTime = Carbon::parse($period->start_time);
                $endTime = Carbon::parse($period->end_time);
                $hours = $endTime->diffInHours($startTime, true);

                $slots[] = [
                    'start_time' => $period->start_time,
                    'end_time' => $period->end_time,
                    'duration' => $hours,
                    'price' => round($space->price_per_hour * $hours, 2),
                    'remaining_capacity' => $remainingCapacity,
                ];
            }
        }

        return $slots;
    }

    /**
     * Check if space has availability for a specific date.
     */
    private function hasAvailability(Space $space, Carbon $date): bool
    {
        return $space->schedules()
            ->where('schedule_type', ScheduleTypes::AVAILABILITY)
            ->where('is_active', true)
            ->where('start_date', '<=', $date->toDateString())
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date->toDateString());
            })
            ->whereHas('periods', function ($query) use ($date) {
                $query->where('date', $date->toDateString())
                    ->where('is_available', true);
            })
            ->exists();
    }

    /**
     * Store a new reservation (booking).
     */
    public function book(Request $request, Space $space)
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'number_of_people' => 'required|integer|min:1|max:' . $space->capacity,
        ]);

        // Verify the user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour réserver.');
        }

        // Verify space is available
        if ($space->status !== 'AVAILABLE') {
            return back()->with('error', 'Cet espace n\'est pas disponible pour le moment.');
        }

        // Check availability
        $availabilityCheck = $this->checkSlotAvailability(
            $space,
            $validated['date'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['number_of_people']
        );

        if (!$availabilityCheck['available']) {
            return back()->with('error', $availabilityCheck['message']);
        }

        // Create or get appointment schedule
        $appointmentSchedule = $space->schedules()->firstOrCreate([
            'schedule_type' => ScheduleTypes::APPOINTMENT,
            'start_date' => $validated['date'],
        ], [
            'name' => 'Réservations',
            'description' => 'Créneaux réservés',
            'is_active' => true,
        ]);

        // Calculate price
        $startDateTime = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $endDateTime = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
        $hours = $endDateTime->diffInHours($startDateTime, true);
        // Price is multiplied by the number of people
        $totalPrice = round($space->price_per_hour * $hours * $validated['number_of_people'], 2);

        // Create the appointment period
        $period = $appointmentSchedule->periods()->create([
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_available' => false,
            'metadata' => [
                'user_id' => auth()->id(),
                'price' => $totalPrice,
            ],
        ]);

        // Create the reservation
        $reservation = Reservation::create([
            'user_id' => auth()->id(),
            'space_id' => $space->id,
            'number_of_people' => $validated['number_of_people'],
            'zap_appointment_id' => $period->id,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'status' => 'PENDING',
            'total_price' => $totalPrice,
        ]);

        return redirect()->route('payment.show', $reservation)
            ->with('success', 'Réservation créée ! Procédez au paiement pour confirmer.');
    }

    /**
     * Check if a specific slot is available with the requested number of people.
     */
    private function checkSlotAvailability(Space $space, string $date, string $startTime, string $endTime, int $numberOfPeople): array
    {
        // Check availability schedule exists
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
            return [
                'available' => false,
                'message' => 'Ce créneau n\'est pas disponible.'
            ];
        }

        // Get all overlapping reservations for this time slot
        $overlappingReservations = Reservation::where('space_id', $space->id)
            ->where('status', '!=', 'CANCELLED')
            ->where(function ($query) use ($date, $startTime, $endTime) {
                $startDateTime = $date . ' ' . $startTime;
                $endDateTime = $date . ' ' . $endTime;
                $query->where(function ($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_datetime', '<', $endDateTime)
                        ->where('end_datetime', '>', $startDateTime);
                });
            })
            ->sum('number_of_people');

        // Check if adding this reservation would exceed capacity
        $remainingCapacity = $space->capacity - $overlappingReservations;
        
        if ($numberOfPeople > $remainingCapacity) {
            return [
                'available' => false,
                'message' => "Ce créneau n'a plus assez de place disponible. Capacité restante: {$remainingCapacity} personne(s)."
            ];
        }

        return [
            'available' => true,
            'message' => 'Créneau disponible',
            'remaining_capacity' => $remainingCapacity
        ];
    }

    /**
     * Show user's reservations.
     */
    public function myReservations()
    {
        $reservations = Reservation::with(['space', 'invoice'])
            ->where('user_id', auth()->id())
            ->orderBy('start_datetime', 'desc')
            ->paginate(10);

        return view('reservations.index', compact('reservations'));
    }

    /**
     * Show a specific reservation.
     */
    public function showReservation(Reservation $reservation)
    {
        // Check authorization
        if ($reservation->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Non autorisé');
        }

        $reservation->load(['space', 'user', 'invoice']);

        return view('reservations.show', compact('reservation'));
    }

    /**
     * Cancel a reservation.
     */
    public function cancelReservation(Reservation $reservation)
    {
        // Check authorization
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'Non autorisé');
        }

        if ($reservation->status === 'CANCELLED') {
            return back()->with('error', 'Cette réservation est déjà annulée.');
        }

        // Update reservation
        $reservation->update(['status' => 'CANCELLED']);

        // Free up the period
        if ($reservation->zapAppointment) {
            $reservation->zapAppointment->update(['is_available' => true]);
        }

        return back()->with('success', 'Réservation annulée avec succès.');
    }
}
