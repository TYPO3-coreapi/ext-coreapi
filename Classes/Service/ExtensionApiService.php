<?php
namespace Etobi\CoreAPI\Service;

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
use InvalidArgumentException;
use RuntimeException;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository;
use TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository;
use TYPO3\CMS\Extensionmanager\Service\ExtensionManagementService;
use TYPO3\CMS\Extensionmanager\Utility\FileHandlingUtility;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Extensionmanager\Utility\Repository\Helper;

/**
 * Extension API service
 *
 * @author Georg Ringer <georg.ringer@cyberhouse.at>
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @package Etobi\CoreAPI\Service\SiteApiService
 */
class ExtensionApiService {

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\Connection\TerUtility $terConnection
	 */
	public $terConnection;

	/**
	 * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager $configurationManager
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility $extensionList
	 */
	public $listUtility;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\InstallUtility $installUtility
	 */
	protected $installUtility;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Repository\RepositoryRepository $repositoryRepository
	 */
	protected $repositoryRepository;

	/**
	 * @var Helper $repositoryHelper
	 */
	protected $repositoryHelper;

	/**
	 * @var ExtensionRepository $extensionRepository
	 */
	protected $extensionRepository;

	/**
	 * @var ExtensionManagementService $extensionManagementService
	 */
	protected $extensionManagementService;

	/**
	 * @var ObjectManagerInterface $objectManager
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\FileHandlingUtility $fileHandlingUtility
	 */
	protected $fileHandlingUtility;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\EmConfUtility $emConfUtility
	 */
	protected $emConfUtility;

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Utility\FileHandlingUtility $fileHandlingUtility
	 *
	 * @return void
	 */
	public function injectFileHandlingUtility(FileHandlingUtility $fileHandlingUtility) {
		$this->fileHandlingUtility = $fileHandlingUtility;
	}

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Utility\InstallUtility $installUtility
	 *
	 * @return void
	 */
	public function injectInstallUtility(InstallUtility $installUtility) {
		$this->installUtility = $installUtility;
	}

	/**
	 * @param RepositoryRepository $repositoryRepository
	 *
	 * @return void
	 */
	public function injectRepositoryRepository(RepositoryRepository $repositoryRepository) {
		$this->repositoryRepository = $repositoryRepository;
	}

	/**
	 * @param Helper $repositoryHelper
	 *
	 * @return void
	 */
	public function injectRepositoryHelper(Helper $repositoryHelper) {
		$this->repositoryHelper = $repositoryHelper;
	}

	/**
	 * @param ExtensionRepository $extensionRepository
	 *
	 * @return void
	 */
	public function injectExtensionRepository(ExtensionRepository $extensionRepository){
		$this->extensionRepository = $extensionRepository;
	}

	/**
	 * @param ExtensionManagementService $extensionManagementService
	 *
	 * @return void
	 */
	public function injectExtensionManagementService(ExtensionManagementService $extensionManagementService) {
		$this->extensionManagementService = $extensionManagementService;
	}

	/**
	 * @param ObjectManagerInterface $objectManager
	 *
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * The constructor
	 */
	public function __construct() {
		$this->configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Configuration\\ConfigurationManager');
	}

	/**
	 * Get information about an extension.
	 *
	 * @param string $extensionKey extension key
	 *
	 * @throws InvalidArgumentException
	 * @return array
	 */
	public function getExtensionInformation($extensionKey) {
		if (strlen($extensionKey) === 0) {
			throw new InvalidArgumentException('No extension key given!');
		}

		$this->checkExtensionExists($extensionKey);

		$extensions = $this->listExtensions();

		$information = array(
			'em_conf' => $extensions[$extensionKey],
			'is_installed' => $this->installUtility->isLoaded($extensionKey)
		);

		return $information;
	}

	/**
	 * Get array of installed extensions.
	 *
	 * @param string $type Local, System, Global or empty (for all)
	 *
	 * @throws InvalidArgumentException
	 * @return array
	 */
	public function listExtensions($type = '') {
		$type = ucfirst(strtolower($type));
		if (!empty($type) && $type !== 'Local' && $type !== 'Global' && $type !== 'System') {
			throw new InvalidArgumentException('Only "Local", "System", "Global" and "" (all) are supported as type');
		}

		$this->initializeExtensionManagerObjects();

		// TODO: Make listUtlity local var
		$extensions = $this->listUtility->getAvailableExtensions();

		$list = array();

		foreach ($extensions as $key => $extension) {
			if ((!empty($type) && $type !== $extension['type'])
					|| (!$this->installUtility->isLoaded($extension['key']))
			) {
				continue;
			}

			// TODO: Make emConfUtility local var
			$configuration = $this->emConfUtility->includeEmConf($extension);
			if (!empty($configuration)) {
				$list[$key] = $configuration;
			}
		}
		ksort($list);

		return $list;
	}

	/**
	 * Update the mirrors, using the scheduler task of EXT:em.
	 *
	 * @throws RuntimeException
	 * @return boolean
	 */
	public function updateMirrors() {
		$result = FALSE;
		$repositories = $this->repositoryRepository->findAll();

		// update all repositories
		foreach ($repositories as $repository) {
			$this->repositoryHelper->setRepository($repository);
			$result = $this->repositoryHelper->updateExtList();
			unset($objRepository, $this->repositoryHelper);
		}

		return $result;
	}

	/**
	 * Install (load) an extension.
	 *
	 * @param string $extensionKey extension key
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function installExtension($extensionKey) {
		$this->checkExtensionExists($extensionKey);

		$this->installUtility->install($extensionKey);
	}

	/**
	 * Uninstall (unload) an extension.
	 *
	 * @param string $extensionKey extension key
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function uninstallExtension($extensionKey) {
		if ($extensionKey === 'coreapi') {
			throw new InvalidArgumentException('Extension "coreapi" cannot be uninstalled!');
		}

		$this->checkExtensionExists($extensionKey);
		$this->checkExtensionLoaded($extensionKey);

		$this->installUtility->uninstall($extensionKey);
	}


	/**
	 * Configure an extension.
	 *
	 * @param string $extensionKey              The extension key
	 * @param array  $newExtensionConfiguration
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function configureExtension($extensionKey, $newExtensionConfiguration = array()) {
		$this->checkExtensionExists($extensionKey);
		$this->checkExtensionLoaded($extensionKey);

		// checks if conf array is empty
		if (empty($newExtensionConfiguration)) {
			throw new InvalidArgumentException(sprintf('No configuration provided for extension "%s"!', $extensionKey));
		}

		// check if extension can be configured
		$extAbsPath = $this->getExtensionPath($extensionKey);
		$extConfTemplateFile = $extAbsPath . 'ext_conf_template.txt';
		if (!file_exists($extConfTemplateFile)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" has no configuration options!', $extensionKey));
		}

		/** @var $configurationUtility \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility */
		$configurationUtility = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\ConfigurationUtility');
		// get existing configuration
		$currentExtensionConfig = $configurationUtility->getCurrentConfiguration($extensionKey);

		// check for unknown configuration settings
		foreach ($newExtensionConfiguration as $key => $_) {
			if (!isset($currentExtensionConfig[$key])) {
				throw new InvalidArgumentException(sprintf('No configuration setting with name "%s" for extension "%s"!', $key, $extensionKey));
			}
		}

		// fill with missing values
		$newExtensionConfiguration = $this->mergeNewExtensionConfiguratonWithCurrentConfiguration(
				$newExtensionConfiguration,
				$currentExtensionConfig
		);

		// write configuration to typo3conf/LocalConfiguration.php
		$configurationUtility->writeConfiguration($newExtensionConfiguration, $extensionKey);
	}

	/**
	 * Fetch an extension from TER.
	 *
	 * @param string $extensionKey     The extension key
	 * @param string $location         Where to import the extension. System = typo3/sysext, Global = typo3/ext, Local = typo3conf/ext
	 * @param bool   $overwrite        Overwrite the extension if it already exists
	 * @param int    $mirror           The mirror to fetch the extension from
	 *
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	public function fetchExtension($extensionKey, $version = '', $location = 'Local', $overwrite = FALSE, $mirror = -1) {
		if (!is_numeric($mirror)) {
			throw new InvalidArgumentException('Option --mirror must be a number. Run the command extensionapi:listmirrors to get the list of all available repositories');
		}

		if ($version === '') {
			$extension = $this->extensionRepository->findHighestAvailableVersion($extensionKey);
			if ($extension === NULL) {
				throw new InvalidArgumentException(sprintf('Extension "%s" was not found on TER', $extensionKey));
			}
		} else {
			$extension = $this->extensionRepository->findOneByExtensionKeyAndVersion($extensionKey, $version);
			if ($extension === NULL) {
				throw new InvalidArgumentException(sprintf('Version %s of extension "%s" does not exist', $version, $extensionKey));
			}
		}

		if (!$overwrite) {
			$comingExtPath = $this->fileHandlingUtility->getExtensionDir($extensionKey, $location);
			if (@is_dir($comingExtPath)) {
				throw new InvalidArgumentException(sprintf('Extension "%s" already exists at "%s"!', $extensionKey, $comingExtPath));
			}
		}

		$mirrors = $this->repositoryHelper->getMirrors();

		if ($mirrors === NULL) {
			throw new RuntimeException('No mirrors found!');
		}

		if ($mirror === -1) {
			$mirrors->setSelect();
		} elseif ($mirror > 0 && $mirror <= count($mirrors->getMirrors())) {
			$mirrors->setSelect($mirror);
		} else {
			throw new InvalidArgumentException(sprintf('Mirror "%s" does not exist', $mirror));
		}

		/**
		 * @var \TYPO3\CMS\Extensionmanager\Utility\DownloadUtility $downloadUtility
		 */
		$downloadUtility = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\DownloadUtility');
		$downloadUtility->setDownloadPath($location);

		$this->extensionManagementService->downloadMainExtension($extension);

		$return = array();
		$extensionDir = $this->fileHandlingUtility->getExtensionDir($extensionKey, $location);
		if (is_dir($extensionDir)) {
			$return['main']['extKey'] = $extension->getExtensionKey();
			$return['main']['version'] = $extension->getVersion();
		} else {
			throw new RuntimeException(
					sprintf('Extension "%s" version %s could not installed!', $extensionKey, $extension->getVersion())
			);
		}

		return $return;
	}

	/**
	 * Lists the possible mirrors
	 *
	 * @return array
	 */
	public function listMirrors() {
		/** @var $repositoryHelper Helper */
		$repositoryHelper = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\Repository\\Helper');
		$mirrors = $repositoryHelper->getMirrors();

		return $mirrors->getMirrors();
	}

	/**
	 * Extracts and returns the file content of the given file
	 *
	 * @param string $file The file with file path
	 *
	 * @return array
	 */
	protected function getFileContentFromUrl($file) {
		return GeneralUtility::getUrl($file);
	}

	/**
	 * Imports extension from file.
	 *
	 * @param string $file      Path to t3x file
	 * @param string $location  Where to import the extension. System = typo3/sysext, Global = typo3/ext, Local = typo3conf/ext
	 * @param bool   $overwrite Overwrite the extension if it already exists
	 *
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 * @return array The extension data
	 */
	public function importExtension($file, $location = 'Local', $overwrite = FALSE) {
		if (!is_file($file)) {
			throw new InvalidArgumentException(sprintf('File "%s" does not exist!', $file));
		}

		$this->checkInstallLocation($location);

		$uploadExtensionFileController = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Controller\\UploadExtensionFileController');

		$filename = pathinfo($file, PATHINFO_BASENAME);
		$return = $uploadExtensionFileController->extractExtensionFromFile($file, $filename, $overwrite, FALSE);

		return $return;
	}

	/**
	 * Checks if the function exists in the system
	 *
	 * @param string $extensionKey The extension key
	 *
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	protected function checkExtensionExists($extensionKey) {
		if (!$this->installUtility->isAvailable($extensionKey)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" does not exist!', $extensionKey));
		}
	}

	/**
	 * Check if an extension is loaded.
	 *
	 * @param string $extensionKey The extension key
	 *
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	protected function checkExtensionLoaded($extensionKey) {
		if (!$this->installUtility->isLoaded($extensionKey)) {
			throw new InvalidArgumentException(sprintf('Extension "%s" is not installed!', $extensionKey));
		}
	}

	/**
	 * Returns the absolute extension path.
	 * Wrapper around the static method. This makes the method mockable.
	 *
	 * @param string $extensionKey The extension key
	 *
	 * @return string
	 */
	protected function getExtensionPath($extensionKey) {
		return ExtensionManagementUtility::extPath($extensionKey);
	}

	/**
	 * Add missing values from current configuration to the new configuration
	 *
	 * @param array $newExtensionConfiguration The new configuration which was provided as argument
	 * @param array $currentExtensionConfig    The current configuration of the extension
	 *
	 * @return array The merged configuration
	 */
	protected function mergeNewExtensionConfiguratonWithCurrentConfiguration($newExtensionConfiguration, $currentExtensionConfig) {
		foreach (array_keys($currentExtensionConfig) as $key) {
			if (!isset($newExtensionConfiguration[$key])) {
				if (!empty($currentExtensionConfig[$key]['value'])) {
					$newExtensionConfiguration[$key] = $currentExtensionConfig[$key]['value'];
				} else {
					$newExtensionConfiguration[$key] = $currentExtensionConfig[$key]['default_value'];
				}
			}
		}

		return $newExtensionConfiguration;
	}

	/**
	 * Checks if the extension is able to install at the demanded location
	 *
	 * @param string $location            The location
	 * @param array  $allowedInstallTypes The allowed locations
	 *
	 * @return boolean
	 * @throws \InvalidArgumentException
	 */
	protected function checkInstallLocation($location) {
		$allowedInstallTypes = Extension::returnAllowedInstallTypes();
		$location = ucfirst(strtolower($location));

		if (!in_array($location, $allowedInstallTypes)) {
			if ($location === 'Global') {
				throw new InvalidArgumentException('Global installation is not allowed!');
			}
			if ($location === 'Local') {
				throw new InvalidArgumentException('Local installation is not allowed!');
			}
			if ($location === 'System') {
				throw new InvalidArgumentException('System installation is not allowed!');
			}
			throw new InvalidArgumentException(sprintf('Unknown location "%s"!', $location));
		}
	}

	/**
	 * Initialize ExtensionManager Objects.
	 *
	 * @return void
	 */
	protected function initializeExtensionManagerObjects() {
		$this->listUtility = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		$this->emConfUtility = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\EmConfUtility');
	}

	/**
	 * Clear the caches.
	 *
	 * @return void
	 */
	protected function clearCaches() {
		$cacheApiService = GeneralUtility::makeInstance('Etobi\\CoreAPI\\Service\\CacheApiService');
		$cacheApiService->initializeObject();
		$cacheApiService->clearAllCaches();
	}
}
