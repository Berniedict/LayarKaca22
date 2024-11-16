<?php
session_start();
include 'includes/db.php'; // Include the database connection

// Fetch movies if logged in
$is_logged_in = isset($_SESSION['user_id']);
$movies = [];

if ($is_logged_in) {
    try {
        // Query to fetch all movies from the database
        $sql = "SELECT * FROM movies";
        $stmt = $pdo->query($sql);
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If there's an error with the database query, show an error message
        echo "Error fetching movies: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LayarKaca22 - Cinema Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- Optional for styling -->
</head>
<body>

    <!-- Navigation Bar -->
    <header>
        <div class="navbar">
            <h1>LayarKaca22</h1>
            <nav>
                <?php if ($is_logged_in): ?>
                    <a href="screens/booking-history.php">Booking History</a> <!-- Link to booking history -->
                    <a href="screens/logout.php">Logout</a>
                <?php else: ?>
                    <a href="screens/login.php">Login</a>
                    <a href="screens/register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <h2>Welcome to LayarKaca22</h2>
        <p>Your online cinema ticket booking system. Browse movies, select showtimes, and book tickets with ease!</p>
    </section>

    <!-- Movie Listings -->
    <?php if ($is_logged_in): ?>
    <section class="movie-section">
        <h3>Available Movies</h3>
        <div class="movie-list">
            <?php foreach ($movies as $movie): ?>
                <div class="movie-item">
                    <!-- Ensure the movie poster exists -->
                    <img src="assets/images/<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                    <div class="movie-details">
                        <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($movie['description'], 0, 100)); ?>...</p>
                        <a href="screens/screenings.php?movie_id=<?php echo $movie['id']; ?>" class="btn">See Screenings</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php else: ?>
        <!-- Message for Unauthenticated Users -->
        <section class="login-prompt">
            <p>Please <a href="screens/login.php">Login</a> or <a href="screens/register.php">Register</a> to start booking your tickets!</p>
        </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 LayarKaca22 Cinema Booking</p>
    </footer>

</body>
</html>