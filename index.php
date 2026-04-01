<?php
// ============================================================
//  KONFIGURASI & KONEKSI DATABASE
// ============================================================
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');       
define('DB_PASS', '');        
define('DB_NAME', 'database');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("<p style='color:red;padding:20px'>Koneksi gagal: " . $conn->connect_error . "</p>");
}
$conn->set_charset("utf8");

session_start();

// ============================================================
//  DATA PESAWAT
// ============================================================
$pesawat = [
    "GRD" => ["nama" => "Garuda",  "kelas" => ["Eksekutif" => 1500000, "Bisnis" => 900000,  "Ekonomi" => 500000]],
    "MPT" => ["nama" => "Merpati", "kelas" => ["Eksekutif" => 1200000, "Bisnis" => 800000,  "Ekonomi" => 400000]],
    "BTV" => ["nama" => "Batavia", "kelas" => ["Eksekutif" => 1000000, "Bisnis" => 700000,  "Ekonomi" => 300000]],
];

// ============================================================
//  LOGOUT
// ============================================================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ============================================================
//  PROSES LOGIN
// ============================================================
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $loginError = 'Email dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, nama, email, password FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && $password === $user['password']) { 
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $loginError = 'Email atau password salah.';
        }
    }
}

// ============================================================
//  PROSES PEMESANAN
// ============================================================
$result     = null;
$pesanError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pesan' && isset($_SESSION['user_id'])) {
    $nama_pemesan = trim($_POST['nama_pemesan'] ?? '');
    $kode_pesawat = $_POST['kode_pesawat'] ?? '';
    $kelas        = $_POST['kelas'] ?? '';
    $jumlah       = (int)($_POST['jumlah'] ?? 0);

    if (!$nama_pemesan || !$kode_pesawat || !$kelas || $jumlah < 1) {
        $pesanError = 'Harap isi semua field dengan benar.';
    } elseif (!isset($pesawat[$kode_pesawat]['kelas'][$kelas])) {
        $pesanError = 'Kode pesawat atau kelas tidak valid.';
    } else {
        $harga  = $pesawat[$kode_pesawat]['kelas'][$kelas];
        $total  = $harga * $jumlah;
        $result = compact('nama_pemesan', 'kode_pesawat', 'kelas', 'jumlah', 'harga', 'total');
        $result['nama_pesawat'] = $pesawat[$kode_pesawat]['nama'];
    }
}

$loggedIn = isset($_SESSION['user_id']);
function rp($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TiketNusantara</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f0f4f8; min-height: 100vh; display: flex; flex-direction: column; }

/* TOPBAR */
.topbar { background: #0d1b2a; padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; }
.brand  { display: flex; align-items: center; gap: 10px; }
.brand-icon { width: 34px; height: 34px; border-radius: 8px; background: #e8b84b; display: grid; place-items: center; font-size: 16px; }
.brand-name { font-size: 1rem; color: #f4f1eb; font-weight: bold; }
.topbar-right { display: flex; align-items: center; gap: 14px; }
.user-label { font-size: 0.85rem; color: #6e8fa8; }
.btn-logout { background: transparent; border: 1px solid #6e8fa8; color: #6e8fa8; padding: 5px 14px; border-radius: 6px; cursor: pointer; font-size: 0.82rem; text-decoration: none; }
.btn-logout:hover { background: rgba(255,255,255,0.07); }

/* LOGIN */
.login-wrap   { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
.login-panel  { display: flex; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.12); max-width: 820px; width: 100%; }
.lp-left      { width: 42%; background: #0d1b2a; padding: 48px 36px; display: flex; flex-direction: column; justify-content: center; }
.lp-headline  { font-size: 1.9rem; font-weight: bold; color: #f4f1eb; line-height: 1.2; margin-bottom: 14px; }
.lp-headline span { color: #e8b84b; }
.lp-sub       { font-size: 0.85rem; color: #6e8fa8; line-height: 1.6; }
.lp-features  { margin-top: 32px; display: flex; flex-direction: column; gap: 12px; }
.lp-feat      { display: flex; align-items: center; gap: 10px; font-size: 0.82rem; color: #6e8fa8; }
.lp-dot       { width: 6px; height: 6px; border-radius: 50%; background: #0f9b8e; flex-shrink: 0; }
.lp-right     { flex: 1; background: #fff; display: flex; align-items: center; justify-content: center; padding: 48px 40px; }
.lf           { width: 100%; max-width: 300px; }
.lf h2        { font-size: 1.5rem; margin-bottom: 4px; color: #1a1a1a; }
.lf .sub      { font-size: 0.85rem; color: #888; margin-bottom: 26px; }

/* FORM */
.fg           { margin-bottom: 16px; }
.fg label     { display: block; font-size: 0.72rem; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #888; margin-bottom: 6px; }
.fg input,
.fg select    { width: 100%; padding: 11px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.92rem; color: #1a1a1a; outline: none; font-family: Arial, sans-serif; }
.fg input:focus,
.fg select:focus { border-color: #0f9b8e; }
.btn          { width: 100%; padding: 12px; background: #0f9b8e; color: #fff; border: none; border-radius: 8px; font-size: 0.95rem; cursor: pointer; font-family: Arial, sans-serif; }
.btn:hover    { background: #0d8a7e; }

/* ALERT */
.alert-err { background: #fff0ee; border: 1px solid #f5b8b0; border-radius: 8px; padding: 10px 14px; font-size: 0.85rem; color: #c0392b; margin-bottom: 16px; }
.alert-ok  { background: #eafaf5; border: 1px solid #9fe1c8; border-radius: 8px; padding: 18px 20px; margin-top: 22px; }
.alert-ok h3 { font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase; color: #0a7a6e; margin-bottom: 14px; }

/* TIKET */
.tiket-wrap { flex: 1; display: flex; align-items: flex-start; justify-content: center; gap: 24px; padding: 40px 20px; flex-wrap: wrap; }
.card       { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 28px; width: 100%; max-width: 500px; }
.card h2    { font-size: 1.1rem; margin-bottom: 20px; color: #1a1a1a; border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; }
.grid2      { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* RESULT */
.result-row { display: flex; justify-content: space-between; font-size: 0.88rem; padding: 8px 0; border-bottom: 1px solid #eee; }
.result-row:last-child { border-bottom: none; font-weight: bold; font-size: 1rem; color: #0f9b8e; padding-top: 10px; }
.result-row span:first-child { color: #888; }

/* TABEL HARGA */
.price-card    { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 28px; width: 100%; max-width: 340px; }
.price-card h2 { font-size: 1rem; margin-bottom: 16px; color: #1a1a1a; border-bottom: 1px solid #f0f0f0; padding-bottom: 12px; }
table          { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
th             { background: #f5f5f5; padding: 8px 10px; text-align: left; border: 1px solid #ddd; }
td             { padding: 7px 10px; border: 1px solid #eee; }

@media (max-width: 640px) {
    .login-panel { flex-direction: column; }
    .lp-left { width: 100%; padding: 32px 24px 20px; }
    .lp-features { display: none; }
    .lp-right { padding: 32px 24px; }
    .grid2 { grid-template-columns: 1fr; }
    .tiket-wrap { flex-direction: column; align-items: center; }
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="brand">
        <div class="brand-icon">&#9992;</div>
        <span class="brand-name">TiketNusantara</span>
    </div>
    <?php if ($loggedIn): ?>
    <div class="topbar-right">
        <span class="user-label">&#128100; <?= htmlspecialchars($_SESSION['nama']) ?> &mdash; <?= htmlspecialchars($_SESSION['email']) ?></span>
        <a href="?logout=1" class="btn-logout">Keluar</a>
    </div>
    <?php endif; ?>
</div>

<?php if (!$loggedIn): ?>
<!-- ════════════════ HALAMAN LOGIN ════════════════ -->
<div class="login-wrap">
    <div class="login-panel">

        <div class="lp-left">
            <h1 class="lp-headline">Pesan tiket<br><span>mudah</span> &amp; cepat.</h1>
            <p class="lp-sub">Platform pemesanan tiket pesawat domestik untuk perjalanan ke seluruh Indonesia.</p>
            <div class="lp-features">
                <div class="lp-feat"><span class="lp-dot"></span> Garuda, Merpati, Batavia</div>
                <div class="lp-feat"><span class="lp-dot"></span> Eksekutif, Bisnis &amp; Ekonomi</div>
                <div class="lp-feat"><span class="lp-dot"></span> Harga transparan, tanpa biaya tersembunyi</div>
            </div>
        </div>

        <div class="lp-right">
            <div class="lf">
                <h2>Selamat datang</h2>
                <p class="sub">Masuk ke akun Anda untuk melanjutkan.</p>

                <?php if ($loginError): ?>
                    <div class="alert-err">&#9888; <?= htmlspecialchars($loginError) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="fg">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="nama@email.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="fg">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
                    </div>
                    <button type="submit" class="btn">Masuk &rarr;</button>
                </form>
            </div>
        </div>

    </div>
</div>

<?php else: ?>
<!-- ════════════════ HALAMAN PEMESANAN ════════════════ -->
<div class="tiket-wrap">

    <div class="card">
        <h2>&#127923; Pemesanan Tiket Pesawat</h2>

        <?php if ($pesanError): ?>
            <div class="alert-err">&#9888; <?= htmlspecialchars($pesanError) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="pesan">

            <div class="fg">
                <label>Nama Pemesan</label>
                <input type="text" name="nama_pemesan" placeholder="Nama lengkap"
                       value="<?= htmlspecialchars($result['nama_pemesan'] ?? '') ?>" required>
            </div>

            <div class="grid2">
                <div class="fg">
                    <label>Kode Pesawat</label>
                    <select name="kode_pesawat" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($pesawat as $kode => $data): ?>
                        <option value="<?= $kode ?>" <?= ($result['kode_pesawat'] ?? '') === $kode ? 'selected' : '' ?>>
                            <?= $kode ?> &ndash; <?= $data['nama'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg">
                    <label>Kelas</label>
                    <select name="kelas" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach (['Eksekutif','Bisnis','Ekonomi'] as $k): ?>
                        <option value="<?= $k ?>" <?= ($result['kelas'] ?? '') === $k ? 'selected' : '' ?>>
                            <?= $k ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="fg">
                <label>Jumlah Tiket</label>
                <input type="number" name="jumlah" min="1" max="10"
                       value="<?= htmlspecialchars((string)($result['jumlah'] ?? 1)) ?>" required>
            </div>

            <button type="submit" class="btn">Hitung Total Bayar</button>
        </form>

        <?php if ($result): ?>
        <div class="alert-ok">
            <h3>Detail Pemesanan</h3>
            <div class="result-row"><span>Nama Pemesan</span><span><?= htmlspecialchars($result['nama_pemesan']) ?></span></div>
            <div class="result-row"><span>Pesawat</span><span><?= $result['kode_pesawat'] ?> &ndash; <?= $result['nama_pesawat'] ?></span></div>
            <div class="result-row"><span>Kelas</span><span><?= $result['kelas'] ?></span></div>
            <div class="result-row"><span>Harga / Tiket</span><span><?= rp($result['harga']) ?></span></div>
            <div class="result-row"><span>Jumlah Tiket</span><span><?= $result['jumlah'] ?></span></div>
            <div class="result-row"><span>Total Bayar</span><span><?= rp($result['total']) ?></span></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tabel Daftar Harga -->
    <div class="price-card">
        <h2>&#128203; Daftar Harga</h2>
        <table>
            <tr><th>Kode</th><th>Pesawat</th><th>Kelas</th><th>Harga</th></tr>
            <?php foreach ($pesawat as $kode => $data):
                $first = true; $rs = count($data['kelas']);
                foreach ($data['kelas'] as $kls => $hrg): ?>
            <tr>
                <?php if ($first): ?><td rowspan="<?= $rs ?>"><?= $kode ?></td><td rowspan="<?= $rs ?>"><?= $data['nama'] ?></td><?php $first = false; endif; ?>
                <td><?= $kls ?></td>
                <td><?= rp($hrg) ?></td>
            </tr>
            <?php endforeach; endforeach; ?>
        </table>
    </div>

</div>
<?php endif; ?>

</body>
</html>
