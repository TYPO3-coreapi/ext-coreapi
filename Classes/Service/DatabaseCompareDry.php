<?php

namespace Etobi\CoreAPI\Service;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use InvalidArgumentException;

/**
 * Class DatabaseDryCompare
 * 
 * @package Etobi\CoreAPI\Service
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @author Stefano Kowalke <blueduck@gmx.net>
 */
class DatabaseCompareDry extends DatabaseComparator {

	/**
	 * @var \TYPO3\CMS\Install\Service\SqlSchemaMigrationService $schemaMigrationService Instance of SQL handler
	 */
	protected $schemaMigrationService;

	/**
	 * Inject the SchemaMigrationService
	 *
	 * @param \TYPO3\CMS\Install\Service\SqlSchemaMigrationService $schemaMigrationService
	 */
	public function injectSchemaMigrationService(\TYPO3\CMS\Install\Service\SqlSchemaMigrationService $schemaMigrationService) {
		$this->schemaMigrationService = $schemaMigrationService;
	}

	/**
	 * Database compare.
	 *
	 * @param string $actions comma separated list of IDs
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return array
	 */
	public function compare($actions) {
		$errors = array();
		$allowedActions = array();

		$this->checkAvailableActions($actions, $allowedActions);

		$tblFileContent = '';

		foreach ($GLOBALS['TYPO3_LOADED_EXT'] as $loadedExtConf) {
			if (is_array($loadedExtConf) && $loadedExtConf['ext_tables.sql']) {
				$extensionSqlContent = $this->getUrl($loadedExtConf['ext_tables.sql']);
				$tblFileContent .= LF . LF . LF . LF . $extensionSqlContent;
			}
		}

		if (is_callable('TYPO3\\CMS\\Core\\Cache\\Cache::getDatabaseTableDefinitions')) {
			$tblFileContent .= $this->getDatabaseTableDefinitionsFromCache();
		}

		if (class_exists('TYPO3\\CMS\\Core\\Category\\CategoryRegistry')) {
			$tblFileContent .= $this->getCategoryRegistry()->getDatabaseTableDefinitions();
		}

		if ($tblFileContent) {
			$fileContent = implode(LF, $this->schemaMigrationService->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));
			$fieldDefinitionsFromFile = $this->schemaMigrationService->getFieldDefinitions_fileContent($fileContent);

			$fieldDefinitionsFromDb = $this->schemaMigrationService->getFieldDefinitions_database();

			$diff = $this->schemaMigrationService->getDatabaseExtra($fieldDefinitionsFromFile, $fieldDefinitionsFromDb);
			$updateStatements = $this->schemaMigrationService->getUpdateSuggestions($diff);

			$results = array();

			if ($allowedActions[self::ACTION_UPDATE_CLEAR_TABLE] == 1) {
				$results['clear_table'] = $updateStatements['clear_table'];
			}

			if ($allowedActions[self::ACTION_UPDATE_ADD] == 1) {
				$results['add'] = $updateStatements['add'];
			}

			if ($allowedActions[self::ACTION_UPDATE_CHANGE] == 1) {
				$results['update_change'] = $updateStatements['change'];
			}

			if ($allowedActions[self::ACTION_REMOVE_CHANGE] == 1) {
				$results['remove_change'] = $updateStatements['change'];
			}

			if ($allowedActions[self::ACTION_REMOVE_DROP] == 1) {
				$results['drop'] = $updateStatements['drop'];
			}

			if ($allowedActions[self::ACTION_UPDATE_CREATE_TABLE] == 1) {
				$results['create_table'] = $updateStatements['create_table'];
			}

			if ($allowedActions[self::ACTION_REMOVE_CHANGE_TABLE] == 1) {
				$results['change_table'] = $updateStatements['change_table'];
			}

			if ($allowedActions[self::ACTION_REMOVE_DROP_TABLE] == 1) {
				$results['change_table'] = $updateStatements['change_table'];
			}

			foreach ($results as $key => $resultSet) {
				if (!empty($resultSet)) {
					$errors[$key] = $resultSet;
				}
			}
		}

		return $errors;
	}
}

