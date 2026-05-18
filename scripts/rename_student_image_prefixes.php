<?php

/**
 * Renomme les fichiers images d'étudiants selon les préfixes de matricule.
 *
 * Règles (sur le nom sans extension) :
 *   AGRGEM*  -> MAGM*
 *   AGRGPC*  -> MAGPC*
 *   LEI*     -> LSI*
 *
 * Usage :
 *   php scripts/rename_student_image_prefixes.php <chemin_vers_dossier> [--dry-run] [--recursive]
 *
 * Exemple :
 *   php scripts/rename_student_image_prefixes.php students-images
 *   php scripts/rename_student_image_prefixes.php C:/wamp64/www/laureatens/students-images --dry-run
 */

$dryRun = in_array('--dry-run', $argv, true);
$recursive = in_array('--recursive', $argv, true) || in_array('-r', $argv, true);

$positional = [];
for ($i = 1, $n = count($argv); $i < $n; $i++) {
    if ($argv[$i] === '--dry-run' || $argv[$i] === '--recursive' || $argv[$i] === '-r') {
        continue;
    }
    if (strncmp($argv[$i], '--', 2) === 0) {
        fwrite(STDERR, "Option inconnue: {$argv[$i]}\n");
        exit(1);
    }
    $positional[] = $argv[$i];
}

$targetDir = $positional[0] ?? '';
if ($targetDir === '') {
    fwrite(STDERR, "Usage: php ".basename(__FILE__)." <dossier> [--dry-run] [--recursive]\n");
    exit(1);
}

$resolved = realpath($targetDir);
if ($resolved === false || !is_dir($resolved)) {
    fwrite(STDERR, "Dossier introuvable: {$targetDir}\n");
    exit(1);
}

/** @var list<array{prefix: string, replace: string}> $rules */
$rules = [
    ['prefix' => 'AGRGPC', 'replace' => 'MAGPC'],
    ['prefix' => 'AGRGEM', 'replace' => 'MAGM'],
    ['prefix' => 'LEI', 'replace' => 'LSI'],
];

$imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

/**
 * @return list<string>
 */
function collect_image_files(string $dir, bool $recursive, array $imageExtensions): array
{
    $out = [];
    $items = @scandir($dir);
    if ($items === false) {
        return $out;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir.DIRECTORY_SEPARATOR.$item;
        if (is_dir($path)) {
            if ($recursive) {
                $out = array_merge($out, collect_image_files($path, true, $imageExtensions));
            }

            continue;
        }
        if (!is_file($path)) {
            continue;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, $imageExtensions, true)) {
            $out[] = $path;
        }
    }

    return $out;
}

/**
 * Applique la première règle dont le préfixe correspond (comparaison sur nom en majuscules).
 */
function apply_prefix_rules(string $stem, array $rules): ?string
{
    $upper = strtoupper($stem);
    foreach ($rules as $r) {
        $p = $r['prefix'];
        $len = strlen($p);
        if ($len > 0 && strncmp($upper, $p, $len) === 0) {
            return $r['replace'].substr($stem, $len);
        }
    }

    return null;
}

$files = collect_image_files($resolved, $recursive, $imageExtensions);
$renamed = 0;
$skipped = 0;
$unchanged = 0;

foreach ($files as $path) {
    $basename = basename($path);
    $dir = dirname($path);
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $stem = pathinfo($path, PATHINFO_FILENAME);

    $newStem = apply_prefix_rules($stem, $rules);
    if ($newStem === null || $newStem === $stem) {
        $unchanged++;

        continue;
    }

    $newBasename = $newStem.($ext !== '' ? '.'.$ext : '');
    $dest = $dir.DIRECTORY_SEPARATOR.$newBasename;

    if (strcasecmp($basename, $newBasename) === 0) {
        $unchanged++;

        continue;
    }

    if (file_exists($dest)) {
        fwrite(STDERR, "[SKIP] Cible existe déjà: {$newBasename} (source: {$basename})\n");
        $skipped++;

        continue;
    }

    if ($dryRun) {
        echo "[DRY-RUN] {$basename} -> {$newBasename}\n";
        $renamed++;

        continue;
    }

    if (!@rename($path, $dest)) {
        fwrite(STDERR, "[ERREUR] rename échoué: {$basename}\n");
        $skipped++;

        continue;
    }

    echo "{$basename} -> {$newBasename}\n";
    $renamed++;
}

echo "\nTerminé. Renommés: {$renamed}, inchangés: {$unchanged}, ignorés/erreurs: {$skipped}";
if ($dryRun) {
    echo ' (simulation)';
}
echo "\n";
