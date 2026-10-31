
@php
    use Carbon\Carbon;

    $departure = $getRecord()->date
        ? Carbon::parse($getRecord()->date)->startOfDay()
        : null;

    $arrival = $getRecord()->arrival_date
        ? Carbon::parse($getRecord()->arrival_date)->startOfDay()
        : null;

    $today = Carbon::today();

    $label = 'Fechas pendientes';
    $detail = 'Sin fechas disponibles';
    $percentage = 0;
    $color = '#9CA3AF';

    if ($departure && $arrival && $arrival->gte($departure)) {
        $totalDays = max(1, $departure->diffInDays($arrival));

        if ($today->lt($departure)) {
            $daysToDeparture = (int) $today->diffInDays($departure, false);
            $daysToArrival = (int) $today->diffInDays($arrival, false);

            $label = 'Próximo a salir';
            $detail = $daysToDeparture === 0
                ? "Sale hoy · Llegada en {$daysToArrival} días"
                : "Sale en {$daysToDeparture} días";
            $color = '#9CA3AF';
        } elseif ($today->lt($arrival)) {
            $elapsed = $departure->diffInDays($today);
            $percentage = min(99, (int) floor(($elapsed / $totalDays) * 100));
            $daysLeft = (int) $today->diffInDays($arrival, false);

            $label = 'En tránsito';
            $detail = "{$daysLeft} días restantes";
            $color = '#3B82F6';
        } elseif ($today->equalTo($arrival)) {
            $percentage = 100;
            $label = 'Llegada prevista hoy';
            $detail = '0 días restantes';
            $color = '#22C55E';
        } else {
            $percentage = 100;
            $lateDays = (int) $arrival->diffInDays($today);

            $label = 'Fecha prevista vencida';
            $detail = "{$lateDays} días de retraso";
            $color = '#F59E0B';
        }
    }
@endphp

<div class="w-44 space-y-1.5 py-1">
    <div class="text-sm font-medium text-gray-800 dark:text-gray-100">
        {{ $label }}
    </div>

    {{-- <div class="h-3 w-full overflow-hidden rounded-md bg-gray-200 dark:bg-gray-700">
        <div
            class="h-full rounded-md transition-all"
            style="width: {{ $percentage }}%; background-color: {{ $color }};"
        ></div>
    </div> --}}
    <div style="width: 180px; height: 12px; background: #E5E7EB; border-radius: 6px; overflow: hidden;">
        <div
            style="width: {{ $percentage }}%; height: 12px; background-color: {{ $color }}; border-radius: 6px;"
        ></div>
    </div>

    <div class="text-xs text-gray-600 dark:text-gray-300">
        {{ $detail }}
    </div>

    {{-- @if ($arrival)
        <div class="text-xs text-gray-500 dark:text-gray-400">
            Llegada: {{ $arrival->format('d/m/Y') }}
        </div>
    @endif --}}
</div>
