CREATE TABLE `mapps_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(155) NOT NULL DEFAULT 'global',
  `mapps_id` int(11) NOT NULL,
  `title` varchar(55) NOT NULL,
  `type` varchar(55) NOT NULL DEFAULT 'js',
  `script_code` text,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `mapps_components` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(155) NOT NULL DEFAULT 'global',
  `mapps_id` int(11) NOT NULL,
  `panel_code` varchar(55) NOT NULL,
  `comp_name` varchar(155) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `src_type` varchar(15) NOT NULL DEFAULT 'php',
  `src_path` varchar(180) NOT NULL,
  `comp_params` text,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `mapps_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(155) NOT NULL DEFAULT 'global',
  `mapps_id` int(11) NOT NULL,
  `form_id` varchar(155) NOT NULL,
  `ref_id` varchar(155) NOT NULL,
  `sess_id` varchar(155) NOT NULL,
  `attachment_name` varchar(155) NOT NULL,
  `attachment_path` varchar(250) NOT NULL,
  `attachment_size` int(11) NOT NULL,
  `attachment_mime` varchar(55) NOT NULL,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `mapps_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(75) NOT NULL DEFAULT 'globals',
  `mapps_id` int(11) NOT NULL,
  `menuid` varchar(25) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `menugroup` varchar(150) DEFAULT NULL,
  `class` varchar(150) DEFAULT NULL,
  `target` varchar(55) DEFAULT NULL,
  `link_url` varchar(255) NOT NULL DEFAULT '#',
  `iconpath` varchar(255) DEFAULT NULL,
  `tips` varchar(255) DEFAULT NULL,
  `privilege` varchar(1000) DEFAULT '*',
  `weight` int(11) DEFAULT '10',
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `mapps_panels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(155) NOT NULL DEFAULT 'global',
  `mapps_id` int(11) NOT NULL,
  `panel_code` varchar(55) NOT NULL,
  `title` varchar(55) NOT NULL,
  `config` text,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `mapps_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(155) NOT NULL DEFAULT 'global',
  `mapps_id` int(11) NOT NULL,
  `options_json` text,
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `mapps_tbl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(155) NOT NULL DEFAULT 'global',
  `app_key` varchar(255) NOT NULL,
  `app_site` varchar(55) NOT NULL,
  `app_name` varchar(155) NOT NULL,
  `build` int(11) NOT NULL DEFAULT '1',
  `app_secret` varchar(155) NOT NULL,
  `default_policies` text,
  `published` enum('false','true') NOT NULL DEFAULT 'false',
  `blocked` enum('true','false') DEFAULT 'false',
  `created_by` varchar(155) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_by` varchar(155) NOT NULL,
  `edited_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
