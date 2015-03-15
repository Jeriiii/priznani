/* propojí města a okrasy a kraje */
ALTER TABLE city
	ADD CONSTRAINT `FK_city_district` FOREIGN KEY (`districtID`) REFERENCES `district` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE district
	ADD CONSTRAINT `FK_district_region` FOREIGN KEY (`regionID`) REFERENCES `region` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;