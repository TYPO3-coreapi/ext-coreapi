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
		// Create a fake admin user
		$adminUser = new t3lib_beUserAuth();
		$adminUser->user['uid'] = $GLOBALS['BE_USER']->user['uid'];
		$adminUser->user['username'] = '_CLI_lowlevel';
		$adminUser->user['admin'] = 1;
		$adminUser->workspace = 0;

		$this->tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$this->tce->start(Array(), Array());
		$this->tce->start(Array(), Array(), $adminUser);
	}

	/**
	 * Clear all caches
	 */
	public function clearAllCaches() {
		if (version_compare(TYPO3_version, '6.0.0', '<')) {
			t3lib_extMgm::removeCacheFiles();
		}
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
		if (version_compare(TYPO3_version, '6.0.0', '<')) {
			t3lib_extMgm::removeCacheFiles();
		}
		$this->tce->clear_cacheCmd('temp_cached');
	}

	/**
	 * Clear all caches except the page cache.
	 * This is especially useful on big sites when you can't just drop the page cache
	 *
	 * @return array with list of cleared caches
	 */
	public function clearAllExceptPageCache() {
		$out = array();
		$cacheKeys = array_keys($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
		$ignoredCaches = array('cache_pages', 'cache_pagesection');

		$toBeFlushed = array_diff($cacheKeys, $ignoredCaches);

		/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
		$cacheManager = $GLOBALS['typo3CacheManager'];
		foreach($cacheKeys as $cacheKey) {
			if ($cacheManager->hasCache($cacheKey)) {
				$out[] = $cacheKey;
				$singleCache = $cacheManager->getCache($cacheKey);
				$singleCache->flush();
			}
		}

		return $toBeFlushed;
	}
}

?>
