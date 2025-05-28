<?php
session_start();
session_unset();
session_destroy();
session_start();
$_SESSION['error_message'] = 'Sua sessão expirou por inatividade.';
header("Location: login.php");
exit();
?>