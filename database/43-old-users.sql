/* starší registrovaní uživatelé */

CREATE TABLE IF NOT EXISTS `old_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `propertyID` int(11) unsigned DEFAULT NULL,
  `coupleID` int(11) unsigned DEFAULT NULL,
  `profilFotoID` int(11) unsigned DEFAULT NULL,
  `confirmed` varchar(100) DEFAULT NULL,
  `admin_score` int(100) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT '',
  `last_active` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `email` varchar(50) DEFAULT '',
  `user_name` varchar(20) DEFAULT '',
  `password` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13022 ;
