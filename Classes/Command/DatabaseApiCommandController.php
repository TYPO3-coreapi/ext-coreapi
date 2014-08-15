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
class DatabaseApiCommandController extends CommandController {

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
	 * @var \Etobi\CoreAPI\Service\DatabaseApiService $databaseApiService
	 */
	protected $databaseApiService;

	/**
	 * Injects the DatabaseApiService object
	 *
	 * @param \Etobi\CoreAPI\Service\DatabaseApiService $databaseApiService
	 *
	 * @return void
	 */
	public function injectDatabaseApiService(\Etobi\CoreAPI\Service\DatabaseApiService $databaseApiService) {
		$this->databaseApiService = $databaseApiService;
	}

	/**
	 * Database compare.
	 * Leave the argument 'actions' empty or use "help" to see the available ones
	 *
	 * @param string $actions List of actions which will be executed
	 * @param bool   $dry
	 */
	public function databaseCompareCommand($actions = '', $dry = FALSE) {
		if ($actions === 'help' || strlen($actions) === 0) {
			$actions = $this->databaseApiService->databaseCompareAvailableActions();
			foreach ($actions as $number => $action) {
				$this->outputLine('  - ' . $action . ' => ' . $number);
			}
			$this->quit();
		}

		$result = $this->databaseApiService->databaseCompare($actions, $dry);

		if ($dry) {
			$this->outputLine('DB compare would execute the following queries:');
			foreach($result as $key => $set) {
				$this->outputLine(sprintf('### Action: %s ###', $key));
				$this->outputLine('===================================');
				$this->logger->info(sprintf('### Action: %s ###', $key));
				$this->logger->info('===================================');
				foreach($set as $line) {
					$this->outputLine($line);
					$this->logger->info($line);
				}
				$this->outputLine(LF);
			}
			$this->logger->info('DB compare executed in dry mode');
		} else {
			if (empty($result)) {
				$message = 'DB has been compared';
				$this->outputLine($message);
				$this->logger->info($message);
			} else {
				$message = sprintf('DB could not be compared, Error(s): %s', array(LF . implode(LF, $result)));
				$this->outputLine($message);
				$this->logger->error($message);
				$this->quit(1);
			}
		}
	}
}