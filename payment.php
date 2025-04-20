<?php
// Connect to the database
$conn = mysqli_connect("localhost", "root", "", "yoksha");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $card_number = $_POST['card_number'];
    $expiry = $_POST['expiry'];
    $cvv = $_POST['cvv'];
    $amount = $_POST['amount'];

    // Basic validation
    if (strlen($card_number) == 16 && strlen($cvv) == 3 && ($amount == "200" || $amount == "500" || $amount == "750")) {
        $sql = "INSERT INTO payments (name, card_number, expiry, cvv, amount) 
                VALUES ('$name', '$card_number', '$expiry', '$cvv', '$amount')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Payment of ₹$amount successful!'); window.location.href='final_project.html';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid payment details.'); window.history.back();</script>";
    }
}

// Close connection
mysqli_close($conn);
?>
