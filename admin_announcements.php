<?php
// admin_announcements.php
session_start();

// Bouncer Protocol: Kick out anyone without the admin session token
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php?tab=admin");
    exit();
}

include 'db_connect.php';

$all_announcements = [];
if(isset($conn) && !$conn->connect_error) {
    $ann_res = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
    if($ann_res) {
        while($a_row = $ann_res->fetch_assoc()) {
            $all_announcements[] = $a_row;
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Civic Clarity | Manage Announcements</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
tailwind.config = { darkMode: "class", theme: { extend: { colors: {
    "primary": "#005bbf", "on-primary": "#ffffff", "surface": "#f8f9fa",
    "on-surface": "#191c1d", "surface-container-highest": "#e1e3e4",
    "surface-container-lowest": "#ffffff", "surface-container-high": "#e7e8e9",
    "on-surface-variant": "#414754", "error": "#ba1a1a", "outline": "#727785",
    "outline-variant": "#c1c6d6", "primary-container": "#1a73e8"
}, fontFamily: { "headline": ["Inter"], "body": ["Inter"], "label": ["Inter"] } } } }
</script>
<style>
    body { font-family: 'Inter', sans-serif; min-height: max(884px, 100dvh); }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
</style>
</head>
<body class="bg-surface text-on-surface">

<header class="fixed top-0 w-full z-50 bg-slate-50/80 backdrop-blur-xl flex items-center justify-between px-6 h-16 w-full shadow-none border-b border-surface-container-highest">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-blue-700" data-icon="account_balance">account_balance</span>
<h1 class="text-xl font-bold tracking-tighter text-blue-700">Civic Clarity (Admin)</h1>
</div>
<div class="flex items-center gap-6">
<a href="admin.php" class="text-sm font-semibold text-primary hover:underline flex items-center gap-1">
    <span class="material-symbols-outlined text-sm">dashboard</span> Dashboard
</a>
<a href="admin.php?logout=1" class="text-sm font-bold text-red-600">Logout</a>
</div>
</header>
<div class="flex min-h-screen pt-16">

<main class="flex-1 p-6 md:p-10 lg:p-12 overflow-x-hidden max-w-7xl mx-auto">
<section class="mb-10 flex flex-col justify-between gap-6">
<div>
<span class="text-xs font-bold uppercase tracking-[0.2em] text-primary mb-2 block">Communications</span>
<h2 class="text-4xl font-extrabold tracking-tighter text-on-surface">Manage Announcements</h2>
</div>
</section>

<section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/10 shadow-sm">
        <h4 class="font-bold text-on-surface mb-4">Post New Announcement</h4>
        <form action="process_announcement.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add">
            <div>
                <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Theme</label>
                <select name="theme" class="w-full mt-1 bg-surface-container-high border-none rounded py-2 px-3 text-sm focus:ring-2 focus:ring-primary">
                    <option value="General">General</option>
                    <option value="Urban Dev">Urban Dev</option>
                    <option value="Transport">Transport</option>
                    <option value="Alert">Alert</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Title</label>
                <input type="text" name="title" required class="w-full mt-1 bg-surface-container-high border-none rounded py-2 px-3 text-sm focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Message body</label>
                <textarea name="description" required rows="4" class="w-full mt-1 bg-surface-container-high border-none rounded py-2 px-3 text-sm focus:ring-2 focus:ring-primary"></textarea>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Attach Picture (Optional)</label>
                <input type="file" name="announcement_image" accept="image/*" class="w-full mt-1 text-sm file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-bold file:bg-surface-container-high file:text-on-surface hover:file:bg-surface-variant transition-all cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-primary text-on-primary font-bold py-3 mt-2 rounded shadow hover:bg-blue-700 transition">Broadcast Announcement</button>
        </form>
    </div>
    
    <div class="lg:col-span-2 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/10 shadow-sm">
        <h4 class="font-bold text-on-surface mb-4">Broadcast History</h4>
        <?php if(empty($all_announcements)): ?>
            <p class="text-sm text-on-surface-variant">No announcements posted yet.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach($all_announcements as $ann): ?>
                <div class="p-4 bg-surface-container-high rounded-lg flex justify-between items-start gap-4 border-l-4 <?php echo $ann['borderColor'] ?? 'border-primary'; ?>">
                    <div class="flex-grow">
                        <span class="text-[10px] font-extrabold uppercase tracking-tighter <?php echo $ann['colorClass'] ?? 'text-primary'; ?> mb-1 block"><?php echo htmlspecialchars($ann['theme'] ?? 'General'); ?></span>
                        <h5 class="font-bold text-sm text-on-surface"><?php echo htmlspecialchars($ann['title']); ?></h5>
                        <p class="text-sm text-on-surface-variant mt-1"><?php echo nl2br(htmlspecialchars($ann['description'])); ?></p>
                        <?php if(!empty($ann['image_path'])): ?>
                            <div class="mt-3">
                                <span class="text-xs font-bold text-primary flex items-center gap-1"><span class="material-symbols-outlined text-sm">image</span> Has Attached Media</span>
                            </div>
                        <?php endif; ?>
                        <span class="text-xs text-outline mt-3 block"><?php echo date('M d, Y h:i A', strtotime($ann['created_at'])); ?></span>
                    </div>
                    <form action="process_announcement.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?');" class="flex-shrink-0">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="announcement_id" value="<?php echo $ann['id']; ?>">
                        <button type="submit" class="text-error hover:text-red-700 transition flex items-center p-2 bg-white rounded shadow-sm" title="Delete Announcement">
                            <span class="material-symbols-outlined text-xl">delete</span>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
</main>
</div>
</body>
</html>
