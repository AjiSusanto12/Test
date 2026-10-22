
<?php
$host = "localhost";
$db   = "db_staff_it";
$user = "root";
$pass = ""; // Sesuaikan dengan konfigurasi komputer

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );
} catch (PDOException $e) {
    die("Koneksi database gagal. Periksa konfigurasi.");
}
?>
