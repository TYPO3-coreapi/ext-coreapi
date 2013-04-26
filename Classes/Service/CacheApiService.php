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
 * Cache API service
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Service_CacheApiService {

	/**
	 * @var t3lib_TCEmain
	 */
	protected $tce;

	/**
	 *
	 */
	public function initializeObject() {
		$this->tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$this->tce->start(Array(), Array());
	}

	/**
	 * Clear all caches
	 */
	public function clearAllCaches() {
		$this->tce->clear_cacheCmd('all');
	}

	/**
	 *
	 */
	public function clearPageCache() {
		$this->tce->clear_cacheCmd('pages');
	}

	/**
	 *
	 */
	public function clearConfigurationCache() {        
		if (verison_compare(TYPO3_version, '6.0.0', '>=')) {
	   		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::removeCacheFiles();
		} else {
			$this->tce->clear_cacheCmd('temp_cached');
		}
		
	}

}

?>