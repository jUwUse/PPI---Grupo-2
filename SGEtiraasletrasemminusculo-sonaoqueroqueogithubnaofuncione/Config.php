<?php
    session_start();

    define('DB_SERVER' , 'localhost');
    define('DB_NAME', 'BD_PPIWOW');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');

    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    /* 

    if ($conn == true) {
        echo "boa";
    }
        
    */
?>



