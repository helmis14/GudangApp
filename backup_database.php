<?php
$conn = new mysqli("localhost", "root", "", "stokbarangs");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$backup_folder = 'database/';
$backup_file = $backup_folder . 'backup_' . date("Y-m-d_H-i-s") . '.sql';

$handle = fopen($backup_file, 'w');

$tables = $conn->query("SHOW TABLES");

while ($row = $tables->fetch_row()) {
    $table = $row[0];

    $create_table = $conn->query("SHOW CREATE TABLE $table");
    $table_ddl = $create_table->fetch_row()[1];
    fwrite($handle, "$table_ddl;\n\n");

    $result = $conn->query("SELECT * FROM $table");
    $total_fields = $result->field_count;

    while ($data = $result->fetch_assoc()) {
        $values = array_map(array($conn, 'real_escape_string'), array_values($data));
        $sql = "INSERT INTO $table VALUES ('" . implode("', '", $values) . "');\n";
        fwrite($handle, $sql);
    }
    fwrite($handle, "\n");
}

fclose($handle);

echo "Backup database berhasil disimpan dalam file: $backup_file";

$conn->close();
