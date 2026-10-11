CREATE TABLE IF NOT EXISTS `#__compacttables_tables` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `has_header` tinyint NOT NULL DEFAULT 1,
  `num_cols` smallint unsigned NOT NULL DEFAULT 0,
  `num_rows` smallint unsigned NOT NULL DEFAULT 0,
  `data` mediumtext NOT NULL,
  `params` text NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
