<?php

// Start session and include required files
session_start();
require_once 'config.php';      // Database connection
require_once 'log_action.php';  // Logging functionality

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle message form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $message = $_POST['message'];
    
    // VULNERABLE CODE: SQL Injection and XSS possible here!
    $query = "INSERT INTO messages (user_id, message) VALUES ($user_id, '$message')";
    mysqli_query($conn, $query);
    
    // Log the message
    log_action($conn, $_SESSION['user_id'], $_SESSION['username'], "Sent a message: $message");
    
    $success = "Message sent successfully!";
}

// Get all messages
// VULNERABLE CODE: No access control - all users can see all messages
$query = "SELECT m.*, u.username FROM messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC";
$messages = mysqli_query($conn, $query);



require_once 'header.php';
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
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Message Form -->
            <form method="POST" class="message-form">
                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea 
                        id="message" 
                        name="message" 
                        placeholder="Type your message here..." 
                        required
                        rows="4"
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
                        <span class="message-author"><?php echo $msg['username']; ?></span>
                        <span class="message-time"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></span>
                    </div>
                    <div class="message-content">
                        <?php echo $msg['message']; ?>
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
        color: var(--success);
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
        background: #cbd5e1;
        border-radius: 3px;
    }

    /* Message item styling */
    .message-item {
        background: var(--background);
        padding: 1rem;
        border-radius: 8px;
        transition: transform 0.2s ease;
    }

    .message-item:hover {
        transform: translateX(4px);
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .message-author {
        font-weight: 600;
        color: var(--text);
    }

    .message-time {
        font-size: 0.875rem;
        color: var(--text-light);
    }

    .message-content {
        color: var(--text);
        line-height: 1.5;
    }

    @media (max-width: 768px) {
        .message-page {
            padding: 1rem 0;
        }

        .page-header h1 {
            font-size: 1.5rem;
        }

        .message-grid {
            grid-template-columns: 1fr;
        }

        .message-card {
            padding: 1rem;
        }
    }
</style>

<?php require_once 'footer.php'; ?> 