<!DOCTYPE html>
<html>

<head>
    <title>Error PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f8d7da;
        }

        .error-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            border-left: 5px solid #dc3545;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <h2>Error Generating PDF</h2>
        <p>{{ $message ?? 'Terjadi kesalahan saat membuat PDF' }}</p>
        <button onclick="window.history.back()" class="btn btn-primary">Kembali</button>
    </div>
</body>

</html>