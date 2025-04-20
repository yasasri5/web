<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "yoksha");

if ($conn) {
    echo "connection succesfully";
} else{
    echo "connection failed";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            echo "<script>alert('Login successful!'); window.location.href = 'payment.html';</script>";
        } else {
            echo "<script>alert('Incorrect password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Email not found. Please sign up.'); window.location.href='signup.html';</script>";
    }
}
?>
