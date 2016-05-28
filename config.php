<?php
/**
 * RockMongo configuration
 *
 * Defining default options and server configuration
 * @package rockmongo
 */
 
$MONGO = array();
$MONGO["features"]["log_query"] = "on";
$MONGO["features"]["theme"] = "default";
$MONGO["features"]["plugins"] = "on";

$i = 0;

/**
* Configuration of MongoDB servers
* 
* @see more details at http://rockmongo.com/wiki/configuration?lang=en_us
*/
$MONGO["servers"][$i]["mongo_name"] = "test";
$MONGO["servers"][$i]["mongo_uri"] = "mongodb://{{username}}:{{password}}@10.13.144.118:14000,10.13.144.117:14000/{{database}}";
$MONGO["servers"][$i]["mongo_auth"] = true;

$i++;

?>
