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

// Handle complaint form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // CSRF attack detected
        log_action($conn, $_SESSION['user_id'], $_SESSION['username'], "CSRF attack detected on complaint form");
        die("Invalid request");
    }
    
    $user_id = $_SESSION['user_id'];
    $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
    
    // FIXED: Secure file upload handling
    if (isset($_FILES['complaint_file'])) {
        $file_name = $_FILES['complaint_file']['name'];
        $file_tmp = $_FILES['complaint_file']['tmp_name'];
        $file_size = $_FILES['complaint_file']['size'];
        $file_type = $_FILES['complaint_file']['type'];
        
        // Get file extension
        $tmp = explode('.', $file_name);
        $file_ext = strtolower(end($tmp));
        
        // Define allowed file types
        $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        
        // Security checks
        $errors = array();
        
        // Check file type
        if (!in_array($file_ext, $allowed_types)) {
            $errors[] = "File type not allowed. Please upload PDF, DOC, DOCX, TXT, JPG, JPEG or PNG files only.";
        }
        
        // Check file size (max 5MB)
        if ($file_size > 5000000) {
            $errors[] = "File size must be less than 5MB";
        }
        
        // Create a secure file name
        $new_file_name = md5($user_id . time() . $file_name) . '.' . $file_ext;
        $upload_dir = "../uploads/";
        
        // Ensure upload directory exists and is writable
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Create a full relative path
        $upload_path = $upload_dir . $new_file_name;
        
        // If no errors, proceed with upload
        if (empty($errors)) {
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // FIXED: Use prepared statements to prevent SQL injection
                $query = "INSERT INTO complaints (user_id, file_name, file_path, description) 
                         VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "isss", $user_id, $new_file_name, $upload_path, $description);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Complaint submitted successfully!";
                    log_action($conn, $_SESSION['user_id'], $_SESSION['username'], "Filed a complaint: $description");
                } else {
                    $errors[] = "Database error: " . mysqli_error($conn);
                }
            } else {
                $errors[] = "Error uploading file. Please try again.";
            }
        }
    }
}

// Include the header (contains navigation and styling)
require_once '../includes/header.php';
?>

<!-- Complaint Form HTML Structure -->
<div class="complaint-page">
    <!-- Page Header -->
    <div class="page-header">
        <h1>File a Complaint</h1>
        <p class="subtitle">We're here to help. Please provide details about your concern.</p>
    </div>

    <!-- Main Complaint Form Card -->
    <div class="complaint-card">
        <!-- Success Message -->
        <?php if (isset($success)): ?>
            <div class="success-message">
                <span class="success-icon">✓</span>
                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Complaint Form -->
        <form method="POST" enctype="multipart/form-data" class="complaint-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <!-- Complaint Description -->
            <div class="form-group">
                <label for="description">Complaint Details</label>
                <textarea 
                    id="description" 
                    name="description" 
                    placeholder="Please describe your complaint in detail..." 
                    required
                    rows="6"
                ></textarea>
            </div>

            <!-- File Upload Section -->
            <div class="form-group file-upload">
                <label for="complaint_file" class="file-label">
                    <span class="file-icon">📎</span>
                    <span class="file-text">Attach Supporting Document</span>
                    <span class="file-hint">Allowed formats: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG (max 5MB)</span>
                </label>
                <input 
                    type="file" 
                    id="complaint_file" 
                    name="complaint_file" 
                    required
                    class="file-input"
                >
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button">
                <span class="button-icon">📤</span>
                Submit Complaint
            </button>
        </form>
    </div>
</div>

<!-- Page Styling -->
<style>
    /* Main page layout */
    .complaint-page {
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

    /* Main card styling */
    .complaint-card {
        background: var(--surface);
        border-radius: 12px;
        padding: 2rem;
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
    }
    
    .error-message ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    /* Form styling */
    .complaint-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
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

    /* File upload styling */
    .file-upload {
        position: relative;
    }

    .file-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 2rem;
        border: 2px dashed #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .file-label:hover {
        border-color: var(--primary);
        background: rgba(37, 99, 235, 0.05);
    }

    .file-icon {
        font-size: 2rem;
    }

    .file-text {
        font-weight: 500;
        color: var(--text);
    }

    .file-hint {
        font-size: 0.875rem;
        color: var(--text-light);
    }

    .file-input {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }

    /* Submit button styling */
    .submit-button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: var(--primary);
        color: white;
        padding: 1rem;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .submit-button:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .button-icon {
        font-size: 1.25rem;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .complaint-page {
            padding: 1rem 0;
        }

        .page-header h1 {
            font-size: 1.5rem;
        }

        .complaint-card {
            padding: 1.5rem;
        }

        .file-label {
            padding: 1.5rem;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?> 