<?php
// Pastikan file yang diunggah adalah file backup SQL
if ($_FILES['fileToRestore']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['fileToRestore']['tmp_name'])) {
    // Koneksi ke database
    $conn = new mysqli("localhost", "root", "", "stokbarangs");

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Nonaktifkan pemeriksaan kunci asing
    $conn->query("SET FOREIGN_KEY_CHECKS=0");

    // Baca isi file backup
    $restore_sql = file_get_contents($_FILES['fileToRestore']['tmp_name']);

    // Eksekusi perintah untuk memulihkan database
    if ($conn->multi_query($restore_sql) === TRUE) {
        echo "Restore database berhasil.";
    } else {
        echo "Error dalam restore database: " . $conn->error;
    }

    // Aktifkan kembali pemeriksaan kunci asing
    // $conn->query("SET FOREIGN_KEY_CHECKS=1");

    // Tutup koneksi
    $conn->close();
} else {
    echo "Upload file gagal atau file yang diunggah bukan file backup SQL.";
}
