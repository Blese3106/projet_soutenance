<?php

namespace App\Filament\Resources\NoneResource\Widgets;

use App\Models\Offer;
use App\Models\User;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class Dashboard extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Card::make('Utilisateurs', User::where('role', '!=', 'admin')->count())
            /* ->description('Nombre utilisateurs au total')
                ->descriptionIcon('heroicon-o-users', IconPosition::Before)
                ->color('primary')
                ->extraAttributes(['class' => 'bg-blue-600 text-white']) */,

            Card::make(
                'Utilisateurs récents',
                User::where('created_at', '>=', now()->subDays(3))
                    ->where('role', '!=', 'admin')
                    ->count()
            )
            /* ->description('Les utilisateurs récent de 3jrs')
                ->descriptionIcon('heroicon-o-users', IconPosition::Before)
                ->color('primary') */,

            Card::make('Mentorés', User::where('role', '=', 'mentee')->count()),

            Card::make('Mentor', User::where('role', '=', 'mentor')->count()),

            Card::make('Coach', User::where('role', '=', 'coach')->count()),

            Card::make('Offres', Offer::count()),
        ];
    }
}
