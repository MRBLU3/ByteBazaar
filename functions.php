<?php

    require_once 'conn.php';

    function display_data(){
        global $conn;
        $query = "select * from orders";
        $result = mysqli_query($conn,$query);
        return $result;
    }


?>