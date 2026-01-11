<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Space;
use App\Models\EquipmentType;
use App\Models\Reservation;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Zap\Enums\ScheduleTypes;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting database seeding...');
        $this->command->info('');

        // ========================================
        // PERMISSIONS & ROLES
        // ========================================
        $this->command->info('📋 Creating permissions...');

        $permissions = [
            // Space permissions
            'view_spaces',
            'create_spaces',
            'edit_spaces',
            'delete_spaces',

            // User permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_user_roles',
            'assign_space_owner',

            // Equipment permissions
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
            'delete_equipment_types',

            // Space equipment permissions
            'manage_space_equipment',

            // Schedule permissions
            'view_schedules',
            'create_schedules',
            'edit_schedules',
            'delete_schedules',

            // Reservation permissions
            'view_all_reservations',
            'view_own_space_reservations',

            // Invoice permissions
            'view_all_invoices',
            'view_own_space_invoices',

            // Statistics permissions
            'view_all_statistics',
            'view_own_space_statistics',

            // Legacy permissions
            'manage users',
            'manage spaces',
            'manage reservations',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Admin role
        $this->command->info('👑 Creating admin role...');
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'view_spaces',
            'create_spaces',
            'edit_spaces',
            'delete_spaces',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_user_roles',
            'assign_space_owner',
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
            'delete_equipment_types',
            'manage_space_equipment',
            'view_schedules',
            'create_schedules',
            'edit_schedules',
            'delete_schedules',
            'view_all_reservations',
            'view_all_invoices',
            'view_all_statistics',
            'manage users',
            'manage spaces',
            'manage reservations',
            'view reports',
        ]);

        // Create Space Owner role
        $this->command->info('👤 Creating owner role...');
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $ownerRole->syncPermissions([
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
            'delete_equipment_types',
            'manage_space_equipment',
            'view_schedules',
            'create_schedules',
            'edit_schedules',
            'delete_schedules',
            'view_own_space_reservations',
            'view_own_space_invoices',
            'view_own_space_statistics',
            'manage spaces',
            'view reports',
        ]);

        // Create User role
        $this->command->info('👥 Creating user role...');
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================================
        // USERS
        // ========================================
        $this->command->info('');
        $this->command->info('👤 Creating users...');

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@worknest.com'],
            [
                'firstname' => 'Admin',
                'lastname' => 'WorkNest',
                'password' => bcrypt('password'),
                'status' => 'ACTIVE',
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create test user
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'firstname' => 'Test',
                'lastname' => 'User',
                'password' => bcrypt('password'),
                'status' => 'ACTIVE',
            ]
        );
        if (!$testUser->hasRole('user')) {
            $testUser->assignRole('user');
        }

        // Create owner user
        $owner = User::firstOrCreate(
            ['email' => 'owner@worknest.com'],
            [
                'firstname' => 'Propriétaire',
                'lastname' => 'WorkNest',
                'password' => bcrypt('password'),
                'status' => 'ACTIVE',
            ]
        );
        if (!$owner->hasRole('owner')) {
            $owner->assignRole('owner');
        }

        // Create additional test users
        $users = User::factory(10)->create();
        foreach ($users as $user) {
            $user->assignRole('user');
        }

        // ========================================
        // EQUIPMENT TYPES
        // ========================================
        $this->command->info('');
        $this->command->info('🔧 Creating equipment types...');
        $equipmentTypes = EquipmentType::factory(15)->create();

        // ========================================
        // WORKNEST SPACES (REAL DATA)
        // ========================================
        $this->command->info('');
        $this->command->info('🏢 Creating WorkNest spaces with schedules...');

        $this->seedWorkNestSpaces($owner);

        // Get all created spaces
        $spaces = Space::all();

        // ========================================
        // EQUIPMENT ASSIGNMENT
        // ========================================
        $this->command->info('');
        $this->command->info('🔗 Attaching equipment to spaces...');

        // Attach random equipment to each space
        foreach ($spaces as $space) {
            $randomEquipment = $equipmentTypes->random(rand(3, 8));
            foreach ($randomEquipment as $equipment) {
                $space->equipmentTypes()->attach($equipment->id, [
                    'quantity' => rand(1, 10)
                ]);
            }
        }

        // ========================================
        // RESERVATIONS
        // ========================================
        $this->command->info('');
        $this->command->info('📅 Creating sample reservations...');

        // Create reservations for test user and other users
        $allUsers = User::all();
        foreach ($allUsers->random(min(8, $allUsers->count())) as $user) {
            Reservation::factory(rand(2, 5))->create([
                'user_id' => $user->id,
                'space_id' => $spaces->random()->id,
            ]);
        }

        // ========================================
        // INVOICES
        // ========================================
        $this->command->info('');
        $this->command->info('💰 Creating invoices for paid reservations...');

        // Create invoices for paid reservations
        $paidReservations = Reservation::where('status', 'PAID')->get();
        foreach ($paidReservations as $reservation) {
            Invoice::factory()->create([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->id,
                'amount' => $reservation->total_price,
            ]);
        }

        // ========================================
        // SUMMARY
        // ========================================
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('✅ DATABASE SEEDING COMPLETED SUCCESSFULLY!');
        $this->command->info('===========================================');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('  • Users: ' . User::count());
        $this->command->info('  • Spaces: ' . Space::count());
        $this->command->info('  • Equipment Types: ' . EquipmentType::count());
        $this->command->info('  • Reservations: ' . Reservation::count());
        $this->command->info('  • Invoices: ' . Invoice::count());
        $this->command->info('  • Schedules: ' . \Zap\Models\Schedule::count());
        $this->command->info('  • Schedule Periods: ' . \Zap\Models\SchedulePeriod::count());
        $this->command->info('');
        $this->command->info('🔑 Test Accounts:');
        $this->command->info('  • Admin: admin@worknest.com / password');
        $this->command->info('  • Owner: owner@worknest.com / password');
        $this->command->info('  • User: test@example.com / password');
    }

    /**
     * Seed WorkNest spaces with real data and schedules.
     */
    private function seedWorkNestSpaces(User $owner): void
    {
        $spacesData = [
            [
                "name" => "WorkNest Turbigo",
                "address" => "10 Rue de Turbigo, 75001 Paris",
                "latitude" => 48.8634,
                "longitude" => 2.3466,
                "capacity" => 45,
                "price_per_hour" => 18,
                "image" => "spaces/photo-1497366754035-f200968a6e72.jpg",
                "description" => "Situé au cœur du quartier historique, WorkNest Turbigo offre un espace lumineux mêlant design contemporain et touches industrielles. Lieu idéal pour freelances et petites équipes, il propose des bureaux flexibles et des salles de réunion connectées. L'ambiance y est calme mais stimulante, parfaite pour la concentration et la collaboration."
            ],
            [
                "name" => "WorkNest Montorgueil",
                "address" => "27 Rue Montorgueil, 75001 Paris",
                "latitude" => 48.8640,
                "longitude" => 2.3470,
                "capacity" => 35,
                "price_per_hour" => 17,
                "image" => "spaces/photo-1497366811353-6870744d04b2.jpg",
                "description" => "Niché dans une rue animée et commerçante, cet espace WorkNest favorise la créativité et les échanges. Les postes ergonomiques côtoient un coin café chaleureux. Idéal pour indépendants et startups en phase de lancement."
            ],
            [
                "name" => "WorkNest Oberkampf",
                "address" => "15 Rue Oberkampf, 75011 Paris",
                "latitude" => 48.8643,
                "longitude" => 2.3730,
                "capacity" => 50,
                "price_per_hour" => 16,
                "image" => "spaces/photo-1527192491265-7e15c55b1ed2.jpg",
                "description" => "Pensé pour les esprits créatifs, WorkNest Oberkampf propose de larges open-spaces et des zones de brainstorming. Sa décoration moderne inspirée du street art stimule l'innovation et la collaboration."
            ],
            [
                "name" => "WorkNest Roquette",
                "address" => "42 Rue de la Roquette, 75011 Paris",
                "latitude" => 48.8538,
                "longitude" => 2.3725,
                "capacity" => 40,
                "price_per_hour" => 15,
                "image" => "spaces/photo-1553028826-f4804a6dba3b.jpg",
                "description" => "WorkNest Roquette se distingue par une ambiance calme et professionnelle. Il offre des salles phoniquement isolées et une connexion haut débit, idéales pour le travail approfondi et les réunions stratégiques."
            ],
            [
                "name" => "WorkNest Rennes",
                "address" => "33 Rue de Rennes, 75006 Paris",
                "latitude" => 48.8488,
                "longitude" => 2.3331,
                "capacity" => 55,
                "price_per_hour" => 19,
                "image" => "spaces/photo-1556586038-29f26c3ceb20.jpg",
                "description" => "Situé sur la rive gauche, WorkNest Rennes séduit par son élégance et son confort haut de gamme. Les espaces lumineux sont adaptés aux équipes établies recherchant un cadre professionnel raffiné."
            ],
            [
                "name" => "WorkNest Croix-Rousse",
                "address" => "8 Rue Ozanam, 69001 Lyon",
                "latitude" => 45.7735,
                "longitude" => 4.8279,
                "capacity" => 30,
                "price_per_hour" => 14,
                "image" => "spaces/photo-1556761175-4b46a572b786.jpg",
                "description" => "Installé dans un quartier emblématique, WorkNest Croix-Rousse propose une atmosphère chaleureuse et authentique. Les espaces favorisent les échanges entre entrepreneurs locaux."
            ],
            [
                "name" => "WorkNest Chartreux",
                "address" => "12 Rue des Chartreux, 69001 Lyon",
                "latitude" => 45.7720,
                "longitude" => 4.8235,
                "capacity" => 25,
                "price_per_hour" => 13,
                "image" => "spaces/photo-1559310278-18a9192d909f.jpg",
                "description" => "Espace intimiste idéal pour freelances et petites équipes. WorkNest Chartreux offre un cadre paisible et studieux, propice à la concentration et à la créativité individuelle."
            ],
            [
                "name" => "WorkNest Audran",
                "address" => "20 Rue Audran, 69001 Lyon",
                "latitude" => 45.7740,
                "longitude" => 4.8351,
                "capacity" => 35,
                "price_per_hour" => 14,
                "image" => "spaces/photo-1562664348-2188b99b5157.jpg",
                "description" => "Moderne et fonctionnel, WorkNest Audran allie flexibilité et convivialité. Il dispose de zones de travail modulables et d'un espace détente."
            ],
            [
                "name" => "WorkNest Saint-Ferréol",
                "address" => "18 Rue Saint-Ferréol, 13001 Marseille",
                "latitude" => 43.2960,
                "longitude" => 5.3800,
                "capacity" => 45,
                "price_per_hour" => 15,
                "image" => "spaces/photo-1562664348-cb222fdcfa86.jpg",
                "description" => "Au cœur du centre-ville, WorkNest Saint-Ferréol propose un espace dynamique et lumineux. Il combine accessibilité, équipements modernes et énergie marseillaise."
            ],
            [
                "name" => "WorkNest République",
                "address" => "55 Rue de la République, 13002 Marseille",
                "latitude" => 43.2990,
                "longitude" => 5.3805,
                "capacity" => 60,
                "price_per_hour" => 16,
                "image" => "spaces/photo-1562664377-709f2c337eb2.jpg",
                "description" => "Grand espace dédié aux startups et entreprises en croissance. WorkNest République favorise la collaboration grâce à ses volumes généreux et ses salles de réunion."
            ],
            [
                "name" => "WorkNest Paradis",
                "address" => "23 Rue Paradis, 13006 Marseille",
                "latitude" => 43.2955,
                "longitude" => 5.3880,
                "capacity" => 40,
                "price_per_hour" => 17,
                "image" => "spaces/photo-1571624436279-b272aff752b5.jpg",
                "description" => "Ambiance élégante et calme pour les professions créatives et consultants. WorkNest Paradis offre un cadre soigné et confortable."
            ],
            [
                "name" => "WorkNest Metz",
                "address" => "6 Rue de Metz, 31000 Toulouse",
                "latitude" => 43.6022,
                "longitude" => 1.4440,
                "capacity" => 35,
                "price_per_hour" => 14,
                "image" => "spaces/photo-1575886876763-10ea19d18fac.jpg",
                "description" => "Espace polyvalent et moderne, idéal pour réunions et travail quotidien. WorkNest Metz mise sur fonctionnalité et confort."
            ],
            [
                "name" => "WorkNest Alsace Centre",
                "address" => "31 Rue Alsace Lorraine, 31000 Toulouse",
                "latitude" => 43.6040,
                "longitude" => 1.4446,
                "capacity" => 50,
                "price_per_hour" => 15,
                "image" => "spaces/photo-1578991624414-276ef23a534f.jpg",
                "description" => "Spacieux et lumineux, WorkNest Alsace Centre est conçu pour les équipes collaboratives avec des équipements technologiques avancés."
            ],
            [
                "name" => "WorkNest Alsace Premium",
                "address" => "44 Rue d'Alsace Lorraine, 31000 Toulouse",
                "latitude" => 43.6042,
                "longitude" => 1.4450,
                "capacity" => 45,
                "price_per_hour" => 16,
                "image" => "spaces/photo-1582005450386-52b25f82d9bb.jpg",
                "description" => "Version premium offrant confort renforcé et services additionnels. Idéal pour un cadre professionnel haut de gamme."
            ],
            [
                "name" => "WorkNest Jean Médecin",
                "address" => "12 Avenue Jean Médecin, 06000 Nice",
                "latitude" => 43.6960,
                "longitude" => 7.2660,
                "capacity" => 55,
                "price_per_hour" => 17,
                "image" => "spaces/photo-1596749853719-e6aa1dc2eabe.jpg",
                "description" => "Espace central, lumineux et moderne, parfaitement adapté aux équipes actives recherchant dynamisme et accessibilité."
            ],
            [
                "name" => "WorkNest France",
                "address" => "38 Rue de France, 06000 Nice",
                "latitude" => 43.6926,
                "longitude" => 7.2680,
                "capacity" => 40,
                "price_per_hour" => 16,
                "image" => "spaces/photo-1600508772927-723e3ba305c5.jpg",
                "description" => "Proche du littoral, WorkNest France combine efficacité professionnelle et atmosphère détendue."
            ],
            [
                "name" => "WorkNest Intendance",
                "address" => "5 Cours de l'Intendance, 33000 Bordeaux",
                "latitude" => 44.8378,
                "longitude" => -0.5800,
                "capacity" => 50,
                "price_per_hour" => 16,
                "image" => "spaces/photo-1600508774634-4e11d34730e2.jpg",
                "description" => "Situé dans une zone prestigieuse, WorkNest Intendance propose un espace élégant pour réunions clients et travail collaboratif."
            ],
            [
                "name" => "WorkNest Sainte-Catherine",
                "address" => "47 Rue Sainte-Catherine, 33000 Bordeaux",
                "latitude" => 44.8400,
                "longitude" => -0.5750,
                "capacity" => 45,
                "price_per_hour" => 15,
                "image" => "spaces/photo-1604328699206-5f24c5ed8dd4.jpg",
                "description" => "Au cœur de l'animation bordelaise, cet espace favorise créativité et échanges dans un cadre confortable."
            ],
            [
                "name" => "WorkNest Béthune",
                "address" => "8 Rue de Béthune, 59800 Lille",
                "latitude" => 50.6360,
                "longitude" => 3.0620,
                "capacity" => 40,
                "price_per_hour" => 15,
                "image" => "spaces/photo-1604328703693-18313fe20f3a.jpg",
                "description" => "Espace convivial et moderne, pensé pour le travail collaboratif au centre de Lille."
            ],
            [
                "name" => "WorkNest Monnaie",
                "address" => "17 Rue de la Monnaie, 59800 Lille",
                "latitude" => 50.6365,
                "longitude" => 3.0628,
                "capacity" => 35,
                "price_per_hour" => 14,
                "image" => "spaces/photo-1604328727766-a151d1045ab4.jpg",
                "description" => "Lieu plus intimiste favorisant concentration et échanges de proximité pour freelances et petites équipes."
            ],
            [
                "name" => "WorkNest Crébillon",
                "address" => "22 Rue Crébillon, 44000 Nantes",
                "latitude" => 47.2180,
                "longitude" => -1.5520,
                "capacity" => 45,
                "price_per_hour" => 15,
                "image" => "spaces/photo-1606836576983-8b458e75221d.jpg",
                "description" => "Environnement moderne et lumineux conçu pour encourager collaboration et diversité professionnelle."
            ],
            [
                "name" => "WorkNest Égalité",
                "address" => "14 Boulevard de l'Égalité, 44000 Nantes",
                "latitude" => 47.2165,
                "longitude" => -1.5545,
                "capacity" => 40,
                "price_per_hour" => 14,
                "image" => "spaces/photo-1606836591695-4d58a73eba1e.jpg",
                "description" => "Cadre serein et fonctionnel, idéal pour le travail quotidien et les réunions professionnelles."
            ],
            [
                "name" => "WorkNest Arcades",
                "address" => "9 Rue des Grandes Arcades, 67000 Strasbourg",
                "latitude" => 48.5840,
                "longitude" => 7.7510,
                "capacity" => 45,
                "price_per_hour" => 16,
                "image" => "spaces/photo-1653463174213-55e0aabce355.jpg",
                "description" => "En plein centre historique, WorkNest Arcades allie charme local et modernité dans un cadre inspirant."
            ],
            [
                "name" => "WorkNest Faubourg",
                "address" => "27 Rue du Faubourg National, 67000 Strasbourg",
                "latitude" => 48.5770,
                "longitude" => 7.7570,
                "capacity" => 50,
                "price_per_hour" => 15,
                "image" => "spaces/photo-1674916975496-d627aa9bfb87.jpg",
                "description" => "Espace spacieux adapté aux équipes de taille moyenne, favorisant collaboration et efficacité."
            ],
            [
                "name" => "WorkNest Mésange",
                "address" => "18 Rue de la Mésange, 67000 Strasbourg",
                "latitude" => 48.5830,
                "longitude" => 7.7525,
                "capacity" => 35,
                "price_per_hour" => 16,
                "image" => "spaces/photo-1684769161054-2fa9a998dcb6.jpg",
                "description" => "Ambiance calme et élégante, idéale pour consultants et créatifs recherchant productivité et sérénité."
            ],
        ];

        foreach ($spacesData as $spaceData) {
            // Create the space
            $space = Space::create([
                'name' => $spaceData['name'],
                'description' => $spaceData['description'],
                'address' => $spaceData['address'],
                'latitude' => $spaceData['latitude'],
                'longitude' => $spaceData['longitude'],
                'capacity' => $spaceData['capacity'],
                'price_per_hour' => $spaceData['price_per_hour'],
                'image' => $spaceData['image'],
                'owner_id' => $owner->id,
                'status' => 'AVAILABLE',
            ]);

            // Create availability schedule for the next 6 months
            $schedule = $space->schedules()->create([
                'name' => 'Horaires d\'ouverture',
                'description' => 'Disponibilités régulières du lundi au vendredi',
                'schedule_type' => ScheduleTypes::AVAILABILITY,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'is_active' => true,
            ]);

            // Create periods for the next 180 days (Monday to Friday, 8h-20h)
            $startDate = Carbon::today();
            $endDate = Carbon::today()->addDays(180);
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // Only create schedules for weekdays (Monday to Friday)
                if ($currentDate->isWeekday()) {
                    // Generate time slots from 8:00 to 20:00 (every 2 hours)
                    $timeSlots = [
                        ['08:00', '10:00'],
                        ['10:00', '12:00'],
                        ['12:00', '14:00'],
                        ['14:00', '16:00'],
                        ['16:00', '18:00'],
                        ['18:00', '20:00'],
                    ];

                    foreach ($timeSlots as $slot) {
                        $schedule->periods()->create([
                            'date' => $currentDate->toDateString(),
                            'start_time' => $slot[0],
                            'end_time' => $slot[1],
                            'is_available' => true,
                        ]);
                    }
                }

                $currentDate->addDay();
            }

            $this->command->info("  ✓ {$space->name}");
        }

        $this->command->info('');
        $this->command->info('✓ All 25 WorkNest spaces created with schedules!');
    }
}
