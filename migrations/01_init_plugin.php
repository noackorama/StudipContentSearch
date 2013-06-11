<?php
class InitPlugin extends DBMigration
{
	function up(){
	//DBManager::get()->exec("DELETE FROM `config` WHERE field =  'CONFIG_StudipContentIndexerDocuments'");
	//DBManager::get()->exec("DROP TABLE IF EXISTS content_search_dokumente_index");
		DBManager::get()->exec("
		CREATE TABLE IF NOT EXISTS `content_search_dokumente_index` (
  `dokument_id` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `filename` varchar(255) NOT NULL,
  `owner` varchar(128) NOT NULL,
  `chdate` int(10) unsigned NOT NULL,
  `object_id` varchar(32) NOT NULL,
  `content` mediumtext NOT NULL,
  `filetype` varchar(6) NOT NULL DEFAULT '',
  PRIMARY KEY (`dokument_id`),
  KEY `range_id` (`object_id`),
  FULLTEXT KEY `name` (`name`,`description`,`filename`,`owner`,`content`,`filetype`)
			) ENGINE=MyISAM
			");
	}
}