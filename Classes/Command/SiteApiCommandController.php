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
 * Site API Command Controller
 *
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @package Etobi\CoreAPI\Service\SiteApiService
 */
class SiteApiCommandController extends CommandController {

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
	 * @var \Etobi\CoreAPI\Service\SiteApiService
	 */
	protected $siteApiService;

	/**
	 * Inject the SiteApiService
	 *
	 * @param \Etobi\CoreAPI\Service\SiteApiService $siteApiService
	 *
	 * @return void
	 */
	public function injectSiteApiService(\Etobi\CoreAPI\Service\SiteApiService $siteApiService) {
		$this->siteApiService = $siteApiService;
	}

	/**
	 * Basic information about the system.
	 *
	 * @return void
	 */
	public function infoCommand() {
		$data = $this->siteApiService->getSiteInfo();

		foreach ($data as $key => $value) {
			$line = wordwrap($value, self::MAXIMUM_LINE_LENGTH - 43, PHP_EOL . str_repeat(' ', 43), TRUE);
			$this->outputLine('%-2s%-40s %s', array(' ', $key, $line));
		}

		$this->logger->info('siteApi:info executes successfully.');
	}

	/**
	 * Sys news record is displayed at the login page.
	 *
	 * @param string $header Header text
	 * @param string $text   Basic text
	 *
	 * @return void
	 */
	public function createSysNewsCommand($header, $text = '') {
		$result = FALSE;

		try {
			$result = $this->siteApiService->createSysNews($header, $text);
		} catch (\Exception $e) {
			$this->outputLine($e->getMessage());
			$this->quit(1);
		}

		if ($result) {
			$this->outputLine('News entry successfully created.');
		} else {
			$this->outputLine('News entry NOT created.');
			$this->quit(1);
		}
	}
}
