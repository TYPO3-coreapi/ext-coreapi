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
	 * @var \TYPO3\CMS\Core\Log\LogManager $logManager
	 */
	protected $logManager;

	/**
	 * @var \TYPO3\CMS\Core\Log\Logger $logger
	 */
	protected $logger;

	/**
	 * @param \TYPO3\CMS\Core\Log\LogManager $logManager
	 *
	 * @return void
	 */
	public function injectLogManager(\TYPO3\CMS\Core\Log\LogManager $logManager) {
		$this->logManager = $logManager;
	}

	/**
	 * Initialize the object
	 */
	public function initializeObject() {
		$this->logger = $this->objectManager->get('\TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
	}

	/**
	 * @var \Etobi\CoreAPI\Service\CacheApiService
	 */
	protected $cacheApiService;

	/**
	 * Inject the CacheApiService
	 *
	 * @param \Etobi\CoreAPI\Service\CacheApiService $cacheApiService
	 */
	public function injectCacheApiService(\Etobi\CoreAPI\Service\CacheApiService $cacheApiService) {
		$this->cacheApiService = $cacheApiService;
	}

	/**
	 * Clear all caches.
	 * If hard, cache will be cleared in a more straightforward approach and the according backend hooks are not executed.
	 *
	 * @param boolean $hard
	 * @return void
	 */
	public function clearAllCachesCommand($hard = false) {
		$this->cacheApiService->clearAllCaches($hard);
		$message = 'All caches have been cleared%s.';
		$this->logger->info(sprintf($message, $hard ? ' hard' : ''));
		$this->outputLine($message, $hard ? array(' hard') : array(''));
	}

	/**
	 * Clear system cache.
	 *
	 * @return void
	 */
	public function clearSystemCacheCommand() {
		$this->cacheApiService->clearSystemCache();
		$message = 'System cache has been cleared';
		$this->logger->info($message);
		$this->outputLine($message);
	}

	/**
	 * Clears the opcode cache.
	 *
	 * @param string|NULL $fileAbsPath The file as absolute path to be cleared
	 *                                 or NULL to clear completely.
	 *
	 * @return void
	 */
	public function clearAllActiveOpcodeCacheCommand($fileAbsPath = NULL) {
		$this->cacheApiService->clearAllActiveOpcodeCache($fileAbsPath);

		if ($fileAbsPath !== NULL) {
			$message = sprintf('The opcode cache for the file %s has been cleared', $fileAbsPath);
			$this->outputLine($message);
			$this->logger->info($message);
		} else {
			$message = 'The complete opcode cache has been cleared';
			$this->outputLine($message);
			$this->logger->info($message);
		}
	}

	/**
	 * Clear configuration cache (temp_CACHED_..).
	 *
	 * @return void
	 */
	public function clearConfigurationCacheCommand() {
		$this->cacheApiService->clearConfigurationCache();
		$message = 'Configuration cache has been cleared.';
		$this->logger->info($message);
		$this->outputLine($message);
	}

	/**
	 * Clear page cache.
	 *
	 * @return void
	 */
	public function clearPageCacheCommand() {
		$this->cacheApiService->clearPageCache();
		$message = 'Page cache has been cleared.';
		$this->logger->info($message);
		$this->outputLine($message);
	}

	/**
	 * Clear all caches except the page cache.
	 * This is especially useful on big sites when you can't just drop the page cache.
	 *
	 * @return void
	 */
	public function clearAllExceptPageCacheCommand() {
		$clearedCaches = $this->cacheApiService->clearAllExceptPageCache();
		$message = 'Cleared caches: ' . implode(', ', $clearedCaches);
		$this->logger->info($message);
		$this->outputLine($message);
	}
}
