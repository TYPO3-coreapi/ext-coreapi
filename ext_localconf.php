<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (class_exists('Tx_Extbase_MVC_Controller_CommandController')) {
	if (TYPO3_MODE === 'BE') {
			// Register commands (available since 4.6)
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Tx_Coreapi_Command_DatabaseApiCommandController';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Tx_Coreapi_Command_CacheApiCommandController';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Tx_Coreapi_Command_SiteApiCommandController';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Tx_Coreapi_Command_ExtensionApiCommandController';
	}
} else {
		// Register the CLI dispatcher
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array(
		'EXT:' . $_EXTKEY . '/Scripts/Cli.php', '_CLI_lowlevel'
	);
}

?>