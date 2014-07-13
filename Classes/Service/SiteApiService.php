<?php
namespace Etobi\CoreAPI\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Georg Ringer <georg.ringer@cyberhouse.at>
 *  (c) 2014 Stefano Kowalke <blueduck@gmx.net>
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * Site API service
 *
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @author Claus Due <claus@wildside.dk>, Wildside A/S
 * @package Etobi\CoreAPI\Service\SiteApiService
 */
class SiteApiService {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * Inject the ObjectManager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 *
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Get some basic site information.
	 *
	 * @return array
	 */
	public function getSiteInfo() {
		$data = array(
			'TYPO3 version' => TYPO3_version,
			'Site name' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
		);

		$data = $this->getDiskUsage($data);
		$data = $this->getDatabaseSize($data);
		$data = $this->getCountOfExtensions($data);

		return $data;
	}

	/**
	 * Create a sys news record.
	 *
	 * @param string $header header
	 * @param string $text   text
	 *
	 * @throws InvalidArgumentException
	 * @return boolean
	 */
	public function createSysNews($header, $text) {
		if (strlen($header) === 0) {
			throw new InvalidArgumentException('No header given');
		}

		return $this->getDatabaseHandler()->exec_INSERTquery('sys_news', array(
			'title' => $header,
			'content' => $text,
			'tstamp' => $GLOBALS['EXEC_TIME'],
			'crdate' => $GLOBALS['EXEC_TIME'],
			'cruser_id' => $GLOBALS['BE_USER']->user['uid']
		));
	}

	/**
	 * Get disk usage.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function getDiskUsage($data) {
		if (TYPO3_OS !== 'WIN') {
			$data['Combined disk usage'] = trim(array_shift(explode("\t", shell_exec('du -sh ' . PATH_site))));
		}

		return $data;
	}

	/**
	 * Get database size.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function getDatabaseSize($data) {
		$databaseHandler = $this->getDatabaseHandler();
		$databaseSizeResult = $databaseHandler->sql_query("SELECT SUM( data_length + index_length ) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = '" . TYPO3_db . "'");
		$databaseSizeRow = $databaseHandler->sql_fetch_assoc($databaseSizeResult);
		$databaseSize = array_pop($databaseSizeRow);
		$value = number_format($databaseSize, ($databaseSize > 10 ? 0 : 1)) . 'M';
		$data['Database size'] = $value;

		return $data;
	}

	/**
	 * Get count of local installed extensions.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function getCountOfExtensions($data) {
		/** @var \Etobi\CoreAPI\Service\ExtensionApiService $extensionService */
		$extensionService = $this->objectManager->get('Etobi\\CoreAPI\\Service\\ExtensionApiService');
		$extensions = $extensionService->listExtensions('Local');
		$data['Count local installed extensions'] = count($extensions);

		return $data;
	}

	/**
	 * Returns the DatabaseConnection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseHandler() {
		return $GLOBALS['TYPO3_DB'];
	}
}
