<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Tobias Liebig <tobias.liebig@typo3.org>
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

interface Tx_Coreapi_Service_ExtensionApiServiceInterface {

	/**
	 * Get information about an extension
	 *
	 * @param string $extensionKey extension key
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getExtensionInformation($extensionKey);

	/**
	 * Get array of installed extensions
	 *
	 * @param string $type L, S, G or empty (for all)
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getInstalledExtensions($type = '');

	/**
	 * Update the mirrors, using the scheduler task of EXT:em
	 *
	 * @return void
	 * @see tx_em_Tasks_UpdateExtensionList
	 * @throws RuntimeException
	 */
	public function updateMirrors();

	/**
	 * createUploadFolders
	 *
	 * @return array
	 */
	public function createUploadFolders();

	/**
	 * Install (load) an extension
	 *
	 * @param string $extensionKey extension key
	 * @return void
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function installExtension($extensionKey);

	/**
	 * Uninstall (unload) an extension
	 *
	 * @param string $extensionKey extension key
	 * @return void
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function uninstallExtension($extensionKey);

	/**
	 * Configure an extension
	 *
	 * @param string $extensionKey extension key
	 * @param array $extensionConfiguration
	 * @return void
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function configureExtension($extensionKey, $extensionConfiguration = array());

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
	public function fetchExtension($extensionKey, $version = '', $location = 'L', $overwrite = FALSE, $mirror = '');

	/**
	 * Imports extension from file
	 *
	 * @param string $file path to t3x file
	 * @param string $location where to import the extension. S = typo3/sysext, G = typo3/ext, L = typo3conf/ext
	 * @param bool $overwrite overwrite the extension if it already exists
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function importExtension($file, $location = 'L', $overwrite = FALSE);

}

?>