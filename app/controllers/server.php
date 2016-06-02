<?php

import("classes.BaseController");

class ServerController extends BaseController {
	/** server infomation **/
	public function doIndex() {
		$db = $this->_mongo->selectDatabase("admin");

		//command line
		try {
			$query = $db->command(array("getCmdLineOpts" => 1))->toArray()[0];
			if (isset($query["argv"])) {
				$this->commandLine = implode(" ", $query["argv"]);
			}
			else {
				$this->commandLine = "";
			}
		} catch (Exception $e) {
			$this->commandLine = "";
		}

		//web server
		$this->webServers = array();
		if (isset($_SERVER["SERVER_SOFTWARE"])) {
			list($webServer) = explode(" ", $_SERVER["SERVER_SOFTWARE"]);
			$this->webServers["Web server"] = $webServer;
		}
		$this->webServers["<a href=\"http://www.php.net\" target=\"_blank\">PHP version</a>"] = "PHP " . PHP_VERSION;
		$this->webServers["<a href=\"http://www.php.net/mongodb\" target=\"_blank\">PHP extension</a>"] = "<a href=\"http://pecl.php.net/package/mongodb\" target=\"_blank\">mongodb</a>/" . MONGODB_VERSION;

		$this->directives = ini_get_all("mongodb");

		//build info
		$this->buildInfos = array();
		try {
			$ret = $db->command(array("buildinfo" => 1))->toArray()[0];
			if ($ret["ok"]) {
				unset($ret["ok"]);
				$this->buildInfos = $ret;
			}
		} catch (Exception $e) {

		}

		//connection
		$this->connections = array(
			"URI" => str_replace("{{username}}", "***", str_replace("{{password}}", "***", str_replace("{{database}}", "", $this->_server->mongoUri()))),
		);

		$this->display();
	}

	/** Server Status **/
	public function doStatus() {
		$this->status = array();

		try {
			//status
			$db = $this->_mongo->selectDatabase("admin");
			$ret = $db->command(array("serverStatus" => 1))->toArray()[0];
			if ($ret["ok"]) {
				unset($ret["ok"]);
				$this->status = $ret;
				foreach ($this->status as $index => $_status) {
					$json = $this->_highlight($_status, "json");
					if ($index == "uptime") {//we convert it to days
						if ($_status >= 86400) {
							$json .= "s (" . ceil($_status/86400) . "days)";
						}
					}
					$this->status[$index] =  $json;
				}
			}
		} catch (Exception $e) {

		}

		$this->display();
	}

	/** show databases **/
	public function doDatabases() {
		$ret = $this->_server->listDbs();
		$this->dbs = $ret["databases"];
		foreach ($this->dbs as $index => $db) {
			$mongodb = $this->_mongo->selectDatabase($db["name"]);
			$ret = $mongodb->command(array("dbstats" => 1))->toArray()[0];
			$ret["collections"] = count(MDb::listCollections($mongodb));
			if (isset($db["sizeOnDisk"])) {
				$ret["diskSize"] = r_human_bytes($db["sizeOnDisk"]);
				$ret["dataSize"] = r_human_bytes($ret["dataSize"]);
			}
			else {
				$ret["diskSize"] = "-";
				$ret["dataSize"] = "-";
			}
			$ret["storageSize"] = r_human_bytes($ret["storageSize"]);
			$ret["indexSize"] = r_human_bytes($ret["indexSize"]);
			$this->dbs[$index] = array_merge($this->dbs[$index], $ret);

		}
		$this->dbs = rock_array_sort($this->dbs, "name");
		$this->display();
	}

	/** execute command **/
	public function doCommand() {
		$ret = $this->_server->listDbs();
		$this->dbs = $ret["databases"];

		if (!$this->isPost()) {
			x("command", json_format("{listCommands:1}"));
			if (!x("db")) {
				x("db", "admin");
			}
		}

		if ($this->isPost()) {
			$command = xn("command");
			$format = x("format");
			if ($format == "json") {
				$command = 	$this->_decodeJson($command);
			}
			else {
				$eval = new VarEval($command);
				$command = $eval->execute();
			}
			if (!is_array($command)) {
				$this->message = "You should send a valid command";
				$this->display();
				return;
			}
			$this->ret = $this->_highlight($this->_mongo->selectDatabase(xn("db"))->command($command)->toArray()[0], $format);
		}
		$this->display();
	}

	/** execute code **/
	public function doExecute() {
		$ret = $this->_server->listDbs();
		$this->dbs = $ret["databases"];
		if (!$this->isPost()) {
			if (!x("db")) {
				x("db", "admin");
			}
			x("code", 'function () {
   var plus = 1 + 2;
   return plus;
}');
		}
		if ($this->isPost()) {
			$code = new MongoDB\BSON\Javascript(trim(xn("code")));
			$arguments = xn("argument");
			if (!is_array($arguments)) {
				$arguments = array();
			}
			else {
				$this->arguments = $arguments;
				foreach ($arguments as $index => $argument) {
					$argument = trim($argument);
					$array = $this->_decodeJson($argument);
					$arguments[$index] = $array;
				}
			}
			$ret = $this->_mongo->selectDatabase(xn("db"))->command(array('eval' => array("function" => $code, "arguments" => $arguments)))->toArray()[0];
			$this->ret = $this->_highlight($ret, "json");
		}
 		$this->display();
	}

	/** processlist **/
	public function doProcesslist() {
		$this->progs = array();

		try {
			$query = $this->_mongo->selectDatabase("admin")->execute('function (){
				return db.$cmd.sys.inprog.find({ $all:1 }).next();
			}');


			if ($query["ok"]) {
				$this->progs = $query["retval"]["inprog"];
			}
			foreach ($this->progs as $index => $prog) {
				foreach ($prog as $key=>$value) {
					if (is_array($value)) {
						$this->progs[$index][$key] = $this->_highlight($value, "json");
					}
				}
			}
		} catch (Exception $e) {

		}
		$this->display();
	}

	/** kill one operation in processlist **/
	public function doKillOp() {
		$opid = xi("opid");
		$query = $this->_mongo->selectDatabase("admin")->execute('function (opid){
			return db.killOp(opid);
		}', array( $opid ));
		if ($query["ok"]) {
			$this->redirect("server.processlist");
		}
		$this->ret = $this->_highlight($query, "json");
		$this->display();
	}

	/** create databse **/
	public function doCreateDatabase() {
		if ($this->isPost()) {
			$name = trim(xn("name"));
			if (empty($name)) {
				$this->error = "Please input a valid database name.";
				$this->display();
				return;
			}
			$this->message = "New database created.";
			print_r($this->_mongo->selectDatabase($name)->command(array("listCollections" => 1)));
		}
		$this->display();
	}

	/** replication status **/
	public function doReplication() {
		$this->status = $this->_mongo->selectDatabase("admin")->command(array('replSetGetStatus' => 1))->toArray()[0];

		//me
		try {
			$this->me = $this->_mongo->selectDatabase("local")->selectCollection("me")->findOne();
		} catch (Exception $e) {
			$this->me = array();
		}

		$this->display();
	}
}

?>
