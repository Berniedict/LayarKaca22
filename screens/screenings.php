<?php
session_start();
include '../includes/db.php'; // Include the database connection

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : null; // Get movie ID from the URL

// If movie ID is not provided, redirect to the index page
if (!$movie_id) {
    header("Location: index.php");
    exit;
}

// Fetch movie details
$movie = null;
if ($is_logged_in) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch();

    // Fetch locations for the movie
    $stmt = $pdo->prepare("SELECT * FROM locations");
    $stmt->execute();
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If movie does not exist, redirect to index
if (!$movie) {
    header("Location: index.php");
    exit;
}

// Fetch screenings based on location and movie selection
$screenings = [];
if (isset($_POST['location_id'])) {
    $location_id = $_POST['location_id'];
    $stmt = $pdo->prepare("SELECT * FROM screenings WHERE movie_id = ? AND location_id = ?");
    $stmt->execute([$movie_id, $location_id]);
    $screenings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screenings - <?php echo htmlspecialchars($movie['title']); ?> | LayarKaca22</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        /* Back Button Styling */
        .back-button-container {
            text-align: center;
            margin-top: 20px;
        }

        .btn-back {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <header>
        <div class="navbar">
            <h1>LayarKaca22</h1>
            <nav>
                <?php if ($is_logged_in): ?>
                    <a href="booking-history.php">Booking History</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Movie Details -->
    <section class="movie-details screenings-container">
        <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
        <p><?php echo htmlspecialchars($movie['description']); ?></p>
    </section>

    <!-- Location Selection -->
    <section class="location-selection screenings-container">
        <h3>Select a Location</h3>
        <form method="POST" action="screenings.php?movie_id=<?php echo $movie_id; ?>">
            <select name="location_id" required>
                <option value="">Select Location</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?php echo $location['id']; ?>"><?php echo htmlspecialchars($location['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Show Screenings</button>
        </form>
    </section>

    <!-- Available Screenings -->
    <?php if (!empty($screenings)): ?>
    <section class="screenings-section screenings-container">
        <h3>Select a Showtime</h3>
        <ul>
            <?php foreach ($screenings as $screening): ?>
                <li>
                    <?php echo date("F j, Y, g:i a", strtotime($screening['showtime'])); ?> - 
                    <a href="seats.php?screening_id=<?php echo $screening['id']; ?>" class="btn">Select Seats</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

    <!-- Back Button -->
    <section class="back-button-container">
        <a href="/index.php" class="btn-back">Back to Movie List</a>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 LayarKaca22 Cinema Booking</p>
    </footer>

</body>
</html>
