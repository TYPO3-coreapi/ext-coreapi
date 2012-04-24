<?php

class Tx_Coreapi_Service_CacheApiService {

	/**
	 * @var t3lib_TCEmain
	 */
	protected $tce;

	/**
	 *
	 */
	public function initializeObject() {
		$this->tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$this->tce->start(Array(), Array());
	}

	/**
	 * Clear all caches
	 */
	public function clearAllCaches() {
		$this->tce->clear_cacheCmd('all');
	}

	/**
	 *
	 */
	public function clearPageCache() {
		$this->tce->clear_cacheCmd('pages');
	}

	/**
	 *
	 */
	public function clearConfigurationCache() {
		$this->tce->clear_cacheCmd('temp_cached');
	}

}