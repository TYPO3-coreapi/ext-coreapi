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
 * Class DatabaseRealCompare
 * 
 * @package Etobi\CoreAPI\Service
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @author  Stefano Kowalke <blueduck@gmx.net>
 */
class DatabaseCompareReal extends DatabaseComparator {

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
	 * @return array
	 */
	public function compare($actions) {
		$errors = array();
		$allowedActions = array();

		$this->checkAvailableActions($actions, $allowedActions);

//		$classReflection = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', 'Etobi\\CoreAPI\\Service\\DatabaseComparator');
//		$availableActions = array_flip($classReflection->getConstants());
//
//		if (empty($actions)) {
//			throw new InvalidArgumentException('No compare modes defined');
//		}
//
//		$allowedActions = array();
//		$actionSplit = $this->trimExplode($actions);
//		foreach ($actionSplit as $split) {
//			if (!isset($availableActions[$split])) {
//				throw new InvalidArgumentException(sprintf('Action "%s" is not available!', $split));
//			}
//			$allowedActions[$split] = 1;
//		}

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

			$diff = $this->schemaMigrationService->getDatabaseExtra($fieldDefinitionsFromDb, $fieldDefinitionsFromFile);
			$removeStatements = $this->schemaMigrationService->getUpdateSuggestions($diff, 'remove');

			$allowedRequestKeys = $this->getRequestKeys($updateStatements, $removeStatements);
			$results = array();

			if ($allowedActions[self::ACTION_UPDATE_CLEAR_TABLE] == 1) {
				$results[] = $this->schemaMigrationService->performUpdateQueries($updateStatements['clear_table'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_UPDATE_ADD] == 1) {
				$results[] = $this->schemaMigrationService->performUpdateQueries($updateStatements['add'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_UPDATE_CHANGE] == 1) {
				$results[] = $this->schemaMigrationService->performUpdateQueries($updateStatements['change'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_REMOVE_CHANGE] == 1) {
				$results[] = $this->schemaMigrationService->performUpdateQueries($removeStatements['change'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_REMOVE_DROP] == 1) {
				$results[] = $this->schemaMigrationService->performUpdateQueries($removeStatements['drop'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_UPDATE_CREATE_TABLE] == 1) {
				$results[] = $this->schemaMigrationService->performUpdateQueries($updateStatements['create_table'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_REMOVE_CHANGE_TABLE] == 1) {
				$results[] = $this->schemaMigrationService->performUpdateQueries($removeStatements['change_table'], $allowedRequestKeys);
			}

			if ($allowedActions[self::ACTION_REMOVE_DROP_TABLE] == 1) {
				$results[] = $this->schemaMigrationService->performUpdateQueries($removeStatements['drop_table'], $allowedRequestKeys);
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
	 * Get all request keys, even for those requests which are not used.
	 *
	 * @param array $update
	 * @param array $remove
	 *
	 * @return array
	 */
	protected function getRequestKeys(array $update, array $remove) {
		$tmpKeys = array();

		$updateActions = array('clear_table', 'add', 'change', 'create_table');
		$removeActions = array('change', 'drop', 'change_table', 'drop_table');

		foreach ($updateActions as $updateAction) {
			if (isset($update[$updateAction]) && is_array($update[$updateAction])) {
				$tmpKeys[] = array_keys($update[$updateAction]);
			}
		}

		foreach ($removeActions as $removeAction) {
			if (isset($remove[$removeAction]) && is_array($remove[$removeAction])) {
				$tmpKeys[] = array_keys($remove[$removeAction]);
			}
		}

		$finalKeys = array();
		foreach ($tmpKeys as $keys) {
			foreach ($keys as $key) {
				$finalKeys[$key] = TRUE;
			}
		}
		return $finalKeys;
	}
}

