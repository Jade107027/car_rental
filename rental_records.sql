CREATE DATABASE assignment2;
use assignment2;


CREATE TABLE rental_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    car_model VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    quantity INT NOT NULL,
    total_cost DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'unconfirmed'
);