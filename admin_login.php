<?php
// admin_login.php (Civic Clarity UI)
session_start();

// If already admin, go to dashboard
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: admin.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['admin_username'] ?? '';
    $password = $_POST['admin_password'] ?? '';
    
    // Strict static check as requested
    if ($username === 'government' && $password === 'jai hind') {
        $_SESSION['is_admin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid Administrative credentials.";
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Civic Clarity - Admin Gateway</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = { darkMode: "class", theme: { extend: { colors: {
        "primary": "#005bbf", "on-primary": "#ffffff", "surface": "#f8f9fa",
        "on-surface": "#191c1d", "surface-container-highest": "#e1e3e4",
        "error-container": "#ffdad6", "on-error-container": "#93000a",
        "outline": "#727785"
    }, fontFamily: { "headline": ["Inter"], "body": ["Inter"], "label": ["Inter"] } } } }
</script>
<style> body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; } </style>
</head>
<body class="bg-surface text-on-surface flex items-center justify-center min-h-screen">
<main class="w-full max-w-md px-6">
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-2xl mb-4 text-blue-700">
            <span class="material-symbols-outlined text-4xl">admin_panel_settings</span>
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-800">Gov Portal</h1>
        <p class="text-slate-500 text-sm mt-2 font-medium">Restricted access database gateway.</p>
    </div>

    <?php if($error): ?>
        <div class="mb-4 bg-error-container text-on-error-container p-4 rounded-xl text-sm font-bold shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">gpp_bad</span>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="admin_login.php" method="POST" class="space-y-6">
        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Admin Username</label>
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-4 text-outline z-10">shield_person</span>
                <input name="admin_username" type="text" class="w-full bg-surface-container-highest border-none rounded-xl pl-12 pr-4 py-4 font-medium focus:ring-2 focus:ring-primary focus:bg-white transition-all text-on-surface" placeholder="Enter username" required/>
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Security Passcode</label>
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-4 text-outline z-10">key</span>
                <input name="admin_password" type="password" class="w-full bg-surface-container-highest border-none rounded-xl pl-12 pr-4 py-4 font-medium focus:ring-2 focus:ring-primary focus:bg-white transition-all text-on-surface" placeholder="Enter password" required/>
            </div>
        </div>

        <button class="w-full bg-slate-800 text-white hover:bg-black py-4 rounded-xl font-bold tracking-wide active:scale-95 transition-all flex items-center justify-center gap-2" type="submit">
            Authenticiate
            <span class="material-symbols-outlined text-lg">login</span>
        </button>
    </form>
    
    <div class="mt-8 text-center">
        <a href="login.php" class="text-sm font-semibold text-primary hover:underline">Return to Citizen Login</a>
    </div>
</main>
</body></html>
