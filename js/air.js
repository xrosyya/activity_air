$(document).ready(function () {

    // 1. Ambil parameter dari URL
    const urlParams = new URLSearchParams(window.location.search);
    const p = urlParams.get('p');

    // === DEKLARASI VARIABEL (Harap sesuaikan dengan darimana Anda mengambil data ini) ===
    // const level = ...; // misal dari element HTML atau local storage
    // const yuser = ...; 

    console.log("Halaman saat ini (p): " + p);

    // Sembunyikan semua section terlebih dahulu
    const $semua = $("#summary, #chart, #user_add, #user_list, #tarif_add, #tarif_list, #catat_meter_add, #catat_meter_list, #pemakaian_warga_list, #pemakaian_sendiri_card");
    $semua.hide();

    // LOGIKA MENU USER
    if (p === "user") {
        $("#user_list").show();

        $(".datatable-dropdown")
            .empty()
            .prepend("<button type='button' class='btn btn-outline-success float-start me-2' id='btnTambahUser'>" +
                     "<i class='fa-solid fa-user-plus'></i> User</button>");

        $("#btnTambahUser").on("click", function () {
            $("#user_list").hide();
            $("#user_add").show();
            $("#user_form")[0].reset();
            $("#user_form input[name='username']").prop("readonly", false).css("background-color", "");
            $("button[name='tombol']").val("user_add").text("Simpan");
        });

    // LOGIKA MENU USER EDIT
    } else if (p === "user_edit") {
        $("#user_add").show();
        $("#user_form input[name='username']").prop("readonly", true).css("background-color", "#e9ecef");

    // LOGIKA MENU TARIF
    } else if (p === "tarif") {
        $("#tarif_list").show();

        $(document).on("click", "#btn_tambah_tarif", function () {
            $("#tarif_list").fadeOut(200, function () {
                $("#tarif_add").fadeIn(300);
            });
            $("#tarif_form")[0].reset();
            $("#tarif_form button[name='tombol']").val("tarif_add").html("<i class='fas fa-save me-1'></i> Simpan Data");
            $("#tarif_form input[name='yid_tarif']").prop("readonly", false).css("background-color", "#fff");
            $("#tarif_form input[type='hidden'][name='yid_tarif']").remove();
        });

    // LOGIKA TARIF EDIT
    } else if (p === "tarif_edit") {
        $("#tarif_add").show();

        const id_tarif = urlParams.get('id');

        $("#tarif_form input[name='yid_tarif']")
            .val(id_tarif)
            .prop("readonly", true)
            .css("background-color", "#e9ecef");

        $("#tarif_form input[type='hidden'][name='yid_tarif']").remove();
        if (id_tarif) {
            $("#tarif_form").append("<input type='hidden' name='yid_tarif' value='" + id_tarif + "'>");
        }

        $("#tarif_form button[name='tombol']")
            .val("tarif_edit")
            .html("<i class='fas fa-save me-1'></i> Simpan Perubahan");

    // LOGIKA MENU CATAT METER
    } else if (p === "catat_meter") {
        $("#catat_meter_list").show();

        $(document).on("click", "#btn_tambah_meter", function () {
            $("#catat_meter_list").fadeOut(200, function () {
                $("#catat_meter_add").fadeIn(300);
            });
            $("#meter_form")[0].reset();
            $("#meter_form #btn_meter_submit").val("meter_add").html("<i class='fas fa-save me-1'></i> Simpan Data");
            $("#form_id_meter").val("");
        });

    // LOGIKA CATAT METER EDIT
    } else if (p === "meter_edit") {
        $("#catat_meter_add").show();

    // LOGIKA LIHAT PEMAKAIAN WARGA (BENDAHARA/ADMIN)
    } else if (p === "pemakaian_warga") {
        $("#pemakaian_warga_list").show();

        $(document).on("click", "#btn_tambah_meter_pw", function () {
            $('input[name="dari"]').val('pemakaian_warga'); 
            
            $("#pemakaian_warga_list").fadeOut(200, function () {
                $("#catat_meter_add").fadeIn(300);
            });
            
            $("#meter_form")[0].reset();
            $("#meter_form #btn_meter_submit").val("meter_add").html("<i class='fas fa-save me-1'></i> Simpan Data");
            $("#form_id_meter").val("");
        });

        if (document.getElementById('pemakaian_warga_table') && typeof simpleDatatables !== "undefined") {
            new simpleDatatables.DataTable('#pemakaian_warga_table', {
                searchable: true,
                fixedHeight: false,
                perPage: 10,
                labels: {
                    placeholder: "Search...",
                    perPage: "{select} entries per page",
                    noRows: "Belum ada data pemakaian",
                    info: "Menampilkan {start} s/d {end} dari {rows} data",
                    noResults: "Tidak ada data yang cocok"
                }
            });
        }

    // LOGIKA PEMAKAIAN SENDIRI (WARGA)
    } else if (p === "pemakaian_sendiri") {
        // Tabel diinisialisasi via jQuery DataTables di index.php script block

    // MODE DEFAULT (DASHBOARD)
    } else {
        $("#summary, #chart").show();
        $("#user_add, #user_list, #tarif_add, #tarif_list, #catat_meter_add, #catat_meter_list, #pemakaian_sendiri_list, #pemakaian_warga_list").hide();

        // Logika memilih waktu
        $("#pilih_waktu select[name='waktu']").off("change").on("change", function(){
            let bln = $(this).val();
            // console.log("bulan dipilih: " + bln + " level: " + level);

            if (bln !== "") {
                $.ajax({
                    type: "post",
                    url: "../assets/ajax.php",
                    data: { p: "summary", t: bln, l: level, y: yuser }, // PENAMBAHAN L & Y SESUAI GAMBAR
                    dataType: "json"
                })
                .done(function(d){
                    console.log("Data bulan ini: ", d);
                    
                    // PERBAIKAN: Mengambil object d langsung secara benar
                    $("#summary .bg-primary h1").text(d.jml_pelanggan); 
                    $("#summary .bg-warning h1").text(d.pemakaian);     
                    $("#summary .bg-success h1").text(d.sudah_dicatat); 
                    $("#summary .bg-danger h1").text(d.belum_dicatat);  
                })
                .fail(function () {
                    console.log("ada error nich..."); // PENYESUAIAN TEKS FAIL SESUAI GAMBAR
                });
            } else {
                // PERBAIKAN: Kembalikan semua kotak ke angka 0 saat opsi kosong dipilih
                $("#summary .bg-primary h1").text("0");
                $("#summary .bg-warning h1").text("0");
                $("#summary .bg-success h1").text("0");
                $("#summary .bg-danger h1").css("font-size", "").text("0");
            }
        });

        // PENAMBAHAN AJAX CHART_BAR SESUAI GAMBAR
        $.ajax({
            type: "post",
            url: "../assets/ajax.php",
            data: { p: "chart_bar", y: yuser },
            dataType: "json"
        })
        .done(function(respon){
            
            sumbuX=respon.filter((num, index)=>index % 2 ==0);
            sumbuY=respon.filter((num, index)=>index % 2 !=0);
            // console.log("respon: "+respon);
            // Set new default font family and font color to mimic Bootstrap's default styling
            Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.global.defaultFontColor = '#292b2c';

            // Bar Chart Example
            var ctx = document.getElementById("myBarChart");
            var myLineChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: sumbuX,
                datasets: [{
                label: "Pemakaian (m3)",
                backgroundColor: "rgba(2,117,216,1)",
                borderColor: "rgba(2,117,216,1)",
                data: sumbuY,
                }],
            },
            options: {
                scales: {
                xAxes: [{
                    time: {
                    unit: 'month'
                    },
                    gridLines: {
                    display: false
                    },
                    ticks: {
                    maxTicksLimit: 6
                    }
                }],
                yAxes: [{
                    ticks: {
                    min: 0,
                    max: 30,
                    maxTicksLimit: 5
                    },
                    gridLines: {
                    display: true
                    }
                }],
                },
                legend: {
                display: false
                }
            }
            });

        });

        $.ajax({
            type: "post",
            url: "../assets/ajax.php",
            data: { p: "chart_line", y: yuser },
            dataType: "json"
        })
        .done(function(respon){
            
            sumbuX=respon.filter((num, index)=>index % 2 ==0);
            sumbuY=respon.filter((num, index)=>index % 2 !=0);
            // console.log("respon: "+respon);
           // Set new default font family and font color to mimic Bootstrap's default styling
            Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.global.defaultFontColor = '#292b2c';

            // Area Chart Example
            var ctx = document.getElementById("myAreaChart");
            var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: sumbuX,
                datasets: [{
                label: "Tagihan (Rp)",
                lineTension: 0.3,
                backgroundColor: "rgba(2,117,216,0.2)",
                borderColor: "rgba(2,117,216,1)",
                pointRadius: 5,
                pointBackgroundColor: "rgba(2,117,216,1)",
                pointBorderColor: "rgba(255,255,255,0.8)",
                pointHoverRadius: 5,
                pointHoverBackgroundColor: "rgba(2,117,216,1)",
                pointHitRadius: 50,
                pointBorderWidth: 2,
                data: sumbuY,
                }],
            },
            options: {
                scales: {
                xAxes: [{
                    time: {
                    unit: 'date'
                    },
                    gridLines: {
                    display: false
                    },
                    ticks: {
                    maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                    min: 0,
                    max: 100000,
                    maxTicksLimit: 5
                    },
                    gridLines: {
                    color: "rgba(0, 0, 0, .125)",
                    }
                }],
                },
                legend: {
                display: false
                }
            }
            });


        });
    }

    // MODAL KONFIRMASI HAPUS TARIF
    $(document).on("click", "button[data-bs-toggle='modal'][data-id_tarif]", function () {
        const id_tarif = $(this).attr("data-id_tarif");

        $("#myModal .modal-body").text("Yakin hapus data Tarif ID: " + id_tarif + "?");

        $("#myModal .modal-footer form input[type='hidden']").remove();
        $("#myModal .modal-footer form").append(
            "<input type='hidden' name='id_tarif' value='" + id_tarif + "'>"
        );

        $("#myModal .modal-footer form button[name='tombol']").val("tarif_hapus");
    });

    // MODAL KONFIRMASI HAPUS CATAT METER
    $(document).on("click", "button[data-bs-toggle='modal'][data-id_meter]", function () {
        const id_meter     = $(this).attr("data-id_meter");
        const id_pelanggan = $(this).attr("data-id_pelanggan");

        $("#myModalMeter #modal_id_meter_text").text(id_pelanggan);
        $("#modal_id_meter_hidden").val(id_meter);
    });

});