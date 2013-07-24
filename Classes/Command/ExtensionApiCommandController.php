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
 * Extension API Command Controller
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Command_ExtensionApiCommandController extends Tx_Extbase_MVC_Controller_CommandController {

	/**
	 * Information about an extension
	 *
	 * @param string $key extension key
	 * @return void
	 */
	public function infoCommand($key) {
		$data = array();
		try {
			$data = $this->getMyService()->getExtensionInformation($key);
		} catch (Exception $e) {
			$this->outputLine($e->getMessage());
			$this->quit();
		}

		$this->outputLine('');
		$this->outputLine('EXTENSION "%s": %s %s', array(strtoupper($key), $data['em_conf']['version'], $data['em_conf']['state']));
		$this->outputLine(str_repeat('-', self::MAXIMUM_LINE_LENGTH));

		$outputInformation = array();
		$outputInformation['is installed'] = ($data['is_installed'] ? 'yes' : 'no');
		foreach ($data['em_conf'] as $emConfKey => $emConfValue) {
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

						if (empty($additionalValue)) {
							continue;
						}
						$description .= LF . str_repeat(' ', 28) . $additionalKey;
						$description .= LF;
						foreach ($additionalValue as $ak => $av) {
							$description .= str_repeat(' ', 30) . $ak . ': ' . $av . LF;
						}
					} else {
						$description .= LF . str_repeat(' ', 28) . $additionalKey . ': ' . $additionalValue;
					}
				}
			} else {
				$description = wordwrap($outputValue, self::MAXIMUM_LINE_LENGTH - 28, PHP_EOL . str_repeat(' ', 28), TRUE);
			}
			$this->outputLine('%-2s%-25s %s', array(' ', $outputKey, $description));
		}
	}

	/**
	 * List all installed extensions
	 *
	 * @param string $type Extension type, can either be L for local, S for system or G for global. Leave it empty for all
	 * @return void
	 */
	public function listInstalledCommand($type = '') {
		$type = strtoupper($type);
		if (!empty($type) && $type !== 'L' && $type !== 'G' && $type !== 'S') {
			$this->outputLine('Only "L", "S" and "G" are supported as type (or nothing)');
			$this->quit();
		}

		/** @var $extensions array */
		$extensions = $this->getMyService()->getInstalledExtensions($type);

		foreach ($extensions as $key => $details) {
			$title = $key . ' - ' . $details['version'] . '/' . $details['state'];
			$description = $details['title'];
			$description = wordwrap($description, self::MAXIMUM_LINE_LENGTH - 43, PHP_EOL . str_repeat(' ', 43), TRUE);
			$this->outputLine('%-2s%-40s %s', array(' ', $title, $description));
		}

		$this->outputLine('%-2s%-40s', array(' ', str_repeat('-', self::MAXIMUM_LINE_LENGTH - 3)));
		$this->outputLine('  Total: ' . count($extensions) . ' extensions');
	}

	/**
	 * Update list
	 *
	 * @return void
	 */
	public function updateListCommand() {
		$this->getMyService()->updateMirrors();

		$this->outputLine('Extension list has been updated.');
	}

	/**
	 * Install(activate) an extension
	 *
	 * @param string $key extension key
	 * @return void
	 */
	public function installCommand($key) {
		try {
			$data = $this->getMyService()->installExtension($key);
		} catch (Exception $e) {
			$this->outputLine($e->getMessage());
			$this->quit();
		}
		$this->outputLine(sprintf('Extension "%s" is now installed!', $key));
	}

	/**
	 * UnInstall(deactivate) an extension
	 *
	 * @param string $key extension key
	 * @return void
	 */
	public function uninstallCommand($key) {
		try {
			$data = $this->getMyService()->uninstallExtension($key);
		} catch (Exception $e) {
			$this->outputLine($e->getMessage());
			$this->quit();
		}
		$this->outputLine(sprintf('Extension "%s" is now uninstalled!', $key));
	}

	/**
	 * Configure an extension
	 *
	 * This command enables you to configure an extension.
	 *
	 * examples:
	 *
	 * [1] Using a standard formatted ini-file
	 * ./cli_dispatch.phpsh extbase extensionapi:configure rtehtmlarea --configfile=C:\rteconf.txt
	 *
	 * [2] Adding configuration settings directly on the command line
	 * ./cli_dispatch.phpsh extbase extensionapi:configure rtehtmlarea --settings="enableImages=1;allowStyleAttribute=0"
	 *
	 * [3] A combination of [1] and [2]
	 * ./cli_dispatch.phpsh extbase extensionapi:configure rtehtmlarea --configfile=C:\rteconf.txt --settings="enableImages=1;allowStyleAttribute=0"
	 *
	 * @param string $key extension key
	 * @param string $configfile path to file containing configuration settings. Must be formatted as a standard ini-file
	 * @param string $settings string containing configuration settings separated on the form "k1=v1;k2=v2;"
	 * @return void
	 */
	public function configureCommand($key, $configfile = '', $settings = '') {
		global $TYPO3_CONF_VARS;
		try {
			$conf = array();
			if (is_file($configfile)) {
				$conf = parse_ini_file($configfile);
			}

			if (strlen($settings)) {
				$arr = explode(';', $settings);
				foreach ($arr as $v) {
					if (strpos($v, '=') === FALSE) {
						throw new InvalidArgumentException(sprintf('Ill-formed setting "%s"!', $v));
					}
					$parts = t3lib_div::trimExplode('=', $v, FALSE, 2);
					if (!empty($parts[0])) {
						$conf[$parts[0]] = $parts[1];
					}
				}
			}

			if (empty($conf)) {
				throw new InvalidArgumentException(sprintf('No configuration settings!', $key));
			}
			$data = $this->getMyService()->configureExtension($key, $conf);

		} catch (Exception $e) {
			$this->outputLine($e->getMessage());
			$this->quit();
		}
		$this->outputLine(sprintf('Extension "%s" has been configured!', $key));
	}

	/**
	 * Fetch an extension from TER
	 *
	 * @param string $key extension key
	 * @param string $version the exact version of the extension, otherwise the latest will be picked
	 * @param string $location where to put the extension. S = typo3/sysext, G = typo3/ext, L = typo3conf/ext
	 * @param bool $overwrite overwrite the extension if it already exists
	 * @param string $mirror mirror to fetch the extension from, otherwise a random mirror will be selected
	 * @return void
	 */
	public function fetchCommand($key, $version = '', $location = 'L', $overwrite = FALSE, $mirror = '') {
		try {
			$data = $this->getMyService()->fetchExtension($key, $version, $location, $overwrite, $mirror);
			$this->outputLine(sprintf('Extension "%s" version %s has been fetched from repository!', $data['extKey'], $data['version']));
		} catch (Exception $e) {
			$this->outputLine($e->getMessage());
			$this->quit();
		}
	}

	/**
	 * Import extension from file
	 *
	 * @param string $file path to t3x file
	 * @param string $location where to import the extension. S = typo3/sysext, G = typo3/ext, L = typo3conf/ext
	 * @param boolean $overwrite overwrite the extension if it already exists
	 * @return void
	 */
	public function importCommand($file, $location = 'L', $overwrite = FALSE) {
		try {
			$data = $this->getMyService()->importExtension($file, $location, $overwrite);
			$this->outputLine(sprintf('Extension "%s" has been imported!', $data['extKey']));

		} catch (Exception $e) {
			$this->outputLine($e->getMessage());
			$this->quit();
		}
	}

	/**
	 * createUploadFoldersCommand
	 *
	 * @return void
	 */
	public function createUploadFoldersCommand() {
		$messages = $this->getMyService()->createUploadFolders();

		if (sizeof($messages)) {
			foreach ($messages as $message) {
				$this->outputLine($message);
			}
		} else {
			$this->outputLine('no uploadFolder created');
		}
	}

	/**
	 * Gets the ExtensionApiService for installed TYPO3 version
	 *
	 * @return Tx_Coreapi_Service_ExtensionApiService
	 */
	public function getMyService() {
		if (t3lib_div::compat_version('6.0.0')) {
			$service = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApi60Service');
			$service->injectObjectManager($this->objectManager);
		} else {
			$service = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApiService');
		}

		return $service;
	}
}

?>