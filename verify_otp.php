<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

// No need to import PHPMailer here (not sending emails on this page)

$errors = [];
$success = false;

// Make sure the user has just registered
if (!isset($_SESSION['pending_verification'])) {
    die("No pending verification. Please register first. <a href='register.php'>Register here</a>");
}

$email = $_SESSION['pending_verification'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    if (empty($otp)) {
        $errors['otp'] = "OTP is required.";
    } else {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Fetch user with matching OTP and check expiry
            $stmt = $conn->prepare("SELECT id, otp, otp_expiry, is_verified 
                                    FROM barangay_officials 
                                    WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if ($user['is_verified'] == 1) {
                    $errors['otp'] = "This account is already verified. You can login.";
                } elseif ($user['otp'] !== $otp) {
                    $errors['otp'] = "Invalid OTP. Please try again.";
                } elseif (strtotime($user['otp_expiry']) < time()) {
                    $errors['otp'] = "Your OTP has expired. Please register again.";
                } else {
                    // ✅ Verify the account
                    $update = $conn->prepare("UPDATE barangay_officials 
                                               SET is_verified = 1, otp = NULL, otp_expiry = NULL 
                                               WHERE id = ?");
                    $update->execute([$user['id']]);

                    unset($_SESSION['pending_verification']);
                    $success = true;
                }
            } else {
                $errors['otp'] = "User not found.";
            }
        } catch (PDOException $e) {
            $errors['database'] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="verify_otp.css">
</head>
<body>
<div class="registration-container">
    <h1>OTP Verification</h1>
    <p>We sent a 6-digit OTP code to your email <strong><?php echo htmlspecialchars($email); ?></strong></p>

    <?php if ($success): ?>
        <div class="alert alert-success">
            ✅ Your account has been verified! <br>
            <a href="login.php">Login here</a>
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="registration-form">
            <label for="otp">Enter OTP:</label>
            <input type="text" name="otp" maxlength="6" required>
            <button type="submit">Verify</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
