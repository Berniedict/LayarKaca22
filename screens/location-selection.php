<?php
session_start();
include 'includes/db.php'; // Include the database connection

// Get movie_id and location from URL
$movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : 0;
$location = isset($_GET['location']) ? $_GET['location'] : '';

if ($movie_id && $location) {
    // Fetch the movie details
    $sql = "SELECT * FROM movies WHERE id = :movie_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['movie_id' => $movie_id]);
    $movie = $stmt->fetch();

    // Fetch available screenings for the selected movie and location
    $sql_screenings = "SELECT * FROM screenings WHERE movie_id = :movie_id AND location = :location";
    $stmt = $pdo->prepare($sql_screenings);
    $stmt->execute(['movie_id' => $movie_id, 'location' => $location]);
    $screenings = $stmt->fetchAll();
} else {
    // If no movie_id or location is passed, redirect or show error
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screenings at <?php echo $location; ?> - <?php echo $movie['title']; ?></title>
</head>
<body>

    <header>
        <h1>Screenings at <?php echo $location; ?> for <?php echo $movie['title']; ?></h1>
    </header>

    <!-- Display screening times for the selected movie and location -->
    <section class="screening-times">
        <h3>Select a Screening Time</h3>
        <?php if (count($screenings) > 0): ?>
            <ul>
                <?php foreach ($screenings as $screening): ?>
                    <li>
                        <?php echo $screening['time']; ?>
                        <a href="seat-selection.php?screening_id=<?php echo $screening['id']; ?>" class="btn">Choose Seat</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No screenings available at this location.</p>
        <?php endif; ?>
    </section>

</body>
</html>