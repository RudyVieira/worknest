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
        $isAdmin = $user->hasRole('administrator');
        $isSpaceOwner = $user->hasRole('space_owner');

        $stats = [];

        if ($isAdmin) {
            $totalSpaces = Space::count();
            $totalUsers = User::count();
            $totalReservations = Reservation::count();
            $totalRevenue = Reservation::whereIn('status', ['CONFIRMED', 'COMPLETED'])
                ->sum('total_price');

            $stats = [
                Stat::make('Total Espaces', $totalSpaces)
                    ->description('Nombre total d\'espaces')
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->color('success'),

                Stat::make('Total Utilisateurs', $totalUsers)
                    ->description('Nombre total d\'utilisateurs')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('info'),

                Stat::make('Total Réservations', $totalReservations)
                    ->description('Toutes les réservations')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('warning'),

                Stat::make('Revenus Total', number_format($totalRevenue, 2, ',', ' ') . ' €')
                    ->description('Revenus de toutes les réservations')
                    ->descriptionIcon('heroicon-m-currency-euro')
                    ->color('success'),
            ];
        } elseif ($isSpaceOwner) {
            $ownedSpaceIds = $user->ownedSpaces()->pluck('id');
            $totalSpaces = $user->ownedSpaces()->count();
            $totalReservations = Reservation::whereIn('space_id', $ownedSpaceIds)->count();
            $totalRevenue = Reservation::whereIn('space_id', $ownedSpaceIds)
                ->whereIn('status', ['CONFIRMED', 'COMPLETED'])
                ->sum('total_price');

            $stats = [
                Stat::make('Mes Espaces', $totalSpaces)
                    ->description('Nombre d\'espaces possédés')
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->color('success'),

                Stat::make('Réservations', $totalReservations)
                    ->description('Total de réservations')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('warning'),

                Stat::make('Revenus', number_format($totalRevenue, 2, ',', ' ') . ' €')
                    ->description('Revenus de vos espaces')
                    ->descriptionIcon('heroicon-m-currency-euro')
                    ->color('success'),
            ];
        }

        return $stats;
    }
}
