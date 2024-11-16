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
    <style>
    /* Global Styling */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
    color: #333;
}

/* Navbar Styling */
.navbar {
    background-color: #2c3e50;
    color: #ecf0f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.navbar h1 {
    margin: 0;
    font-size: 1.5rem;
}

nav a {
    color: #ecf0f1;
    margin-left: 1rem;
    text-decoration: none;
    font-size: 1rem;
    transition: color 0.3s;
}

/* Booking History Section Styling */
.booking-history-section {
    max-width: 1000px;
    margin: 2rem auto;
    background-color: #fff;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.booking-history-section h2 {
    margin-top: 0;
    font-size: 1.8rem;
    border-bottom: 2px solid #3498db;
    padding-bottom: 0.5rem;
}

.booking-history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    overflow-x: auto;
}

.booking-history-table thead {
    background-color: #3498db;
    color: #fff;
}

.booking-history-table th, .booking-history-table td {
    padding: 0.75rem;
    text-align: left;
    border: 1px solid #ddd;
}

.booking-history-table th {
    font-weight: bold;
}

.booking-history-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Responsive Styling */
@media (max-width: 768px) {
    .booking-history-table th, .booking-history-table td {
        padding: 0.5rem;
    }

    nav a {
        font-size: 0.9rem;
    }
}

/* Footer Styling */
footer {
    background-color: #2a2a2a;
    color: white;
    padding: 10px;
    text-align: center;
    font-size: 13px;
    position: fixed;
    width: 100%;
    bottom: 0;
}
    </style>
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
