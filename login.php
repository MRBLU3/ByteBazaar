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
        if ($user['role'] === 'user') {
            header("location: homepage.php");
            exit();
        } elseif ($user['role'] === 'admin') {
            header("location: dashboard.php");
            exit();
        }
    }
}


	// Handle login process
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$username = $_POST["username"];
		$password = $_POST["password"];
		
		if (empty($username) || empty($password)) {
			header("location: login.php?error=All fields are required");
			exit();
		}
	
		$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = 'user'");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
	
		if ($result->num_rows === 1) {
			$user = $result->fetch_assoc();
			if ($password == $user['password']) {
				$_SESSION['userSession'] = $user['username'];
				header("location: homepage.php");
			} else {
				header("location: login.php?error=Invalid credentials");
			}
		} else {
			header("location: login.php?error=Invalid credentials");
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
	<title>Login</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="icon" href="images/logo.png">
	<script>
		window.onload = function() {
			// Check if the "login_required" error is in the URL
			const urlParams = new URLSearchParams(window.location.search);
			if (urlParams.has('error') && urlParams.get('error') === 'login_required') {
				alert('You must log in first to access that page.');
			}
		};
	</script>
	</head>
	<body>
	
	<div class="container">
		<div class="myform">
		<a href="index.php"><button type="button">GO BACK</button></a>
			<form action="login.php" method="post">
				<h2>WELCOME TO BYTEBZAAR <i class="fa-solid fa-book"></i> </h2>
				
				<?php if (isset($_GET['error'])) { ?>
					<p class="error"><?php echo $_GET['error']; ?></p>
				<?php } ?>
	
				<input type="text" name="username" placeholder="Username" required>
				<input type="password" name="password" placeholder="Password" required>
				<button type="submit">LOGIN</button>
			</form>
			<br>
			<p class="register-link">
				Don't have an account? <a href="register.php"><button>REGISTER HERE</button></a>
			</p>
		</div>
		<div class="image">
			<img src="images/logo.png" alt="Geek Logo">
		</div>
	</div>



<script>
 document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById('successModal');
  const closeButton = document.querySelector('.close');

  // Check if the success parameter is present in the URL
  if (modal && new URLSearchParams(window.location.search).get('success') === 'true') {
    modal.style.display = 'flex'; // Show the modal
  }

  // Close the modal
  const closeModal = () => {
    modal.style.display = 'none';
    history.replaceState(null, '', window.location.pathname); // Remove query string
  };

  // Event listeners for closing modal
  closeButton?.addEventListener('click', closeModal);
  window.addEventListener('click', (event) => {
    if (event.target === modal) closeModal();
  });
});
</script>

	</body>
	</html>