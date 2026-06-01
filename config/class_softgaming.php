<?php

class Whitelabel
{
    private $agent = "Lz6qFLjmAA"; //isi agent
    private $agent_key = "68a94f7b91a5311cfb2981e6b3542f98";
    private $BASE_API = "https://api.new-version.site/v2/";

    private function generateSignature()
    {
        return md5($this->agent . $this->agent_key);
    }

    public function CreateMember($username)
    {
        $signature = $this->generateSignature();
        $action = $this->BASE_API . "CreateMember.aspx?agent_code=" . $this->agent . "&username=" . $username . "&signature=" . $signature;
        return $this->connect($action);
    }

    public function getBalance($username)
    {
        $signature = $this->generateSignature();
        $url = $this->BASE_API . "GetBalance.aspx?agent_code=" . $this->agent . "&username=" . $username . "&signature=" . $signature;
        return $this->connect($url);
    }

    public function getBalanceAgent()
    {
        $signature = $this->generateSignature();
        $url = $this->BASE_API . "AgentInfo.ashx?agent_code=" . $this->agent . "&signature=" . $signature;
        return $this->connect($url);
    }

    public function transaksi($username, $type, $amount)
    {
        $signature = $this->generateSignature();
        $url = $this->BASE_API . "MakeTransaction.ashx?agent_code=" . $this->agent . "&username=" . $username . "&amount=" . $amount . "&type=" . $type . "&signature=" . $signature;
        return $this->connect($url);
    }

    public function opengame($username, $gameid)
    {
        $signature = $this->generateSignature();
        $url = $this->BASE_API . "OpenGame.aspx?agent_code=" . $this->agent . "&username=" . $username . "&gameid=" . $gameid . "&signature=" . $signature;
        return $this->connect($url);
    } 

    private function connect($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.47 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $output = curl_exec($ch);
        if($output === false) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($output, true);
    }
}

$WL = new Whitelabel(); // Membuat objek Whitelabel
?>