<?php
// dashboard.php - Admin Dashboard GaiaCity
session_start();

// ── Guard: hanya admin yang boleh akses ──────────────────────────
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_name  = $_SESSION['user_name']  ?? 'Admin';
$user_email = $_SESSION['user_email'] ?? 'admin@gaiacity.id';

// Simulasi data (bisa diganti dengan query database)
$stats = [
    'sensor_aktif' => 24,
    'alert_aktif' => 7,
    'alert_critical' => 3,
    'laporan_pending' => 15,
    'pohon_ditanam' => 1234,
    'target_persen' => 85,
];

$aktivitas = [
    [
        'icon' => '⚠',
        'icon_bg' => 'bg-red-500/20',
        'icon_color' => 'text-red-400',
        'judul' => 'Alert: PM2.5 Tinggi di Sensor Node #12',
        'detail' => 'Nilai: 85 µg/m³ (Tidak Sehat) - Jl. Braga, Bandung',
        'waktu' => '5 menit yang lalu',
    ],
    [
        'icon' => '✓',
        'icon_bg' => 'bg-emerald-500/20',
        'icon_color' => 'text-emerald-400',
        'judul' => 'Laporan Warga Diverifikasi',
        'detail' => 'Laporan polusi tinggi di area Dago telah diverifikasi dan task dibuat',
        'waktu' => '15 menit yang lalu',
    ],
    [
        'icon' => '🌱',
        'icon_bg' => 'bg-cyan-500/20',
        'icon_color' => 'text-cyan-400',
        'judul' => 'Task Penanaman Selesai',
        'detail' => '50 pohon trembesi berhasil ditanam di Taman Lansia',
        'waktu' => '2 jam yang lalu',
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - GaiaCity</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "gaia-green": "#10b981",
                        "gaia-dark": "#064e3b",
                        "gaia-blue": "#0ea5e9",
                    },
                    fontFamily: {
                        sans: ["Inter", "sans-serif"],
                    },
                },
            },
        };
    </script>
    <style>
        body {
            background-image: url("https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNbWL9mlJxLPYj8d623frFXkO_juW0SG-oQQ&s");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        #map {
            height: 400px;
            width: 100%;
            border-radius: 0.5rem;
            z-index: 1;
        }
        .leaflet-container {
            font-family: "Inter", sans-serif;
        }
    </style>
</head>
<body class="font-sans">

    <!-- ============ MODERN FLOATING SIDEBAR ============ -->
    <aside
        id="sidebar"
        class="fixed left-4 top-4 bottom-4 w-72 bg-white/10 backdrop-blur-xl rounded-3xl text-white shadow-2xl flex flex-col border border-white/20 z-50 transition-transform duration-300"
    >
        <!-- Logo Section -->
        <div class="p-6 border-b border-white/10">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
                    <img src="../../assets/images/globalIcons/1930174.png" alt="GaiaCity" class="w-8 h-8" />
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">GaiaCity</h1>
                    <p class="text-xs text-gray-400">Smart City Platform</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <?php
            $nav_items = [
                ['href' => '#',                                                       'label' => 'Dashboard',              'active' => true],
                ['href' => 'Sensor/SensorManagement.php',                            'label' => 'Kelola Sensor',          'active' => false],
                ['href' => '../air_quality/index.php',                               'label' => 'Monitor Kualitas Udara', 'active' => false],
                ['href' => '../lihat_semua_tugas/index.php',                         'label' => 'Lihat Semua Tugas',      'active' => false],
                ['href' => '../buat_tugaskan_penghijauan/index.php',                 'label' => 'Buat & Tugaskan',        'active' => false],
                ['href' => '../AI_recomendation/index.php',                          'label' => 'Rekomendasi AI',         'active' => false],
                ['href' => '../task_verification_quality_control/index.php',         'label' => 'Verifikasi',             'active' => false],
                ['href' => '../UserManagement/index.php',                            'label' => 'User Manajemen',         'active' => false],
            ];
            foreach ($nav_items as $item):
                $active_class = $item['active']
                    ? 'bg-white/10 hover:bg-white/15'
                    : 'hover:bg-white/10';
            ?>
            <a
                href="<?= htmlspecialchars($item['href']) ?>"
                class="group flex items-center px-4 py-3 rounded-xl <?= $active_class ?> transition-all duration-300 relative overflow-hidden"
            >
                <span class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-emerald-400 to-cyan-500 rounded-r <?= $item['active'] ? '' : 'scale-y-0 group-hover:scale-y-100 transition-transform duration-300' ?>"></span>
                <span class="font-medium ml-2"><?= htmlspecialchars($item['label']) ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- User Info -->
        <div class="p-4 border-t border-white/10">
            <div class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 transition-all duration-300">
                <div class="w-11 h-11 rounded-full overflow-hidden ring-2 ring-emerald-400/50">
                    <img
                        src="https://blue.kumparan.com/image/upload/fl_progressive,fl_lossy,c_fill,f_auto,q_auto:best,w_640/v1634025439/01j2edsk0m5zcx2azrrf3xhwf3.jpg"
                        alt="Ghufron"
                        class="w-full h-full object-cover"
                    />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate"><?= htmlspecialchars($user_name) ?></div>
                    <div class="text-xs text-gray-400 truncate"><?= htmlspecialchars($user_email) ?></div>
                </div>
                <a
                    href="logout.php"
                    onclick="return confirm('Yakin ingin logout?')"
                    class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- ============ MAIN CONTENT ============ -->
    <div id="mainContent" class="ml-80 transition-all duration-300">

        <!-- ============ MODERN HERO HEADER ============ -->
        <div class="relative h-72 overflow-hidden rounded-3xl m-4 mb-0">
            <div class="absolute inset-0 bg-cover bg-center"
                style="background-image: url('https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=2000');">
            </div>
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/90 to-teal-900/80"></div>

            <!-- Floating Particles -->
            <div class="absolute inset-0">
                <div class="absolute w-2 h-2 bg-white/30 rounded-full left-[10%] animate-[float_15s_ease-in-out_infinite]"></div>
                <div class="absolute w-3 h-3 bg-white/30 rounded-full left-[25%] animate-[float_15s_ease-in-out_2s_infinite]"></div>
                <div class="absolute w-1.5 h-1.5 bg-white/30 rounded-full left-[45%] animate-[float_15s_ease-in-out_4s_infinite]"></div>
                <div class="absolute w-2.5 h-2.5 bg-white/30 rounded-full left-[70%] animate-[float_15s_ease-in-out_1s_infinite]"></div>
                <div class="absolute w-2 h-2 bg-white/30 rounded-full left-[85%] animate-[float_15s_ease-in-out_3s_infinite]"></div>
            </div>

            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-slate-900 to-transparent"></div>

            <div class="relative h-full flex flex-col justify-between p-8 z-10">
                <!-- Top Bar -->
                <div class="flex justify-between items-center">
                    <button onclick="toggleSidebar()" class="lg:hidden text-white hover:bg-white/10 p-2 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div class="flex items-center space-x-4 ml-auto">
                        <!-- Search -->
                        <div class="hidden md:flex items-center bg-white/10 backdrop-blur-md rounded-lg px-4 py-2 border border-white/20">
                            <svg class="w-4 h-4 text-white/70 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input type="text" placeholder="Cari..." class="bg-transparent outline-none text-sm text-white placeholder-white/70 w-48" />
                        </div>

                        <!-- Notifications -->
                        <button class="relative p-2 text-white hover:bg-white/10 rounded-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                </path>
                            </svg>
                            <span class="absolute top-0 right-0 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>

                        <!-- Time -->
                        <div class="hidden md:flex items-center text-sm text-white bg-white/10 backdrop-blur-md px-4 py-2 rounded-lg border border-white/20">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span id="currentTime"></span>
                        </div>
                    </div>
                </div>

                <!-- Main Title -->
                <div>
                    <h1 class="text-5xl font-bold text-white mb-2">Dashboard Overview</h1>
                    <p class="text-white/90 text-lg">Monitor real-time environmental data across Bekasi</p>
                </div>
            </div>
        </div>

        <!-- ============ CONTENT SECTIONS ============ -->
        <div class="p-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl p-6 border-l-4 border-emerald-400 hover:-translate-y-1 transition-transform duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-300 text-sm mb-1">Total Sensor Aktif</p>
                            <h3 class="text-3xl font-bold text-white"><?= $stats['sensor_aktif'] ?></h3>
                            <p class="text-emerald-400 text-xs mt-2"><span class="inline-block mr-1">↑</span>+2 dari kemarin</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl p-6 border-l-4 border-red-400 hover:-translate-y-1 transition-transform duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-300 text-sm mb-1">Alert Aktif</p>
                            <h3 class="text-3xl font-bold text-white"><?= $stats['alert_aktif'] ?></h3>
                            <p class="text-red-400 text-xs mt-2"><span class="inline-block mr-1">⚠</span><?= $stats['alert_critical'] ?> Critical</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl p-6 border-l-4 border-cyan-400 hover:-translate-y-1 transition-transform duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-300 text-sm mb-1">Laporan Pending</p>
                            <h3 class="text-3xl font-bold text-white"><?= $stats['laporan_pending'] ?></h3>
                            <p class="text-yellow-400 text-xs mt-2"><span class="inline-block mr-1">⏱</span>Menunggu review</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl p-6 border-l-4 border-emerald-500 hover:-translate-y-1 transition-transform duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-300 text-sm mb-1">Pohon Ditanam (Bulan Ini)</p>
                            <h3 class="text-3xl font-bold text-white"><?= number_format($stats['pohon_ditanam'], 0, ',', '.') ?></h3>
                            <p class="text-emerald-400 text-xs mt-2"><span class="inline-block mr-1">↑</span>Target <?= $stats['target_persen'] ?>%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl p-6 border border-white/20">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-white">Tren Polusi PM2.5 (7 Hari)</h3>
                        <a href="../air_quality/index.php" class="text-emerald-400 text-sm hover:underline">Lihat Detail</a>
                    </div>
                    <div class="h-full flex items-center">
                        <canvas id="pollutionChart"></canvas>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl p-6 border border-white/20">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-white">Status Sensor Real-Time</h3>
                        <button onclick="location.reload()" class="text-emerald-400 text-sm hover:underline">Refresh</button>
                    </div>
                    <canvas id="sensorStatusChart"></canvas>
                </div>
            </div>

            <!-- Map Component -->
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl p-6 mb-6 border border-white/20">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white">Peta Sensor & Heatmap Polusi</h3>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 bg-emerald-500 text-white text-sm rounded-lg hover:bg-emerald-600 transition">
                            <span class="mr-1"></span>Sensor
                        </button>
                        <button onclick="refreshMap()" class="px-3 py-1 bg-white/10 text-white text-sm rounded-lg hover:bg-white/20 transition border border-white/20">
                            <span class="mr-1"></span>Refresh
                        </button>
                    </div>
                </div>
                <div id="map" class="rounded-xl overflow-hidden"></div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4">Aktivitas Terbaru</h3>
                <div class="space-y-4">
                    <?php foreach ($aktivitas as $index => $item):
                        $border_class = $index < count($aktivitas) - 1 ? 'pb-4 border-b border-white/10' : '';
                    ?>
                    <div class="flex items-start space-x-4 <?= $border_class ?>">
                        <div class="w-10 h-10 <?= htmlspecialchars($item['icon_bg']) ?> rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="<?= htmlspecialchars($item['icon_color']) ?>"><?= $item['icon'] ?></span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-white"><?= htmlspecialchars($item['judul']) ?></p>
                            <p class="text-xs text-gray-400"><?= htmlspecialchars($item['detail']) ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($item['waktu']) ?></p>
                        </div>
                        <button class="text-cyan-400 hover:underline text-sm">Lihat</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const AppState = {
            currentSection: "overview",
            sidebarOpen: window.innerWidth >= 1024,
            map: null,
        };

        function initMap() {
            const bandungCoords = [-6.9175, 107.6191];
            AppState.map = L.map("map").setView(bandungCoords, 13);

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "&copy; OpenStreetMap contributors",
            }).addTo(AppState.map);

            const sensorLocations = [
                { lat: -6.9147, lng: 107.6098, name: "Sensor #1 - Jl. Braga",   status: "good"      },
                { lat: -6.9023, lng: 107.6189, name: "Sensor #2 - Dago",         status: "moderate"  },
                { lat: -6.9344, lng: 107.6407, name: "Sensor #3 - Buah Batu",    status: "unhealthy" },
                { lat: -6.8938, lng: 107.6107, name: "Sensor #4 - Cihampelas",   status: "good"      },
            ];

            sensorLocations.forEach((sensor) => {
                const color = sensor.status === "good" ? "green"
                            : sensor.status === "moderate" ? "orange" : "red";
                const icon = L.divIcon({
                    className: "custom-marker",
                    html: `<div style="background-color:${color};width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 4px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [24, 24],
                });
                L.marker([sensor.lat, sensor.lng], { icon })
                    .bindPopup(`<b>${sensor.name}</b><br>Status: ${sensor.status}`)
                    .addTo(AppState.map);
            });

            setTimeout(() => AppState.map.invalidateSize(), 100);
        }

        function refreshMap() {
            if (AppState.map) AppState.map.invalidateSize();
        }

        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
            const day = now.toLocaleDateString("id-ID", { weekday: "short" });
            const dateStr = now.toLocaleDateString("id-ID", { day: "numeric", month: "short" });
            document.getElementById("currentTime").textContent = `${timeStr} | ${day}, ${dateStr}`;
        }

        function toggleSidebar() {
            AppState.sidebarOpen = !AppState.sidebarOpen;
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("mainContent");

            if (AppState.sidebarOpen) {
                sidebar.classList.remove("-translate-x-full");
                mainContent.classList.replace("ml-0", "ml-80");
            } else {
                sidebar.classList.add("-translate-x-full");
                mainContent.classList.replace("ml-80", "ml-0");
            }

            setTimeout(() => { if (AppState.map) AppState.map.invalidateSize(); }, 300);
        }

        function initCharts() {
            const pollutionCtx = document.getElementById("pollutionChart").getContext("2d");
            new Chart(pollutionCtx, {
                type: "line",
                data: {
                    labels: ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"],
                    datasets: [{
                        label: "PM2.5 (µg/m³)",
                        data: [45, 52, 48, 65, 71, 68, 55],
                        borderColor: "#10b981",
                        backgroundColor: "rgba(16, 185, 129, 0.1)",
                        tension: 0.4,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } },
                },
            });

            const sensorCtx = document.getElementById("sensorStatusChart").getContext("2d");
            new Chart(sensorCtx, {
                type: "doughnut",
                data: {
                    labels: ["Active", "Maintenance", "Inactive"],
                    datasets: [{
                        data: [<?= $stats['sensor_aktif'] ?>, 3, 2],
                        backgroundColor: ["#10b981", "#f59e0b", "#ef4444"],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { position: "bottom" } },
                },
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            updateTime();
            setInterval(updateTime, 1000);
            initCharts();
            initMap();

            if (window.innerWidth < 1024) toggleSidebar();
            window.addEventListener("resize", () => {
                if (AppState.map) AppState.map.invalidateSize();
            });
        });
    </script>
</body>
</html>