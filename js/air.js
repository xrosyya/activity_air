$(document).ready(function(){
    // 1. Mengambil parameter dari URL
    const urlParams = new URLSearchParams(window.location.search);
    const p = urlParams.get('p'); 

    console.log("Halaman saat ini (p): " + p);

    // LOGIKA UNTUK MENU USER
    if (p === "user") {
        $("#summary, #chart, #user_add, #tarif_add, #tarif_list").hide();
        $("#user_list").show();

        $(".datatable-dropdown").empty().append("<button type='button' class='btn btn-outline-success float-start me-2' id='btnTambahUser'><i class='fa-solid fa-user-plus'></i> User</button>");
        
        $("#btnTambahUser").click(function(){
            $("#user_list").hide();
            $("#user_add").show();
            $("#user_form")[0].reset(); 
            $("#user_form input[name='username']").prop("readonly", false).css("background-color", ""); 
            $("button[name='tombol']").val("user_add").text("Simpan");
        });

    } else if (p === "user_edit") {
        $("#summary, #chart, #user_list, #tarif_add, #tarif_list").hide();
        $("#user_add").show(); 
        $("#user_form input[name='username']").prop("readonly", true).css("background-color", "#e9ecef");

    // LOGIKA UNTUK MENU TARIF
    } else if (p === "tarif") {
        $("#summary, #chart, #user_add, #user_list, #tarif_add").hide();
        $("#tarif_list").show();

        // Menggunakan setTimeout agar menunggu Datatable selesai dirender browser
        setTimeout(function() {
            $(".datatable-dropdown").prepend("<button type='button' class='btn btn-success me-2' id='btn_tambah_tarif'><i class='fa-solid fa-money-bill-wave me-1'></i> +</button>");
            $(".datatable-dropdown, .datatable-search").addClass("mt-2 d-flex flex-wrap gap-2 align-items-center");
        }, 100);
        
        // Ketika tombol Tambah Tarif (+) diklik
        $(document).on("click", "#btn_tambah_tarif", function(){
        // Sembunyikan tabel daftar, tampilkan form
        $("#tarif_list").fadeOut(200, function() {
            $("#tarif_add").fadeIn(300);
        });

        // Reset isi form agar kosong saat menambah data baru
        $("#tarif_form")[0].reset();
        
        // Pastikan tombol bernilai 'tarif_add'
        $("#tarif_form button[name='tombol']").val('tarif_add').html("<i class='fas fa-save me-1'></i> Simpan Data");
        
        // Buka kunci input ID Tarif (agar bisa diisi)
        $("#tarif_form input[name='yid_tarif']").prop("readonly", false).css("background-color", "#fff");
        
        // Hapus input hidden jika ada bekas edit sebelumnya
        $("#tarif_form input[type='hidden'][name='yid_tarif']").remove();
    });

    } else if (p === "tarif_edit") {
        $("#summary, #chart, #user_add, #user_list, #tarif_list").hide();
        $("#tarif_add").show();

        // Atur nilai tombol menjadi edit
        $("#tarif_form button[name='tombol']").val('tarif_edit');
        
        // Kunci kolom ID agar tidak bisa diedit (gunakan readonly agar data tetap terkirim)
        $("#tarif_form input[name='yid_tarif']").prop("readonly", true).css("background-color", "#e9ecef");

        // Jika Anda masih butuh input hidden (mengambil id dari URL ?p=tarif_edit&id=...)
        const id_tarif = urlParams.get('id');
        if(id_tarif) {
            $("#tarif_form").append("<input type='hidden' name='yid_tarif' value='" + id_tarif + "'>");
        }

    // MODE DEFAULT (DASHBOARD)
    } else {
        $("#summary, #chart").show();
        $("#user_add, #user_list, #tarif_add, #tarif_list").hide();
    }

    // LOGIKA MODAL HAPUS TARIF (Global)
    $(document).on("click", "button[data-bs-toggle='modal']", function(){
        let id_tarif = $(this).attr('data-id_tarif');
        if(id_tarif) { 
            $("#myModal .modal-body").text("Yakin hapus data Tarif ID: " + id_tarif + "?");
            $(".modal-footer form input[type='hidden']").remove(); 
            $(".modal-footer form").append("<input type='hidden' name='id_tarif' value='" + id_tarif + "'>");
            $(".modal-footer form button[name='tombol']").val('tarif_hapus');
        }
    });
});