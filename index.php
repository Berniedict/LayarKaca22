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

        /* Movie Section Styling */
        .movie-section {
            max-width: 800px;
            margin: 3rem auto;
            background-color: #fff;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .movie-section h3 {
            margin-top: 0;
            font-size: 1.8rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }

        .movie-list {
            list-style: none;
            padding: 0;
            margin-top: 1rem;
        }

        .movie-item {
            display: flex;
            align-items: center;
            background-color: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .movie-item img {
            width: 150px; /* Enlarged poster size */
            height: auto;
            border-radius: 4px;
            margin-right: 1rem;
        }

        .movie-details {
            flex: 1;
        }

        .movie-details h4 {
            margin: 0;
            font-size: 1.2rem;
        }

        .movie-details p {
            font-size: 0.9rem;
            color: #555;
            margin: 0.5rem 0;
        }

        .btn {
            background-color: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        /* Responsive Styling */
        @media (max-width: 768px) {
            .movie-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .movie-item img {
                width: 100px;
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

    <!-- Movie Listings -->
    <section class="movie-section">
        <h3>Available Movies</h3>
        <?php if ($is_logged_in): ?>
        <ul class="movie-list">
            <?php foreach ($movies as $movie): ?>
                <li class="movie-item">
                    <!-- Enlarged poster image -->
                    <img src="assets/images/<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                    <div class="movie-details">
                        <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($movie['description'], 0, 100)); ?>...</p>
                        <a href="screens/screenings.php?movie_id=<?php echo $movie['id']; ?>" class="btn">See Screenings</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
            <!-- Message for Unauthenticated Users -->
            <section class="login-prompt">
                <p>Please <a href="screens/login.php">Login</a> or <a href="screens/register.php">Register</a> to start booking your tickets!</p>
            </section>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 LayarKaca22 Cinema Booking</p>
    </footer>

</body>
</html>
