<?php
// Send conservative security headers where possible
if(!headers_sent()){
  header('X-Frame-Options: SAMEORIGIN');
  header('X-Content-Type-Options: nosniff');
  header("Referrer-Policy: no-referrer-when-downgrade");
  // Content-Security-Policy: allow self, https, Google Fonts and YouTube embeds
  header("Content-Security-Policy: default-src 'self' https:; font-src 'self' https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https:; frame-src https://www.youtube.com https://www.youtube-nocookie.com; connect-src 'self' https:;");
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dr. Agnes Kapinga — Lecturer, Researcher, Consultant</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <meta name="color-scheme" content="dark">
</head>
<body class="bg-primary">
  <div id="site-root">
