<?php
include "conn.php"; // Database connection

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$id_token = $data['id_token'] ?? '';

if ($id_token) {
    // Verify the ID token with Google
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=".$id_token;
    $response = file_get_contents($url);
    $response_data = json_decode($response, true);

    if (isset($response_data['email'])) {
        $email = $response_data['email'];
        $Name = $response_data['name'];

        // Check if user already exists
        $check_user = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'");

        if (mysqli_num_rows($check_user) > 0) {
            echo json_encode(['success' => true, 'message' => 'User already exists']);
        } else {
            // Register new user with their Google details
            $username = explode("@", $email)[0]; // Set username as the part before '@' in the email
            $default_password = md5($id_token); // You may set a random password
            mysqli_query($conn, "INSERT INTO `users` (Name, email, username, password) VALUES('$Name', '$email', '$username', '$default_password')") or die('query failed');

            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No token provided']);
}
?>
