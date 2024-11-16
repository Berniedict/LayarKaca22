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
    <style>
        /* Global Styling */
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
    }

    /* Dashboard Overview Section */
    .dashboard-overview {
        max-width: 1000px;
        margin: 2rem auto;
        background-color: #fff;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .dashboard-overview h2 {
        margin-top: 0;
        font-size: 1.8rem;
        border-bottom: 2px solid #3498db;
        padding-bottom: 0.5rem;
    }

    .dashboard-overview p {
        margin-bottom: 1.5rem;
        color: #555;
    }

    /* Table Styling */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2rem;
    }

    thead {
        background-color: #3498db;
        color: #fff;
    }

    table th, table td {
        padding: 0.75rem;
        text-align: left;
        border: 1px solid #ddd;
    }

    table th {
        font-weight: bold;
    }

    table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    a {
        color: #3498db;
        text-decoration: none;
    }

    /* Buttons */
    button, .btn-link {
        background-color: #3498db;
        color: #fff;
        border: none;
        padding: 0.5rem 1rem;
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
    }

    /* Footer Styling */
    footer {
        background-color: #2a2a2a;
        color: white;
        padding: 5px;
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

<!-- Dashboard Overview -->
<section class="dashboard-overview">
    <h2>Manage Showtimes</h2>
    <p><a class="back-button" href="javascript:history.back()">&larr; Back</a></p>
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
