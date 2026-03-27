<?php
require 'config/db.php';

$status = 'loading';
$message = '';
$projectPath = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);

if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE verification_code = ?');
    $stmt->execute([$verification_code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stmt = $pdo->prepare('UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?');
        $stmt->execute([$user['id']]);
        
        $status = 'success';
        $message = "Email verified successfully! You can now log in.";
    } else {
        $status = 'error';
        $message = "Invalid or expired verification code.";
    }
} else {
    header("Location: index.php");
    exit;
}

include 'includes/header.php'; 
?>

<main class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
    <div class="bg-white p-10 rounded-3xl shadow-2xl shadow-slate-200 w-full max-w-md border border-slate-100 text-center">
        
        <?php if ($status === 'success'): ?>
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">
                ✓
            </div>
            <h2 class="text-3xl font-bold text-slate-900 mb-2">Verified!</h2>
            <p class="text-slate-500 mb-8"><?= $message ?></p>
            
            <a href="<?= $projectPath ?>login.php" 
               class="inline-block w-full bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                Go to Login Now
            </a>

        <?php else: ?>
            <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">
                ✕
            </div>
            <h2 class="text-3xl font-bold text-slate-900 mb-2">Oops!</h2>
            <p class="text-slate-500 mb-8"><?= $message ?></p>
            
            <a href="<?= $projectPath ?>index.php" 
               class="inline-block w-full bg-slate-900 text-white py-4 rounded-xl font-bold hover:bg-black transition-all">
                Back to Home
            </a>
        <?php endif; ?>

    </div>
</main>

<?php if ($status === 'success'): ?>
<script>
    // JS Redirect after 5 seconds
    setTimeout(() => {
        const path = "<?= $projectPath ?>login.php";
        console.log("Redirecting to:", path);
        window.location.href = path;
    }, 5000);
</script>
<?php endif; ?>

