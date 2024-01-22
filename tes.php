<?php
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

        // Tampilkan gambar
        echo '<img src="data:image/jpeg;base64,' . $base64_image . '" alt="Uploaded Image" style="max-width: 300px; max-height: 300px;">';
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
    <title>Upload Gambar</title>
</head>
<body>
    <form method="post" enctype="multipart/form-data">
        <label for="gambar">Pilih Gambar:</label>
        <input type="file" name="gambar" class="form-control-file" required>
        <br>
        <button type="submit" name="submit">Upload dan Tampilkan</button>
    </form>
</body>
</html>
