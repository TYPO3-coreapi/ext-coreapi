<?php
namespace Etobi\CoreAPI\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Georg Ringer <georg.ringer@cyberhouse.at>
 *  (c) 2014 Stefano Kowalke <blueduck@gmx.net>
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
 ***************************************************************/
use InvalidArgumentException;
use TYPO3\CMS\Core\Cache\Cache;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DB API service
 *
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @package Etobi\CoreAPI\Service\SiteApiService
 */
class DatabaseApiService {
	const ACTION_UPDATE_CLEAR_TABLE = 1;
	const ACTION_UPDATE_ADD = 2;
	const ACTION_UPDATE_CHANGE = 3;
	const ACTION_UPDATE_CREATE_TABLE = 4;
	const ACTION_REMOVE_CHANGE = 5;
	const ACTION_REMOVE_DROP = 6;
	const ACTION_REMOVE_CHANGE_TABLE = 7;
	const ACTION_REMOVE_DROP_TABLE = 8;

	/**
	 * @var \TYPO3\CMS\Install\Service\SqlSchemaMigrationService $schemaMigrationService Instance of SQL handler
	 */
	protected $schemaMigrationService;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	protected $objectManager;

	/**
	 * Inject the ObjectManager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

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
	public function databaseCompare($actions) {
		$errors = array();

		$test = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', 'Etobi\\CoreAPI\\Service\\DatabaseApiService');
		$availableActions = array_flip($test->getConstants());

		if (empty($actions)) {
			throw new InvalidArgumentException('No compare modes defined');
		}

		$allowedActions = array();
		$actionSplit = $this->trimExplode($actions);
		foreach ($actionSplit as $split) {
			if (!isset($availableActions[$split])) {
				throw new InvalidArgumentException(sprintf('Action "%s" is not available!', $split));
			}
			$allowedActions[$split] = 1;
		}

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
	 * Get all available actions.
	 *
	 * @return array
	 */
	public function databaseCompareAvailableActions() {
		$availableActions = array_flip($this->objectManager->get('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', 'Etobi\\CoreAPI\\Service\\DatabaseApiService')->getConstants());

		foreach ($availableActions as $number => $action) {
			if (!$this->isFirstPartOfString($action, 'ACTION_')) {
				unset($availableActions[$number]);
			}
		}
		return $availableActions;
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

	/**
	 * Wrapper around GeneralUtility::trimExplode
	 *
	 * @param string $values The values to explode
	 *
	 * @return array
	 */
	protected function trimExplode($values) {
		return GeneralUtility::trimExplode(',', $values);
	}

	/**
	 * Wrapper around GeneralUtility::getUrl()
	 * @param $url
	 *
	 * @return mixed
	 */
	protected function getUrl($url) {
		return GeneralUtility::getUrl($url);
	}

	/**
	 * Wrapper around Cache::getDatabaseTableDefinitions()
	 *
	 * @return string
	 */
	protected function getDatabaseTableDefinitionsFromCache() {
		return Cache::getDatabaseTableDefinitions();
	}

	/**
	 * Wrapper around \TYPO3\CMS\Core\Category\CategoryRegistry::getInstance()
	 *
	 * @return \TYPO3\CMS\Core\Category\CategoryRegistry
	 */
	protected function getCategoryRegistry() {
		return \TYPO3\CMS\Core\Category\CategoryRegistry::getInstance();
	}

	/**
	 * Wrapper around GeneralUtility::isFirstPartOfStr()
	 *
	 * @param string $str
	 * @param string $partStr
	 *
	 * @return bool
	 */
	protected function isFirstPartOfString($str, $partStr) {
		return GeneralUtility::isFirstPartOfStr($str, $partStr);
	}
}
