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
	 * Inject the ObjectManager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
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
	 * Checks the given actions against the defined and allowed actions
	 *
	 * @param       $actions
	 * @param array $allowedActions
	 *
	 * @throws InvalidArgumentException
	 */
	protected function checkAvailableActions($actions, array &$allowedActions = array()) {
		$classReflection = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', 'Etobi\\CoreAPI\\Service\\DatabaseComparator');
		$availableActions = array_flip($classReflection->getConstants());

		if (empty($actions)) {
			throw new InvalidArgumentException('No compare modes defined');
		}

		$actionSplit = $this->trimExplode($actions);
		foreach ($actionSplit as $split) {
			if (!isset($availableActions[$split])) {
				throw new InvalidArgumentException(sprintf('Action "%s" is not available!', $split));
			}
			$allowedActions[$split] = 1;
		}
	}
}

