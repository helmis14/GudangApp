<!DOCTYPE html>
<html>

<head>
    <title>Tambah Permintaan</title>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tombol untuk menambahkan barang baru
            $("#addBarangBtn").click(function() {
                // Tambahkan form barang baru ke dalam form utama
                let newBarang = `
                    <div id="barang${nomorBarang}">
                        <label for="namabarang[]">Nama Barang:</label>
                        <input type="text" name="namabarang[]" placeholder="Nama Barang" class="form-control" required>
                        <br>
                        <label for="unit[]">Unit:</label>
                        <select name="unit[]" class="form-control">
                            <option value="Pcs">PCS</option>
                            <option value="Pack">Pack</option>
                            <option value="Kg">KG</option>
                            <option value="Ball">BALL</option>
                        </select>
                        <br>
                        <label for="qtypermintaan[]">Jumlah:</label>
                        <input type="Number" name="qtypermintaan[]" placeholder="Quantity" class="form-control" required>
                        <br>
                        <label for="keterangan[]">Keterangan:</label>
                        <input type="text" name="keterangan[]" placeholder="Keterangan" class="form-control" required>
                        <br>
                        <button type="button" class="btn btn-danger" onclick="hapusBarang(${nomorBarang})">Hapus</button>
                        <hr>
                    </div>
                `;

                $("#barangContainer").append(newBarang);
                nomorBarang++;
            });

            // Tangani penghapusan barang di modal edit permintaan
            function hapusBarang(nomor) {
                $("#barang" + nomor).remove();
            }

            // Tombol untuk mengirim formulir
            $("#submitBtn").click(function() {
                // Mengambil data dari formulir
                let formData = new FormData($("#myForm")[0]);

                // Mengirim data ke server menggunakan AJAX
                $.ajax({
                    url: "submit.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Tampilkan hasil dari server
                        alert(response);
                    },
                    error: function(xhr, status, error) {
                        // Tampilkan pesan kesalahan jika ada kesalahan
                        alert("Error: " + error);
                    }
                });
            });
        });
    </script>
</head>

<body>
    <!-- The Modal "Tambah Permintaan"-->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Permintaan</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form id="myForm" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <!-- Formulir utama -->
                        <label for="bukti_base64">Bukti Permintaan:</label>
                        <input type