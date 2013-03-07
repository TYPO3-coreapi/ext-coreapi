<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Georg Ringer <georg.ringer@cyberhouse.at>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * DB API service
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Service_DatabaseApiService {
	const ACTION_UPDATE_CLEAR_TABLE = 1;
	const ACTION_UPDATE_ADD = 2;
	const ACTION_UPDATE_CHANGE = 3;
	const ACTION_UPDATE_CREATE_TABLE = 4;
	const ACTION_REMOVE_CHANGE = 5;
	const ACTION_REMOVE_DROP = 6;
	const ACTION_REMOVE_CHANGE_TABLE = 7;
	const ACTION_REMOVE_DROP_TABLE = 8;

	/**
	 * @var t3lib_install_Sql Instance of SQL handler
	 */
	protected $sqlHandler = NULL;

	/**
	 * Constructor function
	 */
	public function __construct() {
		$this->sqlHandler = t3lib_div::makeInstance('t3lib_install_Sql');
	}

	/**
	 * Database compare
	 *
	 * @param string $actions comma separated list of IDs
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function databaseCompare($actions) {
		$errors = array();

		$availableActions = array_flip(t3lib_div::makeInstance('Tx_Extbase_Reflection_ClassReflection', 'Tx_Coreapi_Service_DatabaseApiService')->getConstants());

		if (empty($actions)) {
			throw new InvalidArgumentException('No compare modes defined');
		}

		$allowedActions = array();
		$actionSplit = t3lib_div::trimExplode(',', $actions);
		foreach ($actionSplit as $split) {
			if (!isset($availableActions[$split])) {
				throw new InvalidArgumentException(sprintf('Action "%s" is not available!', $split));
			}
			$allowedActions[$split] = 1;
		}


		$tblFileContent = t3lib_div::getUrl(PATH_t3lib . 'stddb/tables.sql');

		foreach ($GLOBALS['TYPO3_LOADED_EXT'] as $loadedExtConf) {
			if (is_array($loadedExtConf) && $loadedExtConf['ext_tables.sql']) {
				$extensionSqlContent = t3lib_div::getUrl($loadedExtConf['ext_tables.sql']);
				$tblFileContent .= LF . LF . LF . LF . $extensionSqlContent;
			}
		}

		if ($tblFileContent) {
			$fileContent = implode(LF, $this->sqlHandler->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));
			$FDfile = $this->sqlHandler->getFieldDefinitions_fileContent($fileContent);

			$FDdb = $this->sqlHandler->getFieldDefinitions_database();

			$diff = $this->sqlHandler->getDatabaseExtra($FDfile, $FDdb);
			$update_statements = $this->sqlHandler->getUpdateSuggestions($diff);

			$diff = $this->sqlHandler->getDatabaseExtra($FDdb, $FDfile);
			$remove_statements = $this->sqlHandler->getUpdateSuggestions($diff, 'remove');

			$allowedRequestKeys = $this->getRequestKeys($update_statements, $remove_statements);
			$results = array();

			if ($allowedActions[self::ACTION_UPDATE_CLEAR_TABLE] == 1) {
				$results[] = $this->sqlHandler->performUpdateQueries($update_statements['clear_table'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_UPDATE_ADD] == 1) {
				$results[] = $this->sqlHandler->performUpdateQueries($update_statements['add'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_UPDATE_CHANGE] == 1) {
				$results[] = $this->sqlHandler->performUpdateQueries($update_statements['change'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_REMOVE_CHANGE] == 1) {
				$results[] = $this->sqlHandler->performUpdateQueries($remove_statements['change'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_REMOVE_DROP] == 1) {
				$results[] = $this->sqlHandler->performUpdateQueries($remove_statements['drop'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_UPDATE_CREATE_TABLE] == 1) {
				$results[] = $this->sqlHandler->performUpdateQueries($update_statements['create_table'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_REMOVE_CHANGE_TABLE] == 1) {
				$results[] = $this->sqlHandler->performUpdateQueries($remove_statements['change_table'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_REMOVE_DROP_TABLE] == 1) {
				$results[] = $this->sqlHandler->performUpdateQueries($remove_statements['drop_table'], $allowedRequestKeys);
			}

			foreach ($results as $resultSet) {
				if (is_array($resultSet)) {
					foreach ($resultSet as $key => $errorMessage) {
						$errors[$key] = $errorMessage;
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Get all available actions
	 * @return array
	 */
	public function databaseCompareAvailableActions() {
		$availableActions = array_flip(t3lib_div::makeInstance('Tx_Extbase_Reflection_ClassReflection', 'Tx_Coreapi_Service_DatabaseApiService')->getConstants());

		foreach ($availableActions as $number => $action) {
			if (!t3lib_div::isFirstPartOfStr($action, 'ACTION_')) {
				unset($availableActions[$number]);
			}
		}
		return $availableActions;
	}

	/**
	 * Get all request keys, even for those requests which are not used
	 *
	 * @param array $update
	 * @param array $remove
	 * @return array
	 */
	protected function getRequestKeys(array $update, array $remove) {
		$tmpKeys = array();

		$updateActions = array('clear_table', 'add', 'change', 'create_table');
		$removeActions = array('change', 'drop', 'change_table', 'drop_table');

		foreach ($updateActions as $updateAction) {
			if (isset($update[$updateAction]) && is_array($update[$updateAction])) {
				$tmpKeys += array_keys($update[$updateAction]);
			}
		}

		foreach ($removeActions as $removeAction) {
			if (isset($remove[$removeAction]) && is_array($remove[$removeAction])) {
				$tmpKeys += array_keys($remove[$removeAction]);
			}
		}

		$finalKeys = array();
		foreach ($tmpKeys as $key) {
			$finalKeys[$key] = TRUE;
		}
		return $finalKeys;
	}

}

?>