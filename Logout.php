<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GaiaCity - Logout</title>
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

    @keyframes checkmark {
      0%   { stroke-dashoffset: 100; }
      100% { stroke-dashoffset: 0; }
    }
    .checkmark-path {
      stroke-dasharray: 100;
      stroke-dashoffset: 100;
      animation: checkmark 0.6s ease 0.3s forwards;
    }

    @keyframes countdown {
      from { width: 100%; }
      to   { width: 0%; }
    }
    .countdown-bar {
      animation: countdown 5s linear forwards;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 gradient-bg">

  <div class="w-full max-w-md animate-fade-in text-center">

    <!-- Logo -->
    <div class="mb-8">
      <div class="inline-flex items-center space-x-3">
        <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5
                 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15
                 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <span class="text-2xl font-bold text-white">GaiaCity</span>
      </div>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl p-10 shadow-2xl">

      <!-- Animated Checkmark -->
      <div class="flex justify-center mb-6">
        <div class="w-20 h-20 bg-teal-50 rounded-full flex items-center justify-center">
          <svg class="w-10 h-10" viewBox="0 0 52 52" fill="none">
            <circle cx="26" cy="26" r="25" stroke="#0d9488" stroke-width="2" fill="none" opacity="0.3"/>
            <polyline
              class="checkmark-path"
              points="14,27 22,35 38,18"
              stroke="#0d9488"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
              fill="none"
            />
          </svg>
        </div>
      </div>

      <h2 class="text-2xl font-bold text-gray-900 mb-2">Logout Berhasil</h2>
      <p class="text-gray-500 text-sm mb-2">Anda telah keluar dari akun GaiaCity.</p>
      <p class="text-gray-400 text-xs mb-8">Sesi Anda telah dihapus dengan aman.</p>

      <!-- Countdown progress bar -->
      <div class="mb-6">
        <p class="text-xs text-gray-400 mb-2">
          Mengalihkan ke halaman login dalam <span id="countNum">5</span> detik...
        </p>
        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
          <div class="countdown-bar h-1.5 bg-teal-500 rounded-full"></div>
        </div>
      </div>

      <!-- Buttons -->
      <div class="flex flex-col sm:flex-row gap-3">
        <a href="login.php"
           class="flex-1 py-3 bg-teal-600 text-white rounded-lg font-semibold
                  hover:bg-teal-700 active:scale-95 transition-all shadow-md text-sm
                  flex items-center justify-center gap-2">
          <i class="fa-solid fa-right-to-bracket"></i>
          Masuk Kembali
        </a>
        <a href="index.php"
           class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold
                  hover:bg-gray-200 active:scale-95 transition-all text-sm
                  flex items-center justify-center gap-2">
          <i class="fa-solid fa-house"></i>
          Ke Beranda
        </a>
      </div>
    </div>

    <p class="text-white/50 text-xs mt-6">
      &copy; <?= date('Y') ?> GaiaCity &mdash; Smart City Platform
    </p>
  </div>

  <script>
    // Countdown 5 detik lalu redirect ke login
    let seconds = 5;
    const countNum = document.getElementById('countNum');

    const timer = setInterval(() => {
      seconds--;
      countNum.textContent = seconds;
      if (seconds <= 0) {
        clearInterval(timer);
        window.location.href = 'login.php';
      }
    }, 1000);
  </script>
</body>
</html>