<?php
$timestamp = date('YmdHis');
$zipUrl = "https://join.gov.tw/idea/files/zip/842b1b2a-464b-4f1d-9d61-d5a0ab1b946b/export_{$timestamp}.zip";
$zipFile = __DIR__ . '/temp.zip';
$extractDir = __DIR__ . '/temp_extract';
$outputJson = __DIR__ . '/../docs/842b1b2a-464b-4f1d-9d61-d5a0ab1b946b.json';

// Download the zip file
echo "Downloading zip file...\n";
$zipContent = file_get_contents($zipUrl);
if ($zipContent === false) {
    die("Failed to download zip file\n");
}
file_put_contents($zipFile, $zipContent);

// Extract the zip file
echo "Extracting zip file...\n";
$zip = new ZipArchive();
if ($zip->open($zipFile) === true) {
    $zip->extractTo($extractDir);
    $zip->close();
} else {
    die("Failed to extract zip file\n");
}

// Read the CSV file
$csvFile = $extractDir . '/附議名單.csv';
if (!file_exists($csvFile)) {
    die("CSV file not found\n");
}

echo "Parsing CSV file...\n";
$csvData = file_get_contents($csvFile);
// Remove BOM if present
$csvData = preg_replace('/^\xEF\xBB\xBF/', '', $csvData);

$lines = str_getcsv($csvData, "\n");
$data = [];
$headers = null;

foreach ($lines as $index => $line) {
    $row = str_getcsv($line);

    if ($index === 0) {
        // First row is headers
        $headers = array_map(function($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $row);
    } else {
        if (count($row) === count($headers)) {
            $data[] = array_combine($headers, $row);
        }
    }
}

// Save as JSON
echo "Saving JSON file...\n";
file_put_contents($outputJson, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Cleanup
unlink($zipFile);
array_map('unlink', glob("$extractDir/*"));
rmdir($extractDir);

echo "Done! Saved " . count($data) . " records to $outputJson\n";
