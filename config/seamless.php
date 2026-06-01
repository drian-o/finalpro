<?php
class Devgame
{
    public $agent = "Lz6qFLjmAA"; //isi agent kalian  
    public $agent_key = "68a94f7b91a5311cfb2981e6b3542f98";
    public $signature = "95d7af957db3191f1241a88d3f39c64e";  
    public $BASE_API = "https://api.new-version.site/v2/";

    private function generateSignature()
    {
        return md5($this->agent . $this->agent_key);
    }

    public function createuser($username)
    {
        $signature = $this->generateSignature();
        $action = $this->BASE_API . "CreateMember.aspx?agent_code=" . $this->agent . "&username=" . $username . "&signature=" . $signature;
        return $this->connect($action);
    }

    public function transaksi($username, $type, $amount)
    {
        $signature = $this->generateSignature();
        $action = $this->BASE_API . "MakeTransaction.ashx?agent_code=" . $this->agent . "&username=" . $username . "&amount=" . $amount . "&type=" . $type . "&signature=" . $signature;
        return $this->connect($action);
    }

    public function getBalance($username)
    {
        $signature = $this->generateSignature();
        $action = $this->BASE_API . "GetBalance.aspx?agent_code=" . $this->agent . "&username=" . $username . "&signature=" . $signature;
        return $this->connect($action);
    }

    public function getbalanceagent()
    {
        $signature = $this->generateSignature();
        $action = $this->BASE_API . "AgentInfo.ashx?agent_code=" . $this->agent . "&signature=" . $signature;
        return $this->connect($action);
    }    

    public function opengame($username, $gameid)
    {
        $signature = $this->generateSignature();
        $action = $this->BASE_API . "OpenGame.aspx?agent_code=" . $this->agent . "&username=" . $username . "&gameid=" . $gameid . "&signature=" . $signature;
        return $this->connect($action);
    } 

    public function getgamelist($provider_code)
    {
        $signature = $this->generateSignature();
        $action = $this->BASE_API . "GetGameList.aspx?agent_code=" . $this->agent . "&provider_code=" . $provider_code . "&signature=" . $signature;
        return $this->connect($action);
    }

    private function connect($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE); 
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.47 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $output = curl_exec($ch);
        if($output === false) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($output, true);
    }
}

$dg = new Devgame();