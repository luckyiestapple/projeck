<?php
session_start();
require "koneksi.php"; // Pastikan file koneksi.php sudah benar dan terhubung

// Validasi akses (Hanya Dokter)
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "dokter") {
    header("Location: login.php");
    exit;
}

$id_dokter = $_SESSION["id_dokter"];
$today = date('Y-m-d'); // Tanggal hari ini untuk filter antrian

// ========================================================
// LOGIKA PEMROSESAN AJAX UNTUK SIMPAN DIAGNOSA (POST Request)
// Dipanggil saat tombol "Selesai" di modal diklik.
// ========================================================
if (isset($_POST["ajax_process"]) && $_POST["ajax_process"] == "1") {
    header("Content-Type: application/json");

    // Ambil dan bersihkan Data dari Form AJAX
    $id_antrian = (int) ($_POST['id_antrian'] ?? 0);
    $pasien_id = (int) ($_POST['id_pasien'] ?? 0);
    $diagnosa = htmlspecialchars($_POST['diagnosa'] ?? '');
    $catatan = htmlspecialchars($_POST['catatan'] ?? '');
    $tanggal_periksa = date('Y-m-d H:i:s');
    
    // Safety check data
    if ($id_antrian <= 0 || $pasien_id <= 0 || empty($diagnosa) || empty($catatan)) {
         echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
         exit;
    }

    $konek->begin_transaction(); // Mulai transaksi database (Penting!)

    try {
        // 1. Simpan Data ke Tabel rekam_medis
        $sql_rm = "INSERT INTO rekam_medis 
                   (id_pasien, id_dokter, diagnosis, catatan, tanggal_periksa) 
                   VALUES (?, ?, ?, ?, ?)"; 
                   
        $stmt_rm = $konek->prepare($sql_rm);
        // "iisss" = integer, integer, string, string, string (sesuai tipe kolom)
        $stmt_rm->bind_param("iisss", $pasien_id, $id_dokter, $diagnosa, $catatan, $tanggal_periksa);
        $stmt_rm->execute();
        $stmt_rm->close();

        // 2. Update Status Antrian menjadi 'Selesai'
        $sql_antrian = "UPDATE antrian SET status = 'Selesai' WHERE id = ? AND dokter_id = ?";
        $stmt_antrian = $konek->prepare($sql_antrian);
        $stmt_antrian->bind_param("ii", $id_antrian, $id_dokter);
        $stmt_antrian->execute();
        $stmt_antrian->close();

        $konek->commit(); // Commit transaksi jika kedua query sukses

        // Kirim respons sukses dan URL redirect
        echo json_encode([
            'success' => true, 
            'redirect_url' => "cari_rekam_medis.php?pasien_id=" . $pasien_id // Redirect tujuan
        ]);
        
    } catch (Exception $e) {
        $konek->rollback(); // Batalkan semua jika ada error
        echo json_encode([
            'success' => false, 
            'message' => 'Gagal memproses data. Error: ' . $e->getMessage()
        ]);
    }
    
    $konek->close();
    exit;
}
// ========================================================
// MODE AJAX GET → Ambil Data Antrian Realtime
// ========================================================
if (isset($_GET["ajax"]) && $_GET["ajax"] == "1") {
    $antrian = $konek->query("
        SELECT a.*, p.nama AS nama_pasien, pl.nama_poli
        FROM antrian a
        LEFT JOIN pasien p ON p.id = a.pasien_id
        LEFT JOIN poli pl ON pl.id = a.poli_id
        WHERE a.dokter_id = $id_dokter
          AND DATE(a.waktu_daftar) = '$today'
        ORDER BY FIELD(a.status,'Dipanggil','Menunggu'), a.waktu_daftar ASC
    ");

    $rows = [];
    while ($row = $antrian->fetch_assoc()) {
        $rows[] = $row;
    }

    header("Content-Type: application/json");
    echo json_encode($rows);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Antrian Pasien (Dokter)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body { background: #f8fafc; }
        table td { vertical-align: middle; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="dashboard_dokter.php">Panel Dokter</a>
</nav>

<div class="container py-4">
    <h3>Antrian Pasien 🩺</h3>

    <table class="table table-bordered table-hover mt-3">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Nama Pasien</th>
                <th>Poli</th>
                <th>Keluhan</th>
                <th>Status</th>
                <th>Waktu Daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody id="tbody-antrian">
            </tbody>
    </table>
</div>

<div class="modal fade" id="diagnosaModal" tabindex="-1" aria-labelledby="diagnosaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="diagnosaModalLabel">Input Diagnosa Pasien: <span id="modal_nama_pasien"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formDiagnosa"> 
                <div class="modal-body">
                    <input type="hidden" name="id_antrian" id="modal_id_antrian">
                    <input type="hidden" name="id_pasien" id="modal_id_pasien">
                    <input type="hidden" name="ajax_process" value="1"> <p>Keluhan Awal: <strong id="modal_keluhan_display"></strong></p>
                    
                    <div class="mb-3">
                        <label for="diagnosa" class="form-label">Diagnosa (Wajib)</label>
                        <input type="text" class="form-control" id="diagnosa" name="diagnosa" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan Dokter (Wajib)</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnSelesai">Selesai</button> 
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function badgeStatus(s) {
    if (s === "Menunggu") return '<span class="badge bg-secondary">Menunggu</span>';
    if (s === "Dipanggil") return '<span class="badge bg-warning text-dark">Dipanggil</span>';
    return '<span class="badge bg-success">Selesai</span>';
}

function tombol(row) {
    // Tombol untuk status Menunggu dan Dipanggil akan memicu Modal Diagnosa
    if (row.status === "Menunggu" || row.status === "Dipanggil")
        return `<button 
                    class="btn btn-sm btn-warning panggil-btn" 
                    data-bs-toggle="modal" 
                    data-bs-target="#diagnosaModal"
                    data-id-antrian="${row.id}"
                    data-id-pasien="${row.pasien_id}"
                    data-nama-pasien="${row.nama_pasien}"
                    data-keluhan="${row.keluhan ?? ''}">
                    ${row.status === "Menunggu" ? "Panggil" : "Proses"}
                </button>`;
    // Status Selesai: Tombol langsung ke Rekam Medis
    return `<a href="cari_rekam_medis.php?pasien_id=${row.pasien_id}" class="btn btn-sm btn-info">Lihat RM</a>`;
}

// Mengambil dan me-render daftar antrian secara real-time
function load() {
    fetch("antrian.php?ajax=1")
    .then(r => r.json())
    .then(data => {
        let html = "";
        data.forEach(row => {
            html += `
            <tr>
                <td>${row.nomor}</td>
                <td>${row.nama_pasien}</td>
                <td>${row.nama_poli}</td>
                <td>${row.keluhan ?? "-"}</td>
                <td>${badgeStatus(row.status)}</td>
                <td>${row.waktu_daftar}</td>
                <td>${tombol(row)}</td>
            </tr>`;
        });
        document.getElementById("tbody-antrian").innerHTML = html;
    });
}

document.addEventListener('DOMContentLoaded', (event) => {
    const modalElement = document.getElementById('diagnosaModal');

    // 1. Logika Pengisian Modal (saat tombol 'Panggil' diklik)
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; 
            
            const idAntrian = button.getAttribute('data-id-antrian');
            const idPasien = button.getAttribute('data-id-pasien');
            const namaPasien = button.getAttribute('data-nama-pasien');
            const keluhan = button.getAttribute('data-keluhan');
            
            // Isi data ke dalam modal
            document.getElementById('modal_id_antrian').value = idAntrian;
            document.getElementById('modal_id_pasien').value = idPasien;
            document.getElementById('modal_nama_pasien').textContent = namaPasien;
            document.getElementById('modal_keluhan_display').textContent = keluhan;

            document.getElementById('formDiagnosa').reset();
        });
    }


    // 2. Logika Submit Form Menggunakan AJAX (saat tombol 'Selesai' diklik)
    const formDiagnosa = document.getElementById('formDiagnosa');
    if (formDiagnosa) {
        formDiagnosa.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const form = e.target;
            const formData = new FormData(form);
            const btnSelesai = document.getElementById('btnSelesai');

            btnSelesai.disabled = true;
            btnSelesai.textContent = 'Memproses...';

            // Kirim data ke file antrian.php (PHP POST Handler)
            fetch("antrian.php", {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide(); 
                    
                    // REDIRECT OTOMATIS
                    window.location.href = result.redirect_url;

                } else {
                    alert("Error: " + result.message);
                    btnSelesai.disabled = false;
                    btnSelesai.textContent = 'Selesai';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert("Terjadi kesalahan koneksi.");
                btnSelesai.disabled = false;
                btnSelesai.textContent = 'Selesai';
            });
        });
    }

    load();
    setInterval(load, 3000); // Refresh daftar antrian setiap 3 detik
});
</script>

</body>
</html>