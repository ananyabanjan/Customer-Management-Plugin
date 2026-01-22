-- Custom Customer Management Plugin SQL Dump
-- Table: wp_custom_customers
-- Run this after plugin activation or manually in your database.

-- Create the table
CREATE TABLE `wp_custom_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL, --Stored as VARCHAR to preserve leading zeros
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `cr_number` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cr_number` (`cr_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert dummy data for testing (15 records to test pagination)
INSERT INTO `wp_custom_customers` (`name`, `email`, `phone`, `dob`, `gender`, `cr_number`, `address`, `city`, `country`, `status`) VALUES
('John Doe', 'john@example.com', '1234567890', '1990-01-01', 'Male', 'CR123456', '123 Main St', 'New York', 'USA', 'active'),
('Jane Smith', 'jane@example.com', '0987654321', '1985-05-15', 'Female', 'CR654321', '456 Elm St', 'Los Angeles', 'USA', 'active'),
('Alice Johnson', 'alice@example.com', '1122334455', '1992-03-20', 'Female', 'CR789012', '789 Oak Ave', 'Chicago', 'USA', 'active'),
('Bob Brown', 'bob@example.com', '5566778899', '1980-07-10', 'Male', 'CR345678', '321 Pine Rd', 'Houston', 'USA', 'inactive'),
('Charlie Wilson', 'charlie@example.com', '6677889900', '1995-09-25', 'Male', 'CR901234', '654 Maple Ln', 'Phoenix', 'USA', 'active'),
('Diana Davis', 'diana@example.com', '7788990011', '1988-11-30', 'Female', 'CR567890', '987 Cedar St', 'Philadelphia', 'USA', 'active'),
('Eve Miller', 'eve@example.com', '8899001122', '1993-02-14', 'Female', 'CR123789', '147 Birch Blvd', 'San Antonio', 'USA', 'active'),
('Frank Garcia', 'frank@example.com', '9900112233', '1975-04-05', 'Male', 'CR456789', '258 Spruce Way', 'San Diego', 'USA', 'inactive'),
('Grace Lee', 'grace@example.com', '0011223344', '1997-06-18', 'Female', 'CR789123', '369 Willow Dr', 'Dallas', 'USA', 'active'),
('Henry Taylor', 'henry@example.com', '2233445566', '1982-08-22', 'Male', 'CR012345', '741 Poplar St', 'San Jose', 'USA', 'active'),
('Ivy Anderson', 'ivy@example.com', '3344556677', '1991-10-12', 'Female', 'CR345012', '852 Ash Ave', 'Austin', 'USA', 'active'),
('Jack Thomas', 'jack@example.com', '4455667788', '1978-12-03', 'Male', 'CR678901', '963 Fir Ln', 'Jacksonville', 'USA', 'inactive'),
('Kelly Jackson', 'kelly@example.com', '5566778899', '1994-01-28', 'Female', 'CR901567', '147 Elm Rd', 'Fort Worth', 'USA', 'active'),
('Liam White', 'liam@example.com', '6677889900', '1986-03-15', 'Male', 'CR234678', '258 Oak Blvd', 'Columbus', 'USA', 'active'),
('Mia Harris', 'mia@example.com', '7788990011', '1999-05-07', 'Female', 'CR567123', '369 Pine Dr', 'Charlotte', 'USA', 'active');
