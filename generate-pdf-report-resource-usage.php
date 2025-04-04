<?php

/**
 * http://spencerandlewis.com/utilities/generate-pdf-report-resource-usage.php
 */

require('vendor/autoload.php');

function addTableHeader($pdf, $header)
{
    $pdf->SetFont('Arial', 'B', 10);
    foreach ($header as $col) {
        $pdf->Cell(40, 7, $col, 1);
    }
    $pdf->Ln();
}

function addTableRow($pdf, $row)
{
    $pdf->SetFont('Arial', '', 10);
    foreach ($row as $col) {
        $pdf->Cell(40, 6, $col, 1);
    }
    $pdf->Ln();
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, 'Report Utilizzo Server Linux con Plesk', 1, 1, 'C');

$pdf->Ln(10); // Aggiungi uno spazio

// Intestazione per l'utilizzo del disco
$diskUsageHeader = ['Filesystem', 'Size', 'Used', 'Avail', 'Use%', 'Mounted on'];
addTableHeader($pdf, $diskUsageHeader);

// Esecuzione e parsing dell'output del comando 'df -h'
$diskUsageOutput = shell_exec('df -h | tail -n +2'); // esclude la prima riga di intestazione
$diskUsageLines = explode("\n", $diskUsageOutput);

foreach ($diskUsageLines as $line) {
    if (!empty($line)) {
        addTableRow($pdf, preg_split('/\s+/', $line));
    }
}

$pdf->Ln(10); // Aggiungi spazio prima della prossima sezione

// Intestazione per CPU e memoria
$topHeader = ['PID', 'USER', 'PR', 'NI', 'VIRT', 'RES', 'SHR', 'S', 'CPU%', 'MEM%', 'TIME+', 'COMMAND'];
addTableHeader($pdf, array_slice($topHeader, 0, 6)); // Visualizziamo solo alcune colonne per evitare sovraffollamento

// Esecuzione e parsing dell'output del comando 'top'
$topOutput = shell_exec('top -b -n 1 | head -n 17'); // Prende le prime righe, inclusa l'intestazione
$topLines = explode("\n", $topOutput);

foreach (array_slice($topLines, 7) as $line) { // Esclude le prime 7 righe di intestazione e riepilogo
    if (!empty($line)) {
        addTableRow($pdf, array_slice(preg_split('/\s+/', $line), 0, 6));
    }
}

// Salvataggio del PDF
$pdf->Output('F', 'server_report.pdf');

echo "Il report del server è stato generato con successo.";
