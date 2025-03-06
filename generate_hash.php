<?php
$newPassword = 'admin123';
$newHash = password_hash($newPassword, PASSWORD_BCRYPT);
echo $newHash;
?>
