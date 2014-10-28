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
	 * Database compare.
	 *
	 * @param string $actions comma separated list of IDs
	 *
	 * @throws InvalidArgumentException
	 * @return array
	 */
	public function compare($actions) {
		$errors = array();
		$results = array();

		$allowedActions = $this->getAllowedActions($actions);

		$expectedSchema = $this->sqlExpectedSchemaService->getExpectedDatabaseSchema();
		$currentSchema = $this->schemaMigrationService->getFieldDefinitions_database();

		$addCreateChange = $this->schemaMigrationService->getDatabaseExtra($expectedSchema, $currentSchema);
		$addCreateChange = $this->schemaMigrationService->getUpdateSuggestions($addCreateChange);

		$dropRemove = $this->schemaMigrationService->getDatabaseExtra($currentSchema, $expectedSchema);
		$dropRemove = $this->schemaMigrationService->getUpdateSuggestions($dropRemove, 'remove');

		$allowedRequestKeys = $this->getRequestKeys($addCreateChange, $dropRemove);

		if ($allowedActions[self::ACTION_UPDATE_CLEAR_TABLE] == 1) {
			$results[] = $this->schemaMigrationService->performUpdateQueries($addCreateChange['clear_table'], $allowedRequestKeys);
		}

		if ($allowedActions[self::ACTION_UPDATE_ADD] == 1) {
			$results[] = $this->schemaMigrationService->performUpdateQueries($addCreateChange['add'], $allowedRequestKeys);
		}

		if ($allowedActions[self::ACTION_UPDATE_CHANGE] == 1) {
			$results[] = $this->schemaMigrationService->performUpdateQueries($addCreateChange['change'], $allowedRequestKeys);
		}

		if ($allowedActions[self::ACTION_UPDATE_CREATE_TABLE] == 1) {
			$results[] = $this->schemaMigrationService->performUpdateQueries($addCreateChange['create_table'], $allowedRequestKeys);
		}

		if ($allowedActions[self::ACTION_REMOVE_CHANGE] == 1) {
			$results[] = $this->schemaMigrationService->performUpdateQueries($dropRemove['change'], $allowedRequestKeys);
		}

		if ($allowedActions[self::ACTION_REMOVE_DROP] == 1) {
			$results[] = $this->schemaMigrationService->performUpdateQueries($dropRemove['drop'], $allowedRequestKeys);
		}

		if ($allowedActions[self::ACTION_REMOVE_CHANGE_TABLE] == 1) {
			$results[] = $this->schemaMigrationService->performUpdateQueries($dropRemove['change_table'], $allowedRequestKeys);
		}

		if ($allowedActions[self::ACTION_REMOVE_DROP_TABLE] == 1) {
			$results[] = $this->schemaMigrationService->performUpdateQueries($dropRemove['drop_table'], $allowedRequestKeys);
		}

		foreach ($results as $resultSet) {
			if (is_array($resultSet)) {
				foreach ($resultSet as $key => $errorMessage) {
					$errors[$key] = $errorMessage;
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

