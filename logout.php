<?php
session_start();

// Hapus semua data session
session_unset();

// Hancurkan session
session_destroy();

// Alihkan pengguna kembali ke halaman login
header('Location: login.php');
exit;
?>
