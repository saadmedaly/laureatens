<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$root = dirname(__DIR__);
$options = getopt('', [
    'source::',
    'dest::',
    'dry-run',
    'overwrite',
    'help',
]);

if (isset($options['help'])) {
    echo <<<TXT
Usage:
  php scripts/normalize_student_photos.php [--source=photos] [--dest=photos] [--dry-run] [--overwrite]

Copies student images to normalized names using the guessed matricule:
  "L1 AR 2445.jpg"      => "LAR2445.jpg"
  "EFL L2 5305401906"   => "LEFL5305401906.jpg"
  "M.FLE2509.jpg"       => "MFLE2509.jpg"
  "AGRG PC 2501.jpg"    => "AGRGPC2501.jpg"

Options:
  --source      Folder to scan. Defaults to photos.
  --dest        Folder where normalized copies are written. Defaults to photos.
  --dry-run     Show what would be copied without writing files.
  --overwrite   Replace existing normalized files.

TXT;
    exit(0);
}

$sourceDir = pathFromOption($root, isset($options['source']) ? $options['source'] : 'photos');
$destDir = pathFromOption($root, isset($options['dest']) ? $options['dest'] : 'photos');
$dryRun = isset($options['dry-run']);
$overwrite = isset($options['overwrite']);
$imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

if (!is_dir($sourceDir)) {
    fwrite(STDERR, "Source folder not found: {$sourceDir}\n");
    exit(1);
}

if (!is_dir($destDir)) {
    fwrite(STDERR, "Destination folder not found: {$destDir}\n");
    exit(1);
}

$stats = [
    'copied' => 0,
    'would_copy' => 0,
    'skipped_existing' => 0,
    'skipped_normalized' => 0,
    'skipped_unknown' => 0,
    'errors' => 0,
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $extension = strtolower($file->getExtension());

    if (!in_array($extension, $imageExtensions, true)) {
        continue;
    }

    if (strtolower($file->getBasename()) === 'default.gif') {
        continue;
    }

    $matricule = guessMatricule($file->getBasename('.' . $file->getExtension()));

    if ($matricule === null) {
        $stats['skipped_unknown']++;
        echo "[unknown] {$file->getPathname()}\n";
        continue;
    }

    $target = $destDir . DIRECTORY_SEPARATOR . $matricule . '.' . $extension;

    if (samePath($file->getPathname(), $target)) {
        $stats['skipped_normalized']++;
        continue;
    }

    if (file_exists($target) && !$overwrite) {
        $stats['skipped_existing']++;
        echo "[exists] {$target}\n";
        continue;
    }

    if ($dryRun) {
        $stats['would_copy']++;
        echo "[dry-run] {$file->getPathname()} => {$target}\n";
        continue;
    }

    if (!copy($file->getPathname(), $target)) {
        $stats['errors']++;
        fwrite(STDERR, "[error] Could not copy {$file->getPathname()} => {$target}\n");
        continue;
    }

    $stats['copied']++;
    echo "[copied] {$file->getPathname()} => {$target}\n";
}

echo "\nSummary\n";
foreach ($stats as $name => $count) {
    echo str_pad($name . ':', 21) . $count . "\n";
}

exit($stats['errors'] > 0 ? 1 : 0);

function pathFromOption($root, $path)
{
    if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path) || substr($path, 0, 1) === DIRECTORY_SEPARATOR) {
        return rtrim($path, DIRECTORY_SEPARATOR . '/\\');
    }

    return rtrim($root . DIRECTORY_SEPARATOR . $path, DIRECTORY_SEPARATOR . '/\\');
}

function samePath($left, $right)
{
    $leftReal = realpath($left);
    $rightReal = realpath($right);

    if ($leftReal === false || $rightReal === false) {
        return false;
    }

    return strtolower($leftReal) === strtolower($rightReal);
}

function guessMatricule($name)
{
    $clean = strtoupper($name);
    $clean = preg_replace('/\(\s*\d+\s*\)$/', '', $clean);
    $clean = preg_replace('/[-_\s]+$/', '', $clean);
    $clean = preg_replace('/([A-Z])([0-9])/', '$1 $2', $clean);
    $clean = preg_replace('/([0-9])([A-Z])/', '$1 $2', $clean);

    preg_match_all('/[A-Z]+|\d+/', $clean, $matches);
    $tokens = $matches[0] ?? [];

    if (empty($tokens)) {
        return null;
    }

    $numberIndex = null;
    $number = null;

    foreach ($tokens as $index => $token) {
        if (ctype_digit($token) && strlen($token) >= 4) {
            $numberIndex = $index;
            $number = $token;
            break;
        }
    }

    if ($numberIndex === null || $number === null) {
        return null;
    }

    $prefixTokens = array_slice($tokens, 0, $numberIndex);
    $hasLicenceLevel = false;
    $letters = [];

    for ($i = 0; $i < count($prefixTokens); $i++) {
        $token = $prefixTokens[$i];

        if ($token === 'L') {
            $hasLicenceLevel = true;
            continue;
        }

        if ($token === '1' || $token === '2' || $token === '3') {
            if ($i > 0 && $prefixTokens[$i - 1] === 'L') {
                $hasLicenceLevel = true;
                continue;
            }
        }

        if (ctype_alpha($token)) {
            $letters[] = $token;
        }
    }

    if (empty($letters)) {
        return null;
    }

    $prefix = implode('', $letters);

    if ($hasLicenceLevel && $prefix[0] !== 'L') {
        $prefix = 'L' . $prefix;
    }

    return preg_replace('/[^A-Z0-9_-]/', '', $prefix . $number);
}
