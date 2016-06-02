<?php

import("lib.mongo.RMongo");

class MServer {
	private $_mongoName = null;
	private $_mongoUri = "127.0.0.1";
	private $_mongoDb;
	private $_mongoOptions = array();
	private $_mongoAuth = true;
	private $_uiHideSystemCollections = false;

	private $_docsNatureOrder = false;
	private $_docsRender = "default";

	/**
	 * the server you are operating
	 *
	 * @var MServer
	 */
	private static $_currentServer;
	private static $_servers = array();

	/**
	 * Mongo connection object
	 *
	 * @var RMongo
	 */
	private $_mongo;

	public function __construct(array $config) {
		foreach ($config as $param => $value) {
			switch ($param) {
				case "mongo_name":
					$this->_mongoName = $value;
					break;
				case "mongo_uri":
					$this->_mongoUri = $value;
					break;
				case "mongo_auth":
					$this->_mongoAuth = $value;
					break;
				case "ui_hide_system_collections":
					$this->_uiHideSystemCollections = $value;
					break;
				case "docs_nature_order":
					$this->_docsNatureOrder = $value;
					break;
				case "docs_render":
					$this->_docsRender = $value;
					break;
			}
		}
		if (empty($this->_mongoName)) {
			$this->_mongoName = $this->_mongoUri;
		}
	}

	public function mongoName() {
		return $this->_mongoName;
	}

	public function setMongoName($mongoName) {
		$this->_mongoName = $mongoName;
	}

	public function mongoDb() {
		return $this->_mongoDb;
	}

	public function mongoUri() {
		return $this->_mongoUri;
	}

	public function setMongoUri($mongoUri) {
		$this->_mongoUri = $mongoUri;
	}

	public function mongoAuth() {
		return $this->_mongoAuth;
	}

	public function setMongoAuth($mongoAuth) {
		$this->_mongoAuth = $mongoAuth;
	}

	public function uiHideSystemCollections() {
		return $this->_uiHideSystemCollections;
	}

	public function setUIHideSystemCollections($bool) {
		$this->_uiHideSystemCollections = $bool;
	}

	/**
	 * Set whether documents nature order
	 *
	 * @param boolean $bool true or false
	 * @since 1.1.6
	 */
	public function setDocsNatureOrder($bool) {
		$this->_docsNatureOrder = $bool;
	}

	/**
	 * Whether documents are in nature order
	 * @return boolean
	 * @since 1.1.6
	 */
	public function docsNatureOrder() {
		return $this->_docsNatureOrder;
	}

	/**
	 * Set documents highlight render
	 *
	 * @param string $render can be "default" or "plain"
	 * @since 1.1.6
	 */
	public function setDocsRender($render) {
		$renders = array( "default", "plain" );

		if (in_array($render, $renders)) {
			$this->_docsRender = $render;
		}
		else {
			exit("docs_render should be either 'default' or 'plain'");
		}
	}

	/**
	 * Get documents highlight render
	 *
	 * @return string
	 * @since 1.1.6
	 */
	public function docsRender() {
		return $this->_docsRender;
	}

	public function auth($username, $password, $db = "admin") {
		try {
			$uri = $this->_mongoUri;
			$options = $this->_mongoOptions;
			if ($this->_mongoAuth) {
				$this->_mongoDb = $db;
				$uri = str_replace('{{username}}', $username, $uri);
				$uri = str_replace('{{password}}', $password, $uri); 
				$uri = str_replace('{{database}}', $db, $uri);
			}
			$this->_mongo = new MongoDB\Client($uri, $options, ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]);
			$this->_mongo->listDatabases();
		}
		catch(Exception $e) {
			error_log($e->getMessage());
			if (preg_match("/authentication/i", $e->getMessage())) {
				return false;
			}
			echo "Unable to connect MongoDB, please check your configurations. MongoDB said:" . $e->getMessage() . ".";
			exit();
		}

		return true;
	}

	/**
	 * Current Mongo object
	 *
	 * @return Mongo
	 */
	public function mongo() {
		return $this->_mongo;
	}

	/**
	 * List databases on the server
	 *
	 * @return array
	 */
	public function listDbs() {
		$ret = array();
		try {
			$dbs = iterator_to_array($this->_mongo->listDatabases());
			$ret["ok"] = true;
		} catch (Exception $e) {
			$ret["ok"] = false;
		}
		if (!$ret["ok"]) {
			$user = MUser::userInSession();

			$ret = array(
				"databases" => array(),
				"totalSize" => 0,
				"ok" => 1
			);
			foreach ($user->dbs() as $db) {
				$ret["databases"][] = array( "name" => $db, "empty" => false, "sizeOnDisk" => 0);
			}
		}
		$totalSize = 0;
		foreach ($dbs as $index => $database) {
			$ret['databases'][$index] = array(
				'name' => $database->getName(),
				'sizeOnDisk' => $database->getSizeOnDisk(),
				'empty' => $database->isEmpty(),
			);
			$totalSize += $database->getSizeOnDisk();
		}
		$ret['totalSize'] = $totalSize;
		return $ret;
	}

	/**
	 * Enter description here ...
	 *
	 * @param unknown_type $hostIndex
	 * @return MServer
	 */
	public static function serverWithIndex($hostIndex) {
		global $MONGO;
		if (!isset($MONGO["servers"][$hostIndex])) {
			return null;
		}
		if (!isset(self::$_servers[$hostIndex])) {
			self::$_servers[$hostIndex] = new MServer($MONGO["servers"][$hostIndex]);
		}
		self::$_currentServer = self::$_servers[$hostIndex];
		return self::$_servers[$hostIndex];
	}

	/**
	 * Enter description here ...
	 *
	 * @return MServer
	 */
	public static function currentServer() {
		return self::$_currentServer;
	}
}

?>
