<?php
require_once 'config.php';
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

$organizations = ['OSIS', 'Taruna', 'Rohis', 'PMR', 'PALA'];
$message = '';
$editing_student = null;

// Proses CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo = getDBConnection();
    
    // Edit data - Mode Edit
    if (isset($_POST['edit_mode'])) {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
                $stmt->execute([$id]);
                $editing_student = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($editing_student) {
                    $message = "✏️ Edit mode: " . htmlspecialchars($editing_student['name']);
                }
            } catch(PDOException $e) {
                $message = "❌ Error: " . $e->getMessage();
            }
        }
    }
    
    // Update data
    if (isset($_POST['update_student'])) {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $age = $_POST['age'] ?? '';
        $grade = trim($_POST['grade'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $org = $_POST['organization'] ?? '';
        
        if (!empty($id) && !empty($name) && !empty($age) && !empty($grade) && !empty($gender) && !empty($org)) {
            try {
                $stmt = $pdo->prepare("UPDATE students SET name=?, age=?, grade=?, gender=?, organization=? WHERE id=?");
                $stmt->execute([$name, $age, $grade, $gender, $org, $id]);
                $message = "✅ Data siswa berhasil diupdate!";
                $editing_student = null; // Keluar dari mode edit
            } catch(PDOException $e) {
                $message = "❌ Error: " . $e->getMessage();
            }
        } else {
            $message = "❌ Semua field harus diisi!";
        }
    }
    
    // Hapus data
    if (isset($_POST['delete_student'])) {
        $id = $_POST['id'] ?? '';
        
        if (!empty($id)) {
            try {
                // Ambil nama siswa sebelum dihapus untuk pesan
                $stmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
                $stmt->execute([$id]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $pdo->prepare("DELETE FROM students WHERE id=?");
                $stmt->execute([$id]);
                $message = "✅ Data siswa '" . htmlspecialchars($student['name']) . "' berhasil dihapus!";
            } catch(PDOException $e) {
                $message = "❌ Error: " . $e->getMessage();
            }
        }
    }
    
    // Cancel edit
    if (isset($_POST['cancel_edit'])) {
        $editing_student = null;
        $message = "❌ Edit dibatalkan";
    }
}

// Ambil data siswa
$pdo = getDBConnection();
$students = $pdo->query("SELECT * FROM students ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Pendaftaran Organisasi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-panel-container">
    <div class="logout-link">
        <span>Halo, <strong><?php echo $_SESSION['admin_username']; ?></strong></span> |
        <a href="?logout=1">🚪 Logout</a> | 
        <a href="index.php">🏠 Kembali ke Form</a>
    </div>
    
    <h1 class="admin-panel-title"> Admin Panel - Pendaftaran Organisasi Sekolah</h1>
    
    <?php if ($message): ?>
        <div class="admin-message <?php echo strpos($message, '✅') !== false ? 'message-success' : 'message-error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <!-- Statistik -->
    <div class="admin-stats">
        <div class="admin-stat-card">
            <div class="admin-stat-number"><?php echo count($students); ?></div>
            <div>Total Siswa</div>
        </div>
        <?php
        $gender_count = array_count_values(array_column($students, 'gender'));
        ?>
        <div class="admin-stat-card">
            <div class="admin-stat-number"><?php echo $gender_count['L'] ?? 0; ?></div>
            <div>Siswa Laki-laki</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-number"><?php echo $gender_count['P'] ?? 0; ?></div>
            <div>Siswa Perempuan</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-number"><?php echo count(array_unique(array_column($students, 'organization'))); ?></div>
            <div>Organisasi Terdaftar</div>
        </div>
    </div>
    
    <!-- Form Edit -->
    <?php if ($editing_student): ?>
    <div class="edit-form">
        <h2>✏️ Edit Data Siswa</h2>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo $editing_student['id']; ?>">
            
            <div class="admin-form-group">
                <label class="admin-form-label" for="edit_name">Nama:</label>
                <input class="admin-form-input" type="text" id="edit_name" name="name" value="<?php echo htmlspecialchars($editing_student['name']); ?>" required>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label" for="edit_age">Umur:</label>
                <input class="admin-form-input" type="number" id="edit_age" name="age" min="1" max="25" value="<?php echo $editing_student['age']; ?>" required>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label" for="edit_grade">Kelas:</label>
                <input class="admin-form-input" type="text" id="edit_grade" name="grade" value="<?php echo htmlspecialchars($editing_student['grade']); ?>" required>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label">Jenis Kelamin:</label>
                <div class="admin-gender-group">
                    <div class="admin-gender-option">
                        <input type="radio" id="edit_male" name="gender" value="L" <?php echo $editing_student['gender'] == 'L' ? 'checked' : ''; ?> required>
                        <label for="edit_male">Laki-laki</label>
                    </div>
                    <div class="admin-gender-option">
                        <input type="radio" id="edit_female" name="gender" value="P" <?php echo $editing_student['gender'] == 'P' ? 'checked' : ''; ?>>
                        <label for="edit_female">Perempuan</label>
                    </div>
                </div>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label" for="edit_organization">Organisasi:</label>
                <select class="admin-form-select" id="edit_organization" name="organization" required>
                    <option value="">-- Pilih Organisasi --</option>
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?php echo $org; ?>" <?php echo $editing_student['organization'] == $org ? 'selected' : ''; ?>>
                            <?php echo $org; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-buttons">
                <button class="admin-form-button" type="submit" name="update_student">💾 Update Data</button>
                <button class="admin-form-button cancel-btn" type="submit" name="cancel_edit">❌ Batal</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Daftar Siswa -->
    <div class="admin-section">
        <h2>Daftar Siswa Terdaftar (<?php echo count($students); ?> siswa)</h2>
        <?php if (empty($students)): ?>
            <p style="text-align: center; padding: 20px; color: #666;">Tidak ada data siswa.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Umur</th>
                        <th>Kelas</th>
                        <th>Jenis Kelamin</th>
                        <th>Organisasi</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $index => $student): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($student['name']); ?></strong></td>
                        <td><?php echo $student['age']; ?> tahun</td>
                        <td><?php echo htmlspecialchars($student['grade']); ?></td>
                        <td><?php echo $student['gender'] == 'L' ? ' Laki-laki' : ' Perempuan'; ?></td>
                        <td><span style="background: #e6f7ff; padding: 4px 8px; border-radius: 3px;"><?php echo htmlspecialchars($student['organization']); ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($student['created_at'])); ?></td>
                        <td>
                            <!-- Form Edit -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                <button class="admin-form-button edit-btn" type="submit" name="edit_mode" title="Edit data">
                                    ✏️ Edit
                                </button>
                            </form>
                            
                            <!-- Form Hapus -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                <button class="admin-form-button delete-btn" type="submit" name="delete_student" 
                                        onclick="return confirm('Yakin ingin menghapus data <?php echo htmlspecialchars($student['name']); ?>?')"
                                        title="Hapus data">
                                    🗑️ Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}
?>
</body>
</html>