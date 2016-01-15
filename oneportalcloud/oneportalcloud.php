<?php

require_once('oneportalcloud.api.php');


function check_setup($params){
	$server_id = $params['customfields']['Server ID'];
	//this can be used to sync in a later version
	if (empty($server_id)) return 'Unable to determine Server ID to check';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;


	$res = select_query('tblhosting','dedicatedip,assignedips',array('id'=>$params['serviceid']));
	list($dedicatedip,$assignedips) = mysql_fetch_array($res);
	$ips = false;
	$values = array();
	$ips = oneportalcloud_ipaddresses($params);
	if(is_null($dedicatedip) || strlen($dedicatedip) === 0){
		if(count($ips)){
			$ip_entry = current($ips['public']);
			$values['dedicatedip'] = $ip_entry['ipaddress'];
		}

	}
	$ip_count = 0;
	foreach($ips as $net){
		$ip_count += count($net);
	}
	if(is_null($assignedips) || strlen($assignedips) === 0 || substr_count($assignedips,"\n") != count($ip_count)){
		if(count($ips)){
			$ip_list = '';
			foreach($ips as $net_type){
				foreach($net_type as $ip){
					$ip_list .= $ip['ipaddress'] . "\n";
				}
			}
			$values['assignedips'] = trim($ip_list);
		}

	}
	if(!empty($values)){
		$where = array('id'=>$params['serviceid']);
		update_query('tblhosting',$values,$where);
	}

}

function oneportalcloud_ConfigOptions() {
	# Should return an array of the module options for each product - maximum of 24
	$configarray = array(
		'user' => array(
			'FriendlyName' => 'API User',
			'Type' => 'text',
			'Size' => '60'
		),
		'password' => array(
			'FriendlyName' => 'API Password',
			'Type' => 'password',
			'Size' => '60'
		),
	 	'url' => array(
			'FriendlyName' => 'OnePortal API URL',
			'Type' => 'text',
			'Size' => '60',
			'Default' => 'https://api.dallas-idc.com/v1'
		),
	 	'showbw' => array(
			'FriendlyName' => 'Bandwidth Graph',
			'Type' => 'yesno',
			'Description' => 'Tick to allow the bandwidth graph to be displayed client-side'
		),
	 	'showhw' => array(
			'FriendlyName' => 'Hardware List',
			'Type' => 'yesno',
			'Description' => 'Tick to allow the hardware list to be displayed client-side'
		),
	 	'showpwcontrols' => array(
			'FriendlyName' => 'Power Controls',
			'Type' => 'yesno',
			'Description' => 'Tick to allow the power controls to be displayed client-side'
		),
	 	'rdns' => array(
			'FriendlyName' => 'rDNS Domain',
			'Type' => 'text',
			'Size' => '30',
			'Default' => 'lstn.net'
		),
	 	'showips' => array(
			'FriendlyName' => 'IP Addressses',
			'Type' => 'yesno',
			'Description' => 'Tick to allow assigned IP Addresses to be displayed client-side'
		),
		'ram'  => array(
			'FriendlyName' => 'Ram',
			'Type' => 'dropdown',
			'Options' => "512MB,1GB,2GB,4GB,8GB,16GB,32GB",
			'Default' => '1GB',
			'Description' => 'Default Ram'
		),
		'storage'  => array(
			'FriendlyName' => 'Storage',
			'Type' => 'dropdown',
			'Options' => "5GB,10GB,15GB,20GB,50GB,100GB,120GB,140GB,160GB,180GB,200GB",
			'Description' => 'Default Storage',
			'Default' => '10GB'
		),
		'cores'  => array(
			'FriendlyName' => 'Cores',
			'Type' => 'dropdown',
			'Description' => 'Processor Cores',
			'Default' => '1',
			'Options' => '1,2,3,4,5,6,7,8,9,10,11,12'
		),
		'os' => array(
			'FriendlyName' => 'Operating System',
			'Type' => 'dropdown',
			'Options' =>  'CentOS 5.9 x64,CentOS 6.4 x64,Debian 7.0 x64,Fedora 18 x64,Gentoo 12.1 x64,Red Hat Enterprise Linux 5.9 x64,Red Hat Enterprise Linux 6.4 x64,Ubuntu 13.04 x64,Ubuntu 12.10 x64,Arch Linux 2012.12 x64,CloudLinux Server 6.4 x64,Fedora 19 x64,openSUSE 12.1 x86,PBXware 3.1 x86,Scientific Linux 6.2 x64,Slackware 13.37 x64,Windows 2012 Standard Edition R2 - 64 bit',
			'Default' => 'CentOS 6.4 x64'
		),
		'console' => array(
			'FriendlyName' => 'Server Console',
			'Type' => 'yesno',
			'Description' => 'Tick to allow the server console option to be displayed client-side'
		),
		'firewall' => array(
			'FriendlyName' => 'Firewall Info',
			'Type' => 'yesno',
			'Description' => 'Tick to allow the firewall configuration section to be displayed client-side'
		),
		//'bandwidth'  => array(
		//	'FriendlyName' => 'Bandwidth',
		//	'Type' => 'radio',
		//	'Options' => '5TB,10TB',
		//	'Default' => '5TB'
		//),
	//	'type' => array(
		//	'FriendlyName' => 'Cloud Type',
	//		'Type' => 'radio',
	//		'Options' => 'Cloud,Cloud Solution'
		//),
	);
	return $configarray;
}

function oneportalcloud_ChangePassword($params){
	if(!isset($_POST['ac'])){
		return 'success';
	}
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);
	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;
	$ret = $op->changePassword($server_id,$_POST['ac']);
	if(!$ret->error){
		include ('../../../configuration.php'); # Path to WHMCS configuration file. If change password does not work then make sure this is correct
		$where = array('id'=>$params['serviceid']);
		update_query('tblhosting',array('password'=>encrypt($_POST['ac'],$cc_encryption_hash)),$where);
		$res = 'success';
	}
	else{
		$res = $ret->error;
	}
	return $res;
}

function oneportalcloud_CustomFields() {
	# Should return an array of the module options for each product - maximum of 24
	$configarray = array(
		'server_id' => array(
			'FriendlyName' => 'Server ID (LSN-D####)',
			'Type' => 'text',
			'Size' => '25'
		),
	 	'root_user' => array(
			'FriendlyName' => 'Root User (optional)',
			'Type' => 'text',
			'Size' => '25'
		),
		'root_pass' => array(
			'FriendlyName' => 'Root Pass (optional)',
			'Type' => 'password',
			'Size' => '25'
		),
		'ssh_port' => array(
			'FriendlyName' => 'SSH/RDP Port',
			'Type' => 'text',
			'Size' => '10',
			'Default' => '22'
		),
	);
	return $configarray;
}


function oneportalcloud_CreateAccount($params) {
	if(($params['customfields']['Server ID']) != ""){
		return "Server already created.";
	}
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);
	$post = createPostString($op,$params);
	$post['password'] = $params['password'];
	$server = $op->createServer($post);
	if(isset($server->error)){
		return $server->error;
	}
	if(isset($server->server)){
		$server->server_id = $server->server;
	}
	$server_id = mysql_real_escape_string($server->server_id);

	$res = select_query('tblcustomfields','id',array('relid'=>$params['packageid'],'fieldname'=>'Server ID'));
	list($field_id) = mysql_fetch_array($res);
	$server_id = str_replace('LSN-','',$server_id);
	$values = array('value'=>$server_id);
	$where = array('relid'=>$params['serviceid'],'fieldid'=>$field_id);
	update_query('tblcustomfieldsvalues',$values,$where);
	$values = array('username'=>'root');
	$where = array('id'=>$params['serviceid']);
	update_query('tblhosting',$values,$where);
	$result = 'success';
	return $result;
}

function oneportalcloud_TerminateAccount($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);

	// See if this server is currently cancelled
	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to cancel';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$listing = $op->serverlist($server_id); 
	$server = $listing->server;
	if ($server->status == 'Cancelled') return 'success';
	$cancel = $op->cancelServer($server_id);

	if (empty($cancel->error)) {
		$result = 'success';
	} else {
		$result = $cancel->error;
	}
	return $result;
}

function oneportalcloud_SuspendAccount($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$suspend = $op->suspend($server_id);

	if (empty($suspend->error)) {
		$result = "success";
	} else {
		$result = $suspend->error;
	}
	return $result;
}

function oneportalcloud_UnsuspendAccount($params) {
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
function oneportalcloud_ClientArea($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);
	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;
	$server = $op->serverlist($server_id);
	if (empty($server)) return false;
	if ($server->status == 'provisioning') return 'This server is currently provisioning. See more details here when it is finished.';
	if ($server->status == 'cancelled') return 'This server has been cancelled';
	$status = $op->getStatus($server_id);
	$hardware = $op->gethardware($server_id);
	$fw_rules = $op->getFireWallRules($server_id);
	$ips = oneportalcloud_ipaddresses($params);

	return array(
		'templatefile' => 'clientarea',
		'vars' => array(
			'server' => $server,
			'status' => $status,
			'hardware' => $hardware,
			'ips' => $ips,
			'params' => $params,
			'fw_rules' => $fw_rules
		)
	);

}
function oneportalcloud_AdminCustomButtonArray() {
	# This function can define additional functions your module supports, the example here is a reboot button and then the reboot function is defined below
    $buttonarray = array(
	 "Reboot Server" => "reboot",
	 "Turn Off Server" => "turnoff",
	 "Turn On Server" => "turnon",
	"Rebuild Network" => "rebuildnetwork"
	);
	return $buttonarray;
}

function oneportalcloud_ClientAreaCustomButtonArray() {
	# This function can define additional functions your module supports, the example here is a reboot button and then the reboot function is defined below
    $buttonarray = array(
	 "Reboot Server" => "reboot",
	 "Turn Off Server" => "turnoff",
	 "Turn On Server" => "turnon",
	 "Save rDNS" => "saverdns",
	 "Save Firewall Changes" => 'firewallsave'

	);
	return $buttonarray;
}

function oneportalcloud_reboot($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$action = $op->restart($server_id);

	if (empty($action->error)) {
		$result = "success";
	} else {
		$result = $action->error;
	}
	return $result;
}
function oneportalcloud_rebuildnetwork($params){
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$action = $op->rebuildNetwork($server_id);

	if (empty($action->error)) {
		$result = "success";
	} else {
		$result = $action->error;
	}
	return $result;
}

function oneportalcloud_firewallsave($params){
	if(!isset($_POST['params'])){
		return 'No firewall parameters set.';
	}
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);
	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID';
	$d = trim($_POST['params']);
	$d = str_replace('&quot;', '"', $d);
	$fw_params = json_decode($d);
	$order_changes = array();
	$delete_changes = array();
	$new_changes = array();
	if(strlen($fw_params->deleted_rules) > 1){
		$deletes = explode(',',$fw_params->deleted_rules);

		foreach($deletes as $d){
			list($k,$v) = explode(':',$d);
			$delete_changes[] = $v;
		}
	}
	$delete_keys = array_flip($delete_changes);
	foreach($fw_params->order as $n=>$rule_order){
		$nic = substr($n,strpos($n,'_',4)+1);

		$o = explode(',',$rule_order);
		foreach($o as $rule){
			$r = explode(':',$rule);
			if(in_array($r[0],$delete_keys)) continue;
			$order_changes[$nic][$r[1]] = $r[0];
		}
	}

	foreach($fw_params->new_rules as $nic=>$rules){
		if(!is_null($rules) && !empty($rules)){
			$n = substr($nic,strpos($nic,'_',4)+1);
			$rule_ar = array();
			foreach($rules as $r){

				$r->nic = $n;
				$rule_ar[] = $r;
			}
			if(empty($rule_ar)){
				continue;
			}
			if(!$error = validateFireWallRule($rule_ar)){
				return implode('<br>',$error);
			}
			$new_changes[] = $rule_ar;
		}
	}
	if(!empty($delete_changes)){
		foreach($delete_changes as $delete){
			$op->deleteFirewallRule($server_id,$delete);
		}
	}
	if(!empty($new_changes)){
			foreach($new_changes as $nic=>$new){
				foreach($new as $n){
					$op->createFirewallRule($server_id,$n);
				}

		}
	}
	$op->updateFirewallRuleOrder($server_id,$order_changes);
	$op->updateDefaultFirewallRules($server_id,(array) $fw_params->defaults);
	return 'success';

}
function oneportalcloud_turnoff($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$action = $op->turnoff($server_id);

	if (empty($action->error)) {
		$result = "success";
	} else {
		$result = $action->error;
	}
	return $result;
}
function oneportalcloud_saverdns($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to save rDNS for';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	foreach ($_POST['ipaddress'] as $ip => $rdns) {
		$ips = $op->lookup_ip($ip);
		if ($ips->server_id != $server_id) return 'One or more IPs do not belong to this server';

		if ($ips->ptr != $rdns) {
			$setrdns = $op->dns_setreverse($ip, $rdns);
			if (!empty($setrdns->error)) return 'Unexpected error saving Reverse DNS';
		}
	}

	return 'success';
}

function oneportalcloud_turnon($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to turn on';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$action = $op->turnon($server_id);

	if (empty($action->error)) {
		$result = "success";
	} else {
		$result = $action->error;
	}
	return $result;
}

function oneportalcloud_ipaddresses($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$ips = $op->ipaddresses($server_id);
	//var_dump($ips);
	$ip_array = array();
	foreach ($ips as $ip) {
		$ip_array[(string)$ip->network][(string)$ip->ipaddress] = array(
			'ipaddress' => (string)$ip->ipaddress,
			'network' => (string)$ip->network,
			'type' => (string)$ip->type,
			'subnet' => (string)$ip->subnet,
			'ptr' => (string)$ip->ptr
		);
		if (!empty($params['configoption7'])) {

			if (strpos($ip->ptr, 'lstn.net') !== false && $params['configoption7'] != 'lstn.net') {
				$ip->ptr = str_replace('lstn.net', $params['configoption7'], $ip->ptr);

				// Update OnePortal with the correct rDNS
				$op->dns_setreverse($ip->ipaddress, $ip->ptr);
			}
		}
	}

	if (empty($ip_array)) return false;
	return $ip_array;
}

function oneportalcloud_AdminServicesTabFields($params) {
	check_setup($params);
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);
	$server_id = $params['customfields']['Server ID'];
	$server_id_orig = $server_id;
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;
	$server = $op->serverlist($server_id);
	if (empty($server)) return 'Server cancelled or not provisioned yet.';
	//Get console
	$consolearea = "<link rel=\"stylesheet\" href=\"../modules/servers/oneportalcloud/includes/css/adminArea.css\"/>
	<a class='btn' href='{$params['configoption3']}/server/{$server_id_orig}/console' target='_blank'>Open Server Console</a>";
	// Get hardware
	$hardware = $op->gethardware($server_id);
	$status = $op->getStatus($server_id);
	$fw_rules = $op->getFireWallRules($server_id);
	// Get IP addresses
	$ips = oneportalcloud_ipaddresses($params);
	$statusarea = "";

	if(empty($status->error)){
		if($status->booted){
			$statusarea .= "<span class=\"label label-default power label-success\">Online</span>";
		}
		else{
			$statusarea .= "<span class=\"label label-default offline label-success\">Offline</span>";
		}
		if($status->suspended){
			$statusarea .= "<span class=\"label label-default suspended label-success\">Suspended</span>";
		}
		if($status->locked){
			$statusarea .= "<span class=\"label label-default suspended label-success\">Locked</span>";
		}
		$statusarea.= "<style>.power { background-color: #5cb85c; } .label{ margin-left: 10px;} .offline{ background-color: #FF0000}</style>";
	}

	if (empty($hardware->error)) {
		$hwtable = '<table id="serverhwtable"><tr><th>Category</th><th>Item</th></tr>';
		foreach ($hardware as $item) {
			$hwtable .= "<tr><td>{$item->category}</td><td>{$item->option}</td></tr>";
		}
		$hwtable .= '</table>';
	}

	if (!empty($ips)) {
		$var3 = '<table id="serveriptable"><tr><th>Network</th><th>IP Address</th><th>IP Type</th><th>Reverse DNS</th></tr>';
		foreach ($ips as $nettype => $network) {
			$nettype = ucfirst($nettype);
			foreach ($network as $ip) {
				if ($nettype == 'Private' || $ip['type'] == 'network' || $ip['type'] == 'gateway' || $ip['type'] == 'broadcast') {
					$var3 .= "<tr><td>{$nettype}</td><td>{$ip['ipaddress']}</td><td>{$ip['type']}</td><td>&nbsp;</td></tr>";
				}else{
					$var3 .= "<tr><td>{$nettype}</td><td>{$ip['ipaddress']}</td><td>{$ip['type']}</td><td>{$ip['ptr']}</td></tr>";
				}
			}
		}
		$var3 .= '</table>';
	
	}else{
		$var3 = '<p>Unable to determine IP Addresses</p>';
	}
	ob_start();

	$has_rules = array();
	foreach($fw_rules->firewall_by_network as $id=>$network):?>
		<h3>Firewall Rules for <?=$fw_rules->network_interface_obj->$id->label?></h3>
		<?php $has_rules[$id] = true;?>
		<table class="serverfwtable" id="fw_rules_<?=$id?>">
			<thead>
				<tr>
					<th>Address</th>
					<th>Command</th>
					<th>Port</th>
					<th>Protocol</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($network as $fw):?>
				<tr data-value='<?=$fw->id?>' data-rule-position='<?=$fw->position?>' id='rule_<?=$fw->id?>' class='fw_rule'>
					<td><?=$fw->address?></td>
					<td><?=$fw->command?></td>
					<td><?=$fw->port?></td>
					<td><?=$fw->protocol?></td>
					<td><button type='button' class='btn btn-small btn-danger btn-submit rule_remove' id='<?=$fw->id?>' data-network-id='<?=$id?>'>Remove</button></td>
				</tr>
			<?php endforeach;?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4"></td>
					<td colspan="1">
						<button class="add-row btn btn-small btn-success btn-submit"  type="button" data-network-id="<?=$id?>">Add</button>
					</td>
				</tr>
			</tfoot>
		</table>
	<?php endforeach;?>

	<?php foreach($fw_rules->network_interface_obj as $nic):
		if(!isset($has_rules[$nic->id])):?>
			<h3>Firewall Rules for <?=$nic->label?></h3>
			<table class="serverfwtable" id="fw_rules_<?=$nic->id?>">
				<thead>
					<tr>
						<th>Address</th>
						<th>Command</th>
						<th>Network Interface</th>
						<th>Port</th>
						<th>Protocol</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<tr id="sry">
						<td colspan="5">You have no additional rules configured.</td>
					</tr>
				<tfoot>
					<tr>
						<td colspan="4"></td>
						<td colspan="1">
							<button class="add-row btn btn-small btn-success btn-submit"  type="button" data-network-id="'.$nic->id.'">Add</button>
						</td>
					</tr>
				</tfoot>
			</tbody>
		</table>
		<?php endif;?>
	<?php endforeach;?>

	<table class='serverdeftable'>
		<thead>
			<tr>
				<th>Network Interface</th>
				<th>Default Firewall Rule</th>
			</tr>
		</thead>
		<tbody>

		<?php foreach($fw_rules->network_interface_obj as $nic):
		$default = $nic->default_firewall_rule;
		$accept_select = $default == 'ACCEPT' ? 'selected' : '';
		$drop_select = $default == 'DROP' ? 'selected' : '';
		?>
			<tr>
				<td><?=$nic->label?></td>
				<td>
					<select name='default_rule_<?=$nic->id?>' data-network-id='<?=$nic->id?>'>
						<option <?=$accept_select?> value='accept'>ACCEPT</option>
						<option <?=$drop_select?> value='drop'>DROP</option>
					</select>
				</td>
			</tr>
		<?php endforeach;?>

		</tbody>
	</table>
	<div align='center'>
		<button id="save-changes" class="fw_save btn btn-primary" >Save Firewall changes</button>
	</div>
	<div id='save_dialog' title='Save Firewall changes?'>
		<p>Are you sure you want to make these changes to the firewall? Changes may take up to 5 minutes to become visible.</p>
	</div>
	<input type="hidden" name="deletedRules" value="" id="deletedRules">
	<script type="text/javascript">
		var userid = '<?=$params['userid']?>';
		var serviceid = '<?=$params['serviceid']?>';
	</script>
	<script type="text/javascript" src="../modules/servers/oneportalcloud/includes/js/adminArea.js"></script>
<?php
$fwarea = ob_get_contents();
ob_end_clean();
	// Send what we've made
	$fieldsarray = array(
		//'Bandwidth' => $var1,
		'Server Console' => $consolearea,
		'Server status' => $statusarea,
		'Package Items' => $hwtable,
		'IP Addresses' => $var3,
		'Firewall Information' => $fwarea
	);

	return $fieldsarray;
}
function oneportalcloud_AdminServicesTabFieldsSave($params) {
	$op = new OnePortalCloud($params['configoption1'], $params['configoption2'],$params['configoption3']);
	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;
	$post = createPostString($op,$params);
	$ret = $op->upgradeServer($server_id,$post);
	if(!$ret->error){
		$res = 'success';
	}
	else{
		$res = $ret->error;
	}
	return $res;
}
function createPostString($op,$params){
	$core = 69;
	$ram_name = isset($params['configoptions']['Ram'])?$params['configoptions']['Ram']:$params['configoption9'];
	$storage_name = isset($params['configoptions']['Storage'])?$params['configoptions']['Storage']:$params['configoption10'];
	$os_name = isset($params['configoptions']['OS'])?$params['configoptions']['OS']:$params['configoption12'];
	$cpus_name = isset($params['configoptions']['Cores'])?$params['configoptions']['Cores']:$params['configoption11'];
	$ips_name = isset($params['configoptions']['IPs'])?$params['configoptions']['IPs']:"1 IP";
	$cp_name = isset($params['configoptions']['Control Panel'])?$params['configoptions']['Control Panel']:'cPanel';
	$hostname = $params['domain'];
	//Get options from web service
	$storage = $op->findOption($storage_name,3,$core);
	$ram = $op->findOption($ram_name,1,$core);
	$os = $op->findOption($os_name,8,$core);
	$ip = $op->findOption($ips_name,11,$core);
	$cp = $op->findOption($cp_name,9,$core);
	//build post object
	return array('1'=>$ram->id,'3'=>$storage->id,'33'=>$cpus_name,'core'=>$core,'8'=>$os->id,'9'=>$cp->id,'11'=>$ip->id,'hostname'=>$hostname);
}
function validateFireWallRule($rule){
	$errors = array();
	if(isset($rule->ip)){
		if(!filter_var($rule->ip, FILTER_VALIDATE_IP)){
			if((!preg_match('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\ - \b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b$/',$rule->ip))){
				if(!preg_match('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b\/\d{1,2}$/',$rule->ip)){
					$errors[] = "Invalid IP format";
				}
			}
			else{
				$ip = str_replace(" ","",$rule->ip);
				list($ip1,$ip2) = explode("-",$ip);
				if(ip2long($ip1) > ip2long($ip2)){
					$errors[] = "Invalid IP range";
				}
			}

		}
	}
	else{
		$errors[] = "IP must be filled out.";
	}
	if(isset($rule->cmd)){
		if($rule->cmd != 'accept' && $rule->cmd != 'drop'){
			$errors[] = "Invalid CMD set";
		}
	}
	else{
		$errors[] = "CMD must be set";
	}
	if(isset($rule->nic)){
		if(!is_numeric($rule->nic)){
			$errors[] = "Invalid NIC format";
		}
	}
	else{
		$errors[] = "NIC must be set";
	}
	if(isset($rule->port)){
		if(!is_numeric($rule->port) || $rule->port > 65535 || $rule->port <= 0){
			if(!preg_match('/\b\d{1,5}:\b\d{1,5}$/',$rule->port)){
				if(!preg_match('/(\b\d{1,5},\b\d{1,5})+$/',$rule->port)){
					$errors[] = "Invalid port format";
				}
			}
			else{
				list($p1,$p2) = explode(":",$rule->port);
				if($p1 > $p2){
					$errors[] = "Invalid port range";
				}
				elseif($p2 > 65535 || $p1 <= 0){
					$errors[] = "Invalid port range";
				}
			}
		}
	}
	if(isset($rule->protocol)){
		if($rule->protocol != 'tcp' && $rule->protocol != 'udp'){
			$errors[] = "Invalid protocol";
		}
	}
	else{
		$errors[] = "Protocol must be set";
	}
	return empty($errors)?true:$errors;
}
?>
