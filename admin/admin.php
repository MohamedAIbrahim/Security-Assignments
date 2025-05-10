<?php
session_start();
require_once '../config/config.php';

// A5:2017 - Broken Access Control - No proper role validation
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../public/login.php");
    exit();
}

// Get all complaints
$query = "SELECT c.*, u.username FROM complaints c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC";
$complaints = mysqli_query($conn, $query);

// Get all messages
$query = "SELECT m.*, u.username FROM messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC";
$messages = mysqli_query($conn, $query);

// A10:2017 - Insufficient Logging & Monitoring - Basic logging
function log_action($action) {
    $log_file = "admin_logs.txt";
    $timestamp = date("Y-m-d H:i:s");
    $log_entry = "[$timestamp] User: {$_SESSION['username']} - Action: $action\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Log page access
log_action("Accessed admin dashboard");

require_once '../includes/header.php';

$result = mysqli_query($conn, "SELECT * FROM logs ORDER BY created_at DESC");
?>

<div class="card">
    <h2>Admin Dashboard</h2>
    
    <div class="section">
        <h3>Complaints</h3>
        <table>
            <tr>
                <th>User</th>
                <th>File</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
            <?php while ($complaint = mysqli_fetch_assoc($complaints)): ?>
            <tr>
                <td><?php echo $complaint['username']; ?></td>
                <td><a href="<?php echo $complaint['file_path']; ?>"><?php echo $complaint['file_name']; ?></a></td>
                <td><?php echo $complaint['description']; ?></td>
                <td><?php echo $complaint['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    
    <div class="section">
        <h3>Messages</h3>
        <table>
            <tr>
                <th>User</th>
                <th>Message</th>
                <th>Date</th>
            </tr>
            <?php while ($message = mysqli_fetch_assoc($messages)): ?>
            <tr>
                <td><?php echo $message['username']; ?></td>
                <td><?php echo $message['message']; ?></td>
                <td><?php echo $message['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="section">
        <h3>Action Logs</h3>
        <table>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Action</th>
            </tr>
            <?php while ($log = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $log['created_at']; ?></td>
                <td><?php echo $log['username']; ?></td>
                <td><?php echo $log['action']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<li><a href="admin.php?view=logs">View Logs</a></li>

<?php require_once '../includes/footer.php'; ?> 