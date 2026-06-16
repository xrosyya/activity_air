<?php
session_start();
include '../assets/func.php';
$air = new klas_air;
$koneksi = $air->koneksi();

if (isset($_POST['p'])) {
    $p = $_POST['p'];

    if ($p == "summary") {
        $bln = $_POST['t']; // cth: '2026-01'
        
        $username_session = isset($_SESSION['user']) ? $_SESSION['user'] : '';
        $level_user = '';
        if ($username_session != '') {
            $q_lvl = mysqli_query($koneksi, "SELECT level FROM login WHERE username='$username_session'");
            if ($d_lvl = mysqli_fetch_assoc($q_lvl)) {
                $level_user = strtolower(trim($d_lvl['level']));
            }
        }

        $box1 = "0"; $box1_sub = "orang";
        $box2 = "0"; 
        $box3 = "0"; 
        $box4 = "0"; 

        if ($level_user == 'warga') {
            $q_warga = mysqli_query($koneksi, "SELECT tgl, waktu, pemakaian, tagihan, status FROM pemakaian WHERE username='$username_session' AND tgl LIKE '$bln%' LIMIT 1");
            
            if (mysqli_num_rows($q_warga) > 0) {
                $d_warga = mysqli_fetch_assoc($q_warga);
                $tgl_parts = explode('-', $d_warga['tgl']);
                $box1 = isset($tgl_parts[2]) ? $tgl_parts[2] : '0';
                $box1_sub = $d_warga['waktu'];
                $box2 = $d_warga['pemakaian'] ? $d_warga['pemakaian'] : "0";
                $box3 = number_format($d_warga['tagihan'], 0, ',', '.');
                $box4 = strtoupper($d_warga['status']);
            } else {
                $box1 = "0";
                $box1_sub = "0";
                $box2 = "0";
                $box3 = "0";
                $box4 = "0";
            }
        } 
        else {
            $q1 = mysqli_query($koneksi, "SELECT COUNT(username) as jml_pelanggan FROM login WHERE level='warga'");
            $d1 = mysqli_fetch_assoc($q1);
            $total_warga = $d1['jml_pelanggan'] ? (int)$d1['jml_pelanggan'] : 0;
            $box1 = $total_warga;
            $box1_sub = "orang";

            if ($level_user == 'bendahara') {
                $q2 = mysqli_query($koneksi, "SELECT SUM(tagihan) as pemasukan FROM pemakaian WHERE tgl LIKE '$bln%' AND status='LUNAS'");
                $d2 = mysqli_fetch_assoc($q2);
                $nominal = $d2['pemasukan'] ? $d2['pemasukan'] : 0;
                $box2 = $nominal > 0 ? number_format($nominal, 0, ',', '.') : "0"; 

                $q3 = mysqli_query($koneksi, "SELECT COUNT(DISTINCT username) as sudah_lunas FROM pemakaian WHERE tgl LIKE '$bln%' AND status='LUNAS'");
                $d3 = mysqli_fetch_assoc($q3);
                $box3 = $d3['sudah_lunas'] ? (int)$d3['sudah_lunas'] : 0;

                $calc4 = $total_warga - $box3;
                $box4 = $calc4 < 0 ? 0 : $calc4;

            } else {
                $q2 = mysqli_query($koneksi, "SELECT SUM(pemakaian) as jml_pemakaian FROM pemakaian WHERE tgl LIKE '$bln%'");
                $d2 = mysqli_fetch_assoc($q2);
                $box2 = $d2['jml_pemakaian'] ? $d2['jml_pemakaian'] : "0";

                $q3 = mysqli_query($koneksi, "SELECT COUNT(DISTINCT username) as sudah_dicatat FROM pemakaian WHERE tgl LIKE '$bln%'");
                $d3 = mysqli_fetch_assoc($q3);
                $box3 = $d3['sudah_dicatat'] ? (int)$d3['sudah_dicatat'] : 0;

                $q4 = mysqli_query($koneksi, "SELECT COUNT(username) as belum_dicatat FROM login WHERE level='warga' AND username NOT IN (SELECT username FROM pemakaian WHERE tgl LIKE '$bln%')");
                $d4 = mysqli_fetch_assoc($q4);
                $box4 = $d4['belum_dicatat'] ? (int)$d4['belum_dicatat'] : 0;
            }
        }

        $data = array(
            'jml_pelanggan' => $box1,
            'waktu_sub'     => $box1_sub, 
            'pemakaian'     => $box2, 
            'sudah_dicatat' => $box3, 
            'belum_dicatat' => $box4  
        );

        echo json_encode($data);
    } elseif($p == "chart_bar") {
        $yuser = $_POST['y'];
        $q = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, pemakaian FROM pemakaian WHERE username='$yuser'");
        while ($d = mysqli_fetch_assoc($q)) {
            $response[] = $air->bln($d['bln']);
            $response[] = $d['pemakaian'];
        }
        echo json_encode($response);
    } elseif($p == "chart_line") {
        $yuser = $_POST['y'];
        $q = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, tagihan FROM pemakaian WHERE username='$yuser'");
        while ($d = mysqli_fetch_assoc($q)) {
            $response[] = $air->bln($d['bln']);
            $response[] = $d['tagihan'];
        }
        echo json_encode($response);
    }
}
?>