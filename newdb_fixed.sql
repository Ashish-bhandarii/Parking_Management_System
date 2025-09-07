-- Fixed Database Schema for Parking Management System
-- This file corrects all inconsistencies found in the original newdb.sql

-- Users Table
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicle Types Table (Independent Table)
CREATE TABLE vehicle_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name ENUM('two_wheeler', 'four_wheeler') NOT NULL 
);

-- Insert Default Data for Vehicle Types (Before Referencing)
INSERT INTO vehicle_types (type_name) VALUES ('two_wheeler'), ('four_wheeler');

-- Vehicle Categories Table (Depends on Vehicle Types)
CREATE TABLE vehicle_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    type_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES vehicle_types(type_id) ON DELETE CASCADE
);

-- Parking Areas Table (FIXED: Now includes type_id from the start)
CREATE TABLE parking_areas (
    area_id INT AUTO_INCREMENT PRIMARY KEY,
    area_name VARCHAR(100) NOT NULL,
    total_slots INT NOT NULL,
    reserved_slots INT DEFAULT 0,
    available_slots INT NOT NULL,
    map_iframe_url TEXT,
    type_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES vehicle_types(type_id) ON DELETE CASCADE
);

-- Slots Table (Depends on Parking Areas)
CREATE TABLE slots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    parking_area_id INT NOT NULL,
    slot_name VARCHAR(10) NOT NULL,
    is_reserved BOOLEAN DEFAULT FALSE,
    small_vehicles_count INT DEFAULT 0,
    max_small_vehicles INT DEFAULT 3,
    FOREIGN KEY (parking_area_id) REFERENCES parking_areas(area_id) ON DELETE CASCADE
);

-- Fare Rates Table (Depends on Parking Areas & Vehicle Types & Categories)
CREATE TABLE FareRates (
    rate_id INT AUTO_INCREMENT PRIMARY KEY,
    area_id INT NOT NULL,
    category_id INT NOT NULL,
    type_id INT NOT NULL,
    hourly_rate DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (area_id) REFERENCES parking_areas(area_id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES vehicle_types(type_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES vehicle_categories(category_id) ON DELETE CASCADE
);

-- Reservations Table (Depends on Users, Parking Areas, & Vehicle Data)
CREATE TABLE Reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    area_id INT NOT NULL,
    slot_number INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('Pending','Approved','Declined','Cancelled','Expired','Completed') DEFAULT 'Pending',
    late_notification_sent BOOLEAN DEFAULT FALSE,
    vehicle_status ENUM('IN', 'OUT') DEFAULT 'OUT',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    vehicle_id INT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (area_id) REFERENCES parking_areas(area_id) ON DELETE CASCADE
);

-- Vehicle Data Table (Depends on Users & Parking Areas)
CREATE TABLE VehicleData (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_number VARCHAR(20) NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    area_id INT NOT NULL,
    entry_time DATETIME,
    exit_time DATETIME,
    fare_paid DECIMAL(10, 2) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (area_id) REFERENCES parking_areas(area_id) ON DELETE CASCADE
);

-- Contact Messages Table (Independent)
CREATE TABLE ContactMessages (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System Settings Table (Independent)
CREATE TABLE SystemSettings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    timezone VARCHAR(50) DEFAULT 'Asia/Kathmandu',
    currency VARCHAR(10) DEFAULT 'NPR',
    grace_period_minutes INT DEFAULT 15
);

-- Notifications Table (Depends on Users & Reservations)
CREATE TABLE Notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reservation_id INT NOT NULL,
    message TEXT NOT NULL,
    read_status BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES Reservations(reservation_id) ON DELETE CASCADE
);

-- Sample Data Insert Script for Parking Management System
-- This script inserts comprehensive sample data for testing

-- Insert 2 Sample Users
INSERT INTO Users (name, email, phone, password, user_type) VALUES 
('user1', 'user1@parking.com', '1234567890', '$2y$10$L4GztpAB/b1TQ2aooSMAhe5iB0hAfzb7CrcR1pTPQOKylAIvaXrsm', 'user'),
('user2', 'user2@parking.com', '0987654321', '$2y$10$L4GztpAB/b1TQ2aooSMAhe5iB0hAfzb7CrcR1pTPQOKylAIvaXrsm', 'user');

-- Insert Admin User
INSERT INTO Users (name, email, phone, password, user_type) VALUES 
('Admin User', 'admin@parking.com', '5555555555', '$2y$10$L4GztpAB/b1TQ2aooSMAhe5iB0hAfzb7CrcR1pTPQOKylAIvaXrsm', 'admin');

-- Admin: admin@parking.com
-- User 1: user1@parking.com
-- User 2: jane.smith@email.com
-- Insert Vehicle Categories (if not already present)
INSERT INTO vehicle_categories (category_name, type_id) VALUES 
('Motorcycle', 1),
('Scooter', 1),
('Moped', 1),
('Sport Bike', 1),
('Car', 2),
('Jeep', 2),
('Van', 2),
('SUV', 2),
('Pickup', 2);

-- Insert 5 Parking Areas
INSERT INTO parking_areas (area_name, total_slots, reserved_slots, available_slots, type_id, map_iframe_url) VALUES 
('Two Wheeler Zone A', 20, 0, 20, 1, 'https://www.openstreetmap.org/export/embed.html?bbox=85.3150,27.7000,85.3250,27.7100&layer=mapnik'),
('Two Wheeler Zone B', 15, 0, 15, 1, 'https://www.openstreetmap.org/export/embed.html?bbox=85.3200,27.7050,85.3300,27.7150&layer=mapnik'),
('Four Wheeler Zone A', 12, 0, 12, 2, 'https://www.openstreetmap.org/export/embed.html?bbox=85.3100,27.6950,85.3200,27.7050&layer=mapnik'),
('Four Wheeler Zone B', 18, 0, 18, 2, 'https://www.openstreetmap.org/export/embed.html?bbox=85.3250,27.7100,85.3350,27.7200&layer=mapnik'),
('Mixed Vehicle Zone', 25, 0, 25, 2, 'https://www.openstreetmap.org/export/embed.html?bbox=85.3150,27.7000,85.3250,27.7100&layer=mapnik');

-- Insert Slots for Parking Areas
-- Two Wheeler Zone A (20 slots)
INSERT INTO slots (parking_area_id, slot_name) VALUES 
(1, 'slot_1'), (1, 'slot_2'), (1, 'slot_3'), (1, 'slot_4'), (1, 'slot_5'),
(1, 'slot_6'), (1, 'slot_7'), (1, 'slot_8'), (1, 'slot_9'), (1, 'slot_10'),
(1, 'slot_11'), (1, 'slot_12'), (1, 'slot_13'), (1, 'slot_14'), (1, 'slot_15'),
(1, 'slot_16'), (1, 'slot_17'), (1, 'slot_18'), (1, 'slot_19'), (1, 'slot_20');

-- Two Wheeler Zone B (15 slots)
INSERT INTO slots (parking_area_id, slot_name) VALUES 
(2, 'slot_1'), (2, 'slot_2'), (2, 'slot_3'), (2, 'slot_4'), (2, 'slot_5'),
(2, 'slot_6'), (2, 'slot_7'), (2, 'slot_8'), (2, 'slot_9'), (2, 'slot_10'),
(2, 'slot_11'), (2, 'slot_12'), (2, 'slot_13'), (2, 'slot_14'), (2, 'slot_15');

-- Four Wheeler Zone A (12 slots)
INSERT INTO slots (parking_area_id, slot_name) VALUES 
(3, 'slot_1'), (3, 'slot_2'), (3, 'slot_3'), (3, 'slot_4'), (3, 'slot_5'),
(3, 'slot_6'), (3, 'slot_7'), (3, 'slot_8'), (3, 'slot_9'), (3, 'slot_10'),
(3, 'slot_11'), (3, 'slot_12');

-- Four Wheeler Zone B (18 slots)
INSERT INTO slots (parking_area_id, slot_name) VALUES 
(4, 'slot_1'), (4, 'slot_2'), (4, 'slot_3'), (4, 'slot_4'), (4, 'slot_5'),
(4, 'slot_6'), (4, 'slot_7'), (4, 'slot_8'), (4, 'slot_9'), (4, 'slot_10'),
(4, 'slot_11'), (4, 'slot_12'), (4, 'slot_13'), (4, 'slot_14'), (4, 'slot_15'),
(4, 'slot_16'), (4, 'slot_17'), (4, 'slot_18');

-- Mixed Vehicle Zone (25 slots)
INSERT INTO slots (parking_area_id, slot_name) VALUES 
(5, 'slot_1'), (5, 'slot_2'), (5, 'slot_3'), (5, 'slot_4'), (5, 'slot_5'),
(5, 'slot_6'), (5, 'slot_7'), (5, 'slot_8'), (5, 'slot_9'), (5, 'slot_10'),
(5, 'slot_11'), (5, 'slot_12'), (5, 'slot_13'), (5, 'slot_14'), (5, 'slot_15'),
(5, 'slot_16'), (5, 'slot_17'), (5, 'slot_18'), (5, 'slot_19'), (5, 'slot_20'),
(5, 'slot_21'), (5, 'slot_22'), (5, 'slot_23'), (5, 'slot_24'), (5, 'slot_25');

-- Insert Fare Rates for All Combinations
-- Two Wheeler Zone A rates
INSERT INTO FareRates (area_id, category_id, type_id, hourly_rate) VALUES 
(1, 1, 1, 10.00),  -- Motorcycle
(1, 2, 1, 8.00),   -- Scooter
(1, 3, 1, 7.00),   -- Moped
(1, 4, 1, 12.00);  -- Sport Bike

-- Two Wheeler Zone B rates
INSERT INTO FareRates (area_id, category_id, type_id, hourly_rate) VALUES 
(2, 1, 1, 9.00),   -- Motorcycle
(2, 2, 1, 7.50),   -- Scooter
(2, 3, 1, 6.50),   -- Moped
(2, 4, 1, 11.00);  -- Sport Bike

-- Four Wheeler Zone A rates
INSERT INTO FareRates (area_id, category_id, type_id, hourly_rate) VALUES 
(3, 5, 2, 25.00),  -- Car
(3, 6, 2, 30.00),  -- Jeep
(3, 7, 2, 35.00),  -- Van
(3, 8, 2, 32.00),  -- SUV
(3, 9, 2, 28.00);  -- Pickup

-- Four Wheeler Zone B rates
INSERT INTO FareRates (area_id, category_id, type_id, hourly_rate) VALUES 
(4, 5, 2, 22.00),  -- Car
(4, 6, 2, 27.00),  -- Jeep
(4, 7, 2, 32.00),  -- Van
(4, 8, 2, 29.00),  -- SUV
(4, 9, 2, 25.00);  -- Pickup

-- Mixed Vehicle Zone rates (both types allowed)
INSERT INTO FareRates (area_id, category_id, type_id, hourly_rate) VALUES 
-- Two wheelers in mixed zone
(5, 1, 1, 12.00),  -- Motorcycle
(5, 2, 1, 10.00),  -- Scooter
(5, 3, 1, 9.00),   -- Moped
(5, 4, 1, 14.00),  -- Sport Bike
-- Four wheelers in mixed zone
(5, 5, 2, 28.00),  -- Car
(5, 6, 2, 33.00),  -- Jeep
(5, 7, 2, 38.00),  -- Van
(5, 8, 2, 35.00),  -- SUV
(5, 9, 2, 31.00);  -- Pickup

-- Insert Sample Vehicle Data
INSERT INTO VehicleData (user_id, vehicle_number, vehicle_type, area_id, entry_time) VALUES 
(1, 'BA 1 PA 1234', 'Motorcycle', 1, '2024-01-15 09:00:00'),
(2, 'BA 2 PA 5678', 'Car', 3, '2024-01-15 10:30:00');

-- Insert Sample Reservations
INSERT INTO Reservations (user_id, area_id, slot_number, start_time, end_time, status, vehicle_id) VALUES 
(1, 1, 1, '2024-01-16 08:00:00', '2024-01-16 12:00:00', 'Approved', 1),
(2, 3, 1, '2024-01-16 09:00:00', '2024-01-16 17:00:00', 'Pending', 2);

-- Insert Sample Contact Messages
INSERT INTO ContactMessages (name, email, message) VALUES 
('Test User', 'test@email.com', 'This is a test message for the contact system.'),
('Another User', 'another@email.com', 'I have a question about parking rates and availability.');

-- Insert System Settings
INSERT INTO SystemSettings (timezone, currency, grace_period_minutes) VALUES 
('Asia/Kathmandu', 'NPR', 15);

-- Insert Sample Notifications
INSERT INTO Notifications (user_id, reservation_id, message, read_status) VALUES 
(1, 1, 'Your reservation has been approved!', FALSE),
(2, 2, 'Your reservation is pending approval.', FALSE);

-- Update parking areas available slots count
UPDATE parking_areas SET available_slots = total_slots - reserved_slots;
