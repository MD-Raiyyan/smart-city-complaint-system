<?php
// dashboard.php (Civic Clarity UI)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
$pending = 0;
$resolved = 0;
$rejected = 0;
$efficiency = '0%';
$recent_complaints = [];
$my_recent_complaints = [];
$progress_complaints = [];
$announcements = [];

if(isset($conn) && !$conn->connect_error) {
    $user_id = $_SESSION['user_id'];
    
    // Fetch User Name
    $user_name = "Citizen";
    $name_res = $conn->query("SELECT full_name FROM users WHERE id=$user_id");
    if($name_res && $n_row = $name_res->fetch_assoc()) {
        $user_name = trim($n_row['full_name']);
    }

    // Dashboard metrics are user-specific
    $res = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE user_id=$user_id AND status='Pending'");
    if($res && $row = $res->fetch_assoc()) $pending = $row['c'];
    
    $res2 = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE user_id=$user_id AND status='Resolved'");
    if($res2 && $row2 = $res2->fetch_assoc()) $resolved = $row2['c'];

    $res3 = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE user_id=$user_id AND status='Rejected'");
    if($res3 && $row3 = $res3->fetch_assoc()) $rejected = $row3['c'];
    
    // Determine Gamification Tier
    $my_tier = "🌱 Civic Beginner";
    $my_tier_icon = "eco";
    $my_tier_color = "text-green-200";

    if ($resolved > 0) {
        if ($resolved <= 3) {
            $my_tier = "🥉 Bronze Contributor";
            $my_tier_icon = "workspace_premium";
            $my_tier_color = "text-orange-300";
        } else if ($resolved <= 10) {
            $my_tier = "🥈 Silver Contributor";
            $my_tier_icon = "workspace_premium";
            $my_tier_color = "text-slate-200";
        } else {
            $my_tier = "🥇 Gold Contributor";
            $my_tier_icon = "workspace_premium";
            $my_tier_color = "text-yellow-400";
        }

        // Check if user is in Top 5 (Elite)
        $top5_res = $conn->query("SELECT user_id, COUNT(*) as res_count FROM complaints WHERE status='Resolved' GROUP BY user_id ORDER BY res_count DESC LIMIT 5");
        $is_elite = false;
        if($top5_res) {
            while($top_row = $top5_res->fetch_assoc()) {
                if ($top_row['user_id'] == $user_id) {
                    $is_elite = true;
                    break;
                }
            }
        }
        if ($is_elite) {
            $my_tier = "🏆 Elite Contributor";
            $my_tier_icon = "social_leaderboard";
            $my_tier_color = "text-yellow-200";
        }
    }
    $total = $pending + $resolved + $rejected;
    if ($total > 0) {
        $efficiency = round(($resolved / $total) * 100) . '%';
    }

    $complaints_res = $conn->query("SELECT c.*, u.full_name, (SELECT proof_image FROM complaint_updates cu WHERE cu.complaint_id = c.id ORDER BY created_at DESC LIMIT 1) as proof_image FROM complaints c INNER JOIN users u ON c.user_id = u.id WHERE c.status != 'Resolved' ORDER BY c.created_at DESC LIMIT 5");
    if($complaints_res) {
        while($c_row = $complaints_res->fetch_assoc()) {
            if ($c_row['status'] == 'Pending') {
                $c_row['status_bg'] = 'bg-blue-100 text-blue-700';
            } else if ($c_row['status'] == 'In Progress') {
                $c_row['status_bg'] = 'bg-yellow-100 text-yellow-700';
            } else if ($c_row['status'] == 'Resolved') {
                $c_row['status_bg'] = 'bg-green-100 text-green-700';
            } else if ($c_row['status'] == 'Rejected') {
                $c_row['status_bg'] = 'bg-red-100 text-red-700';
            }
            $recent_complaints[] = $c_row;
        }
    }

    $my_complaints_res = $conn->query("SELECT c.*, u.full_name, (SELECT proof_image FROM complaint_updates cu WHERE cu.complaint_id = c.id ORDER BY created_at DESC LIMIT 1) as proof_image FROM complaints c INNER JOIN users u ON c.user_id = u.id WHERE c.user_id = $user_id ORDER BY c.created_at DESC LIMIT 5");
    if($my_complaints_res) {
        while($c_row = $my_complaints_res->fetch_assoc()) {
            if ($c_row['status'] == 'Pending') {
                $c_row['status_bg'] = 'bg-blue-100 text-blue-700';
            } else if ($c_row['status'] == 'In Progress') {
                $c_row['status_bg'] = 'bg-yellow-100 text-yellow-700';
            } else if ($c_row['status'] == 'Resolved') {
                $c_row['status_bg'] = 'bg-green-100 text-green-700';
            } else if ($c_row['status'] == 'Rejected') {
                $c_row['status_bg'] = 'bg-red-100 text-red-700';
            }
            $my_recent_complaints[] = $c_row;
        }
    }
    
    $progress_res = $conn->query("SELECT c.*, u.full_name, (SELECT proof_image FROM complaint_updates cu WHERE cu.complaint_id = c.id ORDER BY created_at DESC LIMIT 1) as proof_image FROM complaints c INNER JOIN users u ON c.user_id = u.id WHERE c.status = 'Resolved' ORDER BY c.created_at DESC LIMIT 5");
    if($progress_res) {
        while($c_row = $progress_res->fetch_assoc()) {
            $c_row['status_bg'] = 'bg-green-100 text-green-700';
            $progress_complaints[] = $c_row;
        }
    }
    
    $ann_res = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
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
<title>Civic Clarity - Citizen Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
<style> body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; min-height: max(884px, 100dvh); }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; } </style>
</head>
<body class="bg-surface text-on-surface">
<!-- TopAppBar -->
<header class="fixed top-0 w-full z-50 bg-slate-50/80 backdrop-blur-xl flex items-center justify-between px-6 h-16 w-full shadow-sm">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-blue-700" data-icon="account_balance">account_balance</span>
<h1 class="text-xl font-bold tracking-tighter text-blue-700">Civic Clarity</h1>
</div>
<div class="flex items-center gap-4">
<a href="login.php?logout=1" class="p-2 rounded-full hover:bg-slate-200/50 transition-colors active:scale-95 duration-200 text-sm font-bold text-red-600">Logout</a>
</div>
</header>

<main class="pt-24 pb-32 px-6 max-w-7xl mx-auto min-h-screen">
<!-- Hero Section -->
<section class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12 items-end">
<div class="lg:col-span-8">
<p class="text-primary font-black tracking-widest uppercase text-lg mb-3">Welcome Back, <?php echo htmlspecialchars($user_name); ?></p>
<h2 class="text-4xl md:text-5xl font-extrabold tracking-tighter text-on-surface mb-4 leading-tight">
                    Shaping a better city,<br/>
<span class="text-primary">one report at a time.</span>
</h2>
<p class="text-on-surface-variant text-lg max-w-xl">
                    Track your contributions to the community and stay informed about the latest urban developments in your district.
                </p>
</div>
<div class="lg:col-span-4 flex justify-end">
<a href="add_complaint.php" class="group relative bg-primary-container text-on-primary w-full lg:w-auto px-8 py-5 rounded-xl shadow-[0_8px_24px_rgba(0,91,191,0.2)] hover:bg-primary transition-all duration-300 flex items-center justify-center gap-3 active:scale-95">
<span class="material-symbols-outlined text-2xl" data-icon="add_circle">add_circle</span>
<span class="font-bold text-lg tracking-tight">New Complaint</span>
</a>
</div>
</section>

<!-- Stats Bento Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-12">
<div class="bg-surface-container-lowest p-6 rounded-xl border-none transition-all hover:bg-surface-container-low">
<div class="flex items-center justify-between mb-4">
<span class="material-symbols-outlined text-primary bg-primary-fixed p-3 rounded-lg" data-icon="pending_actions">pending_actions</span>
<span class="text-xs font-bold uppercase tracking-widest text-outline">Pending</span>
</div>
<p class="text-3xl font-black text-on-surface"><?php echo $pending; ?></p>
<p class="text-on-surface-variant text-sm mt-1">Ongoing reports</p>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border-none transition-all hover:bg-surface-container-low">
<div class="flex items-center justify-between mb-4">
<span class="material-symbols-outlined text-tertiary bg-tertiary-fixed p-3 rounded-lg" data-icon="check_circle">check_circle</span>
<span class="text-xs font-bold uppercase tracking-widest text-outline">Resolved</span>
</div>
<p class="text-3xl font-black text-on-surface"><?php echo $resolved; ?></p>
<p class="text-on-surface-variant text-sm mt-1">Issues fixed this year</p>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border-none transition-all hover:bg-surface-container-low">
<div class="flex items-center justify-between mb-4">
<span class="material-symbols-outlined text-secondary bg-secondary-fixed p-3 rounded-lg" data-icon="bolt">bolt</span>
<span class="text-xs font-bold uppercase tracking-widest text-outline">Impact</span>
</div>
<p class="text-3xl font-black text-on-surface"><?php echo $efficiency; ?></p>
<p class="text-on-surface-variant text-sm mt-1">Resolution efficiency</p>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border-none transition-all hover:bg-surface-container-low">
<div class="flex items-center justify-between mb-4">
<span class="material-symbols-outlined text-red-500 bg-red-100 p-3 rounded-lg" data-icon="cancel">cancel</span>
<span class="text-xs font-bold uppercase tracking-widest text-outline">Rejected</span>
</div>
<p class="text-3xl font-black text-on-surface"><?php echo $rejected; ?></p>
<p class="text-on-surface-variant text-sm mt-1">Declined cases</p>
</div>
<a href="leaderboard.php" class="hidden lg:block bg-gradient-to-br from-primary-container to-primary p-6 rounded-xl border-none text-on-primary transition-all hover:scale-[1.02] hover:shadow-xl cursor-pointer group">
<div class="flex flex-col h-full justify-between relative overflow-hidden">
<p class="font-bold leading-tight relative z-10">Your reports have directly contributed to <?php echo $resolved; ?> district improvement<?php echo ($resolved == 1) ? '' : 's'; ?>.</p>
<div class="flex items-center gap-2 mt-4 relative z-10">
<span class="material-symbols-outlined <?php echo $my_tier_color; ?>" style="font-variation-settings: 'FILL' 1;"><?php echo $my_tier_icon; ?></span>
<span class="text-sm font-black tracking-wide <?php echo $my_tier_color; ?>"><?php echo $my_tier; ?></span>
</div>
<span class="material-symbols-outlined absolute -bottom-6 -right-6 text-9xl text-white opacity-10 group-hover:scale-110 transition-transform duration-500" style="font-variation-settings: 'FILL' 1;"><?php echo $my_tier_icon; ?></span>
</div>
</a>

</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
<!-- Current Status Section -->
<div class="lg:col-span-8">
<div class="flex items-center justify-between mb-6 border-b border-outline-variant/20 pb-2">
<div class="flex gap-6">
<button onclick="switchTab('community')" id="tab-community" class="text-2xl font-bold tracking-tight text-on-surface border-b-4 border-primary pb-1 transition-all">Current Status</button>
<button onclick="switchTab('progress')" id="tab-progress" class="text-2xl font-bold tracking-tight text-outline border-b-4 border-transparent pb-1 hover:text-on-surface transition-all">Progress</button>
<button onclick="switchTab('personal')" id="tab-personal" class="text-2xl font-bold tracking-tight text-outline border-b-4 border-transparent pb-1 hover:text-on-surface transition-all">My Complaints</button>
</div>
<a href="view_complaints.php" class="text-primary text-sm font-semibold hover:underline">View All Reports</a>
</div>

<!-- Community Status Panel -->
<div id="panel-community" class="space-y-4">
<?php foreach($recent_complaints as $complaint): 
$display_img = !empty($complaint['proof_image']) ? $complaint['proof_image'] : $complaint['attachment'];
?>
<a href="complaint_detail.php?id=<?php echo $complaint['id']; ?>" class="group bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.03)] flex items-center gap-5 transition-all hover:bg-surface-container-low cursor-pointer hover:-translate-y-1 hover:shadow-lg <?php if(!empty($complaint['proof_image'])) echo 'border-2 border-tertiary-fixed border-dashed'; else echo 'border border-outline-variant/10'; ?>">
<div class="relative h-16 w-16 rounded-lg overflow-hidden flex-shrink-0 bg-surface-container-highest">
<?php if(!empty($display_img)): ?>
<img class="h-full w-full object-cover" src="<?php echo htmlspecialchars($display_img); ?>" alt="Report Image" />
<?php if(!empty($complaint['proof_image'])): ?>
    <div class="absolute inset-0 bg-tertiary/20 flex items-center justify-center backdrop-blur-[1px]">
        <span class="material-symbols-outlined text-white shadow-sm drop-shadow-md text-2xl font-bold">verified</span>
    </div>
<?php endif; ?>
<?php else: ?>
<span class="material-symbols-outlined text-outline mt-5 ml-5">broken_image</span>
<?php endif; ?>
</div>
<div class="flex-grow">
<div class="flex items-start justify-between mb-1">
<h4 class="font-bold text-on-surface"><?php echo htmlspecialchars($complaint['title']); ?></h4>
<p class="text-[10px] uppercase font-bold text-outline tracking-wider mt-0.5 mb-1.5 flex items-center gap-1">
    <span class="material-symbols-outlined text-[12px]">person</span> Reported by <strong><?php echo htmlspecialchars($complaint['full_name']); ?></strong>
</p>
<span class="px-3 py-1 rounded-full <?php echo $complaint['status_bg'] ?? 'bg-surface-container-highest text-outline'; ?> text-[10px] font-bold uppercase tracking-wider"><?php echo $complaint['status']; ?></span>
</div>
<p class="text-sm text-on-surface-variant line-clamp-1 mb-2"><?php echo htmlspecialchars($complaint['description']); ?></p>
<div class="flex flex-wrap items-center gap-4 text-[11px] text-outline font-medium">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> <?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></span>
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">category</span> <?php echo htmlspecialchars($complaint['category']); ?></span>
<span class="flex items-center gap-1 truncate max-w-[200px]"><span class="material-symbols-outlined text-sm">location_on</span> <?php echo htmlspecialchars($complaint['location'] ?? 'Location not provided'); ?></span>
</div>
</div>
</a>
<?php endforeach; ?>
<?php if(empty($recent_complaints)): ?>
<p class="text-on-surface-variant text-sm">No recent complaints found.</p>
<?php endif; ?>
</div>

<!-- Progress Panel -->
<div id="panel-progress" class="space-y-4 hidden">
<?php foreach($progress_complaints as $complaint): 
$display_img = !empty($complaint['proof_image']) ? $complaint['proof_image'] : $complaint['attachment'];
?>
<a href="complaint_detail.php?id=<?php echo $complaint['id']; ?>" class="group bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.03)] flex items-center gap-5 transition-all hover:bg-surface-container-low cursor-pointer hover:-translate-y-1 hover:shadow-lg <?php if(!empty($complaint['proof_image'])) echo 'border-2 border-tertiary-fixed border-dashed'; else echo 'border border-outline-variant/10'; ?>">
<div class="relative h-16 w-16 rounded-lg overflow-hidden flex-shrink-0 bg-surface-container-highest">
<?php if(!empty($display_img)): ?>
<img class="h-full w-full object-cover" src="<?php echo htmlspecialchars($display_img); ?>" alt="Report Image" />
<?php if(!empty($complaint['proof_image'])): ?>
    <div class="absolute inset-0 bg-tertiary/20 flex items-center justify-center backdrop-blur-[1px]">
        <span class="material-symbols-outlined text-white shadow-sm drop-shadow-md text-2xl font-bold">verified</span>
    </div>
<?php endif; ?>
<?php else: ?>
<span class="material-symbols-outlined text-outline mt-5 ml-5">broken_image</span>
<?php endif; ?>
</div>
<div class="flex-grow">
<div class="flex items-start justify-between mb-1">
<h4 class="font-bold text-on-surface"><?php echo htmlspecialchars($complaint['title']); ?></h4>
<p class="text-[10px] uppercase font-bold text-outline tracking-wider mt-0.5 mb-1.5 flex items-center gap-1">
    <span class="material-symbols-outlined text-[12px]">person</span> Resolved by <strong>Civic Admin</strong>
</p>
<span class="px-3 py-1 rounded-full <?php echo $complaint['status_bg'] ?? 'bg-surface-container-highest text-outline'; ?> text-[10px] font-bold uppercase tracking-wider"><?php echo $complaint['status']; ?></span>
</div>
<p class="text-sm text-on-surface-variant line-clamp-1 mb-2"><?php echo htmlspecialchars($complaint['description']); ?></p>
<div class="flex flex-wrap items-center gap-4 text-[11px] text-outline font-medium">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> <?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></span>
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">category</span> <?php echo htmlspecialchars($complaint['category']); ?></span>
<span class="flex items-center gap-1 truncate max-w-[200px]"><span class="material-symbols-outlined text-sm">location_on</span> <?php echo htmlspecialchars($complaint['location'] ?? 'Location not provided'); ?></span>
</div>
</div>
</a>
<?php endforeach; ?>
<?php if(empty($progress_complaints)): ?>
<p class="text-on-surface-variant text-sm">No resolved complaints yet.</p>
<?php endif; ?>
</div>

<!-- My Complaints Panel -->
<div id="panel-personal" class="space-y-4 hidden">
<?php foreach($my_recent_complaints as $complaint): 
$display_img = !empty($complaint['proof_image']) ? $complaint['proof_image'] : $complaint['attachment'];
$can_edit = !in_array($complaint['status'], ['Resolved', 'Rejected']);
?>
<div class="group bg-surface-container-lowest p-5 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.03)] flex flex-col md:flex-row md:items-center gap-5 transition-all border border-outline-variant/10 hover:border-primary/30">
<a href="complaint_detail.php?id=<?php echo $complaint['id']; ?>" class="flex items-center gap-5 flex-grow cursor-pointer">
<div class="relative h-16 w-16 rounded-lg overflow-hidden flex-shrink-0 bg-surface-container-highest">
<?php if(!empty($display_img)): ?>
<img class="h-full w-full object-cover" src="<?php echo htmlspecialchars($display_img); ?>" alt="Report Image" />
<?php else: ?>
<span class="material-symbols-outlined text-outline mt-5 ml-5">broken_image</span>
<?php endif; ?>
</div>
<div class="flex-grow">
<div class="flex items-start justify-between mb-1">
<h4 class="font-bold text-on-surface"><?php echo htmlspecialchars($complaint['title']); ?></h4>
<span class="px-3 py-1 rounded-full <?php echo $complaint['status_bg'] ?? 'bg-surface-container-highest text-outline'; ?> text-[10px] font-bold uppercase tracking-wider"><?php echo $complaint['status']; ?></span>
</div>
<p class="text-sm text-on-surface-variant line-clamp-1 mb-2"><?php echo htmlspecialchars($complaint['description']); ?></p>
<div class="flex flex-wrap items-center gap-4 text-[11px] text-outline font-medium">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> <?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></span>
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">category</span> <?php echo htmlspecialchars($complaint['category']); ?></span>
<span class="flex items-center gap-1 truncate max-w-[200px]"><span class="material-symbols-outlined text-sm">location_on</span> <?php echo htmlspecialchars($complaint['location'] ?? 'Location not provided'); ?></span>
</div>
</div>
</a>
<?php if($can_edit): ?>
<div class="flex md:flex-col items-center justify-center gap-2 border-t md:border-t-0 md:border-l border-outline-variant/20 pt-4 md:pt-0 md:pl-4">
    <a href="edit_complaint.php?id=<?php echo $complaint['id']; ?>" class="p-2 text-primary hover:bg-primary-fixed rounded-full transition-colors flex items-center justify-center" title="Edit">
        <span class="material-symbols-outlined text-[20px]">edit</span>
    </a>
    <a href="delete_complaint.php?id=<?php echo $complaint['id']; ?>" onclick="return confirm('Are you sure you want to delete this complaint? This cannot be undone.')" class="p-2 text-error hover:bg-error-container rounded-full transition-colors flex items-center justify-center" title="Delete">
        <span class="material-symbols-outlined text-[20px]">delete</span>
    </a>
</div>
<?php endif; ?>
</div>
<?php endforeach; ?>
<?php if(empty($my_recent_complaints)): ?>
<p class="text-on-surface-variant text-sm">You haven't submitted any complaints yet.</p>
<?php endif; ?>
</div>
</div>

<!-- Recent Announcements Section -->
<div class="lg:col-span-4">
<h3 class="text-2xl font-bold tracking-tight text-on-surface mb-6">Recent Announcements</h3>
<div class="bg-surface-container p-6 rounded-xl space-y-6">

<?php foreach($announcements as $ann): ?>
<a href="announcement_detail.php?id=<?php echo $ann['id']; ?>" class="relative pl-4 border-l-2 <?php echo htmlspecialchars($ann['borderColor'] ?? 'border-primary'); ?> block hover:opacity-80 transition cursor-pointer">
<span class="text-[10px] font-extrabold uppercase tracking-tighter <?php echo htmlspecialchars($ann['colorClass'] ?? 'text-primary'); ?> mb-1 block"><?php echo htmlspecialchars($ann['theme'] ?? 'General'); ?></span>
<h5 class="font-bold text-on-surface mb-1"><?php echo htmlspecialchars($ann['title']); ?></h5>
<p class="text-xs text-on-surface-variant leading-relaxed line-clamp-2"><?php echo htmlspecialchars($ann['description']); ?></p>
</a>
<?php endforeach; ?>

<a href="announcements.php" class="block text-center w-full py-3 bg-surface-container-highest text-on-surface font-bold text-xs uppercase tracking-widest rounded-lg hover:bg-outline-variant transition-colors">
All Announcements
</a>
</div>

<!-- District Insights Card -->
<div class="mt-6 rounded-xl overflow-hidden relative h-48 group cursor-pointer">
<img class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" src="uploads/district.png" alt="District Overview"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex flex-col justify-end p-5">
<h4 class="text-white font-bold text-lg leading-tight">Your District: At a Glance</h4>
<p class="text-white/80 text-xs mt-1">Explore real-time data on air quality, traffic, and public services near you.</p>
</div>
</div>
</div>
</div>

</main>

<!-- BottomNavBar (Mobile only) -->
<nav class="md:hidden fixed bottom-0 left-0 w-full flex justify-around items-center px-2 pt-2 pb-6 bg-white/95 backdrop-blur-xl border-t border-slate-100 shadow-[0_-4px_24px_rgba(0,0,0,0.04)] z-50">
<!-- Home Active -->
<a class="flex flex-col items-center justify-center bg-blue-50 text-blue-700 rounded-xl px-4 py-1.5 active:scale-90 transition-transform" href="dashboard.php">
<span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">home</span>
<span class="text-[9px] font-bold uppercase tracking-widest Inter mt-1">Home</span>
</a>
<!-- My Complaints -->
<a class="flex flex-col items-center justify-center text-slate-500 px-4 py-1.5 hover:text-blue-600 active:scale-90 transition-transform" href="view_complaints.php">
<span class="material-symbols-outlined text-xl">assignment</span>
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
<script>
function switchTab(tab) {
    const communityTab = document.getElementById('tab-community');
    const personalTab = document.getElementById('tab-personal');
    const progressTab = document.getElementById('tab-progress');
    const communityPanel = document.getElementById('panel-community');
    const personalPanel = document.getElementById('panel-personal');
    const progressPanel = document.getElementById('panel-progress');

    // Reset all
    const inactiveClass = 'text-2xl font-bold tracking-tight text-outline border-b-4 border-transparent pb-1 hover:text-on-surface transition-all';
    const activeClass = 'text-2xl font-bold tracking-tight text-on-surface border-b-4 border-primary pb-1 transition-all';
    
    communityTab.className = inactiveClass;
    personalTab.className = inactiveClass;
    progressTab.className = inactiveClass;
    communityPanel.classList.add('hidden');
    personalPanel.classList.add('hidden');
    progressPanel.classList.add('hidden');

    if (tab === 'community') {
        communityTab.className = activeClass;
        communityPanel.classList.remove('hidden');
    } else if (tab === 'progress') {
        progressTab.className = activeClass;
        progressPanel.classList.remove('hidden');
    } else {
        personalTab.className = activeClass;
        personalPanel.classList.remove('hidden');
    }
}
</script>
</body></html>
