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
class Tx_Coreapi_Cli_Dispatcher extends t3lib_cli {

	var $cli_help = array(
		'name' => 'coreapi',
		'synopsis' => './cli_dispatch.phpsh coreapi service:command [options] arguments',
		'description' => '
Coreapi provides a set of services/commands for doing the most common admin task in TYPO3 by CLI instead of doing it in the backend/browser.
Currently the following commands are supported:

###COMMANDS###
',
		'examples' => '
./cli_dispatch.phpsh coreapi site:info
./cli_dispatch.phpsh coreapi cache:clearallcaches
./cli_dispatch.phpsh coreapi extension:info rtehtmlarea
./cli_dispatch.phpsh coreapi site:createsysnews "title" "text"',
		'options' => 'use "./cli_dispatch.phpsh coreapi help service:command" to get help on the arguments and options of a specific command
		',
		'license' => 'GNU GPL - free software!',
		'author' => 'Tobias Liebig',
	);

	const MAXIMUM_LINE_LENGTH = 79;

	var $commandMethodPattern = '/([a-z][a-z0-9]*)([A-Z][a-zA-Z0-9]*)Command/';

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
	 */
	public function __construct() {
		parent::__construct();

		if (!isset($this->cli_args['_DEFAULT'][1]) || $this->cli_args['_DEFAULT'][1] === 'help') {
			$this->cli_help();
			die();
		}

		$split = explode(':', $this->cli_args['_DEFAULT'][1]);
		if (count($split) === 1) {
			$this->error('CLI calls need to be like coreapi cache:clearallcaches');
		} elseif (count($split) !== 2) {
			$this->error('Only one : is allowed in first argument');
		}

		$this->service = strtolower($split[0]);
		$this->command = strtolower($split[1]);
	}

	/**
	 * Starts the script
	 * @return void
	 */
	public function start() {
		try {
			$command = $this->service . ucfirst($this->command) . 'Command';
			$this->runCommand($command);

		} catch (Exception $e) {
			$errorMessage = sprintf('ERROR: Error in service "%s" and command "%s"": %s!', $this->service, $this->command, $e->getMessage());
			$this->outputLine($errorMessage);
		}
	}

	/**
	 * @param $command
	 * @throws InvalidArgumentException
	 */
	protected function runCommand($command) {
		if (method_exists($this, $command)) {
			$args = array_slice($this->cli_args['_DEFAULT'], 2);
			$method = new ReflectionMethod(get_class($this), $command);

			//check number of required arguments
			if ($method->getNumberOfRequiredParameters() !== count($args)) {
				throw new InvalidArgumentException('Wrong number of arguments');
			}

			foreach ($method->getParameters() as $param) {
				if ($param->isOptional()) {
					$name = $param->getName();
					if ($this->cli_isArg('--' . $name)) {
						$args[] = $this->cli_argValue('--' . $name);
					} else {
						$args[] = $param->getDefaultValue();
					}
				}
			}
			//invoke command with given args and options
			$method->invokeArgs($this, $args);
		} else {
			throw new InvalidArgumentException('Service does not exist or command not supported');
		}
	}

	/**
	 * Clear all caches
	 *
	 * Clears all TYPO3 caches
	 *
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi cache:clearallcaches
	 */
	public function cacheClearallcachesCommand() {
		$cacheApiService = $this->getCacheApiService();
		$cacheApiService->clearAllCaches();
		$this->outputLine('All caches cleared');
	}


	/**
	 * Clear configuration cache (temp_CACHED_..)
	 *
	 * Deletes the temp_CACHED_* files in /typo3conf
	 *
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi cache:clearconfigurationcache
	 */
	public function cacheClearconfigurationcacheCommand() {
		$cacheApiService = $this->getCacheApiService();
		$cacheApiService->clearConfigurationCache();
		$this->outputLine('Configuration cache cleared');
	}

	/**
	 * Clear page cache
	 *
	 * Clears the page cache in TYPO3
	 *
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi cache:clearpagecache
	 */
	public function cacheClearpagecacheCommand() {
		$cacheApiService = $this->getCacheApiService();
		$cacheApiService->clearPageCache();
		$this->outputLine('Page cache cleared');
	}

	/**
	 * Clear all caches except the page cache.
	 * This is especially useful on big sites when you can't just drop the page cache
	 *
	 * @example ./cli_dispatch.phpsh coreapi cache:clearallexceptpagecache
	 * @return void
	 */
	public function clearAllExceptPageCacheCommand() {
		$cacheApiService = $this->getCacheApiService();
		$clearedCaches = $cacheApiService->clearAllExceptPageCache();

		$this->outputLine('Cleared caches: ' . implode(', ', $clearedCaches));
	}

	/**
	 * Database compare
	 *
	 * Leave the argument 'actions' empty or use "help" to see the available ones
	 *
	 * @param string $actions List of actions which will be executed
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi database:databasecompare 2
	 */
	public function databaseDatabasecompareCommand($actions) {
		$databaseApiService = $this->getDatabaseApiService();
		if ($actions === 'help') {
			$actions = $databaseApiService->databaseCompareAvailableActions();
			$this->outputTable($actions);
		} else {
			$databaseApiService->databaseCompare($actions);
		}
	}

	/**
	 * Information about an extension
	 *
	 * Echo's out a table with information about a specific extension
	 *
	 * @param string $extkey extension key
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi extension:info rtehtmlarea
	 */
	public function extensionInfoCommand($extkey) {
		$extensionApiService = $this->getExtensionApiService();
		$data = $extensionApiService->getExtensionInformation($extkey);
		$this->outputLine('');
		$this->outputLine('EXTENSION "%s": %s %s', array(strtoupper($extkey), $data['em_conf']['version'], $data['em_conf']['state']));
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
	 * Update list
	 *
	 * Update the list of available extensions in the TER
	 *
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi extension:updatelist
	 */
	public function extensionUpdatelistCommand() {
		$extensionApiService = $this->getExtensionApiService();
		$extensionApiService->updateMirrors();
		$this->outputLine('Extension list has been updated.');
	}

	/**
	 * List all installed (loaded) extensions
	 *
	 * @param string $type Extension type, can either be L for local, S for system or G for global. Leave it empty for all
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi extension:listinstalled --type=S
	 */
	public function extensionListinstalledCommand($type = '') {
		$extensionApiService = $this->getExtensionApiService();
		$extensions = $extensionApiService->getInstalledExtensions($type);
		$out = array();

		foreach ($extensions as $key => $details) {
			$title = $key . ' - ' . $details['version'] . '/' . $details['state'];
			$out[$title] = $details['title'];
		}
		$this->outputTable($out);
	}

	/**
	 * Install(activate) an extension
	 *
	 * @param string $key extension key
	 * @return void
	 */
	public function extensionInstallCommand($key) {
		$extensionApiService = $this->getExtensionApiService();
		$data = $extensionApiService->installExtension($key);
		$this->outputLine(sprintf('Extension "%s" is now installed!', $key));
	}

	/**
	 * UnInstall(deactivate) an extension
	 *
	 * @param string $key extension key
	 * @return void
	 */
	public function extensionUninstallCommand($key) {
		$extensionApiService = $this->getExtensionApiService();
		$data = $extensionApiService->uninstallExtension($key);
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
	 * ./cli_dispatch.phpsh coreapi extension:configure rtehtmlarea --configfile=C:\rteconf.txt
	 *
	 * [2] Adding configuration settings directly on the command line
	 * ./cli_dispatch.phpsh coreapi extension:configure rtehtmlarea --settings="enableImages=1;allowStyleAttribute=0"
	 *
	 * [3] A combination of [1] and [2]
	 * ./cli_dispatch.phpsh extbase extension:configure rtehtmlarea --configfile=C:\rteconf.txt --settings="enableImages=1;allowStyleAttribute=0"
	 *
	 * @param string $key extension key
	 * @param string $configfile path to file containing configuration settings. Must be formatted as a standard ini-file
	 * @param string $settings string containing configuration settings separated on the form "k1=v1;k2=v2;"
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function extensionConfigureCommand($key, $configfile = '', $settings = '') {
		global $TYPO3_CONF_VARS;
		$extensionApiService = $this->getExtensionApiService();
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

		$extensionApiService->configureExtension($key, $conf);
		$this->outputLine(sprintf('Extension "%s" has been configured!', $key));

	}


	/**
	 * Fetch an extension from TER
	 *
	 * @param string $key extension key
	 * @param string $version the exact version of the extension, otherwise the latest will be picked
	 * @param string $location where to put the extension. S = typo3/sysext, G = typo3/ext, L = typo3conf/ext
	 * @param string|bool $overwrite overwrite the extension if it already exists
	 * @param string $mirror mirror to fetch the extension from, otherwise a random mirror will be selected
	 * @return void
	 */
	public function extensionFetchCommand($key, $version = '', $location = 'L', $overwrite = FALSE, $mirror = '') {
		$extensionApiService = $this->getExtensionApiService();
		$data = $extensionApiService->fetchExtension($key, $version, $location, $overwrite, $mirror);
		$this->outputLine(sprintf('Extension "%s" version %s has been fetched from repository!', $data['extKey'], $data['version']));
	}


	/**
	 * Import extension from file
	 *
	 * @param string $file path to t3x file
	 * @param string $location where to import the extension. S = typo3/sysext, G = typo3/ext, L = typo3conf/ext
	 * @param boolean $overwrite overwrite the extension if it already exists
	 * @return void
	 */
	public function extensionImportCommand($file, $location = 'L', $overwrite = FALSE) {
		$extensionApiService = $this->getExtensionApiService();
		$data = $extensionApiService->importExtension($file, $location, $overwrite);
		$this->outputLine(sprintf('Extension "%s" has been imported!', $data['extKey']));
	}

	/**
	 * Ensure upload folders of installed extensions exist
	 * @return void
	 */
	public function extensionCreateuploadfoldersCommand() {
		$extensionApiService = $this->getExtensionApiService();
		$messages = $extensionApiService->createUploadFolders();
		if (sizeof($messages)) {
			foreach ($messages as $message) {
				$this->outputLine($message);
			}
		} else {
			$this->outputLine('no uploadFolder created');
		}
	}

	/**
	 * Site info
	 *
	 * Basic information about the system
	 *
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi site:info
	 */
	public function siteInfoCommand() {
		$siteApiService = $this->getSiteApiService();
		$infos = $siteApiService->getSiteInfo();
		$this->outputTable($infos);
	}

	/**
	 * Create a sys news
	 *
	 * Sys news record is displayed at the login page
	 *
	 * @param string $header Header text
	 * @param string $text Basic text
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi site:createsysnews "The header" "The news text"
	 */
	public function siteCreatesysnewsCommand($header, $text) {
		$siteApiService = $this->getSiteApiService();
		$siteApiService->createSysNews($header, $text);
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
		$this->cli_echo($text . PHP_EOL);
	}

	/**
	 * Output a whole table, maximum 2 cols
	 *
	 * @param array $input input table
	 * @return void
	 */
	protected function outputTable(array $input) {
		$this->outputLine(str_repeat('-', self::MAXIMUM_LINE_LENGTH));
		foreach ($input as $key => $value) {
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
		$this->cli_echo('ERROR: ' . $message, FALSE);
		die();
	}

	/**
	 * Display help
	 * Overridden from parent
	 *
	 * @return void
	 */
	public function cli_help() {
		if (isset($this->cli_args['_DEFAULT'][1]) &&
				$this->cli_args['_DEFAULT'][1] === 'help' &&
				isset($this->cli_args['_DEFAULT'][2]) &&
				strpos($this->cli_args['_DEFAULT'][2], ':') !== FALSE
		) {
			$this->setHelpFromDocComment($this->cli_args['_DEFAULT'][2]);
		} else {
			$class = new ReflectionClass(get_class($this));
			$commands = array();
			foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				if (preg_match($this->commandMethodPattern, $method->getName(), $matches)) {
					$commands[] = strtolower($matches[1]) . ':' . strtolower($matches[2]);
				}
			}
			$this->cli_help['description'] = str_replace('###COMMANDS###', implode(PHP_EOL, $commands), $this->cli_help['description']);
		}
		parent::cli_help();
	}

	/**
	 * Extract help texts from doc comments
	 *
	 * @param $operation
	 */
	protected function setHelpFromDocComment($operation) {
		list($service, $command) = explode(':', $operation, 2);
		$commandMethod = strtolower($service) . ucfirst($command) . 'Command';
		if (method_exists($this, $commandMethod)) {
			$this->cli_help['options'] = '';
			//extract doc comment
			$ref = new ReflectionMethod(get_class($this), $commandMethod);
			$comment = $ref->getDocComment();
			$comment = preg_replace('/\/\*\*\s*(.*)\s*\*\//s', '$1', $comment);
			$lines = explode(PHP_EOL, $comment);

			//get name
			$name = preg_replace('/^\s*\*\s*(.*)\s*$/i', '$1', array_shift($lines));
			$this->cli_help['name'] = $name;

			$description = array();
			$examples = array();
			$params = array();
			foreach ($lines as $n => $l) {
				if (!preg_match('/^\s*\*\s*@/i', $l)) {
					//add to description
					$description[] = preg_replace('/^\s*\*\s*(.*)\s*$/i', '$1', $l);
					continue;
				}

				//params
				if (preg_match('/^\s*\*\s*@param\s*(?P<type>[a-z0-9_]*)\s+\$(?P<name>[a-z0-9_]*)\s+(?P<description>.*)/i', $l, $matches)) {
					$params[$matches['name']] = array(
						'type' => $matches['name'],
						'description' => $matches['description']
					);
					continue;
				}

				// examples
				if (preg_match('/^\s*\*\s*@example\s+(?P<text>.*)/i', $l, $matches)) {
					$examples[] = $matches['text'];
				}
			}

			$this->cli_help['description'] = trim(implode(PHP_EOL, $description));
			if (!empty($examples)) {
				$this->cli_help['examples'] = implode(PHP_EOL, $examples);
			} else {
				unset($this->cli_help['examples']);
			}

			//get params
			$parameters = $ref->getParameters();
			$args = array();
			foreach ($parameters as $param) {
				$name = $param->getName();
				$description = isset($params[$name]) ? $params[$name]['description'] : '';
				if ($param->isOptional()) {
					$this->cli_options[] = array('--' . $name, $description);
				} else {
					$args[strtoupper($name)] = $description;
				}
			}

			//compile arguments section
			if (!empty($args)) {
				$maxLen = 0;
				foreach (array_keys($args) as $argname) {
					if (strlen($argname) > $maxLen) {
						$maxLen = strlen($argname);
					}
				}

				$tmp = array();
				foreach ($args as $argname => $description) {
					$tmp[] = $argname . substr($this->cli_indent(rtrim($description), $maxLen + 4), strlen($argname));
				}

				$offset = array_search('options', array_keys($this->cli_help));
				$this->cli_help = array_slice($this->cli_help, 0, $offset, true) +
						array('arguments' => LF . implode(LF, $tmp)) +
						array_slice($this->cli_help, $offset, NULL, true);
			}

			//set synopsis for this
			$this->cli_help['synopsis'] = './cli_dispatch.phpsh coreapi ' . $operation . ' ###OPTIONS### ' . implode(' ', array_keys($args));

		} else {
			$this->error(sprintf('No help available for "%s"', $operation) . PHP_EOL);
		}
	}

	/**
	 * @return Tx_Coreapi_Service_CacheApiService
	 */
	protected function getCacheApiService() {
		$cacheApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_CacheApiService');
		$cacheApiService->initializeObject();
		return $cacheApiService;
	}

	/**
	 * @return Tx_Coreapi_Service_DatabaseApiService
	 */
	protected function getDatabaseApiService() {
		$databaseApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_DatabaseApiService');
		return $databaseApiService;
	}

	/**
	 * @return Tx_Coreapi_Service_Core45_ExtensionApiService
	 */
	protected function getExtensionApiService() {
		$extensionApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_Core45_ExtensionApiService');
		return $extensionApiService;
	}

	/**
	 * @return Tx_Coreapi_Service_SiteApiService
	 */
	protected function getSiteApiService() {
		/** @var Tx_Coreapi_Service_SiteApiService $siteApiService */
		$siteApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_SiteApiService');
		$siteApiService->injectExtensionApiService($this->getExtensionApiService());
		return $siteApiService;
	}
}

?>