<?php

namespace App\Filament\Resources\Loads\Actions;

use Filament\Actions\Action;

class DeletePalletDetailAction
{
    public static function make($page): Action
    {
        return Action::make('deletePalletDetail')
            ->label('Eliminar coordinación')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Eliminar Item')
            ->modalDescription(
                '¿Estás seguro de que deseas eliminar este item de la paleta? Esta acción no se puede deshacer.'
            )
            ->modalSubmitActionLabel('Sí, eliminar')
            ->action(function (array $arguments) use ($page): void {
                $page->deletePalletDetail($arguments['detailId']);
            });
    }
}
