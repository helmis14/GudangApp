<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Gambar</title>
    <style>
        /* CSS untuk menampilkan modal */
        .modal {
            display: none;
            /* Sembunyikan modal secara default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.9);
            /* Warna latar belakang modal */
        }

        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            max-height: 80%;
        }

        .modal-content img {
            width: 100%;
            height: auto;
        }

        .close {
            color: #fff;
            position: absolute;
            top: 10px;
            right: 25px;
            font-size: 30px;
            font-weight: bold;
            transition: 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #ccc;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <!-- Gambar yang bisa diklik -->
    <img src="gambar1.jpeg" alt="Gambar 1" onclick="openModal('gambar1.jpeg')">
    <img src="gambar2.jpeg" alt="Gambar 2" onclick="openModal('gambar2.jpg')">

    <!-- Modal -->
    <div id="myModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img id="modalImg" src="" alt="Gambar">
        </div>
    </div>

    <!-- JavaScript untuk menampilkan dan menyembunyikan modal -->
    <script>
        // Fungsi untuk membuka modal dan menetapkan sumber gambar
        function openModal(imageSrc) {
            var modal = document.getElementById('myModal');
            var modalImg = document.getElementById('modalImg');
            modal.style.display = "block";
            modalImg.src = imageSrc;
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            var modal = document.getElementById('myModal');
            modal.style.display = "none";
        }

        // Tutup modal ketika pengguna mengklik di luar gambar
        window.onclick = function(event) {
            var modal = document.getElementById('myModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>

</html>