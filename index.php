<?php
// --- Bagian awal index.php ---

// Pastikan file JSON ada, kalau tidak bikin baru
if (!file_exists("jadwal.json")) {
    file_put_contents("jadwal.json", json_encode([]));
}
if (!file_exists("tugas.json")) {
    file_put_contents("tugas.json", json_encode([]));
}

// Baca data dari file JSON
$jadwal = json_decode(file_get_contents("jadwal.json"), true);
if (!is_array($jadwal)) {
    $jadwal = [];
}

$tugas = json_decode(file_get_contents("tugas.json"), true);
if (!is_array($tugas)) {
    $tugas = [];
}

// Tambah jadwal kuliah
if (isset($_POST['add_jadwal'])) {
    $newJadwal = [
        "mataKuliah" => $_POST['mata-kuliah'],
        "hari" => $_POST['hari'],
        "jam" => $_POST['jam'],
        "ruangan" => $_POST['ruangan'],
        "dosen" => $_POST['dosen'],
        "sks" => $_POST['sks']
    ];
    $jadwal[] = $newJadwal;
    file_put_contents("jadwal.json", json_encode($jadwal, JSON_PRETTY_PRINT));
    header("Location: index.php?tab=jadwal");
    exit;
}

// Hapus jadwal
if (isset($_GET['hapus_jadwal'])) {
    $index = (int)$_GET['hapus_jadwal'];
    unset($jadwal[$index]);
    $jadwal = array_values($jadwal);
    file_put_contents("jadwal.json", json_encode($jadwal, JSON_PRETTY_PRINT));
    header("Location: index.php?tab=jadwal");
    exit;
}

// Tambah tugas
if (isset($_POST['add_tugas'])) {
    $newTugas = [
        "mataKuliah" => $_POST['tugas-mata-kuliah'],
        "judul" => $_POST['judul-tugas'],
        "deskripsi" => $_POST['deskripsi'],
        "deadline" => $_POST['deadline'],
        "selesai" => false
    ];
    $tugas[] = $newTugas;
    file_put_contents("tugas.json", json_encode($tugas, JSON_PRETTY_PRINT));
    header("Location: index.php?tab=tugas");
    exit;
}

// Hapus tugas
if (isset($_GET['hapus_tugas'])) {
    $index = (int)$_GET['hapus_tugas'];
    unset($tugas[$index]);
    $tugas = array_values($tugas);
    file_put_contents("tugas.json", json_encode($tugas, JSON_PRETTY_PRINT));
    header("Location: index.php?tab=tugas");
    exit;
}

// Tandai selesai
if (isset($_GET['selesai_tugas'])) {
    $index = (int)$_GET['selesai_tugas'];
    $tugas[$index]['selesai'] = true;
    file_put_contents("tugas.json", json_encode($tugas, JSON_PRETTY_PRINT));
    header("Location: index.php?tab=tugas");
    exit;
}

// Batalkan selesai
if (isset($_GET['batal_tugas'])) {
    $index = (int)$_GET['batal_tugas'];
    $tugas[$index]['selesai'] = false;
    file_put_contents("tugas.json", json_encode($tugas, JSON_PRETTY_PRINT));
    header("Location: index.php?tab=tugas");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Agenda Digital Mahasiswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">

    <div class="header">
        <div class="header-content">
            <div class="header-title">Agenda Mahasiswa</div>
            <div class="nav-buttons">
                <a href="index.php?tab=dashboard" class="tab-button <?php echo (!isset($_GET['tab']) || $_GET['tab']=="dashboard") ? "active" : "" ?>">Dashboard</a>
                <a href="index.php?tab=jadwal" class="tab-button <?php echo (isset($_GET['tab']) && $_GET['tab']=="jadwal") ? "active" : "" ?>">Jadwal Kuliah</a>
                <a href="index.php?tab=tugas" class="tab-button <?php echo (isset($_GET['tab']) && $_GET['tab']=="tugas") ? "active" : "" ?>">Daftar Tugas</a>
            </div>
        </div>
    </div>

    <!-- Hero -->
    <div class="hero-box">
        <h1>Hola, Sefina Ayudia Syauqi</h1>
        <p>Catet jadwal & tugasmu sekarang biar kuliah makin aman banget 😁👍</p>
    </div>

    <!-- Dashboard -->
    <?php if (!isset($_GET['tab']) || $_GET['tab']=="dashboard"): ?>
        <div class="tab-content active">
            <h2>Kegiatanmu Hari Ini🤔</h2>
            <p><?php echo date("d F Y"); ?></p>

            <div class="dashboard-grid">
                <div class="box">
                    <div class="box-header"><div class="box-title">Jadwal Hari Ini</div></div>
                    <?php
                    $hariIni = strftime("%A", strtotime("today"));
                    $jadwalHariIni = array_filter($jadwal, fn($j)=>$j['hari']==$hariIni);
                    if (empty($jadwalHariIni)) {
                        echo "<p>Tidak ada jadwal kuliah hari ini.</p>";
                    } else {
                        foreach($jadwalHariIni as $j) {
                            echo "<div class='task-item'><div class='task-info'><h3>{$j['mataKuliah']}</h3><p>{$j['jam']} | {$j['ruangan']} | {$j['dosen']}</p></div></div>";
                        }
                    }
                    ?>
                </div>

                <div class="box">
                    <div class="box-header"><div class="box-title">Tugas Mendekati Deadline</div></div>
                    <?php
                    $now = time();
                    $nearDeadline = array_filter($tugas, function($t) use($now){
                        if ($t['selesai']) return false;
                        $dl = strtotime($t['deadline']);
                        $diff = ceil(($dl-$now)/86400);
                        return $diff>=0 && $diff<=3;
                    });
                    if (empty($nearDeadline)) {
                        echo "<p>Tidak ada tugas mendekati deadline.</p>";
                    } else {
                        foreach($nearDeadline as $t) {
                            $dl = strtotime($t['deadline']);
                            $diff = ceil(($dl-$now)/86400);
                            echo "<div class='task-item'><div class='task-info'><h3>{$t['mataKuliah']} - {$t['judul']}</h3><p>Deadline: ".date("d M Y H:i",$dl)."</p><span class='sign ".($diff==0?"sign-danger":"sign-warning")."'>".($diff==0?"Hari Ini":"$diff Hari Lagi")."</span></div></div>";
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="box">
                <div class="box-header"><div class="box-title">Ringkasan Akademik</div></div>
                <p>Total Mata Kuliah: <?php echo count($jadwal); ?></p>
                <p>Total SKS: <?php echo array_sum(array_column($jadwal,"sks")); ?></p>
                <p>Total Tugas: <?php echo count($tugas); ?></p>
                <p>Tugas Selesai: <?php echo count(array_filter($tugas, fn($t)=>$t['selesai'])); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['tab']) && $_GET['tab']=="jadwal"): ?>
        <div class="tab-content active">
            <h2>Kelola Jadwal Kuliah</h2>
            <div class="box">
                <div class="box-header">
                    <div class="box-title">Tambah Jadwal Kuliah</div>
                    <i class="fas fa-list"></i>
                </div>
            <form method="post">
                 <tr>
                <th>Mata Kuliah</th>
                <input type="text" name="mata-kuliah" placeholder="Mata Kuliah" required>
                <th>Hari</th>
                <select name="hari" required>
                    <option value="">Pilih Hari</option>
                    <option>Senin</option><option>Selasa</option><option>Rabu</option>
                    <option>Kamis</option><option>Jumat</option><option>Sabtu</option>
                </select>
                <th>Jam</th>
                <input type="time" name="jam" required>
                <th>Ruangan</th>
                <input type="text" name="ruangan" placeholder="Ruangan">
                <th>Dosen</th>
                <input type="text" name="dosen" placeholder="Dosen">
                <th>SKS</th>
                <input type="number" name="sks" min="1" max="8" required>
                <br>
                 </div>
                <button type="submit" name="add_jadwal">Tambah Jadwal</button>
            </form>
             </tr>

    </div>  

            <div class="box">
                <div class="box-header">
                    <div class="box-title">Daftar Jadwal Kuliah</div>
                <i class="fas fa-list"></i>
                </div>
                <table>
                <tr><th>Mata Kuliah</th><th>Hari</th><th>Jam</th><th>Ruangan</th><th>Dosen</th><th>SKS</th><th>Aksi</th></tr>
                <?php foreach($jadwal as $i=>$j): ?>
                    <tr>
                        <td><?= $j['mataKuliah'] ?></td>
                        <td><?= $j['hari'] ?></td>
                        <td><?= $j['jam'] ?></td>
                        <td><?= $j['ruangan'] ?></td>
                        <td><?= $j['dosen'] ?></td>
                        <td><?= $j['sks'] ?></td>
                       <td><form method="get" style="display:inline;" onsubmit="return confirm('Hapus jadwal ini?')">
    <input type="hidden" name="hapus_jadwal" value="<?= $i ?>">
    <button type="submit" class="btn-danger">Hapus</button>
</form></td>


                    </tr>
                <?php endforeach; ?>
            </table>
                </div> 
                
        </div>
    <?php endif; ?>

    <!-- Tugas -->
    <?php if (isset($_GET['tab']) && $_GET['tab']=="tugas"): ?>
        <div class="tab-content active">
            <h2>Kelola Tugas</h2>
            <div class="box">
                <div class="box-header
"><div class="box-title">Tambah Tugas</div>

</div>
                <form action="" method="post">
            <tr>
                <form method="post">
                <th>Mata Kuliah</th>
                <input type="text" name="tugas-mata-kuliah" placeholder="Mata Kuliah" required>
                <th>Judul Tugas</th>
                <input type="text" name="judul-tugas" placeholder="Judul Tugas" required>
                <th>Deskripsi</th>
                <input type="text" name="deskripsi" placeholder="Deskripsi">
                <th>Deadline</th>
                <input type="datetime-local" name="deadline" required>
                <br>
                 </div>
                <button type="submit" name="add_tugas">Tambah Tugas</button>
            </form>
            </tr>
</div>
        <div class="box">
             <?php foreach($tugas as $i=>$t): ?>
                <div class="task-item <?= $t['selesai'] ? 'completed' : '' ?>">
                    <div class="task-info">
                        <h3><?= $t['mataKuliah'] ?> - <?= $t['judul'] ?></h3>
                        <p><?= $t['deskripsi'] ?></p>
                        <p>Deadline: <?= date("d M Y H:i", strtotime($t['deadline'])) ?></p>
                    </div>
                    <div class="task-actions">
<td>
    <?php if (!$t['selesai']): ?>
        <form method="get" style="display:inline;">
            <input type="hidden" name="selesai_tugas" value="<?= $i ?>">
            <button type="submit" class="btn-done">Selesai</button>
        </form>
    <?php else: ?>
        <form method="get" style="display:inline;">
            <input type="hidden" name="batal_tugas" value="<?= $i ?>">
            <button type="submit" class="btn-warning">Batalkan</button>
        </form>
    <?php endif; ?>

    <form method="get" style="display:inline;" onsubmit="return confirm('Hapus tugas ini?')">
        <input type="hidden" name="hapus_tugas" value="<?= $i ?>">
        <button type="submit" class="btn-danger">Hapus</button>
    </form>
</td>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


</body>
</html>
