CREATE TABLE IF NOT EXISTS `magazine` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `text` text NOT NULL,
  `url` varchar(200) NOT NULL,
  `homepage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `order` int(10) unsigned NOT NULL DEFAULT '0',
  `access_rights` varchar(10) DEFAULT 'all',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

INSERT INTO `magazine` (`id`, `name`, `text`, `url`, `homepage`, `order`, `access_rights`) VALUES
	(1, 'Toto je domovská stránka', 'Moje **[nejmilejší][1]** <strike>první</strike> stránka 2\n\n\n  [1]: http://localhost:8080/skola/pd/pd-lab/www/documentation/?url=moje-druha-stranka', 'toto-je-domovska-stranka', 1, 1, 'all'),
	(2, 'Moje druhá stránka', 'Moje nejmilejší druhá stránka 2222', 'moje-druha-stranka', 0, 2, 'admin'),
	(3, 'Kotyho dokumentace', 'dsgvfsvg df vrdfsvdfs\n*dfs gbsfd bf*\ndfs [hbft][1]\n\n\n  [1]: http://seznam.cz', 'kotyho-dokumentace', 0, 3, 'all'),
	(4, 'Moje první stránka', 'cgk', 'moje-prvni-stranka', 0, 45555, 'all');

