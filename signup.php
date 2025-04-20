<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "yoksha");

if ($conn) {
    echo "connection succesfully";
} else{
    echo "connection failed";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $age      = intval($_POST['age']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email already registered. Please log in.'); window.location.href='login.html';</script>";
    } else {
        $sql = "INSERT INTO users (name, email, age, password) VALUES ('$name', '$email', $age, '$password')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Registration successful! Please log in.'); window.location.href='login.html';</script>";
        } else {
            echo "<script>alert('Database Error: " . mysqli_error($conn) . "'); window.history.back();</script>";
        }
    }
    mysqli_close($conn);
}
?>
