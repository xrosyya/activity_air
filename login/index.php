<?php
ob_start();
session_start();
date_default_timezone_set('Asia/Jakarta');
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

$level = strtolower(trim($_SESSION['level'] ?? ($dt_user_row['level'] ?? '')));

// Routing halaman
$page = isset($_GET['p']) ? trim($_GET['p']) : '';
// Bersihkan jika ada sisa parameter lain (misal: catat_tarif_edit&id jadi catat_tarif_edit)
if (strpos($page, '&') !== false) {
    $page = explode('&', $page)[0];
}

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
        "tarif"                                         => ["Manajemen Tarif Air",                               "Manajemen Tarif Air"],
        "catat_meter"                                   => ["Catat Meter", "Daftar pencatatan meter air warga"],
        "lihat_data_pemakaian"                          => ["Lihat Data Pemakaian", "Daftar pencatatan meter air warga"],
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

// HANDLER POST — USER (harus diproses sebelum HTML, gunakan name="aksi_user")
if (isset($_POST['aksi_user'])) {
    $t_user    = $_POST['aksi_user'];
    $user_input= mysqli_real_escape_string($koneksi, trim($_POST['username'] ?? ''));
    $pass2     = $_POST['pwd'] ?? '';
    $nama_in   = mysqli_real_escape_string($koneksi, trim($_POST['nama'] ?? ''));
    $alamat_in = mysqli_real_escape_string($koneksi, trim($_POST['alamat'] ?? ''));
    $kota_in   = mysqli_real_escape_string($koneksi, trim($_POST['kota'] ?? ''));
    $tele_in   = mysqli_real_escape_string($koneksi, trim($_POST['telephone'] ?? ''));
    $level_in  = mysqli_real_escape_string($koneksi, trim($_POST['level'] ?? ''));
    $tipe_in   = mysqli_real_escape_string($koneksi, trim($_POST['tipe'] ?? ''));
    $status_in = mysqli_real_escape_string($koneksi, trim($_POST['status'] ?? ''));

    if ($t_user == "user_add") {
        $pass = password_hash($pass2, PASSWORD_DEFAULT);
        $qc = mysqli_query($koneksi, "SELECT username FROM login WHERE username='$user_input'");
        if (mysqli_num_rows($qc) == 0) {
            mysqli_query($koneksi, "INSERT INTO login (username, password, nama, alamat, kota, telephone, level, tipe, status) VALUES ('$user_input', '$pass', '$nama_in', '$alamat_in', '$kota_in', '$tele_in', '$level_in', '$tipe_in', '$status_in')");
            if (mysqli_affected_rows($koneksi) > 0) {
                $_SESSION['notif'] = ['type' => 'success', 'msg' => "User <b>$user_input</b> berhasil ditambahkan."];
            } else {
                $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal menambahkan user: " . mysqli_error($koneksi)];
            }
        } else {
            $_SESSION['notif'] = ['type' => 'warning', 'msg' => "Username <b>$user_input</b> sudah ada."];
        }
        header("Location: index.php?p=user");
        exit;
    }

    if ($t_user == "user_edit") {
        $user_lama = mysqli_real_escape_string($koneksi, trim($_POST['user_lama'] ?? ''));
        if (!empty($pass2)) {
            $pass = password_hash($pass2, PASSWORD_DEFAULT);
            $query_up = "UPDATE login SET username='$user_input', password='$pass', nama='$nama_in', alamat='$alamat_in', kota='$kota_in', telephone='$tele_in', level='$level_in', tipe='$tipe_in', status='$status_in' WHERE username='$user_lama'";
        } else {
            $query_up = "UPDATE login SET username='$user_input', nama='$nama_in', alamat='$alamat_in', kota='$kota_in', telephone='$tele_in', level='$level_in', tipe='$tipe_in', status='$status_in' WHERE username='$user_lama'";
        }
        if (mysqli_query($koneksi, $query_up)) {
            $_SESSION['notif'] = ['type' => 'success', 'msg' => "User <b>$user_input</b> berhasil diupdate."];
        } else {
            $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal update user: " . mysqli_error($koneksi)];
        }
        header("Location: index.php?p=user");
        exit;
    }
}

// HANDLER DELETE USER (via GET)
if (isset($_GET['p']) && $_GET['p'] == 'user_hapus' && isset($_GET['user'])) {
    $user_hapus = mysqli_real_escape_string($koneksi, $_GET['user']);
    $hapus = mysqli_query($koneksi, "DELETE FROM login WHERE username='$user_hapus'");
    if ($hapus) {
        $_SESSION['notif'] = ['type' => 'success', 'msg' => "User <b>$user_hapus</b> berhasil dihapus."];
    } else {
        $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal menghapus user."];
    }
    header("Location: index.php?p=user");
    exit;
}

// GUARD: petugas edit meter > 30 hari → redirect sebelum HTML
if (isset($_GET['p']) && $_GET['p'] == 'meter_edit' && isset($_GET['id'])) {
    $id_guard_m = (int) $_GET['id'];
    $q_guard_m  = mysqli_query($koneksi, "SELECT tgl FROM pemakaian WHERE no='$id_guard_m'");
    $d_guard_m  = mysqli_fetch_assoc($q_guard_m);
    if ($d_guard_m) {
        $level_g = strtolower(trim($dt_user_row['level'] ?? $_SESSION['level'] ?? ''));
        if ($level_g === 'petugas') {
            $tgl_g  = date_create($d_guard_m['tgl'] ?? '');
            $diff_g = $tgl_g ? date_diff($tgl_g, date_create())->days : 0;
            if ($diff_g > 30) {
                $_SESSION['notif'] = ['type' => 'warning', 'msg' => 'Akses ditolak. Data lebih dari 30 hari tidak dapat diedit.'];
                header("Location: index.php?p=catat_meter");
                exit;
            }
        }
    }
}

// HANDLER POST — TARIF
if (isset($_POST['tombol'])) {
    $aksi = $_POST['tombol'];

    // --- TAMBAH TARIF ---
    if ($aksi == 'tarif_add') {
        $id_t  = mysqli_real_escape_string($koneksi, trim($_POST['yid_tarif']));
        $tipe  = mysqli_real_escape_string($koneksi, trim($_POST['tipe']));
        $tarif = mysqli_real_escape_string($koneksi, trim($_POST['tarif']));
        $stat  = mysqli_real_escape_string($koneksi, trim($_POST['status']));

        // Cek duplikat
        $cek = mysqli_query($koneksi, "SELECT id_tarif FROM tarif WHERE id_tarif='$id_t'");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['notif'] = ['type' => 'warning', 'msg' => "ID Tarif <b>$id_t</b> sudah ada!"];
        } else {
            mysqli_query($koneksi, "INSERT INTO tarif (id_tarif, tipe, tarif, status) VALUES ('$id_t','$tipe','$tarif','$stat')");
            if (mysqli_affected_rows($koneksi) > 0) {
                $_SESSION['notif'] = ['type' => 'success', 'msg' => "Tarif <b>$id_t</b> berhasil ditambahkan."];
            } else {
                $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal menambahkan tarif. Periksa koneksi database."];
            }
        }
        header("Location: index.php?p=tarif");
        exit;
    }

    // --- EDIT TARIF ---
    if ($aksi == 'tarif_edit') {
        $id_t  = mysqli_real_escape_string($koneksi, trim($_POST['yid_tarif']));
        $tipe  = mysqli_real_escape_string($koneksi, trim($_POST['tipe']));
        $tarif = mysqli_real_escape_string($koneksi, trim($_POST['tarif']));
        $stat  = mysqli_real_escape_string($koneksi, trim($_POST['status']));

        mysqli_query($koneksi, "UPDATE tarif SET tipe='$tipe', tarif='$tarif', status='$stat' WHERE id_tarif='$id_t'");
        if (mysqli_affected_rows($koneksi) >= 0) {
            $_SESSION['notif'] = ['type' => 'success', 'msg' => "Tarif <b>$id_t</b> berhasil diperbarui."];
        } else {
            $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal memperbarui tarif."];
        }
        header("Location: index.php?p=tarif");
        exit;
    }

    // --- HAPUS TARIF ---
    if ($aksi == 'tarif_hapus') {
        $id_t = mysqli_real_escape_string($koneksi, trim($_POST['id_tarif']));
        mysqli_query($koneksi, "DELETE FROM tarif WHERE id_tarif='$id_t'");
        if (mysqli_affected_rows($koneksi) > 0) {
            $_SESSION['notif'] = ['type' => 'success', 'msg' => "Tarif <b>$id_t</b> berhasil dihapus."];
        } else {
            $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal menghapus tarif."];
        }
        header("Location: index.php?p=tarif");
        exit;
    }

    // --- TAMBAH CATAT METER ---
    if ($aksi == 'meter_add') {
        $id_pel  = mysqli_real_escape_string($koneksi, trim($_POST['id_pelanggan']));
        $tgl     = mysqli_real_escape_string($koneksi, trim($_POST['tgl']));
        $waktu   = mysqli_real_escape_string($koneksi, trim($_POST['waktu']));
        $m_awal  = mysqli_real_escape_string($koneksi, trim($_POST['meter_awal']));
        $m_akhir = mysqli_real_escape_string($koneksi, trim($_POST['meter_akhir']));
        $pakai   = $m_akhir - $m_awal;

        // Validasi: meter akhir harus lebih besar dari meter awal
        if ((float)$m_akhir <= (float)$m_awal) {
            $_SESSION['notif'] = ['type' => 'warning', 'msg' => "Meter akhir harus lebih besar dari meter awal."];
            header("Location: index.php?p=catat_meter");
            exit;
        }

        // Ambil tarif aktif pelanggan
        $q_tarif = mysqli_query($koneksi, "SELECT t.tarif, t.id_tarif FROM tarif t 
                                            JOIN login l ON LOWER(l.tipe) = LOWER(t.tipe) 
                                            WHERE l.username = '$id_pel' AND t.status='AKTIF' LIMIT 1");
        $d_tarif  = mysqli_fetch_assoc($q_tarif);
        $kd_tarif = $d_tarif['id_tarif'] ?? '';
        $tagihan  = $pakai * ($d_tarif['tarif'] ?? 0);

        mysqli_query($koneksi, "INSERT INTO pemakaian (username, meter_awal, meter_akhir, pemakaian, tgl, waktu, kd_tarif, tagihan, status) 
                                VALUES ('$id_pel', '$m_awal', '$m_akhir', '$pakai', '$tgl', '$waktu', '$kd_tarif', '$tagihan', 'BELUM BAYAR')");
        if (mysqli_affected_rows($koneksi) > 0) {
            $_SESSION['notif'] = ['type' => 'success', 'msg' => "Data meter berhasil disimpan."];
        } else {
            $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal menyimpan data: " . mysqli_error($koneksi)];
        }
        header("Location: index.php?p=catat_meter");
        exit;
    }

    // --- EDIT CATAT METER ---
    if ($aksi == 'meter_edit') {
        $id_rec  = mysqli_real_escape_string($koneksi, trim($_POST['id_meter']));
        $id_pel  = mysqli_real_escape_string($koneksi, trim($_POST['id_pelanggan']));
        $tgl     = mysqli_real_escape_string($koneksi, trim($_POST['tgl']));
        $waktu   = mysqli_real_escape_string($koneksi, trim($_POST['waktu']));
        $m_awal  = mysqli_real_escape_string($koneksi, trim($_POST['meter_awal']));
        $m_akhir = mysqli_real_escape_string($koneksi, trim($_POST['meter_akhir']));
        $pakai   = $m_akhir - $m_awal;

        // Validasi: meter akhir harus lebih besar dari meter awal
        if ((float)$m_akhir <= (float)$m_awal) {
            $_SESSION['notif'] = ['type' => 'warning', 'msg' => "Meter akhir harus lebih besar dari meter awal."];
            header("Location: index.php?p=catat_meter");
            exit;
        }

        $q_tarif = mysqli_query($koneksi, "SELECT t.tarif, t.id_tarif FROM tarif t 
                                            JOIN login l ON l.kd_tarif = t.id_tarif 
                                            WHERE l.username = '$id_pel' AND t.status='AKTIF' LIMIT 1");
        $d_tarif  = mysqli_fetch_assoc($q_tarif);
        $kd_tarif = $d_tarif['id_tarif'] ?? '';
        $tagihan  = $pakai * ($d_tarif['tarif'] ?? 0);

        mysqli_query($koneksi, "UPDATE pemakaian SET username='$id_pel', meter_awal='$m_awal', meter_akhir='$m_akhir', 
                                pemakaian='$pakai', tgl='$tgl', waktu='$waktu', kd_tarif='$kd_tarif', tagihan='$tagihan' 
                                WHERE no='$id_rec'");
        if (mysqli_affected_rows($koneksi) >= 0) {
            $_SESSION['notif'] = ['type' => 'success', 'msg' => "Data meter berhasil diperbarui."];
        } else {
            $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal memperbarui: " . mysqli_error($koneksi)];
        }
        header("Location: index.php?p=catat_meter");
        exit;
    }

    // --- HAPUS CATAT METER ---
    if ($aksi == 'meter_hapus') {
        $id_rec = mysqli_real_escape_string($koneksi, trim($_POST['id_meter']));
        mysqli_query($koneksi, "DELETE FROM pemakaian WHERE no='$id_rec'");
        if (mysqli_affected_rows($koneksi) > 0) {
            $_SESSION['notif'] = ['type' => 'success', 'msg' => "Data meter berhasil dihapus."];
        } else {
            $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal menghapus data meter: " . mysqli_error($koneksi)];
        }
        header("Location: index.php?p=catat_meter");
        exit;
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
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
        <style>
            #tarif_table_wrapper .dataTables_filter input {
                border-radius: 8px; border: 1px solid #1cc88a; padding: 5px 12px;
            }
            #tarif_table_wrapper .dataTables_filter input:focus {
                outline: none; box-shadow: 0 0 0 0.15rem rgba(28,200,138,.25); border-color: #1cc88a;
            }
            #tarif_table_wrapper .dataTables_length select {
                border-radius: 8px; border: 1px solid #1cc88a; padding: 4px 8px;
            }
            #tarif_table_wrapper .dataTables_paginate .paginate_button.current,
            #tarif_table_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: #1cc88a !important; border-color: #1cc88a !important;
                color: #fff !important; border-radius: 6px;
            }
            #tarif_table_wrapper .dataTables_paginate .paginate_button:hover {
                background: #e8f5e9 !important; border-color: #1cc88a !important;
                color: #1cc88a !important; border-radius: 6px;
            }
            /* ===== CSS CATAT METER ===== */
            #meter_table_wrapper .dataTables_filter input {
                border-radius: 8px; border: 1px solid #4e73df; padding: 5px 12px;
            }
            #meter_table_wrapper .dataTables_filter input:focus {
                outline: none; box-shadow: 0 0 0 0.15rem rgba(78,115,223,.25); border-color: #4e73df;
            }
            #meter_table_wrapper .dataTables_length select {
                border-radius: 8px; border: 1px solid #4e73df; padding: 4px 8px;
            }
            #meter_table_wrapper .dataTables_paginate .paginate_button.current,
            #meter_table_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: #4e73df !important; border-color: #4e73df !important;
                color: #fff !important; border-radius: 6px;
            }
            #meter_table_wrapper .dataTables_paginate .paginate_button:hover {
                background: #eaecf4 !important; border-color: #4e73df !important;
                color: #4e73df !important; border-radius: 6px;
            }

        </style>
    </head>
    <body class="sb-nav-fixed">

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

    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading">Core</div>

                    <a class="nav-link" href="index.php">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-tachometer-alt fa-spin text-success"></i>
                        </div>
                        Dashboard
                    </a>

                    <?php if ($level == "admin") : ?>
                        <a class="nav-link" href="index.php?p=user">
                            <div class="sb-nav-link-icon"><i class="fas fa-users text-info"></i></div>
                            Manajemen User
                        </a>
                        <a class="nav-link" href="index.php?p=tarif">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags text-warning"></i></div>
                            Manajemen Tarif
                        </a>
                        <a class="nav-link" href="index.php?p=catat_meter">
                            <div class="sb-nav-link-icon"><i class="fas fa-tint text-primary"></i></div>
                            Lihat Pemakaian Warga
                        </a>
                        <a class="nav-link" href="index.php?p=pembayaran_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-bill text-success"></i></div>
                            Pembayaran Warga
                        </a>

                    <?php elseif ($level === "bendahara") : ?>
                        <a class="nav-link" href="index.php?p=transaksi_pembayaran">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave text-success"></i></div>
                            Transaksi Pembayaran 
                        </a>
                        <a class="nav-link" href="index.php?p=tarif">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags text-warning"></i></div>
                            Manajemen Tarif
                        </a>
                        <a class="nav-link" href="index.php?p=lihat_komplain">
                            <div class="sb-nav-link-icon"><i class="fas fa-comment-dots text-danger"></i></div>
                            Lihat Komplain
                        </a>
                        <a class="nav-link" href="index.php?p=lihat_data_pemakaian">
                            <div class="sb-nav-link-icon"><i class="fas fa-tint text-info"></i></div>
                            Lihat Data Pemakaian
                        </a>

                    <?php elseif ($level === "petugas") : ?>
                        <a class="nav-link" href="index.php?p=catat_meter">
                            <div class="sb-nav-link-icon"><i class="fas fa-plus-circle text-success"></i></div>
                            Catat Meter
                        </a>
                        <!-- <a class="nav-link" href="index.php?p=mengubah_datameter_air_warga_dalam_satu_bulan">
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
                            Melihat Infografis Pemakaian Air Warga -->
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

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4"><?php echo htmlspecialchars($h1); ?></h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($li); ?></li>
                </ol>

                <?php if (isset($_SESSION['notif'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['notif']['type']; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-1"></i> <?php echo $_SESSION['notif']['msg']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['notif']); ?>
                <?php endif; ?>

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
                // Nilai default dan Mode
                $user = $pass2 = $nama = $alamat = $kota = $telephone = '';
                $level_form = $tipe_form = $status_form = '';
                $mode = "user_add"; 
                $txt_tombol = "Simpan";
                $display_form = in_array($page, ['user', 'user_edit']) ? "block" : "none";

                // Ambil data jika mode edit
                if (isset($_GET['p']) && $_GET['p'] == 'user_edit' && isset($_GET['user'])) {
                    $mode = "user_edit";
                    $txt_tombol = "Update Data";
                    $display_form = "block";
                    $get_user = mysqli_real_escape_string($koneksi, $_GET['user']);
                    
                    $q_edit = mysqli_query($koneksi, "SELECT * FROM login WHERE username='$get_user'");
                    if ($d_edit = mysqli_fetch_assoc($q_edit)) {
                        $user      = $d_edit['username'];
                        $nama      = $d_edit['nama'];
                        $alamat    = $d_edit['alamat'];
                        $kota      = $d_edit['kota'];
                        $telephone = $d_edit['telephone'];
                        $level_form= $d_edit['level'];
                        $tipe_form = $d_edit['tipe'];
                        $status_form = $d_edit['status'];
                    }
                }

                // Notif dari session
                if (isset($_SESSION['notif']) && in_array($page, ['user', 'user_edit', ''])) {
                    // ditampilkan di blok notif utama di atas
                }
                ?>
                <div id="user_add" class="card mb-4" style="display: <?php echo (in_array($page, ['user', 'user_edit'])) ? $display_form : 'none'; ?>;"><?php // hanya tampil di halaman user ?>
                    <div class="card-header d-flex align-items-center gap-2">
                        <?php if($mode == 'user_edit'): ?>
                            <i class="fa-solid fa-user-pen me-1 text-warning fa-fade"></i>
                            <span>Edit User &mdash; <strong><?php echo htmlspecialchars($user); ?></strong></span>
                        <?php else: ?>
                            <i class="fa-solid fa-users me-1 text-success fa-fade"></i>
                            <span>Tambah User</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">

                        <?php if($mode == 'user_edit'): ?>
                        <!-- INFO CARD: username dikunci saat edit -->
                        <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-4" style="border-left: 4px solid #0dcaf0;">
                            <i class="fas fa-lock"></i>
                            <span>Mode Edit &mdash; Username <strong><?php echo htmlspecialchars($user); ?></strong> terkunci. Ubah data lain sesuai kebutuhan.</span>
                        </div>
                        <?php endif; ?>

                        <form method="post" action="index.php" class="needs-validation" id="user_form">
                            <!-- Hidden fields untuk identifikasi aksi -->
                            <input type="hidden" name="aksi_user" value="<?php echo $mode; ?>">
                            <?php if($mode == 'user_edit'): ?>
                            <input type="hidden" name="user_lama" value="<?php echo htmlspecialchars($user); ?>">
                            <?php endif; ?>

                            <!-- USERNAME: dikunci saat edit, bisa diisi saat tambah -->
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Username:</label>
                                <?php if($mode == 'user_edit'): ?>
                                    <!-- Tampilkan sebagai display box, tetap kirim value via hidden input -->
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i class="fas fa-lock fa-sm"></i></span>
                                        <input type="text" class="form-control bg-light text-muted fw-bold"
                                               value="<?php echo htmlspecialchars($user); ?>"
                                               readonly tabindex="-1">
                                    </div>
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($user); ?>">
                                    <div class="form-text text-muted">Username tidak dapat diubah saat mode edit.</div>
                                <?php else: ?>
                                    <input type="text" class="form-control" id="username"
                                           placeholder="Masukkan username" name="username"
                                           value="<?php echo htmlspecialchars($user ?? ''); ?>" required>
                                <?php endif; ?>
                            </div>

                            <!-- PASSWORD -->
                            <div class="mb-3">
                                <label for="pwd" class="form-label fw-semibold">Password:</label>
                                <input type="password" class="form-control" id="pwd" name="pwd"
                                       placeholder="<?php echo $mode == 'user_edit' ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan password'; ?>"
                                       <?php if($mode != 'user_edit') echo 'required'; ?>>
                                <?php if($mode == 'user_edit'): ?>
                                    <div class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password.</div>
                                <?php endif; ?>
                            </div>

                            <!-- NAMA + TELEPHONE (2 kolom) -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="nama" class="form-label fw-semibold">Nama:</label>
                                    <input type="text" class="form-control" id="nama"
                                           placeholder="Masukkan nama lengkap" name="nama"
                                           value="<?php echo htmlspecialchars($nama ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label fw-semibold">Telephone:</label>
                                    <input type="text" class="form-control" id="telephone"
                                           placeholder="Masukkan nomor telepon" name="telephone"
                                           value="<?php echo htmlspecialchars($telephone ?? ''); ?>">
                                </div>
                            </div>

                            <!-- ALAMAT -->
                            <div class="mb-3">
                                <label for="alamat" class="form-label fw-semibold">Alamat:</label>
                                <textarea class="form-control" rows="3" id="alamat" name="alamat"
                                          placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($alamat ?? ''); ?></textarea>
                            </div>

                            <!-- KOTA + LEVEL + TIPE + STATUS (2 kolom) -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="kota" class="form-label fw-semibold">Kota:</label>
                                    <input type="text" class="form-control" id="kota"
                                           placeholder="Masukkan kota" name="kota"
                                           value="<?php echo htmlspecialchars($kota ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="level" class="form-label fw-semibold">Level:</label>
                                    <select class="form-select" name="level" required>
                                        <option value="">-- Pilih Level --</option>
                                        <?php
                                        $lv = array("admin", "bendahara", "petugas", "warga");
                                        foreach ($lv as $lv2) {
                                            $sel = (isset($level_form) && $level_form == $lv2) ? "selected" : "";
                                            echo "<option value='$lv2' $sel>" . ucwords($lv2) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="tipe" class="form-label fw-semibold">Tipe:</label>
                                    <select class="form-select" name="tipe">
                                        <option value="">-- Pilih Tipe --</option>
                                        <?php
                                        $tp = array("RT", "kos");
                                        foreach ($tp as $t2) {
                                            $sel = (isset($tipe_form) && $tipe_form == $t2) ? "selected" : "";
                                            echo "<option value='$t2' $sel>" . ucwords($t2) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-semibold">Status:</label>
                                    <select class="form-select" name="status">
                                        <option value="">-- Pilih Status --</option>
                                        <?php
                                        $st = array("AKTIF", "TIDAK AKTIF");
                                        foreach ($st as $s2) {
                                            $sel = (isset($status_form) && $status_form == $s2) ? "selected" : "";
                                            echo "<option value='$s2' $sel>$s2</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- TOMBOL AKSI -->
                            <div class="d-flex gap-2 mt-4 pt-2 border-top">
                                <button type="submit" class="btn <?php echo $mode == 'user_edit' ? 'btn-warning' : 'btn-primary'; ?> px-4">
                                    <i class="fas <?php echo $mode == 'user_edit' ? 'fa-save' : 'fa-plus'; ?> me-1"></i>
                                    <?php echo $txt_tombol; ?>
                                </button>
                                <?php if($mode == "user_edit"): ?>
                                    <a href="index.php?p=user" class="btn btn-secondary px-4">
                                        <i class="fas fa-times me-1"></i> Batal
                                    </a>
                                <?php endif; ?>
                            </div>

                        </form>
                    </div>
                </div>
                <div class="card mb-4" id="user_list" style="display:<?php echo in_array($page, ['user','user_edit']) ? 'block' : 'none'; ?>;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-users me-2 text-success fa-fade"></i> Data User</span>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="datatablesSimple" class="table table-bordered table-striped table-hover align-middle mb-0">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>Kota</th>
                                    <th>Telephone</th>
                                    <th>Level</th>
                                    <th>Tipe</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $q = mysqli_query($koneksi, "SELECT username, nama, alamat, kota, telephone, level, tipe, status FROM login ORDER BY level ASC");
                                $no = 1;
                                while ($d = mysqli_fetch_assoc($q)) {
                                    $user      = htmlspecialchars($d['username']);
                                    $nama      = htmlspecialchars($d['nama']);
                                    $alamat    = htmlspecialchars($d['alamat'] ?: '-');
                                    $kota      = htmlspecialchars($d['kota'] ?: '-');
                                    $telephone = htmlspecialchars($d['telephone'] ?: '-');
                                    $level     = htmlspecialchars($d['level'] ?: '-');
                                    $tipe      = htmlspecialchars($d['tipe'] ?: '-');
                                    $status_raw = $d['status'];

                                    if ($status_raw === 'AKTIF') {
                                        $badge_status = "<span class='badge bg-success px-2 py-1'>AKTIF</span>";
                                    } elseif (!empty($status_raw)) {
                                        $badge_status = "<span class='badge bg-secondary px-2 py-1'>" . htmlspecialchars($status_raw) . "</span>";
                                    } else {
                                        $badge_status = "<span class='text-muted'>-</span>";
                                    }

                                    echo "<tr>
                                        <td class='text-center text-muted'>$no</td>
                                        <td><span class='fw-semibold text-dark'>$user</span></td>
                                        <td>$nama</td>
                                        <td>$alamat</td>
                                        <td>$kota</td>
                                        <td>$telephone</td>
                                        <td><span class='badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2'>$level</span></td>
                                        <td class='text-center'>$tipe</td>
                                        <td class='text-center'>$badge_status</td>
                                        <td class='text-center' style='white-space:nowrap;'>
                                            <a href='index.php?p=user_edit&user=$user' class='btn btn-success btn-sm' title='Edit'>
                                                <i class='fas fa-edit'></i>
                                            </a>
                                            <button type='button' class='btn btn-danger btn-sm' title='Hapus'
                                                data-bs-toggle='modal' data-bs-target='#myModalUser'
                                                data-username='$user'>
                                                <i class='fas fa-trash'></i>
                                            </button>
                                        </td>
                                    </tr>";
                                    $no++;
                                }
                                ?>
                        </table>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm" id="tarif_list" style="display:<?php echo (in_array($page, ['tarif', 'tarif_edit'])) ? 'block' : 'none'; ?>; border-top: 4px solid #1cc88a; border-radius: 0.5rem;">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8fff8 0%, #e8f5e9 100%);">
                        <div class="d-flex align-items-center gap-2">
                            <div style="background:#1cc88a;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-tags text-white"></i>
                            </div>
                            <div>
                                <h6 class="m-0 fw-bold text-success">Data Tarif Air</h6>
                                <small class="text-muted">Daftar seluruh tarif yang tersedia</small>
                            </div>
                        </div>
                        <button type="button" id="btn_tambah_tarif" class="btn btn-success d-flex align-items-center gap-2" style="border-radius:8px; padding:8px 18px; font-weight:600;">
                            <i class="fas fa-plus"></i> Tambah Tarif
                        </button>
                    </div>
                    <div class="card-body">
                        <table id="tarif_table" class="table table-hover align-middle w-100" style="font-size:0.95rem;">
                            <thead style="background:#f1f9f1;">
                                <tr>
                                    <th class="text-secondary fw-semibold" style="width:60px;">No</th>
                                    <th class="text-secondary fw-semibold">ID Tarif</th>
                                    <th class="text-secondary fw-semibold">Tipe Tarif</th>
                                    <th class="text-secondary fw-semibold">Tarif (Rp)</th>
                                    <th class="text-secondary fw-semibold text-center">Status</th>
                                    <th class="text-secondary fw-semibold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $qt = mysqli_query($koneksi, "SELECT * FROM tarif ORDER BY id_tarif ASC");
                                while ($dt = mysqli_fetch_array($qt)) {
                                    $id_t   = htmlspecialchars($dt['id_tarif']);
                                    $tipe   = htmlspecialchars($dt['tipe']);
                                    $harga  = $dt['tarif'];
                                    $stat   = htmlspecialchars($dt['status']);
                                    $badge  = strtolower($stat) == 'aktif'
                                        ? "<span class='badge rounded-pill px-3 py-2' style='background:#d4edda;color:#155724;font-size:0.8rem;'><i class='fas fa-circle me-1' style='font-size:0.5rem;'></i>AKTIF</span>"
                                        : "<span class='badge rounded-pill px-3 py-2' style='background:#f8d7da;color:#721c24;font-size:0.8rem;'><i class='fas fa-circle me-1' style='font-size:0.5rem;'></i>TIDAK AKTIF</span>";

                                    echo "<tr>
                                            <td class='text-muted'>$no</td>
                                            <td><span class='fw-bold text-dark'>$id_t</span></td>
                                            <td>$tipe</td>
                                            <td class='fw-semibold text-success'>Rp " . number_format($harga, 0, ',', '.') . "</td>
                                            <td class='text-center'>$badge</td>
                                            <td class='text-center'>
                                                <a href='index.php?p=tarif_edit&id=$id_t' class='btn btn-sm btn-warning me-1' style='min-width:75px;'>
                                                    <i class='fas fa-edit me-1'></i>Ubah
                                                </a>
                                                <button type='button' class='btn btn-sm btn-danger' style='min-width:75px;'
                                                    data-bs-toggle='modal' data-bs-target='#myModal' data-id_tarif='$id_t'>
                                                    <i class='fas fa-trash me-1'></i>Hapus
                                                </button>
                                            </td>
                                        </tr>";
                                    $no++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php
                // Ambil data edit tarif sebelum render form (agar bisa pre-fill via PHP)
                $edit_t = null;
                $tarif_add_display = 'none';
                if ($page == "tarif_edit" && isset($_GET['id'])) {
                    $id_edit  = mysqli_real_escape_string($koneksi, $_GET['id']);
                    $q_edit_t = mysqli_query($koneksi, "SELECT * FROM tarif WHERE id_tarif='$id_edit'");
                    $edit_t   = mysqli_fetch_array($q_edit_t);
                    if ($edit_t) $tarif_add_display = 'block';
                }
                ?>
                <div id="tarif_add" class="card mb-4" style="display: <?php echo $tarif_add_display; ?>; border-top: 4px solid #1cc88a; box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,0.15);">
                    <div class="card-header bg-white py-3">
                        <h5 class="m-0 fw-bold text-success">
                            <i class="fas <?php echo $edit_t ? 'fa-edit' : 'fa-plus-circle'; ?> me-2"></i>
                            <?php echo $edit_t ? 'Edit Data Tarif' : 'Form Input Data Tarif'; ?>
                        </h5>
                    </div>
                    <div class="card-body px-4 py-4">
                        <form id="tarif_form" method="POST" action="index.php">
                            <div class="row mb-3 align-items-center">
                                <label class="col-md-3 col-form-label fw-bold fs-6">ID Tarif</label>
                                <div class="col-md-9">
                                    <?php if ($edit_t): ?>
                                        <input type="text" class="form-control form-control-lg bg-light text-muted"
                                            name="yid_tarif" value="<?php echo htmlspecialchars($edit_t['id_tarif']); ?>" readonly>
                                    <?php else: ?>
                                        <input type="text" class="form-control form-control-lg" name="yid_tarif"
                                            placeholder="Masukkan ID (Contoh: TRF001)" required>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <label class="col-md-3 col-form-label fw-bold fs-6">Tipe Tarif</label>
                                <div class="col-md-9">
                                    <?php
                                    $tipe_options = ['Rumah Tangga', 'Industri', 'Kos', 'Niaga', 'Sosial'];
                                    $tipe_val = $edit_t ? htmlspecialchars($edit_t['tipe']) : '';
                                    ?>
                                    <select class="form-select form-select-lg" name="tipe" required>
                                        <option value="">-- Pilih Tipe --</option>
                                        <?php foreach ($tipe_options as $opt): ?>
                                            <option value="<?php echo $opt; ?>" <?php echo ($tipe_val == $opt) ? 'selected' : ''; ?>>
                                                <?php echo $opt; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <label class="col-md-3 col-form-label fw-bold fs-6">Besaran Tarif</label>
                                <div class="col-md-9">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white fw-bold">Rp</span>
                                        <input type="number" class="form-control" name="tarif" min="0" required
                                            value="<?php echo $edit_t ? htmlspecialchars($edit_t['tarif']) : ''; ?>"
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <label class="col-md-3 col-form-label fw-bold fs-6">Status Aktivasi</label>
                                <div class="col-md-9">
                                    <div class="d-flex gap-4 mt-1">
                                        <?php
                                        $status_db = $edit_t ? strtolower($edit_t['status']) : 'aktif';
                                        ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="st_aktif" value="aktif"
                                                <?php echo ($status_db == 'aktif') ? 'checked' : ''; ?> required>
                                            <label class="form-check-label fw-bold text-success fs-6" for="st_aktif">
                                                <i class="fas fa-check-circle me-1"></i> Aktif
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="st_non" value="tidak aktif"
                                                <?php echo ($status_db == 'tidak aktif') ? 'checked' : ''; ?> required>
                                            <label class="form-check-label fw-bold text-danger fs-6" for="st_non">
                                                <i class="fas fa-times-circle me-1"></i> Tidak Aktif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="index.php?p=tarif" class="btn btn-secondary btn-lg px-4">
                                    <i class="fas fa-arrow-left me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-success btn-lg px-5" name="tombol"
                                    value="<?php echo $edit_t ? 'tarif_edit' : 'tarif_add'; ?>">
                                    <i class="fas fa-save me-1"></i> <?php echo $edit_t ? 'Update Data' : 'Simpan Data'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm" id="catat_meter_list" style="display:<?php echo (in_array($page, ['catat_meter', 'meter_edit', 'lihat_data_pemakaian'])) ? 'block' : 'none'; ?>; border-top: 4px solid #4e73df; border-radius: 0.5rem;">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8f9ff 0%, #eaecf4 100%);">
                        <div class="d-flex align-items-center gap-2">
                            <div style="background:#4e73df;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-tachometer-alt text-white"></i>
                            </div>
                            <div>
                                <h6 class="m-0 fw-bold text-primary">Data Catat Meter</h6>
                                <small class="text-muted">Daftar pencatatan meter air warga</small>
                            </div>
                        </div>
                        <?php if ($page !== 'lihat_data_pemakaian'): ?>
                        <button type="button" id="btn_tambah_meter" class="btn btn-primary d-flex align-items-center gap-2" style="border-radius:8px; padding:8px 18px; font-weight:600;">
                            <i class="fas fa-plus"></i> Catat Meter
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php
                        // Notifikasi session untuk catat meter
                        if (isset($_SESSION['notif'])) {
                            $n = $_SESSION['notif'];
                            echo "<div class='alert alert-{$n['type']} alert-dismissible fade show'>
                                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                    {$n['msg']}
                                  </div>";
                            unset($_SESSION['notif']);
                        }
                        ?>
                        <table id="meter_table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Tanggal &amp; Waktu</th>
                                    <th>Meter Awal</th>
                                    <th>Meter Akhir</th>
                                    <th>Pemakaian (m³)</th>
                                    <?php if ($page !== 'lihat_data_pemakaian'): ?>
                                    <th>Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no_m = 1;
                                $qm = mysqli_query($koneksi, "
                                    SELECT p.*, l.nama 
                                    FROM pemakaian p 
                                    LEFT JOIN login l ON p.username = l.username 
                                    ORDER BY p.tgl DESC, p.username ASC
                                ");
                                if (!$qm) {
                                    echo "<tr><td colspan='9' class='text-center text-danger py-3'>Gagal: " . mysqli_error($koneksi) . "</td></tr>";
                                }
                                while ($dm = mysqli_fetch_assoc($qm)) {
                                $id_r    = $dm['no'];
                                $id_p    = htmlspecialchars($dm['username']);
                                $nama_p  = htmlspecialchars($dm['nama'] ?? $dm['username']);
                                $m_awal  = $dm['meter_awal'];
                                $m_akhir = $dm['meter_akhir'];
                                $pakai   = $dm['pemakaian'];

                                // Format tanggal: Y-m-d -> d-m-Y
                                $tgl_raw = $dm['tgl'] ?? '';
                                $tgl_fmt = $tgl_raw ? date('d-m-Y', strtotime($tgl_raw)) : '-';

                                // Waktu
                                $waktu_raw = $dm['waktu'] ?? '';
                                preg_match('/\d{2}:\d{2}(:\d{2})?/', $waktu_raw, $wm);
                                $waktu_fmt = isset($wm[0]) ? $wm[0] : '-';

                                // Hitung selisih hari seperti index2
                                $tgl_tabel    = date_create($tgl_raw);
                                $tgl_sekarang = date_create();
                                $diff         = date_diff($tgl_tabel, $tgl_sekarang);
                                $selisih      = $diff ? $diff->days : 0;

                                // Format: 12-05-2026 17:28:59 | 2026-05-12 0 hari
                                $tgl_waktu = "$tgl_fmt $waktu_fmt | " . date("Y-m-d") . " $selisih hari";

                                echo "<tr>
                                    <td>$no_m</td>
                                    <td>$nama_p</td>
                                    <td>$tgl_waktu</td>
                                    <td>$m_awal</td>
                                    <td>$m_akhir</td>
                                    <td>$pakai m³</td>";

                                // Kolom aksi: sembunyikan di halaman lihat_data_pemakaian
                                if ($page !== 'lihat_data_pemakaian') {
                                    echo "<td>";
                                    $level_login = strtolower(trim($dt_user_row['level'] ?? $_SESSION['level'] ?? ''));
                                    if ($level_login == 'admin' || $level_login == 'bendahara' || $selisih <= 30) {
                                        echo "
                                            <a href='index.php?p=meter_edit&id=$id_r'><button type='button' class='btn btn-outline-warning btn-sm'>Edit</button></a>
                                            <button type='button' class='btn btn-outline-danger btn-sm'
                                                data-bs-toggle='modal' data-bs-target='#myModalMeter'
                                                data-id_meter='$id_r' data-id_pelanggan='$id_p'>Hapus</button>
                                        ";
                                    }
                                    echo "</td>";
                                }

                                echo "</tr>";
                                $no_m++;
                            }
                                
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php
                // Ambil data edit meter di sini (sebelum form render) agar bisa pre-fill via PHP
                $edit_m = null;
                $edit_m_display = 'none';
                if ($page == "meter_edit" && isset($_GET['id'])) {
                    $id_edit_m = (int) $_GET['id'];
                    $q_edit_m  = mysqli_query($koneksi, "SELECT p.*, l.nama FROM pemakaian p LEFT JOIN login l ON l.username = p.username WHERE p.no='$id_edit_m'");
                    $edit_m    = mysqli_fetch_assoc($q_edit_m);
                    if ($edit_m) {
                        $edit_m_display = 'block';
                    }
                }
                ?>
                <div id="catat_meter_add" class="card mb-4" style="display: <?php echo $edit_m_display; ?>;">
                    <div class="card-header">
                        <i class="fas fa-tachometer-alt fa-fade me-1" style="color: rgb(99, 230, 190);"></i>
                        <?php echo $edit_m ? 'Edit Data Meter' : 'Catat Meter'; ?>
                    </div>
                    <div class="card-body">
                        <form id="meter_form" method="POST" action="index.php">
                            <!-- id_meter: langsung di-set dari PHP, bukan JS -->
                            <input type="hidden" name="id_meter" id="form_id_meter" value="<?php echo $edit_m ? (int)$edit_m['no'] : ''; ?>">
                            <input type="hidden" name="tgl" id="inp_tgl" value="<?php echo $edit_m ? htmlspecialchars($edit_m['tgl']) : date('Y-m-d'); ?>">
                            <input type="hidden" name="waktu" id="inp_waktu" value="<?php echo $edit_m ? htmlspecialchars(substr($edit_m['waktu'], 0, 5)) : date('H:i'); ?>">

                            <div class="mb-3">
                                <label class="form-label">Nama Warga:</label>
                                <?php if ($edit_m): ?>
                                    <!-- Mode edit: nama terkunci -->
                                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($edit_m['nama'] ?? $edit_m['username']); ?>" readonly>
                                    <input type="hidden" name="id_pelanggan" value="<?php echo htmlspecialchars($edit_m['username']); ?>">
                                <?php else: ?>
                                    <!-- Mode tambah: dropdown pilih warga -->
                                    <select class="form-select" name="id_pelanggan" id="sel_id_pelanggan" required>
                                        <option value="">-- Pilih Warga --</option>
                                        <?php
                                        $qwarga = mysqli_query($koneksi, "SELECT username, nama FROM login WHERE LOWER(level)='warga' ORDER BY nama ASC");
                                        while ($dw = mysqli_fetch_assoc($qwarga)) {
                                            $uname  = htmlspecialchars($dw['username']);
                                            $nwarga = htmlspecialchars($dw['nama']);
                                            echo "<option value='$uname'>$nwarga</option>";
                                        }
                                        ?>
                                    </select>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meter Awal (m³):</label>
                                <input type="number" class="form-control" name="meter_awal" id="inp_meter_awal"
                                    placeholder="0" min="0" step="0.01" required
                                    value="<?php echo $edit_m ? htmlspecialchars($edit_m['meter_awal']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meter Akhir (m³):</label>
                                <input type="number" class="form-control" name="meter_akhir" id="inp_meter_akhir"
                                    placeholder="0" min="0" step="0.01" required
                                    value="<?php echo $edit_m ? htmlspecialchars($edit_m['meter_akhir']) : ''; ?>">
                            </div>

                            <button type="submit" class="btn btn-primary" name="tombol" id="btn_meter_submit"
                                value="<?php echo $edit_m ? 'meter_edit' : 'meter_add'; ?>">
                                <?php echo $edit_m ? 'Simpan Perubahan' : 'Simpan'; ?>
                            </button>
                            <a href="index.php?p=catat_meter" class="btn btn-secondary ms-2">Batal</a>
                        </form>
                    </div>
                </div>

                <?php
                // Scroll otomatis ke form edit jika mode edit
                if ($edit_m): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var el = document.getElementById('catat_meter_add');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
                </script>
                <?php endif; ?>



                <?php
                // Mode Edit catat tarif: isi form dari DB — dihapus (fitur Catat Tarif dinonaktifkan)
                ?>


            </div>
        </main>

        <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="myModalLabel"><i class="fas fa-trash me-2"></i>Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Yakin hapus tarif <strong id="modal_id_tarif_text"></strong>? Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form method="POST" action="index.php">
                            <input type="hidden" name="id_tarif" value="">
                            <button type="submit" name="tombol" value="tarif_hapus" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="myModalMeter" tabindex="-1" aria-labelledby="myModalMeterLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="myModalMeterLabel"><i class="fas fa-trash me-2"></i>Konfirmasi Hapus Meter</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Yakin hapus data meter pelanggan <strong id="modal_id_meter_text"></strong>? Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form method="POST" action="index.php">
                            <input type="hidden" name="id_meter" value="" id="modal_id_meter_hidden">
                            <button type="submit" name="tombol" value="meter_hapus" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="myModalUser" tabindex="-1" aria-labelledby="myModalUserLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="myModalUserLabel"><i class="fas fa-trash me-2"></i>Konfirmasi Hapus User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Yakin hapus user <strong id="modal_username_text"></strong>? Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <a id="modal_user_hapus_link" href="#" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Ya, Hapus
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright &copy; Flavia & Rosita <?php echo date("Y") ?></div>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="../js/air.js"></script>
<script>
$(document).ready(function() {

    // ─── Init DataTable Tarif ────────────────────────────────────
    $('#tarif_table').DataTable({
        language: {
            search:     "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info:       "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
            infoEmpty:  "Menampilkan 0 data",
            emptyTable: "Belum ada data tarif", // Ditambahkan agar otomatis tampil jika kosong
            zeroRecords:"Tidak ada data yang cocok",
            paginate: { previous: "&laquo;", next: "&raquo;" }
        },
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [0, 5] } // kolom No & Aksi tidak bisa di-sort
        ]
    });

    // ─── Init simple-datatables Catat Meter ─────────────────────
    if (document.getElementById('meter_table')) {
        const meterDT = new simpleDatatables.DataTable('#meter_table', {
            searchable: true,
            fixedHeight: false,
            perPage: 10,
            labels: {
                placeholder: "Cari...",
                perPage: "Tampilkan {select} data per halaman",
                noRows: "Belum ada data meter",
                info: "Menampilkan {start} s/d {end} dari {rows} data",
                noResults: "Tidak ada data yang cocok"
            }
        });
    }

    // ─── Tombol Tambah Tarif toggle form ─────────────────────────
    $('#btn_tambah_tarif').click(function() {
        $('#tarif_add').slideToggle();
        $('html, body').animate({ scrollTop: $('#tarif_add').offset().top - 80 }, 400);
    });

    // ─── Modal Hapus Tarif: isi hidden input id_tarif ─────────────
    $('#myModal').on('show.bs.modal', function(event) {
        var btn    = $(event.relatedTarget);
        var id_t   = btn.data('id_tarif');
        $(this).find('input[name="id_tarif"]').val(id_t);
        $(this).find('#modal_id_tarif_text').text(id_t);
    });

    // ─── Modal Hapus Catat Meter: isi hidden input id_meter ──────
    $('#myModalMeter').on('show.bs.modal', function(event) {
        var btn    = $(event.relatedTarget);
        var id_m   = btn.data('id_meter');
        var id_p   = btn.data('id_pelanggan');
        $(this).find('#modal_id_meter_hidden').val(id_m);
        $(this).find('#modal_id_meter_text').text(id_p);
    });

    // ─── Modal Hapus User: set link href ─────────────────────────
    $('#myModalUser').on('show.bs.modal', function(event) {
        var btn      = $(event.relatedTarget);
        var username = btn.data('username');
        $(this).find('#modal_username_text').text(username);
        $(this).find('#modal_user_hapus_link').attr('href', 'index.php?p=user_hapus&user=' + encodeURIComponent(username));
    });


});
</script>
</body>
</html>