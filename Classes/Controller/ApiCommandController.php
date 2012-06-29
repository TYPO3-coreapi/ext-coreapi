<?php

class Tx_Coreapi_Controller_ApiCommandController extends Tx_Extbase_MVC_Controller_CommandController {

	/**
	 * Clear all caches
	 *
	 * @return string
	 */
	public function clearAllCacheCommand() {
		$this->objectManager->get('Tx_Coreapi_Service_CacheApiService')->clearAllCaches();
		return 'OK';
	}

	/**
	 * Clear configuration cache (temp_CACHED_..)
	 *
	 * @return string
	 */
	public function clearConfigurationCacheCommand() {
		$this->objectManager->get('Tx_Coreapi_Service_CacheApiService')->clearConfigurationCache();
		return 'OK';
	}

	/**
	 * Database Compare
	 * @return string
	 */
	public function databaseCompareCommand() {
		$service = $this->objectManager->get('Tx_Coreapi_Service_DatabaseApiService');
		$actions = array(
			$service::ACTION_UPDATE_CLEAR_TABLE => 1,
			$service::ACTION_UPDATE_ADD => 1,
		);
		$result = $service->databaseCompare($actions);
		if (empty($result)) {
			return 'OK';
		} else {
			// TODO
		}
	}

}

?>