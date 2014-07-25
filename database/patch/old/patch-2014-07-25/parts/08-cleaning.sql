/* smazání nepotřebných tabulek */
DROP TABLE `facebook`;
DROP TABLE `wall_items`;
DROP TABLE `pages_galleries`;
DROP TABLE `pages_forms`;
DROP TABLE `pages`;
DROP TABLE `map`;
DROP TABLE `forms_query`;
DROP TABLE `forms3`;
DROP TABLE `forms2`;
DROP TABLE `form_new_send`;
DROP TABLE `news`;
DROP TABLE `news_galleries`;
DROP TABLE `google_analytics`;
DROP TABLE `authorizator_table`;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
DROP TABLE `texts`;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;