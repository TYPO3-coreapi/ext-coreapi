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
	 * Database compare.
	 *
	 * @param string $actions comma separated list of IDs
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

		if ($allowedActions[self::ACTION_UPDATE_CLEAR_TABLE] == 1) {
			$results['update_clear_table'] = $addCreateChange['clear_table'];
		}

		if ($allowedActions[self::ACTION_UPDATE_ADD] == 1) {
			$results['update_add'] = $addCreateChange['add'];
		}

		if ($allowedActions[self::ACTION_UPDATE_CHANGE] == 1) {
			$results['update_change'] = $addCreateChange['change'];
		}

		if ($allowedActions[self::ACTION_UPDATE_CREATE_TABLE] == 1) {
			$results['update_create_table'] = $addCreateChange['create_table'];
		}

		if ($allowedActions[self::ACTION_REMOVE_CHANGE] == 1) {
			$results['remove_change'] = $dropRemove['change'];
		}

		if ($allowedActions[self::ACTION_REMOVE_DROP] == 1) {
			$results['remove_drop'] = $dropRemove['drop'];
		}

		if ($allowedActions[self::ACTION_REMOVE_CHANGE_TABLE] == 1) {
			$results['remove_change_table'] = $dropRemove['change_table'];
		}

		if ($allowedActions[self::ACTION_REMOVE_DROP_TABLE] == 1) {
			$results['remove_drop_table'] = $dropRemove['drop_table'];
		}

		foreach ($results as $key => $resultSet) {
			if (!empty($resultSet)) {
				$errors[$key] = $resultSet;
			}
		}

		return $errors;
	}
}

