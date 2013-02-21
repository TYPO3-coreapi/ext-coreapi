## TYPO3 Extension 'coreapi'

The EXT:coreapi should provide a simple to use API for common core features. Goal is to be able to do the most common tasks by CLI instead of doing it in the backend/browser.

Beside of CLI commands, EXT:coreapi provides service classes, which can be used in your own implementation/extension.

Checkout the project website at forge.typo3.org:
	http://forge.typo3.org/projects/show/extension-coreapi

### Tasks
* DatabaseApi
	* databaseCompare
* CacheApi
	* clearAllCaches
	* clearPageCache
	* clearConfigurationCache
* ExtensionApi
	* info
	* listInstalled
	* updateList
* SiteApi
	* info
	* createSysNews
	
#### planned/comming soon

* Backend
	* manage users (list, create, update, delete)
	* lock/unlock the TYPO3 backend
* PageTree
	* print/get
* DataApi
 	* generic list/create/update/delete records (and not doing the plain SQL, but using the DataHandler (aka tcemain)!)
	* getRecordsByPid
	* create a database dump (exclude "temporary" tables like caches, sys_log, ...)
* ReportsApi
	* run/check the reports from the reports module
* ConfigurationApi
	* list, get and set TYPO3 configurations
* ExtensionApi
	* update an extension from TER

### CLI call: ###

Make sure you have a backend user called `_cli_lowlevel`

#### TYPO3 4.7+ ####
If you are using TYPO3 4.7+, you can use the awesome CommandController of Extbase.

This will show you all available calls
	./typo3/cli_dispatch.phpsh extbase help

#### TYPO3 4.6 and below ####
If you are using 4.5 or 4.6, you can still use the extension with a call like
	./typo3/cli_dispatch.phpsh coreapi cache:clearallcaches