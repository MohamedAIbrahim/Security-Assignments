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
    
    // VULNERABLE CODE: SQL Injection possible here!
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    // If user found in database
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
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
        $error = "Invalid credentials";
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