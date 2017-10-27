<?php
require('includes/application_top.php');

tep_db_query(
    "CREATE TABLE IF NOT EXISTS `newsletter2go_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `apikey` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

tep_db_query("CREATE TABLE IF NOT EXISTS `newsletter2go_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `info` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `newsletter2go_log_ibfk_1` (`user_id`),
  CONSTRAINT `newsletter2go_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `newsletter2go_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

echo 'Plugin installed!';
