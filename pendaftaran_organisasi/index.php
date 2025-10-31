<?php
require_once 'config.php';
session_start();

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'id';
if (!in_array($lang, ['id'])) $lang = 'id';

// Daftar organisasi
$organizations = ['OSIS', 'Taruna', 'Rohis', 'PMR', 'PALA', 'Dewan Ambalan'];

// Konfigurasi logo sekolah
$logoConfig = [
    'type' => 'file', // 'file' atau 'url'
    'path' => 'img/logo.pjg.jpg', // Path ke file logo lokal
    'url' => '', // URL logo jika menggunakan link eksternal
    'alt' => 'Logo Sekolah',
    'width' => '80px' // Lebar logo
];

// Teks multilingual
$texts = [
    'id' => [
        'welcome' => 'Pendaftaran Organisasi Sekolah',
        'name' => 'Nama',
        'age' => 'Umur',
        'grade' => 'Kelas',
        'gender' => 'Jenis Kelamin',
        'male' => 'Laki-laki',
        'female' => 'Perempuan',
        'organization' => 'Pilih Organisasi',
        'submit' => 'Daftarkan',
        'success' => 'Berhasil didaftarkan!',
        'error_name' => 'Nama kosong!',
        'error_age' => 'Umur salah!',
        'error_grade' => 'Kelas kosong!',
        'error_gender' => 'Jenis kelamin harus dipilih!',
        'error_org' => 'Organisasi harus dipilih!',
        'admin_login' => 'Login Admin',
        'view_results' => 'Lihat Hasil Pendaftaran',
        'registration_form' => 'Form Pendaftaran',
        'school_name' => 'SMK PGPRI 2 PONOROGO',
        'academic_year' => 'Tahun Ajaran 2024/2025',
        'deadline' => 'Batas Pendaftaran: 30 November 2024'
    ],
];
$text = $texts[$lang];

// Proses pendaftaran
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_registration'])) {
    $name = trim($_POST['name'] ?? '');
    $age = $_POST['age'] ?? '';
    $grade = trim($_POST['grade'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $org = $_POST['organization'] ?? '';

    // Validasi
    if (empty($name)) $error = $text['error_name'];
    elseif (!is_numeric($age) || $age <= 0) $error = $text['error_age'];
    elseif (empty($grade)) $error = $text['error_grade'];
    elseif (empty($gender) || !in_array($gender, ['L', 'P'])) $error = $text['error_gender'];
    elseif (empty($org) || !in_array($org, $organizations)) $error = $text['error_org'];
    else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("INSERT INTO students (name, age, grade, gender, organization) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $age, $grade, $gender, $org]);
            
            // Redirect ke halaman hasil dengan parameter success
            header('Location: result.php?success=1&name=' . urlencode($name) . '&lang=' . $lang);
            exit;
            
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $text['welcome']; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Header Styles */
        .registration-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .header-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .school-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo-container {
            flex-shrink: 0;
        }
        
        .school-logo {
            width: <?php echo $logoConfig['width']; ?>;
            height: auto;
            border-radius: 8px;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .school-details {
            flex-grow: 1;
        }
        
        .school-name {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }
        
        .academic-year {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .language-selector {
            display: flex;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            padding: 8px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        
        .lang-btn {
            padding: 6px 12px;
            border: none;
            background: transparent;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .lang-btn.active {
            background: rgba(255,255,255,0.3);
            font-weight: bold;
        }
        
        .lang-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .header-bottom {
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 15px;
        }
        
        .registration-title {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .deadline-info {
            font-size: 1.1rem;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            backdrop-filter: blur(10px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .school-info {
                flex-direction: column;
                text-align: center;
            }
            
            .school-name {
                font-size: 1.5rem;
            }
            
            .registration-title {
                font-size: 1.8rem;
            }
            
            .language-selector {
                margin-top: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .registration-header {
                padding: 20px 0;
            }
            
            .school-name {
                font-size: 1.3rem;
            }
            
            .registration-title {
                font-size: 1.5rem;
            }
            
            .deadline-info {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body class="index-body">
<div class="container index-container">
    
    <!-- Header Form Pendaftaran -->
    <header class="registration-header">
        <div class="header-container">
            <div class="header-top">
                <div class="school-info">
                    <div class="logo-container">
                        <?php if ($logoConfig['type'] === 'file' && file_exists($logoConfig['path'])): ?>
                            <img src="<?php echo $logoConfig['path']; ?>" alt="<?php echo $logoConfig['alt']; ?>" class="school-logo">
                        <?php elseif ($logoConfig['type'] === 'url' && !empty($logoConfig['url'])): ?>
                            <img src="<?php echo $logoConfig['url']; ?>" alt="<?php echo $logoConfig['alt']; ?>" class="school-logo">
                        <?php else: ?>
                            <!-- Fallback jika logo tidak ditemukan -->
                            <div style="width: <?php echo $logoConfig['width']; ?>; height: 80px; background: white; color: #667eea; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-weight: bold; font-size: 0.8rem;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="school-details">
                        <div class="school-name"><?php echo $text['school_name']; ?></div>
                        <div class="academic-year"><?php echo $text['academic_year']; ?></div>
                    </div>
                </div>
                
            </div>
            
            <div class="header-bottom">
                <h1 class="registration-title"><?php echo $text['welcome']; ?></h1>
                <div class="deadline-info"><?php echo $text['deadline']; ?></div>
            </div>
        </div>
    </header>
    
    <?php if ($error): ?>
        <div class="error">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="form-section">
        <div class="form-title"> <?php echo $text['registration_form']; ?></div>
        <form method="post">
            <div class="form-group">
                <label class="form-label" for="name"> <?php echo $text['name']; ?>:</label>
                <input class="form-input" type="text" id="name" name="name" placeholder="Masukkan nama lengkap" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="age"> <?php echo $text['age']; ?>:</label>
                <input class="form-input" type="number" id="age" name="age" min="10" max="25" placeholder="Umur antara 10-25" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="grade"> <?php echo $text['grade']; ?>:</label>
                <input class="form-input" type="text" id="grade" name="grade" placeholder="Contoh: X TKJ 1, XI TAB 4" required>
            </div>
            
            <div class="form-group">
                <label class="form-label"> <?php echo $text['gender']; ?>:</label>
                <div class="gender-group">
                    <div class="gender-option">
                        <input type="radio" id="male" name="gender" value="L" required>
                        <label for="male">♂️ <?php echo $text['male']; ?></label>
                    </div>
                    <div class="gender-option">
                        <input type="radio" id="female" name="gender" value="P">
                        <label for="female">♀️ <?php echo $text['female']; ?></label>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="organization"> <?php echo $text['organization']; ?>:</label>
                <select class="form-select" id="organization" name="organization" required>
                    <option value="">-- <?php echo $text['organization']; ?> --</option>
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?php echo $org; ?>"><?php echo $org; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button class="form-button" type="submit" name="submit_registration"> <?php echo $text['submit']; ?></button>
        </form>
    </div>

    <div class="results-link">
        <a href="result.php?lang=<?php echo $lang; ?>"> <?php echo $text['view_results']; ?></a>
    </div>
    
    <div class="admin-link">
        <a href="admin_login.php?lang=<?php echo $lang; ?>"> <?php echo $text['admin_login']; ?></a>
    </div>
</div>

<script>
// Auto focus pada field pertama
document.getElementById('name').focus();

// Animasi untuk form elements
const formElements = document.querySelectorAll('.form-input, .form-select');
formElements.forEach(element => {
    element.addEventListener('focus', () => {
        element.parentElement.style.transform = 'translateY(-2px)';
    });
    element.addEventListener('blur', () => {
        element.parentElement.style.transform = 'translateY(0)';
    });
});
</script>
</body>
</html>