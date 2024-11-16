<?php
session_start();
include '../includes/db.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../screens/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$screening_id = isset($_GET['screening_id']) ? $_GET['screening_id'] : null;

// Redirect if no screening ID is provided
if (!$screening_id) {
    header("Location: screenings.php");
    exit;
}

// Fetch screening details including movie title
$stmt = $pdo->prepare("SELECT screenings.*, movies.title AS movie_title, movies.id AS movie_id, locations.id AS location_id 
                        FROM screenings 
                        JOIN movies ON screenings.movie_id = movies.id 
                        JOIN locations ON screenings.location_id = locations.id
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
            // Update seat availability to 'unavailable'
            foreach ($selected_seats as $seat_number) {
                $stmt = $pdo->prepare("UPDATE seats SET available = 0 WHERE screening_id = ? AND seat_number = ?");
                $stmt->execute([$screening_id, $seat_number]);
            }

            // Insert the booking into the database
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, movie_id, location_id, showtime, seats, created_at) 
                VALUES (:user_id, :movie_id, :location_id, :showtime, :seats, NOW())
            ");
            $stmt->execute([
                ':user_id' => $user_id,
                ':movie_id' => $screening['movie_id'],
                ':location_id' => $screening['location_id'],
                ':showtime' => $screening['showtime'],
                ':seats' => implode(',', $selected_seats) // Store seats as a comma-separated string
            ]);

            // Commit the transaction
            $pdo->commit();

            // Set booking success details in session
            $_SESSION['booking_success'] = [
                'movie_title' => $screening['movie_title'],
                'movie_id' => $screening['movie_id'],
                'location_name' => 'Cinema XYZ', // Replace with actual location if available
                'location_id' => $screening['location_id'],
                'showtime' => $screening['showtime'],
                'seats' => $selected_seats,
            ];

            // Redirect to booking success page
            header("Location: booking-success.php");
            exit; // Ensure no further code is executed after the redirect
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
        /* Basic layout and container styling */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f4f4;
    padding: 20px;
}

header {
    background-color: #333;
    color: #fff;
    padding: 10px 20px;
    text-align: center;
}

header .navbar h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 500;
}

header .navbar nav a {
    color: #fff;
    text-decoration: none;
    margin-left: 20px;
}

header .navbar nav a:hover {
    text-decoration: underline;
}

footer {
    text-align: center;
    margin-top: 30px;
    font-size: 14px;
    color: #777;
}

/* Seat map container */
.seat-map-container {
    text-align: center;
    margin-top: 30px;
}

.seat-map-container h2 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 20px;
}

.seat-map-container p {
    font-size: 1rem;
    color: #555;
    margin-bottom: 30px;
}

/* Seat map grid */
.seat-map {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 12px;
    max-width: 800px;
    margin: 0 auto;
}

.seat {
    width: 50px;
    height: 50px;
    background-color: #e0e0e0;
    border: 2px solid #ddd;
    border-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.seat:hover {
    background-color: #00bcd4;
    transform: scale(1.05);
}

.seat.selected {
    background-color: #4CAF50;
    color: white;
    transform: scale(1.1);
}

.seat.unavailable {
    background-color: #f44336;
    color: white;
    cursor: not-allowed;
}

/* Error messages */
.errors {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 14px;
}

.errors ul {
    padding-left: 20px;
}

.errors li {
    list-style-type: disc;
}

/* Form and button styling */
form {
    margin-top: 30px;
}

button[type="submit"].btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    border-radius: 4px;
    transition: background-color 0.3s ease;
    margin-top: 20px; /* Added margin-top to move button down */
}

button[type="submit"].btn:hover {
    background-color: #45a049;
}

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .seat-map {
        grid-template-columns: repeat(5, 1fr);
    }

    .seat {
        width: 40px;
        height: 40px;
    }

    button.btn {
        font-size: 16px;
        padding: 12px 25px;
    }
}

    </style>
</head>
<body>
<header>
    <div class="navbar">
        <h1>LayarKaca22</h1>
        <nav>
            <a href="/index.php">Back</a>
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
        updateSelectedSeats();
    }
}
function updateSelectedSeats() {
    let selectedSeats = [];
    document.querySelectorAll('.seat.selected').forEach(seat => {
        selectedSeats.push(seat.getAttribute('data-seat-number'));
    });
    document.getElementById('selected_seats').value = selectedSeats.join(',');
}
</script>
</body>
</html>