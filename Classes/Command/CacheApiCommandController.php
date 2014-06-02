<?php
namespace Etobi\CoreAPI\Command;

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
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * API Command Controller
 *
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @package Etobi\CoreAPI\Service\SiteApiService
 */
class CacheApiCommandController extends CommandController {

	/**
	 * Clear all caches.
	 *
	 * @return void
	 */
	public function clearAllCachesCommand() {
		$service = $this->getService();
		$service->clearAllCaches();

		$this->outputLine('All caches have been cleared.');
	}

	/**
	 * Clear configuration cache (temp_CACHED_..).
	 *
	 * @return void
	 */
	public function clearConfigurationCacheCommand() {
		$service = $this->getService();
		$service->clearConfigurationCache();

		$this->outputLine('Configuration cache has been cleared.');
	}

	/**
	 * Clear page cache.
	 *
	 * @return void
	 */
	public function clearPageCacheCommand() {
		$service = $this->getService();
		$service->clearPageCache();

		$this->outputLine('Page cache has been cleared.');
	}

	/**
	 * Clear all caches except the page cache.
	 * This is especially useful on big sites when you can't just drop the page cache.
	 *
	 * @return void
	 */
	public function clearAllExceptPageCacheCommand() {
		$service = $this->getService();
		$clearedCaches = $service->clearAllExceptPageCache();

		$this->outputLine('Cleared caches: ' . implode(', ', $clearedCaches));
	}

	/**
	 * Returns the service object.
	 *
	 * @return \Etobi\CoreAPI\Service\CacheApiService object
	 */
	private function getService() {
		return $this->objectManager->get('Etobi\\CoreAPI\\Service\\CacheApiService');
	}
}