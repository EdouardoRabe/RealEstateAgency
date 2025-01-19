CREATE DATABASE agence;

use agence;

-- Create the table for habitation types
CREATE TABLE agence_habitation_type (
    id_habitation_type INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL
);

-- Insert test data into agence_habitation_type
INSERT INTO agence_habitation_type (name) 
VALUES
('House'),
('Apartment'),
('Studio');

-- Create the table for habitations (with the foreign key reference to agence_habitation_type)
CREATE TABLE agence_habitations (
    id_habitations INT PRIMARY KEY AUTO_INCREMENT,
    type INT NOT NULL,  -- Foreign key to agence_habitation_type
    nb_chambres INT NOT NULL,
    loyer DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),  -- Path to image or URL
    quartier VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (type) REFERENCES agence_habitation_type(id_habitation_type)
);

-- Inserting test data into agence_user (already included)
INSERT INTO agence_user (name, first_name, role, email, password, phone_number) 
VALUES
('John', 'Doe', 'Admin', 'john.doe@example.com', 'password123', '0123456789'),
('Jane', 'Smith', 'User', 'jane.smith@example.com', 'password456', '0987654321'),
('Alice', 'Brown', 'User', 'alice.brown@example.com', 'password789', '0112233445');

-- Inserting test data into agence_habitations (20 entries)
INSERT INTO agence_habitations (type, nb_chambres, loyer, image, quartier, description)
VALUES
(1, 3, 120.50, 'image1.jpg', 'Downtown', '3-bedroom house with a beautiful garden'),
(2, 1, 75.00, 'image2.jpg', 'Uptown', 'Cozy studio apartment, perfect for a single person'),
(3, 2, 95.00, 'image3.jpg', 'Suburbs', '2-bedroom apartment near the park'),
(1, 4, 150.00, 'image4.jpg', 'City Center', 'Large 4-bedroom house with a swimming pool'),
(2, 1, 65.00, 'image5.jpg', 'Old Town', 'Affordable studio in the heart of the city'),
(3, 3, 110.00, 'image6.jpg', 'Downtown', 'Modern 3-bedroom apartment with a balcony'),
(1, 5, 180.00, 'image7.jpg', 'West End', 'Spacious 5-bedroom house with a garden and garage'),
(2, 2, 85.00, 'image8.jpg', 'East Side', '2-bedroom studio with a small terrace'),
(1, 3, 125.00, 'image9.jpg', 'Central Park', '3-bedroom house with a park view'),
(3, 2, 95.50, 'image10.jpg', 'Greenwich', '2-bedroom apartment close to shops and transport'),
(2, 1, 70.00, 'image11.jpg', 'Sunnydale', 'Studio apartment in a quiet neighborhood'),
(1, 4, 140.00, 'image12.jpg', 'Beachside', '4-bedroom house with ocean view'),
(3, 2, 100.00, 'image13.jpg', 'Riverside', '2-bedroom apartment near the river'),
(2, 1, 60.00, 'image14.jpg', 'Old Town', 'Small studio perfect for a student'),
(1, 3, 130.00, 'image15.jpg', 'Uptown', 'Charming 3-bedroom house with a garden'),
(3, 2, 105.00, 'image16.jpg', 'Downtown', '2-bedroom apartment with a modern kitchen'),
(2, 1, 80.00, 'image17.jpg', 'Central City', 'Cozy studio with all essentials included'),
(1, 5, 170.00, 'image18.jpg', 'Forest Area', '5-bedroom house with a large backyard'),
(3, 2, 92.00, 'image19.jpg', 'North Side', '2-bedroom apartment near the metro station'),
(2, 3, 110.00, 'image20.jpg', 'South Hill', '3-bedroom apartment with a scenic view');

-- Inserting test data into agence_reservation (already included)
INSERT INTO agence_reservation (arrival, departure, id_user)
VALUES
('2025-02-10', '2025-02-15', 2),  -- Jane Smith reserves
('2025-03-01', '2025-03-05', 1),  -- John Doe reserves
('2025-04-20', '2025-04-25', 3);  -- Alice Brown reserves

