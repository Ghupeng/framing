<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2018/09/03
 * Time: 16:04:02
 * By: Service.php
 */
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
//use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
//use Phalcon\Flash\Direct as Flash;

class Service {
	/**
	 * @param $di
	 */
	public static function init(&$di) {
		/**
		 * Setting up the view component
		 */
		$di->setShared('view', function () {
				$config = \framing\Library\ConfigLibrary::get('config','application');

				$view = new View();
				$view->setDI($this);
				$view->setViewsDir($config->viewsDir);

				$view->registerEngines([
						'.volt' => function ($view) {
						$config = \framing\Library\ConfigLibrary::get('config','application');

						$volt = new VoltEngine($view, $this);

						$volt->setOptions([
							'compiledPath' => $config->cacheDir,
							'compiledSeparator' => '_'
						]);

						return $volt;
					},
					'.phtml' => PhpEngine::class

				]);

				return $view;
		});


		/**
		 * Database connection is created based in the parameters defined in the configuration file
		 */
		$di->setShared('db', function () {
			$config = \framing\Library\ConfigLibrary::get('config','database');

			$class = 'Phalcon\Db\Adapter\Pdo\\' . $config->adapter;
			$params = [
				'host'     => $config->host,
				'username' => $config->username,
				'password' => $config->password,
				'dbname'   => $config->dbname,
				'charset'  => $config->charset
			];

			if ($config->adapter == 'Postgresql') {
				unset($params['charset']);
			}

			$connection = new $class($params);

			return $connection;
		});

		/**
		 * If the configuration specify the use of metadata adapter use it or use memory otherwise
		 */
		$di->setShared('modelsMetadata', function () {
			return new MetaDataAdapter();
		});


		/**
		 * Start the session the first time some component request the session service
		 */
		$di->setShared('session', function () {
			$session = new SessionAdapter();
			$session->start();

			return $session;
		});

	}
}

