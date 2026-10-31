<?php

namespace App\Filament\Resources\Loads\Actions;

use App\Models\LoadCoordination;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Icons\Heroicon;

class DeleteLoadCoordinationAction
{
    public static function make(): Action
    {
        return Action::make('deleteCoordination')
            ->label('Eliminar')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Eliminar coordinación marítima')
            ->modalDescription(
                '¿Estás seguro de que deseas eliminar esta coordinación? Esta acción no se puede deshacer.'
            )
            ->modalSubmitActionLabel('Sí, eliminar')
            ->action(function (array $arguments): void {
                $coordination = LoadCoordination::findOrFail(
                    $arguments['coordination']
                );

                $coordination->delete();
            });
    }
}
