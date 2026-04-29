<?php
// edit_complaint.php (Civic Clarity UI)
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$success = '';
$error = '';
$user_id = $_SESSION['user_id'];
$complaint_id = intval($_GET['id'] ?? ($_POST['id'] ?? 0));

if (!$complaint_id) {
    header("Location: dashboard.php");
    exit();
}

if(isset($conn) && !$conn->connect_error) {
    // Check ownership and status
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $complaint_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows !== 1) {
        header("Location: dashboard.php");
        exit();
    }
    $complaint = $res->fetch_assoc();
    $stmt->close();

    if(in_array($complaint['status'], ['Resolved', 'Rejected'])) {
        header("Location: dashboard.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST['title'] ?? '';
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        $location = $_POST['location'] ?? '';
        $attachment = $complaint['attachment']; // keep old attachment by default
        
        // Handle Image Upload Logic
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($_FILES['attachment']['name']);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
                $attachment = $target_file;
            }
        }
        
        // Update DB Logic
        $update_stmt = $conn->prepare("UPDATE complaints SET title=?, category=?, description=?, location=?, attachment=? WHERE id=? AND user_id=?");
        if ($update_stmt) {
            $update_stmt->bind_param("sssssii", $title, $category, $description, $location, $attachment, $complaint_id, $user_id);
            if ($update_stmt->execute()) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Error updating database: " . $conn->error;
            }
            $update_stmt->close();
        }
        // Refresh complaint data
        $complaint['title'] = $title;
        $complaint['category'] = $category;
        $complaint['description'] = $description;
        $complaint['location'] = $location;
    }
} else {
    $error = "Database connection offline.";
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Civic Clarity - File a Complaint</title>
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
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { font-family: 'Inter', sans-serif; min-height: max(884px, 100dvh); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  </head>
<body class="bg-surface text-on-surface selection:bg-primary-fixed">
<header class="fixed top-0 w-full z-50 bg-slate-50/80 backdrop-blur-xl flex items-center justify-between px-6 h-16 w-full shadow-sm">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-blue-700">account_balance</span>
<span class="text-xl font-bold tracking-tighter text-blue-700">Civic Clarity</span>
</div>
<div class="flex items-center gap-6">
<nav class="hidden md:flex items-center gap-6">
<a class="text-slate-500 hover:bg-slate-200/50 transition-colors px-2 py-1 rounded-lg" href="dashboard.php">Home</a>
<a class="text-blue-700 font-semibold px-2 py-1 rounded-lg" href="view_complaints.php">My Complaints</a>
</nav>
<span class="material-symbols-outlined text-slate-500 cursor-pointer active:scale-95 duration-200">account_circle</span>
</div>
</header>

<main class="pt-24 pb-32 px-4 max-w-4xl mx-auto">
<div class="mb-12">
<div class="flex items-center justify-between mb-4">
<span class="text-label-md font-bold uppercase tracking-widest text-primary">Step 1 of 2</span>
<span class="text-on-surface-variant text-sm font-medium">Filing the complaint</span>
</div>
<div class="h-1.5 w-full bg-surface-container rounded-full overflow-hidden">
<div class="h-full w-1/2 bg-primary transition-all duration-500"></div>
</div>
</div>
<section class="grid grid-cols-1 md:grid-cols-12 gap-8">
<div class="md:col-span-8">
<div class="mb-10">
<h1 class="text-4xl font-extrabold tracking-tight text-on-surface mb-2">Edit Complaint</h1>
<p class="text-on-surface-variant text-lg">Update the details of your infrastructure or service problem.</p>
</div>

<?php if($error): ?>
    <div class="mb-4 bg-error-container text-on-error-container p-4 rounded-xl text-sm font-bold shadow-sm">
        <span class="material-symbols-outlined align-middle mr-2">error</span>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if($success): ?>
    <div class="mb-4 bg-tertiary-container text-on-tertiary-container p-4 rounded-xl text-sm font-bold shadow-sm">
        <span class="material-symbols-outlined align-middle mr-2">check_circle</span>
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<!-- FORM FIXES APPLIED HERE -->
<form action="edit_complaint.php" method="POST" enctype="multipart/form-data" class="space-y-8">
<input type="hidden" name="id" value="<?php echo $complaint_id; ?>">
<div class="space-y-2">
<label class="text-sm font-bold uppercase tracking-wider text-on-surface-variant" for="title">Complaint Title</label>
<input name="title" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:border-b-2 focus:border-primary transition-all outline-none text-on-surface placeholder:text-outline" id="title" placeholder="e.g., Pothole on 5th Avenue" type="text" value="<?php echo htmlspecialchars($complaint['title']); ?>" required/>
</div>

<div class="space-y-2 relative">
<label class="text-sm font-bold uppercase tracking-wider text-on-surface-variant" for="location">Location</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline z-20">location_on</span>
<input name="location" class="w-full pl-10 bg-surface-container-highest border-none rounded-lg pr-4 py-3 focus:ring-0 focus:border-b-2 focus:border-primary transition-all outline-none text-on-surface placeholder:text-outline relative z-10" id="location" placeholder="e.g., Intersection of 5th and Main, or click map" type="text" autocomplete="off" value="<?php echo htmlspecialchars($complaint['location'] ?? ''); ?>" required/>
<ul id="suggestions" class="absolute top-full left-0 right-0 bg-surface-container-lowest rounded-b-lg shadow-md z-30 max-h-48 overflow-y-auto hidden border border-outline-variant/10"></ul>
</div>
<div id="map" class="mt-2 h-[250px] w-full rounded-lg border border-outline-variant/20 shadow-sm relative z-0"></div>
</div>

<div class="space-y-4">
<label class="text-sm font-bold uppercase tracking-wider text-on-surface-variant">Issue Category</label>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
<label class="cursor-pointer group flex flex-col items-center justify-center p-4 bg-surface-container-lowest border border-outline-variant/20 rounded-xl hover:bg-primary-fixed transition-all has-[:checked]:bg-primary-fixed has-[:checked]:border-primary/20 has-[:checked]:text-primary">
<input type="radio" name="category" value="Road" class="hidden" required <?php echo ($complaint['category'] == 'Road') ? 'checked' : ''; ?>>
<span class="material-symbols-outlined text-primary mb-2">engineering</span>
<span class="text-xs font-semibold group-has-[:checked]:font-bold group-has-[:checked]:text-primary">Road</span>
</label>
<label class="cursor-pointer group flex flex-col items-center justify-center p-4 bg-surface-container-lowest border border-outline-variant/20 rounded-xl hover:bg-primary-fixed transition-all has-[:checked]:bg-primary-fixed has-[:checked]:border-primary/20 has-[:checked]:text-primary">
<input type="radio" name="category" value="Water" class="hidden" <?php echo ($complaint['category'] == 'Water') ? 'checked' : ''; ?>>
<span class="material-symbols-outlined text-primary mb-2">water_drop</span>
<span class="text-xs font-semibold group-has-[:checked]:font-bold group-has-[:checked]:text-primary">Water</span>
</label>
<label class="cursor-pointer group flex flex-col items-center justify-center p-4 bg-surface-container-lowest border border-outline-variant/20 rounded-xl hover:bg-primary-fixed transition-all has-[:checked]:bg-primary-fixed has-[:checked]:border-primary/20 has-[:checked]:text-primary">
<input type="radio" name="category" value="Electricity" class="hidden" <?php echo ($complaint['category'] == 'Electricity') ? 'checked' : ''; ?>>
<span class="material-symbols-outlined text-primary mb-2">bolt</span>
<span class="text-xs font-semibold group-has-[:checked]:font-bold group-has-[:checked]:text-primary">Electricity</span>
</label>
<label class="cursor-pointer group flex flex-col items-center justify-center p-4 bg-surface-container-lowest border border-outline-variant/20 rounded-xl hover:bg-primary-fixed transition-all has-[:checked]:bg-primary-fixed has-[:checked]:border-primary/20 has-[:checked]:text-primary">
<input type="radio" name="category" value="Garbage" class="hidden" <?php echo ($complaint['category'] == 'Garbage') ? 'checked' : ''; ?>>
<span class="material-symbols-outlined text-primary mb-2">delete</span>
<span class="text-xs font-semibold group-has-[:checked]:font-bold group-has-[:checked]:text-primary">Garbage</span>
</label>
</div>
</div>

<div class="space-y-2">
<label class="text-sm font-bold uppercase tracking-wider text-on-surface-variant" for="desc">Description</label>
<textarea name="description" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:border-b-2 focus:border-primary transition-all outline-none text-on-surface placeholder:text-outline" id="desc" placeholder="Describe the situation in detail..." rows="5" required><?php echo htmlspecialchars($complaint['description']); ?></textarea>
</div>

<div class="space-y-4">
<label class="text-sm font-bold uppercase tracking-wider text-on-surface-variant">Supporting Media (Optional)</label>
<div class="flex gap-4">
<!-- Real File Input layered identically -->
<div class="relative w-32 aspect-square border-2 border-dashed border-outline-variant rounded-xl flex flex-col items-center justify-center text-outline hover:border-primary hover:text-primary transition-all cursor-pointer overflow-hidden">
<input type="file" name="attachment" id="file_upload" class="absolute inset-0 opacity-0 cursor-pointer h-full w-full" onchange="
  if(this.files[0]) {
      document.getElementById('upload_text').innerText = this.files[0].name;
      document.getElementById('upload_text').classList.add('truncate', 'px-2', 'w-full', 'text-center');
      document.getElementById('upload_icon').innerText = 'check_circle';
      this.parentElement.classList.add('border-primary', 'text-primary');
  } else {
      document.getElementById('upload_text').innerText = 'Upload Photo';
      document.getElementById('upload_icon').innerText = 'add_a_photo';
      this.parentElement.classList.remove('border-primary', 'text-primary');
  }
">
<span id="upload_icon" class="material-symbols-outlined text-3xl mb-1 mt-6">add_a_photo</span>
<span id="upload_text" class="text-[10px] font-bold tracking-widest uppercase mb-6 block w-full text-center">Upload Photo</span>
</div>
</div>
</div>

<div class="pt-8 flex flex-col sm:flex-row gap-4 items-center justify-between">
<button class="w-full sm:w-auto bg-primary text-on-primary px-12 py-4 rounded-lg font-bold tracking-tight shadow-[0_8px_24px_rgba(0,91,191,0.2)] hover:bg-primary transition-all active:scale-95" type="submit">
                            Save Changes
</button>
</div>
</form>

</div>

<div class="md:col-span-4 space-y-6">
<div class="bg-surface-container-low p-6 rounded-2xl border border-outline-variant/10">
<div class="w-10 h-10 bg-tertiary-container rounded-lg flex items-center justify-center mb-4">
<span class="material-symbols-outlined text-white">verified</span>
</div>
<h3 class="text-lg font-bold mb-2">Our Quality Promise</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-4">Reports with clear photos and accurate locations are resolved 40% faster by our field teams.</p>
</div>
</div>
</section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map
        const map = L.map('map').setView([12.9716, 77.5946], 12); // Default to Bengaluru
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([12.9716, 77.5946], {draggable: true}).addTo(map);

        const locationInput = document.getElementById('location');
        const suggestionsBox = document.getElementById('suggestions');
        
        // Reverse Geocoding
        function reverseGeocode(latlng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latlng.lat}&lon=${latlng.lng}`)
                .then(res => res.json())
                .then(data => {
                    if(data && data.display_name) {
                        locationInput.value = data.display_name;
                    }
                });
        }

        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            reverseGeocode(position);
            map.panTo(position);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            reverseGeocode(e.latlng);
            map.panTo(e.latlng);
        });

        // Autocomplete
        let debounceTimer;
        locationInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value;
            
            if(query.length < 3) {
                suggestionsBox.classList.add('hidden');
                suggestionsBox.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`)
                    .then(res => res.json())
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        if(data && data.length > 0) {
                            suggestionsBox.classList.remove('hidden');
                            data.forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'p-3 cursor-pointer border-b border-outline-variant/10 text-sm text-on-surface hover:bg-surface-container hover:text-primary transition-colors';
                                li.textContent = item.display_name;
                                li.onclick = () => {
                                    locationInput.value = item.display_name;
                                    suggestionsBox.classList.add('hidden');
                                    const latlng = [item.lat, item.lon];
                                    map.setView(latlng, 15);
                                    marker.setLatLng(latlng);
                                };
                                suggestionsBox.appendChild(li);
                            });
                        } else {
                            suggestionsBox.classList.add('hidden');
                        }
                    });
            }, 500);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if(e.target !== locationInput && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('hidden');
            }
        });

        const existingLocation = "<?php echo addslashes($complaint['location'] ?? ''); ?>";
        if(existingLocation && existingLocation !== 'Location not provided') {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(existingLocation)}&limit=1`)
                .then(res => res.json())
                .then(data => {
                    if(data && data.length > 0) {
                        const latlng = [data[0].lat, data[0].lon];
                        map.setView(latlng, 15);
                        marker.setLatLng(latlng);
                    }
                });
        } else if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const latlng = [position.coords.latitude, position.coords.longitude];
                map.setView(latlng, 14);
                marker.setLatLng(latlng);
            });
        }
    });
</script>

</body></html>
