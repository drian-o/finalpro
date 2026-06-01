<?php
https://api.88xgame.net/api/v2/
class whitelabel
{

//    public $agen    =   "Alpha6"; //agent code
//	public $token   =   "4c9c9c62e82e936d5a1aed43523c073b"; //token
//	public $url     =   "https://api.diaslotcasino.com/api/v2/";

    public $agen    =   "77cairweb"; //agent code
	public $token   =   "36fa6ce82b470b688289979d515ceef7"; //token
	public $url     =   "https://api.88xgame.net/api/v2/";
	
//	public $agen    =   "Alpha7"; //agent code
//	public $token   =   "e3b97c485a2983eeee1277b268ba16cd"; //token
//	public $url     =   "https://api.diaslotcasino.com/api/v2/";
	
//	public $agen    =   "Mjhdev"; //agent code
//	public $token   =   "bb4144046599960273da95f7237d66e0"; //token
//	public $url     =   "https://api.diaslotcasino.com/api/v2/";
	
// 	public $agen    =   "test123"; //agent code
//	public $token   =   "8b48be15b6dd3df23d7a0cb1e1d45df6"; //token
//	public $url     =   "https://api.telo.is/api/v2/";

    public function CreateMember($username)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'user_code' => $username
        );
        return $this->wl_connect($this->url . 'user_create', $action);
    }
    
    public function deposit($username, $amount)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'user_code' => $username,
            'amount' => $amount
        );
        return $this->wl_connect($this->url . 'user_deposit', $action);
    }
    
    public function withdraw($username, $amount)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'user_code' => $username,
            'amount' => $amount
        );
        return $this->wl_connect($this->url . 'user_withdraw', $action);
    }
    
	public function gameList($provider)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'provider_code' => $provider
        );
        return $this->wl_connect($this->url . 'game_list', $action);
    }
	
	public function getBalanceUser($username)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'user_code' => $username
        );
        return $this->wl_connect($this->url . 'info', $action);
    }
	
	public function getBalanceAgent()
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token
        );
        return $this->wl_connect($this->url . 'info', $action);
    }
	
	public function providerList()
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token
        );
        return $this->wl_connect($this->url . 'provider_list', $action);
    }
    
    public function openGame($username, $game_type, $provider, $game_code, $amounts)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'user_code' => $username,
            'game_type' => $game_type,
            'provider_code' => $provider,
            'game_code' => $game_code,
            'lang' => 'id',
            'user_balance' => $amounts
        );
        return $this->wl_connect($this->url . 'game_launch', $action);
    }
	
	public function callPlayers()
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token
        );
        return $this->wl_connect($this->url . 'current_players', $action);
    }

	public function callList($provider, $gamecode, $usercode, $call_type)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            "provider_code" => $provider,
            "game_code" => $gamecode,
            "user_code" => $usercode,
            "call_type" => $call_type
        );
        return $this->wl_connect($this->url . 'call_list', $action);
    }

	public function callApply($username, $game_code, $provider, $call_rtp, $call_type)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'provider_code' => $provider,
            'game_code' => $game_code,
            'user_code' => $username,
            'call_rtp' => intval($call_rtp),
            'call_type' => $call_type
        );
        return $this->wl_connect($this->url . 'call_apply', $action);
    }

	public function getCallHistory()
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'offset' => 0,
            'limit' => 100
        );
        return $this->wl_connect($this->url . 'call_history', $action);
    }

	public function callCancel($call_id)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'call_id' => $call_id
        );
        return $this->wl_connect($this->url . 'call_cancel', $action);
    }

	public function controlUserRtp($username, $provider, $rtp)
    {
        $action = array(
            'agent_code' => $this->agen,
            'agent_token' => $this->token,
            'provider_code' => $provider,
            'user_code' => $username,
            'rtp' => $rtp
        );
        return $this->wl_connect($this->url . 'user_rtp', $action);
    }

	private function wl_connect($url, $data)
    {
        $ch = curl_init($url);
        $jsonData = json_encode($data);

        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Cache-Control: no-cache"));

        $res = curl_exec($ch);
        curl_close($ch);

        return json_decode($res, true);
    }
}

$WL = new whitelabel();

?>