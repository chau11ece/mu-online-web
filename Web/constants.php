<?PHP

	define('DMNCMS',		true);
	define('DS',			DIRECTORY_SEPARATOR);
	define('BASEDIR',		realpath(dirname(__FILE__)).DS);
	define('SYSTEM_PATH',	BASEDIR.'system');
	define('APP_PATH',		BASEDIR.'application');
	define('INSTALLED',		true);


	/*
	 *---------------------------------------------------------------
	 * Sql Server-Configuration
	 *---------------------------------------------------------------
	 *
	 *     The following constants define the logins which should be used to access the database.
	 *
	 */

	define('HOST',		getenv('DB_HOST') ?: 'mu-db-dev:1433');
	define('USER',		getenv('DB_USER') ?: 'sa');
	define('PASS',		getenv('DB_PASS') ?: 'Abcd@1234');
	define('WEB_DB',	getenv('DB_NAME') ?: 'MuOnline');
	define('PAGE_START', microtime(true));
	define('LOG_SQL',	false);
	define('DRIVER', 	'pdo_dblib');
	define('MD5',		2);
	define('SOCKET_LIBRARY',1);
	define('ENVIRONMENT', 'production');


	/*
	 *---------------------------------------------------------------
	 * Mu Server Version
	 *---------------------------------------------------------------
	 *
	 *     Define MuOnline Server Version
	 * 		- version 0 - below season 1
	 * 		- version 1 - season 1
	 * 		- version 2 - season 2 and higher
	 * 		- version 3 - ex700 and higher
	 * 		- version 4 - season 8 and higher
	 * 		- version 5 - season 10 and higher
	 * 		- version 6 - season 11 and higher
	 * 		- version 7 - season 12 and higher
	 * 		- version 8 - season 13 and higher
	 * 		- version 9 - season 14 and higher
	 * 		- version 10 - season 15 and higher
	 *
	 */

	define('MU_VERSION',		4);


	/*
	 *---------------------------------------------------------------
	 * Admin CP
	 *---------------------------------------------------------------
	 *
	 */

	define('USERNAME',	'admin');
	define('PASSWORD', 	'admin123');
	define('PINCODE', 	'123456');
	define('SECURITY_SALT','5HOmogLIAs');
	define('ACP_IP_CHECK',false);
	define('ACP_IP_WHITE_LIST','127.0.0.1');


