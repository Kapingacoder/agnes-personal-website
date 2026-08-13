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
    $candidate = realpath($uploads . '/' . ltrim($path, '/'));
    if($candidate === false) return false;
    return strpos($candidate, realpath($uploads)) === 0;
}

function safe_upload(array $file, array $opts = []){
    // opts: allowed_mimes (array), max_size (bytes)
    $allowed = $opts['allowed_mimes'] ?? ['application/pdf','image/jpeg','image/png','image/webp'];
    $max = $opts['max_size'] ?? 5 * 1024 * 1024; // 5MB
    if(empty($file) || $file['error'] !== UPLOAD_ERR_OK) return false;
    if($file['size'] > $max) return false;
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if(!in_array($mime, $allowed, true)) return false;
    $ext = array_search($mime, ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'], true);
    $uploads = uploads_dir();
    if(!is_dir($uploads)) mkdir($uploads, 0750, true);
    $name = bin2hex(random_bytes(16));
    // Determine extension
    $map = ['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $ext = $map[$mime] ?? '';
    $target = $uploads . '/' . $name . ($ext ? '.' . $ext : '');
    if(!move_uploaded_file($file['tmp_name'], $target)) return false;
    return str_replace(realpath(__DIR__ . '/..') . '/', '', realpath($target));
}
