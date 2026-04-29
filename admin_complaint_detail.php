<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include 'db_connect.php';

$complaint_id = intval($_GET['id'] ?? 0);

if (!$complaint_id) {
    header("Location: admin.php");
    exit();
}

$complaint = null;
$updates = [];

if (isset($conn) && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT c.*, u.full_name, u.email FROM complaints c LEFT JOIN users u ON c.user_id = u.id WHERE c.id = ?");
    $stmt->bind_param("i", $complaint_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $complaint = $result->fetch_assoc();
    $stmt->close();

    if (!$complaint) {
        header("Location: admin.php");
        exit();
    }

    // Fetch status update history
    $upd_res = $conn->query("SELECT * FROM complaint_updates WHERE complaint_id = $complaint_id ORDER BY created_at ASC");
    while ($row = $upd_res->fetch_assoc()) {
        $updates[] = $row;
    }
}

$status_meta = [
    'Pending'     => ['bg' => 'bg-blue-100 text-blue-700',   'dot' => 'bg-blue-500'],
    'In Progress' => ['bg' => 'bg-yellow-100 text-yellow-700','dot' => 'bg-yellow-500'],
    'Resolved'    => ['bg' => 'bg-green-100 text-green-700',  'dot' => 'bg-green-500'],
    'Rejected'    => ['bg' => 'bg-red-100 text-red-700',      'dot' => 'bg-red-500'],
];
$cur_meta = $status_meta[$complaint['status']] ?? ['bg' => 'bg-gray-100 text-gray-700', 'dot' => 'bg-gray-400'];

$cat_icons = [
    'Road'        => 'engineering',
    'Water'       => 'water_drop',
    'Electricity' => 'bolt',
    'Garbage'     => 'delete',
    'Animal'      => 'pets',
];
$cat_icon = $cat_icons[$complaint['category']] ?? 'report';

$display_img = $complaint['attachment'] ?? '';
foreach ($updates as $u) {
    if (!empty($u['proof_image'])) { $display_img = $u['proof_image']; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin | Complaint #CC-<?php echo $complaint_id; ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
tailwind.config = { theme: { extend: { colors: {
    "primary": "#005bbf", "on-primary": "#ffffff", "primary-container": "#1a73e8",
    "surface": "#f8f9fa", "on-surface": "#191c1d", "on-surface-variant": "#414754",
    "surface-container-lowest": "#ffffff", "surface-container": "#edeeef",
    "surface-container-high": "#e7e8e9", "outline-variant": "#c1c6d6", "outline": "#727785"
}}}}
</script>
<style>
body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
</style>
</head>
<body class="text-on-surface min-h-screen">

<!-- Header -->
<header class="fixed top-0 w-full z-50 bg-white border-b border-gray-200 flex items-center justify-between px-6 h-16 shadow-sm">
    <div class="flex items-center gap-3">
        <a href="admin.php" class="p-2 rounded-full hover:bg-gray-100 transition-colors">
            <span class="material-symbols-outlined text-gray-600">arrow_back</span>
        </a>
        <span class="material-symbols-outlined text-blue-700">account_balance</span>
        <h1 class="text-lg font-bold tracking-tight text-blue-700">Admin · Complaint #CC-<?php echo $complaint_id; ?></h1>
    </div>
    <a href="admin.php?logout=1" class="text-sm font-bold text-red-600 hover:underline">Logout</a>
</header>

<main class="pt-24 pb-16 px-4 max-w-5xl mx-auto space-y-6">

    <!-- ✅ STATUS UPDATE CARD (TOP) -->
    <div class="bg-white rounded-2xl border border-blue-200 shadow-md p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-1">Admin Action</p>
                <h2 class="text-xl font-extrabold text-gray-900">Update Complaint Status</h2>
                <p class="text-sm text-gray-500 mt-1">Current status:
                    <span class="font-bold px-2 py-0.5 rounded <?php echo $cur_meta['bg']; ?>"><?php echo $complaint['status']; ?></span>
                </p>
            </div>
            <form action="update_status.php" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 md:items-end w-full md:w-auto">
                <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">
                <div class="flex gap-3 flex-wrap">
                    <select name="status" id="status-select" onchange="toggleProof(this)" class="bg-gray-100 border-none rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-blue-400 cursor-pointer" required>
                        <option value="">— Set New Status —</option>
                        <option value="Pending" <?php echo $complaint['status']==='Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo $complaint['status']==='In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Resolved" <?php echo $complaint['status']==='Resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="Rejected" <?php echo $complaint['status']==='Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <button type="submit" class="bg-blue-600 text-white font-bold px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span> Save Status
                    </button>
                </div>
                <!-- Proof Image (shown only for Resolved) -->
                <div id="proof-upload" class="hidden w-full mt-1 bg-green-50 border border-green-200 rounded-lg p-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-green-700 block mb-1">Upload Resolution Proof *</label>
                    <input type="file" name="proof_image" accept="image/*" class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:font-bold file:bg-green-100 file:text-green-700 cursor-pointer">
                </div>
            </form>
        </div>
    </div>

    <!-- COMPLAINT OVERVIEW -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Left: Details -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
            <div>
                <span class="text-xs uppercase tracking-widest font-black text-gray-400 flex items-center gap-1 mb-1">
                    <span class="material-symbols-outlined fill text-[14px]"><?php echo $cat_icon; ?></span>
                    <?php echo htmlspecialchars($complaint['category']); ?> Issue
                </span>
                <h2 class="text-2xl font-extrabold text-gray-900"><?php echo htmlspecialchars($complaint['title']); ?></h2>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Submitted By</p>
                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($complaint['full_name'] ?? '—'); ?></p>
                    <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($complaint['email'] ?? ''); ?></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Submitted On</p>
                    <p class="font-bold text-gray-800"><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></p>
                    <p class="text-gray-500 text-xs"><?php echo date('h:i A', strtotime($complaint['created_at'])); ?></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 col-span-2">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Location</p>
                    <p class="font-semibold text-gray-800 flex items-center gap-1">
                        <span class="material-symbols-outlined fill text-primary text-[16px]">location_on</span>
                        <?php echo htmlspecialchars($complaint['location'] ?? 'Not provided'); ?>
                    </p>
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Description</p>
                <p class="text-gray-700 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
            </div>

            <!-- Update History -->
            <?php if(!empty($updates)): ?>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Status History</p>
                <div class="space-y-2">
                <?php foreach($updates as $u): $um = $status_meta[$u['status']] ?? ['bg'=>'bg-gray-100 text-gray-600','dot'=>'bg-gray-400']; ?>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="w-2 h-2 rounded-full <?php echo $um['dot']; ?> flex-shrink-0"></span>
                        <span class="font-bold px-2 py-0.5 rounded <?php echo $um['bg']; ?>"><?php echo $u['status']; ?></span>
                        <span class="text-gray-400"><?php echo date('M d, Y · h:i A', strtotime($u['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Image + Map -->
        <div class="space-y-5">
            <?php if(!empty($display_img)): ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 pt-4 pb-2">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <?php echo (end($updates)['proof_image'] ?? '') === $display_img ? 'Resolution Proof 🛡️' : 'Attached Evidence 📷'; ?>
                    </p>
                </div>
                <img src="<?php echo htmlspecialchars($display_img); ?>" alt="Complaint Image"
                     class="w-full object-cover max-h-72 cursor-pointer hover:opacity-90 transition-opacity"
                     onclick="window.open(this.src,'_blank')">
            </div>
            <?php endif; ?>

            <?php if(!empty($complaint['location'])): ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Issue Location 📍</p>
                <div id="complaint-map" class="h-56 w-full rounded-xl border border-gray-200 relative z-10"></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<script>
function toggleProof(sel) {
    const container = document.getElementById('proof-upload');
    const fileInput = container.querySelector('input[type="file"]');
    if (sel.value === 'Resolved') {
        container.classList.remove('hidden');
        fileInput.setAttribute('required','required');
    } else {
        container.classList.add('hidden');
        fileInput.removeAttribute('required');
    }
}

<?php if(!empty($complaint['location'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    var loc = "<?php echo addslashes($complaint['location']); ?>";
    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(loc))
        .then(r => r.json())
        .then(data => {
            if (data && data.length > 0) {
                var lat = parseFloat(data[0].lat), lon = parseFloat(data[0].lon);
                var map = L.map('complaint-map').setView([lat, lon], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors', maxZoom: 19
                }).addTo(map);
                L.marker([lat, lon]).addTo(map)
                    .bindPopup('<b><?php echo addslashes($complaint['title']); ?></b><br>' + loc)
                    .openPopup();
                setTimeout(() => map.invalidateSize(), 250);
            } else {
                document.getElementById('complaint-map').innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 text-sm">Location not found on map.</div>';
            }
        });
});
<?php endif; ?>
</script>
</body>
</html>
