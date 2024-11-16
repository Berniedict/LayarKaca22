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
   <style>
    /* Styling for the admin dashboard page */
    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #eef2f5;
    color: #444;
    margin: 0;
    padding: 0;
    }

    /* Navigation Bar */
    .navbar {
        background-color: #1d3557;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #f1faee;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .navbar h1 {
        margin: 0;
        font-size: 28px;
    }

    .navbar nav a {
        color: #a8dadc;
        margin-left: 20px;
        text-decoration: none;
        font-size: 16px;
        transition: color 0.3s;
    }

    .navbar nav a:hover {
        color: #f1faee;
    }

    /* Dashboard Overview */
    .dashboard-overview {
        padding: 30px;
        text-align: center;
        background-color: #ffffff;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        margin: 20px;
        border-radius: 12px;
    }

    /* Movie Management */
    .movie-management {
        padding: 25px;
        margin: 20px;
        background-color: #ffffff;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }

    .movie-management h3 {
        margin-top: 0;
        color: #1d3557;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        border-radius: 8px;
        overflow: hidden;
    }

    .table th, .table td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }

    .table th {
        background-color: #457b9d;
        color: #ffffff;
    }

    .table tr:nth-child(even) {
        background-color: #f1f1f1;
    }

    .table tr:hover {
        background-color: #e9ecef;
        cursor: pointer;
    }

    .table a {
        color: #007bff;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 5px;
        transition: background-color 0.3s, color 0.3s;
    }

    /* Action Buttons */
    .table .edit-btn {
        background-color: #28a745;
        color: #ffffff;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 5px;
        border: none;
        transition: background-color 0.3s;
    }

    .table .edit-btn:hover {
        background-color: #218838;
    }

    .table .delete-btn {
        background-color: #dc3545;
        color: #ffffff;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 5px;
        border: none;
        margin-left: 5px;
        transition: background-color 0.3s;
    }

    .table .delete-btn:hover {
        background-color: #c82333;
    }

    /* Footer */
    footer {
        background-color: #1d3557;
        color: #f1faee;
        text-align: center;
        padding: 15px 0;
        margin-top: 30px;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .navbar nav a {
            margin-left: 10px;
            font-size: 14px;
        }
        .table th, .table td {
            padding: 8px;
        }
        .edit-btn, .delete-btn {
            padding: 4px 8px;
            font-size: 12px;
        }
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