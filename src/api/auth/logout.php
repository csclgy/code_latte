<?php
session_start();
session_destroy();
header('Location: /hrm_module/index.php');
exit;
?>