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
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
            color: #333;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* Container */
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Form elements */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 1rem;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        input[type="text"], textarea, input[type="file"] {
            padding: 12px;
            font-size: 1rem;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        input[type="file"] {
            border: none;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Error messages */
        .errors {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .errors ul {
            list-style-type: none;
        }

        .errors li {
            margin-bottom: 10px;
        }

        .current-poster {
            margin-bottom: 20px;
        }

        .current-poster img {
            max-width: 100%;
            border-radius: 4px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-align: center;
            color: #3498db;
            font-size: 1rem;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

    <h1>Edit Movie</h1>

    <div class="form-container">
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

            <div class="current-poster">
                <h4>Current Poster:</h4>
                <img src="../assets/images/<?php echo htmlspecialchars($movie['poster']); ?>" alt="Current Poster" class="movie-poster">
            </div>

            <button type="submit">Update Movie</button>
        </form>

        <p><a href="admin-dashboard.php" class="back-link">Back to Dashboard</a></p>
    </div>

</body>
</html>
