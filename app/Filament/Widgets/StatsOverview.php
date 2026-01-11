<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $isSpaceOwner = $user->hasRole('owner');

        $stats = [];

        if ($isAdmin) {
            $totalSpaces = Space::count();
            $totalUsers = User::count();
            $totalReservations = Reservation::count();
            $totalRevenue = Reservation::whereIn('status', ['CONFIRMED', 'COMPLETED'])
                ->sum('total_price');

            $stats = [
                Stat::make('Total Espaces', $totalSpaces),
                Stat::make('Total Utilisateurs', $totalUsers),
                Stat::make('Total Réservations', $totalReservations),
                Stat::make('Revenus Total', number_format($totalRevenue, 2, ',', ' ') . ' €'),
            ];
        } elseif ($isSpaceOwner) {
            $ownedSpaceIds = $user->ownedSpaces()->pluck('id');
            $totalSpaces = $user->ownedSpaces()->count();
            $totalReservations = Reservation::whereIn('space_id', $ownedSpaceIds)->count();
            $totalRevenue = Reservation::whereIn('space_id', $ownedSpaceIds)
                ->whereIn('status', ['CONFIRMED', 'COMPLETED'])
                ->sum('total_price');

            $stats = [
                Stat::make('Mes Espaces', $totalSpaces),
                Stat::make('Réservations au sein de vos espaces', $totalReservations),
                Stat::make('Revenus de vos espaces', number_format($totalRevenue, 2, ',', ' ') . ' €'),
            ];
        }

        return $stats;
    }
}
