<?php
class klas_air 
{
    function koneksi() 
    {
        $koneksi = mysqli_connect("localhost", "user_air", "#Us3r_A1r_2025#", "air");

        // Tambahkan pengecekan koneksi
        if (!$koneksi) {
            die("<div style='font-family:sans-serif;color:red;padding:20px;'>
                    <strong>❌ Koneksi database gagal!</strong><br>
                    Error: " . mysqli_connect_error() . "
                 </div>");
        }

        return $koneksi;
    }

    function dt_user($sesi_user) 
    {
        $koneksi = $this->koneksi();
        // Gunakan prepared statement untuk keamanan (hindari SQL injection)
        $stmt = mysqli_prepare($koneksi, "SELECT nama, kota, level FROM login WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $sesi_user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $d = mysqli_fetch_row($result);
        return $d;
    }

    function bln($no)
    {
        if ($no == 1) $bln = "Januari";
        elseif ($no == 2) $bln = "Februari";    
        elseif ($no == 3) $bln = "Maret"; 
        elseif ($no == 4) $bln = "April"; 
        elseif ($no == 5) $bln = "Mei"; 
        elseif ($no == 6) $bln = "Juni"; 
        elseif ($no == 7) $bln = "Juli"; 
        elseif ($no == 8) $bln = "Agustus"; 
        elseif ($no == 9) $bln = "September"; 
        elseif ($no == 10) $bln = "Oktober"; 
        elseif ($no == 11) $bln = "November"; 
        else
            $bln = "Desember";
        return $bln;
    }   
}
?>