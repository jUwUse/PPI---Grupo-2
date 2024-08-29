<?php

    function logout() {
        session_destroy();
        header("location: Login.php");
        exit();
    }

?>
