<?php
session_start();
include '../includes/db.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../screens/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch booking history for the logged-in user
try {
    $sql = "SELECT b.id, b.showtime, b.seats, m.title AS movie_title, l.name AS location_name 
            FROM bookings b
            JOIN movies m ON b.movie_id = m.id
            JOIN locations l ON b.location_id = l.id
            WHERE b.user_id = ? 
            ORDER BY b.created_at DESC"; // Order by most recent booking first
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching booking history: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History - LayarKaca22</title>
    <link rel="stylesheet" href="../assets/css/styles.css"> <!-- Optional for styling -->
</head>
<body>

    <!-- Navigation Bar -->
    <header>
        <div class="navbar">
            <h1>LayarKaca22</h1>
            <nav>
                <a href="../index.php">Home</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <!-- Booking History Section -->
    <section class="booking-history-section">
        <h2>Your Booking History</h2>
        <?php if (empty($bookings)): ?>
            <p>You have no bookings yet.</p>
        <?php else: ?>
            <table class="booking-history-table">
                <thead>
                    <tr>
                        <th>Movie</th>
                        <th>Location</th>
                        <th>Showtime</th>
                        <th>Seats</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                            <td><?php echo htmlspecialchars($booking['location_name']); ?></td>
                            <td><?php echo date('F j, Y, g:i a', strtotime($booking['showtime'])); ?></td>
                            <td><?php echo htmlspecialchars($booking['seats']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 LayarKaca22 Cinema Booking</p>
    </footer>

</body>
</html>
