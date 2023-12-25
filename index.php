<?php

require_once('vendor/autoload.php');

use setasign\Fpdi\Fpdi;

// Directories
$inputDir = __DIR__ . '/input/';
$outputDir = __DIR__ . '/output/';

// Check for command line argument
$pagesPerFile = $argc > 1 ? (int)$argv[1] : 1;

// Read all PDF files from input directory
$pdfFiles = glob($inputDir . '*.PDF');
echo 'Found ' . count($pdfFiles) . ' input PDF files' . PHP_EOL;

foreach ($pdfFiles as $originalPdfIndex => $originalPdf) {
    $originalPdfIndex = $originalPdfIndex + 1;
    echo 'File #' . $originalPdfIndex . ' to split ' . $originalPdf . PHP_EOL;

    if (!file_exists($originalPdf)) {
        throw new Exception("File not found: " . $originalPdf);
    }

    // Initialize FPDI
    $pdf = new Fpdi();
    // Get the page count
    $pageCount = $pdf->setSourceFile($originalPdf);

    echo 'File #' . $originalPdfIndex . ' page count: ' . $pageCount . PHP_EOL;

    echo 'File #' . $originalPdfIndex . ' splits:' . PHP_EOL;

    $splits = 0;

    // Loop through the pages
    for ($startPage = 1; $startPage <= $pageCount; $startPage += $pagesPerFile) {
        // Create a new PDF for each group of pages
        $pdf = new Fpdi();
        for ($pageNo = $startPage; $pageNo < $startPage + $pagesPerFile && $pageNo <= $pageCount; $pageNo++) {
            $pdf->setSourceFile($originalPdf);
            // Add a page
            $pdf->AddPage();
            // Import the page
            $templateId = $pdf->importPage($pageNo);
            // Use the imported page and adjust the page size
            $pdf->useTemplate($templateId, ['adjustPageSize' => true]);
        }

        // Output the new PDF
        $outputFilename = basename($originalPdf, '.pdf') . "_output_" . $startPage . ".pdf";
        $pdf->Output($outputDir . $outputFilename, 'F');

        $splits++;

        echo $splits . ') ' . $outputFilename . PHP_EOL;
    }
}
