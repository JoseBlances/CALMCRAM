<!DOCTYPE html>
<html lang="en" id="app-root" data-mood="stable">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="CramCalm — Your emotionally intelligent study companion. Avoid cramming, reduce stress, and ace your academics.">
    <meta name="theme-color" content="#6B9080">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <title><?= htmlspecialchars($pageTitle ?? 'CramCalm') ?> | CramCalm</title>

    <!-- Tailwind CSS v4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Lexend:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind v4 Theme -->
    <style type="text/tailwindcss">
        @theme {
            --font-heading: "Nunito", system-ui, sans-serif;
            --font-body: "Lexend", system-ui, sans-serif;
            --color-calm-bg: #F0F7F4;
            --color-calm-surface: #CCE3DE;
            --color-calm-primary: #6B9080;
            --color-calm-accent: #A4C3B2;
            --color-calm-text: #2D3436;
            --color-calm-muted: #636E72;
            --color-pressure-bg: #FFF8F0;
            --color-pressure-surface: #FFE8D6;
            --color-pressure-primary: #CB997E;
            --color-pressure-accent: #DDBEA9;
            --color-crisis-bg: #F0E6F6;
            --color-crisis-surface: #E2D1F0;
            --color-crisis-primary: #9B72AA;
            --color-crisis-accent: #C9A7D8;
            --color-burnout-bg: #F5F0EB;
            --color-burnout-surface: #EAE0D5;
            --color-burnout-primary: #A89F91;
            --color-burnout-accent: #D4A5A5;
        }
    </style>

    <!-- App CSS -->
    <link rel="stylesheet" href="<?= asset_url('css/animations.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/states.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('css/components.css') ?>">
</head>
<body class="font-body antialiased min-h-screen transition-colors duration-700 overflow-x-hidden">
    <!-- Atmosphere layer (background effects) -->
    <div id="atmosphere" class="fixed inset-0 pointer-events-none z-0 transition-opacity duration-1000"></div>
    
    <!-- Notification container -->
    <div id="toast-container" class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] flex flex-col gap-2 w-[90vw] max-w-sm pointer-events-none"></div>

    <!-- Main content wrapper -->
    <main id="main-content" class="relative z-10 pb-24 min-h-screen">
