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
 * @author		Georg Ringer <georg.ringer@cyberhouse.at>
 * @package		TYPO3
 * @subpackage	tx_coreapi
 */
class Tx_Coreapi_Cli_Dispatcher {

	const MAXIMUM_LINE_LENGTH = 79;

	protected $service = '';
	protected $command = '';


	public function __construct() {
		if (!isset($_SERVER['argv'][1])) {
			die('ERROR: No service defined');
		}
		$this->service = strtolower($_SERVER['argv'][1]);

		if (!isset($_SERVER['argv'][2])) {
			die('ERROR: No command defined');
		}
		$this->command = strtolower($_SERVER['argv'][2]);
	}


	public function start() {
		try {
			switch ($this->service) {
				case 'databaseapi':
					$this->databaseApi();
					break;
				case 'extensionapi':
					$this->extensionApi();
					break;
				case 'siteapi':
					$this->siteApi();
					break;
				default:
					die(sprintf('ERROR: Service "%s" not supported', $this->service));
			}
		} catch (Exception $e) {
			$errorMessage = sprintf('ERROR: Error in service "%s" and command "%s"": %s!', $this->service, $this->command, $e->getMessage());
			$this->outputLine($errorMessage);
		}
	}

	protected function databaseApi() {
		$databaseApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_DatabaseApiService');
		switch ($this->command) {
			case 'databasecompare':
				// todo
				break;
			default:
				die(sprintf('ERROR: Command "%s" not supported', $this->command));
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
				$data = $extensionApiService->getExtensionInformation($_SERVER['argv'][3]);
				$this->outputLine('');
				$this->outputLine('EXTENSION "%s": %s %s', array(strtoupper($_SERVER['argv'][3]), $data['em_conf']['version'], $data['em_conf']['state']));
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
				$extensions = $extensionApiService->getInstalledExtensions($_SERVER['argv'][3]);
				$out = array();

				foreach($extensions as $key => $details) {
					$title = $key . ' - ' . $details['version'] . '/' . $details['state'];
					$out[$title] = $details['title'];
				}
				$this->outputTable($out);
				break;
			default:
				die(sprintf('ERROR: Command "%s" not supported', $this->command));
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
				$siteApiService->createSysNews($_SERVER['argv'][3], $_SERVER['argv'][4]);
				break;
			default:
				die(sprintf('ERROR: Command "%s" not supported', $this->command));
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
}


if ((TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI) && basename(PATH_thisScript) == 'cli_dispatch.phpsh') {
	$dispatcher = t3lib_div::makeInstance('Tx_Coreapi_Cli_Dispatcher');
	$dispatcher->start();
} else {
	die('This script must be included by the "CLI module dispatcher"');
}

?>