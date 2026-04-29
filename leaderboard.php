<?php
// leaderboard.php (Civic Clarity UI)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$elite = [];
$gold = [];
$silver = [];
$bronze = [];
$beginner = [];

if(isset($conn) && !$conn->connect_error) {
    // Fetch users and their resolved complaint count
    // Left join to include users with 0 resolved complaints as well
    $query = "
        SELECT u.id, u.full_name, COUNT(c.id) as resolved_count 
        FROM users u 
        INNER JOIN complaints c ON u.id = c.user_id AND c.status = 'Resolved' 
        GROUP BY u.id 
        ORDER BY resolved_count DESC, u.id ASC
        LIMIT 4
    ";
    
    $res = $conn->query($query);
    
    $rank = 1;
    if($res) {
        while($row = $res->fetch_assoc()) {
            if ($rank === 1) {
                $elite = $row;
            } else if ($rank === 2) {
                $gold = $row;
            } else if ($rank === 3) {
                $silver = $row;
            } else if ($rank === 4) {
                $bronze = $row;
            }
            $rank++;
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Civic Clarity - Leaderboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
tailwind.config = { darkMode: "class", theme: { extend: { colors: {
    "primary-container": "#1a73e8", "primary": "#005bbf", "on-primary": "#ffffff",
    "surface-container-lowest": "#ffffff", "surface": "#f8f9fa", "on-surface": "#191c1d",
    "on-surface-variant": "#414754", "surface-container-highest": "#e1e3e4", "outline-variant": "#c1c6d6", "outline": "#727785"
}, fontFamily: { "headline": ["Inter"], "body": ["Inter"], "label": ["Inter"] } } } }
</script>
<style>
    body { font-family: 'Inter', sans-serif; min-height: max(300px, 100dvh); background-color: #f8f9fa; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 600, 'GRAD' 0, 'opsz' 24; }
    .material-symbols-outlined.outline { font-variation-settings: 'FILL' 0; }
</style>
</head>
<body class="text-on-surface pb-32">

<header class="fixed top-0 w-full z-50 bg-white border-b border-outline-variant/20 flex items-center px-6 h-16 shadow-sm">
    <a href="dashboard.php" class="mr-4 p-2 rounded-full hover:bg-surface-container-highest transition-colors">
        <span class="material-symbols-outlined text-on-surface-variant outline">arrow_back</span>
    </a>
    <h1 class="text-xl font-bold tracking-tighter text-primary">Civic Impact Leaderboard</h1>
</header>

<main class="pt-24 px-4 max-w-5xl mx-auto">
    
    <div class="mb-12 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-on-surface mb-4">City Hall of Fame</h1>
        <p class="text-on-surface-variant text-lg max-w-2xl mx-auto">Celebrating the citizens who are making our city a better place. Climb the ranks by submitting reports that lead to real-world district improvements!</p>
    </div>

        <div class="space-y-6 max-w-3xl mx-auto">
            
            <?php if(!empty($elite)): ?>
            <div class="bg-gradient-to-br from-yellow-200 to-yellow-50 border-2 border-yellow-300 rounded-2xl p-6 shadow-md flex flex-col md:flex-row items-center gap-6 transform transition-transform hover:scale-105">
                <div class="w-20 h-20 rounded-full bg-yellow-300 flex items-center justify-center text-yellow-800 font-black text-3xl shadow-inner border-2 border-yellow-400 flex-shrink-0">
                    #1
                </div>
                <div class="flex-grow text-center md:text-left">
                    <h2 class="text-2xl font-black text-yellow-800 mb-1">🏆 Elite Contributor</h2>
                    <h3 class="text-xl font-bold text-on-surface"><?php echo htmlspecialchars($elite['full_name']); ?></h3>
                </div>
                <div class="bg-white px-5 py-3 rounded-xl border border-yellow-200 text-center flex-shrink-0 shadow-sm">
                    <p class="text-2xl font-black text-yellow-600 leading-none"><?php echo $elite['resolved_count']; ?></p>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-yellow-600 mt-1">Resolved</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($gold)): ?>
            <div class="bg-gradient-to-br from-yellow-100 to-white border-2 border-yellow-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row items-center gap-6 transform transition-transform hover:scale-105">
                <div class="w-16 h-16 rounded-full bg-yellow-200 flex items-center justify-center text-yellow-700 font-black text-2xl shadow-inner border-2 border-yellow-300 flex-shrink-0">
                    #2
                </div>
                <div class="flex-grow text-center md:text-left">
                    <h2 class="text-xl font-black text-yellow-700 mb-1">⭐ Gold Contributor</h2>
                    <h3 class="text-lg font-bold text-on-surface"><?php echo htmlspecialchars($gold['full_name']); ?></h3>
                </div>
                <div class="bg-white px-5 py-3 rounded-xl border border-yellow-200 text-center flex-shrink-0 shadow-sm">
                    <p class="text-2xl font-black text-yellow-600 leading-none"><?php echo $gold['resolved_count']; ?></p>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-yellow-600 mt-1">Resolved</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($silver)): ?>
            <div class="bg-gradient-to-br from-slate-100 to-white border-2 border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row items-center gap-6 transform transition-transform hover:scale-105">
                <div class="w-16 h-16 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-black text-2xl shadow-inner border-2 border-slate-300 flex-shrink-0">
                    #3
                </div>
                <div class="flex-grow text-center md:text-left">
                    <h2 class="text-xl font-black text-slate-700 mb-1">⭐⭐ Silver Contributor</h2>
                    <h3 class="text-lg font-bold text-on-surface"><?php echo htmlspecialchars($silver['full_name']); ?></h3>
                </div>
                <div class="bg-white px-5 py-3 rounded-xl border border-slate-200 text-center flex-shrink-0 shadow-sm">
                    <p class="text-2xl font-black text-slate-600 leading-none"><?php echo $silver['resolved_count']; ?></p>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-600 mt-1">Resolved</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($bronze)): ?>
            <div class="bg-gradient-to-br from-orange-50 to-white border-2 border-orange-100 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row items-center gap-6 transform transition-transform hover:scale-105">
                <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center text-orange-700 font-black text-2xl shadow-inner border-2 border-orange-200 flex-shrink-0">
                    #4
                </div>
                <div class="flex-grow text-center md:text-left">
                    <h2 class="text-xl font-black text-orange-700 mb-1">⭐⭐⭐ Browze Contributor</h2>
                    <h3 class="text-lg font-bold text-on-surface"><?php echo htmlspecialchars($bronze['full_name']); ?></h3>
                </div>
                <div class="bg-white px-5 py-3 rounded-xl border border-orange-100 text-center flex-shrink-0 shadow-sm">
                    <p class="text-2xl font-black text-orange-600 leading-none"><?php echo $bronze['resolved_count']; ?></p>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-orange-600 mt-1">Resolved</p>
                </div>
            </div>
            <?php endif; ?>

        </div>
        
        <div class="mt-12 text-center">
            <a href="dashboard.php" class="inline-flex items-center gap-2 bg-primary text-on-primary px-8 py-4 rounded-xl font-bold tracking-tight shadow-md hover:bg-primary-container hover:text-primary transition-all active:scale-95">
                <span class="material-symbols-outlined">dashboard</span>
                Return to Dashboard
            </a>
        </div>
    </div>
</main>

</body></html>
