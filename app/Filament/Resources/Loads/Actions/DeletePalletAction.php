<?php

namespace App\Filament\Resources\Loads\Actions;

use Filament\Actions\Action;

class DeletePalletAction
{
    public static function make($page): Action
    {
        return Action::make('deletePallet')
            ->label('Eliminar paleta')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Eliminar paleta marítima')
            ->modalDescription(
                '¿Estás seguro de que deseas eliminar esta paleta y todas sus coordinaciones y cajas? Esta acción no se puede deshacer.'
            )
            ->modalSubmitActionLabel('Sí, eliminar')
            ->action(function (array $arguments) use ($page): void {
                $page->deletePallet($arguments['palletId']);
            });
    }
}