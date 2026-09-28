<?php

namespace App\Filament\Resources\FlightCoordinations\Pages;

use App\Filament\Resources\FlightCoordinations\FlightCoordinationResource;
use App\Models\Flight;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFlightCoordinations extends ListRecords
{
    protected static string $resource = FlightCoordinationResource::class;

    public Flight $flight;

    public function mount(): void
    {
        parent::mount();

        $flightId = request()->query('flight');

        if ($flightId) {
            session()->put('flight_coordination_flight_id', $flightId);
        } else {
            $flightId = session()->get('flight_coordination_flight_id');
        }

        $this->flight = Flight::findOrFail($flightId);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ?->where('flight_id', $this->flight->id);
    }
}
