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
		'synopsis' => './cli_dispatch.phpsh service:command [options] arguments',
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
		
		parent::__construct();
		
		if(!isset($this->cli_args['_DEFAULT'][1]) || $this->cli_args['_DEFAULT'][1] === 'help'){
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
			
			$commandMethod = $this->service.ucfirst($this->command).'Command';
			
			if(method_exists($this, $commandMethod)){
				
				$givenArgs = array_slice($this->cli_args['_DEFAULT'],2);
				
				$method = new ReflectionMethod(get_class($this),$commandMethod);
				
				$args = array();
				
				foreach($method->getParameters() as $param){
					if($param->isOptional()){
						
						$option = '--'.$param->getName();
						
						if($this->cli_isArg($option)){
							$args = $this->cli_argValue($option);
						}
						
					} else {
						
						$args[] = array_shift($givenArgs);
						
					}
				}
				
				//invoke command with given args and options
				$method->invokeArgs($this,$args);
				
			} else {
				
				throw new InvalidArgumentException('Service does not exist or command not supported');
				
			}
			
		} catch (Exception $e) {
			$errorMessage = sprintf('ERROR: Error in service "%s" and command "%s"": %s!', $this->service, $this->command, $e->getMessage());
			$this->outputLine($errorMessage);
		}
	}
	
	
	/**
	 * Clear all caches
	 * 
	 * Clears all TYPO3 caches
	 *
	 * @return void
	 */
	public function cacheClearallcachesCommand(){
		
		$cacheApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_CacheApiService');
		$cacheApiService->initializeObject();

		$cacheApiService->clearAllCaches();
		$this->outputLine('All caches cleared');
		
	}
	
	
	/**
	 * Clear configuration cache (temp_CACHED_..)
	 * 
	 * Deletes the temp_CACHED_* files in /typo3conf
	 *
	 * @return void
	 */
	public function cacheClearconfigurationcacheCommand(){

		$cacheApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_CacheApiService');
		$cacheApiService->initializeObject();

		$cacheApiService->clearConfigurationCache();
		$this->outputLine('Configuration cache cleared');
		
	}

	/**
	 * Clear page cache
	 * 
	 * Clears the page cache in TYPO3
	 *
	 * @return void
	 */
	public function cacheClearpagecacheCommand(){

		$cacheApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_CacheApiService');
		$cacheApiService->initializeObject();
		
		$cacheApiService->clearPageCache();
		$this->outputLine('Page cache cleared');
				
	}


	/**
	 * Database compare
	 *
	 * Leave the argument 'actions' empty or use "help" to see the available ones
	 *
	 * @param string $actions List of actions which will be executed
	 * @return void
	 * @example ./cli_dispatch.phpsh coreapi database:databasecompare 1
	 */
	public function databaseDatabasecompareCommand($actions){
			
		$databaseApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_DatabaseApiService');
			
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
	public function extensionInfoCommand($extkey){
		
		$extensionApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_ExtensionApiService');
		
		$data = $extensionApiService->getExtensionInformation($extkey);
		$this->outputLine('');
		$this->outputLine('EXTENSION "%s": %s %s', array(strtoupper($extkey), $data['em_conf']['version'], $data['em_conf']['state']));
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
	}

	/**
	 * Update list
	 * 
	 * Update the list of available extensions in the TER
	 *
	 * @return void
	 */
	public function extensionUpdatelistCommand(){
		
		$extensionApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_ExtensionApiService');
		$extensionApiService->updateMirrors();
		$this->outputLine('Extension list has been updated.');
		
	}
	
	/**
	 * List all installed (loaded) extensions
	 *
	 * @param string $type Extension type, can either be L for local, S for system or G for global. Leave it empty for all
	 * @return void
	 */
	public function extensionListinstalledCommand($type=''){

		$extensionApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_ExtensionApiService');

		$extensions = $extensionApiService->getInstalledExtensions($type);
		$out = array();
		
		foreach($extensions as $key => $details) {
			$title = $key . ' - ' . $details['version'] . '/' . $details['state'];
			$out[$title] = $details['title'];
		}
		$this->outputTable($out);
		
	}
	
	/**
	 * Site info
	 *
	 * Basic information about the system
	 *
	 * @return void
	 */
	public function siteInfoCommand(){
		
		$siteApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_SiteApiService');
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
	 */
	public function siteCreatesysnewsCommand($header, $text){

		$siteApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_SiteApiService');
		
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
		
		$this->cli_echo('ERROR: '.$message, $force);
		die();
		
	}
	
	/**
	 * Display help
	 * Overridden from parent 
	 * 
	 * @return void
	 */
	public function cli_help(){
		
		if(isset($this->cli_args['_DEFAULT'][1]) && 
			$this->cli_args['_DEFAULT'][1] === 'help' && 
			isset($this->cli_args['_DEFAULT'][2]) && 
			strpos($this->cli_args['_DEFAULT'][2],':') !== FALSE
		){
			
			$this->setHelpFromDocComment($this->cli_args['_DEFAULT'][2]);	
						
		} else {
			
			$class = new ReflectionClass(get_class($this));
			$commands = array();
			foreach($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method){
				if(preg_match('/([a-z][a-z0-9]*)([A-Z][a-zA-Z0-9]*)Command/',$method->getName(),$matches)){
					$commands[] = strtolower($matches[1]) . ':' . strtolower($matches[2]);
				}								
			}
			
			$this->cli_help['description'] = str_replace('###COMMANDS###',implode(PHP_EOL,$commands),$this->cli_help['description']);
			
		}
		
		parent::cli_help();
		
	}
	
	
	protected function setHelpFromDocComment($op){
		
		list($service,$command) = explode(':',$op,2);
		
		$commandmethod = strtolower($service).ucfirst($command).'Command';
		
		if(method_exists($this, $commandmethod)){
			
			//extract doc comment
			$ref = new ReflectionMethod(get_class($this),$commandmethod);
			
			$comment = $ref->getDocComment();

			$comment = preg_replace('/\/\*\*\s*(.*)\s*\*\//s','$1',$comment);
			
			$lines = explode(PHP_EOL,$comment);

			//get name
			$name = preg_replace('/^\s*\*\s*(.*)\s*$/i','$1', array_shift($lines) );
			$this->cli_help['name'] = $name;
			
			$description = array();
			
			foreach($lines as $n => $l){
				if(!preg_match('/^\s*\*\s*@(param|return|example)/i',$l)){
					//add to description
					$description[] = preg_replace('/^\s*\*\s*(.*)\s*$/i','$1',$l);
					continue;
				}
				break;
			}
			
			$this->cli_help['description'] = trim(implode(PHP_EOL,$description));
			
			if(preg_match_all('/@example\s*(.*)/i',$comment,$matches)){
				$this->cli_help['examples'] = implode(PHP_EOL,$matches[1]);				
			} else {
				unset($this->cli_help['examples']);
			}
			
						
			//get params
			$params = $ref->getParameters();
			
			$args = array();
			
			foreach($params as $param){
				
				if($param->isOptional()){
						
					$option = array();
					$option[0] = '--'.$param->getName();
					if(preg_match('/\*\s*@param\s*[a-z0-9_]*\$'.$param->getName().'\s+(.*)/',$comment,$matches)){
						$option[1] = $matches[1];
					}
					$this->cli_options[] = $option;
					
				} else {
					
					$args[] = strtoupper($param->getName());
					
				}
			}
			
			//set synopsis for this
			$this->cli_help['synopsis'] = './cli_dispatch.phpsh coreapi '.$op.' ###OPTIONS### '.implode(' ',$args);
			
		} else {

			$this->error(sprintf('No help available for "%s"',$op).PHP_EOL);
			
		}
		
	}

	
}

if ((TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI) && basename(PATH_thisScript) == 'cli_dispatch.phpsh') {
	$dispatcher = t3lib_div::makeInstance('Tx_Coreapi_Cli_Dispatcher');
	$dispatcher->start();
} else {
	die('This script must be included by the "CLI module dispatcher"');
}

?>