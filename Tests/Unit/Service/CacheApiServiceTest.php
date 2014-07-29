<?php

namespace Etobi\CoreApi\Tests\Unit\Service;

/***************************************************************
 *  Copyright notice
 *
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class CacheApiServiceTest
 * 
 * @package Etobi\CoreApi\Tests\Unit\Service
 * @author  Stefano Kowalke <blueduck@gmx.net>
 * @coversDefaultClass \Etobi\CoreAPI\Service\CacheApiService
 */
class CacheApiServiceTest extends UnitTestCase {
	/**
	 * @var \Etobi\CoreApi\Service\CacheApiService|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $subject
	 */
	protected $subject;

	/**
	 * @var \TYPO3\CMS\Core\DataHandling\DataHandler|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $subject
	 */
	protected $dataHandlerMock;

	/**
	 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $subject
	 */
	protected $backendUserAuthenticationMock;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $subject
	 */
	protected $objectManagerMock;

	/**
	 * Setup the test
	 */
	public function setup() {
		$this->subject = $this->getMock('Etobi\\CoreApi\\Service\\CacheApiService', array('clear_cacheCmd'));
		$this->dataHandlerMock = $this->getMock('TYPO3\\CMS\\Core\\DataHandling\\DataHandler', array('clear_cacheCmd'));
		$this->objectManagerMock = $this->getMock('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->backendUserAuthenticationMock = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication', array('dummy'));
		$this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($this->backendUserAuthenticationMock));

		$this->subject->injectDataHandler($this->dataHandlerMock);
		$this->subject->injectObjectManager($this->objectManagerMock);
		$this->subject->initializeObject();
	}

	/**
	 * @test
	 * @covers ::clearAllCaches
	 */
	public function clearAllCachesClearAllCaches() {
		$this->dataHandlerMock->expects($this->once())->method('clear_cacheCmd')->with('all');
		$this->subject->clearAllCaches();
	}

	/**
	 * @test
	 * @covers ::clearPageCache
	 */
	public function clearPageCacheClearPageCache() {
		$this->dataHandlerMock->expects($this->once())->method('clear_cacheCmd')->with('pages');
		$this->subject->clearPageCache();
	}

	/**
	 * @test
	 * @covers ::clearConfigurationCache
	 */
	public function clearConfigurationCacheClearsConfigurationCache() {
		$this->dataHandlerMock->expects($this->once())->method('clear_cacheCmd')->with('temp_cached');
		$this->subject->clearConfigurationCache();
	}

	/**
	 * @test
	 * @covers ::clearAllExceptPageCache
	 */
	public function clearAllExceptPageCacheClearsAllExceptPageCache() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'] = array(
			0 => 'cache_core',
			1 => 'cache_classes',
			2 => 'cache_hash',
			3 => 'cache_pages',
			4 => 'cache_pagesection',
			5 => 'cache_phpcode',
			6 => 'cache_runtime',
			7 => 'cache_rootline',
			8 => 'l10n',
			9 => 'extbase_object',
			10 => 'extbase_reflection',
		);

		$cacheManager = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		$cacheManager->expects($this->exactly(11))->method('hasCache');
		$GLOBALS['typo3CacheManager'] = $cacheManager;
		$this->subject->clearAllExceptPageCache();
	}

	/**
	 * @test
	 * @covers ::clearSystemCache
	 */
	public function clearSystemCacheClearsSystemCache() {
		$this->dataHandlerMock->expects($this->once())->method('clear_cacheCmd')->with('system');
		$this->subject->clearSystemCache();
	}
}