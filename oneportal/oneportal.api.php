<?php
class OnePortal {

	public $url;
	public $version;
	public $api_key;

	public $module;
	public $action;


	public function __construct($api_key, $url = null) {
		if (empty($api_key)) throw new Exception('OnePortal API Key must be provided.');
		if (empty($url)) $url = 'https://one.limestonenetworks.com/webservices/clientapi.php';

		$this->url = $url . '?';
		$this->version = 1.0;
		$this->api_key = $api_key;

		return true;
	}

	/* Blind functions */
	public function get_balance() { return $this->APIQuery('billing', 'getbalance', 'GET', FALSE); }
	public function unpaid() { return $this->APIQuery('billing', 'unpaid', 'GET', FALSE); }
	public function sources() { return $this->APIQuery('billing', 'sources', 'GET', FALSE); }
	public function getprobtypes() { return $this->APIQuery('support', 'getprobtypes', 'GET', FALSE); }
	public function getOperatingSystems() { return $this->APIQuery('servers', 'getOperatingSystems', 'GET', FALSE); }
	public function phishingstatus() { return $this->APIQuery('servers', 'phishingstatus', 'GET', FALSE); }


	public function serverlist($server_id = false) {
		if (isset($server_id)) $filter['server_id'] = $server_id;
		return $this->APIQuery('servers', 'list', 'GET', (sizeof($filter) ? $filter : FALSE));
	}
  
	public function history($limit = 100) {
		return $this->APIQuery('billing', 'history', 'GET', (isset($limit) ? array('limit' => $limit) : FALSE) );
	}
  
	public function ipaddresses($ipaddress = FALSE, $server_id = FALSE, $network = 'public', $type = FALSE) {
		if (!empty($ipaddress)) $filter['ipaddress'] = $ipaddress;
		if (!empty($server_id)) $filter['server_id'] = $server_id;
		if (!empty($network)) $filter['network'] = $network;
		if (!empty($type)) $filter['type'] = $type;

		$ips = $this->APIQuery('ipaddresses', 'list', 'GET', (sizeof($filter) ? $filter : FALSE) );

		return $ips;
	}

	public function pay($invoiceid, $sourceid, $amount = FALSE) {
		$filter['invoiceid'] = $invoiceid;
		$filter['sourceid']  = $sourceid;
		if (isset($amount) && $amount !== FALSE) $filter['amount'] = $amount;
		return $this->APIQuery('billing', 'pay', 'POST', $filter);
	}

	public function dns_setreverse($ipaddress, $value) {
		$filter['ipaddress'] = $ipaddress;
		$filter['value']     = $value;
		$setrdns = $this->APIQuery('dns', 'setreverse', 'POST', $filter);
		return $setrdns;
	}

	public function gethardware($server_id) {
		$filter['server_id'] = $server_id;
		return $this->APIQuery('servers', 'gethardware', 'GET', $filter);
	}

	public function reload($server_id, $os, $password) {
		$filter['server_id'] = $server_id;
		$filter['os'] = $os;  // available from $this->getOperatingSystems->attributes()->id
		$filter['password'] = $password; // Please change this!
		return $this->APIQuery('servers', 'reload', 'GET', $filter);
	}

	public function rename($newname, $server_id) {
		$filter['serverid'] = $server_id;
		$filter['newname'] = $newname;
		return $this->APIQuery('servers', 'rename', 'GET', $filter);
	}

	public function restart($server_id) {
		$filter['serverid'] = $server_id;
		return $this->APIQuery('servers', 'restart', 'GET', $filter);
	}

	public function turnoff($server_id) {
		$filter['serverid'] = $server_id;
		return $this->APIQuery('servers', 'turnoff', 'GET', $filter);
	}

	public function turnon($server_id) {
		$filter['serverid'] = $server_id;
		return $this->APIQuery('servers', 'turnon', 'GET', $filter);
	}

	public function portcontrol($server_id, $port='public', $action='on') {
		$filter['serverid'] = $server_id;
		$filter['port'] = $port;
		$filter['set'] = $action;
		return $this->APIQuery('servers', 'portcontrol', 'GET', $filter);
	}

	public function bwgraph($server_id, $start = false, $stop = false) {
		$filter['server_id'] = $server_id;
		$filter['start'] = time()-3600;
		$filter['stop'] = time();
		if (!empty($start)) $filter['start'] = $start;
		if (!empty($stop)) $filter['stop'] = $stop;
		return $this->APIQuery('servers', 'bwgraph', 'GET', (sizeof($filter) ? $filter : FALSE));
	}

	public function addticket($probtype, $summary, $description, $user_id, $server = FALSE, $admin_user = FALSE, $admin_pass = FALSE) {
		$filter['probtype']    = $probtype;
		$filter['summary']     = $summary;
		$filter['description'] = $description;
		$filter['user_id']     = $user_id;
		if (isset($server) && $server !== FALSE) $filter['server'] = $server;
		if (isset($admin_user) && $admin_user !== FALSE) $filter['admin_user'] = $admin_user;
		if (isset($admin_pass) && $admin_pass !== FALSE) $filter['admin_pass'] = $admin_pass;
		return $this->APIQuery('support', 'addticket', 'POST', $filter);
	}

	public function listtickets($status = 'open') {
		if (isset($status)) $filter['status'] = $status;
		return $this->APIQuery('support', 'listtickets', 'GET', $filter);
	}

	public function setstatus($ticket, $user_id, $status) {
		$filter['ticket'] = $ticket;
		$filter['user_id']= $user_id;
		$filter['status'] = $status;
		return $this->APIQuery('support', 'setstatus', 'POST', $filter);
	}

	public function updateticket($ticket, $user_id, $message) {
		$filter['ticket'] = $ticket;
		$filter['user_id']= $user_id;
		$filter['message'] = $message;
		return $this->APIQuery('support', 'updateticket', 'POST', $filter);
	}

	public function viewticket($ticket) {
		$filter['ticket'] = $ticket;
		return $this->APIQuery('support', 'viewticket', 'GET', $filter);
	}

	public function userlist() {
		return $this->APIQuery('users', 'list', 'GET', FALSE);
	}

	/* API Call Function */
	public function APIQuery($module, $action, $method = 'GET', $args = FALSE) {
		$this->module = $module;
		$this->action = $action;

		$apiurl = $this->url . 
			"key="     . $this->api_key .
			"&mod="    . $module .
			"&action=" . $action;

		if ($method == 'GET' && $args !== FALSE) $apiurl .= "&" . http_build_query($args);

		/* Initialize cURL Session */
		$apisess = curl_init();

		/* Set generic options */
		curl_setopt($apisess, CURLOPT_URL, $apiurl);
		curl_setopt($apisess, CURLOPT_USERAGENT, 'OnePortal PHP-API/' . $this->version);
		curl_setopt($apisess, CURLOPT_HEADER, 0);
		curl_setopt($apisess, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($apisess, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($apisess, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($apisess, CURLOPT_TIMEOUT, 30);
		curl_setopt($apisess, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($apisess, CURLOPT_VERBOSE, 0);
		curl_setopt($apisess, CURLOPT_HTTP_VERSION, '1.0');
		
		/* SSL Options */
		curl_setopt($apisess, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
                curl_setopt($apisess, CURLOPT_SSL_CIPHER_LIST, 'ecdhe_rsa_aes_128_cbc_sha_256');
		curl_setopt($apisess, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($apisess, CURLOPT_SSL_VERIFYHOST, 2);

		/* POST method options */
		if ($method == 'POST') {
			curl_setopt($apisess, CURLOPT_POST, 1);
			curl_setopt($apisess, CURLOPT_POSTFIELDS, http_build_query($args));
		}

		$response = trim(curl_exec($apisess));
		curl_close($apisess);

		/* Error handling */
		if (!$response) return FALSE;
		if (strlen($response) < 25) return FALSE;

		/* Handle Bandwidth graphs */
		if ($module == 'servers' && $action == 'bwgraph') {
			$final = sprintf("<img src='data:image/png;base64,%s'>", base64_encode($response));
		} else {
			$final = new SimpleXMLElement($response); // rename $return = 
		}

		return $final;
	}
}
?>
