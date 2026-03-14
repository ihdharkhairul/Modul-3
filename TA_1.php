<?php
session_start();

$errors = [];
$email = '';

// Daftar akun valid (simulasi — ganti dengan query database di produksi)
$valid_accounts = [
    'admin@gmail.com'   => ['password' => 'admin123',   'role' => 'admin'],
    'officer@gmail.com' => ['password' => 'officer123', 'role' => 'officer'],
    'user@gmail.com'    => ['password' => 'user123',    'role' => 'citizen'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // ── Validasi Server-Side ──────────────────────────────────
    if (empty($email)) {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }

    // ── Cek Kredensial ───────────────────────────────────────
    if (empty($errors)) {
        if (isset($valid_accounts[$email])) {
            $account = $valid_accounts[$email];

            // Gunakan password_verify() jika pakai password_hash() di DB
            if ($password === $account['password']) {
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role']  = $account['role'];

                // Redirect sesuai role
                switch ($account['role']) {
                    case 'admin':
                        header('Location: ../Admin/dashboard.php');
                        exit;
                    case 'officer':
                        header('Location: ../OfficerDashboard/index.php');
                        exit;
                    default:
                        header('Location: ../Citizen_Dashboard/index.php');
                        exit;
                }
            } else {
                $errors['general'] = 'Email atau password salah.';
            }
        } else {
            $errors['general'] = 'Email atau password salah.';
        }
    }
}

// Helper: tampilkan pesan error
function err(string $key, array $errors): string {
    if (isset($errors[$key])) {
        return '<p class="text-red-500 text-xs mt-1">'
             . htmlspecialchars($errors[$key])
             . '</p>';
    }
    return '';
}

// Highlight border merah jika error
function inputClass(string $key, array $errors): string {
    $base = 'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 '
          . 'focus:ring-teal-500 focus:border-transparent transition-all text-sm';
    return $base . (isset($errors[$key]) ? ' border-red-400' : ' border-gray-300');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GaiaCity - Masuk</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");
    * { font-family: "Inter", sans-serif; }

    .gradient-bg {
      background: linear-gradient(135deg, #0f766e 0%, #134e4a 50%, #064e3b 100%);
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in { animation: fadeInUp 0.6s ease-out forwards; }

    /* Shake animation untuk error */
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%       { transform: translateX(-6px); }
      40%       { transform: translateX(6px); }
      60%       { transform: translateX(-4px); }
      80%       { transform: translateX(4px); }
    }
    .shake { animation: shake 0.4s ease; }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 gradient-bg">

  <div class="w-full max-w-md animate-fade-in">

    <!-- Logo -->
    <div class="text-center mb-8">
      <a href="index.php" class="inline-flex items-center space-x-3">
        <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5
                 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15
                 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <span class="text-2xl font-bold text-white">GaiaCity</span>
      </a>
      <p class="text-white/70 mt-2 text-sm">Platform Smart City Terintegrasi</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl p-8 shadow-2xl" id="loginCard">
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Selamat Datang Kembali</h2>
        <p class="text-gray-500 mt-1 text-sm">Masuk ke akun GaiaCity Anda</p>
      </div>

      <!-- Error umum (kredensial salah) -->
      <?php if (!empty($errors['general'])): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2 text-red-700 text-sm">
          <i class="fa-solid fa-circle-exclamation"></i>
          <?= htmlspecialchars($errors['general']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" id="loginForm" novalidate>

        <!-- Email -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2 text-sm" for="email">Email</label>
          <input
            id="email"
            name="email"
            type="email"
            value="<?= htmlspecialchars($email) ?>"
            class="<?= inputClass('email', $errors) ?>"
            placeholder="email@example.com"
          />
          <?= err('email', $errors) ?>
        </div>

        <!-- Password -->
        <div class="mb-2">
          <label class="block text-gray-700 font-medium mb-2 text-sm" for="password">Password</label>
          <div class="relative">
            <input
              id="password"
              name="password"
              type="password"
              class="<?= inputClass('password', $errors) ?> pr-10"
              placeholder="••••••••"
            />
            <!-- Toggle show/hide password -->
            <button
              type="button"
              onclick="togglePassword()"
              class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
              tabindex="-1"
            >
              <i id="eyeIcon" class="fa-regular fa-eye text-sm"></i>
            </button>
          </div>
          <?= err('password', $errors) ?>
        </div>

        <div class="flex justify-end mb-6">
          <a href="forgot-password.php" class="text-sm text-teal-600 hover:underline">Lupa password?</a>
        </div>

        <button
          type="submit"
          id="submitBtn"
          class="w-full py-3 bg-teal-600 text-white rounded-lg font-semibold
                 hover:bg-teal-700 active:scale-95 transition-all shadow-lg text-sm
                 disabled:opacity-60 disabled:cursor-not-allowed"
        >
          Masuk
        </button>

        <p class="text-center mt-6 text-gray-500 text-sm">
          Belum punya akun?
          <a href="register.php" class="text-teal-600 font-medium hover:underline">Daftar Sekarang</a>
        </p>
      </form>
    </div>

    <!-- Back to home -->
    <div class="text-center mt-6">
      <a href="index.php" class="text-white/70 hover:text-white text-sm transition-colors">
        &larr; Kembali ke Beranda
      </a>
    </div>
  </div>

  <!-- ── JavaScript: Validasi Client-Side ───────────────────────── -->
  <script>
    const form        = document.getElementById('loginForm');
    const emailInput  = document.getElementById('email');
    const passInput   = document.getElementById('password');
    const submitBtn   = document.getElementById('submitBtn');
    const card        = document.getElementById('loginCard');

    // ── Helper: tampilkan / hapus error inline ───────────────────
    function showError(input, message) {
      clearError(input);
      input.classList.add('border-red-400');
      input.classList.remove('border-gray-300');
      const p = document.createElement('p');
      p.className = 'text-red-500 text-xs mt-1 client-error';
      p.textContent = message;
      input.closest('div').appendChild(p);
    }

    function clearError(input) {
      input.classList.remove('border-red-400');
      input.classList.add('border-gray-300');
      input.closest('div').querySelectorAll('.client-error').forEach(el => el.remove());
    }

    // ── Validasi per-field secara real-time ─────────────────────
    emailInput.addEventListener('blur', () => {
      const val = emailInput.value.trim();
      if (!val) {
        showError(emailInput, 'Email wajib diisi.');
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
        showError(emailInput, 'Format email tidak valid.');
      } else {
        clearError(emailInput);
      }
    });

    passInput.addEventListener('blur', () => {
      const val = passInput.value;
      if (!val) {
        showError(passInput, 'Password wajib diisi.');
      } else if (val.length < 6) {
        showError(passInput, 'Password minimal 6 karakter.');
      } else {
        clearError(passInput);
      }
    });

    // Bersihkan error saat user mengetik
    [emailInput, passInput].forEach(input => {
      input.addEventListener('input', () => clearError(input));
    });

    // ── Validasi saat submit ─────────────────────────────────────
    form.addEventListener('submit', function (e) {
      let valid = true;

      const email = emailInput.value.trim();
      const pass  = passInput.value;

      if (!email) {
        showError(emailInput, 'Email wajib diisi.');
        valid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError(emailInput, 'Format email tidak valid.');
        valid = false;
      }

      if (!pass) {
        showError(passInput, 'Password wajib diisi.');
        valid = false;
      } else if (pass.length < 6) {
        showError(passInput, 'Password minimal 6 karakter.');
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
        card.classList.add('shake');
        card.addEventListener('animationend', () => card.classList.remove('shake'), { once: true });
        return;
      }

      // Loading state
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Memproses...';
    });

    // ── Toggle Password Visibility ───────────────────────────────
    function togglePassword() {
      const isPassword = passInput.type === 'password';
      passInput.type = isPassword ? 'text' : 'password';
      document.getElementById('eyeIcon').className =
        isPassword ? 'fa-regular fa-eye-slash text-sm' : 'fa-regular fa-eye text-sm';
    }
  </script>
</body>
</html>