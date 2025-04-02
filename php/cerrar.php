<?php
session_start();
include_once './conexionDB.php';


session_unset(); 
session_destroy(); 

header("Location: ../index.php");
exit();
?>