<?php
session_start();
include '../includes/db.php'; // Include the database connection

// Check if the user is an admin, if not redirect to login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../screens/login.php');
    exit();
}

// Check if the movie_id is provided
if (isset($_GET['movie_id'])) {
    $movie_id = $_GET['movie_id'];

    // Get the movie details to delete the poster file as well
    $sql = "SELECT * FROM movies WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch();

    if ($movie) {
        // Delete the associated seats first
        $sql = "DELETE FROM seats WHERE screening_id IN (SELECT id FROM screenings WHERE movie_id = ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$movie_id]);

        // Delete the associated screenings
        $sql = "DELETE FROM screenings WHERE movie_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$movie_id]);

        // Delete the movie record from the database
        $sql = "DELETE FROM movies WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$movie_id])) {
            // Delete the associated movie poster from the server if exists
            $poster_path = '../assets/images/' . $movie['poster'];
            if (file_exists($poster_path)) {
                unlink($poster_path);
            }
            header('Location: admin-dashboard.php'); // Redirect back to the admin dashboard after deletion
            exit();
        } else {
            echo "Error: Could not delete the movie.";
        }
    } else {
        echo "Error: Movie not found.";
    }
} else {
    echo "Error: Movie ID is missing.";
}
?>
