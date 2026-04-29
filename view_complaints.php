<?php
// view_complaints.php (Civic Clarity UI)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
$complaints = [];
if(isset($conn) && !$conn->connect_error) {
    $user_id = $_SESSION['user_id'];
    // Subquery grabs the latest proof_image from audit table
    $query = "SELECT c.*, 
             (SELECT proof_image FROM complaint_updates cu WHERE cu.complaint_id = c.id ORDER BY created_at DESC LIMIT 1) as proof_image 
              FROM complaints c 
              WHERE c.user_id=$user_id 
              ORDER BY c.created_at DESC";
    $res = $conn->query($query);
    if($res) {
        while($row = $res->fetch_assoc()) {
            $row['icon'] = 'assignment';
            if ($row['category'] == 'Road') $row['icon'] = 'engineering';
            if ($row['category'] == 'Water') $row['icon'] = 'water_drop';
            if ($row['category'] == 'Electricity') $row['icon'] = 'bolt';
            if ($row['category'] == 'Garbage') $row['icon'] = 'delete';
            
            if ($row['status'] == 'Pending') {
                $row['status_bg'] = 'bg-blue-100/50 text-blue-700';
            } else if ($row['status'] == 'Resolved') {
                $row['status_bg'] = 'bg-tertiary-fixed/30 text-tertiary';
            } else {
                $row['status_bg'] = 'bg-secondary-container text-on-secondary-container';
            }
            $row['date'] = date('M d, Y', strtotime($row['created_at']));
            if (empty($row['location'])) {
                $row['location'] = 'Location not provided';
            }
            
            $complaints[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Civic Clarity - Complaint Tracking</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
        tailwind.config = { darkMode: "class", theme: { extend: { colors: {
            "on-secondary": "#ffffff", "primary-fixed": "#d8e2ff", "secondary-fixed": "#d8e2ff",
            "surface-container-highest": "#e1e3e4", "error-container": "#ffdad6", "surface": "#f8f9fa",
            "on-error-container": "#93000a", "primary-fixed-dim": "#adc7ff", "on-surface-variant": "#414754",
            "inverse-on-surface": "#f0f1f2", "tertiary-container": "#008939", "on-tertiary-fixed": "#002108",
            "on-tertiary-container": "#ffffff", "on-background": "#191c1d", "primary-container": "#1a73e8",
            "secondary-fixed-dim": "#afc7fb", "inverse-primary": "#adc7ff", "tertiary": "#006d2c",
            "on-surface": "#191c1d", "outline-variant": "#c1c6d6", "error": "#ba1a1a",
            "surface-dim": "#d9dadb", "outline": "#727785", "on-error": "#ffffff", "on-primary": "#ffffff",
            "secondary": "#475e8c", "surface-container-low": "#f3f4f5", "secondary-container": "#b2c9fe",
            "surface-bright": "#f8f9fa", "surface-container": "#edeeef", "on-primary-fixed": "#001a41",
            "surface-container-high": "#e7e8e9", "inverse-surface": "#2e3132", "primary": "#005bbf",
            "on-secondary-container": "#3d5481", "on-secondary-fixed-variant": "#2e4673",
            "surface-container-lowest": "#ffffff", "surface-variant": "#e1e3e4", "on-secondary-fixed": "#001a41",
            "tertiary-fixed-dim": "#6ddd81", "on-tertiary": "#ffffff", "background": "#f8f9fa",
            "on-tertiary-fixed-variant": "#005320", "surface-tint": "#005bc0", "on-primary-fixed-variant": "#004493",
            "on-primary-container": "#ffffff", "tertiary-fixed": "#89fa9b"
        }, fontFamily: { "headline": ["Inter"], "body": ["Inter"], "label": ["Inter"] } } } }
    </script>
<style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body { min-height: max(884px, 100dvh); }
    </style>
  </head>
<body class="bg-surface font-body text-on-surface">
<!-- TopAppBar -->
<header class="fixed top-0 w-full z-50 bg-slate-50/80 backdrop-blur-xl flex items-center justify-between px-6 h-16 w-full shadow-none">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-blue-700" data-icon="account_balance">account_balance</span>
<h1 class="text-xl font-bold tracking-tighter text-blue-700">Civic Clarity</h1>
</div>
<div class="flex items-center gap-4">
<a href="dashboard.php" class="text-slate-500 font-bold text-sm px-2 py-1">Dashboard</a>
</div>
</header>
<main class="pt-24 pb-32 px-6 max-w-5xl mx-auto">
<section class="mb-12">
<h2 class="text-4xl font-extrabold tracking-tight mb-4 text-on-surface">Complaint History</h2>
<p class="text-on-surface-variant max-w-2xl leading-relaxed">
                Track the status of your reported issues and monitor the progress of city maintenance requests in real-time.
            </p>
</section>

<div class="space-y-4">
<?php foreach($complaints as $row) { ?>
<!-- PHP Loop generated Cards -->
<div class="group bg-surface-container-lowest p-6 rounded-xl transition-all duration-300 hover:shadow-[0_8px_24px_rgba(25,28,29,0.06)] flex flex-col md:flex-row gap-6 items-start cursor-pointer">
<div class="w-14 h-14 bg-surface-container-high rounded-lg flex items-center justify-center flex-shrink-0">
<span class="material-symbols-outlined text-primary text-3xl" data-icon="traffic"><?php echo $row['icon']; ?></span>
</div>
<div class="flex-grow">
<div class="flex flex-wrap items-center justify-between gap-4 mb-2">
<div class="flex items-center gap-3">
<h3 class="text-lg font-bold tracking-tight"><?php echo htmlspecialchars($row['title']); ?></h3>
<span class="text-[10px] font-bold tracking-widest uppercase bg-surface-container-highest px-2 py-1 rounded text-on-surface-variant">#<?php echo $row['id']; ?></span>
</div>
<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full <?php echo $row['status_bg']; ?> text-xs font-bold uppercase tracking-wider">
                            <?php echo $row['status']; ?>
</span>
</div>
<p class="text-on-surface-variant text-sm line-clamp-2 mb-4 leading-relaxed">
                        <?php echo htmlspecialchars($row['description']); ?>
</p>

<!-- Conditional Image Rendering -->
<?php if($row['status'] === 'Resolved' && !empty($row['proof_image'])): ?>
<div class="mb-4 bg-tertiary-fixed/20 p-3 rounded-lg border border-tertiary-fixed items-center flex gap-4">
    <div class="w-16 h-16 rounded overflow-hidden flex-shrink-0 border-2 border-white shadow-sm">
        <img src="<?php echo htmlspecialchars($row['proof_image']); ?>" class="w-full h-full object-cover" alt="Resolution Evidence">
    </div>
    <div>
        <h5 class="text-xs font-bold text-tertiary uppercase tracking-widest flex items-center gap-1"><span class="material-symbols-outlined text-sm">verified</span> Verified Fixed</h5>
        <p class="text-xs text-on-surface-variant font-medium">The city administrator has uploaded photographic evidence resolving this issue.</p>
    </div>
</div>
<?php endif; ?>

<div class="flex items-center gap-6 text-xs font-medium text-outline">
<div class="flex items-center gap-1">
<span class="material-symbols-outlined text-base">calendar_today</span>
                            <?php echo $row['date']; ?>
                        </div>
<div class="flex items-center gap-1">
<span class="material-symbols-outlined text-base">location_on</span>
                            <?php echo htmlspecialchars($row['location']); ?>
                        </div>
</div>
</div>
</div>
<?php } ?>
</div>

</main>
<nav class="md:hidden fixed bottom-0 left-0 w-full flex justify-around items-center px-2 pt-2 pb-6 bg-white/95 backdrop-blur-xl border-t border-slate-100 shadow-[0_-4px_24px_rgba(0,0,0,0.04)] z-50">
<!-- Home -->
<a class="flex flex-col items-center justify-center text-slate-500 px-4 py-1.5 hover:text-blue-600 active:scale-90 transition-transform" href="dashboard.php">
<span class="material-symbols-outlined text-xl">home</span>
<span class="text-[9px] font-bold uppercase tracking-widest Inter mt-1">Home</span>
</a>
<!-- My Complaints Active -->
<a class="flex flex-col items-center justify-center bg-blue-50 text-blue-700 rounded-xl px-4 py-1.5 active:scale-90 transition-transform" href="view_complaints.php">
<span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">assignment</span>
<span class="text-[9px] font-bold uppercase tracking-widest Inter mt-1 text-center leading-[1.1]">My<br>Complaints</span>
</a>
<!-- Alerts -->
<a class="flex flex-col items-center justify-center text-slate-500 px-4 py-1.5 hover:text-blue-600 active:scale-90 transition-transform" href="#">
<span class="material-symbols-outlined text-xl">notifications</span>
<span class="text-[9px] font-bold uppercase tracking-widest Inter mt-1">Alerts</span>
</a>
<!-- Profile -->
<a class="flex flex-col items-center justify-center text-slate-500 px-4 py-1.5 hover:text-blue-600 active:scale-90 transition-transform" href="login.php">
<span class="material-symbols-outlined text-xl">person</span>
<span class="text-[9px] font-bold uppercase tracking-widest Inter mt-1">Profile</span>
</a>
</nav>

<a href="add_complaint.php" class="fixed bottom-24 right-4 md:bottom-10 md:right-10 w-14 h-14 bg-primary text-on-primary rounded-full flex items-center justify-center shadow-xl active:scale-90 transition-transform z-40">
<span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 0; font-weight: 600;">add</span>
</a>
</body></html>
