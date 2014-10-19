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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DB API service
 *
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @package Etobi\CoreAPI\Service\SiteApiService
 */
class DatabaseApiService {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	protected $objectManager;

	/**
	 * @var \Etobi\CoreAPI\Service\DatabaseComparator $comparator
	 */
	protected $comparator = NULL;

	/**
	 * Inject the ObjectManager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Database compare.
	 *
	 * @param string  $actions comma separated list of IDs
	 * @param boolean $dry
	 *
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	public function databaseCompare($actions, $dry) {
		if ($dry) {
			$this->comparator = $this->objectManager->get('Etobi\\CoreAPI\\Service\\DatabaseCompareDry');
		} else {
			$this->comparator = $this->objectManager->get('Etobi\\CoreAPI\\Service\\DatabaseCompareReal');
		}

		try {
			$result = $this->comparator->databaseCompare($actions);
		} catch (\Exception $e) {
			throw new \InvalidArgumentException($e->getMessage());
		}

		return $result;
	}

	/**
	 * Get all available actions.
	 *
	 * @return array
	 */
	public function databaseCompareAvailableActions() {
		$availableActions = array_flip($this->objectManager->get('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', 'Etobi\\CoreAPI\\Service\\DatabaseComparator')->getConstants());

		foreach ($availableActions as $number => $action) {
			if (!$this->isFirstPartOfString($action, 'ACTION_')) {
				unset($availableActions[$number]);
			}
		}
		return $availableActions;
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
