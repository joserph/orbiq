<?php

namespace App\Filament\Resources\Flights\Actions;

use App\Models\FlightCoordination;
use App\Filament\Resources\Flights\Pages\FlightCoordinations;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class DeleteFlightCoordinationAction
{
    public static function make(FlightCoordinations $page): Action
    {
        return Action::make('deleteCoordination')
            ->label('Eliminar')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Eliminar coordinación')
            ->modalDescription(
                '¿Estás seguro de que deseas eliminar esta coordinación? Esta acción no se puede deshacer.'
            )
            ->modalSubmitActionLabel('Sí, eliminar')
            ->modalCancelActionLabel('Cancelar')

            ->action(function (array $arguments): void {

                $coordination = FlightCoordination::findOrFail(
                    $arguments['coordination']
                );

                $coordination->delete();

                Notification::make()
                    ->title('Coordinación eliminada')
                    ->success()
                    ->send();
            });
    }
}