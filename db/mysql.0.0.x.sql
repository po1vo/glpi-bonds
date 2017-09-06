CREATE TABLE `glpi_plugin_bonds_bonds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `asset_type` enum('NetworkEquipment','Computer','Peripheral') COLLATE utf8_unicode_ci NOT NULL,
  `outlet_id` smallint(6) NOT NULL,
  `outlet_type` enum('Power','Console') COLLATE utf8_unicode_ci NOT NULL,
  `connected_to` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `asset_index` (`asset_id`,`asset_type`),
  KEY `connected_id` (`connected_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
