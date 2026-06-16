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

// Ambil data user dengan aman
$nama_user = isset($dt_user_row['nama']) ? $dt_user_row['nama'] : (isset($dt_user_row['username']) ? $dt_user_row['username'] : '-');
$level_db  = isset($dt_user_row['level']) ? $dt_user_row['level'] : '-';
$uname_db  = isset($dt_user_row['username']) ? $dt_user_row['username'] : '-';

$dt_user = [$nama_user, $level_db, $uname_db];

$lvl_session = isset($_SESSION['level']) ? $_SESSION['level'] : '';
$level_raw   = $lvl_session ? $lvl_session : $level_db;
$level       = strtolower(trim($level_raw));

// Routing halaman
$page = isset($_GET['p']) ? trim($_GET['p']) : '';
// Bersihkan jika ada sisa parameter lain
if (strpos($page, '&') !== false) {
    $page = explode('&', $page)[0];
}

$h1 = "Dashboard";
$li = "Dashboard";

if (!empty($page)) {
    $routes = [
        "user"                                          => ["Manajemen User",                                    "Menu untuk CRUD User"],
        "pemakaian_warga"                               => ["Manajemen Pemakaian Air Warga",                    "Manajemen Pemakaian Air Warga"],
        "ubah_datameter_warga"                          => ["Ubah Data Meter Warga",                             "Ubah Data Meter Air Warga"],
        "menghapus_datameter_air_warga"                 => ["Menghapus Data Meter Warga",                        "Menghapus Data Meter Air Warga"],
        "melihat_tagihan_seluruh_warga"                 => ["Melihat Tagihan Seluruh Warga",                     "Melihat Tagihan Seluruh Warga"],
        "melihat_infografis_tagihan_warga"              => ["Melihat Infografis Tagihan Warga",                  "Melihat Infografis Tagihan Warga"],
        "tarif"                                         => ["Manajemen Tarif Air",                               "Manajemen Tarif Air"],
        "tarif_edit"                                    => ["Manajemen Tarif Air",                               "Edit Data Tarif Air"],
        "catat_meter"                                   => ["Catat Meter",                                       "Daftar pencatatan meter air warga"],
        "meter_edit"                                    => ["Catat Meter",                                       "Edit Data Meter Air Warga"],
        "user_edit"                                     => ["Manajemen User",                                    "Edit Data User"],
        "lihat_data_pemakaian"                          => ["Lihat Data Pemakaian",                              "Daftar pencatatan meter air warga"],
        "mengubah_datameter_air_warga_dalam_satu_bulan" => ["Mengubah Datameter Air Warga Dalam Satu Bulan",     "Mengubah Datameter Air Warga Dalam Satu Bulan"],
        "melihat_jumlah_total_pelanggan"                => ["Melihat Jumlah Total Pelanggan",                    "Melihat Jumlah Total Pelanggan"],
        "melihat_jumlah_pemakaian_air_seluruh_warga"    => ["Melihat Jumlah Pemakaian Air Seluruh Warga",        "Melihat Jumlah Pemakaian Air Seluruh Warga"],
        "melihat_infografis_pemakaian_air_warga"        => ["Melihat Infografis Pemakaian Air Warga",            "Melihat Infografis Pemakaian Air Warga"],
        "memantau_air_tiap_bulan"                       => ["Memantau Air Tiap Bulan",                           "Memantau Air Tiap Bulan"],
        "melihat_tagihan_tiap_bulan"                    => ["Melihat Tagihan Tiap Bulan",                        "Melihat Tagihan Tiap Bulan"],
        "melihat_infografis_pemakaian_dan_tagihan_perbulan" => ["Melihat Infografis Pemakaian Dan Tagihan Perbulan", "Melihat Infografis Pemakaian Dan Tagihan Perbulan"],
        "pemakaian_sendiri" => ["Pemakaian & Tagihan Air", "Data Pemakaian & Tagihan Air"],
    ];

    if (isset($routes[$page])) {
        $h1 = $routes[$page][0];
        $li = $routes[$page][1];
    }

    // Override judul meter_edit sesuai konteks asal halaman
    if ($page === 'meter_edit') {
        $dari_ctx = isset($_GET['dari']) ? trim($_GET['dari']) : '';
        if ($dari_ctx === 'pemakaian_warga') {
            $h1 = "Manajemen Pemakaian Air Warga";
            $li = "Edit Data Meter Air Warga";
        } else {
            $h1 = "Catat Meter";
            $li = "Edit Data Meter Air Warga";
        }
    }
}

// HANDLER POST — USER
if (isset($_POST['aksi_user'])) {
    $t_user    = $_POST['aksi_user'];
    $user_input= mysqli_real_escape_string($koneksi, trim(isset($_POST['username']) ? $_POST['username'] : ''));
    $pass2     = isset($_POST['pwd']) ? $_POST['pwd'] : '';
    $nama_in   = mysqli_real_escape_string($koneksi, trim(isset($_POST['nama']) ? $_POST['nama'] : ''));
    $alamat_in = mysqli_real_escape_string($koneksi, trim(isset($_POST['alamat']) ? $_POST['alamat'] : ''));
    $kota_in   = mysqli_real_escape_string($koneksi, trim(isset($_POST['kota']) ? $_POST['kota'] : ''));
    $tele_in   = mysqli_real_escape_string($koneksi, trim(isset($_POST['telephone']) ? $_POST['telephone'] : ''));
    $level_in  = mysqli_real_escape_string($koneksi, trim(isset($_POST['level']) ? $_POST['level'] : ''));
    $tipe_in   = mysqli_real_escape_string($koneksi, trim(isset($_POST['tipe']) ? $_POST['tipe'] : ''));
    $status_in = mysqli_real_escape_string($koneksi, trim(isset($_POST['status']) ? $_POST['status'] : ''));

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
        $user_lama = mysqli_real_escape_string($koneksi, trim(isset($_POST['user_lama']) ? $_POST['user_lama'] : ''));
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
        $level_g = strtolower(trim(isset($dt_user_row['level']) ? $dt_user_row['level'] : (isset($_SESSION['level']) ? $_SESSION['level'] : '')));
        if ($level_g === 'petugas') {
            $tgl_g  = date_create(isset($d_guard_m['tgl']) ? $d_guard_m['tgl'] : '');
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
        
        $tgl     = date('Y-m-d');
        $waktu   = date('H:i:s');
        
        $m_awal  = mysqli_real_escape_string($koneksi, trim($_POST['meter_awal']));
        $m_akhir = mysqli_real_escape_string($koneksi, trim($_POST['meter_akhir']));
        
        $status_add = isset($_POST['status']) ? trim($_POST['status']) : 'BELUM BAYAR';
        if (!in_array($status_add, ['LUNAS', 'BELUM BAYAR'])) $status_add = 'BELUM BAYAR';
        
        $pakai   = (float)$m_akhir - (float)$m_awal;
        $dari_add = isset($_POST['dari']) ? mysqli_real_escape_string($koneksi, trim($_POST['dari'])) : '';

        // VALIDASI 1: Cek apakah data bulan ini sudah ada (1 bulan sekali)
        $bln_input = date('m', strtotime($tgl));
        $thn_input = date('Y', strtotime($tgl));
        $cek_sebulan = mysqli_query($koneksi, "SELECT no FROM pemakaian WHERE username='$id_pel' AND MONTH(tgl)='$bln_input' AND YEAR(tgl)='$thn_input'");
        
        if (mysqli_num_rows($cek_sebulan) > 0) {
            $_SESSION['notif'] = ['type' => 'warning', 'msg' => "Data warga ini sudah dicatat pada bulan tersebut. Pencatatan hanya bisa dilakukan 1x dalam sebulan (30 hari)."];
            header("Location: index.php?p=" . ($dari_add ? $dari_add : 'catat_meter'));
            exit;
        }

        // Validasi 2: meter akhir harus lebih besar dari meter awal
        if ((float)$m_akhir <= (float)$m_awal) {
            $_SESSION['notif'] = ['type' => 'warning', 'msg' => "Meter akhir harus lebih besar dari meter awal."];
            header("Location: index.php?p=" . ($dari_add ? $dari_add : 'catat_meter'));
            exit;
        }

        // Ambil tarif aktif pelanggan
        $q_tarif = mysqli_query($koneksi, "SELECT t.tarif, t.id_tarif FROM tarif t 
                                            JOIN login l ON LOWER(l.tipe) = LOWER(t.tipe) 
                                            WHERE l.username = '$id_pel' AND LOWER(t.status)='aktif' LIMIT 1");
        $d_tarif = mysqli_fetch_assoc($q_tarif);
        // Fallback: ambil tarif pertama yang aktif jika tidak ketemu by tipe
        if (!$d_tarif) {
            $q_tarif2 = mysqli_query($koneksi, "SELECT tarif, id_tarif FROM tarif WHERE LOWER(status)='aktif' LIMIT 1");
            $d_tarif  = mysqli_fetch_assoc($q_tarif2);
        }
        
        $kd_tarif = isset($d_tarif['id_tarif']) ? $d_tarif['id_tarif'] : '';
        $tarif_val = isset($d_tarif['tarif']) ? $d_tarif['tarif'] : 0;
        $tagihan  = (float)$pakai * (float)$tarif_val;

        mysqli_query($koneksi, "INSERT INTO pemakaian (username, meter_awal, meter_akhir, pemakaian, tgl, waktu, kd_tarif, tagihan, status) 
                                VALUES ('$id_pel', '$m_awal', '$m_akhir', '$pakai', '$tgl', '$waktu', '$kd_tarif', '$tagihan', '$status_add')");
        if (mysqli_affected_rows($koneksi) > 0) {
            $_SESSION['notif'] = ['type' => 'success', 'msg' => "Data meter berhasil disimpan."];
        } else {
            $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal menyimpan data: " . mysqli_error($koneksi)];
        }
        
        header("Location: index.php?p=" . ($dari_add ? $dari_add : 'catat_meter'));
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
        $pakai   = (float)$m_akhir - (float)$m_awal;
        
        $status  = isset($_POST['status']) ? trim($_POST['status']) : 'BELUM BAYAR';
        $dari    = isset($_POST['dari']) ? trim($_POST['dari']) : '';

        // Validasi: meter akhir harus lebih besar dari meter awal
        if ((float)$m_akhir <= (float)$m_awal) {
            $_SESSION['notif'] = ['type' => 'warning', 'msg' => "Meter akhir harus lebih besar dari meter awal."];
            header("Location: index.php?p=" . ($dari ? $dari : 'catat_meter'));
            exit;
        }

        $q_tarif = mysqli_query($koneksi, "SELECT t.tarif, t.id_tarif FROM tarif t 
                                            JOIN login l ON LOWER(l.tipe) = LOWER(t.tipe) 
                                            WHERE l.username = '$id_pel' AND LOWER(t.status)='aktif' LIMIT 1");
        $d_tarif = mysqli_fetch_assoc($q_tarif);
        // Fallback: ambil tarif pertama yang aktif jika tidak ketemu by tipe
        if (!$d_tarif) {
            $q_tarif2 = mysqli_query($koneksi, "SELECT tarif, id_tarif FROM tarif WHERE LOWER(status)='aktif' LIMIT 1");
            $d_tarif  = mysqli_fetch_assoc($q_tarif2);
        }
        
        $kd_tarif = isset($d_tarif['id_tarif']) ? $d_tarif['id_tarif'] : '';
        $tarif_val = isset($d_tarif['tarif']) ? $d_tarif['tarif'] : 0;
        $tagihan  = (float)$pakai * (float)$tarif_val;

        mysqli_query($koneksi, "UPDATE pemakaian SET username='$id_pel', meter_awal='$m_awal', meter_akhir='$m_akhir', 
                                pemakaian='$pakai', tgl='$tgl', waktu='$waktu', kd_tarif='$kd_tarif', tagihan='$tagihan', status='$status' 
                                WHERE no='$id_rec'");
        if (mysqli_affected_rows($koneksi) >= 0) {
            $_SESSION['notif'] = ['type' => 'success', 'msg' => "Data meter berhasil diperbarui."];
        } else {
            $_SESSION['notif'] = ['type' => 'danger', 'msg' => "Gagal memperbarui: " . mysqli_error($koneksi)];
        }
        // PERBAIKAN DI SINI: Mengembalikan user ke halaman asal ($dari) agar tampilan tidak ikut berubah/rusak
        header("Location: index.php?p=" . ($dari ? $dari : 'catat_meter'));
        exit;
    }

    // --- HAPUS CATAT METER ---
    if ($aksi == 'meter_hapus') {
        $id_rec = mysqli_real_escape_string($koneksi, trim($_POST['id_meter']));
        mysqli_query($koneksi, "DELETE FROM pemakaian WHERE no='$id_rec'");
        if (mysqli_affected_rows($koneksi) > 0) {
            $_SESSION['notif'] = ['type' => 'success', 'msg' => "Data meter berhasil deleted."];
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
            #tarif_table_wrapper .dataTables_filter input { border-radius: 8px; border: 1px solid #4e73df; padding: 5px 12px; }
            #tarif_table_wrapper .dataTables_filter input:focus { outline: none; box-shadow: 0 0 0 0.15rem rgba(78,115,223,.25); border-color: #4e73df; }
            #tarif_table_wrapper .dataTables_length select { border-radius: 8px; border: 1px solid #4e73df; padding: 4px 8px; }
            #tarif_table_wrapper .dataTables_paginate .paginate_button.current,
            #tarif_table_wrapper .dataTables_paginate .paginate_button.current:hover { background: #4e73df !important; border-color: #4e73df !important; color: #fff !important; border-radius: 6px; }
            #tarif_table_wrapper .dataTables_paginate .paginate_button:hover { background: #eaecf4 !important; border-color: #4e73df !important; color: #4e73df !important; border-radius: 6px; }
            
            #meter_table_wrapper .dataTables_filter input { border-radius: 8px; border: 1px solid #4e73df; padding: 5px 12px; }
            #meter_table_wrapper .dataTables_filter input:focus { outline: none; box-shadow: 0 0 0 0.15rem rgba(78,115,223,.25); border-color: #4e73df; }
            #meter_table_wrapper .dataTables_length select { border-radius: 8px; border: 1px solid #4e73df; padding: 4px 8px; }
            #meter_table_wrapper .dataTables_paginate .paginate_button.current,
            #meter_table_wrapper .dataTables_paginate .paginate_button.current:hover { background: #4e73df !important; border-color: #4e73df !important; color: #fff !important; border-radius: 6px; }
            #meter_table_wrapper .dataTables_paginate .paginate_button:hover { background: #eaecf4 !important; border-color: #4e73df !important; color: #4e73df !important; border-radius: 6px; }

            .sb-sidenav-dark .sb-sidenav-menu .nav-link,
            .sb-sidenav-dark .sb-sidenav-menu .nav-link .sb-nav-link-icon,
            .sb-sidenav-dark .sb-sidenav-menu .nav-link .sb-nav-link-icon i {
                color: rgba(255, 255, 255, 0.4) !important;
                transition: color 0.15s ease-in-out;
            }
            
            .sb-sidenav-dark .sb-sidenav-menu .nav-link.active,
            .sb-sidenav-dark .sb-sidenav-menu .nav-link:hover {
                color: #ffffff !important;
                font-weight: 600;
            }
            .sb-sidenav-dark .sb-sidenav-menu .nav-link.active .sb-nav-link-icon,
            .sb-sidenav-dark .sb-sidenav-menu .nav-link.active .sb-nav-link-icon i,
            .sb-sidenav-dark .sb-sidenav-menu .nav-link:hover .sb-nav-link-icon,
            .sb-sidenav-dark .sb-sidenav-menu .nav-link:hover .sb-nav-link-icon i {
                color: #4e73df !important;
                opacity: 1 !important;
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

                    <a class="nav-link <?php echo ($page == '' || $page == 'dashboard') ? 'active' : ''; ?>" href="index.php">
                        <div class="sb-nav-link-icon">
                            <i class="fas fa-tachometer-alt fa-spin"></i>
                        </div>
                        Dashboard
                    </a>

                    <?php if ($level == "admin") : ?>
                        <a class="nav-link <?php echo ($page == 'user' || $page == 'user_edit') ? 'active' : ''; ?>" href="index.php?p=user">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Manajemen User
                        </a>
                        <a class="nav-link <?php echo ($page == 'tarif' || $page == 'tarif_edit') ? 'active' : ''; ?>" href="index.php?p=tarif">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                            Manajemen Tarif
                        </a>
                        <a class="nav-link <?php echo ($page == 'pemakaian_warga') ? 'active' : ''; ?>" href="index.php?p=pemakaian_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-tint"></i></div>
                            Lihat Pemakaian Warga
                        </a>

                    <?php elseif ($level === "bendahara") : ?>
                        <a class="nav-link <?php echo ($page == 'tarif' || $page == 'tarif_edit') ? 'active' : ''; ?>" href="index.php?p=tarif">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                            Manajemen Tarif
                        </a>
                        <a class="nav-link <?php echo ($page == 'pemakaian_warga') ? 'active' : ''; ?>" href="index.php?p=pemakaian_warga">
                            <div class="sb-nav-link-icon"><i class="fas fa-tint"></i></div>
                            Lihat Pemakaian Warga
                        </a>

                    <?php elseif ($level === "petugas") : ?>
                        <a class="nav-link <?php echo ($page == 'catat_meter' || $page == 'meter_edit') ? 'active' : ''; ?>" href="index.php?p=catat_meter">
                            <div class="sb-nav-link-icon"><i class="fas fa-plus-circle"></i></div>
                            Catat Meter
                        </a>
                    <?php elseif ($level === "warga") : ?>
                        <a class="nav-link <?php echo ($page == 'pemakaian_sendiri') ? 'active' : ''; ?>" href="index.php?p=pemakaian_sendiri">
                            <div class="sb-nav-link-icon"><i class="fas fa-tint"></i></div>
                            Lihat Pemakaian
                        </a>
                    <?php endif; ?>

                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">
                    <i class="fa-regular fa-user fa-flip" style="color:#4e73df"></i>
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

                <?php 
                $hide_summary = in_array($page, ['pemakaian_warga','pemakaian_sendiri']) ? 'style="display:none!important;"' : ''; 
                ?>
                
                <?php if ($page == '' || $page == 'dashboard'): ?>
                    
                    <?php 
                    $b1_txt = "Pelanggan"; $b1_unit = "orang";
                    $b2_txt = "Pemakaian Air"; $b2_unit = "m<sup>3</sup>";
                    $b3_txt = "Sudah Dicatat"; $b3_unit = "warga";
                    $b4_txt = "Belum Dicatat"; $b4_unit = "warga";

                    if ($level == 'bendahara') {
                        $b2_txt = "Pemasukan";     $b2_unit = "Rp";
                        $b3_txt = "Sudah Lunas"; 
                        $b4_txt = "Belum Bayar";
                    } elseif ($level == 'warga') {
                        $b1_txt = "Waktu Pencatatan"; $b1_unit = "-"; 
                        $b3_txt = "Tagihan";          $b3_unit = "Rp";
                        $b4_txt = "Status Tagihan";   $b4_unit = "";
                    }
                    ?>

                    <form id="form_filter_waktu" method="GET" action="index.php">
                        <div class="row mb-3" id="pilih_waktu">
                            <div class="col-xl-3 col-md-12">
                                <label for="sel1" class="form-label">Pilih Waktu:</label>
                                <select class="form-select" id="sel1" name="waktu">
                                    <option value="" selected>Bulan</option>
                                    <?php 
                                    $waktu_terpilih = isset($_GET['waktu']) ? $_GET['waktu'] : '';
                                    $tahun_sekarang = date("Y"); 
                                    
                                    for ($i = 1; $i <= 12; $i++) {
                                        $bln_angka = str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $val_waktu = "2026-" . $bln_angka;
                                        $label_waktu = $air->bln($bln_angka) . " 2026";
                                        $is_sel = ($waktu_terpilih == $val_waktu) ? 'selected' : '';
                                        echo "<option value='$val_waktu' $is_sel>$label_waktu</option>\n";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </form>

                    <div class="row" id="summary">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body d-flex justify-content-center align-items-center">
                                    <h1>0</h1>
                                    <div class="ms-3"><?php echo $b1_unit; ?></div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-center">
                                    <div class="small text-white"><?php echo $b1_txt; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-warning text-white mb-4">
                               <div class="card-body d-flex justify-content-center align-items-center">
                                    <h1>0</h1>
                                    <div class="ms-3"><?php echo $b2_unit; ?></div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-center">
                                    <div class="small text-white"><?php echo $b2_txt; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body d-flex justify-content-center align-items-center">
                                     <h1>0</h1>
                                    <div class="ms-3"><?php echo $b3_unit; ?></div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-center">
                                    <div class="small text-white"><?php echo $b3_txt; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-danger text-white mb-4">
                                <div class="card-body d-flex justify-content-center align-items-center">
                                    <h1>0</h1>
                                    <div class="ms-3"><?php echo $b4_unit; ?></div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-center">
                                    <div class="small text-white"><?php echo $b4_txt; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="chart">
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fa-solid fa-users"></i> Grafik Pemakaian Air (m<sup>3</sup>)
                                </div>
                                <div class="card-body">
                                    <canvas id="myAreaChart" width="100%" height="40"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-chart-bar me-1"></i> Grafik Tagihan (Rp)
                                </div>
                                <div class="card-body">
                                    <canvas id="myBarChart" width="100%" height="40"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
                <?php
                // Form User Form handling...
                $user = $pass2 = $nama = $alamat = $kota = $telephone = '';
                $level_form = $tipe_form = $status_form = '';
                $mode = "user_add"; 
                $txt_tombol = "Simpan";
                $display_form = in_array($page, ['user', 'user_edit']) ? "block" : "none";

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
                ?>
                <div id="user_add" class="card mb-4 shadow-sm" style="display: <?php echo $display_form; ?>;">
                    <div class="card-header d-flex align-items-center justify-content-between py-3" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-bottom: 2px solid #4e73df;">
                        <div class="d-flex align-items-center gap-2">
                            <?php if($mode == 'user_edit'): ?>
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:#4e73df;">
                                    <i class="fa-solid fa-user-pen text-white fa-sm"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:1rem;">Edit User</div>
                                    <div class="text-muted" style="font-size:0.8rem;">Mengubah data: <strong><?php echo htmlspecialchars($user); ?></strong></div>
                                </div>
                            <?php else: ?>
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:#4e73df;">
                                    <i class="fa-solid fa-user-plus text-white fa-sm"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:1rem;">Tambah User Baru</div>
                                    <div class="text-muted" style="font-size:0.8rem;">Isi semua data yang diperlukan</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-4">

                        <form method="post" action="index.php" class="needs-validation" id="user_form">
                            <input type="hidden" name="aksi_user" value="<?php echo $mode; ?>">
                            <?php if($mode == 'user_edit'): ?>
                            <input type="hidden" name="user_lama" value="<?php echo htmlspecialchars($user); ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Username:</label>
                                <?php if($mode == 'user_edit'): ?>
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
                                           value="<?php echo htmlspecialchars($user); ?>" required>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="pwd" class="form-label fw-semibold">Password:</label>
                                <input type="password" class="form-control" id="pwd" name="pwd"
                                       placeholder="<?php echo ($mode == 'user_edit') ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan password'; ?>"
                                       <?php if($mode != 'user_edit') echo 'required'; ?>>
                                <?php if($mode == 'user_edit'): ?>
                                    <div class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password.</div>
                                <?php endif; ?>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="nama" class="form-label fw-semibold">Nama:</label>
                                    <input type="text" class="form-control" id="nama"
                                           placeholder="Masukkan nama lengkap" name="nama"
                                           value="<?php echo htmlspecialchars($nama); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label fw-semibold">Telephone:</label>
                                    <input type="text" class="form-control" id="telephone"
                                           placeholder="Masukkan nomor telepon" name="telephone"
                                           value="<?php echo htmlspecialchars($telephone); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label fw-semibold">Alamat:</label>
                                <textarea class="form-control" rows="3" id="alamat" name="alamat"
                                          placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($alamat); ?></textarea>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="kota" class="form-label fw-semibold">Kota:</label>
                                    <input type="text" class="form-control" id="kota"
                                           placeholder="Masukkan kota" name="kota"
                                           value="<?php echo htmlspecialchars($kota); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="level" class="form-label fw-semibold">Level:</label>
                                    <select class="form-select" name="level" required>
                                        <option value="">-- Pilih Level --</option>
                                        <?php
                                        $lv = ["admin", "bendahara", "petugas", "warga"];
                                        foreach ($lv as $lv2) {
                                            $sel = ($level_form == $lv2) ? "selected" : "";
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
                                        $tp = ["RT", "kos"];
                                        foreach ($tp as $t2) {
                                            $sel = ($tipe_form == $t2) ? "selected" : "";
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
                                        $st = ["AKTIF", "TIDAK AKTIF"];
                                        foreach ($st as $s2) {
                                            $sel = ($status_form == $s2) ? "selected" : "";
                                            echo "<option value='$s2' $sel>$s2</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                <?php $btn_color = ($mode == 'user_edit') ? 'btn-warning' : 'btn-primary'; ?>
                                <?php $icon_btn = ($mode == 'user_edit') ? 'fa-save' : 'fa-plus'; ?>
                                <button type="submit" class="btn <?php echo $btn_color; ?> px-4">
                                    <i class="fas <?php echo $icon_btn; ?> me-1"></i> <?php echo $txt_tombol; ?>
                                </button>
                                <a href="index.php?p=user" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-1"></i> Batal
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
                
                <?php $show_user_list = in_array($page, ['user','user_edit']) ? 'block' : 'none'; ?>
                <div class="card mb-4" id="user_list" style="display:<?php echo $show_user_list; ?>;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-users me-2 text-primary fa-fade"></i> Data User</span>
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
                                    $user_val  = htmlspecialchars($d['username']);
                                    $nama_val  = htmlspecialchars($d['nama']);
                                    $alamat_val= htmlspecialchars($d['alamat'] ? $d['alamat'] : '-');
                                    $kota_val  = htmlspecialchars($d['kota'] ? $d['kota'] : '-');
                                    $tele_val  = htmlspecialchars($d['telephone'] ? $d['telephone'] : '-');
                                    $level_val = htmlspecialchars($d['level'] ? $d['level'] : '-');
                                    $tipe_val  = htmlspecialchars($d['tipe'] ? $d['tipe'] : '-');
                                    $status_raw= $d['status'];

                                    if ($status_raw === 'AKTIF') {
                                        $badge_status = "<span class='badge bg-success px-2 py-1'>AKTIF</span>";
                                    } elseif (!empty($status_raw)) {
                                        $badge_status = "<span class='badge bg-secondary px-2 py-1'>" . htmlspecialchars($status_raw) . "</span>";
                                    } else {
                                        $badge_status = "<span class='text-muted'>-</span>";
                                    }

                                    echo "<tr>
                                        <td class='text-center text-muted'>$no</td>
                                        <td><span class='fw-semibold text-dark'>$user_val</span></td>
                                        <td>$nama_val</td>
                                        <td>$alamat_val</td>
                                        <td>$kota_val</td>
                                        <td>$tele_val</td>
                                        <td><span class='badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2'>$level_val</span></td>
                                        <td class='text-center'>$tipe_val</td>
                                        <td class='text-center'>$badge_status</td>
                                        <td class='text-center' style='white-space:nowrap;'>
                                            <a href='index.php?p=user_edit&user=$user_val' class='btn btn-primary btn-sm' title='Edit'>
                                                <i class='fas fa-edit'></i>
                                            </a>
                                            <button type='button' class='btn btn-danger btn-sm' title='Hapus'
                                                data-bs-toggle='modal' data-bs-target='#myModalUser'
                                                data-username='$user_val'>
                                                <i class='fas fa-trash'></i>
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

                <?php $show_tarif_list = in_array($page, ['tarif', 'tarif_edit']) ? 'block' : 'none'; ?>
                <div class="card mb-4 shadow-sm" id="tarif_list" style="display:<?php echo $show_tarif_list; ?>; border-top: 4px solid #4e73df; border-radius: 0.5rem;">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 100%);">
                        <div class="d-flex align-items-center gap-2">
                            <div style="background:#4e73df;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-tags text-white"></i>
                            </div>
                            <div>
                                <h6 class="m-0 fw-bold text-primary">Data Tarif Air</h6>
                                <small class="text-muted">Daftar seluruh tarif yang tersedia</small>
                            </div>
                        </div>
                        <button type="button" id="btn_tambah_tarif" class="btn btn-primary d-flex align-items-center gap-2" style="border-radius:8px; padding:8px 18px; font-weight:600;">
                            <i class="fas fa-plus"></i> Tambah Tarif
                        </button>
                    </div>
                    <div class="card-body">
                        <table id="tarif_table" class="table table-hover align-middle w-100" style="font-size:0.95rem;">
                            <thead style="background:#eef2ff;">
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
                                            <td class='fw-semibold text-primary'>Rp " . number_format($harga, 0, ',', '.') . "</td>
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
                $edit_t = null;
                $tarif_add_display = 'none';
                if ($page == "tarif_edit" && isset($_GET['id'])) {
                    $id_edit  = mysqli_real_escape_string($koneksi, $_GET['id']);
                    $q_edit_t = mysqli_query($koneksi, "SELECT * FROM tarif WHERE id_tarif='$id_edit'");
                    $edit_t   = mysqli_fetch_array($q_edit_t);
                    if ($edit_t) $tarif_add_display = 'block';
                }
                ?>
                <div id="tarif_add" class="card mb-4" style="display: <?php echo $tarif_add_display; ?>; border-top: 4px solid #4e73df; box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,0.15);">
                    <div class="card-header bg-white py-3">
                        <h5 class="m-0 fw-bold text-primary">
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
                                            <?php $is_sel = ($tipe_val == $opt) ? 'selected' : ''; ?>
                                            <option value="<?php echo $opt; ?>" <?php echo $is_sel; ?>>
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
                                        <?php $tarif_val = $edit_t ? htmlspecialchars($edit_t['tarif']) : ''; ?>
                                        <input type="number" class="form-control" name="tarif" min="0" required
                                            value="<?php echo $tarif_val; ?>"
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
                                            <label class="form-check-label fw-bold text-primary fs-6" for="st_aktif">
                                                <i class="fas fa-check-circle me-1"></i> if aktif
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
                                <?php $tmb_val = $edit_t ? 'tarif_edit' : 'tarif_add'; ?>
                                <?php $tmb_txt = $edit_t ? 'Update Data' : 'Simpan Data'; ?>
                                <button type="submit" class="btn btn-primary btn-lg px-5" name="tombol" value="<?php echo $tmb_val; ?>">
                                    <i class="fas fa-save me-1"></i> <?php echo $tmb_txt; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php $show_meter_list = in_array($page, ['catat_meter', 'meter_edit', 'lihat_data_pemakaian']) ? 'block' : 'none'; ?>
                <div class="card mb-4 shadow-sm" id="catat_meter_list" style="display:<?php echo $show_meter_list; ?>; border-top: 4px solid #4e73df; border-radius: 0.5rem;">
                    <?php
                    $is_admin_cm = ($level === 'admin');
                    ?>
                    <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8f9ff 0%, #eaecf4 100%);">
                        <div class="d-flex align-items-center gap-2">
                            <div style="background:#4e73df;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-tachometer-alt text-white"></i>
                            </div>
                            <div>
                                <h6 class="m-0 fw-bold text-primary">Data Meter Warga</h6>
                                <small class="text-muted">Pencatatan Meter Air Warga</small>
                            </div>
                        </div>
                        <?php if ($page !== 'lihat_data_pemakaian' && $level !== 'admin'): ?>
                        <button type="button" id="btn_tambah_meter" class="btn btn-primary d-flex align-items-center gap-2" style="border-radius:8px; padding:8px 18px; font-weight:600;">
                            <i class="fas fa-plus"></i> Meter
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <table id="meter_table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Warga</th>
                                    <?php if ($is_admin_cm): ?><th>Tipe</th><?php endif; ?>
                                    <th>Tanggal &amp; Waktu</th>
                                    <th>Meter Awal (m³)</th>
                                    <th>Meter Akhir (m³)</th>
                                    <th>Pemakaian (m³)</th>
                                    <?php if ($is_admin_cm): ?>
                                    <th>Tagihan (Rp)</th>
                                    <th>Status</th>
                                    <?php endif; ?>
                                    <?php if ($page !== 'lihat_data_pemakaian'): ?>
                                    <th>Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no_m = 1;
                                $qm = mysqli_query($koneksi, "
                                    SELECT p.*, l.nama, l.tipe
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
                                    $nama_p  = htmlspecialchars(isset($dm['nama']) ? $dm['nama'] : $dm['username']);
                                    $tipe_p  = htmlspecialchars(isset($dm['tipe']) ? $dm['tipe'] : '-');
                                    $m_awal  = $dm['meter_awal'];
                                    $m_akhir = $dm['meter_akhir'];
                                    $pakai   = $dm['pemakaian'];
                                    $tagihan_cm = number_format(isset($dm['tagihan']) ? $dm['tagihan'] : 0, 0, ',', '');
                                    $status_cm  = isset($dm['status']) ? $dm['status'] : 'BELUM BAYAR';
                                    $badge_cm   = ($status_cm === 'LUNAS') ? 'success' : 'danger';

                                    $tgl_fmt = $dm['tgl'] ? date('d-m-Y', strtotime($dm['tgl'])) : '-';
                                    $waktu_fmt = $dm['waktu'] ? $dm['waktu'] : '-';

                                    $tgl_tabel    = date_create($dm['tgl']);
                                    $tgl_sekarang = date_create();
                                    $diff         = date_diff($tgl_tabel, $tgl_sekarang);
                                    $selisih      = $diff ? $diff->days : 0;

                                    $tgl_waktu = "$tgl_fmt $waktu_fmt | " . date("Y-m-d") . " $selisih hari";

                                    echo "<tr>
                                        <td>$no_m</td>
                                        <td>$nama_p</td>";
                                    if ($is_admin_cm) {
                                        $str_tipe = strtoupper($tipe_p);
                                        echo "<td>$str_tipe</td>";
                                    }
                                    echo "  <td>$tgl_waktu</td>
                                        <td>$m_awal</td>
                                        <td>$m_akhir</td>
                                        <td>$pakai</td>";
                                    if ($is_admin_cm) {
                                        $st_txt = ($status_cm === 'LUNAS') ? 'LUNAS' : 'BLM LUNAS';
                                        echo "<td>$tagihan_cm</td>
                                              <td><span class='badge bg-$badge_cm'>$st_txt</span></td>";
                                    }

                                    if ($page !== 'lihat_data_pemakaian') {
                                        echo "<td>";
                                        if ($is_admin_cm || $level == 'bendahara' || $selisih <= 30) {
                                            echo "
                                                <a href='index.php?p=meter_edit&id=$id_r'><button type='button' class='btn btn-outline-warning btn-sm'>Ubah</button></a>
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
                
                $is_pw_context = (isset($_GET['dari']) && $_GET['dari'] === 'pemakaian_warga') || ($page === 'pemakaian_warga');
                $back_url = $is_pw_context ? 'index.php?p=pemakaian_warga' : 'index.php?p=catat_meter';
                $show_status_field = ($level === 'admin' || $level === 'bendahara');
                ?>
                <div id="catat_meter_add" class="card mb-4 shadow-sm" style="display: <?php echo $edit_m_display; ?>; border-top: 4px solid #4e73df; border-radius: 0.5rem;">
                    <div class="card-header py-3 d-flex align-items-center gap-2" style="background: linear-gradient(135deg, #f8f9ff 0%, #eaecf4 100%);">
                        <div style="background:#4e73df;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <?php $icn_m = $edit_m ? 'edit' : 'plus'; ?>
                            <i class="fas fa-<?php echo $icn_m; ?> text-white" style="font-size:0.85rem;"></i>
                        </div>
                        <div>
                            <?php $ttl_m = $edit_m ? 'Edit Data Meter' : 'Catat Meter'; ?>
                            <?php $sub_m = $edit_m ? 'Perbarui data pencatatan meter air' : 'Tambah pencatatan meter air warga'; ?>
                            <h6 class="m-0 fw-bold text-primary"><?php echo $ttl_m; ?></h6>
                            <small class="text-muted"><?php echo $sub_m; ?></small>
                        </div>
                    </div>
                    <div class="card-body px-4 py-4">
                        <form id="meter_form" method="POST" action="index.php">
                            <?php $id_m_val = $edit_m ? (int)$edit_m['no'] : ''; ?>
                            <?php $tgl_m_val = $edit_m ? htmlspecialchars($edit_m['tgl']) : date('Y-m-d'); ?>
                            <?php $wkt_m_val = $edit_m ? htmlspecialchars(substr($edit_m['waktu'], 0, 5)) : date('H:i'); ?>
                            
                            <input type="hidden" name="id_meter" id="form_id_meter" value="<?php echo $id_m_val; ?>">
                            <input type="hidden" name="tgl" id="inp_tgl" value="<?php echo $tgl_m_val; ?>">
                            <input type="hidden" name="waktu" id="inp_waktu" value="<?php echo $wkt_m_val; ?>">

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark">Nama Warga</label>
                                <?php if ($edit_m): ?>
                                    <?php $nm_m_val = htmlspecialchars(isset($edit_m['nama']) ? $edit_m['nama'] : $edit_m['username']); ?>
                                    <?php $un_m_val = htmlspecialchars($edit_m['username']); ?>
                                    <input type="text" class="form-control bg-light text-muted" value="<?php echo $nm_m_val; ?>" readonly>
                                    <input type="hidden" name="id_pelanggan" value="<?php echo $un_m_val; ?>">
                                <?php else: ?>
                                    <select class="form-select" name="id_pelanggan" id="sel_id_pelanggan" required>
                                        <option value="" data-meter="">- Pilih Warga -</option>
                                        <?php
                                        $qwarga = mysqli_query($koneksi, "
                                            SELECT l.username, l.nama, 
                                                   (SELECT meter_akhir FROM pemakaian WHERE username = l.username ORDER BY tgl DESC, waktu DESC LIMIT 1) AS last_meter
                                            FROM login l 
                                            WHERE LOWER(l.level)='warga' 
                                            ORDER BY l.nama ASC
                                        ");
                                        while ($dw = mysqli_fetch_assoc($qwarga)) {
                                            $uname  = htmlspecialchars($dw['username']);
                                            $nwarga = htmlspecialchars($dw['nama']);
                                            $last_m = isset($dw['last_meter']) ? floatval($dw['last_meter']) : 0;
                                            echo "<option value='$uname' data-meter='$last_m'>$nwarga</option>";
                                        }
                                        ?>
                                    </select>
                                <?php endif; ?>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Meter Awal (m³)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i class="fas fa-tint"></i></span>
                                        <?php $ma_val = $edit_m ? htmlspecialchars($edit_m['meter_awal']) : '0'; ?>
                                        <input type="number" class="form-control bg-light text-muted fw-bold" name="meter_awal" id="inp_meter_awal"
                                            placeholder="0" min="0" step="0.01" required readonly tabindex="-1"
                                            value="<?php echo $ma_val; ?>">
                                        <span class="input-group-text bg-light text-muted">m³</span>
                                    </div>
                                    <div class="form-text text-muted mt-1" style="font-size:0.75rem;">Otomatis terisi & terkunci dari bulan lalu.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Meter Akhir (m³)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted"><i class="fas fa-tint-slash"></i></span>
                                        <?php $mb_val = $edit_m ? htmlspecialchars($edit_m['meter_akhir']) : ''; ?>
                                        <input type="number" class="form-control" name="meter_akhir" id="inp_meter_akhir"
                                            placeholder="0" min="0" step="0.01" required
                                            value="<?php echo $mb_val; ?>">
                                        <span class="input-group-text bg-white text-muted">m³</span>
                                    </div>
                                </div>
                            </div>

                            <?php if ($show_status_field): ?>
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark">Status Tagihan</label>
                                <?php
                                $cur_status = $edit_m ? (isset($edit_m['status']) ? $edit_m['status'] : 'BELUM BAYAR') : 'BELUM BAYAR';
                                ?>
                                <select class="form-select" name="status" id="inp_status">
                                    <?php $sel_b = ($cur_status !== 'LUNAS') ? 'selected' : ''; ?>
                                    <?php $sel_l = ($cur_status === 'LUNAS') ? 'selected' : ''; ?>
                                    <option value="BELUM BAYAR" <?php echo $sel_b; ?>>Belum Lunas</option>
                                    <option value="LUNAS" <?php echo $sel_l; ?>>Lunas</option>
                                </select>
                            </div>
                            <?php else: ?>
                                <?php if ($edit_m): ?>
                                    <?php $st_hid = htmlspecialchars(isset($edit_m['status']) ? $edit_m['status'] : 'BELUM BAYAR'); ?>
                                    <input type="hidden" name="status" value="<?php echo $st_hid; ?>">
                                <?php else: ?>
                                    <input type="hidden" name="status" value="BELUM BAYAR">
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php $dr_val = isset($_GET['dari']) ? htmlspecialchars($_GET['dari']) : ($is_pw_context ? 'pemakaian_warga' : ''); ?>
                            <input type="hidden" name="dari" value="<?php echo $dr_val; ?>">

                            <hr class="my-4">
                            <div class="d-flex gap-2 justify-content-start">
                                <?php $btn_v = $edit_m ? 'meter_edit' : 'meter_add'; ?>
                                <?php $btn_t = $edit_m ? 'Simpan Perubahan' : 'Simpan'; ?>
                                <button type="submit" class="btn btn-primary px-4" name="tombol" id="btn_meter_submit" value="<?php echo $btn_v; ?>">
                                    <i class="fas fa-save me-1"></i>
                                    <?php echo $btn_t; ?>
                                </button>
                                <a href="<?php echo $back_url; ?>" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-1"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($show_pw_list = ($page === 'pemakaian_warga' ? 'block' : 'none')): ?>
                <div class="card mb-4 shadow-sm" id="pemakaian_warga_list" style="display:<?php echo $show_pw_list; ?>; border-top: 4px solid #4e73df; border-radius: 0.5rem;">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8f9ff 0%, #eaecf4 100%);">
                        <div class="d-flex align-items-center gap-2">
                            <div style="background:#4e73df;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-tachometer-alt text-white"></i>
                            </div>
                            <div>
                                <h6 class="m-0 fw-bold text-primary">Pencatatan Meter</h6>
                                <small class="text-muted">Pencatatan Meter Air Warga</small>
                            </div>
                        </div>
                        <?php if ($level === 'admin'): ?>
                        <button type="button" id="btn_tambah_meter_pw" class="btn btn-primary d-flex align-items-center gap-2" style="border-radius:8px; padding:8px 18px; font-weight:600;">
                            <i class="fas fa-plus"></i> Meter
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <table id="pemakaian_warga_table" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Warga</th>
                                    <th>Tipe</th>
                                    <th>Tanggal &amp; Waktu</th>
                                    <th>Meter Awal (m³)</th>
                                    <th>Meter Akhir (m³)</th>
                                    <th>Pemakaian (m³)</th>
                                    <th>Tagihan (Rp)</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($page === 'pemakaian_warga'):
                                    $qpw = mysqli_query($koneksi, "
                                        SELECT p.*, l.nama, l.tipe
                                        FROM pemakaian p
                                        LEFT JOIN login l ON p.username = l.username
                                        ORDER BY p.tgl DESC, p.username ASC
                                    ");
                                    while ($dpw = mysqli_fetch_assoc($qpw)):
                                        $nama_pw  = htmlspecialchars(isset($dpw['nama']) ? $dpw['nama'] : $dpw['username']);
                                        $tipe_pw  = htmlspecialchars(isset($dpw['tipe']) ? $dpw['tipe'] : '-');
                                        
                                        $tgl_pw   = $dpw['tgl'] ? date('d-m-Y', strtotime($dpw['tgl'])) : '-';
                                        $waktu_pw = $dpw['waktu'] ? $dpw['waktu'] : '-';
                                        
                                        $tgl_now2 = date_create($dpw['tgl']);
                                        $diff2    = $tgl_now2 ? date_diff($tgl_now2, date_create()) : null;
                                        $hari_pw  = $diff2 ? $diff2->days : 0;
                                        
                                        $tglwaktu_pw = "$tgl_pw $waktu_pw | " . date("Y-m-d") . " $hari_pw hari";
                                        $tagihan_pw  = number_format(isset($dpw['tagihan']) ? $dpw['tagihan'] : 0, 0, ',', '');
                                        
                                        $status_pw   = isset($dpw['status']) ? $dpw['status'] : 'BELUM BAYAR';
                                        $badge_pw    = ($status_pw === 'LUNAS') ? 'success' : 'danger';
                                        $status_tx_pw = ($status_pw === 'LUNAS') ? 'LUNAS' : 'BLM LUNAS';

                                        $id_pw       = (int)$dpw['no'];
                                        $id_user_pw  = htmlspecialchars($dpw['username']);
                                ?>
                                <tr>
                                    <td><?php echo $nama_pw; ?></td>
                                    <td><?php echo strtoupper($tipe_pw); ?></td>
                                    <td><?php echo $tglwaktu_pw; ?></td>
                                    <td><?php echo $dpw['meter_awal']; ?></td>
                                    <td><?php echo $dpw['meter_akhir']; ?></td>
                                    <td><?php echo $dpw['pemakaian']; ?></td>
                                    <td><?php echo $tagihan_pw; ?></td>
                                    <td><span class="badge bg-<?php echo $badge_pw; ?>"><?php echo $status_tx_pw; ?></span></td>
                                    <td>
                                        <a href="index.php?p=meter_edit&id=<?php echo $id_pw; ?>&dari=pemakaian_warga"><button type="button" class="btn btn-outline-warning btn-sm">Ubah</button></a>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#myModalMeter"
                                            data-id_meter="<?php echo $id_pw; ?>" data-id_pelanggan="<?php echo $id_user_pw; ?>">Hapus</button>
                                    </td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($page === 'pemakaian_sendiri'): ?>
                <div class="card mb-4 shadow-sm" style="border-top: 4px solid #4e73df; border-radius: 0.5rem;">
                    <div class="card-header py-3 d-flex align-items-center gap-2" style="background: linear-gradient(135deg, #f8f9ff 0%, #eaecf4 100%);">
                        <div style="background:#4e73df;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-tint text-white"></i>
                        </div>
                        <div>
                            <h6 class="m-0 fw-bold text-primary">Data Pemakaian dan Pembayaran</h6>
                            <small class="text-muted">Riwayat pemakaian air dan tagihan Anda</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="pemakaian_sendiri_table" class="table table-bordered table-hover table-striped w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu Pencatatan Meter</th>
                                    <th>Kode Tarif</th>
                                    <th>Meter Awal (m³)</th>
                                    <th>Meter Akhir (m³)</th>
                                    <th>Pemakaian (m³)</th>
                                    <th>Tagihan (Rp)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $q_ps = mysqli_prepare($koneksi, "
                                    SELECT p.*, t.id_tarif
                                    FROM pemakaian p
                                    LEFT JOIN tarif t ON p.kd_tarif = t.id_tarif
                                    WHERE p.username = ?
                                    ORDER BY p.tgl DESC, p.waktu DESC
                                ");
                                mysqli_stmt_bind_param($q_ps, "s", $username_session);
                                mysqli_stmt_execute($q_ps);
                                $res_ps = mysqli_stmt_get_result($q_ps);
                                if (mysqli_num_rows($res_ps) == 0):
                                ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data pemakaian.</td></tr>
                                <?php else: while ($dps = mysqli_fetch_assoc($res_ps)):
                                    
                                    $tgl_raw_ps = isset($dps['tgl']) ? $dps['tgl'] : '';
                                    $tgl_ps    = $tgl_raw_ps ? date('d-m-Y', strtotime($tgl_raw_ps)) : '-';
                                    
                                    $wkt_raw_ps = isset($dps['waktu']) ? $dps['waktu'] : '';
                                    $waktu_ps  = $wkt_raw_ps ? $wkt_raw_ps : '-';
                                    
                                    // PERBAIKAN: Menambahkan perhitungan selisih hari agar formatnya seragam dengan admin view
                                    $tgl_tabel_ps = date_create($tgl_raw_ps);
                                    $tgl_sekarang_ps = date_create();
                                    $diff_ps      = $tgl_tabel_ps ? date_diff($tgl_tabel_ps, $tgl_sekarang_ps) : null;
                                    $selisih_ps   = $diff_ps ? $diff_ps->days : 0;
                                    $tglwaktu_ps_full = "$tgl_ps $waktu_ps | " . date("Y-m-d") . " $selisih_ps hari";
                                    
                                    $kd_tarif_1 = isset($dps['kd_tarif']) ? $dps['kd_tarif'] : null;
                                    $kd_tarif_2 = isset($dps['id_tarif']) ? $dps['id_tarif'] : '-';
                                    $kd_tarif_ps = htmlspecialchars($kd_tarif_1 ? $kd_tarif_1 : $kd_tarif_2);
                                    
                                    $tagihan_ps  = number_format(isset($dps['tagihan']) ? $dps['tagihan'] : 0, 0, ',', '');
                                    
                                    $status_ps   = isset($dps['status']) ? $dps['status'] : 'BELUM BAYAR';
                                    $badge_ps    = ($status_ps === 'LUNAS') ? 'success' : 'danger';
                                    $label_ps    = ($status_ps === 'LUNAS') ? 'LUNAS' : 'BLM LUNAS';
                                ?>
                                <tr>
                                    <td><?php echo $tglwaktu_ps_full; ?></td>
                                    <td><?php echo $kd_tarif_ps; ?></td>
                                    <td><?php echo $dps['meter_awal']; ?></td>
                                    <td><?php echo $dps['meter_akhir']; ?></td>
                                    <td><?php echo $dps['pemakaian']; ?></td>
                                    <td><?php echo $tagihan_ps; ?></td>
                                    <td><span class="badge bg-<?php echo $badge_ps; ?> px-3 py-2"><?php echo $label_ps; ?></span></td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

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
                    <div class="text-muted">Copyright &copy; Flavia & Rosita <?php echo date("Y"); ?></div>
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
<!-- <script src="../assets/demo/chart-area-demo.js"></script>
<script src="../assets/demo/chart-bar-demo.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script src="../js/datatables-simple-demo.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    const yuser = "<?php echo isset($username_session) ? $username_session : ''; ?>";
    const level = "<?php echo isset($level) ? $level : ''; ?>";
</script>
<script src="../js/air.js"></script>
<script>
// Menangkap data waktu untuk spesifik level "warga" secara otomatis dari AJAX
$(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url === "../assets/ajax.php") {
        try {
            let d = JSON.parse(xhr.responseText);
            if (d.waktu_sub !== undefined && d.waktu_sub !== "orang" && d.waktu_sub !== "0") {
                $("#summary .bg-primary .ms-3").text(d.waktu_sub); 
                
                if (d.belum_dicatat === "LUNAS" || d.belum_dicatat === "BELUM BAYAR") {
                    $("#summary .bg-danger h1").css("font-size", "2.2rem").text(d.belum_dicatat);
                }
            } else if (d.belum_dicatat !== "LUNAS" && d.belum_dicatat !== "BELUM BAYAR") {
                 $("#summary .bg-danger h1").css("font-size", "");
            }
        } catch(e) {}
    }
});

$(document).ready(function() {
    if (document.getElementById('pemakaian_sendiri_table')) {
        $('#pemakaian_sendiri_table').DataTable({
            language: {
                search:     "Search...",
                lengthMenu: "_MENU_ entries per page",
                info:       "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty:  "Showing 0 entries",
                emptyTable: "Belum ada data pemakaian",
                zeroRecords:"Tidak ada data yang cocok",
                paginate: { previous: "&laquo;", next: "&raquo;" }
            },
            pageLength: 10,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [6] }]
        });
    }

    $('#tarif_table').DataTable({
        language: {
            search:     "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info:       "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
            infoEmpty:  "Menampilkan 0 data",
            emptyTable: "Belum ada data tarif",
            zeroRecords:"Tidak ada data yang cocok",
            paginate: { previous: "&laquo;", next: "&raquo;" }
        },
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [0, 5] } 
        ]
    });

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

    $('#btn_tambah_tarif').click(function() {
        $('#tarif_add').slideToggle();
        $('html, body').animate({ scrollTop: $('#tarif_add').offset().top - 80 }, 400);
    });

    $('#btn_tambah_meter').click(function() {
        $('#catat_meter_add').slideToggle();
        $('html, body').animate({ scrollTop: $('#catat_meter_add').offset().top - 80 }, 400);
    });

    // --- FITUR AUTO-FILL METER AWAL BERDASARKAN METER AKHIR TERAKHIR ---
    $('#sel_id_pelanggan').change(function() {
        var meter_awal = $(this).find(':selected').data('meter');
        if (meter_awal !== undefined && meter_awal !== '') {
            $('#inp_meter_awal').val(meter_awal);
        } else {
            $('#inp_meter_awal').val('0');
        }
    });

    // PERBAIKAN: Logika untuk menangkap username dan memasukkannya ke tombol konfirmasi hapus modal
    $(document).on("click", "button[data-username]", function () {
        var username = $(this).attr("data-username");
        $("#modal_username_text").text(username);
        $("#modal_user_hapus_link").attr("href", "index.php?p=user_hapus&user=" + encodeURIComponent(username));
    });

});
</script>
</body>
</html>