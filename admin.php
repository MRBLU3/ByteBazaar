<?php
include("conn.php");
include("ck_session.php");

// Redirect if already logged in
if (isSessionAction()) {
    // Check the role of the logged-in user
    $stmt = $conn->prepare("SELECT role FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['userSession']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['role'] === 'admin') {
            header("location: dashboard.php");
            exit();
        } elseif ($user['role'] === 'user') {
            header("location: homepage.php");
            exit();
        }
    }
}

// Handle the form submission (login processing)
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = "All fields are required";
    } else {
        // Prepare the SQL statement to check if the user is an admin
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($password == $user['password']) {
                // Set the session for the logged-in admin user
                $_SESSION['userSession'] = $user['username'];
                header("location: dashboard.php");
                exit();
            } else {
              header("location: admin.php?error=Invalid credentials");
          }
      } else {
          header("location: admin.php?error=Invalid credentials");
      }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Admin Login</title>
  <link rel="stylesheet" href="css/admin.css">
  <link rel="icon" href="images/code.png">
</head>
<body>

  <div class="container">
    <div class="myform">
      <form action="admin.php" method="post">
        <h2>ADMIN LOGIN</h2>
        
        <?php if (isset($_GET['error'])) { ?>
				<p class="error"><?php echo $_GET['error']; ?></p>
			<?php } ?>

        <input type="text" name="username" placeholder="Admin Name" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
        <input type="password" name="password" placeholder="Password">
        <button type="submit">LOGIN</button>
      </form>
    </div>
    <div class="image">
      <img src="images/logo.png">
    </div>
  </div>

</body>
</html>
