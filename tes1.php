<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Data Barang</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>id permintaan</th>
                    <th>id barang</th>
                    <th>nama barang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data barang akan dimuat di sini dari database -->
            </tbody>
        </table>
    </div>

    <!-- Include jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Include custom script -->
    <script>
        $(document).ready(function() {
            // Memuat data barang saat dokumen siap
            loadData();

            // Fungsi untuk memuat data barang dari database
            function loadData() {
                $.ajax({
                    url: 'load_data.php',
                    type: 'GET',
                    success: function(response) {
                        $('tbody').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }

            // Menghapus data barang
            $('tbody').on('click', '.delete-btn', function() {
                var idToDelete = $(this).data('idbarang');
                var confirmation = confirm("Apakah Anda yakin ingin menghapus data ini?");

                if (confirmation) {
                    $.ajax({
                        type: 'POST',
                        url: 'delete_data.php',
                        data: {
                            idbarang: idToDelete
                        },
                        success: function(response) {
                            alert(response);
                            loadData(); // Memuat ulang data setelah penghapusan berhasil
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
