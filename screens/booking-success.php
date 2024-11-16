<?php
session_start();
include '../includes/db.php'; // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../screens/login.php');
    exit();
}

// Retrieve the booking success message and details from the session
if (!isset($_SESSION['booking_success'])) {
    header('Location: ../index.php');
    exit();
}

$booking_success = $_SESSION['booking_success'];
$movie_title = $booking_success['movie_title']; // Movie title
$location_name = $booking_success['location_name']; // Location name
$location_id = $booking_success['location_id']; // Location ID
$showtime = $booking_success['showtime']; // Showtime
$seats = $booking_success['seats']; // Array of seat numbers
$user_id = $_SESSION['user_id'];

// Check if booking already exists
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM bookings 
    WHERE user_id = :user_id AND movie_id = :movie_id AND location_id = :location_id AND showtime = :showtime
");
$stmt->execute([
    ':user_id' => $user_id,
    ':movie_id' => $booking_success['movie_id'], // Movie ID from session
    ':location_id' => $location_id,
    ':showtime' => $showtime
]);
$booking_exists = $stmt->fetchColumn() > 0;

if (!$booking_exists) {
    // Insert the booking into the database
    try {
        $pdo->beginTransaction();

        // Insert booking details into the bookings table
        $stmt = $pdo->prepare("
            INSERT INTO bookings (user_id, movie_id, location_id, showtime, seats, created_at) 
            VALUES (:user_id, :movie_id, :location_id, :showtime, :seats, NOW())
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':movie_id' => $booking_success['movie_id'], // Movie ID from session
            ':location_id' => $location_id,
            ':showtime' => $showtime,
            ':seats' => implode(',', $seats) // Store seats as a comma-separated string
        ]);

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error saving booking: " . $e->getMessage());
    }
}

// Clear the session variable after processing the booking
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