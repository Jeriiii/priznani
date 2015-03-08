<?php

<#assign licenseFirst = "/* ">
<#assign licensePrefix = " * ">
<#assign licenseLast = " */">
<#include "${project.licensePath}">


namespace POS\Model;

/**
 * NAME DAO NAMEDao
 * slouží k
 *
 * @author ${user}
 */
class ${name}Dao extends AbstractDao {

	const TABLE_NAME = "";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
