<?php
// login.php (Civic Clarity UI)
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: admin.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['login_type'] ?? 'citizen';

    if ($type === 'admin') {
        $username = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        if ($username === 'government' && $password === 'jai hind') {
            $_SESSION['is_admin'] = true;
            header("Location: admin.php");
            exit();
        } else {
            $error = "Wrong username or password.";
        }
    } else {
        error_reporting(0);
        include 'db_connect.php';
        error_reporting(E_ALL);

        if (isset($conn) && !$conn->connect_error) {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($id, $hashed_password);
                    $stmt->fetch();
                    if (password_verify($password, $hashed_password)) {
                        $_SESSION['user_id'] = $id;
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Wrong email or password.";
                    }
                } else {
                    $error = "Wrong email or password.";
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
<title>Civic Clarity | Secure Access</title>
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
<!-- TopAppBar Fragment -->
<header class="fixed top-0 w-full z-50 flex items-center justify-between px-8 h-20 bg-transparent">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-2xl" data-icon="account_balance">account_balance</span>
<span class="text-2xl font-bold tracking-tighter text-blue-700">Civic Clarity</span>
</div>
<div class="flex items-center gap-6">
</div>
</header>
<main class="relative min-h-screen flex items-center justify-center lg:justify-start px-6 lg:px-24">
<!-- Background Image with Overlay -->
<div class="fixed inset-0 z-0">
<img alt="Modern sustainable city skyline" class="w-full h-full object-cover" data-alt="Stunning panoramic view of a modern clean city skyline" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCrGD81tzolycKOZjnrb_L-s7k0-jJmL6I53dOGLx-zWJLnXQtgMfuRQ5229Zkuo7Y8a3mSZPSvxaebFCVF3EiwFcT4cy4Ng5PLFVAeBJHTRx6BWOp5IUJq_UjJQORBxV1NTreUuNydlJpsU0W6HX11GTFhL8Sofp8PINdjy8oqqVaUygGTxF-Bpu7VMAoBgT93JzTBDMXd3O9r-rSLxqo00P7z1RxjCBt9j597Vxvnp-8IMOMw1vWNCXM4qIPIqhmzgHEoHJN8niLt"/>
<div class="absolute inset-0 bg-gradient-to-r from-surface via-surface/60 to-transparent"></div>
</div>
<!-- Auth Canvas -->
<div class="relative z-10 w-full max-w-lg">
<div class="mb-10">
<span class="inline-block px-3 py-1 bg-surface-container-highest text-on-surface-variant text-[10px] font-bold uppercase tracking-widest rounded-full mb-4">
                    Official Portal
                </span>
<h1 class="text-5xl lg:text-6xl font-extrabold tracking-tighter text-on-surface mb-4 leading-none">
                    Welcome to your <br/><span class="text-primary">Digital City.</span>
</h1>
<p class="text-lg text-on-surface-variant max-w-md font-medium leading-relaxed">
                    Access city services, track complaints, and participate in civic decisions through your secure unified portal.
                </p>
</div>
<!-- Login Card -->
<div class="bg-surface-container-lowest p-8 lg:p-10 rounded-xl shadow-[0_8px_40px_rgba(0,0,0,0.08)] border border-outline-variant/10">
<!-- Toggle Tab Asymmetry -->
<?php if($error): ?>
    <div class="mb-4 bg-error-container text-on-error-container p-3 rounded-md text-sm font-bold text-center">
        <?php echo $error; ?>
    </div>
<?php endif; ?>
<div class="flex items-center gap-1 p-1 bg-surface-container-low rounded-lg mb-8">
<button type="button" id="tab_cit" onclick="switchLogin('citizen')" class="flex-1 text-center py-3 px-4 rounded-md text-sm font-bold bg-surface-container-lowest text-primary shadow-sm transition-colors">
                        Citizen Login
</button>
<button type="button" id="tab_adm" onclick="switchLogin('admin')" class="flex-1 text-center py-3 px-4 rounded-md text-sm font-bold text-on-surface-variant hover:bg-surface-container-highest transition-colors">
                        Admin Login
</button>
</div>

<!-- THIS IS THE FORM WE FIXED -->
<form action="login.php" method="POST" class="space-y-6">
<input type="hidden" name="login_type" id="login_type" value="citizen">
<div class="space-y-2">
<label id="lbl_email" class="text-xs font-bold uppercase tracking-widest ml-1 <?php echo $error ? 'text-error' : 'text-on-surface-variant'; ?>">Email Address</label>
<div class="relative">
<span id="icon_email" class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 <?php echo $error ? 'text-error' : 'text-outline'; ?>">mail</span>
<input id="input_email" name="email" class="w-full pl-12 pr-4 py-4 border-b-2 focus:ring-0 transition-all rounded-t-lg outline-none font-medium <?php echo $error ? 'bg-error-container/30 border-error text-error focus:border-error' : 'bg-surface-container-highest border-transparent text-on-surface focus:border-primary'; ?>" placeholder="name@domain.gov" type="text" required/>
</div>
</div>
<div class="space-y-2">
<label class="text-xs font-bold uppercase tracking-widest ml-1 <?php echo $error ? 'text-error' : 'text-on-surface-variant'; ?>">Secure Password</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 <?php echo $error ? 'text-error' : 'text-outline'; ?>">lock</span>
<input name="password" class="w-full pl-12 pr-4 py-4 border-b-2 focus:ring-0 transition-all rounded-t-lg outline-none font-medium <?php echo $error ? 'bg-error-container/30 border-error text-error focus:border-error' : 'bg-surface-container-highest border-transparent text-on-surface focus:border-primary'; ?>" placeholder="••••••••" type="password" required/>
</div>
</div>
<div class="flex items-center justify-between py-2">
<label class="flex items-center gap-2 cursor-pointer group">
<input class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/20" type="checkbox"/>
<span class="text-sm font-medium text-on-surface-variant group-hover:text-on-surface">Remember me</span>
</label>
<a class="text-sm font-bold text-primary hover:underline underline-offset-4" href="#">Forgot Password?</a>
</div>
<button class="w-full py-4 bg-primary text-on-primary rounded-lg font-extrabold text-lg tracking-tight hover:shadow-xl hover:shadow-primary/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2" type="submit">
                        Sign In
                        <span class="material-symbols-outlined" data-icon="arrow_forward">arrow_forward</span>
</button>
</form>

<div id="register_box" class="mt-10 pt-8 border-t border-outline-variant/20">
<div class="flex flex-col gap-4">
<p class="text-sm text-center text-on-surface-variant font-medium">New to Civic Clarity?</p>
<a href="register.php" class="text-center w-full py-3 border border-outline-variant rounded-lg font-bold text-on-surface hover:bg-surface-container-low transition-colors">
                            Create Citizen Account
</a>
</div>
</div>
</div>
</div>

<script>
function switchLogin(type) {
    document.getElementById('login_type').value = type;
    if (type === 'admin') {
        document.getElementById('tab_cit').className = "flex-1 text-center py-3 px-4 rounded-md text-sm font-bold text-on-surface-variant hover:bg-surface-container-highest transition-colors";
        document.getElementById('tab_adm').className = "flex-1 text-center py-3 px-4 rounded-md text-sm font-bold bg-surface-container-lowest text-primary shadow-sm transition-colors";
        
        document.getElementById('lbl_email').innerText = "Admin Username";
        document.getElementById('icon_email').innerText = "shield_person";
        document.getElementById('input_email').placeholder = "admin";
        
        document.getElementById('register_box').style.display = 'none';
    } else {
        document.getElementById('tab_adm').className = "flex-1 text-center py-3 px-4 rounded-md text-sm font-bold text-on-surface-variant hover:bg-surface-container-highest transition-colors";
        document.getElementById('tab_cit').className = "flex-1 text-center py-3 px-4 rounded-md text-sm font-bold bg-surface-container-lowest text-primary shadow-sm transition-colors";
        
        document.getElementById('lbl_email').innerText = "Email Address";
        document.getElementById('icon_email').innerText = "mail";
        document.getElementById('input_email').placeholder = "name@domain.gov";
        
        document.getElementById('register_box').style.display = 'block';
    }
}
<?php 
if (isset($_GET['tab']) && $_GET['tab'] == 'admin') echo "switchLogin('admin');"; 
if (isset($_POST['login_type']) && $_POST['login_type'] == 'admin') echo "switchLogin('admin');"; 
?>
</script>

</main>
<footer class="fixed bottom-0 left-0 w-full px-8 py-6 flex flex-col md:flex-row justify-between items-center text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant/60 z-10">
<div class="flex gap-6 mb-4 md:mb-0">
<span>© 2024 Civic Clarity Authority</span>
</div>
</footer>
</body></html>
