<?php
session_start();

function isSessionAction(){
    return isset($_SESSION['userSession']);
}
?>
