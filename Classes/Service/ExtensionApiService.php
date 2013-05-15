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
		$this->extensionList = t3lib_div::makeInstance('tx_em_Extensions_List', $this);
		
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
		if ($key == 'coreapi') {
			
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
		
		
		//check if this is a required extension (such as "cms") that cannot be uninstalled
		$requiredExtList = t3lib_div::trimExplode(',',t3lib_extMgm::getRequiredExtensionList());
		if (in_array($key, $requiredExtList)) {		

			throw new InvalidArgumentException(sprintf('Extension "%s" is a required extension and cannot be uninstalled!', $key));
		
		}
		
		

		//check if localconf.php is writable
		if (!t3lib_extMgm::isLocalconfWritable()) {

			throw new RuntimeException('Localconf.php is not writeable!');
			
		}
		
		$newlist = $this->extensionList->removeExtFromList($key, $list);	
		if ($newlist === -1) {

			throw new RuntimeException(sprintf('Extension "%s" could not be installed!', $key));

		}
		
		//update typo3conf/localconf.php
		$this->extensionList = t3lib_div::makeInstance('tx_em_Extensions_List', $this);

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
		
		global $TYPO3_CONF_VARS;
		
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
		if(!file_exists($extconftemplatefile)){

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
		

		//check for unknow configuration settings		
		foreach($conf as $k => $v){
			
			if(!isset($constants[$k])){

				throw new InvalidArgumentException(sprintf('No configuration setting with name "%s" for extension "%s"!', $k, $key));
				
			}
						
		}
		

		// get existing configuration
		$arr = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$key]);
		$arr = is_array($arr) ? $arr : array();
		
		
		//fill with missing values
		foreach(array_keys($constants) as $k){
			if(!isset($conf[$k])){
				
				if(isset($arr[$k])){
			
					$conf[$k] = $arr[$k]; 
			
				} else {
				
					if(!empty($constants[$k]['value'])){
						$conf[$k] = $constants[$k]['value'];
					} else {
						$conf[$k] = $constants[$k]['default_value'];
					}
					
				}
			}
		}
			
		// process incoming configuration
		// values are checked against types in $constants
		$tsStyleConfig->ext_procesInput(array('data'=>$conf), array(), $constants, array());
		
		// current configuration is merged with incoming configuration
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
	 * Fetch an extension from repos
	 * 
	 * @param string $key extension key
	 * @return void
	 */
	public function fetchExtension($key, $version='', $location='L', $overwrite = FALSE, $mirror = ''){
		
		$return = array();
		
		if(!tx_em_Tools::importAsType($location)){
			
			if($location === 'G'){
				throw new InvalidArgumentException(sprintf('Global installation (%s) is not allowed!',$location));
			}

			if($location === 'L'){
				throw new InvalidArgumentException(sprintf('Local installation (%s) is not allowed!',$location));
			}

			if($location === 'S'){
				throw new InvalidArgumentException(sprintf('System installation (%s) is not allowed!',$location));
			}
			
			throw new InvalidArgumentException(sprintf('Unknown location "%s"!',$location));

		}		

		if (!$overwrite) {
			$location = ($location==='G' || $location==='S') ? $location : 'L';
			$comingExtPath = tx_em_Tools::typePath($location) . $key . '/';
			if (@is_dir($comingExtPath)) {

				throw new InvalidArgumentException(sprintf('Extension "%s" already exists at "%s"!',$key,$comingExtPath));

			} 
		}

		//some dependencies
		$this->xmlHandler = t3lib_div::makeInstance('tx_em_Tools_XmlHandler');
		$this->extensionList = t3lib_div::makeInstance('tx_em_Extensions_List', $this);
		$this->terConnection = t3lib_div::makeInstance('tx_em_Connection_Ter', $this);
		$this->extensionDetails = t3lib_div::makeInstance('tx_em_Extensions_Details', $this);

		//check extension list
		$this->xmlHandler->searchExtensionsXMLExact($key, '', '', TRUE, TRUE);
		if(!isset($this->xmlHandler->extensionsXML[$key])){
			
			throw new InvalidArgumentException(sprintf('Extension "%s" was not found',$key));

		}

		//get latest version
		if (!strlen($version)) {
			$versions = array_keys($this->xmlHandler->extensionsXML[$key]['versions']);
				// sort version numbers ascending to pick the highest version
			natsort($versions);
			$version = end($versions);
		}
		
		//check if version exists
		if(!isset($this->xmlHandler->extensionsXML[$key]['versions'][$version])){
			
			throw new InvalidArgumentException(sprintf('Version %s of extension "%s" does not exist',$version,$key));
			
		}

		//get mirrors
		$mirrors = array();
		
		$mfile = t3lib_div::tempnam('mirrors');
		$mirrorsFile = t3lib_div::getUrl($GLOBALS['TYPO3_CONF_VARS']['EXT']['em_mirrorListURL'], 0);
		
		if ($mirrorsFile===FALSE) {
			
			t3lib_div::unlink_tempfile($mfile);
			
			throw new RuntimeException('Could not retrieve the list of mirrors!');
			
		} else {
			
			t3lib_div::writeFile($mfile, $mirrorsFile);
			$mirrors = implode('', gzfile($mfile));
			t3lib_div::unlink_tempfile($mfile);
			$mirrors = $this->xmlHandler->parseMirrorsXML($mirrors);
			
		}
		
		if ((!is_array($mirrors)) || (count($mirrors) < 1)) {
				
				throw new RuntimeException('No mirrors found!');
				
		}
		
		
		$mirrorurl = '';
		
		if (!strlen($mirror)) {
				
			$rand = array_rand($mirrors);
			$mirrorurl = 'http://' . $mirrors[$rand]['host'] . $mirrors[$rand]['path'];

		} elseif(isset($mirrors[$mirror])){
			
			$mirrorurl = 'http://' . $mirrors[$mirror]['host'] . $mirrors[$mirror]['path'];
			
		} else {
			
			throw new InvalidArgumentException(sprintf('Mirror "%s" does not exist',$mirror));
			
		}

		$fetchData = $this->terConnection->fetchExtension($key, $version, $this->xmlHandler->extensionsXML[$key]['versions'][$version]['t3xfilemd5'], $mirrorurl);

		if(!is_array($fetchData)){
			
			throw new RuntimeException($fetchData);
		
		}

		$extKey = $fetchData[0]['extKey'];
		
		if(!$extKey){

			throw new RuntimeException($fetchData);

		}

		$return['extKey'] = $extKey;
		$return['version'] = $fetchData[0]['EM_CONF']['version'];
		
		$install = t3lib_div::makeInstance('tx_em_Install', $this);
		$install->setSilentMode(TRUE);
		$content = $install->installExtension($fetchData, $location, null, '', !$overwrite);
		
		return $return;
	}



	/**
	 * Imports extension from file
	 * 
	 * @param string $file path to t3x file
	 * @param string $location where to import the extension. S = typo3/sysext, G = typo3/ext, L = typo3conf/ext
	 * @param boolean $overwrite overwrite the extension if it already exists
	 * @return void
	 */
	public function importExtension($file,$location='L',$overwrite = FALSE){
		
		$return = array();

		if(!is_file($file)){
			
			throw new InvalidArgumentException(sprintf('File "%s" does not exist!',$file));
		
		}
		
		
		if(!tx_em_Tools::importAsType($location)){
			
			if($location === 'G'){
				throw new InvalidArgumentException(sprintf('Global installation (%s) is not allowed!',$location));
			}

			if($location === 'L'){
				throw new InvalidArgumentException(sprintf('Local installation (%s) is not allowed!',$location));
			}

			if($location === 'S'){
				throw new InvalidArgumentException(sprintf('System installation (%s) is not allowed!',$location));
			}

			throw new InvalidArgumentException(sprintf('Unknown location "%s"!',$location));
			
		}		
		
		
		
		$fileContent = t3lib_div::getUrl($file);
		
		if (!$fileContent) {

			throw new InvalidArgumentException(sprintf('File "%s" is empty!',$file));
			
		}

		//some dependencies
		$this->xmlHandler = t3lib_div::makeInstance('tx_em_Tools_XmlHandler');
		$this->extensionList = t3lib_div::makeInstance('tx_em_Extensions_List', $this);
		$this->terConnection = t3lib_div::makeInstance('tx_em_Connection_Ter', $this);
		$this->extensionDetails = t3lib_div::makeInstance('tx_em_Extensions_Details', $this);
		
		$fetchData = $this->terConnection->decodeExchangeData($fileContent);
		
		if(!is_array($fetchData)){
			
			throw new InvalidArgumentException(sprintf('File "%s" is of a wrong format!',$file));
		
		}

		$extKey = $fetchData[0]['extKey'];
		
		if(!$extKey){

			throw new InvalidArgumentException(sprintf('File "%s" is of a wrong format!',$file));
			
		}
		
		$return['extKey'] = $extKey;
		$return['version'] = $fetchData[0]['EM_CONF']['version'];
			
		if (!$overwrite) {
			$location = ($location==='G' || $location==='S') ? $location : 'L';
			$comingExtPath = tx_em_Tools::typePath($location) . $extKey . '/';
			if (@is_dir($comingExtPath)) {

				throw new InvalidArgumentException(sprintf('Extension "%s" already exists at "%s"!',$extKey,$comingExtPath));
				
			} 
		} 

		$install = t3lib_div::makeInstance('tx_em_Install', $this);
		$install->setSilentMode(TRUE);
		$content = $install->installExtension($fetchData, $location, null, $file, !$overwrite);
			
		return $return;
	}


	/**
	 * Check if an extension exists
	 * 
	 * @param string $key extension key
	 * @return void
	 */
	public function exist($key){

		if(!is_object($this->extensionList)){
			$this->extensionList = t3lib_div::makeInstance('tx_em_Extensions_List', $this);
		}

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