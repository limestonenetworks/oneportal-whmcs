<?php

require('oneportal.api.php');

function oneportal_ConfigOptions() {
	# Should return an array of the module options for each product - maximum of 24
	$configarray = array(
		'api_key' => array(
			'FriendlyName' => 'API Key',
			'Type' => 'password',
			'Size' => '60'
		),
	 	'url' => array(
			'FriendlyName' => 'OnePortal API URL',
			'Type' => 'text',
			'Size' => '60',
			'Default' => 'https://one.limestonenetworks.com/webservices/clientapi.php'
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
		)
	);
	return $configarray;
}

function oneportal_CustomFields() {
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

/*
function oneportal_CreateAccount($params) {
	$result = 'Server must be ordered manually from Limestone Networks';
	return $result;
}
*/

function oneportal_TerminateAccount($params) {
	$op = new OnePortal($params['configoption1'], $params['configoption2']);

	// See if this server is currently cancelled
	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to cancel';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$listing = $op->serverlist($server_id); 
	$server = $listing->server;
	if ($server->status == 'Cancelled') return 'success';

	// Now, since we haven't returned yet, it must not be cancelled. Request cancellation
	$userlist = $op->userlist();
	$newticket = $op->addticket(
			10,
			"Please cancel {$server_id}",
			'This server has been cancelled through our billing system and we are no longer being paid by our client for this server. Please cancel it.',
			(int) $userlist->user[0]['id'],
			$server = $server_id);

	if (empty($newticket->error)) {
		$result = 'success';
	} else {
		$result = $newticket->error;
	}
	return $result;
}

function oneportal_SuspendAccount($params) {
	$op = new OnePortal($params['configoption1'], $params['configoption2']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$suspend = $op->portcontrol($server_id, 'public', 'off');

	if (empty($suspend->error)) {
		$result = "success";
	} else {
		$result = $suspend->error;
	}
	return $result;
}

function oneportal_UnsuspendAccount($params) {
	$op = new OnePortal($params['configoption1'], $params['configoption2']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$suspend = $op->portcontrol($server_id, 'public', 'on');

	if (empty($suspend->error)) {
		$result = "success";
	} else {
		$result = $suspend->error;
	}
	return $result;
}

function oneportal_ClientArea($params) {
        $op = new OnePortal($params['configoption1'], $params['configoption2']);

        $server_id = $params['customfields']['Server ID'];
        if (empty($server_id)) return 'Unable to determine Server ID';
        if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

        $server = $op->serverlist($server_id);
        if (empty($server)) return false;
        $server = $server->server;

        if ($server->status == 'provisioning') return 'This server is currently provisioning. See more details here when it is finished.';
        if ($server->status == 'cancelled') return 'This server has been cancelled';

        if ($params['configoption3'] == 'on') {
                // Get bandwidth graph
                $bwgraph = $op->bwgraph($server_id);
                if ($bwgraph->error) $bwgraph = '';

                $bandwidth = "
                        <div class=\"row\">
                                <div class=\"col-sm-6\">{$bwgraph}</div>
                                <div class=\"col-sm-6\">
                                        <h4>Bandwidth Usage</h4>
                                        <table id=\"bwtable\" class=\"table table-striped\">
                                                <tr><th>Direction</th><th colspan=\"2\">Usage</th><th>Bytes</th></tr>
                                                <tr><td>Inbound</td><td colspan=\"3\">Unmetered</td></tr>
                                                <tr><td>Actual Outbound</td><td>{$server->bandwidth->actual->percentage}%</td><td>{$server->bandwidth->actual->friendly}</td><td>{$server->bandwidth->actual->bytes}</td></tr>
                                                <tr><td>Predicted Outbound</td><td>{$server->bandwidth->predicted->percentage}%</td><td>{$server->bandwidth->predicted->friendly}</td><td>{$server->bandwidth->predicted->bytes}</td></tr>
                                        </table>
                                </div>
                        </div>
                        <hr />
                ";
                $code .= $bandwidth;
        }
        if ($params['configoption4'] == 'on') {
                // Get hardware
                $hardware = $op->gethardware($server_id);
                if (empty($hardware->error)) {
                        $hwtable = '<h4>Hardware Details</h4><table id="serverhwtable" class="table table-striped"><tr><th>Serial</th><th>Item</th></tr>';
                        foreach ($hardware->item as $item) {
                                $hwtable .= "<tr><td>{$item->serial}</td><td>{$item->description}</td></tr>";
                        }
                        $hwtable .= '</table><hr />';
                        $code .= $hwtable;
                }
        }


        // Get IP addresses
        if ($params['configoption7'] == 'on') {
                $ips = oneportal_ipaddresses($params);

                if (!empty($ips)) {
                        $iptable = '<form method="post" action="clientarea.php?action=productdetails">
        <input type="hidden" name="id" value="'.$params['serviceid'].'" />
        <input type="hidden" name="modop" value="custom" />
        <input type="hidden" name="a" value="saverdns" />';
                        $iptable .= '<table id="serveriptable" class="table table-striped"><tr><th>Network</th><th>IP Address</th><th>IP Type</th><th>Reverse DNS</th></tr>';
                        foreach ($ips as $nettype => $network) {
                                $nettype = ucfirst($nettype);
                                foreach ($network as $ip) {
                                        if ($nettype == 'Private' || $ip['type'] == 'network' || $ip['type'] == 'gateway' || $ip['type'] == 'broadcast') {
                                                $iptable .= "<tr><td>{$nettype}</td><td>{$ip['ipaddress']}</td><td>{$ip['type']}</td><td>&nbsp;</td></tr>";
                                        }else{
                                                $iptable .= "<tr><td>{$nettype}</td><td>{$ip['ipaddress']}</td><td>{$ip['type']}</td><td><input type=\"text\" name=\"ipaddress[{$ip['ipaddress']}]\" value=\"{$ip['ptr']}\" class=\"form-control\" /></td></tr>";
                                        }
                                }
                        }
                        $iptable .= '</table>';

                        $code .= '<h4>IP Addressses</h4>'.$iptable;
                        $code .= '<p class="text-center"><input type="submit" value="Save Reverse DNS Changes" class="btn-large btn-success" /></form></p>';
                }else{
                        $code .= '<h3>IP Addresses</h3><p>Unable to determine IP Addresses</p>';
                }
        }

        return $code;
}

function oneportal_AdminLink($params) {
	$code = '';
	return $code;
}

/*
function oneportal_LoginLink($params) {
	echo "<a href=\"http://".$params["serverip"]."/controlpanel?gotousername=".$params["username"]."\" target=\"_blank\" style=\"color:#cc0000\">login to control panel</a>";
}
*/
function oneportal_AdminCustomButtonArray() {
	# This function can define additional functions your module supports, the example here is a reboot button and then the reboot function is defined below
    $buttonarray = array(
	 "Reboot Server" => "reboot",
	 "Turn Off Server" => "turnoff",
	 "Turn On Server" => "turnon"
	);
	return $buttonarray;
}

function oneportal_ClientAreaCustomButtonArray() {
	# This function can define additional functions your module supports, the example here is a reboot button and then the reboot function is defined below
    $buttonarray = array(
	 "Reboot Server" => "reboot",
	 "Turn Off Server" => "turnoff",
	 "Turn On Server" => "turnon",
	 "Save rDNS" => "saverdns"
	);
	return $buttonarray;
}

function oneportal_reboot($params) {
	$op = new OnePortal($params['configoption1'], $params['configoption2']);

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

function oneportal_turnoff($params) {
	$op = new OnePortal($params['configoption1'], $params['configoption2']);

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

function oneportal_saverdns($params) {
	$op = new OnePortal($params['configoption1'], $params['configoption2']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to save rDNS for';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	foreach ($_POST['ipaddress'] as $ip => $rdns) {
		$ips = $op->ipaddresses($ip, $server_id);
		if (empty($ips)) return 'One or more IPs do not belong to this server';

		if ($ips->ptr != $rdns) {
			$setrdns = $op->dns_setreverse($ip, $rdns);
			if (!empty($setrdns->error)) return 'Unexpected error saving Reverse DNS';
		}
	}

	return 'success';
}

function oneportal_turnon($params) {
	$op = new OnePortal($params['configoption1'], $params['configoption2']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$action = $op->turnon($server_id);

	if (empty($action->error)) {
		$result = "success";
	} else {
		$result = $action->error;
	}
	return $result;
}

function oneportal_ipaddresses($params) {
	$op = new OnePortal($params['configoption1'], $params['configoption2']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;
	$ips = $op->ipaddresses(false, $server_id, false);
	$ip_array = array();
	foreach ($ips->ipaddress as $ip) {
		$ip_array[(string)$ip->network][(string)$ip->attributes()->ip] = array(
			'ipaddress' => (string)$ip->attributes()->ip,
			'network' => (string)$ip->network,
			'type' => (string)$ip->type,
			'ptr' => (string)$ip->ptr,
			'subnet' => (string)$ip->subnet
		);

		if (!empty($params['configoption6'])) {
			if (strpos((string)$ip->ptr, 'lstn.net') !== false) {
				$ip_array[(string)$ip->network][(string)$ip->attributes()->ip]['ptr'] = str_replace('lstn.net', $params['configoption6'], (string)$ip->ptr);

				// Update OnePortal with the correct rDNS
				$op->dns_setreverse((string)$ip->attributes()->ip, $ip_array[(string)$ip->network][(string)$ip->attributes()->ip]['ptr']);
			}
		}
	}

	if (empty($ip_array)) return false;
	return $ip_array;
}

function oneportal_AdminServicesTabFields($params) {
	$op = new OnePortal($params['configoption1'], $params['configoption2']);

	$server_id = $params['customfields']['Server ID'];
	if (empty($server_id)) return 'Unable to determine Server ID to suspend';
	if (substr(strtoupper($server_id), 0, 3) != 'LSN') $server_id = 'LSN-' . $server_id;

	$server = $op->serverlist($server_id);
	if (empty($server)) return 'Server cancelled or not provisioned yet.';
	$server = $server->server;

	$result = select_query("mod_customtable","",array("serviceid"=>$params['serviceid']));
	$data = mysql_fetch_array($result);

	// Get bandwidth graph
	$var1 = '';
	$bwgraph = $op->bwgraph($server_id);
	if (empty($bwgraph->error)) $var1 .= $bwgraph;
	$table = '<table id="bwtable"><tr><th>Bandwidth Usage</th><th>Percentage</th><th>Friendly</th><th>Bytes</th></tr>';
	$table .= '<tr><td>Inbound</td><td colspan="3">Unmetered</td></tr>';
	$table .= "<tr><td>Actual Outbound</td><td>{$server->bandwidth->actual->percentage}%</td><td>{$server->bandwidth->actual->friendly}</td><td>{$server->bandwidth->actual->bytes}</td></tr>";
	$table .= "<tr><td>Predicted Outbound</td><td>{$server->bandwidth->predicted->percentage}%</td><td>{$server->bandwidth->predicted->friendly}</td><td>{$server->bandwidth->predicted->bytes}</td></tr>";
	$table .= '</table>';
	$table .= '<style>
#bwtable { margin: 10px 0 10px 0; border: 1px solid #ccc; width: 100%; }
#bwtable th { background-color: #333; color: #fff; font-weight: normal; }
#bwtable tr td, #bwtable tr th { padding: 3px; }
</style>';
	$var1 .= $table;

	// Get hardware
	$hardware = $op->gethardware($server_id);
	if (empty($hardware->error)) {
		$hwtable = '<table id="serverhwtable"><tr><th>Serial</th><th>Item</th></tr>';
		foreach ($hardware->item as $item) {
			$hwtable .= "<tr><td>{$item->serial}</td><td>{$item->description}</td></tr>";
		}
		$hwtable .= '</table>';
		$hwtable .= '<style>
#serverhwtable { margin: 10px 0 10px 0; border: 1px solid #ccc; width: 100%; }
#serverhwtable th { background-color: #333; color: #fff; font-weight: normal; }
#serverhwtable tr td, #bwtable tr th { padding: 3px; }
</style>';
		$var2 .= $hwtable;
	}

	// Get IP addresses
	$ips = oneportal_ipaddresses($params);
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
		$var3 .= '<style>
#serveriptable { margin: 10px 0 10px 0; border: 1px solid #ccc; width: 100%; }
#serveriptable th { background-color: #333; color: #fff; font-weight: normal; }
#serveriptable tr td, #bwtable tr th { padding: 3px; }
#serveriptable input[type="text"] { width: 300px; }
</style>';
	
	}else{
		$var3 = '<p>Unable to determine IP Addresses</p>';
	}

	// Send what we've made
	$fieldsarray = array(
		'Bandwidth' => $var1,
		'Hardware' => $var2,
		'IP Addresses' => $var3
	);

	/*
	$var2 = $data['var2'];

	$fieldsarray = array(
		'Field 1' => '<input type="text" name="modulefields[0]" size="30" value="'.$var1.'" />',
		'Field 2' => '<select name="modulefields[1]"><option>Val1</option</select>',
		'Field 3' => '<textarea name="modulefields[2]" rows="2" cols="80">'.$var3.'</textarea>',
		'Field 4' => $var4, # Info Output Only
	);
	*/

	return $fieldsarray;
}

function template_AdminServicesTabFieldsSave($params) {
	/*
	update_query("mod_customtable",array(
		"var1"=>$_POST['modulefields'][0],
		"var2"=>$_POST['modulefields'][1],
		"var3"=>$_POST['modulefields'][2],
	),array("serviceid"=>$params['serviceid']));
	*/
}

?>
