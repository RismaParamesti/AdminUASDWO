<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $default_username = 'admin';
    $default_password = 'admin';

    // Membaca file users.txt
    $users = file('users.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $is_valid = false;
    
    foreach ($users as $user) {
        list($stored_username, $stored_password) = explode('|', $user);
        
        // Cek kecocokan username dan password
        if ($username === $stored_username && $password === $stored_password) {
            $is_valid = true;
            break;
        }
    }
    
    // Jika valid, login berhasil
    if ($is_valid) {
        echo "<script>
            alert('Login Berhasil!');
            document.location.href = 'index.php'; 
        </script>";
    } else {
        echo "<script>
            alert('Login Gagal!');
            document.location.href = 'login.php'; 
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        font-family: 'Poppins', Arial, sans-serif;
        background: linear-gradient(to right, #6a11cb, #2575fc);
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        color: #fff;
    }

    .login-container {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        padding: 40px;
        width: 380px;
        text-align: center;
        color: #333;
    }

    .login-container h2 {
        font-size: 32px;
        margin-bottom: 10px;
        font-weight: 700;
        color: #2575fc;
    }

    .login-container p {
        font-size: 14px;
        margin-bottom: 30px;
        color: #555;
    }

    .input-container {
        position: relative;
        margin-bottom: 25px;
        width: 100%;
    }

    .input-container input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        outline: none;
        transition: 0.3s;
    }

    .input-container input:focus {
        border-color: #2575fc;
        box-shadow: 0 0 5px rgba(37, 117, 252, 0.5);
    }

    .btn {
        background: linear-gradient(to right, #ff7e5f, #feb47b); /* Warna gradasi oranye */
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 8px;
        width: 40%;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn:hover {
        background: linear-gradient(to right, #feb47b, #ff7e5f); /* Gradasi terbalik untuk efek hover */
    }

    div {
    margin-bottom: 20px; /* Atur jarak antar elemen div */
}

    .register-link {
        margin-top: 15px;
        font-size: 14px;
    }

    .register-link a {
        text-decoration: none;
        color: #2575fc;
        font-weight: bold;
    }

    .register-link a:hover {
        text-decoration: underline;
    }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Dashboard DWO</h2>
        <p>Sign in to start your session</p>
        <form action="login.php" method="POST">
            <div>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button class="btn" type="submit">Sign In</button>
        </form>
        <div class="register-link">
            Don't have an account? <a href="register.php">Register</a>
        </div>
    </div>
</body>
</html>
