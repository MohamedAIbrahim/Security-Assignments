<?php

// Start a new session to store user data
session_start();

// Include required files
require_once '../config/config.php';      // Database connection
require_once '../includes/log_action.php';  // Logging functionality

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// SECURITY FIX 1: IP Address Tracking
function getClientIP() {
    // Get user IP address
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// SECURITY FIX 2: Failed Login Attempts Tracking
function checkLoginAttempts($conn, $username, $ip) {
    // Check if there are too many failed login attempts
    $lockout_time = 15 * 60; // 15 minutes lockout
    $max_attempts = 3; // Max 3 failed attempts
    
    // Check for failed attempts from this IP or for this username within the lockout period
    $query = "SELECT COUNT(*) as attempts FROM login_attempts 
              WHERE (ip_address = '$ip' OR username = '$username') 
              AND success = 0 
              AND attempt_time > DATE_SUB(NOW(), INTERVAL $lockout_time SECOND)";
    
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    
    return $data['attempts'] >= $max_attempts;
}

function logLoginAttempt($conn, $username, $ip, $success) {
    // Log the login attempt to the database
    $success_val = $success ? 1 : 0;
    $query = "INSERT INTO login_attempts (username, ip_address, success, attempt_time) 
              VALUES ('$username', '$ip', $success_val, NOW())";
    
    mysqli_query($conn, $query);
}

// Ensure login_attempts table exists
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'login_attempts'");
if(mysqli_num_rows($check_table) == 0) {
    // Create table if it doesn't exist
    $create_table = "CREATE TABLE login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        success TINYINT(1) DEFAULT 0,
        attempt_time DATETIME NOT NULL
    )";
    mysqli_query($conn, $create_table);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get username and password from form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $ip = getClientIP();
    
    // SECURITY FIX 3: Account Lockout after multiple failed attempts
    if (checkLoginAttempts($conn, $username, $ip)) {
        $error = "Too many failed login attempts. Please try again later.";
    } else {
        // Keep the original SQL query to maintain the intentional SQL injection vulnerability
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($conn, $query);
        
        // If user found in database
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Log successful login
            logLoginAttempt($conn, $username, $ip, true);
            
            // Store user information in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // VULNERABLE CODE: Storing password in session is a security risk!
            $_SESSION['password'] = $password;
            
            // Log the successful login
            log_action($conn, $user['id'], $user['username'], 'Logged in');
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Log failed login attempt
            logLoginAttempt($conn, $username, $ip, false);
            $error = "Invalid credentials";
        }
    }
}


require_once '../includes/header.php';
?>

<!-- Login Form HTML -->
<div class="card">
    <h2>Bank Login</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <div class="form-group">
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <div class="form-group">
            <input type="submit" value="Login">
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 