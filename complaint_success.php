<?php
// complaint_success.php (Civic Clarity UI)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="refresh" content="4;url=dashboard.php" />
<title>Civic Clarity - Complaint Registered</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = { darkMode: "class", theme: { extend: { colors: {
            "primary-container": "#1a73e8", "primary": "#005bbf", "on-primary": "#ffffff",
            "surface-container-lowest": "#ffffff", "surface": "#f8f9fa", "on-surface": "#191c1d",
            "on-surface-variant": "#414754", "surface-container-highest": "#e1e3e4"
      }, fontFamily: { "headline": ["Inter"], "body": ["Inter"], "label": ["Inter"] } } } }
</script>
<style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { font-family: 'Inter', sans-serif; min-height: max(300px, 100dvh); }
</style>
</head>
<body class="bg-surface text-on-surface">
<header class="fixed top-0 w-full z-50 bg-slate-50/80 backdrop-blur-xl flex items-center justify-between px-6 h-16 shadow-sm">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-blue-700">account_balance</span>
<span class="text-xl font-bold tracking-tighter text-blue-700">Civic Clarity</span>
</div>
</header>

<main class="pt-24 pb-32 px-4 max-w-2xl mx-auto flex flex-col items-center justify-center text-center mt-6">
    <div class="w-full mb-12 text-left">
        <div class="flex items-center justify-between mb-4">
            <span class="text-label-md font-bold uppercase tracking-widest text-primary">Step 2 of 2</span>
            <span class="text-on-surface-variant text-sm font-medium">Complaint in progress</span>
        </div>
        <div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
            <div class="h-full w-full bg-primary transition-all duration-500"></div>
        </div>
    </div>

    <div class="bg-surface-container-lowest p-10 rounded-2xl border border-gray-200 shadow-[0_8px_40px_rgba(0,0,0,0.04)] w-full">
        <span class="material-symbols-outlined text-6xl text-green-600 mb-4 inline-block">check_circle</span>
        <h1 class="text-4xl font-extrabold tracking-tight text-on-surface mb-4">Complaint Registered</h1>
        <p class="text-on-surface-variant text-lg mb-8">Your complaint has been successfully submitted to the authorities. You will be redirected to your homepage shortly.</p>
        
        <a href="dashboard.php" class="inline-block bg-primary text-on-primary px-8 py-3 rounded-lg font-bold tracking-tight hover:bg-blue-700 transition-all">
            Return to Homepage Now
        </a>
    </div>
</main>
</body></html>
