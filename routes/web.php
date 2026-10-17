<?php

use App\Exports\FlightClientExport;
use App\Exports\FlightCompleteExport;
use App\Models\Company;
use App\Models\Flight;
use App\Reports\FlightClientPdf;
use App\Reports\FlightCompletePdf;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

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

