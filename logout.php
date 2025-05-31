<?php
session_start();
$_SESSION['auth'] = null;
header("location: index.php");