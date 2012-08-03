<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Georg Ringer (georg.ringer@cyberhouse.at)
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
 * Starts all due tasks, used by the command line interface
 * This script must be included by the "CLI module dispatcher"
 *
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Cli_Dispatcher {

	const MAXIMUM_LINE_LENGTH = 79;

	/**
	 * @var string service
	 */
	protected $service = '';

	/**
	 * @var string command
	 */
	protected $command = '';

	/**
	 * Constructor with basic checks
	 *
	 * @return void
	 */
	public function __construct() {
		if (!isset($_SERVER['argv'][1])) {
			$this->error('No service defined');
		}

		$split = explode(':', $_SERVER['argv'][1]);
		if (count($split) === 1) {
			$this->error('CLI calls need to be like coreapi cache:clearallcaches');
		} elseif (count($split) !== 2) {
			$this->error('Only one : is allowed in first argument');
		}

		$this->service = strtolower($split[0]);
		$this->command = strtolower($split[1]);
	}


	public function start() {
		try {
			switch ($this->service) {
				case 'cache':
					$this->cacheApi();
					break;
				case 'database':
					$this->databaseApi();
					break;
				case 'extension':
					$this->extensionApi();
					break;
				case 'site':
					$this->siteApi();
					break;
				default:
					$this->error(sprintf('Service "%s" not supported', $this->service));
			}
		} catch (Exception $e) {
			$errorMessage = sprintf('ERROR: Error in service "%s" and command "%s"": %s!', $this->service, $this->command, $e->getMessage());
			$this->outputLine($errorMessage);
		}
	}

	protected function cacheApi() {
		$cacheApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_CacheApiService');
		$cacheApiService->initializeObject();

		switch ($this->command) {
			case 'clearallcaches':
				$cacheApiService->clearAllCaches();
				$this->outputLine('All caches cleared');
				break;
			case 'clearconfigurationcache':
				$cacheApiService->clearConfigurationCache();
				$this->outputLine('Configuration cache cleared');
				break;
			case 'clearpagecache':
				$cacheApiService->clearPageCache();
				$this->outputLine('Page cache cleared');
				break;
			default:
				$this->error(sprintf('Command "%s" not supported', $this->command));
		}
	}

	protected function databaseApi() {
		$databaseApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_DatabaseApiService');

		switch ($this->command) {
			case 'databasecompare':
				if ($_SERVER['argv'][2] === 'help') {
					$actions = $databaseApiService->databaseCompareAvailableActions();
					$this->outputTable($actions);
				} else {
					$databaseApiService->databaseCompare($_SERVER['argv'][2]);
				}
				break;
			default:
				$this->error(sprintf('Command "%s" not supported', $this->command));
		}
	}

		/**
	 * Implement the extensionapi service commands
	 *
	 * @return void
	 */
	protected function extensionApi() {
		$extensionApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_ExtensionApiService');

		switch ($this->command) {
			case 'info':
					// @todo: remove duplicated code
				$data = $extensionApiService->getExtensionInformation($_SERVER['argv'][2]);
				$this->outputLine('');
				$this->outputLine('EXTENSION "%s": %s %s', array(strtoupper($_SERVER['argv'][2]), $data['em_conf']['version'], $data['em_conf']['state']));
				$this->outputLine(str_repeat('-', self::MAXIMUM_LINE_LENGTH));

				$outputInformation = array();
				$outputInformation['is installed'] = ($data['is_installed'] ? 'yes' : 'no');
				foreach($data['em_conf'] as $emConfKey => $emConfValue) {
						// Skip empty properties
					if (empty($emConfValue)) {
						continue;
					}
						// Skip properties which are already handled
					if ($emConfKey === 'title' || $emConfKey === 'version' || $emConfKey === 'state') {
						continue;
					}
					$outputInformation[$emConfKey] = $emConfValue;
				}

				foreach ($outputInformation as $outputKey => $outputValue) {
					$description = '';
					if (is_array($outputValue)) {
						foreach ($outputValue as $additionalKey => $additionalValue) {
							if (is_array($additionalValue)) {

								if (empty($additionalValue))  {
									continue;
								}
								$description .= LF . str_repeat(' ', 28) . $additionalKey;
								$description .= LF;
								foreach ($additionalValue as $ak => $av) {
									$description .= str_repeat(' ', 30) . $ak . ': ' . $av . LF;
								}
							} else {
								$description .= LF . str_repeat(' ', 28) . $additionalKey . ': '. $additionalValue;
							}
						}
					} else {
						$description = wordwrap($outputValue, self::MAXIMUM_LINE_LENGTH - 28, PHP_EOL . str_repeat(' ', 28), TRUE);
					}
					$this->outputLine('%-2s%-25s %s', array(' ', $outputKey, $description));
				}
				break;
			case 'updatelist':
				$extensionApiService->updateMirrors();
				break;
			case 'listinstalled':
				$extensions = $extensionApiService->getInstalledExtensions($_SERVER['argv'][2]);
				$out = array();

				foreach($extensions as $key => $details) {
					$title = $key . ' - ' . $details['version'] . '/' . $details['state'];
					$out[$title] = $details['title'];
				}
				$this->outputTable($out);
				break;
			default:
				$this->error(sprintf('Command "%s" not supported', $this->command));
		}

	}

	/**
	 * Implement the siteapi service commands
	 *
	 * @return void
	 */
	protected function siteApi() {
		$siteApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_SiteApiService');

		switch ($this->command) {
			case 'info':
				$infos = $siteApiService->getSiteInfo();
				$this->outputTable($infos);
				break;
			case 'createsysnews':
				$siteApiService->createSysNews($_SERVER['argv'][2], $_SERVER['argv'][3]);
				break;
			default:
				$this->error(sprintf('Command "%s" not supported', $this->command));
		}
	}

	/**
	 * Output a single line
	 *
	 * @param string $text text
	 * @param array $arguments optional arguments
	 * @return void
	 */
	protected function outputLine($text, array $arguments = array()) {
		if ($arguments !== array()) {
			$text = vsprintf($text, $arguments);
		}
		echo $text . PHP_EOL;
	}

	/**
	 * Output a whole table, maximum 2 cols
	 *
	 * @param array $input input table
	 * @return void
	 */
	protected function outputTable(array $input) {
		$this->outputLine(str_repeat('-', self::MAXIMUM_LINE_LENGTH));
		foreach($input as $key => $value) {
			$line = wordwrap($value, self::MAXIMUM_LINE_LENGTH - 43, PHP_EOL . str_repeat(' ', 43), TRUE);
			$this->outputLine('%-2s%-40s %s', array(' ', $key, $line));
		}
		$this->outputLine(str_repeat('-', self::MAXIMUM_LINE_LENGTH));
	}

	/**
	 * End call
	 *
	 * @param string $message Error message
	 * @return void
	 */
	protected function error($message) {
		die('ERROR: ' . $message);
	}

}

if ((TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI) && basename(PATH_thisScript) == 'cli_dispatch.phpsh') {
	$dispatcher = t3lib_div::makeInstance('Tx_Coreapi_Cli_Dispatcher');
	$dispatcher->start();
} else {
	die('This script must be included by the "CLI module dispatcher"');
}

?>