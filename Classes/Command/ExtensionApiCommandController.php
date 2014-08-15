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
use Exception;
use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Extension API Command Controller
 *
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @package Etobi\CoreAPI\Service\SiteApiService
 */
class ExtensionApiCommandController extends CommandController {

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
	 * @var \Etobi\CoreAPI\Service\ExtensionApiService
	 * @inject
	 */
	protected $extensionApiService;

	/**
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 * @inject
	 */
	protected $signalSlotDispatcher;

	/**
	 * Information about an extension.
	 *
	 * @param string $key The extension key
	 *
	 * @return void
	 */
	public function infoCommand($key) {
		$data = array();
		try {
			$data = $this->extensionApiService->getExtensionInformation($key);
		} catch (Exception $e) {
			$message = $e->getMessage();
			$this->outputLine($message);
			$this->logger->error($message);
			$this->quit(1);
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

		$this->logger->info('extensionApi:info executes successfully.');
	}

	/**
	 * List all installed extensions.
	 *
	 * @param string $type Extension type, can either be "Local",
	 *                     "System" or "Global". Leave it empty for all
	 *
	 * @return void
	 */
	public function listInstalledCommand($type = '') {
		$type = ucfirst(strtolower($type));
		if (!empty($type) && $type !== 'Local' && $type !== 'Global' && $type !== 'System') {
			// TODO: Throw a exception here?
			$message = 'Only "Local", "System" and "Global" are supported as type (or nothing)';
			$this->outputLine($message);
			$this->logger->error($message);
			$this->quit(1);
		}

		$extensions = $this->extensionApiService->listExtensions($type);

		foreach ($extensions as $key => $details) {
			$title = $key . ' - ' . $details['version'] . '/' . $details['state'];
			$description = $details['title'];
			$description = wordwrap($description, self::MAXIMUM_LINE_LENGTH - 43, PHP_EOL . str_repeat(' ', 43), TRUE);
			$this->outputLine('%-2s%-40s %s', array(' ', $title, $description));
		}

		$this->outputLine('%-2s%-40s', array(' ', str_repeat('-', self::MAXIMUM_LINE_LENGTH - 3)));
		$this->outputLine('  Total: ' . count($extensions) . ' extensions');
		$this->logger->info('extensionApi:listInstalled executed successfully');
	}

	/**
	 * Update list.
	 *
	 * @return void
	 */
	public function updateListCommand() {
		$this->outputLine('This may take a while...');
		$result = $this->extensionApiService->updateMirrors();

		if ($result) {
			$message = 'Extension list has been updated.';
			$this->outputLine($message);
			$this->logger->info($message);
		} else {
			$message = 'Extension list already up-to-date.';
			$this->outputLine($message);
			$this->logger->info($message);
		}
	}

	/**
	 * Install(activate) an extension.
	 *
	 * @param string $key The extension key
	 *
	 * @return void
	 */
	public function installCommand($key) {
		try {
			$this->emitPackagesMayHaveChangedSignal();
			$this->extensionApiService->installExtension($key);
		} catch (Exception $e) {
			$message = $e->getMessage();
			$this->outputLine($message);
			$this->logger->error($message);
			$this->quit(1);
		}

		$message = sprintf('Extension "%s" is now installed!', $key);
		$this->outputLine($message);
		$this->logger->info($message);
	}

	/**
	 * UnInstall(deactivate) an extension.
	 *
	 * @param string $key The extension key
	 *
	 * @return void
	 */
	public function uninstallCommand($key) {
		try {
			$this->extensionApiService->uninstallExtension($key);
		} catch (Exception $e) {
			$message = $e->getMessage();
			$this->outputLine($message);
			$this->logger->error($message);
			$this->quit(1);
		}

		$message = sprintf('Extension "%s" is now uninstalled!', $key);
		$this->outputLine($message);
		$this->logger->info($message);
	}

	/**
	 * Configure an extension.
	 * This command enables you to configure an extension.
	 *
	 * <code>
	 * [1] Using a standard formatted ini-file
	 * ./cli_dispatch.phpsh extbase extensionapi:configure rtehtmlarea --configfile=C:\rteconf.txt
	 *
	 * [2] Adding configuration settings directly on the command line
	 * ./cli_dispatch.phpsh extbase extensionapi:configure rtehtmlarea --settings="enableImages=1;allowStyleAttribute=0"
	 *
	 * [3] A combination of [1] and [2]
	 * ./cli_dispatch.phpsh extbase extensionapi:configure rtehtmlarea --configfile=C:\rteconf.txt --settings="enableImages=1;allowStyleAttribute=0"
	 * </code>
	 *
	 * @param string $key        The extension key
	 * @param string $configFile Path to file containing configuration settings. Must be formatted as a standard ini-file
	 * @param string $settings   String containing configuration settings separated on the form "k1=v1;k2=v2;"
	 *
	 * @return void
	 */
	public function configureCommand($key, $configFile = '', $settings = '') {
		try {
			$conf = array();
			if (is_file($configFile)) {
				$conf = parse_ini_file($configFile);
			}

			if (strlen($settings)) {
				$arr = explode(';', $settings);
				foreach ($arr as $v) {
					if (strpos($v, '=') === FALSE) {
						throw new InvalidArgumentException(sprintf('Ill-formed setting "%s"!', $v));
					}
					$parts = GeneralUtility::trimExplode('=', $v, FALSE, 2);
					if (!empty($parts[0])) {
						$conf[$parts[0]] = $parts[1];
					}
				}
			}

			if (empty($conf)) {
				$this->response->setExitCode(1);
				$message = sprintf('No configuration settings!', $key);
				$this->logger->error($message);
				throw new InvalidArgumentException($message);
			}

			$this->extensionApiService->configureExtension($key, $conf);

		} catch (Exception $e) {
			$message = $e->getMessage();
			$this->outputLine($message);
			$this->logger->error($message);
			$this->quit(1);
		}

		$message = sprintf('Extension "%s" has been configured!', $key);
		$this->outputLine($message);
		$this->logger->info($message);
	}

	/**
	 * Fetch an extension from TER.
	 *
	 * @param string $key       The extension key
	 * @param string $version   The exact version of the extension, otherwise the latest will be picked
	 * @param string $location  Where to put the extension. System = typo3/sysext, Global = typo3/ext, Local = typo3conf/ext
	 * @param bool   $overwrite Overwrite the extension if already exists
	 * @param int    $mirror    Mirror to fetch the extension from. Run extensionapi:listmirrors to get the list of all available repositories, otherwise a random mirror will be selected
	 *
	 * @return void
	 */
	public function fetchCommand($key, $version = '', $location = 'Local', $overwrite = FALSE, $mirror = -1) {
		try {
			$data = $this->extensionApiService->fetchExtension($key, $version, $location, $overwrite, $mirror);
			$message = sprintf('Extension "%s" version %s has been fetched from repository! Dependencies were not resolved.', $data['main']['extKey'], $data['main']['version']);
			$this->outputLine($message);
			$this->logger->info($message);
		} catch (Exception $e) {
			$message = $e->getMessage();
			$this->outputLine($message);
			$this->logger->error($message);
			$this->quit(1);
		}
	}

	/**
	 * Lists the possible mirrors
	 *
	 * @return void
	 */
	public function listMirrorsCommand() {
		try {
			$mirrors = $this->extensionApiService->listMirrors();
			$key = 0;
			foreach ($mirrors as $mirror) {
				$this->outputLine($key . ' = ' . $mirror['title'] . ' ' . $mirror['host']);
				++$key;
			}
		} catch (Exception $e) {
			$message = $e->getMessage();
			$this->outputLine($message);
			$this->logger->error($message);
			$this->quit(1);
		}
		$this->logger->info('extensionApi:listMirrors executed successfully.');
	}

	/**
	 * Import extension from file.
	 *
	 * @param string  $file      Path to t3x file
	 * @param string  $location  Where to import the extension. System = typo3/sysext, Global = typo3/ext, Local = typo3conf/ext
	 * @param boolean $overwrite Overwrite the extension if already exists
	 *
	 * @return void
	 */
	public function importCommand($file, $location = 'Local', $overwrite = FALSE) {
		try {
			$data = $this->extensionApiService->importExtension($file, $location, $overwrite);
			$message = sprintf('Extension "%s" has been imported!', $data['extKey']);
			$this->outputLine($message);
			$this->logger->info($message);
		} catch (Exception $e) {
			$this->outputLine($e->getMessage());
			$this->logger->error($e->getMessage());
			$this->quit(1);
		}
	}

	/**
	 * Emits packages may have changed signal
	 *
	 * @return \Etobi\CoreAPI\Service\ExtensionApiService object
	 */
	protected function emitPackagesMayHaveChangedSignal() {
		$this->signalSlotDispatcher->dispatch('PackageManagement', 'packagesMayHaveChanged');
	}
}
