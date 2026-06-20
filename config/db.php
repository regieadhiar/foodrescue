<?php
// config/db.php

$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$dbname = 'foodrescue';

try {
    // 1. First connect without selecting database to ensure it exists
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 2. Re-connect with database selected
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // 3. Create Tables if they do not exist
    
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'rescuer',
        remember_token VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // Merchants table
    $pdo->exec("CREATE TABLE IF NOT EXISTS merchants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        business_name VARCHAR(100) NOT NULL,
        address TEXT NOT NULL,
        latitude DOUBLE NOT NULL,
        longitude DOUBLE NOT NULL,
        phone VARCHAR(20) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // Food items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS food_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        merchant_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        image_url TEXT NULL,
        original_price DECIMAL(10,2) NOT NULL,
        rescue_price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        expiry_time DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // Orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        food_item_id INT NOT NULL,
        rescuer_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        payment_method VARCHAR(20) NOT NULL DEFAULT 'cash',
        payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE,
        FOREIGN KEY (rescuer_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // Dynamic schema update: Alter orders table if columns do not exist
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(20) NOT NULL DEFAULT 'cash'");
    } catch (PDOException $e) {
        // Column already exists
    }
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
    } catch (PDOException $e) {
        // Column already exists
    }

    // Add password reset columns to users table if they don't exist
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(100) NULL");
    } catch (PDOException $e) {
        // Column already exists
    }
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL");
    } catch (PDOException $e) {
        // Column already exists
    }

    // 4. Seed default Admin if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $adminUsername = 'admin';
        $adminEmail = 'admin@foodrescue.com';
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $seedStmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
        $seedStmt->execute([$adminUsername, $adminEmail, $adminPassword]);
    }

} catch (PDOException $e) {
    // Return connection error formatted nicely for debugging
    die("Database connection failed: " . $e->getMessage() . ". Please ensure your MySQL server is running and configurations in config/db.php match.");
}
