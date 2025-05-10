<?php

// Start session and include required files
session_start();
require_once '../config/config.php';      // Database connection
require_once '../includes/log_action.php';  // Logging functionality

// VULNERABLE CODE: XSS possible here - username not sanitized
$username = $_SESSION['username'];

// Basic session check - VULNERABLE: Should check session validity
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's accounts from database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM accounts WHERE user_id = $user_id";
$accounts = mysqli_query($conn, $query);

// Handle money transfer form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['transfer'])) {
    // Get transfer details from form
    $from_account = $_POST['from_account'];
    $to_account = $_POST['to_account'];
    $amount = $_POST['amount'];
    
    // VULNERABLE CODE: SQL Injection possible here!

    $query = "UPDATE accounts SET balance = balance - $amount WHERE account_number = '$from_account'";
    mysqli_query($conn, $query);
    
    $query = "UPDATE accounts SET balance = balance + $amount WHERE account_number = '$to_account'";
    mysqli_query($conn, $query);
    
    // VULNERABLE CODE: Insecure data handling
    $query = "INSERT INTO transactions (from_account, to_account, amount) VALUES ('$from_account', '$to_account', $amount)";
    mysqli_query($conn, $query);
    
    // Log the transfer
    log_action($conn, $_SESSION['user_id'], $_SESSION['username'], "Transferred $amount from $from_account to $to_account");
    
    $success = "Transfer successful!";
}

// Include the header (contains navigation and styling)
require_once '../includes/header.php';
?>

<!-- Dashboard HTML Structure -->
<div class="dashboard">
    <!-- Welcome Section -->
    <div class="dashboard-header">
        <h1>Welcome, <?php echo $username; ?></h1>
        <p class="dashboard-subtitle">Manage your accounts and transactions</p>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Accounts Overview Card -->
        <div class="dashboard-card accounts-card">
            <div class="card-header">
                <h2>Your Accounts</h2>
                <span class="card-icon">💰</span>
            </div>
            <div class="accounts-list">
                <?php while ($account = mysqli_fetch_assoc($accounts)): ?>
                <div class="account-item">
                    <div class="account-info">
                        <span class="account-number"><?php echo $account['account_number']; ?></span>
                        <span class="account-balance">$<?php echo number_format($account['balance'], 2); ?></span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Transfer Money Card -->
        <div class="dashboard-card transfer-card">
            <div class="card-header">
                <h2>Transfer Money</h2>
                <span class="card-icon">💸</span>
            </div>
            <?php if (isset($success)): ?>
                <div class="success-message">
                    <span class="success-icon">✓</span>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <form method="POST" class="transfer-form">
                <input type="hidden" name="transfer" value="1">
                <div class="form-group">
                    <label for="from_account">From Account</label>
                    <input type="text" id="from_account" name="from_account" placeholder="Enter account number" required>
                </div>
                <div class="form-group">
                    <label for="to_account">To Account</label>
                    <input type="text" id="to_account" name="to_account" placeholder="Enter recipient account" required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="number" id="amount" name="amount" placeholder="Enter amount" required>
                </div>
                <button type="submit" class="btn-transfer">Transfer Money</button>
            </form>
        </div>

        <!-- Quick Actions Card -->
        <div class="dashboard-card actions-card">
            <div class="card-header">
                <h2>Quick Actions</h2>
                <span class="card-icon">⚡</span>
            </div>
            <div class="quick-actions">
                <a href="statement.php" class="action-button">
                    <span class="action-icon">📊</span>
                    <span class="action-text">View Statement</span>
                </a>
                <a href="complaint.php" class="action-button">
                    <span class="action-icon">📝</span>
                    <span class="action-text">File Complaint</span>
                </a>
                <a href="message.php" class="action-button">
                    <span class="action-icon">✉️</span>
                    <span class="action-text">Message Admin</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Styling -->
<style>
    /* Main dashboard layout */
    .dashboard {
        padding: 2rem 0;
    }

    /* Header styling */
    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-header h1 {
        font-size: 2rem;
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .dashboard-subtitle {
        color: var(--text-light);
        font-size: 1.1rem;
    }

    /* Grid layout for cards */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    /* Card styling */
    .dashboard-card {
        background: var(--surface);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
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

    /* Accounts list styling */
    .accounts-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .account-item {
        background: var(--background);
        padding: 1rem;
        border-radius: 8px;
        transition: background-color 0.2s ease;
    }

    .account-item:hover {
        background: #f1f5f9;
    }

    .account-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .transfer-form {
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

    .btn-transfer {
        background: var(--primary);
        color: white;
        padding: 0.75rem;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-transfer:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .success-message {
        background: #dcfce7;
        color: var(--success);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .success-icon {
        font-size: 1.25rem;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
    }

    .action-button {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        background: var(--background);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text);
        transition: all 0.2s ease;
    }

    .action-button:hover {
        background: #f1f5f9;
        transform: translateY(-2px);
    }

    .action-icon {
        font-size: 1.5rem;
    }

    .action-text {
        font-size: 0.875rem;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .quick-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?> 