-- Isolated database for PHPUnit (RefreshDatabase must never touch demo data).
CREATE DATABASE IF NOT EXISTS payments_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON payments_test.* TO 'payments'@'%';
FLUSH PRIVILEGES;
