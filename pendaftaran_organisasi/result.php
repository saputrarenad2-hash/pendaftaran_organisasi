<?php
require_once 'config.php';
session_start();

// Pilihan bahasa
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'id';
if (!in_array($lang, ['id', 'en'])) $lang = 'id';

// Teks multilingual
$texts = [
    'id' => [
        'title' => 'Hasil Pendaftaran Organisasi',
        'back_to_form' => 'Kembali ke Form Pendaftaran',
        'admin_login' => 'Login Admin',
        'registration_results' => 'Hasil Pendaftaran',
        'total_students' => 'Total Pendaftar',
        'no_data' => 'Belum ada pendaftar',
        'name' => 'Nama',
        'age' => 'Umur',
        'grade' => 'Kelas',
        'gender' => 'Jenis Kelamin',
        'male' => 'Laki-laki',
        'female' => 'Perempuan',
        'organization' => 'Organisasi',
        'registration_date' => 'Tanggal Daftar',
        'success_message' => 'Selamat! Pendaftaran berhasil!',
        'new_registration' => 'Pendaftaran Baru!',
        'statistics' => 'Statistik Pendaftaran',
        'by_organization' => 'Berdasarkan Organisasi',
        'by_gender' => 'Berdasarkan Jenis Kelamin',
        'students_count' => 'jumlah siswa'
    ]
];
$text = $texts[$lang];

// Cek jika ada pendaftaran berhasil
$success = isset($_GET['success']);
$registered_name = isset($_GET['name']) ? urldecode($_GET['name']) : '';

// Ambil data dari database
try {
    $pdo = getDBConnection();
    
    // Data semua siswa
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
    $all_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_students = count($all_students);
    
    // Statistik organisasi
    $org_stmt = $pdo->query("SELECT organization, COUNT(*) as count FROM students GROUP BY organization ORDER BY count DESC");
    $org_stats = $org_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Statistik gender
    $gender_stmt = $pdo->query("SELECT gender, COUNT(*) as count FROM students GROUP BY gender");
    $gender_stats = $gender_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Data terbaru (5 data terbaru)
    $recent_stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
    $recent_students = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $all_students = [];
    $total_students = 0;
    $org_stats = [];
    $gender_stats = [];
    $recent_students = [];
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $text['title']; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1 class="result-title"> <?php echo $text['title']; ?></h1>
    <div class="result-subtitle"><?php echo $text['registration_results']; ?></div>

    <?php if ($success && $registered_name): ?>
        <div class="success-message">
            <h2>🎉 <?php echo $text['success_message']; ?></h2>
            <p><strong><?php echo htmlspecialchars($registered_name); ?></strong> <?php echo $lang == 'id' ? 'telah berhasil terdaftar!' : 'has been successfully registered!'; ?></p>
        </div>
    <?php endif; ?>

    <div class="content-wrapper">
        <!-- Statistik -->
        <div class="stats-section">
            <h2 class="section-title"> <?php echo $text['statistics']; ?></h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $total_students; ?></span>
                    <span class="stat-label"><?php echo $text['total_students']; ?></span>
                </div>
                <?php
                $male_count = 0;
                $female_count = 0;
                foreach ($gender_stats as $stat) {
                    if ($stat['gender'] == 'L') $male_count = $stat['count'];
                    if ($stat['gender'] == 'P') $female_count = $stat['count'];
                }
                ?>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $male_count; ?></span>
                    <span class="stat-label"><?php echo $text['male']; ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $female_count; ?></span>
                    <span class="stat-label"><?php echo $text['female']; ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo count($org_stats); ?></span>
                    <span class="stat-label">Organisasi</span>
                </div>
            </div>

            <!-- Chart Organisasi -->
            <div class="chart-container">
                <div class="chart-title"> <?php echo $text['by_organization']; ?></div>
                <?php if (!empty($org_stats)): ?>
                    <?php 
                    $max_count = max(array_column($org_stats, 'count'));
                    foreach ($org_stats as $org): 
                        $percentage = ($org['count'] / $max_count) * 100;
                    ?>
                        <div class="chart-bar">
                            <div class="chart-label"><?php echo htmlspecialchars($org['organization']); ?></div>
                            <div class="chart-bar-inner">
                                <div class="chart-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <div class="chart-count"><?php echo $org['count']; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 20px;"><?php echo $text['no_data']; ?></p>
                <?php endif; ?>
            </div>

            <!-- Chart Gender -->
            <div class="chart-container">
                <div class="chart-title"> <?php echo $text['by_gender']; ?></div>
                <?php if ($male_count > 0 || $female_count > 0): ?>
                    <?php 
                    $gender_total = $male_count + $female_count;
                    $male_percentage = $gender_total > 0 ? ($male_count / $gender_total) * 100 : 0;
                    $female_percentage = $gender_total > 0 ? ($female_count / $gender_total) * 100 : 0;
                    ?>
                    <div class="chart-bar">
                        <div class="chart-label"><?php echo $text['male']; ?></div>
                        <div class="chart-bar-inner">
                            <div class="chart-bar-fill" style="width: <?php echo $male_percentage; ?>%"></div>
                        </div>
                        <div class="chart-count"><?php echo $male_count; ?></div>
                    </div>
                    <div class="chart-bar">
                        <div class="chart-label"><?php echo $text['female']; ?></div>
                        <div class="chart-bar-inner">
                            <div class="chart-bar-fill" style="width: <?php echo $female_percentage; ?>%"></div>
                        </div>
                        <div class="chart-count"><?php echo $female_count; ?></div>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 20px;"><?php echo $text['no_data']; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pendaftaran Terbaru -->
        <div class="recent-section">
            <h2 class="section-title"> <?php echo $lang == 'id' ? 'Pendaftaran Terbaru' : 'Recent Registrations'; ?></h2>
            
            <?php if (empty($recent_students)): ?>
                <div class="empty-state">
                    <div></div>
                    <p><?php echo $text['no_data']; ?></p>
                </div>
            <?php else: ?>
                <div class="student-list">
                    <?php foreach ($recent_students as $student): ?>
                        <div class="student-item">
                            <div class="student-name">
                                <?php echo htmlspecialchars($student['name']); ?>
                                <?php if ($success && $registered_name == $student['name']): ?>
                                    <span class="new-badge">✨ <?php echo $text['new_registration']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="student-details">
                                <span><?php echo $student['age']; ?> <?php echo $text['age']; ?></span> • 
                                <span><?php echo htmlspecialchars($student['grade']); ?></span> • 
                                <span><?php echo $student['gender'] == 'L' ? $text['male'] : $text['female']; ?></span>
                            </div>
                            <div class="student-org">
                                <?php echo htmlspecialchars($student['organization']); ?>
                            </div>
                            <div class="timestamp">
                                 <?php echo date('d/m/Y H:i', strtotime($student['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="navigation-links">
        <a href="index.php?lang=<?php echo $lang; ?>" class="nav-link">← <?php echo $text['back_to_form']; ?></a>
        <a href="admin_login.php?lang=<?php echo $lang; ?>" class="nav-link"> <?php echo $text['admin_login']; ?></a>
    </div>
</div>

<script>
// Animate chart bars on page load
document.addEventListener('DOMContentLoaded', function() {
    const bars = document.querySelectorAll('.chart-bar-fill');
    bars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });
});

// Remove new badge after 5 seconds
setTimeout(() => {
    const badges = document.querySelectorAll('.new-badge');
    badges.forEach(badge => {
        badge.style.opacity = '0.5';
    });
}, 5000);
</script>
</body>
</html>