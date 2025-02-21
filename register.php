<?php

include "ck_session.php";
if(isSessionAction()){
  header("location: index.php");
}
?>
<?php
include "conn.php";

$firstName = $middleName = $lastName = $suffix = $email = $Number = $userName = $passWord = $cpassWord = $birthDate = $Gender = '';

if(isset($_POST['submit'])){
    $firstName = $_POST['first_name'];
    $middleName = $_POST['middle_name'];
    $lastName = $_POST['last_name'];
    $suffix = $_POST['suffix'];
    $email = $_POST['email'];
    $Number = $_POST['Number'];
    $userName = $_POST['username'];
    $passWord = $_POST['password'];
    $cpassWord = $_POST['cpassword'];
    $birthDate = $_POST['birth_date']; // Birthday input from form
    $Gender = $_POST['gender'];

    // Calculate age based on birthdate
    $dob = new DateTime($birthDate);
    $today = new DateTime();
    $age = $today->diff($dob)->y; // Get age in years

    // Check if the username already exists
    $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE username = '$userName'") or die('query failed');

    if(mysqli_num_rows($select_users) > 0){
        header("location: register.php?error=Username already exists! Choose another.");
        exit();
    } else {
        if($passWord != $cpassWord){
            header("location: register.php?error=Confirm password does not match!");
            exit();
        } else {
            $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName . ' ' . $suffix);
            
            mysqli_query($conn, "INSERT INTO `users` (first_name, middle_name, last_name, suffix, email, Number, username, password, birth_date, gender) 
                                VALUES('$firstName', '$middleName', '$lastName', '$suffix', '$email', '$Number', '$userName', '$cpassWord', '$birthDate', '$Gender')")
                                or die('query failed');
            
            header("Location: register.php?success=true");
            exit();
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
  <title>Registration</title>
  <link rel="stylesheet" href="css/register.css">
  <link rel="icon" href="images/logo.png">
  <script src="https://apis.google.com/js/platform.js" async defer></script>
  <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
</head>
<body>

  <div class="container">
  <div id="successModal" class="modal" style="display: none;">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Registration Successful!</h2>
    <p>Your account has been created successfully.</p>
    <button id="modalClose">OK</button>
  </div>
</div>
    <div class="myform">
      <a href="index.php"><button type="button"><i class="fas fa-sign-out"></i> RETURN</button></a>
      <form action="#" method="POST">
    <h2>PLEASE REGISTER AN ACCOUNT</h2>

    <?php if (isset($_GET['error'])) { ?>
        <p class="error"><?php echo $_GET['error']; ?></p>
    <?php } ?>

    <input type="text" name="first_name" placeholder="First Name" required pattern="[A-Za-z]*+" 
        title="First name must contain only letters." value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : ''; ?>">

    <input type="text" name="middle_name" placeholder="Middle Name"
        pattern="[A-Za-z]*" 
        title="Middle name must contain only letters." value="<?php echo isset($_POST['middle_name']) ? $_POST['middle_name'] : ''; ?>">

    <input type="text" name="last_name" placeholder="Last Name" required 
        pattern="[A-Za-z]+" 
        title="Last name must contain only letters." value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : ''; ?>">

    <input type="text" name="suffix" placeholder="Suffix (Optional)" value="<?php echo isset($_POST['suffix']) ? $_POST['suffix'] : ''; ?>">

    <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">

    <input type="text" name="Number" placeholder="Number" required 
        pattern="\d{11}"
        title="Phone number must contain only 11 digits." value="<?php echo isset($_POST['Number']) ? $_POST['Number'] : ''; ?>">

    <input type="text" name="username" placeholder="Username" required value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">

    <input type="password" name="password" placeholder="Password" required minlength="8" 
        pattern=".*[!@#$%^&*(),.?\:{}|<>].*" 
        title="Password must be at least 8 characters long and contain at least one special character.">

    <input type="password" name="cpassword" placeholder="Confirm Password" required minlength="8" 
        pattern=".*[!@#$%^&*(),.?\:{}|<>].*" 
        title="Password must be at least 8 characters long and contain at least one special character.">

        <label for="birth_date">Date of Birth:</label>
        <input type="date" name="birth_date" required value="<?php echo isset($_POST['birth_date']) ? $_POST['birth_date'] : ''; ?>">

    <label for="">Gender:</label><br><br>
    Male<input class="gender" type="radio" name="gender" value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'checked' : ''; ?> /><br>
    Female<input class="gender" type="radio" name="gender" value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'checked' : ''; ?> /><br>

    <button type="submit" name="submit">REGISTER</button>
</form>


      
      <br>
      <p class="register-link">


  <script src="https://apis.google.com/js/platform.js" async defer></script>


<script>
  // Function to get URL parameters
  function getUrlParameter(name) {
    const params = new URLSearchParams(window.location.search);
    return params.get(name);
  }

  // Check if 'success' parameter exists
  window.onload = function() {
    if (getUrlParameter('success') === 'true') {
      const modal = document.getElementById('successModal');
      modal.style.display = 'block';

      // Close modal on button click
      document.getElementById('modalClose').onclick = function() {
        modal.style.display = 'none';
        window.location.href = 'login.php'; // Redirect after closing
      };

      // Close modal on "X" click
      document.querySelector('.close').onclick = function() {
        modal.style.display = 'none';
        window.location.href = 'login.php';
      };

      // Close modal if clicking outside of it
      window.onclick = function(event) {
        if (event.target === modal) {
          modal.style.display = 'none';
          window.location.href = 'login.php';
        }
      };
    }
  };
</script>

</body>
</html>
