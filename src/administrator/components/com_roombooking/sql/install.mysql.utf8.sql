CREATE TABLE IF NOT EXISTS `#__roombooking_rooms` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`alias` varchar(400) NOT NULL DEFAULT '',
	`description` text,
	`capacity` int(10) NOT NULL DEFAULT 0,
	`image` varchar(255) NOT NULL DEFAULT '',
	`created` datetime NOT NULL,
	`language` varchar(7) NOT NULL,
	`created_by` int(10) unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) NOT NULL,
	`metakey` text NOT NULL,
	`own_prefix` tinyint(1) NOT NULL DEFAULT 0,
	`metakey_prefix` varchar(255) NOT NULL,
	`modified` datetime NOT NULL,
	`modified_by` int(10) unsigned NOT NULL DEFAULT 0,
	`version` int(10) unsigned NOT NULL DEFAULT 1,
	`ordering` int(10) NOT NULL DEFAULT 0,
	`state` tinyint(1) NOT NULL DEFAULT 0,
	INDEX `idx_state` (`state`),
	INDEX `idx_language` (`language`),
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__roombooking_bookings` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`ordering` int(10) NOT NULL,
	`created` datetime NOT NULL,
	`room_id` int(10) NOT NULL,
	`state` INT(10) NOT NULL DEFAULT 1,
	`name` varchar(255) NOT NULL,
	`booking_date` datetime NOT NULL,
	`confirmed` tinyint(1) NOT NULL DEFAULT '0',
	`payment_status` enum('unpaid', 'paid', 'cancelled') NOT NULL DEFAULT 'unpaid',
	`recurring` tinyint(1) NOT NULL DEFAULT '0',
	`recurrence_type` enum('weekly', 'biweekly', 'monthly', 'none') DEFAULT 'none',
	`recurrence_end_date` datetime DEFAULT NULL,
	`customer_name` varchar(255) NOT NULL,
	`customer_address` text NOT NULL,
	`customer_phone` varchar(20) NOT NULL,
	`customer_email` varchar(255) NOT NULL,
	INDEX `idx_state` (`state`),
	PRIMARY KEY (`id`),
	KEY `idx_room_id` (`room_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;