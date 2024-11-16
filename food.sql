CREATE TABLE food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) NOT NULL
);


CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    items TEXT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample food items
INSERT INTO food_items (name, price) VALUES
('Popcorn', 5.00),
('Hot Dog', 3.50),
('Soda', 2.00),
('Nachos', 4.50);
