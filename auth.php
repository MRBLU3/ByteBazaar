<?php
include("ck_session.php");
include "conn.php";
// Admin login logic (auth.php)
include "ck_session.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        header("location: admin.php?error=All fields are required");
        exit();
    }

    // Assuming a `role` column exists in the users table, where 'admin' indicates an admin user
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($password == $user['password']) {
            $_SESSION['userSession'] = $user['username'];
            header("location: dashboard.php");
        } else {
            header("location: admin.php?error=Invalid credentials");
        }
    } else {
        header("location: admin.php?error=Invalid credentials");
    }
}
?>
