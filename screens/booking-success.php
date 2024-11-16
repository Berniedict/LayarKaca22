<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../screens/login.php');
    exit();
}

// Retrieve the booking success message and details
if (!isset($_SESSION['booking_success'])) {
    header('Location: ../index.php');
    exit();
}

$booking_success = $_SESSION['booking_success'];
$movie_title = $booking_success['movie_title'];
$location_name = $booking_success['location_name'];
$showtime = $booking_success['showtime'];
$seats = $booking_success['seats'];

// Clear the session variable after displaying the success page
unset($_SESSION['booking_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Success - LayarKaca22</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<header>
    <div class="navbar">
        <h1>LayarKaca22</h1>
        <nav>
            <a href="../screens/logout.php">Logout</a>
            <a href="../index.php">Home</a>
        </nav>
    </div>
</header>

<section class="success-section">
    <h2>Booking Confirmed!</h2>
    <p>Thank you for booking with LayarKaca22. Your reservation details are below:</p>
    
    <div class="booking-summary">
        <h3>Booking Summary</h3>
        <p><strong>Movie:</strong> <?php echo htmlspecialchars($movie_title); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($location_name); ?></p>
        <p><strong>Showtime:</strong> <?php echo date('F j, Y, g:i a', strtotime($showtime)); ?></p>
        <p><strong>Seats:</strong> <?php echo htmlspecialchars(implode(', ', $seats)); ?></p>
    </div>

    <div class="actions">
        <a href="../index.php" class="btn">Back to Home</a>
        <a href="booking-history.php" class="btn">View My Bookings</a>
    </div>
</section>

<footer>
    <p>&copy; 2024 LayarKaca22 Cinema Booking</p>
</footer>
</body>
</html>
