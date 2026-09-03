<?php
// Small helper utilities for templates

function esc($str){
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function safe_url($url){
    if(!$url) return false;
    $url = trim($url);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
}

function youtube_embed_url($url){
    if(!$url) return false;
    // extract YouTube video id
    $patterns = [
        '/youtube\.com\/shorts\/([\w-]{11})/', // YouTube shorts
        '/youtu\.be\/([\w-]{11})/',
        '/v=([\w-]{11})/',
        '/embed\/([\w-]{11})/',
        '/watch\?v=([\w-]{11})/'
    ];
    foreach($patterns as $p){
        if(preg_match($p, $url, $m)){
            $id = $m[1];
            return 'https://www.youtube.com/embed/' . $id . '?rel=0';
        }
    }
    return false;
}

/* CSRF helpers */
function ensure_session(){
    if(session_status() !== PHP_SESSION_ACTIVE){
        session_start();
    }
}

function csrf_token(){
    ensure_session();
    if(empty($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(){
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return '<input type="hidden" name="_csrf" value="' . $token . '">';
}

function verify_csrf($token): bool {
    ensure_session();
    if(empty($token)) return false;
    return hash_equals($_SESSION['csrf_token'] ?? '', (string)$token);
}

/* File upload and path helpers */
function uploads_dir(){
    return realpath(__DIR__ . '/../uploads') ?: __DIR__ . '/../uploads';
}

function is_safe_local_path($path){
    if(!$path) return false;
    // Only allow paths under uploads directory when stored locally
    $uploads = uploads_dir();
    $relativePath = ltrim((string)$path, '/');
    if (str_starts_with($relativePath, 'uploads/')) {
        $relativePath = substr($relativePath, strlen('uploads/'));
    }
    $candidate = realpath($uploads . '/' . $relativePath);
    if($candidate === false) return false;
    return strpos($candidate, realpath($uploads)) === 0;
}

function ensure_table_columns(PDO $pdo, string $table, array $columns): void {
    $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

    try {
        // MySQL uses DATABASE() instead of current_schema()
        $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$safeTable'");
        $existing = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        $existingSet = array_fill_keys($existing, true);

        foreach ($columns as $name => $type) {
            $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$name);
            if (!isset($existingSet[$column])) {
                // MySQL uses backticks instead of double quotes
                $pdo->exec(sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $safeTable, $column, $type));
            }
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        if (stripos($message, 'must be owner') !== false || stripos($message, '42501') !== false) {
            error_log('Skipping schema migration for ' . $safeTable . ': ' . $message);
            return;
        }
        throw $e;
    }
}

function ensure_projects_table(PDO $pdo): void {
    $pdo->exec('CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(500) NOT NULL,
        description TEXT,
        publication_date DATE DEFAULT NULL,
        pdf_file VARCHAR(255),
        pdf_file_name VARCHAR(255),
        cover_image VARCHAR(255),
        caption TEXT,
        allow_download BOOLEAN NOT NULL DEFAULT TRUE,
        allow_print BOOLEAN NOT NULL DEFAULT TRUE,
        viewer_enabled BOOLEAN NOT NULL DEFAULT TRUE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL
    )');
}

function safe_upload(array $file, array $opts = []){
    // opts: allowed_mimes (array), max_size (bytes)
    $allowed = $opts['allowed_mimes'] ?? ['application/pdf','image/jpeg','image/png','image/webp'];
    $max = $opts['max_size'] ?? 5 * 1024 * 1024; // 5MB

    if(empty($file) || !isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    if((int)$file['size'] > $max) return false;

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if(!in_array($mime, $allowed, true)) return false;

    $map = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    ];
    $ext = strtolower((string)($map[$mime] ?? pathinfo($file['name'], PATHINFO_EXTENSION)));
    if($ext === '') return false;

    $uploads = uploads_dir();
    if(!is_dir($uploads)) mkdir($uploads, 0755, true);

    $name = bin2hex(random_bytes(16));
    $target = $uploads . '/' . $name . '.' . $ext;
    if(!move_uploaded_file($file['tmp_name'], $target)) return false;

    return '/uploads/' . basename($target);
}

function allowed_document_mimes(): array {
    return [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];
}

function allowed_image_mimes(): array {
    return ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
}

function allowed_video_mimes(): array {
    return ['video/mp4', 'video/webm', 'video/quicktime'];
}

function merge_allowed_mimes(array ...$groups): array {
    $merged = [];
    foreach ($groups as $group) {
        foreach ($group as $mime) {
            $merged[$mime] = true;
        }
    }
    return array_keys($merged);
}

function file_extension_from_path(string $path): string {
    return strtolower((string)pathinfo(parse_url($path, PHP_URL_PATH) ?? $path, PATHINFO_EXTENSION));
}

function file_kind_from_path(?string $path): string {
    if (!has_text($path)) {
        return 'unknown';
    }

    $ext = file_extension_from_path((string)$path);
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
        return 'image';
    }
    if (in_array($ext, ['mp4', 'webm', 'mov'], true)) {
        return 'video';
    }
    if (in_array($ext, ['pdf'], true)) {
        return 'pdf';
    }
    if (in_array($ext, ['doc', 'docx'], true)) {
        return 'document';
    }
    if (in_array($ext, ['ppt', 'pptx'], true)) {
        return 'presentation';
    }

    return 'file';
}

function file_label_from_path(?string $path): string {
    $kind = file_kind_from_path($path);
    return match ($kind) {
        'image' => 'Image',
        'video' => 'Video',
        'pdf' => 'PDF',
        'document' => 'Document',
        'presentation' => 'Presentation',
        default => 'File',
    };
}

function sanitize_original_filename($name): string {
    $name = trim((string)$name);
    if ($name === '') {
        return '';
    }

    $name = basename(str_replace('\\', '/', $name));
    $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? '';

    if (strlen($name) > 140) {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);
        $extPart = $ext !== '' ? ('.' . $ext) : '';
        $base = substr($base, 0, max(1, 140 - strlen($extPart)));
        $name = $base . $extPart;
    }

    return $name;
}

function resolved_file_display_name($originalName, string $path): string {
    if (has_text($originalName)) {
        return sanitize_original_filename($originalName);
    }

    $ext = file_extension_from_path($path);
    $label = file_label_from_path($path);

    return $ext !== '' ? ($label . ' file.' . $ext) : ($label . ' file');
}

function truncate_filename_for_display(string $name, int $max = 34): string {
    if (mb_strlen($name) <= $max) {
        return $name;
    }

    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $base = pathinfo($name, PATHINFO_FILENAME);
    $extPart = $ext !== '' ? ('.' . $ext) : '';
    $baseMax = max(6, $max - mb_strlen($extPart) - 1);

    return mb_substr($base, 0, $baseMax) . '…' . $extPart;
}

function file_icon_svg(): string {
    return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7 2.25h6.19c.4 0 .78.16 1.06.44l4.06 4.06c.28.28.44.66.44 1.06V19.5A2.25 2.25 0 0 1 16.5 21.75h-9A2.25 2.25 0 0 1 5.25 19.5V4.5A2.25 2.25 0 0 1 7 2.25Z" fill="currentColor" fill-opacity="0.16"/><path d="M7 2.25h6.19c.4 0 .78.16 1.06.44l4.06 4.06c.28.28.44.66.44 1.06V19.5A2.25 2.25 0 0 1 16.5 21.75h-9A2.25 2.25 0 0 1 5.25 19.5V4.5A2.25 2.25 0 0 1 7 2.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M13.5 2.4V6a1.5 1.5 0 0 0 1.5 1.5h3.6" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M8.4 13.1h7.2M8.4 16.3h5.1M8.4 9.9h3.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
}

function has_text($value): bool {
    return trim((string)$value) !== '';
}

/* PHP upload/post size-limit detection helpers */
function parse_ini_size_to_bytes(string $value): int {
    $value = trim($value);
    if ($value === '') {
        return 0;
    }

    $unit = strtolower(substr($value, -1));
    $number = (float)$value;

    switch ($unit) {
        case 'g':
            return (int)($number * 1024 * 1024 * 1024);
        case 'm':
            return (int)($number * 1024 * 1024);
        case 'k':
            return (int)($number * 1024);
        default:
            return (int)$value;
    }
}

function post_max_size_bytes(): int {
    return parse_ini_size_to_bytes((string)ini_get('post_max_size'));
}

function upload_max_filesize_bytes(): int {
    return parse_ini_size_to_bytes((string)ini_get('upload_max_filesize'));
}

function format_bytes_human(int $bytes): string {
    if ($bytes <= 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $power = (int)floor(log($bytes, 1024));
    $power = max(0, min($power, count($units) - 1));
    $value = $bytes / (1024 ** $power);

    return round($value, $value < 10 ? 1 : 0) . ' ' . $units[$power];
}

/**
 * Detects the classic PHP failure mode where an uploaded request's total
 * size exceeds `post_max_size`. In that case PHP empties $_POST and
 * $_FILES before the script ever runs, which otherwise surfaces as a
 * confusing, unrelated "required field missing" error.
 */
function post_exceeds_size_limit(): bool {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return false;
    }

    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
    if ($contentLength <= 0) {
        return false;
    }

    $maxPost = post_max_size_bytes();
    if ($maxPost <= 0) {
        return false;
    }

    return $contentLength > $maxPost && empty($_POST) && empty($_FILES);
}

function post_size_limit_message(): string {
    return 'The file(s) you tried to upload are too large for this server. '
        . 'The current limit is ' . format_bytes_human(post_max_size_bytes()) . ' per submission '
        . '(individual files are also capped at ' . format_bytes_human(upload_max_filesize_bytes()) . '). '
        . 'Please upload a smaller file, or ask the site administrator to increase the server\'s '
        . '`post_max_size` and `upload_max_filesize` limits.';
}

/* Admin dashboard icon set (inline SVG, Feather-style, stroke-based) */
function admin_icon(string $name): string {
    $body = [
        'overview' => '<rect x="3" y="3" width="7.5" height="7.5" rx="1.6"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.6"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.6"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.6"/>',
        'lecturers' => '<path d="M12 3 2 8.2l10 5.1 10-5.1L12 3Z"/><path d="M6 10.6V16c0 1.66 2.69 3 6 3s6-1.34 6-3v-5.4"/>',
        'publications' => '<path d="M4 5.2A2.2 2.2 0 0 1 6.2 3H20v15.6H6.2A2.2 2.2 0 0 0 4 20.8V5.2Z"/><path d="M4 18.8A2.2 2.2 0 0 1 6.2 16.6H20"/>',
        'consultancy' => '<rect x="2.2" y="4.2" width="19.6" height="13" rx="2"/><path d="M7.5 20.8h9M12 17.2v3.6"/>',
        'messages' => '<rect x="2.4" y="4.6" width="19.2" height="14.8" rx="2.4"/><path d="m3 6.4 8.4 6c.35.25.85.25 1.2 0l8.4-6"/>',
        'logout' => '<path d="M9 21H5.2A2.2 2.2 0 0 1 3 18.8V5.2A2.2 2.2 0 0 1 5.2 3H9"/><path d="m15.5 16.5 4.5-4.5-4.5-4.5"/><path d="M20 12H9"/>',
        'view' => '<path d="M2 12s3.6-7.2 10-7.2S22 12 22 12s-3.6 7.2-10 7.2S2 12 2 12Z"/><circle cx="12" cy="12" r="3.1"/>',
        'add' => '<circle cx="12" cy="12" r="9.4"/><path d="M12 7.8v8.4M7.8 12h8.4"/>',
        'edit' => '<path d="M4 20.5 4.7 17 16 5.7a2 2 0 0 1 2.8 0l.6.6a2 2 0 0 1 0 2.8L8.1 20.4 4 20.5Z"/><path d="m14.5 7.4 2.1 2.1"/>',
        'delete' => '<path d="M4 7h16M9.2 7V4.9c0-.5.4-.9.9-.9h3.8c.5 0 .9.4.9.9V7M6.3 7l.9 12.9a2 2 0 0 0 2 1.9h5.6a2 2 0 0 0 2-1.9L17.7 7"/>',
        'menu' => '<path d="M3.5 6.5h17M3.5 12h17M3.5 17.5h17"/>',
        'close' => '<path d="M5.5 5.5 18.5 18.5M18.5 5.5 5.5 18.5"/>',
        'chevron' => '<path d="m9 5 7 7-7 7"/>',
        'calendar' => '<rect x="3" y="4.6" width="18" height="16" rx="2.2"/><path d="M3 9.6h18M8 3v3.2M16 3v3.2"/>',
        'inbox' => '<path d="M3 12h4.6l1.5 3h5.8l1.5-3H21"/><path d="M5.6 5h12.8l2.6 7v6.4A1.6 1.6 0 0 1 19.4 20H4.6A1.6 1.6 0 0 1 3 18.4V12L5.6 5Z"/>',
        'external' => '<path d="M14 5h5v5"/><path d="M19 5 10.2 13.8"/><path d="M18 13.6V19a1.6 1.6 0 0 1-1.6 1.6H6a1.6 1.6 0 0 1-1.6-1.6V8a1.6 1.6 0 0 1 1.6-1.6h5.4"/>',
        'check' => '<path d="m5 12.5 4.8 4.8L19.5 7"/>',
        'user' => '<circle cx="12" cy="8.2" r="3.9"/><path d="M4.6 20a7.4 7.4 0 0 1 14.8 0"/>',
        'stats' => '<path d="M4 20V10M11 20V4M18 20v-7"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m20 20-4.4-4.4"/>',
        'shield' => '<path d="M12 3.2 4.5 6v6c0 5 3.4 7.9 7.5 9 4.1-1.1 7.5-4 7.5-9V6L12 3.2Z"/>',
    ];

    if (!isset($body[$name])) {
        return '';
    }

    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body[$name] . '</svg>';
}

function admin_icon_html(string $name, string $class = ''): string {
    $svg = admin_icon($name);
    if ($svg === '') {
        return '';
    }

    if ($class !== '') {
        $safeClass = htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $svg = preg_replace('/<svg /', '<svg class="' . $safeClass . '" ', $svg, 1) ?? $svg;
    }

    return $svg;
}

function format_display_date($value): string {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('F j, Y', $timestamp);
}
