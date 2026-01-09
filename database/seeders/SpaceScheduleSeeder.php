<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;
use Zap\Enums\ScheduleTypes;
use Carbon\Carbon;

class SpaceScheduleSeeder extends Seeder
{
    /**
     * Seed space availability schedules.
     */
    public function run(): void
    {
        $spaces = Space::all();

        foreach ($spaces as $space) {
            // Create availability schedule for the next 3 months
            $schedule = $space->schedules()->create([
                'name' => 'Horaires d\'ouverture',
                'description' => 'Disponibilités régulières',
                'schedule_type' => ScheduleTypes::AVAILABILITY,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
                'is_active' => true,
            ]);

            // Create periods for the next 90 days
            $startDate = Carbon::today();
            $endDate = Carbon::today()->addDays(90);

            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Skip some days randomly to simulate closed days
                if (rand(1, 10) > 8) {
                    $currentDate->addDay();
                    continue;
                }

                // Generate time slots for each day
                $openingTime = rand(8, 9);
                $closingTime = rand(17, 19);

                for ($hour = $openingTime; $hour < $closingTime; $hour++) {
                    // Each slot is 2 hours
                    if ($hour + 2 <= $closingTime) {
                        $schedule->periods()->create([
                            'date' => $currentDate->toDateString(),
                            'start_time' => sprintf('%02d:00', $hour),
                            'end_time' => sprintf('%02d:00', $hour + 2),
                            'is_available' => true,
                        ]);
                    }
                }

                $currentDate->addDay();
            }

            $this->command->info("Schedule created for space: {$space->name}");
        }

        $this->command->info('All space schedules have been created successfully!');
    }
}
