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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        h1 {
            text-align: center;
            color: #212529;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
        }
        .btn {
            display: block;
            width: 100%;
            background-color: #007bff;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .errors {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .errors ul {
            padding-left: 20px;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
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
            <div class="form-group">
                <label for="title">Movie Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="genre">Genre:</label>
                <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($genre); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="poster">Poster Image:</label>
                <input type="file" id="poster" name="poster" required>
            </div>
            
            <button type="submit" class="btn">Add Movie</button>
        </form>

        <a href="admin-dashboard.php">&#8592; Back to Dashboard</a>
    </div>
</body>
</html>
