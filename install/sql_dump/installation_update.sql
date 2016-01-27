
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `categories_count` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1' AFTER  `listings_count` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `business_name` TINYINT( 10 ) UNSIGNED NOT NULL DEFAULT  '1' AFTER  `categories_count` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `business_description` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1' AFTER  `business_name` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `logo` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1' AFTER  `business_description` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `phone` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1' AFTER  `logo` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `address` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1' AFTER  `phone` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `keywords_count` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1' AFTER  `address` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `inquiries_count` TINYINT( 10 ) NOT NULL DEFAULT  '1' AFTER  `keywords_count` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `rating_button` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1' AFTER  `inquiries_count` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `inquiry_button` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1' AFTER  `inquiries_count`;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `images_count` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `logo` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `video_link` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `images_count` ;
ALTER TABLE  `<DB_PREFIX>advertise_plans` ADD  `map` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `address`;

ALTER TABLE  `<DB_PREFIX>menus` CHANGE  `menu_placement`  `menu_placement` ENUM(  '',  'left',  'top',  'right',  'bottom',  'hidden' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

INSERT INTO `<DB_PREFIX>email_templates` (`id`, `language_id`, `template_code`, `template_name`, `template_subject`, `template_content`, `is_system_template`) VALUES
(NULL, 'en', 'inquiry_new', 'Email notification to customer about new inquiry', 'We have received a new inquiry for you from our visitors', 'Dear {FIRST NAME} {LAST NAME}!\r\n\r\nThis e-mail is to confirm that we have received new inquiry for you from our visitors.\r\n\r\nYou can now login in to your account to check it:\r\n{BASE URL}index.php?customer=login\r\n\r\nThanks for choosing {WEB SITE}.\r\n-\r\nSincerely,\r\nAdministration', 1),
(NULL, 'es', 'inquiry_new', 'Notificación por correo electrónico a los clientes acerca de nueva investigación', 'Hemos recibido una nueva investigación para usted de nuestro visitante del sitio', 'Estimado {FIRST NAME} {LAST NAME}!\r\n\r\nEste e-mail es para confirmar que hemos recibido su consulta nueva para usted de nuestros visitantes.\r\n\r\nAhora puede iniciar sesión en su cuenta para comprobar que:\r\n{BASE URL}index.php?customer=login\r\n\r\nGracias por elegir {WEB SITE}.\r\n-\r\nAtentamente,\r\nadministración', 1),
(NULL, 'de', 'inquiry_new', 'E-Mail Benachrichtigung an den Kunden über neue Anfrage', 'Wir haben eine neue Anfrage für Sie von unserer Website-Besucher erhalten', 'Hallo {FIRST NAME} {LAST NAME}!\r\n\r\nDiese E-Mail ist zu bestätigen, dass wir neue Anfrage für Sie von unseren Besuchern erhalten.\r\n\r\nSie können jetzt in Ihrem Konto anmelden, um es zu überprüfen:\r\n{BASE URL}index.php?customer=login\r\n\r\nDanke für die Wahl {WEB SITE}.\r\n-\r\nMit freundlichen Grüßen,\r\nVerwaltung', 1),
(NULL, 'en', 'inquiry_reply', 'Email notification to visitor about inquiry reply', 'You have received a reply to your inquiry', 'Dear {FIRST NAME} {LAST NAME}!\r\n\r\nBelow the reply to your inquiry from one our customers:\r\n\r\n{REPLY DETAILS}\r\n\r\nThanks for choosing {WEB SITE}.\r\n-\r\nSincerely,\r\nAdministration', 1),
(NULL, 'es', 'inquiry_reply', 'Notificación por correo electrónico a los visitantes acerca de contestación investigación', 'Usted ha recibido una respuesta a su pregunta', 'Estimado {FIRST NAME} {LAST NAME}!\r\n\r\nA continuación, la respuesta a su pregunta de uno de nuestros clientes:\r\n\r\n{REPLY DETAILS}\r\n\r\nGracias por elegir {WEB SITE}.\r\n-\r\nAtentamente,\r\nadministración', 1),
(NULL, 'de', 'inquiry_reply', 'E-Mail Benachrichtigung an Besucher über Anfrage Antwort', 'Sie haben eine Antwort auf Ihre Anfrage erhalten', 'Hallo {FIRST NAME} {LAST NAME}!\r\n\r\nUnterhalb der Antwort auf Ihre Anfrage von einem unserer Kunden:\r\n\r\n{REPLY DETAILS}\r\n\r\nDanke für die Wahl {WEB SITE}.\r\n-\r\nMit freundlichen Grüßen,\r\nVerwaltung', 1);


ALTER TABLE  `<DB_PREFIX>accounts` CHANGE  `last_login`  `date_lastlogin` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00' ;

ALTER TABLE  `<DB_PREFIX>languages` ADD  `lc_time_name` VARCHAR( 5 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  'en_US' AFTER  `abbreviation` ;

ALTER TABLE  `<DB_PREFIX>currencies` ADD  `decimals` TINYINT( 1 ) NOT NULL DEFAULT  '2' AFTER  `rate`;

ALTER TABLE  `<DB_PREFIX>listings` ADD  `listing_location_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `customer_id` ;
ALTER TABLE  `<DB_PREFIX>listings` ADD  `listing_sub_location_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `listing_location_id` ;
ALTER TABLE  `<DB_PREFIX>listings` DROP  `category_id` ;
ALTER TABLE  `<DB_PREFIX>listings` CHANGE  `image_file`  `image_file` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';
ALTER TABLE  `<DB_PREFIX>listings` CHANGE  `website_url`  `website_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';
ALTER TABLE  `<DB_PREFIX>listings` CHANGE  `business_phone`  `business_phone` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';
ALTER TABLE  `<DB_PREFIX>listings` CHANGE  `business_fax`  `business_fax` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '';
ALTER TABLE  `<DB_PREFIX>listings` ADD  `video_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '' AFTER  `website_url`;
ALTER TABLE  `<DB_PREFIX>listings` ADD  `keywords` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER  `video_url` ;


ALTER TABLE  `<DB_PREFIX>orders` ADD  `cc_holder_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER  `cc_type` ;
ALTER TABLE  `<DB_PREFIX>orders` CHANGE  `cc_holder_name`  `cc_holder_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '' ;

INSERT INTO  `<DB_PREFIX>modules` (`id` ,`name` ,`name_const` ,`description_const` ,`icon_file` ,`module_tables` ,`dependent_modules` ,`settings_page` ,`settings_const` ,`settings_access_by` ,`management_page` ,`management_const` ,`management_access_by` ,`is_installed` ,`is_system` ,`priority_order`) VALUES (NULL ,  'inquiries',  '_INQUIRIES',  '_MD_INQUIRIES',  'inquiries.png',  'inquiries',  '',  'mod_inquiries_settings',  '_INQUIRIES_SETTINGS',  'owner,mainadmin',  'mod_inquiries_management',  '_INQUIRIES_MANAGEMENT',  '',  '1',  '0',  '3' );
INSERT INTO  `<DB_PREFIX>modules` (`id`, `name`, `name_const`, `description_const`, `icon_file`, `module_tables`, `dependent_modules`, `settings_page`, `settings_const`, `settings_access_by`, `management_page`, `management_const`, `management_access_by`, `is_installed`, `is_system`, `priority_order`) VALUES (NULL, 'ratings', '_RATINGS', '_MD_RATINGS', 'ratings.png', 'ratings_items,ratings_users', '', 'mod_ratings_settings', '_RATINGS_SETTINGS', 'owner,mainadmin', '', '', '', '1', '0', '13');

INSERT INTO  `<DB_PREFIX>modules_settings` (`id` ,`module_name` ,`settings_key` ,`settings_value` ,`settings_name` ,`settings_description_const` ,`key_display_type` ,`key_is_required` ,`key_display_source`) VALUES (NULL , 'listings',  'maximum_categories',  '1',  'Multiple Categories',  '_MS_MULTIPLE_CATEGORIES',  'enum',  '1',  '1,2,3,4,5' );
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'inquiries', 'maximum_replies', '1', 'Maximum Number of Replies', '_MS_MAXIMUM_REPLIES', 'enum', '1', '1,2,3,4,5,6,7,8,9,10');
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'inquiries', 'direct_inquiry_allow', 'yes', 'Allow Direct Inquiry', '_MS_ALLOW_DIRECT_INQUIRY', 'yes/no', '1', '');
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'inquiries', 'show_inquiries_block', 'left side', 'Last Inquiries Block', '_MS_SHOW_INQUIRIES_BLOCK_BLOCK', 'enum', '1', 'no,left side,right side');
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'ratings', 'user_type', 'all', 'User Type', '_MS_RATINGS_USER_TYPE', 'enum', '1', 'all,registered');
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'ratings', 'multiple_items_per_day', 'yes', 'Multiple Items per Day', '_MS_MULTIPLE_ITEMS_PER_DAY', 'yes/no', '1', '');
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'inquiries', 'keep_history_days', '730', 'Keep History', '_MS_KEEP_HISTORY_DAYS', 'positive integer', '1', '');
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'listings', 'sub_categories_count', '5', 'Sub-Categories On Home Page', '_MS_SUB_CATEGORIES_COUNT', 'enum', '1', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15');
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'banners', 'slideshow_caption_html', 'yes', 'HTML in Slideshow Caption', '_MS_BANNERS_CAPTION_HTML', 'yes/no', '1', '');
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'listings', 'watermark', 'yes', 'Add Watermark to Images', '_MS_WATERMARK_TO_MAGES', 'yes/no', '1', '');
INSERT INTO  `<DB_PREFIX>modules_settings` (`id`, `module_name`, `settings_key`, `settings_value`, `settings_name`, `settings_description_const`, `key_display_type`, `key_is_required`, `key_display_source`) VALUES (NULL, 'listings', 'watermark_text', '', 'Watermark Text', '_MS_WATERMARK_TEXT', 'string', '0', '');

CREATE TABLE IF NOT EXISTS `<DB_PREFIX>ratings_items` (
  `item` varchar(200) NOT NULL DEFAULT '',
  `totalrate` int(10) NOT NULL DEFAULT '0',
  `nrrates` int(9) NOT NULL DEFAULT '1',
  PRIMARY KEY (`item`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `<DB_PREFIX>ratings_users` (
  `day` int(2) DEFAULT NULL,
  `rater` varchar(15) DEFAULT NULL,
  `item` varchar(200) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE  `<DB_PREFIX>listings_categories` (
  `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `category_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0',
  `listing_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0'
) ENGINE = MYISAM ;


CREATE TABLE IF NOT EXISTS `<DB_PREFIX>listings_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


CREATE TABLE IF NOT EXISTS `<DB_PREFIX>listings_sub_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state_id` (`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


CREATE TABLE IF NOT EXISTS `<DB_PREFIX>inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inquiry_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - standard, 1 - direct',
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `listing_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `location_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sub_location_id` int(10) unsigned NOT NULL DEFAULT '0',
  `availability` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `preferred_contact` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `replies_count` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `inquiry_type` (`inquiry_type`),
  KEY `location_id` (`location_id`),
  KEY `sub_location_id` (`sub_location_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `<DB_PREFIX>inquiries_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inquiry_id` int(10) unsigned NOT NULL DEFAULT '0',
  `customer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `inquiry_id` (`inquiry_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `<DB_PREFIX>inquiries_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inquiry_id` int(10) unsigned NOT NULL DEFAULT '0',
  `customer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `listing_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `inquiry_id` (`inquiry_id`),
  KEY `customer_id` (`customer_id`),
  KEY `listing_id` (`listing_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id` ,`key_value` ,`key_text`) VALUES (NULL , 'en', '_ORDER_BY', 'Order By'), (NULL , 'es', '_ORDER_BY', 'Ordenar por'), (NULL , 'de', '_ORDER_BY', 'Sortieren nach');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_IN', 'in'), (NULL, 'es', '_IN', 'en'), (NULL, 'de', '_IN', 'in');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id` ,`key_value` ,`key_text`) VALUES (NULL, 'en', '_ADVANCED_SEARCH', 'Advanced Search'), (NULL , 'es', '_ADVANCED_SEARCH', 'Búsqueda Avanzada'), (NULL , 'de', '_ADVANCED_SEARCH', 'Erweiterte Suche');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_YESTERDAY', 'Yesterday'), (NULL, 'es', '_YESTERDAY', 'Ayer'), (NULL, 'de', '_YESTERDAY', 'Gestern');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_VIEW', 'View'), (NULL, 'es', '_VIEW', 'Ver'), (NULL, 'de', '_VIEW', 'Sehen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_LAST_7_DAYS', 'Last 7 days'), (NULL, 'es', '_LAST_7_DAYS', 'Últimos 7 días'), (NULL, 'de', '_LAST_7_DAYS', 'Letzte 7 Tage');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_DESCENDING', 'Descending'), (NULL, 'es', '_DESCENDING', 'Descendente'), (NULL, 'de', '_DESCENDING', 'Absteigend');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_ASCENDING', 'Ascending'), (NULL, 'es', '_ASCENDING', 'Ascendente'), (NULL, 'de', '_ASCENDING', 'Aufsteigend');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_LOCATION', 'Location'), (NULL, 'es', '_LOCATION', 'Ubicación'), (NULL, 'de', '_LOCATION', 'Lage');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id` ,`key_value` ,`key_text`) VALUES (NULL, 'en', '_WITH_IMAGES_ONLY', 'With images only'), (NULL , 'es', '_WITH_IMAGES_ONLY', 'Con sólo imágenes'), (NULL , 'de', '_WITH_IMAGES_ONLY', 'Nur mit Bildern');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_MULTIPLE_CATEGORIES', 'Specifies whether to allow Multiple Categories for Listings: 1 - one category, 2 - two categories etc.'), (NULL, 'es', '_MS_MULTIPLE_CATEGORIES', 'Especifica si se permite múltiples categorías de Listados: 1 - una categoría, 2 - dos categorías, etc'), (NULL, 'de', '_MS_MULTIPLE_CATEGORIES', 'Gibt an, ob Multiple Kategorien für Anzeigen: 1 - eine Kategorie, 2 - zwei Kategorien etc.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRIES', 'Inquiries'), (NULL, 'es', '_INQUIRIES', 'Consultas'), (NULL, 'de', '_INQUIRIES', 'Anfragen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRIES_MANAGEMENT', 'Inquiries Management'), (NULL, 'es', '_INQUIRIES_MANAGEMENT', 'Preguntas de Gestión'), (NULL, 'de', '_INQUIRIES_MANAGEMENT', 'Anfragen-Management');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRIES_SETTINGS', 'Inquiries Settings'), (NULL, 'es', '_INQUIRIES_SETTINGS', 'Consultas Configuración'), (NULL, 'de', '_INQUIRIES_SETTINGS', 'Anfragen Einstellungen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'de', '_GENERATE', 'Erzeugen'), (NULL, 'en', '_GENERATE', 'Generate'), (NULL, 'es', '_GENERATE', 'Generar'), (NULL, 'de', '_USE_THIS_PASSWORD', 'Verwenden Sie dieses Kennwort'), (NULL, 'en', '_USE_THIS_PASSWORD', 'Use this password'), (NULL, 'es', '_USE_THIS_PASSWORD', 'Utilice esta contraseña');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MENUS_AND_PAGES', 'Menus and Pages'), (NULL, 'es', '_MENUS_AND_PAGES', 'Los menús y páginas'), (NULL, 'de', '_MENUS_AND_PAGES', 'Menüs und Seiten');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_LOCATIONS', 'Locations'), (NULL, 'es', '_LOCATIONS', 'Ubicaciones'), (NULL, 'de', '_LOCATIONS', 'Standorte');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_SUB_LOCATIONS', 'Sub Locations'), (NULL, 'es', '_SUB_LOCATIONS', 'Ubicaciones Sub'), (NULL, 'de', '_SUB_LOCATIONS', 'Sub Standorte');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MD_INQUIRIES', 'The Enquiries module allows visitors on your site to submit requests to businesses in specific category or directly to obtain from them the necessary information, get offers etc.'), (NULL, 'es', '_MD_INQUIRIES', 'El módulo de consultas permite a los visitantes de su sitio web para enviar solicitudes a las empresas en la categoría específica o directamente para obtener de ellos la información necesaria, se ofrece, etc'), (NULL, 'de', '_MD_INQUIRIES', 'Die Anfragen-Modul ermöglicht es den Besuchern auf Ihrer Website, um Anforderungen an Unternehmen in bestimmten Kategorie einreichen oder direkt von ihnen die notwendigen Informationen erhalten, bekommen Angebote etc.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_MAXIMUM_REPLIES', 'Specifies a maximum number of replies that visitor can retrieve for each inquiry'), (NULL, 'es', '_MS_MAXIMUM_REPLIES', 'Especifica el número máximo de respuestas que el visitante puede recuperar por cada consulta'), (NULL, 'de', '_MS_MAXIMUM_REPLIES', 'Legt die maximale Anzahl der Antworten, die Besucher können für jede Anfrage abzurufen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_ALLOW_DIRECT_INQUIRY', 'Specifies whether to allow visitors to send direct inquiries to businesses'), (NULL, 'es', '_MS_ALLOW_DIRECT_INQUIRY', 'Especifica si se permite a los visitantes enviar consultas directas a las empresas'), (NULL, 'de', '_MS_ALLOW_DIRECT_INQUIRY', 'Gibt an, ob die Besucher direkte Anfragen an Unternehmen senden');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_REPLIES', 'Replies'), (NULL, 'es', '_REPLIES', 'Respuestas'), (NULL, 'de', '_REPLIES', 'Antworten');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_RATING', 'Rating'), (NULL, 'es', '_RATING', 'Clasificación'), (NULL, 'de', '_RATING', 'Wertung');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_SHOW', 'Show'), (NULL, 'es', '_SHOW', 'Mostrar'), (NULL, 'de', '_SHOW', 'Zeigen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INVALID_FILE_SIZE', 'Invalid file size: _FILE_SIZE_ (max. allowed: _MAX_ALLOWED_)'), (NULL, 'es', '_INVALID_FILE_SIZE', 'Tamaño de archivo no válido: _FILE_SIZE_ (máximo permitido: _MAX_ALLOWED_)'), (NULL, 'de', '_INVALID_FILE_SIZE', 'Ungültige Dateigröße: _FILE_SIZE_ (max. erlaubt: _MAX_ALLOWED_)');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_LISTING_MAX_CATEGORIES_ALERT', 'You have reached the maximum number of allowed categories for this listing.'), (NULL, 'es', '_LISTING_MAX_CATEGORIES_ALERT', 'Ha alcanzado el número máximo de categorías permitidas para este listado.'), (NULL, 'de', '_LISTING_MAX_CATEGORIES_ALERT', 'Sie haben die maximal zulässige Anzahl von Kategorien für dieses Angebot erreicht.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_SUB_LOCATION', 'Sub Location'), (NULL, 'es', '_SUB_LOCATION', 'Lugar Sub'), (NULL, 'de', '_SUB_LOCATION', 'Sub Ort');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_I_AM_AVAILABLE', 'I am available'), (NULL, 'es', '_I_AM_AVAILABLE', 'Estoy a su disposición'), (NULL, 'de', '_I_AM_AVAILABLE', 'Ich stehe zur Verfügung');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_PREFERRED_TO_BE_CONTACTED', 'I prefer to be contacted'), (NULL, 'es', '_PREFERRED_TO_BE_CONTACTED', 'Prefiero ser contactado'), (NULL, 'de', '_PREFERRED_TO_BE_CONTACTED', 'Ich bevorzuge es, kontaktiert werden');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_SEND_INQUIRY', 'Send Inquiry'), (NULL, 'es', '_SEND_INQUIRY', 'Enviar Consulta'), (NULL, 'de', '_SEND_INQUIRY', 'Anfrage senden');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_CREDIT_CARD_HOLDER_NAME', 'Card Holder''s Name'), (NULL, 'es', '_CREDIT_CARD_HOLDER_NAME', 'Nombre del titular'), (NULL, 'de', '_CREDIT_CARD_HOLDER_NAME', 'Name des Karteninhabers');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id` ,`key_value` ,`key_text`) VALUES (NULL, 'en', '_CC_CARD_HOLDER_NAME_EMPTY', 'No card holder''s name provided! Please re-enter.'), (NULL, 'es', '_CC_CARD_HOLDER_NAME_EMPTY', 'No Nombre del titular de la tarjeta siempre! Por favor, vuelva a introducir.'), (NULL, 'de', '_CC_CARD_HOLDER_NAME_EMPTY', 'No Name des Karteninhabers versehen! Bitte erneut eingeben.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_STEP', 'Step'), (NULL, 'es', '_STEP', 'Paso'), (NULL, 'de', '_STEP', 'Schritt');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_STEP_1_DESCRIPTION', '<b>Send an inquiry</b><br>Completely free, enter what you want help with and where.'), (NULL, 'es', '_STEP_1_DESCRIPTION', '<b>Enviar solicitud de información</b><br>MedlinePlus Completamente gratis, escriba lo que quiere ayudar y dónde.'), (NULL, 'de', '_STEP_1_DESCRIPTION', '<b>Senden Sie eine Anfrage</b><br>Völlig kostenlos, geben Sie, was Sie wollen helfen, mit und wo.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_STEP_2_DESCRIPTION', '<b>Wait for quotes</b><br>Receive quotes from skilled craftsmen.'), (NULL, 'es', '_STEP_2_DESCRIPTION', '<b>Espere comillas</b><br>MedlinePlus Recibe cotizaciones de expertos artesanos.'), (NULL, 'de', '_STEP_2_DESCRIPTION', '<b>für Zitate Warten</b><br>Erhalten Zitate aus erfahrenen Handwerkern.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_STEP_3_DESCRIPTION', '<b>Compare and choose</b><br>Choose the company you like best among quotations.'), (NULL, 'es', '_STEP_3_DESCRIPTION', '<b>Compare y elija</b><br>MedlinePlus elegir la compañía que más te guste entre comillas.'), (NULL, 'de', '_STEP_3_DESCRIPTION', '<b>Vergleichen und wählen Sie</b><br>Wählen Sie die Firma, die Sie am besten gefällt zu Zitaten.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_WHAT_DO_YOU_NEED', 'What do you need help with?'), (NULL, 'es', '_WHAT_DO_YOU_NEED', '¿Qué necesitas ayuda?'), (NULL, 'de', '_WHAT_DO_YOU_NEED', 'Was brauchen Sie Hilfe bei?');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_SUBMIT_INQUIRY', 'Submit Inquiry'), (NULL, 'es', '_SUBMIT_INQUIRY', 'Enviar Preguntar'), (NULL, 'de', '_SUBMIT_INQUIRY', 'Anfrage senden');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_ANYTIME', 'Anytime'), (NULL, 'es', '_ANYTIME', 'En cualquier momento'), (NULL, 'de', '_ANYTIME', 'Jederzeit');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MORNING', 'Morning'), (NULL, 'es', '_MORNING', 'Mañana'), (NULL, 'de', '_MORNING', 'Morgen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_LUNCH', 'Lunch'), (NULL, 'es', '_LUNCH', 'Almuerzo'), (NULL, 'de', '_LUNCH', 'Mittagessen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_AFTERNOON', 'Afternoon'), (NULL, 'es', '_AFTERNOON', 'Tarde'), (NULL, 'de', '_AFTERNOON', 'Nachmittag');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_EVENING', 'Evening'), (NULL, 'es', '_EVENING', 'Anochecer'), (NULL, 'de', '_EVENING', 'Abend');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_WEEKEND', 'Weekend'), (NULL, 'es', '_WEEKEND', 'Fin de semana'), (NULL, 'de', '_WEEKEND', 'Wochenende');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_BY_PHONE_OR_EMAIL', 'By phone or email'), (NULL, 'es', '_BY_PHONE_OR_EMAIL', 'Por teléfono o correo'), (NULL, 'de', '_BY_PHONE_OR_EMAIL', 'Per Telefon oder E-Mail');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_BY_PHONE', 'By phone'), (NULL, 'es', '_BY_PHONE', 'Por teléfono'), (NULL, 'de', '_BY_PHONE', 'Per Telefon');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_VIA_EMAIL', 'Via email'), (NULL, 'es', '_VIA_EMAIL', 'Por correo electrónico'), (NULL, 'de', '_VIA_EMAIL', 'Via E-Mail');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_DIRECT', 'Direct'), (NULL, 'es', '_DIRECT', 'Dirigir'), (NULL, 'de', '_DIRECT', 'Direkt');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRY_SENT_SUCCESS_MSG', 'Thank you! Your inquiry has been successfully submitted. Please wait for at least few business days to receive a response to your inquiry.'), (NULL, 'es', '_INQUIRY_SENT_SUCCESS_MSG', '¡Gracias! Su consulta ha sido enviada correctamente. Por favor, espere por lo menos durante algunos días laborales para recibir una respuesta a su consulta.'), (NULL, 'de', '_INQUIRY_SENT_SUCCESS_MSG', 'Danke! Ihre Anfrage wurde erfolgreich übermittelt. Bitte warten Sie mindestens wenigen Werktagen eine Antwort auf Ihre Anfrage erhalten.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_SEND_DIRECT_INQUIRY', 'Send Direct Inquiry'), (NULL, 'es', '_SEND_DIRECT_INQUIRY', 'Enviar Consulta Directa'), (NULL, 'de', '_SEND_DIRECT_INQUIRY', 'Senden Direkte Anfrage');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_SHOW_INQUIRIES_BLOCK_BLOCK', 'Defines whether to show Last Inquiries side block or not'), (NULL, 'es', '_MS_SHOW_INQUIRIES_BLOCK_BLOCK', 'Define si se mostrará bloque Última lado Consultas o no'), (NULL, 'de', '_MS_SHOW_INQUIRIES_BLOCK_BLOCK', 'Legt fest, ob Last Anfragen Seite Block zeigen oder nicht');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INVALID_IMAGE_FILE_TYPE', 'Uploaded file is not a valid image! Please re-enter.'), (NULL, 'es', '_INVALID_IMAGE_FILE_TYPE', 'El archivo cargado no es una imagen válida! Por favor, vuelva a entrar.'), (NULL, 'de', '_INVALID_IMAGE_FILE_TYPE', 'Hochgeladene Datei ist kein gültiges Bild! Bitte geben Sie erneut.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_DESCRIBE_WHAT_YOU_NEED', '<b>Describe here what you need</b> <br>NOTE! It is free and not binding'), (NULL, 'es', '_DESCRIBE_WHAT_YOU_NEED', '<b>Describa lo que usted necesita aquí</b> MedlinePlus<br>NOTA! Es gratuito y no vinculante'), (NULL, 'de', '_DESCRIBE_WHAT_YOU_NEED', '<b>Beschreiben Sie, was Sie hier benötigen </b><br>HINWEIS! Es ist kostenlos und unverbindlich');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRY_FORM_DISCLAIMER', 'Your personal information will be used only for the purposes stated above, will not be shared with third parties other than those described above and you can only retrieve reply out of _COUNT_ companies.'), (NULL, 'es', '_INQUIRY_FORM_DISCLAIMER', 'Sus datos personales serán utilizados únicamente para los fines indicados más arriba, no será compartida con terceros distintos de los descritos anteriormente y sólo se puede obtener respuesta de las empresas _COUNT_.'), (NULL, 'de', '_INQUIRY_FORM_DISCLAIMER', 'Ihre persönlichen Daten werden nur für die oben genannten Zwecke verwendet werden, werden nicht mit Dritten andere als die oben beschriebenen geteilt werden und Sie können nur abgerufen antworten von _COUNT_ Unternehmen.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INCOMING_JOBS', 'Incoming Jobs'), (NULL, 'es', '_INCOMING_JOBS', 'Empleos Entrantes'), (NULL, 'de', '_INCOMING_JOBS', 'Eingehende Jobs');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_CATEGORIES_COUNT', 'Categories Count'), (NULL, 'es', '_CATEGORIES_COUNT', 'Categorías Conde'), (NULL, 'de', '_CATEGORIES_COUNT', 'Kategorien Graf');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MORE_INFO', 'More Info'), (NULL, 'es', '_MORE_INFO', 'Más información'), (NULL, 'de', '_MORE_INFO', 'Mehr Info');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRY_DETAILS', 'Inquiry Details'), (NULL, 'es', '_INQUIRY_DETAILS', 'Detalles Query'), (NULL, 'de', '_INQUIRY_DETAILS', 'Anfrage Details');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_LISTING', 'Listing'), (NULL, 'es', '_LISTING', 'Listado'), (NULL, 'de', '_LISTING', 'Auflistung');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_ALL_LOCATIONS', 'all locations'), (NULL, 'es', '_ALL_LOCATIONS', 'todas las ubicaciones'), (NULL, 'de', '_ALL_LOCATIONS', 'alle Standorte');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_ALL_SUB_LOCATIONS', 'all sub locations'), (NULL, 'es', '_ALL_SUB_LOCATIONS', 'todos los sub-sitios'), (NULL, 'de', '_ALL_SUB_LOCATIONS', 'alle Sub-Standorten');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MAXIMUM_ALLOWED_INQUIRIES_PER_SESSION', 'You have reached the maximum number (_COUNT_) of allowed inquiries for this session. Please try again later.'), (NULL, 'es', '_MAXIMUM_ALLOWED_INQUIRIES_PER_SESSION', 'Ha alcanzado el número máximo (_COUNT_) de consultas permitidas para esta sesión. Por favor, inténtelo de nuevo más tarde.'), (NULL, 'de', '_MAXIMUM_ALLOWED_INQUIRIES_PER_SESSION', 'Sie haben die maximale Anzahl (_COUNT_) der erlaubten Anfragen für diese Sitzung erreicht. Bitte versuchen Sie es später erneut.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_LOGO', 'Logo'), (NULL, 'es', '_LOGO', 'Logotipo'), (NULL, 'de', '_LOGO', 'Emblem');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_DIRECT_INQUIRY_NOT_ALLOWED', 'According to the system settings you cannot send direct inquiry to this company/business!'), (NULL, 'es', '_DIRECT_INQUIRY_NOT_ALLOWED', 'Según la configuración del sistema no puede enviar pregunta directamente a esta empresa/negocio!'), (NULL, 'de', '_DIRECT_INQUIRY_NOT_ALLOWED', 'Nach den Systemeinstellungen können Sie nicht senden direkt eine Anfrage an dieses Unternehmen/Wirtschaft!');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_CLOSED', 'Closed'), (NULL, 'es', '_CLOSED', 'Cerrado'), (NULL, 'de', '_CLOSED', 'Geschlossen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_NO_INCOMMING_JOBS_YET', 'No incoming jobs yet.'), (NULL, 'es', '_NO_INCOMMING_JOBS_YET', 'No hay trabajos entrantes aún.'), (NULL, 'de', '_NO_INCOMMING_JOBS_YET', 'Keine eingehenden Aufträge noch.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_KEYWORDS_COUNT', 'Keywords Count'), (NULL, 'es', '_KEYWORDS_COUNT', 'Palabras clave conde'), (NULL, 'de', '_KEYWORDS_COUNT', 'Schlüsselwörter Graf');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRIES_COUNT', 'Inquiries Count'), (NULL, 'es', '_INQUIRIES_COUNT', 'Preguntas conde'), (NULL, 'de', '_INQUIRIES_COUNT', 'Anfragen Graf');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_PER_MONTH', 'per month'), (NULL, 'es', '_PER_MONTH', 'por mes'), (NULL, 'de', '_PER_MONTH', 'pro Monat');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRIES_REPLIES', 'Inquiries Replies'), (NULL, 'es', '_INQUIRIES_REPLIES', 'Consultas respuestas'), (NULL, 'de', '_INQUIRIES_REPLIES', 'Anfragen Antworten');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRY_BUTTON', 'Inquiry Button'), (NULL, 'es', '_INQUIRY_BUTTON', 'Verificación de teclas'), (NULL, 'de', '_INQUIRY_BUTTON', 'Anfrage-Button');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_RATING_BUTTON', 'Rating Button'), (NULL, 'es', '_RATING_BUTTON', 'Puntuación Button'), (NULL, 'de', '_RATING_BUTTON', 'Rating-Taste');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_DATE_ADDED', 'Date Added'), (NULL, 'es', '_DATE_ADDED', 'Fecha Alta'), (NULL, 'de', '_DATE_ADDED', 'Aufgenommen am');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_REPLY', 'Reply'), (NULL, 'es', '_REPLY', 'Responder'), (NULL, 'de', '_REPLY', 'Antworten');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_ANSWERED', 'Answered'), (NULL, 'es', '_ANSWERED', 'Respondido'), (NULL, 'de', '_ANSWERED', 'Beantwortete');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_PC_INQUIRY_REPLAY_DETAILS_TEXT', 'information about inquiry and customer'), (NULL, 'es', '_PC_INQUIRY_REPLAY_DETAILS_TEXT', 'información sobre la investigación y el cliente'), (NULL, 'de', '_PC_INQUIRY_REPLAY_DETAILS_TEXT', 'Informationen über Anfragen und Kunden');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_DASHBOARD_INQUIRIES_LINK', '<p><b>&#8226;</b> <a href="index.php?customer=inquiries">Inquiries</a> page allows you to view received inquires and reply them.</p>'), (NULL, 'es', '_DASHBOARD_INQUIRIES_LINK', '<p><b>&#8226;</b> <a href="index.php?customer=inquiries">Preguntas</a> de página le permite ver y contestar preguntas recibidas ellos.</p>'), (NULL, 'de', '_DASHBOARD_INQUIRIES_LINK', '<p><b>&#8226;</b> <a href="index.php?customer=inquiries">Anfragen</a> Seite können Sie erhalten Anfragen ansehen und beantworten sie.</p>');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_SELECT_PLAN', 'Select Advertise Plan'), (NULL, 'es', '_SELECT_PLAN', 'Seleccione Plan de Publicidad'), (NULL, 'de', '_SELECT_PLAN', 'Wählen Sie Werben Plans');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value` ,`key_text`) VALUES (NULL, 'en',  '_DASHBOARD_ORDERS_LINK',  '<p><b>&#8226;</b> <a href=''index.php?customer=my_orders''>Orders</a> page allows you to manage your orders.</p>'), (NULL ,  'es',  '_DASHBOARD_ORDERS_LINK',  '<p><b>&#8226;</b> <a href=''index.php?customer=my_orders''>Los pedidos</a> página le permite gestionar sus pedidos.</p>'), (NULL ,  'de',  '_DASHBOARD_ORDERS_LINK',  '<p><b>&#8226;</b> <a href=''index.php?customer=my_orders''>Bestellungen</a> Seite können Sie Ihre Aufträge verwalten.</p>');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_CLICK_TO_SELECT', 'Click to select'), (NULL, 'es', '_CLICK_TO_SELECT', 'Haga clic para seleccionar'), (NULL, 'de', '_CLICK_TO_SELECT', 'Klicken Sie auf');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_RATINGS', 'Ratings'), (NULL, 'es', '_RATINGS', 'Valoraciones'), (NULL, 'de', '_RATINGS', 'Bewertungen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_RATINGS_SETTINGS', 'Ratings Settings'), (NULL, 'es', '_RATINGS_SETTINGS', 'Valoraciones Configuración'), (NULL, 'de', '_RATINGS_SETTINGS', 'Bewertungen Einstellungen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_LISTING_MAX_KEYWORDS_ALERT', 'The maximum allowed number of keywords is _MAX_! Please re-enter.'), (NULL, 'es', '_LISTING_MAX_KEYWORDS_ALERT', 'El número máximo de palabras clave es _MAX_! Por favor, vuelva a entrar.'), (NULL, 'de', '_LISTING_MAX_KEYWORDS_ALERT', 'Die maximal zulässige Anzahl an Keywords beträgt _Max_! Bitte geben Sie erneut.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MD_RATINGS', 'The Ratings module allows your users to rate the listings. The number of votes and average rating will be shown at the appropriate listing.'), (NULL, 'es', '_MD_RATINGS', 'El módulo de calificación permite a los usuarios votar por las listas. El número de votos y la calificación media se mostrará en la lista correspondiente.'), (NULL, 'de', '_MD_RATINGS', 'Die Ratings-Modul ermöglicht es den Benutzern, um die Angebote zu bewerten. Die Zahl der Stimmen und die durchschnittliche Bewertung wird auf der entsprechenden Liste angezeigt werden.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_RATINGS_USER_TYPE', 'Type of users, who can rate listings'), (NULL, 'es', '_MS_RATINGS_USER_TYPE', 'Tipo de usuarios, que pueden votar los listados'), (NULL, 'de', '_MS_RATINGS_USER_TYPE', 'Art der Nutzer, die Inserate bewerten können');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_MULTIPLE_ITEMS_PER_DAY', 'Specifies whether to allow users to rate multiple items per day or not'), (NULL, 'es', '_MS_MULTIPLE_ITEMS_PER_DAY', 'Especifica si se permite a los usuarios artículos tasas múltiples por día o no'), (NULL, 'de', '_MS_MULTIPLE_ITEMS_PER_DAY', 'Gibt an, ob Benutzer sich mehrere Artikel pro Tag oder nicht zulassen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_VOTE_NOT_REGISTERED', 'Your vote has not been registered! You must be logged in before you can vote.'), (NULL, 'es', '_VOTE_NOT_REGISTERED', 'Su voto no se ha registrado? Debe estar registrado para poder votar.'), (NULL, 'de', '_VOTE_NOT_REGISTERED', 'Ihre Stimme wurde nicht registriert! Sie müssen, bevor Sie abstimmen können protokolliert werden.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_KEEP_HISTORY_DAYS', 'The maximum number of days to keep the inquiries history'), (NULL, 'es', '_MS_KEEP_HISTORY_DAYS', 'El número máximo de días para conservar el historial de consultas'), (NULL, 'de', '_MS_KEEP_HISTORY_DAYS', 'Die maximale Anzahl der Tage, um die Anfragen Geschichte zu halten');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INQUIRY_TO_YOURSELF_PROHIBITED', 'You cannot send inquiry to yourself!'), (NULL, 'es', '_INQUIRY_TO_YOURSELF_PROHIBITED', 'No se puede enviar la investigación a ti mismo!'), (NULL, 'de', '_INQUIRY_TO_YOURSELF_PROHIBITED', 'Sie können nicht senden Anfrage an sich selbst!');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INTEGRATION', 'Integration'), (NULL, 'es', '_INTEGRATION', 'Integración'), (NULL, 'de', '_INTEGRATION', 'Integration');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INTEGRATION_MESSAGE', 'Copy the code below and put it in the appropriate place of your website to get a Inquiry Form.'), (NULL, 'es', '_INTEGRATION_MESSAGE', 'Copie el código abajo y lo puso en el lugar correspondiente de su sitio web para obtener un formulario de consulta.'), (NULL, 'de', '_INTEGRATION_MESSAGE', 'Kopieren Sie den Code unten ein und steckte es in die entsprechende Stelle Ihrer Webseite ein Anfrageformular zu bekommen.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_SUB_CATEGORIES_COUNT', 'Specifies a  number of sub-categories for each category that could be displayed on home page'), (NULL, 'es', '_MS_SUB_CATEGORIES_COUNT', 'Especifica un número de sub-categorías para cada categoría que se puede mostrar en la página principal'), (NULL, 'de', '_MS_SUB_CATEGORIES_COUNT', 'Gibt eine Anzahl von Sub-Kategorien für jede Kategorie, die auf der Startseite angezeigt werden können');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_SEND_INQUIRY_TO', 'Send Inquiry to'), (NULL, 'es', '_SEND_INQUIRY_TO', 'Enviar la pregunta a'), (NULL, 'de', '_SEND_INQUIRY_TO', 'Anfrage senden an');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_INTEGRATION_TOP_MESSAGE', 'You may integrate Inquiry Form engine with another existing website.'), (NULL, 'es', '_INTEGRATION_TOP_MESSAGE', 'Usted puede integrar motor Formulario de consulta con otro sitio web existente.'), (NULL, 'de', '_INTEGRATION_TOP_MESSAGE', 'Sie können Anfrageformular Motor mit anderen bestehenden Website zu integrieren.');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_DECIMALS', 'Decimals'), (NULL, 'es', '_DECIMALS', 'Decimales'), (NULL, 'de', '_DECIMALS', 'Dezimalstellen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MONTHS', 'Months'), (NULL, 'es', '_MONTHS', 'Meses'), (NULL, 'de', '_MONTHS', 'Monate');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_HIDDEN', 'Hidden'), (NULL, 'es', '_HIDDEN', 'Oculto'), (NULL, 'de', '_HIDDEN', 'Versteckt');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_BANNERS_CAPTION_HTML', 'Specifies whether to allow using of HTML in slideshow captions or not'), (NULL, 'es', '_MS_BANNERS_CAPTION_HTML', 'Especifica si se debe permitir el uso de HTML en los textos de presentación de diapositivas o no'), (NULL, 'de', '_MS_BANNERS_CAPTION_HTML', 'Gibt an, ob mit Hilfe von HTML-Diashow in Bildunterschriften oder nicht');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_ADD_WATERMARK', 'Specifies whether to add watermark to listing images or not'), (NULL, 'es', '_MS_ADD_WATERMARK', 'Especifica si se debe añadir marcas de agua a las imágenes o no listado'), (NULL, 'de', '_MS_ADD_WATERMARK', 'Gibt an, ob Wasserzeichen zur Liste Bilder oder nicht hinzufügen');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_MS_WATERMARK_TEXT', 'Watermark text that will be added to images'), (NULL, 'es', '_MS_WATERMARK_TEXT', 'Texto de la marca que se añadirá a las imágenes'), (NULL, 'de', '_MS_WATERMARK_TEXT', 'Wasserzeichen Text, der die Bilder hinzugefügt werden');
INSERT INTO `<DB_PREFIX>vocabulary` (`id`, `language_id`, `key_value`, `key_text`) VALUES (NULL, 'en', '_OWNER', 'Owner'), (NULL, 'es', '_OWNER', 'Propietario'), (NULL, 'de', '_OWNER', 'Eigentümer');
