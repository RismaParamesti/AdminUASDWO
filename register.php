<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Menyimpan username dan password dalam format "username|password"
    $data = $username . "|" . $password . "\n";
    file_put_contents('users.txt', $data, FILE_APPEND);
    
    echo "<script>
            alert('Pendaftaran Berhasil');
            document.location.href = 'login.php';
        </script>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Dashboard DWO</title>
    <style>
        body {
    font-family: 'Poppins', Arial, sans-serif;
    background: linear-gradient(to right, #6a11cb, #2575fc); /* Sama seperti login */
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    color: #fff;
}

.register-container {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    padding: 40px;
    width: 380px;
    text-align: center;
    color: #333;
}

.register-container h2 {
    font-size: 32px;
    margin-bottom: 10px;
    font-weight: 700;
    color: #2575fc; /* Warna biru yang sama dengan login */
}

.input-container {
    position: relative;
    margin-bottom: 25px;
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
    background: linear-gradient(to right, #ff7e5f, #feb47b); /* Sama dengan login */
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 8px;
    width: 100%;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

.btn:hover {
    background: linear-gradient(to right, #feb47b, #ff7e5f); /* Gradasi terbalik untuk efek hover */
}

.login-link {
    margin-top: 15px;
    font-size: 14px;
}

.login-link a {
    text-decoration: none;
    color: #2575fc;
    font-weight: bold;
}

.login-link a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <div class="input-container">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-container">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-container">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>
