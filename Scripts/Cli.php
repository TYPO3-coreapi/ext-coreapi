<?php
if ((TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI) && basename(PATH_thisScript) == 'cli_dispatch.phpsh') {
	$dispatcher = t3lib_div::makeInstance('Tx_Coreapi_Cli_Dispatcher');
	$dispatcher->start();
} else {
	die('This script must be included by the "CLI module dispatcher"');
}

?>
