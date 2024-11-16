<?php
include '../includes/db.php'; // Include database connection
session_start(); // Start session

// Ensure only admin can access this page
if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php'); // Redirect non-admins to the homepage
    exit();
}

$errors = [];
$movie = null;

// Get the movie ID from the URL
if (isset($_GET['movie_id'])) {
    $movie_id = $_GET['movie_id'];

    // Fetch movie details from the database
    $sql = "SELECT * FROM movies WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch();

    // If the movie doesn't exist
    if (!$movie) {
        header('Location: admin-dashboard.php'); // Redirect if movie not found
        exit();
    }
} else {
    header('Location: admin-dashboard.php'); // Redirect if no movie ID is provided
    exit();
}

// Form handling and validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $genre = trim($_POST['genre']);
    $poster_name = $movie['poster']; // Keep the original poster if not replaced

    // Validate the movie poster upload (if any)
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $poster = $_FILES['poster'];
        $poster_name = time() . '_' . basename($poster['name']);
        $poster_tmp_name = $poster['tmp_name'];
        $poster_path = '../assets/images/' . $poster_name;

        // Allowed image extensions
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $image_extension = strtolower(pathinfo($poster_name, PATHINFO_EXTENSION));

        // Check for valid image file
        if (!in_array($image_extension, $allowed_extensions)) {
            $errors[] = "Invalid image file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }

        // Move the uploaded file to the destination directory
        if (empty($errors) && !move_uploaded_file($poster_tmp_name, $poster_path)) {
            $errors[] = "Failed to upload poster image.";
        }
    }

    // If no errors, update movie details in the database
    if (empty($errors)) {
        $sql = "UPDATE movies SET title = ?, description = ?, genre = ?, poster = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$title, $description, $genre, $poster_name, $movie_id])) {
            // If the poster was changed, delete the old one
            if ($poster_name !== $movie['poster'] && file_exists('../assets/images/' . $movie['poster'])) {
                unlink('../assets/images/' . $movie['poster']);
            }

            header('Location: admin-dashboard.php'); // Redirect to admin dashboard after successful update
            exit();
        } else {
            $errors[] = "Failed to update movie.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Movie - Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

    <h1>Edit Movie</h1>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit-movie.php?movie_id=<?php echo $movie['id']; ?>" enctype="multipart/form-data">
        <label for="title">Movie Title:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($movie['description']); ?></textarea>

        <label for="genre">Genre:</label>
        <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($movie['genre']); ?>" required>

        <label for="poster">Poster Image (Leave blank to keep the current one):</label>
        <input type="file" id="poster" name="poster">

        <!-- Show current poster image -->
        <div class="current-poster">
            <h4>Current Poster:</h4>
            <img src="../assets/images/<?php echo htmlspecialchars($movie['poster']); ?>" alt="Current Poster" class="movie-poster">
        </div>

        <button type="submit">Update Movie</button>
    </form>

    <p><a href="admin-dashboard.php">Back to Dashboard</a></p>

</body>
</html>