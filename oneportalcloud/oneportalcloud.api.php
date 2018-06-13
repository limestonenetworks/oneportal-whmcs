<?php
class OnePortalCloud {

	public $url;
	public $version;
	public $user;
	public $pass;
	public $module;
	public $action;


	public function __construct($user,$pass, $url = null) {
		if (empty($user)) throw new Exception('OnePortal API user must be provided.');
		if (empty($pass)) throw new Exception('OnePortal API pass must be provided.');
		if (empty($url)) $url = 'https://api.dallas-idc.com/v1';
		$this->url = $url;
		$this->version = 1.1;
		$this->user = $user;
		$this->pass = $pass;

		return true;
	}
	public function serverlist($server_id = '') {
		$url = '/server/' . $server_id;
		return $this->APIQuery($url, 'GET');
	}
	public function lookup_ip($ip){
		$url = '/ipaddress/' . $ip;
		return $this->APIQuery($url);
	}
	public function ipaddresses($server_id) {

		$url = '/server/'.$server_id.'/ipaddress';
		$ips = $this->APIQuery($url, 'GET' );
		foreach($ips as &$ip){
			$ip->ipaddress = long2ip($ip->ipaddress);
		}
		return $ips;
	}
	public function findOption($name,$category,$core){
		$url = '/core/'.$core.'/category/'.$category.'/find/'.$name;
		$opt = $this->APIQuery($url,'GET');

		return $opt;
	}
	public function gethardware($server_id) {
		$url = '/server/' .$server_id . '/hardware';
		return $this->APIQuery($url,'GET');
	}
	public function restart($server_id) {
		$url = '/server/' . $server_id . '/restart';
		return $this->APIQuery($url,'POST');
	}

	public function turnoff($server_id) {
		$url = '/server/' . $server_id . '/stop';
		return $this->APIQuery($url,'POST');
	}
	public function turnon($server_id) {

		$url = '/server/' . $server_id . '/start';
		return $this->APIQuery($url,'POST');
	}
	public function suspend($server_id) {

		$url = '/server/' . $server_id . '/suspend';
		return $this->APIQuery($url,'POST');
	}
	public function unsuspend($server_id) {

		$url = '/server/' . $server_id . '/unsuspend';
		return $this->APIQuery($url,'POST');
	}
	public function getStatus($server_id) {

		$url = '/server/' . $server_id . '/status';
		return $this->APIQuery($url,'GET');
	}
	public function createServer($data){
		$url = '/server';
		return $this->APIQuery($url,'POST',$data);
	}
	public function cancelServer($server_id){
		$url = '/server/' . $server_id;
		return $this->APIQuery($url,'DELETE');
	}
	public function upgradeServer($server_id,$data){
		$url = '/server/' . $server_id;
		return $this->APIQuery($url,'PUT',$data);
	}
	public function getFireWallRules($server_id){
		$url = '/server/' . $server_id . '/firewall';
		return $this->APIQuery($url,'GET');
	}
	public function updateFirewallRuleOrder($server_id,$data){
		$url = '/server/' . $server_id . '/firewall/order';
		return $this->APIQuery($url,'PUT',$data);
	}
	public function deleteFirewallRule($server_id,$rule_id){
		$url = '/server/' . $server_id . '/firewall/' . $rule_id;
		return $this->APIQuery($url,'DELETE');
	}
	public function createFirewallRule($server_id,$data){
		$url = '/server/' . $server_id . '/firewall';
		return $this->APIQuery($url,'POST',$data);
	}
	public function updateDefaultFirewallRules($server_id,$data){
		$url = '/server/' . $server_id . '/firewall/default';
		return $this->APIQuery($url,'PUT',$data);
	}
	public function changePassword($server_id,$password){
		$data = array('password'=>$password);
		return $this->upgradeServer($server_id,$data);
	}
	public function rebuildNetwork($server_id){
		$url = '/server/' . $server_id . '/network';
		return $this->APIQuery($url,'GET');
	}
	public function dns_setreverse($ip, $domain){
		$url = "/ipaddress/$ip/reverse";
		return $this->APIQuery($url,'PUT',array('domain'=>$domain));
	}
	/* API Call Function */
	public function APIQuery($url, $method = 'GET', $args = FALSE) {

		$apiurl = $this->url . $url;
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
		curl_setopt($apisess, CURLOPT_USERPWD, "{$this->user}:{$this->pass}");
		/* SSL Options */
		curl_setopt($apisess, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
                curl_setopt($apisess, CURLOPT_SSL_CIPHER_LIST, 'ecdhe_rsa_aes_128_cbc_sha_256');
		curl_setopt($apisess, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($apisess, CURLOPT_SSL_VERIFYHOST, 0);
		if($method == 'PUT' && $args !== false){
			$args['_method'] = 'PUT';
			//hacking put support
			$method = 'POST';
		}

		/* POST method options */
		if ($method == 'POST' && $args !== false) {
			curl_setopt($apisess, CURLOPT_POST, 1);
			curl_setopt($apisess, CURLOPT_POSTFIELDS, http_build_query($args));
		}
		if($method == 'DELETE'){
			curl_setopt($apisess, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
		$response = trim(curl_exec($apisess));
		curl_close($apisess);
		/* Error handling */
		if (!$response) return FALSE;
		if (strlen($response) < 25) return FALSE;

		$final = json_decode($response);

		return $final;
	}
}

