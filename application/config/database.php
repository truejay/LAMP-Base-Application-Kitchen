<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the "Database Connection"
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the "default" group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$ci =& get_instance();

$master_hostname = $ci->config->item('master_hostname');
$master_username = $ci->config->item('master_username');
$master_password = $ci->config->item('master_password');
$master_database = $ci->config->item('master_database');
$ary_slaves = $ci->config->item('slaves');
$dbdriver = $ci->config->item('dbdriver');
$dbprefix = $ci->config->item('dbprefix');
$pconnect = $ci->config->item('pconnect');
$db_debug = $ci->config->item('db_debug');
$cache_on = $ci->config->item('cache_on');
$cachedir = $ci->config->item('cachedir');
$char_set = $ci->config->item('char_set');
$dbcollat = $ci->config->item('dbcollat');

$active_group = "master";
$active_record = TRUE;

$db['master']['hostname'] = $master_hostname;
$db['master']['username'] = $master_username;
$db['master']['password'] = $master_password;
$db['master']['database'] = $master_database;
$db['master']['dbdriver'] = $dbdriver;
$db['master']['dbprefix'] = $dbprefix;
$db['master']['pconnect'] = $pconnect;
$db['master']['db_debug'] = $db_debug;
$db['master']['cache_on'] = $cache_on;
$db['master']['cachedir'] = $cachedir;
$db['master']['char_set'] = $char_set;
$db['master']['dbcollat'] = $dbcollat;

for($i = 1; $i <= sizeof($ary_slaves); $i++)
{
    $db['slave_' . $i]['hostname'] = $ary_slaves[$i - 1]['hostname'];
    $db['slave_' . $i]['username'] = $ary_slaves[$i - 1]['username'];
    $db['slave_' . $i]['password'] = $ary_slaves[$i - 1]['password'];
    $db['slave_' . $i]['database'] = $ary_slaves[$i - 1]['database'];
    $db['slave_' . $i]['dbdriver'] = $dbdriver;
    $db['slave_' . $i]['dbprefix'] = $dbprefix;
    $db['slave_' . $i]['pconnect'] = $pconnect;
    $db['slave_' . $i]['db_debug'] = $db_debug;
    $db['slave_' . $i]['cache_on'] = $cache_on;
    $db['slave_' . $i]['cachedir'] = $cachedir;
    $db['slave_' . $i]['char_set'] = $char_set;
    $db['slave_' . $i]['dbcollat'] = $dbcollat;
}

/* End of file database.php */
/* Location: ./system/application/config/database.php */