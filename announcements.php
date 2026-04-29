<?php
// announcements.php (Civic Clarity UI)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
$announcements = [];

if(isset($conn) && !$conn->connect_error) {
    $ann_res = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
    if($ann_res) {
        while($a_row = $ann_res->fetch_assoc()) {
            $announcements[] = $a_row;
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Civic Clarity - All Announcements</title>
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
<header class="fixed top-0 w-full z-50 bg-slate-50/80 backdrop-blur-xl flex items-center px-6 h-16 w-full shadow-sm">
    <a href="dashboard.php" class="mr-4 flex items-center justify-center p-2 rounded-full hover:bg-slate-200 transition-colors">
        <span class="material-symbols-outlined text-outline">arrow_back</span>
    </a>
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-blue-700" data-icon="campaign">campaign</span>
        <h1 class="text-xl font-bold tracking-tighter text-blue-700">All Announcements</h1>
    </div>
</header>

<main class="pt-24 pb-32 px-6 max-w-4xl mx-auto min-h-screen">
    <section class="max-w-3xl mx-auto space-y-6">
        <?php if(empty($announcements)): ?>
            <p class="text-center text-on-surface-variant mt-10">There are no announcements currently.</p>
        <?php else: ?>
            <?php foreach($announcements as $ann): ?>
            <a href="announcement_detail.php?id=<?php echo $ann['id']; ?>" class="block bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/10 shadow-sm hover:shadow-md transition cursor-pointer hover:-translate-y-1">
                <div class="flex items-start gap-4">
                    <div class="w-1.5 h-full min-h-[80px] rounded-full <?php echo str_replace('text-', 'bg-', htmlspecialchars($ann['colorClass'] ?? 'text-primary')); ?> bg-opacity-70"></div>
                    <div class="flex-grow">
                        <span class="text-[10px] font-extrabold uppercase tracking-tighter <?php echo htmlspecialchars($ann['colorClass'] ?? 'text-primary'); ?> mb-1 block">
                            <?php echo htmlspecialchars($ann['theme'] ?? 'General'); ?>
                        </span>
                        <h4 class="font-bold text-lg text-on-surface mb-2"><?php echo htmlspecialchars($ann['title']); ?></h4>
                        <p class="text-sm text-on-surface-variant leading-relaxed line-clamp-3">
                            <?php echo htmlspecialchars($ann['description']); ?>
                        </p>
                        <div class="mt-4 flex items-center gap-4 text-xs font-semibold text-outline tracking-wider uppercase">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                <?php echo date('M d, Y', strtotime($ann['created_at'])); ?>
                            </span>
                            <?php if(!empty($ann['image_path'])): ?>
                            <span class="flex items-center gap-1 text-primary">
                                <span class="material-symbols-outlined text-sm">image</span> Media
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body></html>
