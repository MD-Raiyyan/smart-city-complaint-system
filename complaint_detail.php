<?php
// complaint_detail.php (Civic Clarity UI)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$complaint_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Fetch complaint details
if(isset($conn) && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT c.*, u.full_name FROM complaints c INNER JOIN users u ON c.user_id = u.id WHERE c.id = ?");
    $stmt->bind_param("i", $complaint_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $complaint = $res->fetch_assoc();
    $stmt->close();
    
    if (!$complaint) {
        die("Complaint not found or you do not have permission.");
    }
    
    // Fetch updates for timeline
    $updates_res = $conn->query("SELECT * FROM complaint_updates WHERE complaint_id = $complaint_id ORDER BY created_at ASC");
    $updates = [];
    while ($row = $updates_res->fetch_assoc()) {
        $updates[$row['status']] = $row;
    }
} else {
    die("Database connection failed.");
}

// Map status levels for the timeline
$statuses = ['Pending' => 1, 'In Progress' => 2, 'Resolved' => 3, 'Rejected' => 3];
$current_level = $statuses[$complaint['status']] ?? 1;

// Badge mapping
$badge_map = [
    'Pending' => 'bg-blue-100 text-blue-700 border-blue-200',
    'In Progress' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
    'Resolved' => 'bg-green-100 text-green-700 border-green-200',
    'Rejected' => 'bg-red-100 text-red-700 border-red-200',
];
$badge = $badge_map[$complaint['status']] ?? 'bg-gray-100 text-gray-700 border-gray-200';

$display_img = $complaint['attachment'];
$proof_showing = false;
if (isset($updates['Resolved']['proof_image']) && !empty($updates['Resolved']['proof_image'])) {
    $display_img = $updates['Resolved']['proof_image'];
    $proof_showing = true;
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Civic Clarity - Tracking Report</title>
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
    body { font-family: 'Inter', sans-serif; min-height: max(300px, 100dvh); background-color: #f3f4f6; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 600, 'GRAD' 0, 'opsz' 24; }
    .material-symbols-outlined.outline { font-variation-settings: 'FILL' 0; }
</style>
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="text-on-surface">

<header class="fixed top-0 w-full z-50 bg-white border-b border-gray-200 flex items-center px-6 h-16 w-full shadow-sm">
    <a href="dashboard.php" class="mr-4 p-2 rounded-full hover:bg-gray-100 transition-colors">
        <span class="material-symbols-outlined text-gray-600 outline">arrow_back</span>
    </a>
    <h1 class="text-xl font-bold tracking-tighter text-blue-700">Track Report #CC-<?php echo $complaint['id']; ?></h1>
</header>

<main class="pt-24 pb-32 px-4 max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-8">
    
    <!-- LEFT COLUMN: Status Timeline (Flipkart Style) -->
    <div class="md:col-span-5 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <h2 class="text-lg font-bold mb-8 uppercase tracking-widest text-outline border-b pb-4">Report Journey</h2>
        
        <div class="relative pl-4 border-l-2 border-gray-200 ml-4 space-y-12">
            
            <!-- Step 1: Submitted -->
            <div class="relative">
                <div class="absolute -left-[25px] top-0 w-6 h-6 rounded-full border-[3px] border-white <?php echo ($current_level >= 1) ? 'bg-green-500' : 'bg-gray-300'; ?> flex items-center justify-center ring-4 ring-white shadow-sm z-10">
                    <span class="material-symbols-outlined text-white text-[14px]">check</span>
                </div>
                <h3 class="font-bold text-gray-900 leading-none">Report Submitted</h3>
                <p class="text-xs text-gray-500 mt-1"><?php echo date('D, M d, Y - h:i A', strtotime($complaint['created_at'])); ?></p>
            </div>

            <!-- Step 2: Under Review (Implicit immediately) -->
            <div class="relative">
                <div class="absolute -left-[25px] top-0 w-6 h-6 rounded-full border-[3px] border-white <?php echo ($current_level >= 1) ? 'bg-green-500' : 'bg-gray-300'; ?> flex items-center justify-center ring-4 ring-white shadow-sm z-10">
                    <?php if($current_level >= 1): ?><span class="material-symbols-outlined text-white text-[14px]">check</span><?php endif; ?>
                </div>
                <!-- Dynamic active line connecting Step 1 to Step 2 -->
                <?php if($current_level >= 1): ?>
                <div class="absolute -left-[22px] -top-[48px] w-[2px] h-[48px] bg-green-500 -z-10"></div>
                <?php endif; ?>

                <h3 class="font-bold <?php echo ($current_level >= 1) ? 'text-gray-900' : 'text-gray-400'; ?> leading-none">Under Review</h3>
                <p class="text-xs text-gray-500 mt-1">Our team is reviewing your report.</p>
            </div>

            <!-- Step 3: In Progress -->
            <div class="relative">
                <?php
                $bg_3 = 'bg-gray-300';
                $icon_3 = '';
                if ($current_level > 2) {
                    $bg_3 = 'bg-green-500 text-white';
                    $icon_3 = 'check';
                } else if ($current_level == 2) {
                    $bg_3 = 'bg-yellow-500 ring-yellow-100 text-white';
                    $icon_3 = 'engineering';
                }
                ?>
                <div class="absolute -left-[25px] top-0 w-6 h-6 rounded-full border-[3px] border-white <?php echo $bg_3; ?> flex items-center justify-center ring-4 ring-white shadow-sm z-10">
                    <?php if($current_level >= 2): ?><span class="material-symbols-outlined text-[14px]"><?php echo $icon_3; ?></span><?php endif; ?>
                </div>
                <!-- Dynamic line from Step 2 to 3 -->
                <?php if($current_level >= 2): ?>
                <div class="absolute -left-[22px] -top-[48px] w-[2px] h-[48px] bg-green-500 -z-10"></div>
                <?php endif; ?>

                <h3 class="font-bold <?php echo ($current_level >= 2) ? 'text-gray-900' : 'text-gray-400'; ?> leading-none">In Progress</h3>
                <?php if(isset($updates['In Progress'])): ?>
                    <p class="text-xs text-gray-500 mt-1">Assigned to field team on <?php echo date('M d', strtotime($updates['In Progress']['created_at'])); ?>.</p>
                <?php else: ?>
                    <p class="text-xs text-gray-400 mt-1">Waiting for crew assignment.</p>
                <?php endif; ?>
            </div>

            <!-- Step 4: Resolved / Rejected -->
            <div class="relative">
                <?php 
                $final_bg = 'bg-gray-300';
                $final_icon = '';
                $final_title = 'Resolved';
                $final_line = '';
                if ($current_level === 3) {
                    if ($complaint['status'] === 'Resolved') {
                        $final_bg = 'bg-green-600 ring-green-100';
                        $final_icon = 'verified';
                        $final_title = 'Resolved Successfully';
                        $final_line = 'bg-yellow-500'; // line linking from yellow to green
                    } else if ($complaint['status'] === 'Rejected') {
                        $final_bg = 'bg-red-600 ring-red-100';
                        $final_icon = 'close';
                        $final_title = 'Report Rejected';
                        $final_line = 'bg-yellow-500';
                    }
                }
                ?>
                <div class="absolute -left-[25px] top-0 w-6 h-6 rounded-full border-[3px] border-white <?php echo $final_bg; ?> flex items-center justify-center ring-4 ring-white shadow-sm z-10">
                     <?php if($current_level === 3): ?><span class="material-symbols-outlined text-white text-[14px]"><?php echo $final_icon; ?></span><?php endif; ?>
                </div>
                <!-- Dynamic line from Step 3 to 4 -->
                <?php if($current_level === 3): ?>
                <div class="absolute -left-[22px] -top-[48px] w-[2px] h-[48px] <?php echo (isset($updates['In Progress'])) ? 'bg-yellow-500' : 'bg-green-500'; ?> -z-10"></div>
                <?php endif; ?>

                <h3 class="font-bold <?php echo ($current_level === 3) ? 'text-gray-900' : 'text-gray-400'; ?> leading-none"><?php echo $final_title; ?></h3>
                <?php if($current_level === 3 && isset($updates[$complaint['status']])): ?>
                    <p class="text-xs text-gray-500 mt-1">Closed on <?php echo date('M d, h:i A', strtotime($updates[$complaint['status']]['created_at'])); ?></p>
                <?php else: ?>
                    <p class="text-xs text-gray-400 mt-1">Awaiting final resolution.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- RIGHT COLUMN: Report Information -->
    <div class="md:col-span-7 bg-white p-8 rounded-2xl shadow-sm border border-gray-100 h-fit">
        <div class="flex justify-between items-start mb-6">
            <div>
                <span class="text-xs uppercase tracking-widest font-black text-gray-400 mb-1 block"><?php echo htmlspecialchars($complaint['category']); ?> Issue</span>
                <h2 class="text-2xl font-extrabold text-gray-900"><?php echo htmlspecialchars($complaint['title']); ?></h2>
                <div class="text-xs text-gray-500 mt-3 flex items-center gap-2 font-medium">
                    <span class="material-symbols-outlined text-[14px]">person</span> Reported by <strong class="text-gray-700"><?php echo htmlspecialchars($complaint['full_name']); ?></strong>
                    <span class="text-gray-300">|</span>
                    <span class="material-symbols-outlined text-[14px]">location_on</span> <?php echo !empty($complaint['location']) ? htmlspecialchars($complaint['location']) : 'Location disabled'; ?>
                </div>
            </div>
            <span class="px-3 py-1.5 rounded-full border text-xs font-bold uppercase tracking-wider <?php echo $badge; ?>">
                <?php echo $complaint['status']; ?>
            </span>
        </div>

        <div class="bg-gray-50 rounded-xl p-5 mb-6 border border-gray-100">
            <h4 class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Description</h4>
            <p class="text-gray-700 leading-relaxed text-sm">
                <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
            </p>
        </div>

        <?php if(!empty($display_img)): ?>
        <div class="mb-4">
            <h4 class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">
                <?php echo $proof_showing ? 'Resolution Proof 🛡️' : 'Attached Evidence 📷'; ?>
            </h4>
            <div class="rounded-xl overflow-hidden border-2 <?php echo $proof_showing ? 'border-green-500 border-dashed' : 'border-gray-200'; ?>">
                <img src="<?php echo htmlspecialchars($display_img); ?>" alt="Report Image" class="w-full h-auto max-h-80 object-cover hover:scale-105 transition-transform duration-500 cursor-pointer" onclick="window.open(this.src,'_blank')">
            </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($complaint['location'])): ?>
        <div class="mt-6">
            <h4 class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Issue Location 📍</h4>
            <div id="complaint-map" class="h-64 w-full rounded-xl border border-gray-200 z-10 relative"></div>
        </div>
        <?php endif; ?>

    </div>
</main>

<script>
    <?php if(!empty($complaint['location'])): ?>
    document.addEventListener("DOMContentLoaded", function() {
        var locationStr = "<?php echo addslashes($complaint['location']); ?>";
        
        // Fetch coordinates using Nominatim API
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(locationStr))
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    var lat = parseFloat(data[0].lat);
                    var lon = parseFloat(data[0].lon);
                    
                    var map = L.map('complaint-map').setView([lat, lon], 15);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(map);
                    
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('<b><?php echo addslashes($complaint['title']); ?></b><br>' + locationStr)
                        .openPopup();
                        
                    // Fix grey tile issue by invalidating size after a short delay
                    setTimeout(function() {
                        map.invalidateSize();
                    }, 250);
                } else {
                    document.getElementById('complaint-map').innerHTML = '<div class="flex items-center justify-center h-full text-gray-400">Location could not be found on the map.</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching location:', error);
                document.getElementById('complaint-map').innerHTML = '<div class="flex items-center justify-center h-full text-gray-400">Map unavailable right now.</div>';
            });
    });
    <?php endif; ?>
</script>
</body></html>
