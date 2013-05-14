<?php

/* * *************************************************************
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
 * ************************************************************* */

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
			/** @var $service Tx_Coreapi_Service_ExtensionApiService */
			$service = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApiService');
			$data = $service->getExtensionInformation($key);
		} catch (Exception $e) {
			$this->outputLine($e->getMessage());
			$this->quit();
		}

		$this->outputLine('');
		$this->outputLine('EXTENSION "%s": %s %s', array(strtoupper($key), $data['em_conf']['version'], $data['em_conf']['state']));
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

		/** @var $extensions Tx_Coreapi_Service_ExtensionApiService */
		$extensions = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApiService')->getInstalledExtensions($type);

		foreach ($extensions as $key => $details) {
			$title = $key . ' - ' . $details['version'] . '/' . $details['state'];
			$description = $details['title'];
			$description = wordwrap($description, self::MAXIMUM_LINE_LENGTH - 43, PHP_EOL . str_repeat(' ', 43), TRUE);
			$this->outputLine('%-2s%-40s %s', array(' ', $title, $description));
		}
	}

	/**
	 * Update list
	 *
	 * @return void
	 */
	public function updateListCommand() {
		/** @var $service Tx_Coreapi_Service_ExtensionApiService */
		$service = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApiService');
		$service->updateMirrors();

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
			
			/** @var $service Tx_Coreapi_Service_ExtensionApiService */
			$service = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApiService');
			$data = $service->installExtension($key);
			
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
	public function unInstallCommand($key) {
		
		try {
			
			/** @var $service Tx_Coreapi_Service_ExtensionApiService */
			$service = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApiService');
			$data = $service->unInstallExtension($key);
			
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
	 * You can use this in two ways
	 * 
	 * 1) extensionkey + path to file containing a configuration for the extension
	 * example: ./cli_dispatch.phpsh extbase extensionapi:configure rtehtmlarea C:\rteconf.txt
	 * 
	 * 2) extensionkey + key=value pair for each setting you want to change
	 * example: ./cli_dispatch.phpsh extbase extensionapi:configure rtehtmlarea enableImages=1 allowStyleAttribute=0
	 *
	 * @param string $key extension key
	 * @return void
	 */
	public function configureCommand($key) {
		
		global $TYPO3_CONF_VARS;
		
		try {
			
			/** @var $service Tx_Coreapi_Service_ExtensionApiService */
			$service = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApiService');
				
			$args = $this->request->getExceedingArguments();
			
			$conf = array();
			
			if(count($args) == 1 && isset($args[0]) && is_file($args[0])){
			
				$conf =  parse_ini_file($args[0]);
				
				if(empty($conf)){

					throw new InvalidArgumentException(sprintf('File did not contain any configuration settings!', $key));
					
				}
			
			} else {
				
				foreach($args as $arg){
					
					if(strstr($arg,'=')){
						$parts = explode('=',$arg,2);
						$conf[$parts[0]] = $parts[1];
					} else {

						throw new InvalidArgumentException(sprintf('Invalid argument "%s"!', $arg));
						
					}
							
				}
				
				if(empty($conf)){

					throw new InvalidArgumentException(sprintf('No configuration settings!', $key));
					
				}
				
			}
			
			
			$data = $service->configureExtension($key,$conf);
			
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
	 * @param string $version
	 * @param string $location where to put the extension. S = typo3/sysext, G = typo3/ext, L = typo3conf/ext
	 * @param string $overwrite overwrite the extension if it already exists
	 * @param string $mirror mirror URL
	 * @return void
	 */

	public function fetchCommand($key, $version='', $location='L', $overwrite = FALSE, $mirror = ''){

		try {
			
			/** @var $service Tx_Coreapi_Service_ExtensionApiService */
			$service = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApiService');
			$data = $service->fetchExtension($key, $version, $location, $overwrite,$mirror);
			$this->outputLine(sprintf('Extension "%s" version %s has been fetched from repository!', $data['extKey'],$data['version']));
			
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

	public function importCommand($file, $location='L', $overwrite = FALSE){

		try {
			
			/** @var $service Tx_Coreapi_Service_ExtensionApiService */
			$service = $this->objectManager->get('Tx_Coreapi_Service_ExtensionApiService');
			$data = $service->importExtension($file,$location,$overwrite);
			$this->outputLine(sprintf('Extension "%s" has been imported!', $data['extKey']));
			
		} catch (Exception $e) {
			
			$this->outputLine($e->getMessage());
			$this->quit();
			
		}
		
	}


}

?>