CREATE DATABASE IF NOT EXISTS store_db;
USE store_db;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL DEFAULT 'Umum',
    price DECIMAL(12,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data Dummy
INSERT INTO products (name, category, price, stock) VALUES 
('Laptop Pro 15', 'Elektronik', 15000000, 10),
('Kopi Arabica 200g', 'Makanan', 75000, 50);