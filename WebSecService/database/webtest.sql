-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 12:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webtest`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `charge_customer_credit` (IN `employee_id` INT, IN `customer_id` INT, IN `amount` DECIMAL(12,2))   BEGIN
    DECLARE employee_role INT;
    
    -- Check if the user is an employee
    SELECT role_id INTO employee_role
    FROM model_has_roles
    WHERE model_id = employee_id
    AND model_type = 'App\\Models\\User'
    AND role_id = (SELECT id FROM roles WHERE name = 'Employee')
    LIMIT 1;
    
    IF employee_role IS NOT NULL AND amount > 0 THEN
        -- Update customer credit
        UPDATE users
        SET credit = credit + amount
        WHERE id = customer_id;
        
        -- Log the transaction
        INSERT INTO credit_transactions (employee_id, customer_id, amount, transaction_date)
        VALUES (employee_id, customer_id, amount, NOW());
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid employee or amount';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `reset_customer_credit` (IN `employee_id` INT, IN `customer_id` INT)   BEGIN
    DECLARE employee_role INT;
    DECLARE current_credit DECIMAL(12,2);
    
    -- Check if the user is an employee
    SELECT role_id INTO employee_role
    FROM model_has_roles
    WHERE model_id = employee_id
    AND model_type = 'App\\Models\\User'
    AND role_id = (SELECT id FROM roles WHERE name = 'Employee')
    LIMIT 1;
    
    -- Get current credit
    SELECT credit INTO current_credit
    FROM users
    WHERE id = customer_id;
    
    IF employee_role IS NOT NULL THEN
        -- Update customer credit to 0
        UPDATE users
        SET credit = 0
        WHERE id = customer_id;
        
        -- Log the transaction (negative amount for reset)
        INSERT INTO credit_transactions (employee_id, customer_id, amount, transaction_date)
        VALUES (employee_id, customer_id, -current_credit, NOW());
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid employee';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `return_product` (IN `p_user_id` INT, IN `p_product_id` INT)   BEGIN
    DECLARE purchase_exists INT;
    
    -- Check if the purchase exists
    SELECT COUNT(*) INTO purchase_exists
    FROM purchases
    WHERE user_id = p_user_id AND product_id = p_product_id;
    
    IF purchase_exists > 0 THEN
        -- Delete the purchase (this will trigger the after_purchase_delete trigger)
        DELETE FROM purchases
        WHERE user_id = p_user_id AND product_id = p_product_id;
        
        SELECT 'Product returned successfully' as message;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Purchase not found';
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('spatie.permission.cache', 'a:3:{s:5:\"alias\";a:5:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:12:\"display_name\";s:1:\"d\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:8:{i:0;a:5:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"add_products\";s:1:\"c\";s:12:\"Add Products\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:1;a:5:{s:1:\"a\";i:2;s:1:\"b\";s:13:\"edit_products\";s:1:\"c\";s:13:\"Edit Products\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:5:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"delete_products\";s:1:\"c\";s:15:\"Delete Products\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:5:{s:1:\"a\";i:4;s:1:\"b\";s:10:\"show_users\";s:1:\"c\";s:10:\"Show Users\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:5:{s:1:\"a\";i:5;s:1:\"b\";s:10:\"edit_users\";s:1:\"c\";s:10:\"Edit Users\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:5:{s:1:\"a\";i:7;s:1:\"b\";s:12:\"delete_users\";s:1:\"c\";s:12:\"Delete Users\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:5:{s:1:\"a\";i:8;s:1:\"b\";s:11:\"admin_users\";s:1:\"c\";s:11:\"Admin Users\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:5:{s:1:\"a\";i:9;s:1:\"b\";s:8:\"buy_item\";s:1:\"c\";s:8:\"Buy Item\";s:1:\"d\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"Admin\";s:1:\"d\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:8:\"Employee\";s:1:\"d\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:8:\"Customer\";s:1:\"d\";s:3:\"web\";}}}', 1745770901);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credit_transactions`
--

CREATE TABLE `credit_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `credit_transactions`
--

INSERT INTO `credit_transactions` (`id`, `employee_id`, `customer_id`, `amount`, `transaction_date`) VALUES
(15, 2, 22, 10000.00, '2025-04-12 21:24:11'),
(20, 2, 55, 150000.00, '2025-04-13 01:01:47'),
(21, 2, 55, -150000.00, '2025-04-13 23:52:28'),
(22, 2, 55, 0.00, '2025-04-13 23:53:00'),
(23, 2, 55, 180000.00, '2025-04-13 23:53:10'),
(24, 2, 55, -180000.00, '2025-04-14 00:12:38'),
(25, 2, 55, 180000.00, '2025-04-14 00:12:48'),
(26, 2, 59, 0.00, '2025-04-20 16:53:03'),
(27, 2, 59, 110000.00, '2025-04-20 16:53:08'),
(28, 2, 59, -110000.00, '2025-04-20 16:53:11'),
(29, 2, 59, 110000.00, '2025-04-20 16:53:16'),
(30, 2, 59, -110000.00, '2025-04-25 04:58:43'),
(31, 2, 59, 110000.00, '2025-04-25 04:58:51'),
(32, 2, 59, -110000.00, '2025-04-26 18:16:32'),
(33, 2, 59, 110000.00, '2025-04-26 18:16:40');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `insufficient_credit_errors`
-- (See below for the actual view)
--
CREATE TABLE `insufficient_credit_errors` (
`user_id` bigint(20) unsigned
,`user_name` varchar(255)
,`user_email` varchar(255)
,`product_id` bigint(20) unsigned
,`product_name` varchar(256)
,`product_price` int(10) unsigned
,`current_credit` decimal(12,2)
,`insufficient_amount` decimal(13,2)
,`error_time` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_03_10_185119_create_permission_tables', 2),
(5, '2025_03_18_add_quantity_to_products', 3),
(6, '2025_04_12_180515_update_product_photo_paths', 4);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 22),
(3, 'App\\Models\\User', 30),
(3, 'App\\Models\\User', 36),
(3, 'App\\Models\\User', 55),
(3, 'App\\Models\\User', 59);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(128) DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'add_products', 'Add Products', 'web', NULL, '2025-04-12 17:21:58'),
(2, 'edit_products', 'Edit Products', 'web', NULL, '2025-04-12 17:21:58'),
(3, 'delete_products', 'Delete Products', 'web', NULL, '2025-04-12 17:21:58'),
(4, 'show_users', 'Show Users', 'web', NULL, '2025-04-12 17:21:58'),
(5, 'edit_users', 'Edit Users', 'web', NULL, '2025-04-12 17:21:58'),
(7, 'delete_users', 'Delete Users', 'web', NULL, '2025-04-12 17:21:58'),
(8, 'admin_users', 'Admin Users', 'web', NULL, '2025-04-12 17:21:58'),
(9, 'buy_item', 'Buy Item', 'web', NULL, '2025-04-12 17:21:58');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(64) NOT NULL,
  `name` varchar(256) NOT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `model` varchar(128) NOT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(128) DEFAULT NULL,
  `in_stock` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `code`, `name`, `price`, `model`, `description`, `photo`, `in_stock`, `quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'TV01', 'LG TV 50 Insh', 28000, 'LG8768787', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_lgtv50.jpg', NULL, 5, NULL, '2025-04-26 15:19:28', NULL),
(2, 'RF01', 'Toshipa Refrigerator 14\"', 22000, 'TS76634', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_tsrf50.jpg', NULL, 8, NULL, '2025-04-12 17:24:30', NULL),
(3, 'RF02', 'Toshipa Refrigerator 18\"', 28000, 'TS76634', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_rf2.jpg', NULL, 8, NULL, '2025-04-12 17:24:39', NULL),
(4, 'RF03', 'Toshipa Refrigerator 19\"', 32000, 'TS76634', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_rf3.jpg', NULL, 8, NULL, '2025-04-12 17:24:57', NULL),
(5, 'TV02', 'LG TV 55\"', 23000, 'LG8768787', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_tv2.jpg', NULL, 9, NULL, '2025-04-12 17:25:08', NULL),
(6, 'RF04', 'LG Refrigerator 14\"', 22000, 'TS76634', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_rf4.jpg', '0', 7, NULL, '2025-04-26 19:55:20', NULL),
(7, 'TV03', 'LG TV 60\"', 44000, 'LG8768787', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_tv3.jpg', 'Yes', 8, NULL, '2025-04-12 17:25:29', NULL),
(8, 'RF05', 'Toshipa Refrigerator 12\"', 10700, 'TS76634', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_rf5.jpg', '0', 8, NULL, '2025-04-26 19:56:19', NULL),
(9, 'TV04', 'LG TV 99\"', 108000, 'LG8768787', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_tv4.jpg', NULL, 12, NULL, '2025-04-26 19:56:32', NULL),
(10, 'RF05', 'LG Refrigerator 19\"', 50000, 'TS76634', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'products/1744481138_rf6.jpg', '0', 0, '2025-02-25 03:18:04', '2025-04-26 19:58:40', NULL),
(27, 'VC07', 'Vacuum Cleaner', 50000, 'Toshiba', 'Vacuum Cleaner by Toshiba. Switch the dirty floor to mirror floor', 'products/1744482308_1_g2e2-6e.jpg', '0', 20, '2025-04-12 16:25:08', '2025-04-26 19:57:01', NULL),
(31, 'MR0011', 'Samsung Microwave', 55000, 'Samsung', 'Samsung Microwave is good for foods', 'products/1744481138_78552ffc33b4ac967cbf82f3029cf646cafb21c28da18f07bccfb6244d1ce244.jpg', '0', 0, '2025-04-12 22:05:28', '2025-04-26 19:40:20', NULL),
(33, 'GS0066', 'Fresh Gas stove', 30000, 'Fresh', 'Fresh Gas stove is good for food cocking', 'products/1744481138_IMG_0955.jpg', '0', 9, '2025-04-12 23:05:30', '2025-04-26 19:57:40', NULL),
(34, 'FR99011', 'Fresh Air Condition', 44000, 'Fresh', 'Fresh Air Condition good for summer and hot weather', 'products/1744481138_fresh_split_air_conditioner_turbo_1.5_hp_cool_and_heat_inverter_white_sifw13hip-sifw13ho-x2.png', '0', 11, '2025-04-26 19:47:21', '2025-04-26 19:57:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `purchases`
--
DELIMITER $$
CREATE TRIGGER `after_purchase_delete` AFTER DELETE ON `purchases` FOR EACH ROW BEGIN
    DECLARE product_price DECIMAL(12,2);
    
    -- Get product price
    SELECT CAST(price AS DECIMAL(12,2)) INTO product_price
    FROM products
    WHERE id = OLD.product_id;
    
    -- Update user credit (refund)
    UPDATE users
    SET credit = credit + product_price
    WHERE id = OLD.user_id;
    
    -- Update product quantity
    UPDATE products
    SET quantity = quantity + 1
    WHERE id = OLD.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_purchase_insert` AFTER INSERT ON `purchases` FOR EACH ROW BEGIN
    DECLARE product_price DECIMAL(12,2);
    DECLARE user_credit DECIMAL(12,2);
    
    -- Get product price
    SELECT CAST(price AS DECIMAL(12,2)) INTO product_price
    FROM products
    WHERE id = NEW.product_id;
    
    -- Get user credit
    SELECT credit INTO user_credit
    FROM users
    WHERE id = NEW.user_id;
    
    -- Check if user has enough credit
    IF user_credit >= product_price THEN
        -- Update user credit
        UPDATE users
        SET credit = credit - product_price
        WHERE id = NEW.user_id;
        
        -- Update product quantity
        UPDATE products
        SET quantity = quantity - 1
        WHERE id = NEW.product_id;
    ELSE
        -- Rollback the purchase
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Insufficient credit';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'web', NULL, '2025-04-12 17:21:58'),
(2, 'Employee', 'web', NULL, '2025-04-12 17:21:58'),
(3, 'Customer', 'web', NULL, '2025-04-12 17:21:58');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(4, 1),
(4, 2),
(5, 1),
(5, 2),
(7, 1),
(8, 1),
(9, 3);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('yIHZDAZyHbVdzBF8tk3x6SjeSeXEpefZd1iA9qzB', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiR0ZBMjNKVGJTc0YzZ3FpMGFHQ3QyclNQQ1l3MW5mMjQ2VFlMSklXZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9kdWN0cyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NToic3RhdGUiO3M6NDA6InVlVGJaR3lrajI3RHFaanQ1TU05ZzM5NHR0TVZkOTBIMTlaWXU4OG0iO3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc0NTY4NDQ5OTt9czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3Byb2R1Y3RzIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9', 1745708321);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `credit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_by` text DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `provider` varchar(20) DEFAULT NULL,
  `provider_id` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `credit`, `created_by`, `google_id`, `provider`, `provider_id`, `avatar`) VALUES
(1, 'Hazem', 'hazem230104892@sut.edu.eg', NULL, '$2y$12$CsOC62YaWhbf.J5LizSL..MaT5VAHvx0saIdh1jEh7fMx5jK9/gEW', NULL, '2025-04-11 10:45:29', '2025-04-11 10:45:29', 0.00, NULL, NULL, NULL, NULL, NULL),
(2, 'Andrew', 'universe25349@gmail.com', NULL, '$2y$12$FX/APZcIQSKtTzYMqFt0KOtwOnQTSB0uyHWzzixRKEw6p216Y2hcS', NULL, '2025-04-11 10:47:09', '2025-04-11 10:47:09', 0.00, NULL, NULL, NULL, NULL, NULL),
(22, 'Amjad', 'Allinonev14-250@outlook.com', NULL, '$2y$12$.AX9JY1ClaxN.m5FEppADepzyse9tZrOKH4x/uJOYpfO8HB16qyWy', NULL, '2025-04-11 19:31:07', '2025-04-11 19:31:07', 200000.00, NULL, NULL, NULL, NULL, NULL),
(55, 'Yousef', 'hazemsalam33@gmail.com', NULL, '$2y$12$UQodP1O0GDhthTNGu6SGbOMJnmJ/tzxauLzjuINQP1e6xQlIOkSw6', NULL, '2025-04-12 22:42:50', '2025-04-12 22:42:50', 180000.00, NULL, NULL, NULL, NULL, NULL),
(59, 'Mohamed', 'lima.sam2025@outlook.com', NULL, '$2y$12$HrkEUQ5xBHzIRDfF7vhI2.ZeOICg7UPVLnwmtugDlcKxYKcTS2x66', NULL, '2025-04-20 14:52:19', '2025-04-20 14:52:19', 110000.00, NULL, NULL, NULL, NULL, NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO model_has_roles (role_id, model_type, model_id)
    SELECT r.id, 'App\Models\User', NEW.id
    FROM roles r
    WHERE r.name = 'Customer'
    AND NOT EXISTS (
        SELECT 1 FROM model_has_roles 
        WHERE model_id = NEW.id 
        AND model_type = 'App\Models\User'
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_credit_update` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.credit < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Credit cannot be negative';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure for view `insufficient_credit_errors`
--
DROP TABLE IF EXISTS `insufficient_credit_errors`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `insufficient_credit_errors`  AS SELECT `u`.`id` AS `user_id`, `u`.`name` AS `user_name`, `u`.`email` AS `user_email`, `p`.`id` AS `product_id`, `p`.`name` AS `product_name`, `p`.`price` AS `product_price`, `u`.`credit` AS `current_credit`, `p`.`price`- `u`.`credit` AS `insufficient_amount`, current_timestamp() AS `error_time` FROM (`users` `u` join `products` `p`) WHERE `u`.`credit` < `p`.`price` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `credit_transactions`
--
ALTER TABLE `credit_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchases_user_id_foreign` (`user_id`),
  ADD KEY `purchases_product_id_foreign` (`product_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `credit_transactions`
--
ALTER TABLE `credit_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `credit_transactions`
--
ALTER TABLE `credit_transactions`
  ADD CONSTRAINT `credit_transactions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `credit_transactions_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
