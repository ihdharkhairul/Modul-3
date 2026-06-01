<?php
session_start();

$errors = [];
$old = ['name' => '', 'email' => '', 'role' => 'citizen'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');
    $role     = trim($_POST['role']     ?? '');

    // Simpan nilai lama untuk sticky form
    $old = compact('name', 'email', 'role');

    // ── Validasi Server-Side ──────────────────────────────────

    // Nama
    if (empty($name)) {
        $errors['name'] = 'Nama lengkap wajib diisi.';
    } elseif (strlen($name) < 3) {
        $errors['name'] = 'Nama minimal 3 karakter.';
    } elseif (!preg_match('/^[\p{L}\s\'\-\.]+$/u', $name)) {
        $errors['name'] = 'Nama hanya boleh mengandung huruf, spasi, atau tanda baca umum.';
    }

    // Email
    if (empty($email)) {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    } else {
        // Simulasi cek email sudah terdaftar (ganti dengan query DB di produksi)
        $registered_emails = ['admin@gmail.com', 'officer@gmail.com', 'user@gmail.com'];
        if (in_array(strtolower($email), $registered_emails)) {
            $errors['email'] = 'Email sudah terdaftar. Silakan gunakan email lain.';
        }
    }

    // Password
    if (empty($password)) {
        $errors['password'] = 'Password wajib diisi.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password minimal 8 karakter.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password harus mengandung minimal 1 huruf kapital.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password harus mengandung minimal 1 angka.';
    }

    // Konfirmasi Password
    if (empty($confirm)) {
        $errors['confirm'] = 'Konfirmasi password wajib diisi.';
    } elseif ($password !== $confirm) {
        $errors['confirm'] = 'Konfirmasi password tidak cocok.';
    }

    // Role
    $valid_roles = ['citizen', 'officer'];
    if (!in_array($role, $valid_roles)) {
        $errors['role'] = 'Pilihan role tidak valid.';
    }

    // ── Proses Registrasi ────────────────────────────────────
    if (empty($errors)) {
        // Di produksi: simpan ke database dengan password_hash($password, PASSWORD_DEFAULT)
        // Contoh: $hashed = password_hash($password, PASSWORD_DEFAULT);
        // INSERT INTO users (name, email, password, role) VALUES (...)

        $_SESSION['register_success'] = 'Akun berhasil dibuat! Silakan masuk.';
        header('Location: login.php');
        exit;
    }
}

// Helper functions
function err(string $key, array $errors): string {
    if (isset($errors[$key])) {
        return '<p class="text-red-500 text-xs mt-1 server-error">'
             . htmlspecialchars($errors[$key]) . '</p>';
    }
    return '';
}

function inputClass(string $key, array $errors): string {
    $base = 'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 '
          . 'focus:ring-teal-500 focus:border-transparent transition-all text-sm';
    return $base . (isset($errors[$key]) ? ' border-red-400' : ' border-gray-300');
}

function old(string $key, array $old): string {
    return htmlspecialchars($old[$key] ?? '');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GaiaCity - Daftar</title>
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

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%       { transform: translateX(-6px); }
      40%       { transform: translateX(6px); }
      60%       { transform: translateX(-4px); }
      80%       { transform: translateX(4px); }
    }
    .shake { animation: shake 0.4s ease; }

    /* Password strength bar */
    .strength-bar { transition: width 0.3s ease, background-color 0.3s ease; }
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
    <div class="bg-white rounded-2xl p-8 shadow-2xl" id="registerCard">
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Buat Akun Baru</h2>
        <p class="text-gray-500 mt-1 text-sm">Bergabung dengan komunitas peduli lingkungan</p>
      </div>

      <form method="POST" action="" id="registerForm" novalidate>

        <!-- Nama Lengkap -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2 text-sm" for="name">Nama Lengkap</label>
          <input
            id="name"
            name="name"
            type="text"
            value="<?= old('name', $old) ?>"
            class="<?= inputClass('name', $errors) ?>"
            placeholder="John Doe"
          />
          <?= err('name', $errors) ?>
        </div>

        <!-- Email -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2 text-sm" for="email">Email</label>
          <input
            id="email"
            name="email"
            type="email"
            value="<?= old('email', $old) ?>"
            class="<?= inputClass('email', $errors) ?>"
            placeholder="email@example.com"
          />
          <?= err('email', $errors) ?>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2 text-sm" for="password">Password</label>
          <div class="relative">
            <input
              id="password"
              name="password"
              type="password"
              class="<?= inputClass('password', $errors) ?> pr-10"
              placeholder="Min. 8 karakter, 1 kapital, 1 angka"
            />
            <button type="button" onclick="togglePass('password','eyePass')"
              class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600" tabindex="-1">
              <i id="eyePass" class="fa-regular fa-eye text-sm"></i>
            </button>
          </div>
          <!-- Password strength indicator -->
          <div class="mt-2">
            <div class="w-full bg-gray-100 rounded-full h-1.5">
              <div id="strengthBar" class="strength-bar h-1.5 rounded-full w-0"></div>
            </div>
            <p id="strengthLabel" class="text-xs mt-1 text-gray-400"></p>
          </div>
          <?= err('password', $errors) ?>
        </div>

        <!-- Konfirmasi Password -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-2 text-sm" for="confirm">Konfirmasi Password</label>
          <div class="relative">
            <input
              id="confirm"
              name="confirm"
              type="password"
              class="<?= inputClass('confirm', $errors) ?> pr-10"
              placeholder="Ulangi password"
            />
            <button type="button" onclick="togglePass('confirm','eyeConfirm')"
              class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600" tabindex="-1">
              <i id="eyeConfirm" class="fa-regular fa-eye text-sm"></i>
            </button>
          </div>
          <?= err('confirm', $errors) ?>
        </div>

        <!-- Role -->
        <div class="mb-6">
          <label class="block text-gray-700 font-medium mb-2 text-sm" for="role">Role</label>
          <select
            id="role"
            name="role"
            class="<?= inputClass('role', $errors) ?> bg-white"
          >
            <option value="citizen"  <?= old('role', $old) === 'citizen'  ? 'selected' : '' ?>>Citizen (Warga)</option>
            <option value="officer"  <?= old('role', $old) === 'officer'  ? 'selected' : '' ?>>Field Officer (Petugas Lapangan)</option>
          </select>
          <?= err('role', $errors) ?>
        </div>

        <button
          type="submit"
          id="submitBtn"
          class="w-full py-3 bg-teal-600 text-white rounded-lg font-semibold
                 hover:bg-teal-700 active:scale-95 transition-all shadow-lg text-sm
                 disabled:opacity-60 disabled:cursor-not-allowed"
        >
          Daftar Sekarang
        </button>

        <p class="text-center mt-6 text-gray-500 text-sm">
          Sudah punya akun?
          <a href="login.php" class="text-teal-600 font-medium hover:underline">Masuk</a>
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
    const form       = document.getElementById('registerForm');
    const card       = document.getElementById('registerCard');
    const submitBtn  = document.getElementById('submitBtn');

    const fields = {
      name:     document.getElementById('name'),
      email:    document.getElementById('email'),
      password: document.getElementById('password'),
      confirm:  document.getElementById('confirm'),
    };

    // ── Helpers ──────────────────────────────────────────────────
    function showError(input, message) {
      clearError(input);
      input.classList.add('border-red-400');
      input.classList.remove('border-gray-300');
      const p = document.createElement('p');
      p.className = 'text-red-500 text-xs mt-1 client-error';
      p.textContent = message;
      // insert after the input's direct wrapper (handle relative div for password)
      const wrapper = input.closest('.relative') || input;
      wrapper.insertAdjacentElement('afterend', p);
    }

    function clearError(input) {
      input.classList.remove('border-red-400');
      input.classList.add('border-gray-300');
      const wrapper = input.closest('.relative') || input;
      const next = wrapper.nextElementSibling;
      if (next && next.classList.contains('client-error')) next.remove();
    }

    // ── Validasi per-field ───────────────────────────────────────
    function validateName() {
      const v = fields.name.value.trim();
      if (!v)          return showError(fields.name, 'Nama lengkap wajib diisi.'), false;
      if (v.length < 3) return showError(fields.name, 'Nama minimal 3 karakter.'), false;
      if (!/^[\w\s'\-\.]+$/i.test(v)) return showError(fields.name, 'Nama mengandung karakter tidak valid.'), false;
      clearError(fields.name);
      return true;
    }

    function validateEmail() {
      const v = fields.email.value.trim();
      if (!v) return showError(fields.email, 'Email wajib diisi.'), false;
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return showError(fields.email, 'Format email tidak valid.'), false;
      clearError(fields.email);
      return true;
    }

    function validatePassword() {
      const v = fields.password.value;
      if (!v)          return showError(fields.password, 'Password wajib diisi.'), false;
      if (v.length < 8) return showError(fields.password, 'Password minimal 8 karakter.'), false;
      if (!/[A-Z]/.test(v)) return showError(fields.password, 'Harus ada minimal 1 huruf kapital.'), false;
      if (!/[0-9]/.test(v)) return showError(fields.password, 'Harus ada minimal 1 angka.'), false;
      clearError(fields.password);
      return true;
    }

    function validateConfirm() {
      const v = fields.confirm.value;
      if (!v) return showError(fields.confirm, 'Konfirmasi password wajib diisi.'), false;
      if (v !== fields.password.value) return showError(fields.confirm, 'Konfirmasi password tidak cocok.'), false;
      clearError(fields.confirm);
      return true;
    }

    // Blur listeners
    fields.name.addEventListener('blur', validateName);
    fields.email.addEventListener('blur', validateEmail);
    fields.password.addEventListener('blur', validatePassword);
    fields.confirm.addEventListener('blur', validateConfirm);

    // Input listeners (hapus error saat mengetik)
    Object.values(fields).forEach(f => f.addEventListener('input', () => clearError(f)));

    // Re-validate confirm saat password berubah
    fields.password.addEventListener('input', () => {
      if (fields.confirm.value) validateConfirm();
    });

    // ── Password Strength ────────────────────────────────────────
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');

    fields.password.addEventListener('input', () => {
      const v = fields.password.value;
      let score = 0;
      if (v.length >= 8)        score++;
      if (/[A-Z]/.test(v))      score++;
      if (/[0-9]/.test(v))      score++;
      if (/[^A-Za-z0-9]/.test(v)) score++;
      if (v.length >= 12)       score++;

      const levels = [
        { w: '0%',   color: '',               text: '' },
        { w: '25%',  color: 'bg-red-400',     text: 'Sangat Lemah' },
        { w: '50%',  color: 'bg-orange-400',  text: 'Lemah' },
        { w: '75%',  color: 'bg-yellow-400',  text: 'Cukup' },
        { w: '90%',  color: 'bg-teal-400',    text: 'Kuat' },
        { w: '100%', color: 'bg-teal-600',    text: 'Sangat Kuat' },
      ];

      bar.className = `strength-bar h-1.5 rounded-full ${levels[score].color}`;
      bar.style.width = v.length ? levels[score].w : '0%';
      label.textContent = v.length ? levels[score].text : '';
      label.className   = `text-xs mt-1 ${levels[score].color.replace('bg-', 'text-')}`;
    });

    // ── Toggle Password Visibility ───────────────────────────────
    function togglePass(inputId, iconId) {
      const inp  = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      const show = inp.type === 'password';
      inp.type   = show ? 'text' : 'password';
      icon.className = show ? 'fa-regular fa-eye-slash text-sm' : 'fa-regular fa-eye text-sm';
    }

    // ── Submit ───────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
      const v1 = validateName();
      const v2 = validateEmail();
      const v3 = validatePassword();
      const v4 = validateConfirm();

      if (!v1 || !v2 || !v3 || !v4) {
        e.preventDefault();
        card.classList.add('shake');
        card.addEventListener('animationend', () => card.classList.remove('shake'), { once: true });
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Mendaftarkan...';
    });
  </script>
</body>
</html>