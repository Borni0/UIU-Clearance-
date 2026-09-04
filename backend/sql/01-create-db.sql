CREATE DATABASE uiu_clearance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'uiu_app'@'localhost' IDENTIFIED BY 'devpass';
GRANT ALL PRIVILEGES ON uiu_clearance.* TO 'uiu_app'@'localhost';
FLUSH PRIVILEGES;
