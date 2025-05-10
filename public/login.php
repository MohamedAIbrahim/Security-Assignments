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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get username and password from form
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // FIXED: Use prepared statements to prevent SQL injection
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // If user found in database
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // FIXED: Properly verify hashed password using password_verify
        if (password_verify($password, $user['password_hash'])) {
            // Store user information in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Log the successful login
            log_action($conn, $user['id'], $user['username'], 'Logged in');
            
            // FIXED: Update password hash if needed (using newer/stronger algorithm)
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password_hash = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $user['id']);
                mysqli_stmt_execute($update_stmt);
            }
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials";
        }
    } else {
        $error = "Invalid credentials";
    }
}


require_once '../includes/header.php';
?>

<!-- Login Form HTML -->
<div class="card">
    <h2>Bank Login</h2>
    <?php if (isset($error)) echo "<p class='error'>".htmlspecialchars($error, ENT_QUOTES, 'UTF-8')."</p>"; ?>
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
    <div class="register-link">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 