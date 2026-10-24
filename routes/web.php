<?php

use App\Exports\FlightClientExport;
use App\Exports\FlightCompleteExport;
use App\Exports\FlightSelectedClientsExport;
use App\Models\Company;
use App\Models\Flight;
use App\Reports\FlightClientPdf;
use App\Reports\FlightCompletePdf;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\FlightSelectedClientsReportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reports/flights/{flight}/complete', function (\App\Models\Flight $flight) {
    return \App\Reports\FlightCompletePdf::stream($flight);
})->name('reports.flight.complete');


Route::get('/reports/flights/{flight}/complete-excel', function (Flight $flight) {

    return (new \App\Exports\FlightCompleteExport($flight))->download();

})->name('reports.flight.complete.excel');


Route::get('/reports/flights/{flight}/clients/{client}/pdf', function (
    \App\Models\Flight $flight,
    \App\Models\Client $client
) {
    abort_unless(
        $flight->coordinations()
            ->where('client_id', $client->id)
            ->exists(),
        404
    );

    return FlightClientPdf::stream($flight, $client);
})->name('reports.flight.client.pdf');


Route::get('/reports/flights/{flight}/clients/{client}/excel', function (
    \App\Models\Flight $flight,
    \App\Models\Client $client
) {
    abort_unless(
        $flight->coordinations()
            ->where('client_id', $client->id)
            ->exists(),
        404
    );

    return (new FlightClientExport($flight, $client))->download();

})->name('reports.flight.client.excel');

Route::get('/reports/flights/{flight}/selected-clients/pdf', function (
    Flight $flight,
    \Illuminate\Http\Request $request
) {
    $clientIds = $request->query('client_ids', []);

    abort_unless(is_array($clientIds) && count($clientIds) > 0, 422);

    // Permitir únicamente clientes que tengan coordinaciones en este vuelo.
    $validClientIds = $flight->coordinations()
        ->whereIn('client_id', $clientIds)
        ->distinct()
        ->pluck('client_id')
        ->map(fn ($id) => (int) $id)
        ->all();

    abort_unless(count($validClientIds) === count(array_unique(array_map('intval', $clientIds))), 404);

    return \App\Reports\FlightSelectedClientsPdf::stream(
        $flight,
        $validClientIds
    );
})->name('reports.flight.selected-clients.pdf');

Route::get('/reports/flights/{flight}/selected-clients/excel', function (
    Flight $flight,
    \Illuminate\Http\Request $request
) {
    $clientIds = $request->query('client_ids', []);

    abort_unless(is_array($clientIds) && count($clientIds) > 0, 422);

    // Validar que los clientes tengan coordinaciones en este vuelo.
    $validClientIds = $flight->coordinations()
        ->whereIn('client_id', $clientIds)
        ->distinct()
        ->pluck('client_id')
        ->map(fn ($id) => (int) $id)
        ->all();

    abort_unless(
        count($validClientIds) === count(array_unique(array_map('intval', $clientIds))),
        404
    );

    return (new FlightSelectedClientsExport($flight, $validClientIds))->download();

    // return \Maatwebsite\Excel\Facades\Excel::download(
    //     new \App\Exports\FlightSelectedClientsExport($flight, $validClientIds),
    //     'flight-' . $flight->awb . '-selected-clients.xlsx'
    // );
})->name('reports.flight.selected-clients.excel');
