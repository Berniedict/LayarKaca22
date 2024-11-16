<?php
session_start();
include '../includes/db.php'; // Include the database connection file

// Check if the user is an admin, if not redirect to login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../screens/login.php');
    exit();
}

// Fetch all showtimes with location name
$sql = "SELECT screenings.*, movies.title, locations.name AS location_name FROM screenings
        JOIN movies ON screenings.movie_id = movies.id
        JOIN locations ON screenings.location_id = locations.id";
$stmt = $pdo->query($sql);
$screenings = $stmt->fetchAll();

// Handle deletion of screenings
if (isset($_GET['delete'])) {
    $screening_id = $_GET['delete'];

    // First, delete related seats
    $delete_seats_sql = "DELETE FROM seats WHERE screening_id = ?";
    $delete_seats_stmt = $pdo->prepare($delete_seats_sql);
    $delete_seats_stmt->execute([$screening_id]);

    // Then, delete the screening itself
    $delete_screening_sql = "DELETE FROM screenings WHERE id = ?";
    $delete_screening_stmt = $pdo->prepare($delete_screening_sql);
    $delete_screening_stmt->execute([$screening_id]);

    header('Location: manage-showtimes.php'); // Refresh page after deletion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Showtimes - Admin Dashboard</title>
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
            <a href="../screens/logout.php">Logout</a>
        </nav>
    </div>
</header>

<!-- Dashboard Overview -->
<section class="dashboard-overview">
    <h2>Manage Showtimes</h2>
    <p>Below are the list of scheduled showtimes. You can add, edit, or delete them.</p>

    <!-- Showtimes Table -->
    <table>
        <thead>
            <tr>
                <th>Movie</th>
                <th>Location</th>
                <th>Showtime</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($screenings as $screening): ?>
                <tr>
                    <td><?php echo $screening['title']; ?></td>
                    <td><?php echo $screening['location_name']; ?></td>
                    <td><?php echo date('F j, Y, g:i a', strtotime($screening['showtime'])); ?></td>
                    <td>
                        <a href="manage-showtimes.php?delete=<?php echo $screening['id']; ?>" onclick="return confirm('Are you sure you want to delete this screening and its seats?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Add Showtime Button -->
    <p><a href="add-showtime.php">Add New Showtime</a></p>

</section>

<!-- Footer -->
<footer>
    <p>&copy; 2024 LayarKaca22 Cinema Booking</p>
</footer>

</body>
</html>
