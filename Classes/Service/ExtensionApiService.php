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
 * Extension API service
 *
 * @package TYPO3
 * @subpackage tx_coreapi
 */
class Tx_Coreapi_Service_ExtensionApiService {
/*
	extension                   Provides some basic information on the site's extension status
	[x] extension:info              Fetches the latest (or provided) version of an extension from TER
	[x] extension:list              Lists all available extensions of a site
	extension:search            Searches for an extension in the TER
	extension:fetch             Fetches the latest (or provided) version of an extension from TER
	extension:install           Installs the latest (or provided) version of an extension
	extension:uninstall         Uninstalls an extension
	extension:refresh           Refreshes the local cache of all extensions available in TER
*/

	public function __construct(){

		if (version_compare(TYPO3_version, '6.0.0', '<')) {

			$this->extensionList = t3lib_div::makeInstance('tx_em_Extensions_List', $this);
		
		}
	}



	/**
	 * Get information about an extension
	 *
	 * @param string $key extension key
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getExtensionInformation($key) {
		if (strlen($key) === 0) {
			throw new InvalidArgumentException('No extension key given!');
		}
		if (!$GLOBALS['TYPO3_LOADED_EXT'][$key]) {
			throw new InvalidArgumentException(sprintf('Extension "%s" not found!', $key));
		}

		include_once(t3lib_extMgm::extPath($key) . 'ext_emconf.php');
		$information = array(
			'em_conf' => $EM_CONF[''],
			'isLoaded' => t3lib_extMgm::isLoaded($key)
		);

		return $information;
	}

	public function getInstalledExtensions($type = '') {
		$type = strtoupper($type);
		if (!empty($type) && $type !== 'L' && $type !== 'G' && $type !== 'S') {
			throw new InvalidArgumentException('Only "L", "S" and "G" are supported as type (or nothing)');
		}

		$extensions = $GLOBALS['TYPO3_LOADED_EXT'];

		$list = array();
		foreach ($extensions as $key => $extension) {
			if (!empty($type) && $type !== $extension['type']) {
				continue;
			}

			include_once(t3lib_extMgm::extPath($key) . 'ext_emconf.php');
			$list[$key] = $EM_CONF[''];
		}

		ksort($list);

		return $list;
	}

	/**
	 * Update the mirrors, using the scheduler task of EXT:em
	 *
	 * @return void
	 * @see tx_em_Tasks_UpdateExtensionList
	 */
	public function updateMirrors() {
		/** @var $emTask tx_em_Tasks_UpdateExtensionList */
		$emTask = t3lib_div::makeInstance('tx_em_Tasks_UpdateExtensionList');
		$emTask->execute();
	}
	
	

	/**
	 * Install (load) an extension
	 * 
	 * @param string $key extension key
	 * @return void
	 */
	public function installExtension($key){

		if(t3lib_div::compat_version('6.0.0')){
			
			throw new RuntimeException('This feature is not available in TYPO3 versions > 4.7 (yet)!');
			
		}
		
		// checks if extension exists		
		if (!$this->exist($key)) {
			
			throw new InvalidArgumentException(sprintf('Extension "%s" does not exist!', $key));
			
		}

		//check if extension is already loaded
		if (t3lib_extMgm::isLoaded($key)) {
			
			throw new InvalidArgumentException(sprintf('Extension "%s" already installed!', $key));
			
		}
		
		//check if localconf.php is writable
		if (!t3lib_extMgm::isLocalconfWritable()) {

			throw new RuntimeException('Localconf.php is not writeable!');
			
		}
		
		//add extension to list of loaded extensions
		$newlist = $this->extensionList->addExtToList($key, $list);	
		if ($newlist === -1) {

			throw new RuntimeException(sprintf('Extension "%s" could not be installed!', $key));

		}
		
		//update typo3conf/localconf.php
		$install = t3lib_div::makeInstance('tx_em_Install', $this);
		$install->setSilentMode(TRUE);
		$install->writeNewExtensionList($newlist);

		tx_em_Tools::refreshGlobalExtList();		
		
		//make database changes
		$install->forceDBupdates($key, $list[$key]);

		$cacheApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_CacheApiService');
		$cacheApiService->initializeObject();
		$cacheApiService->clearAllCaches();		
		
	}


	/**
	 * Uninstall (unload) an extension
	 * 
	 * @param string $key extension key
	 * @return void
	 */
	public function unInstallExtension($key){
		
		if(t3lib_div::compat_version('6.0.0')){
			
			throw new RuntimeException('This feature is not available in TYPO3 versions > 4.7 (yet)!');
			
		}
		
		//check if extension is this extension (coreapi)
		if ($ext == 'coreapi') {
			
			throw new InvalidArgumentException(sprintf('Extension "%s" cannot be uninstalled!', $key));
		
		}
		
		// checks if extension exists		
		if (!$this->exist($key)) {
			
			throw new InvalidArgumentException(sprintf('Extension "%s" does not exist!', $key));
			
		}
		
		//check if extension is loaded
		if (!t3lib_extMgm::isLoaded($key)) {

			throw new InvalidArgumentException(sprintf('Extension "%s" is not installed!', $key));

		}

		//check if localconf.php is writable
		if (!t3lib_extMgm::isLocalconfWritable()) {

			throw new RuntimeException('Localconf.php is not writeable!');
			
		}
		
		$newlist = $this->extensionList->removeExtFromList($key, $list);	
		if ($newlist === -1) {

			throw new RuntimeException(sprintf('Extension "%s" could not be installed!', $key));

		}

		$install = t3lib_div::makeInstance('tx_em_Install', $this);
		$install->setSilentMode(TRUE);
		$install->writeNewExtensionList($newlist);
		
		tx_em_Tools::refreshGlobalExtList();		
		
		$cacheApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_CacheApiService');
		$cacheApiService->initializeObject();
		$cacheApiService->clearAllCaches();		
	}

	/**
	 * Configure an extension
	 * 
	 * @param string $key extension key
	 * @return void
	 */
	public function configureExtension($key,$conf = array()){
		
		if(t3lib_div::compat_version('6.0.0')){
			throw new RuntimeException('This feature is not available in TYPO3 versions > 4.7 (yet)!');
		}
		
		
		//check if extension exists
		if (!$this->exist($key)) {
			
			throw new InvalidArgumentException(sprintf('Extension "%s" does not exist!', $key));
			
		}
		
		//check if extension is loaded
		if (!t3lib_extMgm::isLoaded($key)) {

			throw new InvalidArgumentException(sprintf('Extension "%s" is not installed!', $key));

		}

		// check if extension can be configured
		$extAbsPath = t3lib_extMgm::extPath($key);
				
		$extconftemplatefile = $extAbsPath.'ext_conf_template.txt';
		if(!file_exist($extconftemplatefile)){

			throw new InvalidArgumentException(sprintf('Extension "%s" has no configuration options!', $key));
			
		}
		
		//checks if conf array is empty
		if(empty($conf)){
			
			throw new InvalidArgumentException(sprintf('No configuration for extension "%s"!', $key));
			
		}
		
		// Load tsStyleConfig class and parse configuration template:
		$extRelPath = t3lib_extmgm::extRelPath($key);
		
		$tsStyleConfig = t3lib_div::makeInstance('t3lib_tsStyleConfig');
		$tsStyleConfig->doNotSortCategoriesBeforeMakingForm = TRUE;
		$constants = $tsStyleConfig->ext_initTSstyleConfig(
			t3lib_div::getUrl($extconftemplatefile),
			$extRelPath,
			$extAbsPath,
			$GLOBALS['BACK_PATH']
		);
			
		foreach(array_keys($constants) as $k){
			if(!isset($conf[$k])){
				if(!empty($constants[$k]['value'])){
					$conf[$k] = $constants[$k]['value'];
				} else {
					$conf[$k] = $constants[$k]['default_value'];
				}
			}
		}

		// get existing configuration
		$arr = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$key]);
		$arr = is_array($arr) ? $arr : array();
			
		// process incoming configuration
		// values are checked against types in $constants
		$tsStyleConfig->ext_procesInput(array('data'=>$conf), array(), $constants, array());
			
		// current configuration is merged with incoming configuration
		// NOTE: incoming configuration must contain ALL settings for the extension
		$arr = $tsStyleConfig->ext_mergeIncomingWithExisting($arr);
			
		// write configuration to typo3conf/localconf.php
		$install = t3lib_div::makeInstance('tx_em_Install', $this);
		$install->setSilentMode(TRUE);
		$install->writeTsStyleConfig($key,$arr);
		
		$cacheApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_CacheApiService');
		$cacheApiService->initializeObject();
		$cacheApiService->clearAllCaches();		
		
	}


	/**
	 * Check if an extension exists
	 * 
	 * @param string $key extension key
	 * @return void
	 */
	public function exist($key){

		list($list,) = $this->extensionList->getInstalledExtensions();
				
		$exist = FALSE;
		foreach ($list as $k => $v) {
			if ($v['extkey'] === $key) {
				$exist = TRUE;
				break;
			}
		}

		return $exist;
	}
	
}

?>