<?php
/**
 * QR scan benchmark helper for current project implementation.
 *
 * Usage:
 *   php tools/qr_scan_benchmark.php
 *   php tools/qr_scan_benchmark.php "assets/uploads/qr_tmp/*.png"
 *   php tools/qr_scan_benchmark.php "assets/uploads/qr_tmp/*.png" --with-external
 *
 * PHP compatibility target: legacy PHP (5.3+).
 */

$root = realpath(dirname(__FILE__) . '/..');
if ($root === false) {
    fwrite(STDERR, "Cannot resolve project root.\n");
    exit(1);
}

$autoload = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "vendor/autoload.php not found. Run composer install first.\n");
    exit(1);
}

require_once $autoload;

if (!class_exists('QrReader')) {
    fwrite(STDERR, "QrReader class unavailable.\n");
    exit(1);
}

$defaultPattern = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'qr_tmp' . DIRECTORY_SEPARATOR . '*.png';
$patternInput = isset($argv[1]) ? $argv[1] : $defaultPattern;
$withExternal = in_array('--with-external', $argv, true);
$normalizedInput = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $patternInput);
$isWildcard = (strpos($patternInput, '*') !== false);
$isAbsolute = (preg_match('/^[A-Za-z]:\\\\/', $normalizedInput) === 1) || (strpos($normalizedInput, DIRECTORY_SEPARATOR) === 0);

if ($isWildcard) {
    $pattern = $patternInput;
    $sampleFiles = glob($pattern);
    if (!is_array($sampleFiles)) {
        $sampleFiles = array();
    }
} else {
    $singlePath = $isAbsolute
        ? $normalizedInput
        : $root . DIRECTORY_SEPARATOR . ltrim($normalizedInput, DIRECTORY_SEPARATOR);
    $pattern = $singlePath;
    $sampleFiles = file_exists($singlePath) ? array($singlePath) : array();
}

sort($sampleFiles, SORT_STRING);

if (count($sampleFiles) === 0) {
    fwrite(STDERR, "No sample files found for pattern: {$patternInput}\n");
    exit(1);
}

function safe_crop($image, $imgW, $imgH, $area)
{
    $x = max(0, (int) $area['x']);
    $y = max(0, (int) $area['y']);
    $w = max(1, min((int) $area['w'], $imgW - $x));
    $h = max(1, min((int) $area['h'], $imgH - $y));

    if (!function_exists('imagecrop')) {
        $crop = imagecreatetruecolor($w, $h);
        imagecopy($crop, $image, 0, 0, $x, $y, $w, $h);
        return $crop;
    }

    return @imagecrop($image, array('x' => $x, 'y' => $y, 'width' => $w, 'height' => $h));
}

function apply_threshold(&$image, $threshold)
{
    $w = imagesx($image);
    $h = imagesy($image);

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $luma = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
            $val = $luma >= $threshold ? 255 : 0;
            $color = imagecolorallocate($image, $val, $val, $val);
            imagesetpixel($image, $x, $y, $color);
        }
    }
}

function load_image_resource($filePath)
{
    if (!file_exists($filePath)) {
        return false;
    }

    $raw = @file_get_contents($filePath);
    if ($raw === false) {
        return false;
    }

    $img = @imagecreatefromstring($raw);
    if ($img !== false) {
        return $img;
    }

    return false;
}

function build_qr_variants($sourcePath, $tempDir)
{
    $variants = array($sourcePath);
    $image = load_image_resource($sourcePath);
    if (!$image) {
        return $variants;
    }

    $imgW = imagesx($image);
    $imgH = imagesy($image);
    if ($imgW < 1 || $imgH < 1) {
        imagedestroy($image);
        return $variants;
    }

    $areas = array(
        array('name' => 'full', 'x' => 0, 'y' => 0, 'w' => $imgW, 'h' => $imgH),
        array('name' => 'centerLowerLarge', 'x' => (int)($imgW * 0.18), 'y' => (int)($imgH * 0.50), 'w' => (int)($imgW * 0.64), 'h' => (int)($imgH * 0.42)),
        array('name' => 'centerLowerMedium', 'x' => (int)($imgW * 0.22), 'y' => (int)($imgH * 0.54), 'w' => (int)($imgW * 0.56), 'h' => (int)($imgH * 0.36)),
        array('name' => 'centerSquare', 'x' => (int)($imgW * 0.25), 'y' => (int)($imgH * 0.47), 'w' => (int)($imgW * 0.50), 'h' => (int)($imgW * 0.50))
    );
    $rotations = array(0, 45, -45, 90, 135, -135, 180, 270);
    $modes = array(
        array('gray' => false, 'threshold' => false),
        array('gray' => true, 'threshold' => false),
        array('gray' => true, 'threshold' => true)
    );

    $targetSize = 900;
    $variantCount = 0;
    $maxVariants = 12;

    foreach ($areas as $area) {
        foreach ($modes as $mode) {
            foreach ($rotations as $rotation) {
                if ($variantCount >= $maxVariants) {
                    break 3;
                }

                $crop = safe_crop($image, $imgW, $imgH, $area);
                if (!$crop) {
                    continue;
                }

                $canvas = imagecreatetruecolor($targetSize, $targetSize);
                $white = imagecolorallocate($canvas, 255, 255, 255);
                imagefilledrectangle($canvas, 0, 0, $targetSize, $targetSize, $white);
                imagecopyresampled(
                    $canvas,
                    $crop,
                    0,
                    0,
                    0,
                    0,
                    $targetSize,
                    $targetSize,
                    imagesx($crop),
                    imagesy($crop)
                );
                imagedestroy($crop);

                if ($mode['gray']) {
                    imagefilter($canvas, IMG_FILTER_GRAYSCALE);
                    imagefilter($canvas, IMG_FILTER_CONTRAST, -40);
                }
                if ($mode['threshold']) {
                    apply_threshold($canvas, 140);
                }

                $output = $canvas;
                if ($rotation !== 0) {
                    $rotated = imagerotate($canvas, $rotation, $white);
                    imagedestroy($canvas);
                    if ($rotated) {
                        $output = $rotated;
                    }
                }

                $variantName = $tempDir . DIRECTORY_SEPARATOR . 'bench_variant_' . uniqid('', true) . '.png';
                if (@imagepng($output, $variantName)) {
                    $variants[] = $variantName;
                    $variantCount++;
                }
                imagedestroy($output);
            }
        }
    }

    imagedestroy($image);
    return $variants;
}

function decode_variants($variantFiles)
{
    foreach ($variantFiles as $idx => $filePath) {
        try {
            $qr = new QrReader($filePath);
            $text = $qr->text();
            if ($text !== false && $text !== null && trim((string) $text) !== '') {
                return array(
                    'success' => true,
                    'text' => trim((string) $text),
                    'decoder_path' => ($idx === 0 ? 'server_original' : 'server_preprocessed')
                );
            }
        } catch (Exception $e) {
            continue;
        }
    }

    return array(
        'success' => false,
        'text' => '',
        'decoder_path' => 'server_preprocessed'
    );
}

function create_curl_file_compat($filePath, $fieldName)
{
    if (class_exists('CURLFile')) {
        return array($fieldName => new CURLFile($filePath));
    }

    // Legacy PHP cURL upload format.
    return array($fieldName => '@' . $filePath);
}

function decode_via_qrserver($filePath)
{
    if (!function_exists('curl_init')) {
        return '';
    }

    $ch = curl_init('https://api.qrserver.com/v1/read-qr-code/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, create_curl_file_compat($filePath, 'file'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!is_string($response) || trim($response) === '') {
        return '';
    }

    $json = json_decode($response, true);
    if (!is_array($json) || !isset($json[0]['symbol'][0]['data'])) {
        return '';
    }

    $data = (string) $json[0]['symbol'][0]['data'];
    return trim($data);
}

function decode_via_zxing_web($filePath)
{
    if (!function_exists('curl_init')) {
        return '';
    }

    $ch = curl_init('https://zxing.org/w/decode');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, create_curl_file_compat($filePath, 'f'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!is_string($response) || trim($response) === '' || stripos($response, 'No Barcode Found') !== false) {
        return '';
    }

    if (preg_match('/<pre[^>]*>(.*?)<\/pre>/is', $response, $m)) {
        return trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
    }
    if (preg_match('/<td[^>]*>Parsed Result<\/td>\s*<td[^>]*>(.*?)<\/td>/is', $response, $m2)) {
        return trim(html_entity_decode(strip_tags($m2[1]), ENT_QUOTES, 'UTF-8'));
    }

    return '';
}

function decode_with_external_services($variantFiles)
{
    $checked = array_slice($variantFiles, 0, 8);
    foreach ($checked as $filePath) {
        if (!file_exists($filePath)) {
            continue;
        }

        $qrServer = decode_via_qrserver($filePath);
        if ($qrServer !== '') {
            return array('success' => true, 'text' => $qrServer, 'decoder_path' => 'external_qrserver');
        }

        $zxing = decode_via_zxing_web($filePath);
        if ($zxing !== '') {
            return array('success' => true, 'text' => $zxing, 'decoder_path' => 'external_zxing_web');
        }
    }

    return array('success' => false, 'text' => '', 'decoder_path' => 'external_failed');
}

$tempDir = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'qr_tmp';
if (!is_dir($tempDir)) {
    @mkdir($tempDir, 0755, true);
}

$results = array();
$summary = array(
    'total_samples' => count($sampleFiles),
    'success' => 0,
    'failed' => 0,
    'decoder_path_counts' => array()
);

foreach ($sampleFiles as $sample) {
    $variants = array();
    $record = array(
        'file' => str_replace($root . DIRECTORY_SEPARATOR, '', $sample),
        'success' => false,
        'decoder_path' => '',
        'text_preview' => ''
    );

    $variants = build_qr_variants($sample, $tempDir);
    $scan = decode_variants($variants);

    if (!$scan['success'] && $withExternal) {
        $scan = decode_with_external_services($variants);
    }

    if ($scan['success']) {
        $record['success'] = true;
        $record['decoder_path'] = $scan['decoder_path'];
        $record['text_preview'] = function_exists('mb_substr')
            ? mb_substr($scan['text'], 0, 120)
            : substr($scan['text'], 0, 120);
        $summary['success']++;
        if (!isset($summary['decoder_path_counts'][$scan['decoder_path']])) {
            $summary['decoder_path_counts'][$scan['decoder_path']] = 0;
        }
        $summary['decoder_path_counts'][$scan['decoder_path']]++;
    } else {
        $record['decoder_path'] = 'failed';
        $summary['failed']++;
        if (!isset($summary['decoder_path_counts']['failed'])) {
            $summary['decoder_path_counts']['failed'] = 0;
        }
        $summary['decoder_path_counts']['failed']++;
    }

    foreach ($variants as $v) {
        if ($v !== $sample && file_exists($v)) {
            @unlink($v);
        }
    }

    $results[] = $record;
}

$summary['success_rate_percent'] = round(($summary['success'] / max(1, $summary['total_samples'])) * 100, 2);

$output = array(
    'generated_at' => date('c'),
    'pattern' => $patternInput,
    'with_external' => $withExternal,
    'summary' => $summary,
    'results' => $results
);

if (defined('JSON_UNESCAPED_SLASHES')) {
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} else {
    echo json_encode($output) . PHP_EOL;
}
