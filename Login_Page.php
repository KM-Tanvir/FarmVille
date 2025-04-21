<?php
// Start session
session_start();

// Database connection parameters
$db_host = "localhost";
$db_user = "farmville_user";
$db_pass = "your_secure_password";
$db_name = "farmville_db";

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_POST['user_select']) ? $_POST['user_select'] : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate input
    if (empty($password)) {
        $_SESSION['error'] = "Password is required";
        header("Location: index.html");
        exit;
    }
    
    // Prepare SQL statement based on login method
    if (!empty($email)) {
        // Login with email
        $sql = "SELECT id, first_name, last_name, password_hash FROM farmers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
    } elseif (!empty($phone)) {
        // Login with phone
        $sql = "SELECT id, first_name, last_name, password_hash FROM farmers WHERE phone = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $phone);
    } else {
        $_SESSION['error'] = "Email or phone number is required";
        header("Location: index.html");
        exit;
    }
    
    // Execute query
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, start a new session
            session_regenerate_id();
            
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['logged_in'] = true;
            
            // Redirect to dashboard
            header("Location: Farmer_Profile.html");
            exit;
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Invalid password";
            header("Location: index.html");
            exit;
        }
    } else {
        // User not found
        $_SESSION['error'] = "No account found with that email/phone";
        header("Location: index.html");
        exit;
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to login page
    header("Location: index.html");
    exit;
}

$conn->close();
?>