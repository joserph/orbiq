<?php

namespace App\Reports;

use App\Models\Flight;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;

class FlightClientPdf
{
    public static function generate(Flight $flight, Client $client)
    {
        $data = FlightCompleteReport::data($flight);
        // dd($data);
        // Obtener únicamente las coordinaciones del cliente.
        $coordinations = $flight->coordinations()
            ->with(['client', 'farm'])
            ->where('client_id', $client->id)
            ->get();

        // Preparar los nombres de las variedades.
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

        $activeBoxes = $data['activeBoxes'];

        // Calcular los totales solo con las coordinaciones del cliente.
        $totals = [
            'coordinated' => [],
            'received' => [],
        ];

        foreach ($activeBoxes as $box) {
            $totals['coordinated'][$box] = $coordinations->sum(
                fn ($item) => (float) ($item->{$box} ?? 0)
            );

            $receivedField = $box . '_r';

            $totals['received'][$box] = $coordinations->sum(
                fn ($item) => (float) ($item->{$receivedField} ?? 0)
            );
        }

        $totals['coordinated']['pieces'] = $coordinations->sum(
            fn ($item) => (float) ($item->pieces ?? 0)
        );
        $totals['coordinated']['fulls'] = $coordinations->sum(
            fn ($item) => (float) ($item->fulls ?? 0)
        );

        $totals['received']['pieces'] = $coordinations->sum(
            fn ($item) => (float) ($item->pieces_r ?? 0)
        );
        $totals['received']['fulls'] = $coordinations->sum(
            fn ($item) => (float) ($item->fulls_r ?? 0)
        );
        $totals['returns'] = $coordinations->sum(
            fn ($item) => (float) ($item->returns ?? 0)
        );
        $totals['missing'] = $coordinations->sum(
            fn ($item) => (float) ($item->missing ?? 0)
        );
        // dd($totals);
        // La plantilla recorre las coordinaciones agrupadas.
        $data['coordinations'] = $coordinations->groupBy(
            fn ($item) => $item->client?->name ?? $client->name
        );

        $data['totals'] = $totals;
        $data['client'] = $client;

        return Pdf::loadView('reports.flights.client', $data)
            ->setPaper('a4', 'landscape');
    }

    public static function download(Flight $flight, Client $client)
    {
        return self::generate($flight, $client)->download(
            'flight-' . $flight->awb . '-client-' . $client->id . '.pdf'
        );
    }

    public static function stream(Flight $flight, Client $client)
    {
        return self::generate($flight, $client)->stream(
            'flight-' . $flight->awb . '-client-' . $client->id . '.pdf'
        );
    }
}
