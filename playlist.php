<?php
// ==========================================
// 1. KONFIGURASI DATABASE & KONEKSI
// ==========================================
$host = "localhost";
$db_name = "db_musik";
$user = "root";
$pass = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Gagal: " . $e->getMessage());
}

// ==========================================
// 2. MODEL (Logika Data)
// ==========================================
class MusikModel {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll() {
        return $this->db->query("SELECT * FROM playlist ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM playlist WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function store($j, $p, $g, $t) {
        return $this->db->prepare("INSERT INTO playlist (judul_lagu, penyanyi, genre, tahun_rilis) VALUES (?,?,?,?)")->execute([$j, $p, $g, $t]);
    }
    public function update($id, $j, $p, $g, $t) {
        return $this->db->prepare("UPDATE playlist SET judul_lagu=?, penyanyi=?, genre=?, tahun_rilis=? WHERE id=?")->execute([$j, $p, $g, $t, $id]);
    }
    public function delete($id) {
        return $this->db->prepare("DELETE FROM playlist WHERE id = ?")->execute([$id]);
    }
}

// ==========================================
// 3. CONTROLLER (Logika Navigasi)
// ==========================================
$model = new MusikModel($db);
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

if ($action == 'save') {
    $model->store($_POST['judul'], $_POST['penyanyi'], $_POST['genre'], $_POST['tahun']);
    header("Location: " . $_SERVER['PHP_SELF']);
} elseif ($action == 'update_data') {
    $model->update($id, $_POST['judul'], $_POST['penyanyi'], $_POST['genre'], $_POST['tahun']);
    header("Location: " . $_SERVER['PHP_SELF']);
} elseif ($action == 'delete') {
    $model->delete($id);
    header("Location: " . $_SERVER['PHP_SELF']);
}

// ==========================================
// 4. VIEW (Tampilan HTML)
// ==========================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Manajemen List Musik</title>
    <style>
        body { font-family: sans-serif; margin: 40px; background: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #333; color: #fff; }
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 3px; }
        .btn-add { background: green; color: white; }
        .btn-edit { background: orange; color: white; }
        .btn-delete { background: red; color: white; }
    </style>
</head>
<body>

<?php if ($action == 'index'): ?>
    <h2>Koleksi Musik Saya</h2>
    <a href="?action=create" class="btn btn-add">+ Tambah Lagu</a><br><br>
    <table>
        <tr>
            <th>Judul</th><th>Penyanyi</th><th>Genre</th><th>Tahun</th><th>Aksi</th>
        </tr>
        <?php foreach ($model->getAll() as $m): ?>
        <tr>
            <td><?= $m['judul_lagu'] ?></td>
            <td><?= $m['penyanyi'] ?></td>
            <td><?= $m['genre'] ?></td>
            <td><?= $m['tahun_rilis'] ?></td>
            <td>
                <a href="?action=edit&id=<?= $m['id'] ?>" class="btn btn-edit">Edit</a>
                <a href="?action=delete&id=<?= $m['id'] ?>" class="btn btn-delete" onclick="return confirm('Hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

<?php elseif ($action == 'create' || $action == 'edit'): 
    $data = ($action == 'edit') ? $model->getById($id) : null;
    $target = ($action == 'edit') ? "?action=update_data&id=$id" : "?action=save";
?>
    <h2><?= ucfirst($action) ?> Lagu</h2>
    <form action="<?= $target ?>" method="POST">
        <label>Judul:</label><br>
        <input type="text" name="judul" value="<?= $data['judul_lagu'] ?? '' ?>" required><br><br>
        <label>Penyanyi:</label><br>
        <input type="text" name="penyanyi" value="<?= $data['penyanyi'] ?? '' ?>" required><br><br>
        <label>Genre:</label><br>
        <input type="text" name="genre" value="<?= $data['genre'] ?? '' ?>"><br><br>
        <label>Tahun:</label><br>
        <input type="number" name="tahun" value="<?= $data['tahun_rilis'] ?? '' ?>"><br><br>
        <button type="submit" class="btn btn-add">Simpan</button>
        <a href="?action=index">Batal</a>
    </form>
<?php endif; ?>

</body>
</html>