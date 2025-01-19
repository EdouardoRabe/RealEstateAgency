
-- Supprimer la base si elle existe déjà
DROP DATABASE IF EXISTS agence;

CREATE DATABASE agence;

use agence;

-- 1. Create table for agence_user
CREATE TABLE agence_user (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'User') NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL
);





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
    id_habitation INT PRIMARY KEY AUTO_INCREMENT,
    type INT NOT NULL, -- Référence à agence_habitation_type
    nb_chambres INT NOT NULL,
    loyer DECIMAL(10, 2) NOT NULL,
    quartier VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (type) REFERENCES agence_habitation_type(id_habitation_type)
);

-- 3. Create table for agence_reservation
CREATE TABLE agence_reservation (
    id_reservation INT PRIMARY KEY AUTO_INCREMENT,
    arrival DATE NOT NULL,
    departure DATE NOT NULL,
    id_user INT,
    id_habitation INT,
    FOREIGN KEY (id_user) REFERENCES agence_user(id_user),
    FOREIGN KEY (id_habitation) REFERENCES agence_habitations(id_habitation)
);


-- 4. Créer la table pour les images des habitations
CREATE TABLE agence_habitation_images (
    id_image INT PRIMARY KEY AUTO_INCREMENT,
    id_habitation INT NOT NULL, -- Référence à agence_habitations
    image_path VARCHAR(255) NOT NULL, -- Chemin ou URL de l'image
    FOREIGN KEY (id_habitation) REFERENCES agence_habitations(id_habitation)
);

-- Inserting test data into agence_user (already included)
INSERT INTO agence_user (name, first_name, role, email, password, phone_number) 
VALUES
('John', 'Doe', 'Admin', 'john.doe@example.com', 'password123', '0123456789'),
('Jane', 'Smith', 'User', 'jane.smith@example.com', 'password456', '0987654321'),
('Alice', 'Brown', 'User', 'alice.brown@example.com', 'password789', '0112233445');

-- Insérer des données de test dans agence_habitations avec les types 1 (House), 2 (Apartment), 3 (Studio)
INSERT INTO agence_habitations (type, nb_chambres, loyer, quartier, description)
VALUES
(1, 3, 120.50, 'Downtown', '3-bedroom house with a beautiful garden'),
(2, 1, 75.00, 'Uptown', 'Cozy studio apartment, perfect for a single person'),
(3, 2, 95.00, 'Suburbs', '2-bedroom apartment near the park'),
(1, 4, 150.00, 'City Center', 'Large 4-bedroom house with a swimming pool'),
(2, 2, 85.00, 'Countryside', 'Charming 2-bedroom cottage in the countryside'),
(1, 5, 200.00, 'Beachfront', '5-bedroom house with ocean views'),
(2, 6, 300.00, 'Luxury Hills', 'Luxurious 6-bedroom villa with modern amenities'),
(3, 1, 70.00, 'Old Town', 'Affordable studio in the heart of the city'),
(1, 3, 110.00, 'Riverside', '3-bedroom house with river views'),
(2, 1, 80.00, 'Mountain View', 'Cozy studio with mountain views'),
(1, 4, 160.00, 'Downtown', 'Spacious 4-bedroom house in a prime location'),
(2, 2, 90.00, 'University District', '2-bedroom apartment near campus'),
(1, 5, 250.00, 'Seaside', '5-bedroom house with private beach access'),
(3, 1, 65.00, 'Historic District', 'Studio with a vintage feel'),
(2, 3, 120.00, 'City Center', '3-bedroom apartment in the heart of the city'),
(1, 6, 220.00, 'Suburbs', 'Large 6-bedroom house with a garden'),
(3, 2, 100.00, 'University District', 'Affordable 2-bedroom studio apartment'),
(2, 4, 180.00, 'Luxury Hills', '4-bedroom apartment with amazing views'),
(1, 3, 130.00, 'Beachfront', '3-bedroom house with a deck overlooking the ocean');



-- Inserting test data into agence_reservation (already included)
INSERT INTO agence_reservation (arrival, departure, id_user, id_habitation)
VALUES
('2025-02-10', '2025-02-15', 2,1),  -- Jane Smith reserves
('2025-03-01', '2025-03-05', 1,1),  -- John Doe reserves
('2025-04-20', '2025-04-25', 3,1);  -- Alice Brown reserves

-- Insérer des images pour chaque habitation (au moins 5 images par habitation)
INSERT INTO agence_habitation_images (id_habitation, image_path)
VALUES
(1, 'house1_image1.jpg'), (1, 'house1_image2.jpg'), (1, 'house1_image3.jpg'), (1, 'house1_image4.jpg'), (1, 'house1_image5.jpg'),
(2, 'studio1_image1.jpg'), (2, 'studio1_image2.jpg'), (2, 'studio1_image3.jpg'), (2, 'studio1_image4.jpg'), (2, 'studio1_image5.jpg'),
(3, 'apartment1_image1.jpg'), (3, 'apartment1_image2.jpg'), (3, 'apartment1_image3.jpg'), (3, 'apartment1_image4.jpg'), (3, 'apartment1_image5.jpg'),
(4, 'house2_image1.jpg'), (4, 'house2_image2.jpg'), (4, 'house2_image3.jpg'), (4, 'house2_image4.jpg'), (4, 'house2_image5.jpg'),
(5, 'cottage1_image1.jpg'), (5, 'cottage1_image2.jpg'), (5, 'cottage1_image3.jpg'), (5, 'cottage1_image4.jpg'), (5, 'cottage1_image5.jpg'),
(6, 'house3_image1.jpg'), (6, 'house3_image2.jpg'), (6, 'house3_image3.jpg'), (6, 'house3_image4.jpg'), (6, 'house3_image5.jpg'),
(7, 'villa1_image1.jpg'), (7, 'villa1_image2.jpg'), (7, 'villa1_image3.jpg'), (7, 'villa1_image4.jpg'), (7, 'villa1_image5.jpg'),
(8, 'studio2_image1.jpg'), (8, 'studio2_image2.jpg'), (8, 'studio2_image3.jpg'), (8, 'studio2_image4.jpg'), (8, 'studio2_image5.jpg'),
(9, 'apartment2_image1.jpg'), (9, 'apartment2_image2.jpg'), (9, 'apartment2_image3.jpg'), (9, 'apartment2_image4.jpg'), (9, 'apartment2_image5.jpg'),
(10, 'cottage2_image1.jpg'), (10, 'cottage2_image2.jpg'), (10, 'cottage2_image3.jpg'), (10, 'cottage2_image4.jpg'), (10, 'cottage2_image5.jpg'),
(11, 'house4_image1.jpg'), (11, 'house4_image2.jpg'), (11, 'house4_image3.jpg'), (11, 'house4_image4.jpg'), (11, 'house4_image5.jpg'),
(12, 'apartment3_image1.jpg'), (12, 'apartment3_image2.jpg'), (12, 'apartment3_image3.jpg'), (12, 'apartment3_image4.jpg'), (12, 'apartment3_image5.jpg'),
(13, 'villa2_image1.jpg'), (13, 'villa2_image2.jpg'), (13, 'villa2_image3.jpg'), (13, 'villa2_image4.jpg'), (13, 'villa2_image5.jpg'),
(14, 'cottage3_image1.jpg'), (14, 'cottage3_image2.jpg'), (14, 'cottage3_image3.jpg'), (14, 'cottage3_image4.jpg'), (14, 'cottage3_image5.jpg'),
(15, 'apartment4_image1.jpg'), (15, 'apartment4_image2.jpg'), (15, 'apartment4_image3.jpg'), (15, 'apartment4_image4.jpg'), (15, 'apartment4_image5.jpg'),
(16, 'house5_image1.jpg'), (16, 'house5_image2.jpg'), (16, 'house5_image3.jpg'), (16, 'house5_image4.jpg'), (16, 'house5_image5.jpg'),
(17, 'apartment5_image1.jpg'), (17, 'apartment5_image2.jpg'), (17, 'apartment5_image3.jpg'), (17, 'apartment5_image4.jpg'), (17, 'apartment5_image5.jpg'),
(18, 'villa3_image1.jpg'), (18, 'villa3_image2.jpg'), (18, 'villa3_image3.jpg'), (18, 'villa3_image4.jpg'), (18, 'villa3_image5.jpg'),
(19, 'cottage4_image1.jpg'), (19, 'cottage4_image2.jpg'), (19, 'cottage4_image3.jpg'), (19, 'cottage4_image4.jpg'), (19, 'cottage4_image5.jpg');


