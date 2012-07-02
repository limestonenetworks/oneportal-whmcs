<?php

// Path to configuration.php
$oneportal_api_key = 'fpj4abbvoxhagw84atmkpfr9ebllrs6iye3n926extkoydtauc4yn1r504npy0tx';

require(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../configuration.php');
require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'oneportal.api.php');

try {
	// Check MySQL Configuration
	$db_conn = mysql_connect($db_host, $db_username, $db_password);
	if (empty($db_conn)) throw new Exception('Unable to connect to DB');

	$db_select = @mysql_select_db($db_name, $db_conn);
	if (empty($db_select)) throw new Exception('Unable to select WHMCS database');

	// Get listing from OnePortal
	$op = new OnePortal($oneportal_api_key);
	$servers = $op->serverlist();

	if (empty($servers)) throw new Exception('Unable to access OnePortal');

	foreach ($servers as $server) {
		$server_id = str_replace('LSN-', '', $server->attributes()->id);

		$query = "SELECT `tblhosting`.*
FROM `tblcustomfields`, `tblcustomfieldsvalues`, `tblhosting`
WHERE `tblcustomfields`.`fieldname` = 'Server ID'
AND `tblcustomfieldsvalues`.`value` LIKE '%{$server_id}'
AND `tblcustomfieldsvalues`.`relid` = `tblcustomfields`.`relid`
AND `tblhosting`.`id` = `tblcustomfieldsvalues`.`relid`";

		$result = mysql_query($query);
		$server_from_id = @mysql_fetch_assoc($result);

		if (count($server_from_id) > 0) {
			$usage['mb'] = (int)$server->bandwidth->actual->bytes / 1024 / 1024;
			$usage['allocated'] = (int)str_replace('GB', '', $server->bandwidth->actual->allocated) * 1024;
			
			mysql_query("UPDATE `tblhosting` SET
					`domain` = '{$server->publicip}',
					`dedicatedip` = '{$server->publicip}',
					`assignedips` = 'See below for details',
					`bwusage` = {$usage['mb']},
					`bwlimit` = {$usage['allocated']}
					WHERE `id` = {$server_from_id['id']}
					LIMIT 1");

		}
	}
} catch (Exception $e) {
    echo 'Error: ',  $e->getMessage(), "\n";
}

?>
