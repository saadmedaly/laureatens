<?php
/**
 * Génère images/signature_officielle.png à partir de signature_officielle.jpeg
 * en rendant transparents le blanc (fond scan) et le noir pur (export incorrect).
 *
 * Usage (navigateur, en local uniquement) :
 *   http://localhost/githubversion/laureatens/generate_signature_png.php
 *
 * Puis supprimez ce fichier si vous voulez éviter qu’il soit rappelé par erreur.
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($ip !== '127.0.0.1' && $ip !== '::1') {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Accès réservé à localhost.';
        exit;
    }
}

if (!extension_loaded('gd')) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Extension PHP GD requise (activez extension=gd dans php.ini).';
    exit(1);
}

$base = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
$srcJpeg = $base . 'signature_officielle.jpeg';
$srcJpg  = $base . 'signature_officielle.jpg';
$srcPath = is_file($srcJpeg) ? $srcJpeg : (is_file($srcJpg) ? $srcJpg : '');

if ($srcPath === '') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Aucun fichier source : images/signature_officielle.jpeg ou .jpg';
    exit(1);
}

$data = file_get_contents($srcPath);
if ($data === false) {
    echo 'Lecture impossible.';
    exit(1);
}

$src = @imagecreatefromstring($data);
if ($src === false) {
    echo 'Image illisible (format non supporté).';
    exit(1);
}

$w = imagesx($src);
$h = imagesy($src);

$dst = imagecreatetruecolor($w, $h);
imagealphablending($dst, false);
imagesavealpha($dst, true);
$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
imagefilledrectangle($dst, 0, 0, $w, $h, $transparent);
imagealphablending($dst, true);

// Seuils : blanc papier / noir « fond » après export ; on préserve l’encre bleue (B dominant)
$whiteMin = 218;
$blackMax = 42;

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($src, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        $isWhite = ($r >= $whiteMin && $g >= $whiteMin && $b >= $whiteMin);
        // Noir ou gris très sombre sans dominance bleue (évite d’effacer le bleu du cachet)
        $isBlackBg = ($r <= $blackMax && $g <= $blackMax && $b <= $blackMax)
            || ($r + $g + $b < 95 && $b <= $r + 25);

        if ($isWhite || $isBlackBg) {
            continue; // reste transparent
        }

        $a = 0;
        $col = imagecolorallocatealpha($dst, $r, $g, $b, $a);
        imagesetpixel($dst, $x, $y, $col);
    }
}

imagealphablending($dst, false);
imagesavealpha($dst, true);

$out = $base . 'signature_officielle.png';
if (!imagepng($dst, $out, 6)) {
    imagedestroy($src);
    imagedestroy($dst);
    echo 'Écriture PNG impossible (droits du dossier images ?).';
    exit(1);
}

imagedestroy($src);
imagedestroy($dst);

header('Content-Type: text/plain; charset=UTF-8');
echo "OK : fichier créé :\n{$out}\n\n";
echo "Rechargez la carte étudiant : images/signature.png est utilisé en priorité (puis signature_officielle.*, sign_catre.*).\n";
echo "Vous pouvez supprimer generate_signature_png.php après usage.\n";
