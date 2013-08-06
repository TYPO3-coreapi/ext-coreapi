<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alexander Opitz <opitz@pluspol.info>
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
class Tx_Coreapi_Service_ExtensionApi60Service extends Tx_Coreapi_Service_ExtensionApiService
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get array of installed extensions
     *
     * @param string $type L, S, G or empty (for all)
     * @return array
     * @throws InvalidArgumentException
     */
    public function getInstalledExtensions($type = '')
    {
        $type = strtoupper($type);

        if (!empty($type)) {
            switch ($type) {
                case 'L':
                    $type = 'Local';
                    break;
                case 'S':
                    $type = 'System';
                    break;
                case 'G':
                    $type = 'Global';
                    break;
                default:
                    throw new InvalidArgumentException('Only "L", "S", "G" and "" (all) are supported as type');
            }
        }

        $list = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
        $extensions = $list->getAvailableAndInstalledExtensionsWithAdditionalInformation();

        if (!empty($type)) {
            foreach ($extensions as $key => $extension) {
                if ($type !== $extension['type']) {
                    unset($extensions[$key]);
                }
            }
        }

        ksort($extensions);
        return $extensions;
    }

    /**
     * createUploadFolders
     *
     * @return array
     */
    public function createUploadFolders()
    {
        $extensions = $this->getInstalledExtensions();

        $fileHandlingUtility = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility');
        foreach ($extensions as $key => $extension) {
            $extension['key'] = $key;
            $fileHandlingUtility->ensureConfiguredDirectoriesExist($extension);
        }
        return array(
            'done with \\TYPO3\\CMS\\Extensionmanager\\Utility\\FileHandlingUtility->ensureConfiguredDirectoriesExist'
        );
    }


    /**
     * Install (load) an extension
     *
     * @param string $extensionKey extension key
     * @return void
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function installExtension($extensionKey)
    {
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

        $installUtility = $this->getInstallUtility();
        $installUtility->install($extensionKey);
    }

    /**
     * Uninstall (unload) an extension
     *
     * @param string $extensionKey extension key
     * @return void
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function uninstallExtension($extensionKey)
    {
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

        $installUtility = $this->getInstallUtility();
        $installUtility->uninstall($extensionKey);
    }

    /**
     * Check if an extension exists
     *
     * @param string $extensionKey extension key
     * @return void
     */
    protected function extensionExists($extensionKey)
    {
        return $this->getInstallUtility()->isAvailable($extensionKey);
    }

    protected function getInstallUtility()
    {
        return $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility');
    }
}
