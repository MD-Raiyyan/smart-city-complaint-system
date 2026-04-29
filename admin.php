<?php
// admin.php (Civic Clarity UI)
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Bouncer Protocol: Kick out anyone without the admin session token
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php?tab=admin");
    exit();
}

include 'db_connect.php';

$categories = ['Road', 'Water', 'Electricity', 'Garbage', 'Animal'];
$complaints_by_cat = [];
foreach($categories as $cat) $complaints_by_cat[$cat] = [];

if(isset($conn) && !$conn->connect_error) {
    $res = $conn->query("SELECT c.id, c.title, c.status, c.location, c.category, u.full_name as user FROM complaints c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC");
    if($res) {
        while($row = $res->fetch_assoc()) {
            if ($row['status'] == 'Pending') {
                $row['bg'] = 'bg-blue-100 text-blue-700';
            } else if ($row['status'] == 'In Progress') {
                $row['bg'] = 'bg-yellow-100 text-yellow-700';
            } else if ($row['status'] == 'Resolved') {
                $row['bg'] = 'bg-green-100 text-green-700';
            } else if ($row['status'] == 'Rejected') {
                $row['bg'] = 'bg-red-100 text-red-700';
            } else {
                $row['bg'] = 'bg-surface-container-high text-on-surface';
            }
            $cat = $row['category'] ?? 'Other';
            if (!isset($complaints_by_cat[$cat])) $complaints_by_cat[$cat] = [];
            $complaints_by_cat[$cat][] = $row;
        }
    }
    
    // Quick counters
    $total_res = $conn->query("SELECT COUNT(*) as c FROM complaints");
    $total = ($total_res && $row = $total_res->fetch_assoc()) ? $row['c'] : 0;
    
    $resolved_res = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE status='Resolved'");
    $resolved = ($resolved_res && $row = $resolved_res->fetch_assoc()) ? $row['c'] : 0;
    
    $all_announcements = [];
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
<title>Civic Clarity | Admin Dashboard</title>
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
<style>
        body { font-family: 'Inter', sans-serif; min-height: max(884px, 100dvh); }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
  </head>
<body class="bg-surface text-on-surface selection:bg-primary-fixed selection:text-on-primary-fixed">

<header class="fixed top-0 w-full z-50 bg-slate-50/80 backdrop-blur-xl flex items-center justify-between px-6 h-16 w-full shadow-none border-b border-surface-container-highest">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-blue-700" data-icon="account_balance">account_balance</span>
<h1 class="text-xl font-bold tracking-tighter text-blue-700">Civic Clarity (Admin)</h1>
</div>
<div class="flex items-center gap-4">
<a href="admin.php?logout=1" class="text-sm font-bold text-red-600">Logout</a>
</div>
</header>
<div class="flex min-h-screen pt-16">

<main class="flex-1 p-6 md:p-10 lg:p-12 overflow-x-hidden max-w-7xl mx-auto">
<section class="mb-10 flex flex-col justify-between gap-6">
<div>
<span class="text-xs font-bold uppercase tracking-[0.2em] text-primary mb-2 block">Executive Overview</span>
<h2 class="text-4xl font-extrabold tracking-tighter text-on-surface">Civic Dashboard</h2>
</div>
</section>

<section class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-12 gap-6 mb-8">
<div class="md:col-span-2 lg:col-span-3 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/10">
<p class="text-sm font-medium text-on-surface-variant mb-1">Total Complaints</p>
<h3 class="text-3xl font-black text-on-surface"><?php echo $total ?? 0; ?></h3>
</div>
<div class="md:col-span-2 lg:col-span-3 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/10">
<p class="text-sm font-medium text-on-surface-variant mb-1">Resolved Cases</p>
<h3 class="text-3xl font-black text-on-surface"><?php echo $resolved ?? 0; ?></h3>
</div>
<div class="md:col-span-4 lg:col-span-6 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/10 flex flex-col">
<div class="flex justify-between items-center mb-6">
<h4 class="text-sm font-bold uppercase tracking-wider text-on-surface-variant">Resolution vs Pending</h4>
</div>
<div class="flex-1 flex items-end gap-3 h-24">
<div class="flex-1 bg-primary rounded-t-lg h-[70%]"></div>
<div class="flex-1 bg-secondary-container rounded-t-lg h-[30%]"></div>
<div class="flex-1 bg-primary rounded-t-lg h-[85%]"></div>
<div class="flex-1 bg-secondary-container rounded-t-lg h-[15%]"></div>
<div class="flex-1 bg-primary rounded-t-lg h-[60%]"></div>
</div>
</div>
</section>

<!-- Admin Action Links -->
<section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <a href="admin_announcements.php" class="bg-primary text-on-primary p-6 rounded-xl shadow-md hover:-translate-y-1 transition-transform flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold mb-1">Manage Announcements</h3>
            <p class="text-white/80 text-sm">Broadcast updates to the citizen dashboard</p>
        </div>
        <span class="material-symbols-outlined text-4xl">campaign</span>
    </a>
</section>

<!-- Manage Complaints By Category -->
<section class="space-y-6">
<h3 class="text-xl font-bold tracking-tight">Manage Complaints by Category</h3>

<?php
$cat_meta = [
    'Road'        => ['icon' => 'engineering',  'color' => 'text-orange-600',  'bg' => 'bg-orange-50',  'border' => 'border-orange-200'],
    'Water'       => ['icon' => 'water_drop',   'color' => 'text-blue-600',    'bg' => 'bg-blue-50',    'border' => 'border-blue-200'],
    'Electricity' => ['icon' => 'bolt',         'color' => 'text-yellow-600',  'bg' => 'bg-yellow-50',  'border' => 'border-yellow-200'],
    'Garbage'     => ['icon' => 'delete',       'color' => 'text-green-600',   'bg' => 'bg-green-50',   'border' => 'border-green-200'],
    'Animal'      => ['icon' => 'pets',         'color' => 'text-purple-600',  'bg' => 'bg-purple-50',  'border' => 'border-purple-200'],
];
foreach($complaints_by_cat as $cat => $rows):
    $meta = $cat_meta[$cat] ?? ['icon' => 'report', 'color' => 'text-gray-600', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'];
    $count = count($rows);
?>
<div class="bg-surface-container-lowest rounded-2xl border <?php echo $meta['border']; ?> shadow-sm overflow-hidden">
  <!-- Category Header -->
  <div class="flex items-center justify-between px-6 py-4 <?php echo $meta['bg']; ?> border-b <?php echo $meta['border']; ?>">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl <?php echo $meta['bg']; ?> border <?php echo $meta['border']; ?> flex items-center justify-center">
        <span class="material-symbols-outlined <?php echo $meta['color']; ?>" style="font-variation-settings:'FILL' 1;"><?php echo $meta['icon']; ?></span>
      </div>
      <div>
        <h4 class="text-base font-extrabold <?php echo $meta['color']; ?> tracking-tight"><?php echo $cat; ?> Issues</h4>
        <p class="text-xs text-outline font-medium"><?php echo $count; ?> complaint<?php echo $count !== 1 ? 's' : ''; ?> registered</p>
      </div>
    </div>
    <span class="text-2xl font-black <?php echo $meta['color']; ?>"><?php echo $count; ?></span>
  </div>

  <?php if($count > 0): ?>
  <div class="overflow-x-auto">
  <table class="w-full text-left min-w-[600px]">
    <thead>
      <tr class="text-[10px] uppercase font-bold tracking-widest text-outline border-b border-outline-variant/20">
        <th class="py-3 px-5">Ticket ID</th>
        <th class="py-3 px-5">User</th>
        <th class="py-3 px-5">Complaint Title</th>
        <th class="py-3 px-5">Location</th>
        <th class="py-3 px-5">Status</th>
        <th class="py-3 px-5">Update Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $row): ?>
      <tr onclick="window.location='admin_complaint_detail.php?id=<?php echo $row['id']; ?>'" class="border-b border-outline-variant/10 hover:bg-blue-50 transition-colors cursor-pointer">
        <td class="py-4 px-5 font-bold text-sm text-on-surface">#CC-<?php echo $row['id']; ?></td>
        <td class="py-4 px-5 text-sm text-on-surface-variant"><?php echo htmlspecialchars($row['user'] ?? '—'); ?></td>
        <td class="py-4 px-5 text-sm font-semibold text-on-surface"><?php echo htmlspecialchars($row['title']); ?></td>
        <td class="py-4 px-5 text-xs text-on-surface-variant max-w-[160px] truncate" title="<?php echo htmlspecialchars($row['location'] ?? 'Not provided'); ?>"><?php echo htmlspecialchars($row['location'] ?? 'Not provided'); ?></td>
        <td class="py-4 px-5">
          <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded <?php echo $row['bg']; ?>"><?php echo $row['status']; ?></span>
        </td>
        <td class="py-4 px-5">
          <form action="update_status.php" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2 items-start">
            <div class="flex gap-2 items-center">
              <input type="hidden" name="complaint_id" value="<?php echo $row['id']; ?>">
              <select name="status" onchange="toggleProofInput(this, 'proof_input_<?php echo $row['id']; ?>')" class="bg-surface-container-high border-none rounded focus:ring-primary text-xs py-1.5 px-2 text-on-surface font-medium cursor-pointer" required>
                <option value="">Set Status...</option>
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Resolved">Resolved</option>
                <option value="Rejected">Rejected</option>
              </select>
              <button type="submit" class="bg-primary text-on-primary text-xs font-bold px-3 py-1.5 rounded hover:bg-blue-700 transition-colors">Save</button>
            </div>
            <div id="proof_input_<?php echo $row['id']; ?>" class="hidden w-full mt-1">
              <input type="file" name="proof_image" accept="image/*" class="w-full text-[10px] file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-surface-container-highest file:text-on-surface hover:file:bg-surface-variant transition-all cursor-pointer">
              <p class="text-[9px] text-error font-bold mt-1 ml-1 tracking-wider uppercase">* Required for resolution</p>
            </div>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php else: ?>
  <div class="flex items-center gap-3 px-6 py-8 text-outline">
    <span class="material-symbols-outlined">inbox</span>
    <p class="text-sm font-medium">No complaints registered under this category yet.</p>
  </div>
  <?php endif; ?>
</div>
<?php endforeach; ?>

</section>

</main>
</div>
<script>
function toggleProofInput(selectElement, inputContainerId) {
    var container = document.getElementById(inputContainerId);
    var fileInput = container.querySelector('input[type="file"]');
    if (selectElement.value === 'Resolved') {
        container.classList.remove('hidden');
        fileInput.setAttribute('required', 'required');
    } else {
        container.classList.add('hidden');
        fileInput.removeAttribute('required');
    }
}
</script>
</body></html>
