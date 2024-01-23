<?php
// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "stokbarangs");

// Function untuk mengonversi gambar menjadi base64
function convertToBase64($file_path)
{
    $image_data = file_get_contents($file_path);
    $base64_image = base64_encode($image_data);
    return $base64_image;
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Pastikan ada file yang diunggah
    if ($_FILES['gambar']['error'] == 0) {
        // Path sementara file yang diunggah
        $tmp_path = $_FILES['gambar']['tmp_name'];

        // Konversi gambar ke base64
        $base64_image = convertToBase64($tmp_path);

        // Simpan base64 ke dalam database
        $query = "INSERT INTO permintaan (bukti_base64) VALUES (?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $base64_image);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo 'Gambar berhasil diunggah dan disimpan.';
        } else {
            echo 'Gagal menyimpan gambar ke database.';
        }

        mysqli_stmt_close($stmt);
    } else {
        echo 'Gagal mengunggah file.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload dan Simpan Gambar Base64</title>
</head>
<body>
    <form method="post" enctype="multipart/form-data">
        <label for="gambar">Pilih Gambar:</label>
        <input type="file" name="gambar" accept="image/*" required>
        <br>
        <button type="submit" name="submit">Upload dan Simpan ke Database</button>
    </form>
    <?php
    // Ambil data gambar dari database (misalnya, 5 gambar terakhir)
    $query = "SELECT bukti_base64 FROM permintaan ORDER BY idpermintaan DESC LIMIT 5";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $base64_image = $row['bukti_base64'];
        $data_uri = 'data:image/jpeg;base64,' . $base64_image;
    ?>
        <img src="<?= $data_uri; ?>" alt="Gambar dari Database" style="max-width: 100px; margin-top: 10px;">
    <?php
    }
    ?>
</body>
</html>
