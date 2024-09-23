<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($_FILES['file']['name']);

    // Check if the upload directory exists, if not, create it
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo json_encode(['filename' => basename($_FILES['file']['name'])]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'File upload failed.']);
    }
    exit;
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded.']);
}
