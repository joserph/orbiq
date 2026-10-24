<?php

namespace App\Reports;

use App\Models\Client;
use App\Models\Flight;
use Barryvdh\DomPDF\Facade\Pdf;

class FlightSelectedClientsPdf
{
    public static function generate(
        Flight $flight,
        array $clientIds
    ) {
        $data = FlightCompleteReport::data($flight);

        // Conservar únicamente los clientes seleccionados.
        $selectedClients = Client::query()
            ->whereIn('id', $clientIds)
            ->pluck('name', 'id');

        $coordinations = $flight->coordinations()
            ->with(['client', 'farm'])
            ->whereIn('client_id', $selectedClients->keys())
            ->get();

        // Obtener los nombres de las variedades.
        $coordinations = $coordinations->map(function ($coordination) {
            $varietyNames = [];

            if (is_array($coordination->varieties)) {
                $varietyNames = \App\Models\FlowerVariety::query()
                    ->whereIn('id', $coordination->varieties)
                    ->orderBy('name')
                    ->pluck('name')
                    ->toArray();
            }

            $coordination->variety_names = implode(', ', $varietyNames);

            return $coordination;
        });

        // Agrupar las coordinaciones por cliente.
        $grouped = $coordinations
            ->sortBy(fn ($coordination) => $coordination->client?->name ?? '')
            ->groupBy(fn ($coordination) => $coordination->client?->name ?? 'SIN CLIENTE');

        // Calcular los totales únicamente de los clientes seleccionados.
        $activeBoxes = $data['activeBoxes'];

        $totals = [
            'coordinated' => [],
            'received' => [],
        ];

        foreach ($activeBoxes as $box) {
            $totals['coordinated'][$box] = $coordinations->sum(
                fn ($coordination) => (float) ($coordination->{$box} ?? 0)
            );

            $receivedField = $box . '_r';

            $totals['received'][$box] = $coordinations->sum(
                fn ($coordination) => (float) ($coordination->{$receivedField} ?? 0)
            );
        }

        $totals['coordinated']['fulls'] = $coordinations->sum(
            fn ($coordination) => (float) ($coordination->fulls ?? 0)
        );

        $totals['received']['fulls'] = $coordinations->sum(
            fn ($coordination) => (float) ($coordination->fulls_r ?? 0)
        );

        // dd($totals);
        $data['coordinations'] = $grouped;
        $data['totals'] = $totals;
        $data['selectedClients'] = $selectedClients->values();

        return Pdf::loadView(
            'reports.flights.complete',
            $data
        )->setPaper('a4', 'landscape');
    }

    public static function stream(
        Flight $flight,
        array $clientIds
    ) {
        return self::generate($flight, $clientIds)->stream(
            'flight-' . $flight->awb . '-selected-clients.pdf'
        );
    }

    public static function download(
        Flight $flight,
        array $clientIds
    ) {
        return self::generate($flight, $clientIds)->download(
            'flight-' . $flight->awb . '-selected-clients.pdf'
        );
    }
}