<?php
require __DIR__ . '/app/core/Database.php';

try {
    Database::execute('ALTER TABLE Booking ALTER COLUMN NgayNhan DATETIME NOT NULL');
    Database::execute('ALTER TABLE Booking ALTER COLUMN NgayTra DATETIME NOT NULL');
    echo "Columns altered successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
