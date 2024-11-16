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
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background-color: #34495e;
            padding: 1rem 2rem;
            color: #ecf0f1;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        nav a {
            color: #ecf0f1;
            margin-left: 1rem;
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #3498db;
        }

        /* Add Showtime Form */
        .add-showtime {
            max-width: 900px;
            margin: 3rem auto;
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .add-showtime h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
            color: #34495e;
        }

        label {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            display: block;
            color: #34495e;
        }

        select, input[type="datetime-local"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 1rem;
            color: #34495e;
            background-color: #f9f9f9;
        }

        select:focus, input[type="datetime-local"]:focus {
            outline: none;
            border-color: #3498db;
            background-color: #fff;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Footer */
        footer {
            background-color: #2c3e50;
            color: white;
            padding: 10px 0;
            text-align: center;
            font-size: 13px;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 1rem;
            background-color: #95a5a6;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #7f8c8d;
        }
    </style>
</head>
<body>

<!-- Admin Navigation Bar -->
<header>
    <div class="navbar">
        <h1>Admin Dashboard - LayarKaca22</h1>
        <nav>
            <a href="add-movie.php">Add Movie</a>
            <a href="manage-showtimes.php">Manage Showtimes</a>
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
