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
 * Extension API service
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Service_ExtensionApiService {
/*
	extension                   Provides some basic information on the site's extension status
	extension:info              Fetches the latest (or provided) version of an extension from TER
	extension:list              Lists all available extensions of a site
	extension:search            Searches for an extension in the TER
	extension:fetch             Fetches the latest (or provided) version of an extension from TER
	extension:install           Installs the latest (or provided) version of an extension
	extension:uninstall         Uninstalls an extension
	extension:refresh           Refreshes the local cache of all extensions available in TER
*/

	public function getInstalledExtensions($type = '') {
		$extensions = $GLOBALS['TYPO3_LOADED_EXT'];

		$list = array();
		foreach ($extensions as $key => $extension) {
			if (!empty($type) && $type !== $extension['type']) {
				continue;
			}

			include_once(t3lib_extMgm::extPath($key) . 'ext_emconf.php');
			$list[$key] = $EM_CONF[''];
		}

		ksort($list);

		return $list;
	}

	/**
	 * Update the mirrors, using the scheduler task of EXT:em
	 *
	 * @return void
	 * @see tx_em_Tasks_UpdateExtensionList
	 */
	public function updateMirrors() {
		/** @var $emTask tx_em_Tasks_UpdateExtensionList */
		$emTask = t3lib_div::makeInstance('tx_em_Tasks_UpdateExtensionList');
		$emTask->execute();
	}
}

?>