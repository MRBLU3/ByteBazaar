<?php
session_start();
session_destroy();
header("location: index.php"); // Redirect to login page after logout

?>
