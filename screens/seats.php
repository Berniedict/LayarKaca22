<?php
session_start();
include '../includes/db.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../screens/login.php");
    exit;
}

$screening_id = isset($_GET['screening_id']) ? $_GET['screening_id'] : null;

// Redirect if no screening ID is provided
if (!$screening_id) {
    header("Location: screenings.php");
    exit;
}

// Fetch screening details including movie title
$stmt = $pdo->prepare("SELECT screenings.*, movies.title AS movie_title FROM screenings 
                        JOIN movies ON screenings.movie_id = movies.id 
                        WHERE screenings.id = ?");
$stmt->execute([$screening_id]);
$screening = $stmt->fetch();

// Redirect if the screening does not exist
if (!$screening) {
    header("Location: screenings.php");
    exit;
}

// Fetch all seats for the selected screening
$stmt = $pdo->prepare("SELECT * FROM seats WHERE screening_id = ?");
$stmt->execute([$screening_id]);
$seats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle seat selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_seats'])) {
    $selected_seats = explode(',', $_POST['selected_seats']); // Split seat numbers
    $errors = [];

    // Validate selected seats
    foreach ($selected_seats as $seat_number) {
        $stmt = $pdo->prepare("SELECT available FROM seats WHERE screening_id = ? AND seat_number = ?");
        $stmt->execute([$screening_id, $seat_number]);
        $seat = $stmt->fetch();

        if (!$seat || $seat['available'] == 0) {
            $errors[] = "Seat $seat_number is unavailable.";
        }
    }

    // If no errors, update seat availability and proceed
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            foreach ($selected_seats as $seat_number) {
                $stmt = $pdo->prepare("UPDATE seats SET available = 0 WHERE screening_id = ? AND seat_number = ?");
                $stmt->execute([$screening_id, $seat_number]);
            }
            $pdo->commit();

            // Set booking success details
            $_SESSION['booking_success'] = [
                'movie_title' => $screening['movie_title'],
                'location_name' => 'Cinema XYZ', // Replace with actual location if available
                'showtime' => $screening['showtime'],
                'seats' => $selected_seats,
            ];

            // Redirect to success page
            header("Location: booking-success.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Error processing your booking. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats - <?php echo htmlspecialchars($screening['movie_title']); ?> | LayarKaca22</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Body and Layout */
        body {
            font-family: 'Helvetica Neue', sans-serif;
            background-color: #ffffff; /* White background */
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background-color: #2a2a2a;
            padding: 20px 0;
            text-align: center;
            color: white;
        }

        header h1 {
            font-size: 2rem;
            margin: 0;
        }

        .navbar nav a {
            color: white;
            font-size: 16px;
            margin: 0 20px;
            text-decoration: none;
        }

        .navbar nav a:hover {
            text-decoration: underline;
        }

        .seat-map-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .seat-map-container h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 20px;
        }

        .seat-map-container p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #777;
        }

        .seat-map {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .seat {
            width: 60px;
            height: 60px;
            background: #4caf50;
            color: white;
            font-size: 18px;
            font-weight: bold;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .seat:hover {
            background: #45a049;
            transform: scale(1.1);
        }

        .seat.selected {
            background: #ff9800;
            transform: scale(1.1);
        }

        .seat.unavailable {
            background: #bdbdbd;
            cursor: not-allowed;
        }

        .errors ul {
            padding: 10px;
            background-color: #f44336;
            color: white;
            list-style: none;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .btn {
            background-color: #4caf50;
            color: white;
            padding: 14px 28px;
            border: none;
            font-size: 18px;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #388e3c;
        }

        footer {
            background-color: #2a2a2a;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            position: absolute;
            width: 100%;
            bottom: 0;
        }

        @media (max-width: 768px) {
            .seat-map {
                grid-template-columns: repeat(5, 1fr);
            }

            .seat {
                width: 50px;
                height: 50px;
                font-size: 14px;
            }

            .btn {
                padding: 12px 24px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="navbar">
        <h1>LayarKaca22</h1>
        <nav>
            <a href="../screens/screenings.php">Back</a>
        </nav>
    </div>
</header>

<section class="seat-map-container">
    <h2>Select Your Seats</h2>
    <p><strong><?php echo htmlspecialchars($screening['movie_title']); ?></strong> - Showtime: <?php echo date("F j, Y, g:i a", strtotime($screening['showtime'])); ?></p>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="seats.php?screening_id=<?php echo $screening_id; ?>">
        <div class="seat-map">
            <?php foreach ($seats as $seat): ?>
                <div class="seat <?php echo $seat['available'] == 0 ? 'unavailable' : ''; ?>" 
                     data-seat-number="<?php echo $seat['seat_number']; ?>" 
                     onclick="toggleSeatSelection(this)">
                     <?php echo htmlspecialchars($seat['seat_number']); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="selected_seats" id="selected_seats" value="">
        <button type="submit" class="btn">Confirm Selection</button>
    </form>
</section>

<footer>
    <p>&copy; 2024 LayarKaca22 Cinema Booking</p>
</footer>

<script>
function toggleSeatSelection(seatElement) {
    if (!seatElement.classList.contains('unavailable')) {
        seatElement.classList.toggle('selected');
        let selectedSeats = [];
        document.querySelectorAll('.seat.selected').forEach(seat => {
            selectedSeats.push(seat.getAttribute('data-seat-number'));
        });
        document.getElementById('selected_seats').value = selectedSeats.join(',');
    }
}
</script>

</body>
</html>
