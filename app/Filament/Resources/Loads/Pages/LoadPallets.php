<?php

namespace App\Filament\Resources\Loads\Pages;

use App\Filament\Resources\Loads\Actions\CreatePalletDetailAction;
use App\Filament\Resources\Loads\Actions\DeletePalletAction;
use App\Filament\Resources\Loads\Actions\DeletePalletDetailAction;
use App\Filament\Resources\Loads\Actions\EditPalletDetailAction;
use App\Filament\Resources\Loads\LoadResource;
use App\Models\Load;
use App\Models\LoadCoordination;
use App\Models\Pallet;
use App\Models\PalletDetail;
use App\Services\CustomerLoadReport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class LoadPallets extends Page
{
    use InteractsWithRecord;

    protected static string $resource = LoadResource::class;

    protected string $view = 'filament.resources.loads.pages.load-pallets';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Paletas marítimas';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createPallet')
                ->label('Nueva Paleta')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->visible(fn (): bool =>
                    $this->getPalletizedBoxes() < $this->getCoordinatedBoxes()
                )
                ->action(function (): void {

                    DB::transaction(function (): void {

                        $load = Load::query()
                            ->lockForUpdate()
                            ->findOrFail($this->record->id);

                        $lastCounter = Pallet::query()
                            ->where('load_id', $load->id)
                            ->get()
                            ->max(fn ($pallet) => (int) $pallet->counter) ?? 0;

                        $counter = $lastCounter + 1;

                        Pallet::create([
                            'counter' => (string) $counter,
                            'number' => $load->shipment . '-' . $counter,
                            'quantity' => 0,
                            'usda' => false,
                            'load_id' => $load->id,
                        ]);

                    });

                    Notification::make()
                        ->success()
                        ->title('Paleta creada correctamente')
                        ->send();

                    $this->redirect(
                        LoadResource::getUrl('pallets', [
                            'record' => $this->record,
                        ])
                    );
                }),
            
            Action::make('exportCustomerReport')
                ->label('Exportar informe Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->visible(fn (): bool =>
                    $this->getCoordinatedBoxes() > 0
                    && $this->getPalletizedBoxes() === $this->getCoordinatedBoxes()
                )
                ->action(function (): mixed {
                    return app(CustomerLoadReport::class)
                        ->download($this->record);
                }),

        ];
    }

    public function getPallets()
    {
        return Pallet::query()
            ->where('load_id', $this->record->id)
            // ->orderByRaw('CAST(counter AS UNSIGNED)')
            ->orderByDesc('id')
            ->get();
    }


    public function getCoordinationOptions(): array
    {
        return LoadCoordination::query()
            ->with(['farm', 'client'])
            ->where('load_id', $this->record->id)
            ->orderBy('hawb')
            ->get()
            ->mapWithKeys(function ($coordination) {
                $label = $coordination->hawb
                    . ' | '
                    . ($coordination->farm?->name ?? 'Sin finca')
                    . ' | '
                    . ($coordination->client?->name ?? 'Sin cliente');

                return [$coordination->id => $label];
            })
            ->toArray();
    }

    public function getAvailableBoxes(int $coordinationId): array
    {
        $coordination = LoadCoordination::query()
            ->where('load_id', $this->record->id)
            ->findOrFail($coordinationId);

        $allocated = PalletDetail::query()
            ->where('load_coordination_id', $coordinationId)
            ->selectRaw('
                COALESCE(SUM(fb), 0) as fb,
                COALESCE(SUM(hb), 0) as hb,
                COALESCE(SUM(qb), 0) as qb,
                COALESCE(SUM(eb), 0) as eb,
                COALESCE(SUM(db), 0) as db
            ')
            ->first();

        return [
            'fb' => max(0, (int) $coordination->fb_r - (int) $allocated->fb),
            'hb' => max(0, (int) $coordination->hb_r - (int) $allocated->hb),
            'qb' => max(0, (int) $coordination->qb_r - (int) $allocated->qb),
            'eb' => max(0, (int) $coordination->eb_r - (int) $allocated->eb),
            'db' => max(0, (int) $coordination->db_r - (int) $allocated->db),
        ];
    }

    public function createPalletDetailAction(): Action
    {
        return CreatePalletDetailAction::make($this);
    }

    public function DeletePalletDetailAction(): Action
    {
        return DeletePalletDetailAction::make($this);
    }

    public function getPalletDetails(Pallet $pallet)
    {
        return PalletDetail::query()
            ->with(['farm', 'client', 'loadCoordination'])
            ->where('pallet_id', $pallet->id)
            ->get();
    }

    public function editPalletDetailAction(): Action
    {
        return EditPalletDetailAction::make($this);
    }

    public function deletePallet(int $palletId): void
    {
        DB::transaction(function () use ($palletId) {

            $pallet = Pallet::where('load_id', $this->record->id)
                ->lockForUpdate()
                ->findOrFail($palletId);

            // Solo permitir eliminar la última paleta
            $lastPalletId = Pallet::where('load_id', $this->record->id)
                ->max('id');

            if ($pallet->id != $lastPalletId) {
                Notification::make()
                    ->danger()
                    ->title('No se puede eliminar')
                    ->body('Solo puedes eliminar la última paleta agregada.')
                    ->persistent()
                    ->send();

                return;
            }

            // Eliminar los detalles y sus coordinaciones asignadas
            PalletDetail::where('pallet_id', $pallet->id)->delete();

            // Eliminar la paleta
            $pallet->delete();
        });

        $this->dispatch('$refresh');
    }

    public function deletePalletAction(): Action
    {
        return DeletePalletAction::make($this);
    }

    public function getPalletizedBoxes(): int
    {
        return (int) PalletDetail::query()
            ->whereHas('pallet', function ($query) {
                $query->where('load_id', $this->record->id);
            })
            ->sum('quantity');
    }

    public function getCoordinatedBoxes(): int
    {
        return (int) LoadCoordination::query()
            ->where('load_id', $this->record->id)
            ->sum('pieces');
    }

    public function getPalletizationPercentage(): float
    {
        $coordinated = $this->getCoordinatedBoxes();

        if ($coordinated <= 0) {
            return 0;
        }

        return min(
            100,
            ($this->getPalletizedBoxes() / $coordinated) * 100
        );
    }

    public function deletePalletDetail(int $detailId): void
    {
        $detail = PalletDetail::query()
            ->whereHas('pallet', function ($query) {
                $query->where('load_id', $this->record->id);
            })
            ->findOrFail($detailId);

        $detail->delete();

        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Coordinación eliminada')
            ->send();
    }

}
