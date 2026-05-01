DROP DATABASE IF EXISTS bistrofdi;

CREATE DATABASE bistrofdi
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

CREATE USER IF NOT EXISTS 'bistro_user'@'localhost'
    IDENTIFIED BY 'bistro_pass';

GRANT ALL PRIVILEGES ON bistrofdi.* TO 'bistro_user'@'localhost';

FLUSH PRIVILEGES;