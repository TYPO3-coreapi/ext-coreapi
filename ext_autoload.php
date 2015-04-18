<?php

$extensionPath = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('coreapi');
$extensionClassesPath = $extensionPath . 'Classes/';

return array(
	'Etobi\CoreAPI\Command\BackendApiCommandController' => $extensionClassesPath . 'Command/BackendApiCommandController.php',
	'Etobi\CoreAPI\Command\DatabaseApiCommandController' => $extensionClassesPath . 'Command/DatabaseApiCommandController.php',
	'Etobi\CoreAPI\Command\SiteApiCommandController' => $extensionClassesPath . 'Command/SiteApiCommandController.php',
	'Etobi\CoreAPI\Command\CacheApiCommandController' => $extensionClassesPath . 'Command/CacheApiCommandController.php',
	'Etobi\CoreAPI\Command\ExtensionApiCommandController' => $extensionClassesPath . 'Command/ExtensionApiCommandController.php',
	'Etobi\CoreAPI\Command\ConfigurationApiCommandController' => $extensionClassesPath . 'Command/ConfigurationApiCommandController.php',
	'Etobi\CoreAPI\Service\CacheApiService' => $extensionClassesPath . 'Service/CacheApiService.php',
	'Etobi\CoreAPI\Service\SiteApiService' => $extensionClassesPath . 'Service/SiteApiService.php',
	'Etobi\CoreAPI\Service\DatabaseApiService' => $extensionClassesPath . 'Service/DatabaseApiService.php',
	'Etobi\CoreAPI\Service\DatabaseComparator' => $extensionClassesPath . 'Service/DatabaseComparator.php',
	'Etobi\CoreAPI\Service\DatabaseCompareDry' => $extensionClassesPath . 'Service/DatabaseCompareDry.php',
	'Etobi\CoreAPI\Service\DatabaseCompareReal' => $extensionClassesPath . 'Service/DatabaseCompareReal.php',
	'Etobi\CoreAPI\Service\ExtensionApiService' => $extensionClassesPath . 'Service/ExtensionApiService.php'
);
