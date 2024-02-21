<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #ff6347;
        }

        p {
            color: #666;
        }

        .loading {
            display: none;
            margin-top: 20px;
            font-size: 16px;
            color: #333;
        }

        .loading:before {
            content: "Mohon tunggu...";
            display: inline-block;
            width: 100px;
            height: 100px;
            border: 8px solid #ccc;
            border-top-color: #333;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Access Denied</h1>
        <p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <p>Klik <a href="javascript:history.back()">di sini</a> untuk kembali ke halaman sebelumnya.</p>
        <div class="loading" id="loading"></div>
    </div>

    <script>
        document.getElementById('loading').style.display = 'block';

        setTimeout(function() {
            window.location.href = 'javascript:history.back()';
        }, 3000);
    </script>
</body>

</html>