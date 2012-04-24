## TYPO3 Extension 'coreapi' ##

The EXT:coreapi should provide a simple to use API for common core features. Useable by injectable ServiceClasses or by the CLI dispatcher.

Checkout the project website at forge.typo3.org:
	http://forge.typo3.org/projects/show/extension-coreapi

### Tasks ###

* Cache
	* clearAllCaches
	* clearPageCache
	* clearConfigurationCache

### Sample CLI call: ###
	
	./typo3/cli_dispatch.phpsh extbase api:clearAllCache

