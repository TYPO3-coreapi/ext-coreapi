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
	 * some ExtensionManager Objects require public access to these objects
	 */
	/** @var tx_em_Tools_XmlHandler */
	public $xmlHandler;

	/** @var tx_em_Extensions_List */
	public $extensionList;

	/** @var tx_em_Connection_Ter */
	public $terConnection;

	/** @var tx_em_Extensions_Details */
	public $extensionDetails;

	/**
	 * Get information about an extension
	 *
	 * @param string $extensionKey extension key
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getExtensionInformation($extensionKey) {
		if (strlen($extensionKey) === 0) {
			throw new InvalidArgumentException('No extension key given!');
		}
		if (!$GLOBALS['TYPO3_LOADED_EXT'][$extensionKey]) {
			throw new InvalidArgumentException(sprintf('Extension "%s" not found!', $extensionKey));
		}

		include_once(t3lib_extMgm::extPath($extensionKey) . 'ext_emconf.php');
		$information = array(
			'em_conf' => $EM_CONF[''],
			'is_installed' => t3lib_extMgm::isLoaded($extensionKey)
		);

		return $information;
	}

	/**
	 * Get array of installed extensions
	 *
	 * @param string $type L, S, G or empty (for all)
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getInstalledExtensions($type = '') {
		$type = strtoupper($type);
		if (!empty($type) && $type !== 'L' && $type !== 'G' && $type !== 'S') {
			throw new InvalidArgumentException('Only "L", "S", "G" and "" (all) are supported as type');
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
	 * @throws RuntimeException
	 */
	public function updateMirrors() {
		if (t3lib_div::compat_version('6.0.0')) {
			throw new RuntimeException('This feature is not available in TYPO3 versions > 4.7 (yet)!');
		}

		// get repositories
		$repositories = tx_em_Database::getRepositories();
		if (!is_array($repositories)) {
			return;
		}

			// update all repositories
		foreach ($repositories as $repository) {
			/* @var $objRepository tx_em_Repository */
			$objRepository = t3lib_div::makeInstance('tx_em_Repository', $repository['uid']);
			/* @var $objRepositoryUtility tx_em_Repository_Utility */
			$objRepositoryUtility = t3lib_div::makeInstance('tx_em_Repository_Utility', $objRepository);
			$count = $objRepositoryUtility->updateExtList(FALSE);
			unset($objRepository, $objRepositoryUtility);
		}
	}

	/**
	 * createUploadFolders
	 *
	 * @return array
	 */
	public function createUploadFolders() {
		$extensions = $this->getInstalledExtensions();

		$messages = array();
		foreach ($extensions as $extKey => $extInfo) {
			$uploadFolder = PATH_site . tx_em_Tools::uploadFolder($extKey);
			if ($extInfo['uploadfolder'] && !@is_dir($uploadFolder)) {
				t3lib_div::mkdir($uploadFolder);
				$messages[] = 'mkdir ' . $uploadFolder;
				$indexContent = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
					<HTML>
					<HEAD>
					<TITLE></TITLE>
					<META http-equiv=Refresh Content="0; Url=../../">
					</HEAD>
					</HTML>';
				t3lib_div::writeFile($uploadFolder . 'index.html', $indexContent);
			}
		}
		return $messages;
	}


	/**
	 * Install (load) an extension
	 *
	 * @param string $extensionKey extension key
	 * @return void
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function installExtension($extensionKey) {
		// checks if extension exists
		if (!$this->extensionExists($extensionKey)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" does not exist!', $extensionKey));
		}

		// check if extension is already loaded
		if (t3lib_extMgm::isLoaded($extensionKey)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" already installed!', $extensionKey));
		}

		// check if localconf.php is writable
		if (!t3lib_extMgm::isLocalconfWritable()) {
			throw new RuntimeException('Localconf.php is not writeable!');
		}

		$this->initializeExtensionManagerObjects();
		list($currentList,) = $this->extensionList->getInstalledExtensions();

		// add extension to list of loaded extensions
		$newList = $this->extensionList->addExtToList($extensionKey, $currentList);
		if ($newList === -1) {
			throw new RuntimeException(sprintf('Extension "%s" could not be installed!', $extensionKey));
		}

		// update typo3conf/localconf.php
		$install = $this->getEmInstall();
		$install->writeNewExtensionList($newList);

		tx_em_Tools::refreshGlobalExtList();

		// make database changes
		// TODO make this optional
		$install->forceDBupdates($extensionKey, $newList[$extensionKey]);

		// TODO make this optional
		$this->clearCaches();
	}

	/**
	 * Uninstall (unload) an extension
	 *
	 * @param string $extensionKey extension key
	 * @return void
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function uninstallExtension($extensionKey) {
		// check if extension is this extension (coreapi)
		if ($extensionKey == 'coreapi') {
			throw new InvalidArgumentException(sprintf('Extension "%s" cannot be uninstalled!', $extensionKey));
		}

		// checks if extension exists
		if (!$this->extensionExists($extensionKey)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" does not exist!', $extensionKey));
		}

		// check if extension is loaded
		if (!t3lib_extMgm::isLoaded($extensionKey)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" is not installed!', $extensionKey));
		}

		// check if this is a required extension (such as "cms") that cannot be uninstalled
		$requiredExtList = t3lib_div::trimExplode(',', t3lib_extMgm::getRequiredExtensionList());
		if (in_array($extensionKey, $requiredExtList)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" is a required extension and cannot be uninstalled!', $extensionKey));
		}

		// check if localconf.php is writable
		if (!t3lib_extMgm::isLocalconfWritable()) {
			throw new RuntimeException('Localconf.php is not writeable!');
		}

		$this->initializeExtensionManagerObjects();
		list($currentList,) = $this->extensionList->getInstalledExtensions();
		$newList = $this->extensionList->removeExtFromList($extensionKey, $currentList);
		if ($newList === -1) {
			throw new RuntimeException(sprintf('Extension "%s" could not be installed!', $extensionKey));
		}

		// update typo3conf/localconf.php
		$install = $this->getEmInstall();
		$install->writeNewExtensionList($newList);

		tx_em_Tools::refreshGlobalExtList();

		// TODO make this optional
		$this->clearCaches();
	}

	/**
	 * Configure an extension
	 *
	 * @param string $extensionKey extension key
	 * @param array $extensionConfiguration
	 * @return void
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function configureExtension($extensionKey, $extensionConfiguration = array()) {
		global $TYPO3_CONF_VARS;

		if (t3lib_div::compat_version('6.0.0')) {
			throw new RuntimeException('This feature is not available in TYPO3 versions > 4.7 (yet)!');
		}

		// check if extension exists
		if (!$this->extensionExists($extensionKey)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" does not exist!', $extensionKey));
		}

		// check if extension is loaded
		if (!t3lib_extMgm::isLoaded($extensionKey)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" is not installed!', $extensionKey));
		}

		// check if extension can be configured
		$extAbsPath = t3lib_extMgm::extPath($extensionKey);

		$extConfTemplateFile = $extAbsPath . 'ext_conf_template.txt';
		if (!file_exists($extConfTemplateFile)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" has no configuration options!', $extensionKey));
		}

		// checks if conf array is empty
		if (empty($extensionConfiguration)) {
			throw new InvalidArgumentException(sprintf('No configuration for extension "%s"!', $extensionKey));
		}

		// Load tsStyleConfig class and parse configuration template:
		$extRelPath = t3lib_extmgm::extRelPath($extensionKey);

		$tsStyleConfig = t3lib_div::makeInstance('t3lib_tsStyleConfig');
		$tsStyleConfig->doNotSortCategoriesBeforeMakingForm = TRUE;
		$constants = $tsStyleConfig->ext_initTSstyleConfig(
			t3lib_div::getUrl($extConfTemplateFile),
			$extRelPath,
			$extAbsPath,
			$GLOBALS['BACK_PATH']
		);

		// check for unknown configuration settings
		foreach ($extensionConfiguration as $key => $value) {
			if (!isset($constants[$key])) {
				throw new InvalidArgumentException(sprintf('No configuration setting with name "%s" for extension "%s"!', $key, $extensionKey));
			}
		}

		// get existing configuration
		$configurationArray = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$extensionKey]);
		$configurationArray = is_array($configurationArray) ? $configurationArray : array();

		// fill with missing values
		foreach (array_keys($constants) as $key) {
			if (!isset($extensionConfiguration[$key])) {
				if (isset($configurationArray[$key])) {
					$extensionConfiguration[$key] = $configurationArray[$key];
				} else {
					if (!empty($constants[$key]['value'])) {
						$extensionConfiguration[$key] = $constants[$key]['value'];
					} else {
						$extensionConfiguration[$key] = $constants[$key]['default_value'];
					}
				}
			}
		}

		// process incoming configuration
		// values are checked against types in $constants
		$tsStyleConfig->ext_procesInput(array('data' => $extensionConfiguration), array(), $constants, array());

		// current configuration is merged with incoming configuration
		$configurationArray = $tsStyleConfig->ext_mergeIncomingWithExisting($configurationArray);

		// write configuration to typo3conf/localconf.php
		$install = $this->getEmInstall();
		$install->writeTsStyleConfig($extensionKey, $configurationArray);

		// TODO make this optional
		$this->clearCaches();
	}

	/**
	 * Fetch an extension from TER
	 *
	 * @param $extensionKey
	 * @param string $version
	 * @param string $location
	 * @param bool $overwrite
	 * @param string $mirror
	 * @return array
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function fetchExtension($extensionKey, $version = '', $location = 'L', $overwrite = FALSE, $mirror = '') {
		if (t3lib_div::compat_version('6.0.0')) {
			throw new RuntimeException('This feature is not available in TYPO3 versions > 4.7 (yet)!');
		}

		$return = array();
		if (!tx_em_Tools::importAsType($location)) {
			if ($location === 'G') {
				throw new InvalidArgumentException(sprintf('Global installation (%s) is not allowed!', $location));
			}
			if ($location === 'L') {
				throw new InvalidArgumentException(sprintf('Local installation (%s) is not allowed!', $location));
			}
			if ($location === 'S') {
				throw new InvalidArgumentException(sprintf('System installation (%s) is not allowed!', $location));
			}
			throw new InvalidArgumentException(sprintf('Unknown location "%s"!', $location));
		}

		if (!$overwrite) {
			$location = ($location === 'G' || $location === 'S') ? $location : 'L';
			$comingExtPath = tx_em_Tools::typePath($location) . $extensionKey . '/';
			if (@is_dir($comingExtPath)) {
				throw new InvalidArgumentException(sprintf('Extension "%s" already exists at "%s"!', $extensionKey, $comingExtPath));
			}
		}

		// check extension list
		$this->initializeExtensionManagerObjects();
		$this->xmlHandler->searchExtensionsXMLExact($extensionKey, '', '', TRUE, TRUE);
		if (!isset($this->xmlHandler->extensionsXML[$extensionKey])) {
			throw new InvalidArgumentException(sprintf('Extension "%s" was not found', $extensionKey));
		}

		// get latest version
		if (!strlen($version)) {
			$versions = array_keys($this->xmlHandler->extensionsXML[$extensionKey]['versions']);
			// sort version numbers ascending to pick the highest version
			natsort($versions);
			$version = end($versions);
		}

		// check if version exists
		if (!isset($this->xmlHandler->extensionsXML[$extensionKey]['versions'][$version])) {
			throw new InvalidArgumentException(sprintf('Version %s of extension "%s" does not exist', $version, $extensionKey));
		}

		// get mirrors
		$mirrors = array();
		$mirrorsTmpFile = t3lib_div::tempnam('mirrors');
		$mirrorsFile = t3lib_div::getUrl($GLOBALS['TYPO3_CONF_VARS']['EXT']['em_mirrorListURL'], 0);

		if ($mirrorsFile === FALSE) {
			t3lib_div::unlink_tempfile($mirrorsTmpFile);
			throw new RuntimeException('Could not retrieve the list of mirrors!');
		} else {
			t3lib_div::writeFile($mirrorsTmpFile, $mirrorsFile);
			$mirrorsXml = implode('', gzfile($mirrorsTmpFile));
			t3lib_div::unlink_tempfile($mirrorsTmpFile);
			$mirrors = $this->xmlHandler->parseMirrorsXML($mirrorsXml);
		}

		if ((!is_array($mirrors)) || (count($mirrors) < 1)) {
			throw new RuntimeException('No mirrors found!');
		}

		$mirrorUrl = '';
		if (!strlen($mirror)) {
			$rand = array_rand($mirrors);
			$mirrorUrl = 'http://' . $mirrors[$rand]['host'] . $mirrors[$rand]['path'];
		} elseif (isset($mirrors[$mirror])) {
			$mirrorUrl = 'http://' . $mirrors[$mirror]['host'] . $mirrors[$mirror]['path'];
		} else {
			throw new InvalidArgumentException(sprintf('Mirror "%s" does not exist', $mirror));
		}

		$fetchData = $this->terConnection->fetchExtension($extensionKey, $version, $this->xmlHandler->extensionsXML[$extensionKey]['versions'][$version]['t3xfilemd5'], $mirrorUrl);
		if (!is_array($fetchData)) {
			throw new RuntimeException($fetchData);
		}

		$extKey = $fetchData[0]['extKey'];
		if (!$extKey) {
			throw new RuntimeException($fetchData);
		}

		$return['extKey'] = $extKey;
		$return['version'] = $fetchData[0]['EM_CONF']['version'];

		// TODO make this optional
		$install = $this->getEmInstall();
		$content = $install->installExtension($fetchData, $location, null, '', !$overwrite);

		return $return;
	}

	/**
	 * Imports extension from file
	 *
	 * @param string $file path to t3x file
	 * @param string $location where to import the extension. S = typo3/sysext, G = typo3/ext, L = typo3conf/ext
	 * @param bool $overwrite overwrite the extension if it already exists
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function importExtension($file, $location = 'L', $overwrite = FALSE) {
		$return = array();
		if (!is_file($file)) {
			throw new InvalidArgumentException(sprintf('File "%s" does not exist!', $file));
		}

		if (!tx_em_Tools::importAsType($location)) {
			if ($location === 'G') {
				throw new InvalidArgumentException(sprintf('Global installation (%s) is not allowed!', $location));
			}
			if ($location === 'L') {
				throw new InvalidArgumentException(sprintf('Local installation (%s) is not allowed!', $location));
			}
			if ($location === 'S') {
				throw new InvalidArgumentException(sprintf('System installation (%s) is not allowed!', $location));
			}
			throw new InvalidArgumentException(sprintf('Unknown location "%s"!', $location));
		}

		$fileContent = t3lib_div::getUrl($file);
		if (!$fileContent) {
			throw new InvalidArgumentException(sprintf('File "%s" is empty!', $file));
		}

		$fetchData = $this->terConnection->decodeExchangeData($fileContent);
		if (!is_array($fetchData)) {
			throw new InvalidArgumentException(sprintf('File "%s" is of a wrong format!', $file));
		}

		$extensionKey = $fetchData[0]['extKey'];
		if (!$extensionKey) {
			throw new InvalidArgumentException(sprintf('File "%s" is of a wrong format!', $file));
		}

		$return['extKey'] = $extensionKey;
		$return['version'] = $fetchData[0]['EM_CONF']['version'];

		if (!$overwrite) {
			$location = ($location === 'G' || $location === 'S') ? $location : 'L';
			$destinationPath = tx_em_Tools::typePath($location) . $extensionKey . '/';
			if (@is_dir($destinationPath)) {
				throw new InvalidArgumentException(sprintf('Extension "%s" already exists at "%s"!', $extensionKey, $destinationPath));
			}
		}

		$install = $this->getEmInstall();
		$content = $install->installExtension($fetchData, $location, null, $file, !$overwrite);

		return $return;
	}


	/**
	 * Check if an extension exists
	 *
	 * @param string $extensionKey extension key
	 * @return void
	 */
	protected function extensionExists($extensionKey) {
		$this->initializeExtensionManagerObjects();
		list($list,) = $this->extensionList->getInstalledExtensions();
		$extensionExists = FALSE;
		foreach ($list as $values) {
			if ($values['extkey'] === $extensionKey) {
				$extensionExists = TRUE;
				break;
			}
		}
		return $extensionExists;
	}

	/**
	 * initialize ExtensionManager Objects
	 */
	protected function initializeExtensionManagerObjects() {
		$this->xmlHandler = t3lib_div::makeInstance('tx_em_Tools_XmlHandler');
		$this->extensionList = t3lib_div::makeInstance('tx_em_Extensions_List', $this);
		$this->terConnection = t3lib_div::makeInstance('tx_em_Connection_Ter', $this);
		$this->extensionDetails = t3lib_div::makeInstance('tx_em_Extensions_Details', $this);
	}

	/**
	 * @return tx_em_Install
	 */
	protected function getEmInstall() {
		$install = t3lib_div::makeInstance('tx_em_Install', $this);
		$install->setSilentMode(TRUE);
		return $install;
	}

	/**
	 * Clear the caches
	 */
	protected function clearCaches() {
		$cacheApiService = t3lib_div::makeInstance('Tx_Coreapi_Service_CacheApiService');
		$cacheApiService->initializeObject();
		$cacheApiService->clearAllCaches();
	}
}

?>