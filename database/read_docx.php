<?php
$docx = realpath(__DIR__ . '/../kuesioner desa 2025[1].docx');
if (!$docx) die('File tidak ditemukan: ' . __DIR__ . '/../kuesioner desa 2025[1].docx');
// Baca raw content, cari word/document.xml di dalam ZIP
$raw = file_get_contents($docx);
// Cari local file header untuk document.xml
$pos = strpos($raw, 'word/document.xml');
if ($pos === false) die('Tidak ditemukan word/document.xml di dalam file.');
// Cari compressed data (skip local file header: 30 bytes + filename + extra)
$fnLen  = unpack('v', substr($raw, $pos - 18, 2))[1];
$extLen = unpack('v', substr($raw, $pos - 16, 2))[1];
$dataStart = $pos + $fnLen + $extLen + 2; // +2 karena kita sudah di offset filename

// Method: 0=store, 8=deflate
$method = unpack('v', substr($raw, $pos - 22, 2))[1];
$compSize   = unpack('V', substr($raw, $pos - 20, 4))[1];
$uncompSize = unpack('V', substr($raw, $pos - 16, 4))[1];

if ($method === 0) {
    $xml = substr($raw, $dataStart, $uncompSize);
} elseif ($method === 8) {
    $xml = gzinflate(substr($raw, $dataStart, $compSize));
} else {
    die("Method tidak dikenal: $method");
}

$text = strip_tags(str_replace(['</w:p>','</w:tr>'], ["\n", "\n"], $xml));
$lines = array_filter(array_map('trim', explode("\n", $text)));
header('Content-Type: text/plain; charset=utf-8');
echo implode("\n", array_values($lines));
