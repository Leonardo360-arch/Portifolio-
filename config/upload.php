<?php
declare(strict_types=1);

const IMG_MAX_BYTES = 2 * 1024 * 1024;   // limite do WebP final (spec)

function save_image_webp(array $f): string
{
    if (!function_exists('imagewebp')) { throw new RuntimeException('A extensão GD com suporte a WebP não está ativa neste PHP.'); }
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) { throw new RuntimeException('Falha no envio da imagem.'); }
    if ($f['size'] > 8 * 1024 * 1024) { throw new RuntimeException('Imagem muito grande (máximo de 8 MB no arquivo original).'); }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $img = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($f['tmp_name']),
        'image/png'  => @imagecreatefrompng($f['tmp_name']),
        'image/webp' => @imagecreatefromwebp($f['tmp_name']),
        default      => false,
    };
    if (!$img) { throw new RuntimeException('Envie uma imagem JPG, PNG ou WebP válida.'); }
    imagepalettetotruecolor($img); imagealphablending($img, true); imagesavealpha($img, true);
    if (imagesx($img) > 1600) { $img = imagescale($img, 1600); }

    $rel  = 'uploads/' . bin2hex(random_bytes(8)) . '.webp';
    $dest = dirname(__DIR__) . '/' . $rel;
    foreach ([82, 70, 55, 40] as $q) {
        imagewebp($img, $dest, $q); clearstatcache(true, $dest);
        if (filesize($dest) <= IMG_MAX_BYTES) { imagedestroy($img); return $rel; }
    }
    imagedestroy($img); @unlink($dest);
    throw new RuntimeException('Não foi possível reduzir a imagem para menos de 2 MB.');
}

function delete_upload(?string $rel): void
{
    if ($rel && preg_match('#^uploads/[a-f0-9]{16}\.webp$#', $rel)) { @unlink(dirname(__DIR__) . '/' . $rel); }
}

function save_pdf(array $f): string
{
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) { throw new RuntimeException('Falha no envio do PDF.'); }
    if ($f['size'] > 5 * 1024 * 1024) { throw new RuntimeException('PDF muito grande (máximo de 5 MB).'); }
    if ((new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) !== 'application/pdf') { throw new RuntimeException('O arquivo precisa ser um PDF.'); }
    if (!move_uploaded_file($f['tmp_name'], dirname(__DIR__) . '/uploads/curriculo.pdf')) { throw new RuntimeException('Não foi possível salvar o PDF.'); }
    return 'uploads/curriculo.pdf';
}
