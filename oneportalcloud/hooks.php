<?php
require_once('oneportalcloud.api.php');
function hook_oneportalcloud_ChangePackage($upgradeid) {

	$upgradeFields = array('Ram'=>1,'Storage'=>3,'Cores'=>33,'OS'=>8,'IPs'=>11);
	$table = 'tblupgrades as tu left join tblhosting as th on tu.relid = th.id left join tblproducts as tp on tp.id = th.packageid left join tblcustomfields as tcf on tcf.relid = tp.id left join tblcustomfieldsvalues as tcfv on tcf.id = tcfv.fieldid and tcfv.relid = tu.relid';
	$fields = 'tcfv.value as server_id, tu.relid as service_id,tp.configoption1,tp.configoption2,tp.configoption3';
	$where = "tu.id = '{$upgradeid['upgradeid']}' and tcf.fieldname = 'Server ID'";
	$query = "Select $fields from $table where $where";
	$result = mysql_query($query);
	$params = mysql_fetch_array($result);

	$table = 'tblupgrades';
	$fields = 'originalvalue,newvalue';
	$where = "id = '{$upgradeid['upgradeid']}'";
	$query = "Select $fields from $table where $where";
	$result = mysql_query($query);
	$names = array();
	while($data = mysql_fetch_array($result)){
		list($cat,$opt) = explode('=>',$data['originalvalue']);
		$table = 'tblproductconfigoptions as tc left join tblproductconfigoptionssub as tcs on tc.id = tcs.configid ';
		$fields = 'tc.optionname as category, tcs.optionname';
		$where = "tcs.configid = '{$cat}' and tcs.id = '{$data['newvalue']}'";
		$query = "Select $fields from $table where $where";
		$res = mysql_query($query);
		$item = mysql_fetch_array($res);
		$names[$item['category']] = $item['optionname'];

	}
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);
	$core = 69;
	$post = array();
	foreach($upgradeFields as $up=>$cat){
		if(isset($names[$up])){

			$var =  $op->findOption($names[$up],$cat,$core);
			$val = $var->id;

			$post[$cat] = $val;
		}
	}
	$ret = $op->upgradeServer($params['server_id'],$post);
	return 'success';
}

add_hook("AfterConfigOptionsUpgrade",1,'hook_oneportalcloud_ChangePackage');

function hook_oneportalcloud_UnSuspend($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);
	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;
	$suspend = $op->unsuspend($server_id);
	if (empty($suspend->error)) {
		$result = "success";
	} else {
		$result = $suspend->error;
	}
	return $result;
}

add_hook("AfterModuleUnsuspend",1,'hook_oneportalcloud_UnSuspend');

