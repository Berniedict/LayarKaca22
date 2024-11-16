<?php
// Database connection
$host = "localhost";
$db = "cinema_food";
$user = "root"; // Replace with your MySQL username
$pass = ""; // Replace with your MySQL password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch food items
$sql = "SELECT * FROM food_items";
$result = $conn->query($sql);

// Initialize variables for order processing
$total_price = 0;
$order_items = [];

// Handle clear order
if (isset($_GET['clear'])) {
    session_start();
    unset($_SESSION['order_items']);
    unset($_SESSION['total_price']);
    header("Location: order.php");
    exit;
}

// Start the session to store the order temporarily
session_start();

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_order'])) {
    // Insert the order into the database after confirmation
    $order_items = $_SESSION['order_items'];
    $total_price = $_SESSION['total_price'];

    // Insert order into database
    if (!empty($order_items)) {
        $items_json = json_encode($order_items);
        $sql = "INSERT INTO orders (items, total_price) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sd", $items_json, $total_price);
        $stmt->execute();
        echo "<p>Order placed successfully! Total: $" . number_format($total_price, 2) . "</p>";

        // Clear session after successful order
        unset($_SESSION['order_items']);
        unset($_SESSION['total_price']);
    } else {
        echo "<p>Please select at least one item.</p>";
    }
}

// Handle item selection and temporary order storage
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $selected_items = $_POST['food'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    foreach ($selected_items as $id) {
        $quantity = isset($quantities[$id]) ? (int)$quantities[$id] : 1; // Default quantity is 1 if not provided
        $sql = "SELECT name, price, image FROM food_items WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $food_result = $stmt->get_result();
        $food = $food_result->fetch_assoc();
        
        $_SESSION['order_items'][] = $food['name'] . " (x" . $quantity . ")";
        $_SESSION['total_price'] += $food['price'] * $quantity;
    }

    header("Location: order.php"); // Redirect to the same page to review order
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Food</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        h1, h2, h3 {
            color: #2c3e50;
        }

        h1 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 1.8em;
            margin-bottom: 20px;
        }

        h3 {
            font-size: 1.5em;
            margin-top: 20px;
        }

        /* Container for form and order summary */
        form, .order-summary {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            max-width: 600px;
            margin-bottom: 30px;
        }

        /* Form Elements */
        input[type="checkbox"] {
            margin-right: 10px;
        }

        input[type="number"], button {
            padding: 8px;
            margin-top: 5px;
            font-size: 1em;
        }

        input[type="number"] {
            width: 60px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            padding: 12px 20px;
            font-size: 1.1em;
            margin-top: 10px;
            width: 100%;
        }

        button:hover {
            background-color: #2ecc71;
        }

        a {
            text-decoration: none;
        }

        a button {
            background-color: #e74c3c;
            width: auto;
            margin-top: 20px;
            padding: 10px 15px;
        }

        a button:hover {
            background-color: #c0392b;
        }

        /* List and order summary */
        ul {
            list-style-type: none;
            margin-top: 15px;
        }

        ul li {
            margin-bottom: 8px;
        }

        strong {
            font-size: 1.2em;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            form, .order-summary {
                max-width: 100%;
                padding: 15px;
            }

            button {
                font-size: 1em;
            }
        }

        /* Styling for menu items */
        .menu-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .menu-item img {
            width: 100px;
            height: auto;
            margin-right: 15px;
            border-radius: 8px;
        }

        .menu-item label {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <h1>Order Food</h1>
    <form method="POST" action="order.php">
        <h2>Menu</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="menu-item">
                <img src="images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <div>
                    <input type="checkbox" name="food[]" value="<?= $row['id'] ?>" id="food_<?= $row['id'] ?>">
                    <label for="food_<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?> - $<?= number_format($row['price'], 2) ?></label><br>
                    <label for="quantity_<?= $row['id'] ?>">Quantity:</label>
                    <input type="number" name="quantity[<?= $row['id'] ?>]" id="quantity_<?= $row['id'] ?>" value="1" min="1"><br><br>
                </div>
            </div>
        <?php endwhile; ?>
        <button type="submit" name="place_order">Place Order</button>
    </form>

    <!-- Show order summary if items have been selected -->
    <?php if (isset($_SESSION['order_items']) && !empty($_SESSION['order_items'])): ?>
        <div class="order-summary">
            <h3>Your Order Summary:</h3>
            <ul>
                <?php foreach ($_SESSION['order_items'] as $item): ?>
                    <li><?= htmlspecialchars($item) ?></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Total Price: $<?= number_format($_SESSION['total_price'], 2) ?></strong></p>
            <form method="POST" action="order.php">
                <button type="submit" name="confirm_order">Confirm Order</button>
            </form>
            <a href="order.php?clear=true"><button>Clear Order</button></a>
        </div>
    <?php endif; ?>
</body>
</html>
