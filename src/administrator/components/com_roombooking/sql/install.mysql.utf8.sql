CREATE TABLE IF NOT EXISTS `#__roombooking_rooms` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`alias` varchar(400) NOT NULL DEFAULT '',
	`short_description` text NOT NULL,
	`description` text,
	`capacity` int(10) NOT NULL DEFAULT 0,
	`image` varchar(255) NOT NULL DEFAULT '',
	`size` int(10) NOT NULL DEFAULT 0,
	`price` decimal(19, 4) NOT NULL DEFAULT 0.0000,
	`created` datetime NOT NULL,
	`language` varchar(7) NOT NULL,
	`created_by` int(10) unsigned NOT NULL DEFAULT 0,
	`created_by_alias` varchar(255) DEFAULT '',
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
	`ordering` int(10) NOT NULL DEFAULT 0,
	`created` datetime NOT NULL,
	`room_id` int(10) NOT NULL,
	`state` tinyint(1) NOT NULL DEFAULT 0,
	`name` varchar(255) NOT NULL,
	`confirmed` tinyint(1) NOT NULL DEFAULT '0',
	`payment_status` enum('unpaid', 'paid', 'cancelled') NOT NULL DEFAULT 'unpaid',
	`total_amount` decimal(19, 4) NOT NULL DEFAULT 0.0000,
	`recurring` tinyint(1) NOT NULL DEFAULT '0',
	`recurrence_type` enum('weekly', 'biweekly', 'monthly', 'none') DEFAULT 'none',
	`recurrence_end_date` datetime DEFAULT NULL,
	`customer_name` varchar(255) NOT NULL,
	`customer_address` text NOT NULL,
	`customer_phone` varchar(20) NOT NULL,
	`customer_email` varchar(255) NOT NULL,
	`privacy_accepted` tinyint(1) NOT NULL DEFAULT '0',
	INDEX `idx_state` (`state`),
	INDEX `idx_room_id` (`room_id`),
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__roombooking_booking_dates` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`booking_id` int(10) NOT NULL,
	`booking_date` datetime NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `idx_booking_id` (`booking_id`),
	CONSTRAINT `fk_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `#__roombooking_bookings` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__roombooking_mail_templates` (
	`id` int(10) NOT NULL,
	`name` varchar(255) NOT NULL,
	`template_type` enum('admin', 'customer') NOT NULL,
	`subject` varchar(255) NOT NULL DEFAULT '',
	`body` text NOT NULL,
	`from_email` varchar(255) NOT NULL DEFAULT '',
	`to_email` varchar(255) DEFAULT NULL,
	`cc` varchar(255) NOT NULL DEFAULT '',
	`bcc` varchar(255) NOT NULL DEFAULT '',
	`language` char(7) NOT NULL DEFAULT '*',
	`state` tinyint(1) NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__roombooking_tokens` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`booking_id` int(11) NOT NULL,
	`token` varchar(64) NOT NULL,
	`type` enum('email_confirmation', 'booking_cancellation') NOT NULL,
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`expires_at` timestamp NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `token` (`token`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

-- Insert default templates
INSERT INTO
	`#__roombooking_mail_templates` (
		`id`,
		`template_type`,
		`name`,
		`subject`,
		`body`,
		`from_email`,
		`to_email`,
		`cc`,
		`bcc`,
		`language`
	)
VALUES
	(
		1,
		'admin',
		'Admin Template',
		'Neue Raumbuchung',
		'Eine neue Raumbuchung wurde vorgenommen. Hier sind die Details:\n\n{{booking_details}}',
		'noreply@example.com',
		'admin@example.com',
		'',
		'',
		'*'
	),
	(
		2,
		'customer',
		'Customer Template',
		'Ihre Raumbuchung: Bestätigung',
		'Sehr geehrter {{customer_name}},\n\nVielen Dank für Ihre Buchung. Hier sind die Details Ihrer Reservierung:\n\n{{booking_details}}\n\nBei Fragen stehen wir Ihnen gerne zur Verfügung.\n\nMit freundlichen Grßen,\nIhr Raumbuchungsteam',
		'info@example.com',
		NULL,
		'',
		'',
		'*'
	) ON DUPLICATE KEY
UPDATE
	`template_type` =
VALUES
	(`template_type`),
	`subject` =
VALUES
	(`subject`),
	`body` =
VALUES
	(`name`),
	`name` =
VALUES
	(`body`),
	`from_email` =
VALUES
	(`from_email`),
	`to_email` =
VALUES
	(`to_email`),
	`cc` =
VALUES
	(`cc`),
	`bcc` =
VALUES
	(`bcc`),
	`language` =
VALUES
	(`language`);

INSERT INTO
	`#__roombooking_rooms` (
		`name`,
		`alias`,
		`short_description`,
		`description`,
		`capacity`,
		`image`,
		`size`,
		`price`,
		`created`,
		`language`,
		`state`,
		`created_by`,
		`created_by_alias`,
		`modified_by`,
		`modified`,
		`version`,
		`ordering`,
		`metakey`,
		`metakey_prefix`
	)
VALUES
	(
		'Konferenzraum A',
		'konferenzraum-a',
		'<p>Großer Konferenzraum mit moderner Ausstattung</p>',
		'<p>Unser Konferenzraum A bietet den perfekten Rahmen für Ihre geschäftlichen Veranstaltungen. Mit seiner großzügigen Fläche und modernster technischer Ausstattung eignet er sich hervorragend für Konferenzen, Seminare und Präsentationen.</p><p>Der Raum verfügt über eine flexible Bestuhlung, hochauflösende Projektoren, ein leistungsstarkes Soundsystem und schnelles WLAN. Die großen Fenster sorgen für natürliches Licht und eine angenehme Atmosphäre, während die schalldichte Konstruktion für ungestörte Gespräche sorgt.</p>',
		20,
		'images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg#joomlaImage://local-images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg?width=6000&height=4000',
		50,
		95.0000,
		NOW(),
		'*',
		1,
		592,
		'',
		592,
		NOW(),
		1,
		1,
		'konferenzraum, meeting, präsentation',
		'raum:'
	),
	(
		'Besprechungsraum B',
		'besprechungsraum-b',
		'<p>Gemütlicher Besprechungsraum für kleinere Gruppen</p>',
		'<p>Unser Besprechungsraum B ist die ideale Wahl für kleinere Meetings und Teamzusammenkünfte. Mit seiner gemütlichen Atmosphäre und der überschaubaren Größe fördert er konzentriertes Arbeiten und produktiven Austausch.</p><p>Ausgestattet mit einem großen Konferenztisch, bequemen Stühlen und einem Smartboard bietet der Raum alles, was Sie für erfolgreiche Besprechungen benötigen. Die warme Beleuchtung und die dezente Farbgestaltung schaffen eine einladende Umgebung, die Kreativität und offene Kommunikation fördert.</p>',
		8,
		'images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg#joomlaImage://local-images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg?width=6000&height=4000',
		25,
		60.0000,
		NOW(),
		'*',
		1,
		592,
		'',
		592,
		NOW(),
		1,
		2,
		'besprechungsraum, meeting, team',
		'raum:'
	),
	(
		'Schulungsraum C',
		'schulungsraum-c',
		'<p>Ideal für Schulungen und Workshops</p>',
		'<p>Unser Schulungsraum C ist speziell für Bildungsveranstaltungen konzipiert. Mit seiner optimalen Raumaufteilung und der umfassenden technischen Ausstattung bietet er beste Voraussetzungen für effektives Lernen und interaktive Workshops.</p><p>Der Raum verfügt über flexible Tisch- und Stuhlkombinationen, die sich leicht an verschiedene Unterrichtsformate anpassen lassen. Mehrere Whiteboards, ein leistungsstarker Beamer und ein modernes Audiosystem unterstützen vielfältige Präsentationsmöglichkeiten. Zusätzlich stehen Laptops für praktische Übungen zur Verfügung.</p>',
		15,
		'images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg#joomlaImage://local-images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg?width=6000&height=4000',
		40,
		85.0000,
		NOW(),
		'*',
		1,
		592,
		'',
		592,
		NOW(),
		1,
		3,
		'schulungsraum, workshop, bildung',
		'raum:'
	),
	(
		'Präsentationsraum D',
		'praesentationsraum-d',
		'<p>Ausgestattet mit modernster Präsentationstechnik</p>',
		'<p>Unser Präsentationsraum D ist ein Hightech-Raum, der für beeindruckende Vorträge und Produktpräsentationen konzipiert wurde. Mit seiner state-of-the-art Ausstattung bietet er optimale Bedingungen, um Ihre Ideen und Projekte professionell zu präsentieren.</p><p>Der Raum verfügt über ein 4K-Projektionssystem, ein hochwertiges Surround-Soundsystem und eine fortschrittliche Beleuchtungssteuerung. Die ergonomischen Sitze sorgen für den Komfort Ihrer Zuhörer, während das integrierte Klimasystem für eine angenehme Raumtemperatur sorgt. Ein leistungsfähiges WLAN und mehrere Anschlussmöglichkeiten für verschiedene Geräte runden die technische Ausstattung ab.</p>',
		30,
		'images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg#joomlaImage://local-images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg?width=6000&height=4000',
		60,
		100.0000,
		NOW(),
		'*',
		1,
		592,
		'',
		592,
		NOW(),
		1,
		4,
		'präsentationsraum, vortrag, produkt',
		'raum:'
	),
	(
		'Kreativraum E',
		'kreativraum-e',
		'<p>Inspirierender Raum für Brainstorming-Sessions</p>',
		'<p>Unser Kreativraum E ist ein einzigartiger Ort, der darauf ausgelegt ist, Inspiration und innovative Ideen zu fördern. Mit seiner unkonventionellen Einrichtung und flexiblen Raumgestaltung bietet er die perfekte Umgebung für Brainstorming-Sessions, Design Thinking Workshops und kreative Teamarbeit.</p><p>Der Raum ist mit verschiedenen Sitzmöglichkeiten ausgestattet, von bequemen Sofas bis hin zu Hochtischen und Stehpulten. Große Whiteboards und Pinnwände an den Wänden bieten Platz für Ihre Ideen, während bunte Haftnotizen, Marker und andere Kreativmaterialien zur freien Verfügung stehen. Ein mobiler Touchscreen-Monitor ermöglicht die digitale Zusammenarbeit und Präsentation von Ideen.</p>',
		10,
		'images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg#joomlaImage://local-images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg?width=6000&height=4000',
		30,
		75.0000,
		NOW(),
		'*',
		1,
		592,
		'',
		592,
		NOW(),
		1,
		5,
		'kreativraum, brainstorming, ideen',
		'raum:'
	),
	(
		'Meetingraum F',
		'meetingraum-f',
		'<p>Kompakter Raum für effiziente Meetings</p>',
		'<p>Unser Meetingraum F ist die perfekte Wahl für kurze, fokussierte Besprechungen und effiziente Teamzusammenkünfte. Trotz seiner kompakten Größe bietet er alles, was für produktive Meetings benötigt wird.</p><p>Der Raum ist mit einem ovalen Konferenztisch und ergonomischen Stühlen ausgestattet, die eine angenehme Gesprächsatmosphäre schaffen. Ein Wandmonitor ermöglicht die einfache Präsentation von Inhalten, während eine Videokonferenzanlage reibungslose virtuelle Meetings ermöglicht. Die schallgedämmten Wände sorgen für Privatsphäre, und die eingebaute Kaffeemaschine hält Sie und Ihre Gäste während der Besprechungen erfrischt.</p>',
		6,
		'images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg#joomlaImage://local-images/kenny-eliason-7MKYpAA4aMw-unsplash.jpg?width=6000&height=4000',
		20,
		45.0000,
		NOW(),
		'*',
		1,
		592,
		'',
		592,
		NOW(),
		1,
		6,
		'meetingraum, besprechung, team',
		'raum:'
	);