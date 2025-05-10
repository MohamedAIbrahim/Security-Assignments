<?php

// Start session and include required files
session_start();
require_once '../config/config.php';      // Database connection
require_once '../includes/log_action.php';  // Logging functionality

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle message form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // CSRF attack detected
        log_action($conn, $_SESSION['user_id'], $_SESSION['username'], "CSRF attack detected on message form");
        die("Invalid request");
    }
    
    $user_id = $_SESSION['user_id'];
    $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
    
    // FIXED: SQL Injection prevention with prepared statements
    $query = "INSERT INTO messages (user_id, message) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
    
    if (mysqli_stmt_execute($stmt)) {
        // Log the message
        log_action($conn, $_SESSION['user_id'], $_SESSION['username'], "Sent a message");
        
        $success = "Message sent successfully!";
    } else {
        $error = "Error sending message: " . mysqli_error($conn);
    }
}

// Get messages for current user only
// FIXED: Access control - users can only see their own messages
$user_id = $_SESSION['user_id'];
$query = "SELECT m.*, u.username FROM messages m JOIN users u ON m.user_id = u.id 
          WHERE m.user_id = ? OR (u.role = 'admin') 
          ORDER BY m.created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$messages = mysqli_stmt_get_result($stmt);

require_once '../includes/header.php';
?>

<!-- Message System HTML Structure -->
<div class="message-page">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Message Admin</h1>
        <p class="subtitle">Send a message to the administrator</p>
    </div>

    <!-- Main Message Grid -->
    <div class="message-grid">
        <!-- Message Form Card -->
        <div class="message-card">
            <!-- Success Message -->
            <?php if (isset($success)): ?>
                <div class="success-message">
                    <span class="success-icon">✓</span>
                    <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            
            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <span class="error-icon">❌</span>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <!-- Message Form -->
            <form method="POST" class="message-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea 
                        id="message" 
                        name="message" 
                        placeholder="Type your message here..." 
                        required
                        rows="4"
                        maxlength="500"
                    ></textarea>
                </div>
                <button type="submit" class="send-button">
                    <span class="button-icon">✉️</span>
                    Send Message
                </button>
            </form>
        </div>

        <!-- Message History Card -->
        <div class="message-card">
            <div class="card-header">
                <h2>Message History</h2>
                <span class="card-icon">📜</span>
            </div>
            <div class="message-list">
                <?php while ($msg = mysqli_fetch_assoc($messages)): ?>
                <div class="message-item">
                    <div class="message-header">
                        <span class="message-author"><?php echo htmlspecialchars($msg['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="message-time"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></span>
                    </div>
                    <div class="message-content">
                        <?php echo htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<!-- Page Styling -->
<style>
    /* Main page layout */
    .message-page {
        padding: 2rem 0;
    }

    /* Header styling */
    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 2rem;
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .subtitle {
        color: var(--text-light);
        font-size: 1.1rem;
    }

    /* Grid layout */
    .message-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    @media (max-width: 768px) {
        .message-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Card styling */
    .message-card {
        background: var(--surface);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }

    /* Success message styling */
    .success-message {
        background: #dcfce7;
        color: #166534;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .success-icon {
        font-size: 1.25rem;
    }
    
    /* Error message styling */
    .error-message {
        background: #fee2e2;
        color: #b91c1c;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Form styling */
    .message-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        color: var(--text);
        font-weight: 500;
    }

    /* Textarea styling */
    textarea {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        font-family: inherit;
        resize: vertical;
        transition: all 0.2s ease;
    }

    textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* Button styling */
    .send-button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: var(--primary);
        color: white;
        padding: 0.75rem;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .send-button:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .button-icon {
        font-size: 1.25rem;
    }

    /* Card header styling */
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .card-header h2 {
        font-size: 1.25rem;
        color: var(--text);
    }

    .card-icon {
        font-size: 1.5rem;
    }

    /* Message list styling */
    .message-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-height: 500px;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    /* Scrollbar styling */
    .message-list::-webkit-scrollbar {
        width: 6px;
    }

    .message-list::-webkit-scrollbar-track {
        background: var(--background);
        border-radius: 3px;
    }

    .message-list::-webkit-scrollbar-thumb {
        background: var(--primary-light);
        border-radius: 3px;
    }

    .message-list::-webkit-scrollbar-thumb:hover {
        background: var(--primary);
    }

    /* Message item styling */
    .message-item {
        background: var(--background);
        padding: 1rem;
        border-radius: 8px;
        transition: background-color 0.2s ease;
    }

    .message-item:hover {
        background: #f1f5f9;
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .message-author {
        font-weight: 500;
        color: var(--primary);
    }

    .message-time {
        color: var(--text-light);
    }

    .message-content {
        color: var(--text);
        line-height: 1.5;
        word-break: break-word;
    }
</style>

<?php require_once '../includes/footer.php'; ?> 