<?php
// announcement_detail.php (Civic Clarity UI)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$announcement = null;
if (isset($_GET['id']) && isset($conn) && !$conn->connect_error) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $announcement = $res->fetch_assoc();
        }
        $stmt->close();
    }
}

if (!$announcement) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Civic Clarity - Announcement Detail</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = { darkMode: "class", theme: { extend: { colors: {
        "primary": "#005bbf", "on-primary": "#ffffff", "surface": "#f8f9fa",
        "on-surface": "#191c1d", "surface-container-highest": "#e1e3e4",
        "surface-container-lowest": "#ffffff", "surface-container-high": "#e7e8e9",
        "on-surface-variant": "#414754", "outline": "#727785",
        "outline-variant": "#c1c6d6", "primary-container": "#1a73e8"
    }, fontFamily: { "headline": ["Inter"], "body": ["Inter"], "label": ["Inter"] } } } }
</script>
<style> body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; } </style>
</head>
<body class="bg-surface text-on-surface">

<header class="fixed top-0 w-full z-50 bg-slate-50/80 backdrop-blur-xl flex items-center px-6 h-16 w-full shadow-sm border-b border-outline-variant/10">
    <a href="dashboard.php" class="mr-4 flex items-center justify-center p-2 rounded-full hover:bg-slate-200 transition-colors">
        <span class="material-symbols-outlined text-outline">arrow_back</span>
    </a>
    <div class="flex items-center gap-3">
        <h1 class="text-xl font-bold tracking-tighter text-on-surface">Announcement Details</h1>
    </div>
</header>

<main class="pt-24 pb-32 px-6 max-w-4xl mx-auto min-h-screen">
    <article class="bg-surface-container-lowest p-8 lg:p-12 rounded-2xl shadow-sm border border-outline-variant/10">
        
        <div class="flex items-center gap-3 mb-6">
            <span class="px-3 py-1 bg-surface-container-high rounded-full text-xs font-black tracking-widest uppercase <?php echo htmlspecialchars($announcement['colorClass'] ?? 'text-primary'); ?>">
                <?php echo htmlspecialchars($announcement['theme'] ?? 'General'); ?>
            </span>
            <span class="text-xs font-semibold text-outline flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?>
            </span>
        </div>

        <h2 class="text-3xl md:text-4xl font-extrabold text-on-surface mb-8 tracking-tight">
            <?php echo htmlspecialchars($announcement['title']); ?>
        </h2>

        <?php if(!empty($announcement['image_path'])): ?>
        <div class="mb-10 rounded-xl overflow-hidden shadow-md max-h-[500px]">
            <img src="<?php echo htmlspecialchars($announcement['image_path']); ?>" alt="Announcement Context Image" class="w-full h-full object-cover">
        </div>
        <?php endif; ?>

        <div class="prose prose-slate max-w-none">
            <p class="text-lg text-on-surface-variant leading-relaxed whitespace-pre-wrap font-medium"><?php echo htmlspecialchars($announcement['description']); ?></p>
        </div>
        
    </article>
</main>

<footer class="fixed bottom-0 w-full py-4 text-center text-xs font-bold uppercase tracking-widest text-outline bg-surface/80 backdrop-blur-sm z-10 border-t border-outline-variant/10">
    Official Municipal Broadcast
</footer>

</body></html>
