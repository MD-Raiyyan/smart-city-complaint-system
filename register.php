<?php
// register.php (Civic Clarity UI)
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_reporting(0);
    include 'db_connect.php';
    error_reporting(E_ALL);

    if (isset($conn) && !$conn->connect_error) {
        $full_name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($full_name && $email && $password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $full_name, $email, $hashed);
                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Email might already be registered.";
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Civic Clarity | Register</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "on-surface-variant": "#414754",
                    "primary-container": "#1a73e8",
                    "error": "#ba1a1a",
                    "surface-container-low": "#f3f4f5",
                    "surface-container-lowest": "#ffffff",
                    "primary": "#005bbf",
                    "on-primary": "#ffffff",
                    "outline-variant": "#c1c6d6",
                    "surface-container-highest": "#e1e3e4",
                    "outline": "#727785",
                    "surface": "#f8f9fa",
                    "on-surface": "#191c1d"
            },
            "fontFamily": {
                    "headline": ["Inter"],
                    "body": ["Inter"],
                    "label": ["Inter"]
            }
          },
        }
      }
    </script>
<style>
        body { font-family: 'Inter', sans-serif; min-height: max(884px, 100dvh); }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
  </head>
<body class="bg-surface text-on-surface antialiased min-h-screen overflow-x-hidden">
<header class="fixed top-0 w-full z-50 flex items-center justify-between px-8 h-20 bg-transparent">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-2xl" data-icon="account_balance">account_balance</span>
<span class="text-2xl font-bold tracking-tighter text-blue-700">Civic Clarity</span>
</div>
</header>
<main class="relative min-h-screen flex items-center justify-center lg:justify-start px-6 lg:px-24">
<div class="fixed inset-0 z-0">
<img alt="Modern sustainable city skyline" class="w-full h-full object-cover" data-alt="Stunning panoramic view of a modern clean city skyline" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCrGD81tzolycKOZjnrb_L-s7k0-jJmL6I53dOGLx-zWJLnXQtgMfuRQ5229Zkuo7Y8a3mSZPSvxaebFCVF3EiwFcT4cy4Ng5PLFVAeBJHTRx6BWOp5IUJq_UjJQORBxV1NTreUuNydlJpsU0W6HX11GTFhL8Sofp8PINdjy8oqqVaUygGTxF-Bpu7VMAoBgT93JzTBDMXd3O9r-rSLxqo00P7z1RxjCBt9j597Vxvnp-8IMOMw1vWNCXM4qIPIqhmzgHEoHJN8niLt"/>
<div class="absolute inset-0 bg-gradient-to-r from-surface via-surface/60 to-transparent"></div>
</div>
<div class="relative z-10 w-full max-w-lg">
<div class="mb-10">
<h1 class="text-5xl lg:text-6xl font-extrabold tracking-tighter text-on-surface mb-4 leading-none">
                    Join Your <br/><span class="text-primary">Digital City.</span>
</h1>
</div>
<div class="bg-surface-container-lowest p-8 lg:p-10 rounded-xl shadow-[0_8px_40px_rgba(0,0,0,0.08)] border border-outline-variant/10">
<?php if($error): ?>
    <div class="mb-4 bg-error-container text-on-error-container p-3 rounded-md text-sm font-bold text-center">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<form action="register.php" method="POST" class="space-y-6">
<div class="space-y-2">
<label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant ml-1">Full Name</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" data-icon="person">person</span>
<input name="full_name" class="w-full pl-12 pr-4 py-4 bg-surface-container-highest border-b-2 border-transparent focus:border-primary focus:ring-0 transition-all rounded-t-lg outline-none text-on-surface font-medium" placeholder="Jane Doe" type="text" required/>
</div>
</div>
<div class="space-y-2">
<label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant ml-1">Email Address</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" data-icon="mail">mail</span>
<input name="email" class="w-full pl-12 pr-4 py-4 bg-surface-container-highest border-b-2 border-transparent focus:border-primary focus:ring-0 transition-all rounded-t-lg outline-none text-on-surface font-medium" placeholder="name@domain.gov" type="email" required/>
</div>
</div>
<div class="space-y-2">
<label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant ml-1">Secure Password</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" data-icon="lock">lock</span>
<input name="password" class="w-full pl-12 pr-4 py-4 bg-surface-container-highest border-b-2 border-transparent focus:border-primary focus:ring-0 transition-all rounded-t-lg outline-none text-on-surface font-medium" placeholder="••••••••" type="password" required/>
</div>
</div>
<button class="w-full py-4 bg-primary text-on-primary rounded-lg font-extrabold text-lg tracking-tight hover:shadow-xl hover:shadow-primary/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2" type="submit">
                        Create Account
</button>
</form>

<div class="mt-10 pt-8 border-t border-outline-variant/20">
<p class="text-sm text-center text-on-surface-variant font-medium">Already have an account?</p>
<a href="login.php" class="mt-4 block text-center w-full py-3 border border-outline-variant rounded-lg font-bold text-on-surface hover:bg-surface-container-low transition-colors">
                            Sign In Instead
</a>
</div>
</div>
</div>
</main>
</body></html>
