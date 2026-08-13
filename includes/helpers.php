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
