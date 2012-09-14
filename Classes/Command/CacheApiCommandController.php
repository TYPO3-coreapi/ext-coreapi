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
 * API Command Controller
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Command_CacheApiCommandController extends Tx_Extbase_MVC_Controller_CommandController {

	/**
	 * Clear all caches
	 *
	 * @return void
	 */
	public function clearAllCachesCommand() {
		/** @var $service Tx_Coreapi_Service_CacheApiService */
		$service = $this->objectManager->get('Tx_Coreapi_Service_CacheApiService');
		$service->clearAllCaches();

		$this->outputLine('All caches have been cleared.');
	}

	/**
	 * Clear configuration cache (temp_CACHED_..)
	 *
	 * @return void
	 */
	public function clearConfigurationCacheCommand() {
		/** @var $service Tx_Coreapi_Service_CacheApiService */
		$service = $this->objectManager->get('Tx_Coreapi_Service_CacheApiService');
		$service->clearConfigurationCache();

		$this->outputLine('Configuration cache has been cleared.');
	}

	/**
	 * Clear page cache
	 *
	 * @return void
	 */
	public function clearPageCacheCommand() {
		/** @var $service Tx_Coreapi_Service_CacheApiService */
		$service = $this->objectManager->get('Tx_Coreapi_Service_CacheApiService');
		$service->clearPageCache();

		$this->outputLine('Page cache has been cleared.');
	}

}

?>