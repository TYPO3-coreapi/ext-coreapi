<?php
/***************************************************************
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
 ***************************************************************/

/**
 * Site API service
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Service_SiteApiService {

	/**
	 * Get some basic site information
	 *
	 * @return array
	 */
	public function getSiteInfo() {
		$data = array(
			'TYPO3 version' => TYPO3_version,
			'Site name' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
		);

		$this->getDiskUsage($data);
		$this->getDatabaseInformation($data);

		return $data;
	}

	/**
	 * Create a sys news record
	 *
	 * @param string $header header
	 * @param string $text text
	 * @return void
	 */
	public function createSysNews($header, $text) {
		if (strlen($header) === 0) {
			throw new InvalidArgumentException('No header given');
		}
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_news', array(
			'title' => $header,
			'content' => $text,
			'tstamp' => $GLOBALS['EXEC_TIME'],
			'crdate' => $GLOBALS['EXEC_TIME'],
		));
	}

	/**
	 * Get disku usage
	 *
	 * @author Claus Due <claus@wildside.dk>, Wildside A/S
	 * @param array $data
	 * @return void
	 */
	protected function getDiskUsage(&$data) {
		if (TYPO3_OS !== 'WIN') {
			$data['Combined disk usage'] = trim(array_shift(explode("\t", shell_exec('du -sh ' . PATH_site))));
		}
	}

	/**
	 * Get database size
	 *
	 * @author Claus Due <claus@wildside.dk>, Wildside A/S
	 * @param array $data
	 * @return void
	 */
	protected function getDatabaseInformation(&$data) {
		$databaseSizeResult = $GLOBALS['TYPO3_DB']->sql_query("SELECT SUM( data_length + index_length ) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = '" . TYPO3_db . "'");
		$databaseSizeRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($databaseSizeResult);
		$databaseSize = array_pop($databaseSizeRow);
		$value = number_format($databaseSize, ($databaseSize > 10 ? 0 : 1)) . 'M';
		$data['Database size'] = $value;
	}

}

?>