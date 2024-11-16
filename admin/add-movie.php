<?php
include '../includes/db.php'; // Include database connection
session_start(); // Start session

// Ensure only admin can access this page
if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php'); // Redirect non-admins to the homepage
    exit();
}

$errors = [];
$title = $description = $genre = $poster = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate the form inputs
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $genre = trim($_POST['genre']);
    
    // Validate file upload (poster image)
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
    } else {
        $errors[] = "Please upload a movie poster image.";
    }

    // Insert movie into database if no errors
    if (empty($errors)) {
        $sql = "INSERT INTO movies (title, description, genre, poster) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$title, $description, $genre, $poster_name])) {
            header('Location: admin-dashboard.php'); // Redirect to admin dashboard after success
            exit();
        } else {
            $errors[] = "Failed to add movie.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie - Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

    <h1>Add New Movie</h1>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="add-movie.php" enctype="multipart/form-data">
        <label for="title">Movie Title:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>

        <label for="genre">Genre:</label>
        <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($genre); ?>" required>

        <label for="poster">Poster Image:</label>
        <input type="file" id="poster" name="poster" required>

        <button type="submit">Add Movie</button>
    </form>

    <p><a href="admin-dashboard.php">Back to Dashboard</a></p>

</body>
</html>