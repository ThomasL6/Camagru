<?php
session_start();
session_destroy();
header('Location: feed.php');
exit;
?>
