<?php
session_start();
include '../assets/func.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../login/index.php");
    exit;
}

$air     = new klas_air;
$koneksi = $air->koneksi();

// Ambil data user dari session
$username_session = $_SESSION['user'];

$stmt = mysqli_prepare($koneksi, "SELECT * FROM login WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username_session);
mysqli_stmt_execute($stmt);
$result   = mysqli_stmt_get_result($stmt);
$dt_user_row = mysqli_fetch_assoc($result);

// $dt_user[0] = nama, [1] = level, [2] = username (sesuaikan dengan kolom tabel Anda)
$dt_user = [
    $dt_user_row['nama']     ?? $dt_user_row['username'],
    $dt_user_row['level']    ?? '-',
    $dt_user_row['username'] ?? '-',
];

$level = $_SESSION['level'] ?? ($dt_user_row['level'] ?? '');

// Routing halaman
$e  = explode("=", $_SERVER['REQUEST_URI']);
$page = $e[1] ?? '';

$h1 = "Dashboard";
$li = "Dashboard";

if (!empty($page)) {
    $routes = [
        "user"                                          => ["Manajemen User",                                    "Menu untuk CRUD User"],
        "pemakaian_warga"                               => ["Lihat Pemakaian Warga",                             "Lihat Data Pemakaian Air Warga"],
        "pembayaran_warga"                              => ["Lihat Pembayaran Warga",                            "Lihat Data Pembayaran Air Warga"],
        "ubah_datameter_warga"                          => ["Ubah Data Meter Warga",                             "Ubah Data Meter Air Warga"],
        "menghapus_datameter_air_warga"                 => ["Menghapus Data Meter Warga",                        "Menghapus Data Meter Air Warga"],
        "melihat_tagihan_seluruh_warga"                 => ["Melihat Tagihan Seluruh Warga",                     "Melihat Tagihan Seluruh Warga"],
        "melihat_infografis_tagihan_warga"              => ["Melihat Infografis Tagihan Warga",                  "Melihat Infografis Tagihan Warga"],
        "manajemen_tarif_air"                           => ["Manajemen Tarif Air",                               "Manajemen Tarif Air"],
        "memasukan_datameter_pemakaian_air_warga"       => ["Memasukan Datameter Pemakaian Air Warga",           "Memasukan Datameter Pemakaian Air Warga"],
        "mengubah_datameter_air_warga_dalam_satu_bulan" => ["Mengubah Datameter Air Warga Dalam Satu Bulan",     "Mengubah Datameter Air Warga Dalam Satu Bulan"],
        "melihat_jumlah_total_pelanggan"                => ["Melihat Jumlah Total Pelanggan",                    "Melihat Jumlah Total Pelanggan"],
        "melihat_jumlah_pemakaian_air_seluruh_warga"    => ["Melihat Jumlah Pemakaian Air Seluruh Warga",        "Melihat Jumlah Pemakaian Air Seluruh Warga"],
        "melihat_infografis_pemakaian_air_warga"        => ["Melihat Infografis Pemakaian Air Warga",            "Melihat Infografis Pemakaian Air Warga"],
        "memantau_air_tiap_bulan"                       => ["Memantau Air Tiap Bulan",                           "Memantau Air Tiap Bulan"],
        "melihat_tagihan_tiap_bulan"                    => ["Melihat Tagihan Tiap Bulan",                        "Melihat Tagihan Tiap Bulan"],
        "melihat_infografis_pemakaian_dan_tagihan_perbulan" => ["Melihat Infografis Pemakaian Dan Tagihan Perbulan", "Melihat Infografis Pemakaian Dan Tagihan Perbulan"],
    ];

    if (isset($routes[$page])) {
        $h1 = $routes[$page][0];
        $li = $routes[$page][1];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Dashboard - AirSystem</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="../js/air.js"></script>
    </head>
    <body class="sb-nav-fixed">

<!-- TOP NAVBAR -->
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="index.php">AirSystem</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <div class="input-group">
            <input class="form-control" type="text" placeholder="Search for..."
                   aria-label="Search for..." aria-describedby="btnNavbarSearch" />
            <button class="btn btn-primary" id="btnNavbarSearch" type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#"
               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#!">Settings</a></li>
                <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<div id="layoutSidenav">

    <!-- SIDEBAR -->
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading">Core</div>

                    <!-- Dashboard (semua role) -->
                    <a class="nav-link" href="index.php">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-tachometer-alt fa-spin text-success"></i>
                        </div>
                        Dashboard
                    </a>

                    <?php if ($level === "admin") : ?>
                        <a class="nav-link" href="index.php?p=user">
                            <div class="sb-nav-link-icon"><i class="fas fa-users text-info"></i></div>
                            Manajemen User
                        </a>
                        <a class="nav-link" href="index.php?p=pemakaian_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-tint text-primary"></i></div>
                            Lihat Pemakaian Warga
                        </a>
                        <a class="nav-link" href="index.php?p=pembayaran_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-bill text-success"></i></div>
                            Pembayaran Warga
                        </a>
                        <a class="nav-link" href="index.php?p=ubah_datameter_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-edit text-warning"></i></div>
                            Ubah Datameter Warga
                        </a>

                    <?php elseif ($level === "bendahara") : ?>
                        <a class="nav-link" href="index.php?p=pembayaran_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-bill text-success"></i></div>
                            Pembayaran Warga
                        </a>
                        <a class="nav-link" href="index.php?p=ubah_datameter_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-edit text-warning"></i></div>
                            Ubah Datameter Warga
                        </a>

                    <?php elseif ($level === "petugas") : ?>
                        <a class="nav-link" href="index.php?p=memasukan_datameter_pemakaian_air_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-plus-circle text-success"></i></div>
                            Memasukan Datameter Pemakaian Air Warga
                        </a>
                        <a class="nav-link" href="index.php?p=mengubah_datameter_air_warga_dalam_satu_bulan">
                            <div class="sb-nav-link-icon"><i class="fas fa-edit text-warning"></i></div>
                            Mengubah Datameter Air Warga Dalam Satu Bulan
                        </a>
                        <a class="nav-link" href="index.php?p=melihat_jumlah_total_pelanggan">
                            <div class="sb-nav-link-icon"><i class="fas fa-users text-info"></i></div>
                            Melihat Jumlah Total Pelanggan
                        </a>
                        <a class="nav-link" href="index.php?p=melihat_jumlah_pemakaian_air_seluruh_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-tint text-primary"></i></div>
                            Melihat Jumlah Pemakaian Air Seluruh Warga
                        </a>
                        <a class="nav-link" href="index.php?p=melihat_infografis_pemakaian_air_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line text-danger"></i></div>
                            Melihat Infografis Pemakaian Air Warga
                        </a>

                    <?php elseif ($level === "warga") : ?>
                        <a class="nav-link" href="index.php?p=memantau_air_tiap_bulan">
                            <div class="sb-nav-link-icon"><i class="fas fa-tint text-primary"></i></div>
                            Memantau Air Tiap Bulan
                        </a>
                        <a class="nav-link" href="index.php?p=melihat_tagihan_tiap_bulan">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-invoice text-warning"></i></div>
                            Melihat Tagihan Tiap Bulan
                        </a>
                        <a class="nav-link" href="index.php?p=melihat_infografis_pemakaian_dan_tagihan_perbulan">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-bar text-success"></i></div>
                            Melihat Infografis Pemakaian Dan Tagihan Perbulan
                        </a>
                    <?php endif; ?>

                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">
                    <i class="fa-regular fa-user fa-flip text-warning"></i>
                    Logged in as: <?php echo htmlspecialchars($dt_user[2]); ?>
                </div>
                <?php echo htmlspecialchars($dt_user[0]) . ' (' . htmlspecialchars($dt_user[1]) . ')'; ?>
            </div>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4"><?php echo htmlspecialchars($h1); ?></h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Menu untuk CRUD User</li>
                </ol>

                <!-- Summary Cards -->
                <div class="row" id="summary">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">Primary Card</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="#">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning text-white mb-4">
                            <div class="card-body">Warning Card</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="#">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">Success Card</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="#">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-danger text-white mb-4">
                            <div class="card-body">Danger Card</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="#">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row" id="chart">
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fa-solid fa-users"></i> Data User
                            </div>
                            <div class="card-body">
                                <canvas id="myAreaChart" width="100%" height="40"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-1"></i> Bar Chart Example
                            </div>
                            <div class="card-body">
                                <canvas id="myBarChart" width="100%" height="40"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                // Nilai default supaya tidak Notice saat halaman pertama dibuka
                $user = $pass2 = $nama = $alamat = $kota = $telephone = '';
                $level = $tipe = $status = '';

                if (isset($_POST['tombol'])) {
                    $t = $_POST['tombol'];
                    if ($t == "user_add") {
                        $user      = $_POST['username'];
                        $pass      = password_hash($_POST['pwd'], PASSWORD_DEFAULT);
                        $pass2     = $_POST['pwd'];
                        $nama      = $_POST['nama'];
                        $alamat    = $_POST['alamat'];
                        $kota      = $_POST['kota'];
                        $telephone = $_POST['telephone'];
                        $level     = $_POST['level'];
                        $tipe      = $_POST['tipe'];
                        $status    = $_POST['status'];

                        $qc = mysqli_query($koneksi, "SELECT username FROM login WHERE username='$user'");
                        $qj = mysqli_num_rows($qc);

                        if (empty($qj)) {
                            mysqli_query($koneksi, "INSERT INTO login (username, password, nama, alamat, kota, telephone, level, tipe, status) VALUE ('$user', '$pass', \"$nama\", '$alamat', '$kota', '$telephone', '$level', '$tipe', '$status')");
                            if (mysqli_affected_rows($koneksi) > 0) {
                                // Reset form hanya kalau berhasil disimpan
                                $user = $pass2 = $nama = $alamat = $kota = $telephone = '';
                                $level = $tipe = $status = '';
                                echo "<div class='alert alert-success alert-dismissible fade show'>
                                        <button type=button class=btn-close data-bs-dismiss=alert></button>
                                        <strong>Data</strong> berhasil dimasukkan...
                                    </div>";
                            } else {
                                echo "<div class='alert alert-danger alert-dismissible fade show'>
                                        <button type=button class=btn-close data-bs-dismiss=alert></button>
                                        <strong>Data</strong> GAGAL dimasukkan...
                                    </div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger alert-dismissible fade show'>
                                    <button type=button class=btn-close data-bs-dismiss=alert></button>
                                    <strong>Username $user</strong> sudah ada...
                                </div>";
                        }
                    }
                }
                ?>
                <div class="card mb-4" id="user_add">
                    <div class="card-header">
                        <i class="fa-solid fa-user-plus me-2 text-success fa-fade"></i> User
                    </div>
                    <div class="card-body">
                       <form method="post" class="needs-validation" id="user_form">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" class="form-control" id="username" placeholder="Enter username" name="username" value="<?php echo $user ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="pwd" class="form-label">Password:</label>
                                <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="pwd" value="<?php echo $pass2 ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama:</label>
                                <input type="text" class="form-control" id="pwd" placeholder="Enter nama" name="nama" value="<?php echo $nama ?>"  required>
                            </div>
                            <div class="mb-3">
                                <label for="alamat">Alamat:</label>
                                <textarea class="form-control" rows="5" id="alamat" name="alamat"><?php echo $alamat ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="kota" class="form-label">Kota:</label>
                                <input type="text" class="form-control" id="kota" placeholder="Enter kota" name="kota" value="<?php echo $kota ?>">
                            </div>
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Telephone:</label>
                                <input type="text" class="form-control" id="telephone" placeholder="Enter telephone" name="telephone" value="<?php echo $telephone ?>">
                            </div>
                            <div class="mb-3">
                                <label for="level" class="form-label">Level:</label>
                                <select class="form-select" name="level">
                                    <option value="">Level</option>
                                    <?php
                                    $lv = array("admin", "bendahara", "petugas", "warga");
                                    foreach ($lv as $lv2) {
                                        if($level == $lv2) $sel = "SELECTED";
                                        else $sel = "";
                                        echo "<option value=$lv2 $sel>" . ucwords($lv2) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tipe" class="form-label">Tipe:</label>
                                <select class="form-select" name="tipe">
                                    <option value="">Tipe</option>
                                    <?php
                                    $t = array("RT", "kos");
                                    foreach ($t as $t2) {
                                        if($tipe == $t2) $sel = "SELECTED";
                                        else $sel = "";
                                        echo "<option value=$t2 $sel>" . ucwords($t2) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status:</label>
                                <select class="form-select" name="status">
                                    <option value="">Status</option>
                                    <?php
                                    $s = array("AKTIF", "TIDAK AKTIF");
                                    foreach ($s as $s2) {
                                        if($status == $s2) $sel = "SELECTED";
                                        else $sel = "";
                                        echo "<option value='$s2' $sel>$s2</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" name="tombol" value="user_add">Simpan</button>
                            </form> 
                    </div>
                </div>
                <!-- Data Table -->
                <div class="card mb-4" id="user_list">
                    <div class="card-header">
                        <i class="fa-solid fa-users me-2 text-success fa-fade"></i> Data User
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>Kota</th>
                                    <th>Telephone</th>
                                    <th>Level</th>
                                    <th>Tipe</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $q=mysqli_query($koneksi,"SELECT username, nama, alamat, kota, telephone, level, tipe, status FROM login ORDER BY level ASC");
                                while($d=mysqli_fetch_row($q)) {
                                    $user=$d[0];
                                    $nama=$d[1];
                                    $alamat=$d[2];
                                    $kota=$d[3];
                                    $telephone=$d[4];
                                    $level=$d[5];
                                    $tipe=$d[6];
                                    $status=$d[7];

                                    echo "<tr>
                                        <th>$user</th>
                                        <th>$nama</th>
                                        <th>$alamat</th>
                                        <th>$kota</th>
                                        <th>$telephone</th>
                                        <th>$level</th>
                                        <th>$tipe</th>
                                        <th>$status</th>
                                    </tr>";
                                }
                                ?>
                        </table>
                    </div>
                </div>

            </div>
        </main>

        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright &copy; Flavia &amp; Rosita</div>
                    <div>
                        <a href="#">Privacy Policy</a>
                        &middot;
                        <a href="#">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../js/scripts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
<script src="../assets/demo/chart-area-demo.js"></script>
<script src="../assets/demo/chart-bar-demo.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script src="../js/datatables-simple-demo.js"></script>
</body>
</html>