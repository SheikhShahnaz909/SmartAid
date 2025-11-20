-- create_schema.sql
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  location VARCHAR(255),
  role ENUM('donor','reporter') NOT NULL,
  phone_verified TINYINT(1) DEFAULT 0,
  aadhaar_last4 VARCHAR(4) DEFAULT NULL,
  aadhaar_hash VARCHAR(255) DEFAULT NULL,
  aadhaar_uploaded_file VARCHAR(255) DEFAULT NULL,
  aadhaar_kyc_ref VARCHAR(255) DEFAULT NULL,
  aadhaar_verified TINYINT(1) DEFAULT 0,
  consent_aadhaar TINYINT(1) DEFAULT 0,
  verification_status ENUM('none','pending','verified','rejected') DEFAULT 'none',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS phone_otps (
  otp_id INT AUTO_INCREMENT PRIMARY KEY,
  phone VARCHAR(20) NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  attempts INT DEFAULT 0,
  used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS donations (
  donation_id INT AUTO_INCREMENT PRIMARY KEY,
  donor_id INT NOT NULL,
  item_name VARCHAR(255) NOT NULL,
  quantity INT DEFAULT 1,
  expiry_date DATE NULL,
  pickup_location VARCHAR(255) NOT NULL,
  notes TEXT NULL,
  status ENUM('Pending','Accepted','PickedUp','Delivered','Cancelled') DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (donor_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reports (
  report_id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  report_type VARCHAR(255),
  description TEXT,
  location VARCHAR(255),
  status ENUM('New','InProgress','Resolved','Discarded') DEFAULT 'New',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
