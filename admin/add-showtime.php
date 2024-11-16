<?php
session_start();
include '../includes/db.php'; // Include the database connection file

// Check if the user is an admin, if not redirect to login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../screens/login.php');
    exit();
}

// Fetch all movies and locations for the dropdown
$movies_sql = "SELECT * FROM movies";
$locations_sql = "SELECT * FROM locations";
$movies_stmt = $pdo->query($movies_sql);
$locations_stmt = $pdo->query($locations_sql);
$movies = $movies_stmt->fetchAll();
$locations = $locations_stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = $_POST['movie_id'];
    $location_id = $_POST['location_id'];
    $showtime = $_POST['showtime'];

    // Insert the new screening
    $sql = "INSERT INTO screenings (movie_id, location_id, showtime) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$movie_id, $location_id, $showtime]);

    // Get the ID of the newly inserted screening
    $screening_id = $pdo->lastInsertId();

    // Fetch the number of seats for the selected location (this can be hard-coded if needed)
    // Example: Hard-code 50 seats for each location
    $seat_count = 50; // Adjust this based on your logic or table

    // Insert seats for the newly created screening
    for ($i = 1; $i <= $seat_count; $i++) {
        $sql = "INSERT INTO seats (screening_id, seat_number, available) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$screening_id, $i, 1]); // Mark all seats as available (1)
    }

    header('Location: manage-showtimes.php'); // Redirect to manage showtimes after adding
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Showtime - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<!-- Admin Navigation Bar -->
<header>
    <div class="navbar">
        <h1>Admin Dashboard - LayarKaca22</h1>
        <nav>
            <a href="add-movie.php">Add Movie</a>
            <a href="manage-showtimes.php">Manage Showtimes</a>
            <a href="manage-bookings.php">Manage Bookings</a>
            <a href="../screens/logout.php">Logout</a>
        </nav>
    </div>
</header>

<!-- Add Showtime Form -->
<section class="add-showtime">
    <h2>Add New Showtime</h2>

    <form method="POST" action="">
        <label for="movie_id">Movie:</label>
        <select name="movie_id" id="movie_id" required>
            <option value="">Select Movie</option>
            <?php foreach ($movies as $movie): ?>
                <option value="<?php echo $movie['id']; ?>"><?php echo $movie['title']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="location_id">Location:</label>
        <select name="location_id" id="location_id" required>
            <option value="">Select Location</option>
            <?php foreach ($locations as $location): ?>
                <option value="<?php echo $location['id']; ?>"><?php echo $location['name']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="showtime">Showtime:</label>
        <input type="datetime-local" name="showtime" id="showtime" required>

        <button type="submit">Add Showtime</button>
    </form>
</section>

<!-- Footer -->
<footer>
    <p>&copy; 2024 LayarKaca22 Cinema Booking</p>
</footer>

</body>
</html>
