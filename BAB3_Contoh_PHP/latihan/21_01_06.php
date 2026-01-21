<!DOCTYPE html>
<html>
<head>
    <title>Pemesanan Tiket Pesawat - Latihan Bab 3</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 500px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .result { margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #4CAF50; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pemesanan Tiket Pesawat</h2>
        <?php
        $pesawat = [
            "GRD" => [
                "nama" => "Garuda",
                "kelas" => [
                    "Eksekutif" => 1500000,
                    "Bisnis" => 900000,
                    "Ekonomi" => 500000
                ]
            ],
            "MPT" => [
                "nama" => "Merpati",
                "kelas" => [
                    "Eksekutif" => 1200000,
                    "Bisnis" => 800000,
                    "Ekonomi" => 400000
                ]
            ],
            "BTV" => [
                "nama" => "Batavia",
                "kelas" => [
                    "Eksekutif" => 1000000,
                    "Bisnis" => 700000,
                    "Ekonomi" => 300000
                ]
            ]
        ];
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $kode_pesawat = $_POST['kode_pesawat'];
            $kelas = $_POST['kelas'];
            $jumlah = $_POST['jumlah'];
            if (!empty($kode_pesawat) && !empty($kelas) && !empty($jumlah) && $jumlah > 0) {
                if (isset($pesawat[$kode_pesawat])) {
                    $nama_pesawat = $pesawat[$kode_pesawat]['nama'];
                    $harga_tiket = $pesawat[$kode_pesawat]['kelas'][$kelas];
                    $total_bayar = $harga_tiket * $jumlah;                   
                    $harga_format = number_format($harga_tiket, 0, ',', '.');
                    $total_format = number_format($total_bayar, 0, ',', '.');
                    echo "<div class='result'>";
                    echo "<h3>Detail Pemesanan:</h3>";
                    echo "<p><strong>Kode Pesawat:</strong> $kode_pesawat</p>";
                    echo "<p><strong>Nama Pesawat:</strong> $nama_pesawat</p>";
                    echo "<p><strong>Kelas:</strong> $kelas</p>";
                    echo "<p><strong>Harga Tiket:</strong> Rp $harga_format</p>";
                    echo "<p><strong>Jumlah Tiket:</strong> $jumlah</p>";
                    echo "<p><strong>Total Bayar:</strong> Rp $total_format</p>";
                    echo "</div>";
                } else {
                    echo "<div class='result' style='border-left-color: #f44336;'>Kode pesawat tidak valid!</div>";
                }
            } else {
                echo "<div class='result' style='border-left-color: #f44336;'>Harap isi semua field dengan benar!</div>";
            }
        }
        ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="kode_pesawat">Kode Pesawat:</label>
                <select name="kode_pesawat" id="kode_pesawat" required>
                    <option value="">-- Pilih Kode Pesawat --</option>
                    <option value="GRD">GRD - Garuda</option>
                    <option value="MPT">MPT - Merpati</option>
                    <option value="BTV">BTV - Batavia</option>
                </select>
            </div>
            <div class="form-group">
                <label for="kelas">Kelas:</label>
                <select name="kelas" id="kelas" required>
                    <option value="">-- Pilih Kelas --</option>
                    <option value="Eksekutif">Eksekutif</option>
                    <option value="Bisnis">Bisnis</option>
                    <option value="Ekonomi">Ekonomi</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="jumlah">Jumlah Tiket:</label>
                <input type="number" name="jumlah" id="jumlah" min="1" max="10" value="1" required>
            </div>
            
            <button type="submit">Hitung Total Bayar</button>
        </form>
        
        <div style="margin-top: 30px; font-size: 14px; color: #666;">
            <h4>Daftar Harga:</h4>
            <table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
                <tr style="background:#f2f2f2;">
                    <th>Kode</th>
                    <th>Nama Pesawat</th>
                    <th>Kelas</th>
                    <th>Harga</th>
                </tr>
                <?php
                foreach ($pesawat as $kode => $data) {
                    $rowspan = count($data['kelas']);
                    $first = true;
                    
                    foreach ($data['kelas'] as $kelas_nama => $harga) {
                        echo "<tr>";
                        if ($first) {
                            echo "<td rowspan='$rowspan'>$kode</td>";
                            echo "<td rowspan='$rowspan'>{$data['nama']}</td>";
                            $first = false;
                        }
                        echo "<td>$kelas_nama</td>";
                        echo "<td>Rp " . number_format($harga, 0, ',', '.') . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>