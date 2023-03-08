<?php
require_once "config.php";
session_unset();
session_destroy();
header("Location: signin.php");
$conn->close();
?>