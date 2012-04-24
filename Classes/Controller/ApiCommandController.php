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
}

?>