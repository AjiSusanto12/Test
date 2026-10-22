<?php
require 'db.php';

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

$pesan = [
    'created' => 'Data berhasil ditambahkan.',
    'updated' => 'Data berhasil diperbarui.',
    'deleted' => 'Data berhasil dihapus.'
];

$error = '';
$status = $_GET['status'] ?? '';
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editData = null;

// PROSES TAMBAH, EDIT, DAN HAPUS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    try {
        if ($aksi === 'tambah' || $aksi === 'edit') {
            $nik = trim($_POST['nik'] ?? '');
            $nama = trim($_POST['nama'] ?? '');
            $jabatan = trim($_POST['jabatan'] ?? '');
            $departemen = trim($_POST['departemen'] ?? '');

            if (
                $nik === '' || $nama === '' ||
                $jabatan === '' || $departemen === ''
            ) {
                throw new RuntimeException(
                    'Semua kolom wajib diisi.'
                );
            }

            if ($aksi === 'tambah') {
                $sql = "INSERT INTO karyawan
                    (nik, nama, jabatan, departemen)
                    VALUES (?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $nik,
                    $nama,
                    $jabatan,
                    $departemen
                ]);

                header('Location: index.php?status=created');
                exit;
            }

            $id = filter_input(
                INPUT_POST,
                'id',
                FILTER_VALIDATE_INT
            );

            if (!$id) {
                throw new RuntimeException('ID tidak valid.');
            }

            $sql = "UPDATE karyawan
                    SET nik = ?, nama = ?, jabatan = ?,
                        departemen = ?
                    WHERE id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nik,
                $nama,
                $jabatan,
                $departemen,
                $id
            ]);

            header('Location: index.php?status=updated');
            exit;
        }

        if ($aksi === 'hapus') {
            $id = filter_input(
                INPUT_POST,
                'id',
                FILTER_VALIDATE_INT
            );

            if (!$id) {
                throw new RuntimeException('ID tidak valid.');
            }

            $stmt = $pdo->prepare(
                "DELETE FROM karyawan WHERE id = ?"
            );
            $stmt->execute([$id]);

            header('Location: index.php?status=deleted');
            exit;
        }
    } catch (RuntimeException $ex) {
        $error = $ex->getMessage();
    } catch (PDOException $ex) {
        $error = 'Operasi gagal. Periksa data dan database.';
    }
}

// AMBIL DATA YANG AKAN DIEDIT
if ($editId) {
    $stmt = $pdo->prepare(
        "SELECT * FROM karyawan WHERE id = ?"
    );
    $stmt->execute([$editId]);
    $editData = $stmt->fetch();
}

// TAMPILKAN SELURUH DATA
$stmt = $pdo->query(
    "SELECT * FROM karyawan ORDER BY id DESC"
);
$karyawan = $stmt->fetchAll();

function nilai($data, $kolom)
{
    return e($data[$kolom] ?? '');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1">
    <title>Data Karyawan - Staff IT</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 24px;
            color: #222;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .panel {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        h1 {
            color: #1d4ed8;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin: 6px 0 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button,
        .btn {
            display: inline-block;
            border: 0;
            padding: 9px 13px;
            border-radius: 5px;
            background: #2563eb;
            color: white;
            cursor: pointer;
            text-decoration: none;
        }

        .hapus {
            background: #dc2626;
        }

        .batal {
            background: #6b7280;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #eff6ff;
        }

        .pesan {
            color: #15803d;
        }

        .error {
            color: #b91c1c;
        }

        .aksi {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .aksi form {
            margin: 0;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .panel {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <h1>Data Karyawan</h1>
        <p>Aplikasi Manajemen Karyawan - Staff IT</p>

        <div class="panel">
            <h2><?= $editData ? 'Edit Karyawan' : 'Tambah Karyawan' ?></h2>

            <?php if (isset($pesan[$status])): ?>
                <p class="pesan"><?= e($pesan[$status]) ?></p>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <input type="hidden" name="aksi"
                    value="<?= $editData ? 'edit' : 'tambah' ?>">

                <?php if ($editData): ?>
                    <input type="hidden" name="id"
                        value="<?= e($editData['id']) ?>">
                <?php endif; ?>

                <label>NIK</label>
                <input name="nik" required maxlength="20"
                    value="<?= nilai($editData, 'nik') ?>">

                <label>Nama</label>
                <input name="nama" required maxlength="100"
                    value="<?= nilai($editData, 'nama') ?>">

                <label>Jabatan</label>
                <input name="jabatan" required maxlength="100"
                    value="<?= nilai($editData, 'jabatan') ?>">

                <label>Departemen</label>
                <input name="departemen" required maxlength="100"
                    value="<?= nilai($editData, 'departemen') ?>">

                <button type="submit">
                    <?= $editData ? 'Simpan Perubahan' : 'Tambah Data' ?>
                </button>

                <?php if ($editData): ?>
                    <a class="btn batal" href="index.php">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="panel">
            <h2>Daftar Karyawan</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Departemen</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!$karyawan): ?>
                            <tr>
                                <td colspan="6">Belum ada data karyawan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($karyawan as $i => $row): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= e($row['nik']) ?></td>
                                    <td><?= e($row['nama']) ?></td>
                                    <td><?= e($row['jabatan']) ?></td>
                                    <td><?= e($row['departemen']) ?></td>
                                    <td>
                                        <div class="aksi">
                                            <a class="btn"
                                                href="index.php?edit=<?= e($row['id']) ?>">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                onsubmit="return confirm('Hapus data ini?')">
                                                <input type="hidden" name="aksi"
                                                    value="hapus">
                                                <input type="hidden" name="id"
                                                    value="<?= e($row['id']) ?>">
                                                <button class="hapus" type="submit">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>

</html>