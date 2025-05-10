# Assignment 1 Vulnerable


## 🚀 Quick Start Guide


### Step-by-Step Setup

1. **Start WAMP Server**
   - Open WAMP from your desktop or start menu
   - Wait until the WAMP icon turns green (indicating all services are running)

2. **Database Setup**
   - Open your web browser and go to: `http://localhost/phpmyadmin`
   - Click on "New" to create a new database
   - Name it `vulnerable_bank`
   - Click "Create"
   - Select the new database and click on "Import"
   - Choose the `schema.sql` file from this project
   - Click "Go" to import the database structure

3. **Project Setup**
   - Copy all project files to: `D:\wamp64\www\vulnerable-bank`

4. **Configuration**
   - Open `config.php` in your code editor
   - Update the database connection details if needed:
     ```php
     $host = 'localhost';
     $dbname = 'vulnerable_bank';
     $username = 'root';
     $password = ''; // default WAMP password is empty
     ```

5. **Access the Application**
   - Open your web browser
   - Go to: `http://localhost/vulnerable-bank`



## OWASP Top 10 Vulnerabilities and Exploitation Scenarios

### 1. Injection (A1:2017)
- **Location**: Multiple files using direct SQL queries
- **Exploitation**: 
  - Login bypass: `' OR '1'='1`
  - SQL injection in transfer: `' OR '1'='1' -- `
  - Impact: Unauthorized access, data manipulation

### 2. Broken Authentication (A2:2017)
- **Location**: `login.php`, `schema.sql`
- **Exploitation**:
  - Weak passwords stored in plain text
  - No password complexity requirements
  - Impact: Easy password cracking, unauthorized access

### 3. Sensitive Data Exposure (A3:2017)
- **Location**: `login.php`
- **Exploitation**:
  - Passwords stored in session
  - No encryption for sensitive data
  - Impact: Session hijacking, data theft

### 4. XML External Entities (XXE) (A4:2017)
- **Location**: `complaint.php`
- **Exploitation**:
  - Upload XML files with external entity references
  - Impact: Server-side request forgery, file disclosure

### 5. Broken Access Control (A5:2017)
- **Location**: Multiple files
- **Exploitation**:
  - Direct access to admin pages
  - Manipulate session variables
  - Impact: Privilege escalation

### 6. Security Misconfiguration (A6:2017)
- **Location**: `complaint.php`
- **Exploitation**:
  - Upload malicious files
  - Access to sensitive directories
  - Impact: Remote code execution

### 7. Cross-Site Scripting (XSS) (A7:2017)
- **Location**: `message.php`, `dashboard.php`
- **Exploitation**:
  - Inject JavaScript in messages
  - Steal session cookies
  - Impact: Session hijacking, malware distribution

### 8. Insecure Deserialization (A8:2017)
- **Location**: `dashboard.php`
- **Exploitation**:
  - Manipulate transaction data
  - Impact: Data integrity compromise

### 9. Using Components with Known Vulnerabilities (A9:2017)
- **Location**: All files
- **Exploitation**:
  - Outdated PHP version
  - Unpatched MySQL
  - Impact: Various depending on vulnerabilities

### 10. Insufficient Logging & Monitoring (A10:2017)
- **Location**: `admin.php`
- **Exploitation**:
  - Basic logging without proper monitoring
  - No alerting system
  - Impact: Delayed detection of attacks
