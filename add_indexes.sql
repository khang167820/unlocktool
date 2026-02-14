-- =============================================
-- Thêm INDEX cho database u620980434_yenz
-- Chạy trên phpMyAdmin hoặc MySQL CLI
-- =============================================

-- 1. Index cho orders(account_id) — tăng tốc JOIN orders ↔ accounts
ALTER TABLE `orders` ADD INDEX `idx_account_id` (`account_id`);

-- 2. Index cho orders(status, account_id) — tăng tốc tìm đơn paid chưa có TK
ALTER TABLE `orders` ADD INDEX `idx_status_account` (`status`, `account_id`);

-- 3. Index cho accounts(is_available) — tăng tốc tìm TK trống
ALTER TABLE `accounts` ADD INDEX `idx_is_available` (`is_available`);
