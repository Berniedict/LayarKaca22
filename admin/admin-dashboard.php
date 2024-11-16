<?php
session_start();
include '../includes/db.php'; // Include the database connection

// Check if the user is an admin, if not redirect to login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../screens/login.php');
    exit();
}

// Fetch all movies for management
$sql = "SELECT * FROM movies";
$stmt = $pdo->query($sql);
$movies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LayarKaca22</title>
    <link rel="stylesheet" href="../assets/css/styles.css"> <!-- Optional for styling -->
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
        <h2>Welcome, Admin</h2>
        <p>Manage your cinema's movies, showtimes, and bookings from here.</p>
    </section>

    <!-- Movie Management -->
    <section class="movie-management">
        <h3>Manage Movies</h3>
        <p>Below is the list of all movies. You can add, edit, or delete movies.</p>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Genre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movies as $movie): ?>
                    <tr>
                        <td><?php echo $movie['title']; ?></td>
                        <td><?php echo $movie['genre']; ?></td>
                        <td>
                            <a href="edit-movie.php?movie_id=<?php echo $movie['id']; ?>">Edit</a> | 
                            <a href="delete-movie.php?movie_id=<?php echo $movie['id']; ?>" onclick="return confirm('Are you sure you want to delete this movie?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 LayarKaca22 Cinema Booking</p>
    </footer>

</body>
</html>