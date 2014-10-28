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
use TYPO3\CMS\Core\Cache\Cache;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Service\SqlExpectedSchemaService;

/**
 * Class DatabaseCompareAbstract
 * 
 * @package Etobi\CoreAPI\Service
 * @author  Stefano Kowalke <blueduck@gmx.net>
 */
abstract class DatabaseComparator {
	const ACTION_UPDATE_CLEAR_TABLE = 1;
	const ACTION_UPDATE_ADD = 2;
	const ACTION_UPDATE_CHANGE = 3;
	const ACTION_UPDATE_CREATE_TABLE = 4;
	const ACTION_REMOVE_CHANGE = 5;
	const ACTION_REMOVE_DROP = 6;
	const ACTION_REMOVE_CHANGE_TABLE = 7;
	const ACTION_REMOVE_DROP_TABLE = 8;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Install\Service\SqlSchemaMigrationService $schemaMigrationService Instance of SQL handler
	 */
	protected $schemaMigrationService;

	/**
	 * @var \TYPO3\CMS\Install\Service\SqlExpectedSchemaService
	 */
	protected $sqlExpectedSchemaService;

	/**
	 * Inject the ObjectManager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \TYPO3\CMS\Install\Service\SqlExpectedSchemaService $sqlExpectedSchemaService
	 */
	public function injectSqlExpectedSchemaService(SqlExpectedSchemaService $sqlExpectedSchemaService) {
		$this->sqlExpectedSchemaService = $sqlExpectedSchemaService;
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
	 * @param $actions
	 *
	 * @return mixed
	 */
	public final function databaseCompare($actions) {
		return $this->compare($actions);
	}

	protected abstract function compare($action);

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
	 * Reflect, checks and return the allowed actions
	 *
	 * @param string $actions comma separated list of IDs
	 * @return array
	 */
	protected function getAllowedActions($actions) {
		if (empty($actions)) {
			throw new InvalidArgumentException('No compare modes defined');
		}

		$allowedActions = array();
		$availableActions = array_flip(
			$this->objectManager->get(
				'TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection',
				'Etobi\\CoreAPI\\Service\\DatabaseComparator'
			)->getConstants()
		);

		$actions = $this->trimExplode($actions);
		foreach ($actions as $action) {
			if (!isset($availableActions[$action])) {
				throw new InvalidArgumentException(sprintf('Action "%s" is not available!', $action));
			}
			$allowedActions[$action] = 1;
		}

		return $allowedActions;
	}
}

