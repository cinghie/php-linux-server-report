<?php

/**
 * http://spencerandlewis.com/utilities/generate-pdf-report-plesk.php
 */

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Plesk Monitoring Report', 0, 1, 'C');
        $this->Ln(10);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Table header
    function TableHeader()
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(40, 10, 'Server', 1);
        $this->Cell(40, 10, 'Status', 1);
        $this->Cell(60, 10, 'Uptime', 1);
        $this->Cell(40, 10, 'Load', 1);
        $this->Ln();
    }

    // Table row
    function TableRow($server, $status, $uptime, $load)
    {
        $this->SetFont('Arial', '', 10);
        $this->Cell(40, 10, $server, 1);
        $this->Cell(40, 10, $status, 1);
        $this->Cell(60, 10, $uptime, 1);
        $this->Cell(40, 10, $load, 1);
        $this->Ln();
    }
}

// Fetch monitoring data from Plesk API
function fetchPleskMonitoringData()
{
    $pleskApiUrl = 'https://your-plesk-server:8443/api/v2/monitoring';
    $username = 'your-plesk-username';
    $password = 'your-plesk-password';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pleskApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    return json_decode($result, true);
}

// Filter data for the previous month
function filterDataForPreviousMonth($data)
{
    $filteredData = [];
    $currentDate = new DateTime();
    $previousMonth = $currentDate->sub(new DateInterval('P1M'))->format('Y-m');

    foreach ($data as $monitoring) {
        $monitoringDate = DateTime::createFromFormat('Y-m-d H:i:s', $monitoring['date']);
        if ($monitoringDate->format('Y-m') === $previousMonth) {
            $filteredData[] = $monitoring;
        }
    }

    return $filteredData;
}

$data = fetchPleskMonitoringData();
$filteredData = filterDataForPreviousMonth($data);

// Create PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->TableHeader();

foreach ($filteredData as $serverMonitoring) {
    $server = $serverMonitoring['server'];
    $status = $serverMonitoring['status'];
    $uptime = $serverMonitoring['uptime'];
    $load = $serverMonitoring['load'];

    $pdf->TableRow($server, $status, $uptime, $load);
}

$pdf->Output('D', 'plesk_monitoring_report.pdf');

