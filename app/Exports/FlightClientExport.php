<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\Flight;
use App\Models\Company;
use App\Models\FlowerVariety;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FlightClientExport
{
    public function __construct(
        protected Flight $flight,
        protected Client $client,
    ) {}

    
    public function download()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Client');

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(2);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(18);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(43);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(16);

        // Obtener exclusivamente las coordinaciones
        // del vuelo y del cliente seleccionados.
        $coordinations = $this->flight->coordinations()
            ->with(['client', 'farm'])
            ->where('client_id', $this->client->id)
            ->get();
        // dd($this->client->name);
        // Obtener los tipos de caja activos del vuelo.
        $activeBoxes = collect([
            'fb' => $this->flight->fb_status,
            'hb' => $this->flight->hb_status,
            'qb' => $this->flight->qb_status,
            'eb' => $this->flight->eb_status,
            'db' => $this->flight->db_status,
        ])
            ->filter()
            ->keys()
            ->values()
            ->all();

        // Preparar los datos del reporte.
        $company = Company::first();

        /*
        |--------------------------------------------------------------------------
        | SPACE WHITE
        |--------------------------------------------------------------------------
        */
        $groups = 1; // EQUIVALENTE A LA CANTIDAD DE CLIENTES
        $sub_space = $groups * 2;
        $total_line = 12;
        $line_final = count($coordinations) + $groups + $sub_space + $total_line + 3;
        // dd($line_final);
        // dd($line_final);
        $letra = 'A';
        $column_final = chr(ord($letra) + 11 + (count($activeBoxes) * 2));
        // dd($column_final);
        for($letra; $letra <= "{$column_final}"; $letra++)
        {
            $spreadsheet->getActiveSheet()->getStyle($letra . '1:' . $letra . $line_final)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFFFF');
        }

        /*
        |--------------------------------------------------------------------------
        | GENERAL STYLES
        |--------------------------------------------------------------------------
        */

        $border = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'B7C7D8'],
                ],
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        if ($company?->logo) {

            $logoPath = public_path('storage/' . $company->logo);

            if (file_exists($logoPath)) {

                $drawing = new Drawing();

                $drawing->setName($company->name ?? 'Company Logo');
                $drawing->setDescription('Company Logo');
                $drawing->setPath($logoPath);

                // Posición del logo
                $drawing->setCoordinates('C2');

                // Tamaño
                $drawing->setHeight(155);

                // Ajuste de posición
                $drawing->setOffsetX(1);
                $drawing->setRotation(0);

                $drawing->setWorksheet($sheet);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('D2:H4');

        $sheet->setCellValue(
            'D2',
            'COORDINACIONES AÉREAS'
        );

        $sheet->getStyle('B2:P2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 22,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ])->getAlignment()->setWrapText(true);

        $sheet->mergeCells('D5:H6');

        $sheet->setCellValue(
            'D5',
            'CONTROL Y SEGUIMIENTO DE CARGA AÉREA'
        );

        $sheet->getStyle('D5:H6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(2)->setRowHeight(15);

        /*
        |--------------------------------------------------------------------------
        | FLIGHT INFORMATION
        |--------------------------------------------------------------------------
        */
        $infoStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '24435F'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];

        // AWB
        $sheet->mergeCells('I3:J3');
        $sheet->mergeCells('I4:J4');

        $sheet->setCellValue('I3', 'AWB');
        $sheet->setCellValue('I4', $this->flight->awb ?? '—');

        // AIRLINE
        $sheet->mergeCells('K3:M3');
        $sheet->mergeCells('K4:M4');

        $sheet->setCellValue(
            'K3',
            'AIRLINE'
        );

        $sheet->setCellValue(
            'K4',
            $this->flight->airline?->name ?? '—'
        );

        // FLIGHT DATE
        $sheet->mergeCells('N3:O3');
        $sheet->mergeCells('N4:O4');

        $sheet->setCellValue(
            'N3',
            'FLIGHT DATE'
        );

        $sheet->setCellValue(
            'N4',
            $this->flight->date?->format('d/m/Y') ?? '—'
        );

        // ARRIVAL DATE
        $sheet->mergeCells('P3:Q3');
        $sheet->mergeCells('P4:Q4');

        $sheet->setCellValue(
            'P3',
            'ARRIVAL DATE'
        );

        $sheet->setCellValue(
            'P4',
            $this->flight->arrival_date?->format('d/m/Y') ?? '—'
        );

        $sheet->mergeCells('I5:P5'); //#34226a color base
        // $sheet->setCellValue('K2', 'INFORMACIÓN DEL VUELO');
        $sheet->getStyle('I5:P5')
            ->applyFromArray($infoStyle);

        // ORIGIN
        $sheet->mergeCells('I6:L6');
        $sheet->mergeCells('I7:L7');

        $sheet->setCellValue(
            'I6',
            'ORIGIN'
        );

        $sheet->setCellValue(
            'I7',
            $this->flight->originCity?->name ?? '—'
        );

        // DESTINATION
        $sheet->mergeCells('M6:P6');
        $sheet->mergeCells('M7:P7');

        $sheet->setCellValue(
            'M6',
            'DESTINATION'
        );

        $sheet->setCellValue(
            'M7',
            $this->flight->destinationCity?->name ?? '—'
        );

        /*
        |--------------------------------------------------------------------------
        | STYLE FLIGHT INFORMATION
        |--------------------------------------------------------------------------
        */

        $flightInfoRanges = [
            'I2:Q2',
            'I3:J4',
            'K3:M4',
            'N3:O4',
            'P3:Q4',
            'I5:P5',
            'I6:L7',
            'M6:P7',
        ];

        foreach ($flightInfoRanges as $range) {
            $sheet->getStyle($range)->applyFromArray($border);
        }

        // Labels
        foreach ([
            'I2',
            'I3',
            'K3',
            'N3',
            'P3',
            'I5',
            'I6',
            'M6',
        ] as $cell) {
            $sheet->getStyle($cell)->getFont()->setSize(10);
            $sheet->getStyle($cell)->getFont()->setBold(false);
            $sheet->getStyle($cell)->getFont()->getColor()->setRGB('34226A');
        }

        // Values
        foreach ([
            'I4',
            'K4',
            'N4',
            'P4',
            'I7',
            'M7',
        ] as $cell) {
            $sheet->getStyle($cell)->getFont()->setSize(13);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Vertical alignment
        $sheet->getStyle('I2:Q7')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('I2:Q2'); //#34226a color base
        $sheet->setCellValue('I2', 'INFORMACIÓN DEL VUELO');
        $sheet->getStyle('I2:Q2')
            ->applyFromArray($infoStyle);

        $sheet->mergeCells('D8:I9');
        $sheet->setCellValue('D8', 'REPORTE DE COORDINACIÓN ' . $this->client->name);

        $sheet->mergeCells('P9:Q9');
        $sheet->setCellValue('P9', 'DOCUMENTO OPERATIVO');

        $sheet->getStyle('D8:I9')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('P9:Q9')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | COORDINATIONS
        |--------------------------------------------------------------------------
        */

        $row = 10;

        /*
        |--------------------------------------------------------------------------
        | COLUMN HEADERS
        |--------------------------------------------------------------------------
        */

        // $activeBoxes = collect([
        //     'fb' => $this->flight->fb_status,
        //     'hb' => $this->flight->hb_status,
        //     'qb' => $this->flight->qb_status,
        //     'eb' => $this->flight->eb_status,
        //     'db' => $this->flight->db_status,
        // ])
            
        //     ->all();
        $activeBoxes = collect([
            'fb' => $this->flight->fb_status,
            'hb' => $this->flight->hb_status,
            'qb' => $this->flight->qb_status,
            'eb' => $this->flight->eb_status,
            'db' => $this->flight->db_status,
        ])
            ->filter()
            ->keys()
            ->values()
            ->all();
        // dd( count($activeBoxes));

        $nextRow = $row + 1;


         /*
        |--------------------------------------------------------------------------
        | HAWB / FARM / VARIETY
        |--------------------------------------------------------------------------
        */
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '24435F'], // 34226A - 172033
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
        // Estilo celda coordinadas
        $headerStyle_c = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '247FB8'], // 34226A - 172033
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];

        $sheet->mergeCells("B{$row}:B{$nextRow}");
        $sheet->setCellValue("B{$row}", 'HAWB');

        $sheet->mergeCells("C{$row}:C{$nextRow}");
        $sheet->setCellValue("C{$row}", 'FARM');

        $sheet->mergeCells("D{$row}:D{$nextRow}");
        $sheet->setCellValue("D{$row}", 'VARIETY');

        // $sheet->mergeCells("B{$row}:D{$nextRow}");
        // $sheet->setCellValue("B{$row}", '');
        $columnHeader = 'E';
        $nextColumnHeader = chr(ord($columnHeader) + 1 + count($activeBoxes));
        // dd($row);
        
        $sheet->mergeCells("{$columnHeader}{$row}:{$nextColumnHeader}{$row}");
        $sheet->setCellValue("E{$row}", 'COORDINATED');
        
        $columnHeader = chr(ord($nextColumnHeader) + 1);
        $nextColumnHeader = chr(ord($columnHeader) + 1 + count($activeBoxes));
        // dd($nextColumnHeader);
        $sheet->mergeCells("{$columnHeader}{$row}:{$nextColumnHeader}{$row}");
        $sheet->setCellValue("{$columnHeader}{$row}", 'RECEIVED');
        // dd($columnHeader);
        $columnHeader = chr(ord($nextColumnHeader) + 1);
        $sheet->mergeCells("{$columnHeader}{$row}:{$columnHeader}{$nextRow}");
        $sheet->setCellValue("{$columnHeader}{$row}", 'RETURNS');

        $columnHeader++;
        $sheet->mergeCells("{$columnHeader}{$row}:{$columnHeader}{$nextRow}");
        $sheet->setCellValue("{$columnHeader}{$row}", 'MISSING');

        $columnHeader++;
        $sheet->mergeCells("{$columnHeader}{$row}:{$columnHeader}{$nextRow}");
        $sheet->setCellValue("{$columnHeader}{$row}", 'OBSERVATIONS');

        /*
        |--------------------------------------------------------------------------
        | SECOND HEADER ROW
        |--------------------------------------------------------------------------
        */
        $sheet->getStyle("B{$row}:D" . ($row + 1))
            ->applyFromArray($headerStyle);
        $column = 'E';
        
        $rowH = $row + 1;
        // Cajas coordinadas.
        foreach ($activeBoxes as $box) {
            $spreadsheet->getActiveSheet()->getColumnDimension("{$column}")->setWidth(8);
            $sheet->setCellValue(
                "{$column}{$rowH}",
                strtoupper($box)
            );

            $column++;
        }
        $spreadsheet->getActiveSheet()->getColumnDimension("{$column}")->setWidth(8);
        $sheet->setCellValue("{$column}{$rowH}", 'PIECES');
        $column++;
        $spreadsheet->getActiveSheet()->getColumnDimension("{$column}")->setWidth(11);
        $sheet->setCellValue("{$column}{$rowH}", 'FULLS');
        
        $sheet->getStyle("E{$row}:{$column}" . ($row + 1))
            ->applyFromArray($headerStyle_c);
        $column++;

        $column_r = $column;
        // Cajas coordinadas.
        foreach ($activeBoxes as $box) {
            $spreadsheet->getActiveSheet()->getColumnDimension("{$column}")->setWidth(8);
            $sheet->setCellValue(
                "{$column}{$rowH}",
                strtoupper($box) . '_R'
            );

            $column++;
        }
        $spreadsheet->getActiveSheet()->getColumnDimension("{$column}")->setWidth(8);
        $sheet->setCellValue("{$column}{$rowH}", 'PIECES_R');
        $column++;
        $spreadsheet->getActiveSheet()->getColumnDimension("{$column}")->setWidth(11);
        $sheet->setCellValue("{$column}{$rowH}", 'FULLS_R');

        $headerStyle_r = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'FFB800'], // 34226A - 172033
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
        $sheet->getStyle("{$column_r}{$row}:{$column}" . ($row + 1))
            ->applyFromArray($headerStyle_r);

        $column++;
        $column_o = $column;
        $spreadsheet->getActiveSheet()->getColumnDimension("{$column}")->setWidth(8);
        $column++;
        $spreadsheet->getActiveSheet()->getColumnDimension("{$column}")->setWidth(8);
        $column++;
        $spreadsheet->getActiveSheet()->getColumnDimension("{$column}")->setWidth(30);

        $sheet->getStyle("{$column_o}{$row}:{$column}" . ($row + 1))
            ->applyFromArray($headerStyle);

        $sheet->getRowDimension($row)->setRowHeight(25);
        $sheet->getRowDimension($row + 1)->setRowHeight(25);

        $row += 2;

        /*
        |--------------------------------------------------------------------------
        | CLIENT
        |--------------------------------------------------------------------------
        */

        $clientName = $this->client->name ?? 'NO CLIENT';

        /*
        |--------------------------------------------------------------------------
        | CLIENT HEADER
        |--------------------------------------------------------------------------
        */
        $clientStyle = [
            'font' => [
                'bold' => true,
                'size' => 15,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E5E7EB'],
            ],
        ];


        $sheet->mergeCells("C{$row}:{$column}{$row}");

        $sheet->setCellValue(
            "B{$row}",
            'CLIENT:'
        );
        $row_client = $row;
        $sheet->getRowDimension($row)->setRowHeight(25);
        $sheet->setCellValue(
            "C{$row}",
            $clientName
        );

        $sheet->getStyle("C{$row}:{$column}{$row}")
                ->applyFromArray($clientStyle);

        $sheet->getStyle("B{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E5E7EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                // 'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $row++;

        /*
        |--------------------------------------------------------------------------
        | CLIENT SUBTOTAL
        |--------------------------------------------------------------------------
        */

        $subtotal = [
            'fb' => 0,
            'hb' => 0,
            'qb' => 0,
            'eb' => 0,
            'db' => 0,
            'pieces' => 0,
            'fulls' => 0,

            'fb_r' => 0,
            'hb_r' => 0,
            'qb_r' => 0,
            'eb_r' => 0,
            'db_r' => 0,
            'pieces_r' => 0,
            'fulls_r' => 0,

            'returns' => 0,
            'missing' => 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | COORDINATION ROWS
        |--------------------------------------------------------------------------
        */

        $row_first = $row;
        foreach ($coordinations as $coordination) {

            $varietyIds = $coordination->varieties ?? [];

            $varieties = \App\Models\FlowerVariety::query()
                ->whereIn('id', $varietyIds)
                ->pluck('name')
                ->implode(', ');
            // $varieties = $coordination->varieties;
            
            // if (is_array($varieties)) {
            //     $variety = implode(', ', $varieties);
            // } else {
            //     $variety = $varieties ?? '—';
            // }


            /*
            |--------------------------------------------------------------------------
            | VALUES
            |--------------------------------------------------------------------------
            */

            

            $fb = (int) ($coordination->fb ?? 0);
            $hb = (int) ($coordination->hb ?? 0);
            $qb = (int) ($coordination->qb ?? 0);
            $eb = (int) ($coordination->eb ?? 0);
            $db = (int) ($coordination->db ?? 0);

            $pieces = (int) ($coordination->pieces ?? 0);
            $fulls = (float) ($coordination->fulls ?? 0);

            $fbR = (int) ($coordination->fb_r ?? 0);
            $hbR = (int) ($coordination->hb_r ?? 0);
            $qbR = (int) ($coordination->qb_r ?? 0);
            $ebR = (int) ($coordination->eb_r ?? 0);
            $dbR = (int) ($coordination->db_r ?? 0);

            $piecesR = (int) ($coordination->pieces_r ?? 0);
            $fullsR = (float) ($coordination->fulls_r ?? 0);

            $returns = (int) ($coordination->returns ?? 0);
            $missing = (int) ($coordination->missing ?? 0);


            /*
            |--------------------------------------------------------------------------
            | WRITE ROW
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue("B{$row}", $coordination->hawb ?? '—');

            $sheet->setCellValue(
                "C{$row}",
                $coordination->farm?->name ?? '—'
            );

            $sheet->setCellValue(
                "D{$row}",
                strtoupper($varieties)
            );
            $column = 'E';
            $sum_column = $column;
            // Cajas coordinadas.
            foreach ($activeBoxes as $box) {
                $sheet->setCellValue(
                    "{$column}{$row}",
                    (int) $coordination->{$box} ?? 0
                );

                $column++;
            }
            // Coordinated
            // Celda de Piezas - 1
            $pieza_nemos_uno = chr(ord($column) - 1);
            $pesos = [
                'fb' => 1,
                'hb' => 0.5,
                'qb' => 0.25,
                'eb' => 0.125,
                'db' => 0.0625,
            ];
            $partes = [];
            // Calculo para suma horizontal y dependiendo del tipo de caja coordinada
            foreach($activeBoxes as $box){
                if(!isset($pesos[$box])) continue;
                $peso = $pesos[$box];
                $partes[] = "({$sum_column}{$row}*{$peso})";
                $sum_column++;
            }
            $formula_c = '=' . implode('+', $partes);
            
            $sheet->setCellValue("{$column}{$row}", "=SUM(E{$row}:{$pieza_nemos_uno}{$row})");
            $col_pieces_c = $column;
            $column++;
            $sheet->setCellValue("{$column}{$row}", $formula_c);
            $column++;
            $sum_column_r = $column;
            // Cajas recibidas.
            foreach ($activeBoxes as $box) {
                $receivedField = $box . '_r';
                // dd($receivedField);
                $sheet->setCellValue(
                    "{$column}{$row}",
                    $coordination->{$receivedField} ?? 0
                );

                $column++;
            }
            // Calculo para suma horizontal y dependiendo del tipo de caja recibida
            $pieza_nemos_uno_r = chr(ord($column) - 1);
            $pieza_mas_dos = chr(ord($pieza_nemos_uno) + 3);
            $partes_r = [];
            foreach($activeBoxes as $box){
                if(!isset($pesos[$box])) continue;
                $peso = $pesos[$box];
                $partes_r[] = "({$sum_column_r}{$row}*{$peso})";
                $sum_column_r++;
            }
            $formula_r = '=' . implode('+', $partes_r);
            // Received
            
            $sheet->setCellValue("{$column}{$row}", "=SUM({$pieza_mas_dos}{$row}:{$pieza_nemos_uno_r}{$row})");
            $col_pieces_r = $column;
            $column++;
            $sheet->setCellValue("{$column}{$row}", $formula_r);
            $column++;
            $sheet->setCellValue("{$column}{$row}", $returns);
            $column++;
            $sheet->setCellValue("{$column}{$row}", "=+{$col_pieces_c}{$row}-{$col_pieces_r}{$row}");
            $column++;
            $sheet->setCellValue("{$column}{$row}", $coordination->observation);

            /*
            |--------------------------------------------------------------------------
            | SUBTOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal['fb'] += $fb;
            $subtotal['hb'] += $hb;
            $subtotal['qb'] += $qb;
            $subtotal['eb'] += $eb;
            $subtotal['db'] += $db;
            $subtotal['pieces'] += $pieces;
            $subtotal['fulls'] += $fulls;

            $subtotal['fb_r'] += $fbR;
            $subtotal['hb_r'] += $hbR;
            $subtotal['qb_r'] += $qbR;
            $subtotal['eb_r'] += $ebR;
            $subtotal['db_r'] += $dbR;
            $subtotal['pieces_r'] += $piecesR;
            $subtotal['fulls_r'] += $fullsR;

            $subtotal['returns'] += $returns;
            $subtotal['missing'] += $missing;

            $row++;
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENT SUBTOTAL ROW
        |--------------------------------------------------------------------------
        */
        // Fila de subtotal del cliente.
        $totalRow = $row;
        $sheet->getRowDimension($totalRow)->setRowHeight(20);
        $sheet->mergeCells("B{$totalRow}:D{$totalRow}");
        $sheet->setCellValue("B{$totalRow}", 'TOTAL');

        // Color de subtotal 
        $sheet->getStyle("B{$row}:D{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 13,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F1F3F5'],
            ],
        ]);

        $column = 'E';
        // Totales de cajas coordinadas.
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$column}{$row}",
                "=SUM({$column}" . ($row - 1) . ":{$column}{$row_first})"
            );

            $column++;
        }
        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}" . ($row - 1) . ":{$column}{$row_first})");
        $column++;
        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}" . ($row - 1) . ":{$column}{$row_first})");

        /*
        |--------------------------------------------------------------------------
        | SUBTOTAL STYLE
        |--------------------------------------------------------------------------
        */
        // Color de subtotal coordinado
        $sheet->getStyle("E{$row}:{$column}{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 13,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'EDF5FE'],
            ],
        ]);
        $column++;
        // Totales de cajas coordinadas.
        $tem_column = $column;
        foreach ($activeBoxes as $box) {
            $sheet->setCellValue(
                "{$column}{$row}",
                "=SUM({$column}" . ($row - 1) . ":{$column}{$row_first})"
            );

            $column++;
        }

        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}" . ($row - 1) . ":{$column}{$row_first})");
        $column++;
        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}" . ($row - 1) . ":{$column}{$row_first})");
        /*
        |--------------------------------------------------------------------------
        | SUBTOTAL STYLE
        |--------------------------------------------------------------------------
        */
        // Color subtotal Recibido
        $sheet->getStyle("{$tem_column}{$row}:{$column}{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'FFF4CC'],
            ],
        ]);

        $column++;
        $tem_column2 = $column;
        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}" . ($row - 1) . ":{$column}{$row_first})");
        $column++;
        $sheet->setCellValue("{$column}{$row}", "=SUM({$column}" . ($row - 1) . ":{$column}{$row_first})");
        $column++;

        $arrSubTotal[] = $row;
        /*
        |--------------------------------------------------------------------------
        | SUBTOTAL STYLE
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("{$tem_column2}{$row}:{$column}{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F1F3F5'],
            ],
        ]);

        $sheet->getStyle("B{$row_client}:{$column}{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $row++;
        $letra = 'A';
        // dd($column_final);
        $spreadsheet->getActiveSheet()->getStyle($letra . $row . ":" . $column_final . $row)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFFFF');

        /*
        |--------------------------------------------------------------------------
        | ACCUMULATE GLOBAL
        |--------------------------------------------------------------------------
        */

        // foreach ($global as $key => $value) {
        //     $global[$key] += $subtotal[$key];
        // }

        // Espacio entre clientes
        $row++;

        /*
        |--------------------------------------------------------------------------
        | GLOBAL TOTAL STYLE
        |--------------------------------------------------------------------------
        */
        // dd($row);
        // $sheet->getStyle("B{$row}:{$column}{$row}")->applyFromArray([
        //     'font' => [
        //         'bold' => true,
        //         'size' => 15,
        //     ],
        //     'fill' => [
        //         'fillType' => Fill::FILL_SOLID,
        //         'color' => ['rgb' => 'CBD5E1'],
        //     ],
        //     'borders' => [
        //         'allBorders' => [
        //             'borderStyle' => Border::BORDER_THIN,
        //             'color' => ['rgb' => '94A3B8'],
        //         ],
        //     ],
        // ]);

        /*
        |--------------------------------------------------------------------------
        | TABLE BORDERS & ALIGNMENT
        |--------------------------------------------------------------------------
        */

        $lastRow = $row;

        /*
        |--------------------------------------------------------------------------
        | NUMBER FORMAT
        |--------------------------------------------------------------------------
        */

        $column = 'E';
        // dd(count($activeBoxes));
        $column = chr(ord($column) + count($activeBoxes) + 1);
        // dd($column);
        $sheet->getStyle("{$column}13:{$column}{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.000');
        $column++;
        
        $column = chr(ord($column) + count($activeBoxes) + 1);
        // dd($column);
        $sheet->getStyle("{$column}13:{$column}{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.000');

        /*
        |--------------------------------------------------------------------------
        | ALIGN NUMBERS
        |--------------------------------------------------------------------------
        */
        
        $column = chr(ord($column) + 3);
        // dd($column);
        $sheet->getStyle("D12:{$column}{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("B13:B{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("C13:C{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

            
        /*
        |--------------------------------------------------------------------------
        | PRINT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $pageSetup = $sheet->getPageSetup();

        $pageSetup->setOrientation(
            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
        );

        $pageSetup->setPaperSize(
            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
        );

        // Una página de ancho y tantas páginas de alto como sean necesarias.
        $pageSetup->setFitToPage(true);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);

        // Definir el área de impresión automáticamente.
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        $sheet->getPageMargins()
            ->setLeft(0.25)
            ->setRight(0.25)
            ->setTop(0.40)
            ->setBottom(0.40);

        $sheet->getPageSetup()->setPrintArea(
            "A1:{$lastColumn}{$lastRow}"
        );

        // Repetir el encabezado de la tabla en cada página impresa.
        // En tu Excel, la tabla comienza en las filas 10 y 11.
        $pageSetup->setRowsToRepeatAtTopByStartAndEnd(10, 11);

        // $sheet->setCellValue('B2', 'COORDINACIONES AÉREAS');
        // $sheet->setCellValue('B3', 'CLIENTE: ' . $this->client->name);
        // $sheet->setCellValue('B4', 'AWB: ' . ($this->flight->awb ?? '—'));
        // $sheet->setCellValue(
        //     'B5',
        //     'AIRLINE: ' . ($this->flight->airline?->name ?? '—')
        // );
        // $sheet->setCellValue(
        //     'B6',
        //     'FLIGHT DATE: ' . ($this->flight->date?->format('d/m/Y') ?? '—')
        // );
        // $sheet->setCellValue(
        //     'B7',
        //     'ARRIVAL DATE: ' . ($this->flight->arrival_date?->format('d/m/Y') ?? '—')
        // );

        // En el siguiente paso construiremos la tabla,
        // sus subtotales y el formato del Excel completo.

        // // Encabezados de la tabla. /////////////********************* */////////////////////
        // $headerRow = 10;

        // $sheet->setCellValue("B{$headerRow}", 'HAWB');
        // $sheet->setCellValue("C{$headerRow}", 'FARM');
        // $sheet->setCellValue("D{$headerRow}", 'VARIETY');

        // $column = 'E';

        // // Cajas coordinadas.
        // foreach ($activeBoxes as $box) {
        //     $sheet->setCellValue(
        //         "{$column}{$headerRow}",
        //         strtoupper($box)
        //     );

        //     $column++;
        // }

        // $sheet->setCellValue("{$column}{$headerRow}", 'FULLS');
        // $column++;

        // // Cajas recibidas.
        // foreach ($activeBoxes as $box) {
        //     $sheet->setCellValue(
        //         "{$column}{$headerRow}",
        //         strtoupper($box) . ' RECEIVED'
        //     );

        //     $column++;
        // }

        // $sheet->setCellValue("{$column}{$headerRow}", 'FULLS RECEIVED');
        // $column++;
        // $sheet->setCellValue("{$column}{$headerRow}", 'RETURNS');
        // $column++;
        // $sheet->setCellValue("{$column}{$headerRow}", 'MISSING');
        // $column++;
        // $sheet->setCellValue("{$column}{$headerRow}", 'OBSERVATIONS');

        // // Escribir las coordinaciones del cliente seleccionado.
        // $row = $headerRow + 1;

        // foreach ($coordinations as $coordination) {
        //     $sheet->setCellValue("B{$row}", $coordination->hawb);
        //     $sheet->setCellValue("C{$row}", $coordination->farm?->name ?? '—');

        //     // Convertir los IDs de variedades en sus nombres.
        //     $varietyNames = FlowerVariety::query()
        //         ->whereIn('id', $coordination->varieties ?? [])
        //         ->pluck('name')
        //         ->implode(', ');

        //     $sheet->setCellValue("D{$row}", $varietyNames ?: '—');

        //     $column = 'E';

        //     // Cajas coordinadas.
        //     foreach ($activeBoxes as $box) {
        //         $sheet->setCellValue(
        //             "{$column}{$row}",
        //             $coordination->{$box} ?? 0
        //         );

        //         $column++;
        //     }

        //     $sheet->setCellValue(
        //         "{$column}{$row}",
        //         $coordination->fulls ?? 0
        //     );
        //     $column++;

        //     // Cajas recibidas.
        //     foreach ($activeBoxes as $box) {
        //         $receivedField = $box . '_r';

        //         $sheet->setCellValue(
        //             "{$column}{$row}",
        //             $coordination->{$receivedField} ?? 0
        //         );

        //         $column++;
        //     }

        //     $sheet->setCellValue(
        //         "{$column}{$row}",
        //         $coordination->fulls_r ?? 0
        //     );
        //     $column++;

        //     $sheet->setCellValue(
        //         "{$column}{$row}",
        //         $coordination->returns ?? 0
        //     );
        //     $column++;

        //     $sheet->setCellValue(
        //         "{$column}{$row}",
        //         $coordination->missing ?? 0
        //     );
        //     $column++;

        //     $sheet->setCellValue(
        //         "{$column}{$row}",
        //         $coordination->observation ?? ''
        //     );

        //     $row++;
        // }

        // $lastColumn = $column;

        // $sheet->getStyle("B{$headerRow}:{$lastColumn}{$headerRow}")
        //     ->getFont()
        //     ->setBold(true);

        // $sheet->getStyle("B{$headerRow}:{$lastColumn}{$headerRow}")
        //     ->getAlignment()
        //     ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // $sheet->getStyle("B{$headerRow}:{$lastColumn}{$headerRow}")
        //     ->getFill()
        //     ->setFillType(Fill::FILL_SOLID)
        //     ->getStartColor()
        //     ->setARGB('FFD9EAF7');

        // // Fila de subtotal del cliente.
        // $totalRow = $row;

        // $sheet->setCellValue("B{$totalRow}", 'TOTAL CLIENTE');
        
        // // Sumar las columnas numéricas de la tabla.
        // $lastDataRow = $totalRow - 1;
        // // dd($headerRow);
        // if ($lastDataRow > $headerRow) {
        //     $column = 'E';
            
        //     // Totales de cajas coordinadas.
        //     foreach ($activeBoxes as $box) {
        //         $sheet->setCellValue(
        //             "{$column}{$totalRow}",
        //             "=SUM({$column}" . ($headerRow + 1) . ":{$column}{$lastDataRow})"
        //         );

        //         $column++;
        //     }

        //     // Total de fulls coordinados.
        //     $sheet->setCellValue(
        //         "{$column}{$totalRow}",
        //         "=SUM({$column}" . ($headerRow + 1) . ":{$column}{$lastDataRow})"
        //     );
        //     $column++;

        //     // Totales de cajas recibidas.
        //     foreach ($activeBoxes as $box) {
        //         $sheet->setCellValue(
        //             "{$column}{$totalRow}",
        //             "=SUM({$column}" . ($headerRow + 1) . ":{$column}{$lastDataRow})"
        //         );

        //         $column++;
        //     }

        //     // Total de fulls recibidos.
        //     $sheet->setCellValue(
        //         "{$column}{$totalRow}",
        //         "=SUM({$column}" . ($headerRow + 1) . ":{$column}{$lastDataRow})"
        //     );
        //     $column++;

        //     // Total de devoluciones.
        //     $sheet->setCellValue(
        //         "{$column}{$totalRow}",
        //         "=SUM({$column}" . ($headerRow + 1) . ":{$column}{$lastDataRow})"
        //     );
        //     $column++;

        //     // Total de faltantes.
        //     $sheet->setCellValue(
        //         "{$column}{$totalRow}",
        //         "=SUM({$column}" . ($headerRow + 1) . ":{$column}{$lastDataRow})"
        //     );
        // } else {
        //     $sheet->setCellValue("B{$totalRow}", 'SIN COORDINACIONES');
        // }

        // // Formato de la fila de totales.
        // $sheet->getStyle("B{$totalRow}:{$lastColumn}{$totalRow}")
        //     ->getFont()
        //     ->setBold(true);

        // $sheet->getStyle("B{$totalRow}:{$lastColumn}{$totalRow}")
        //     ->getFill()
        //     ->setFillType(Fill::FILL_SOLID)
        //     ->getStartColor()
        //     ->setARGB('FFE2F0D9');

        // $sheet->getStyle("B{$totalRow}:{$lastColumn}{$totalRow}")
        //     ->getBorders()
        //     ->getTop()
        //     ->setBorderStyle(Border::BORDER_THIN);


        // // Estilo básico de los encabezados.

        

        // // Ajustar automáticamente el ancho de las columnas utilizadas.
        // foreach (range('B', $lastColumn) as $columnLetter) {
        //     $sheet->getColumnDimension($columnLetter)
        //         ->setAutoSize(true);
        // }

        // // Alinear verticalmente el contenido.
        // $sheet->getStyle("B{$headerRow}:{$lastColumn}{$totalRow}")
        //     ->getAlignment()
        //     ->setVertical(Alignment::VERTICAL_CENTER);

        // // Ajustar el texto en las columnas de variedades y observaciones.
        // $sheet->getStyle("D{$headerRow}:D{$totalRow}")
        //     ->getAlignment()
        //     ->setWrapText(true);

        // $sheet->getStyle("{$lastColumn}{$headerRow}:{$lastColumn}{$totalRow}")
        //     ->getAlignment()
        //     ->setWrapText(true);

        // // Inmovilizar las filas superiores para facilitar la lectura.
        // $sheet->freezePane('B11');

        // // Configurar la impresión en horizontal, tamaño A4.
        // $sheet->getPageSetup()
        //     ->setOrientation(
        //         \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
        //     );

        // $sheet->getPageSetup()
        //     ->setPaperSize(
        //         \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
        //     );

        // $sheet->getPageSetup()->setFitToWidth(1);
        // $sheet->getPageSetup()->setFitToHeight(0);
        // $sheet->getPageSetup()->setFitToPage(true);

        // // Repetir los encabezados al imprimir varias páginas.
        // $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(10, 10);


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */


        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            'flight-' . $this->flight->awb
                . '-client-' . $this->client->id . '.xlsx',
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

}
