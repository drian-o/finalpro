<?php

class zulhayker
{
    private string $urlRequest;
    private string $agentCode;
    private string $signature;

    public function __construct()
    {
        $this->urlRequest = "https://api.chaosapi.site/v1/";
        $this->agentCode = "eJrFkHtp4O";
        $this->signature = "17222fd7be90e4e6967be6005f495622";
    }

    public function Create(string $username): ?array
    {
        $action = $this->urlRequest . "CreateMember.aspx?agent_code=" . $this->agentCode . "&secret_key=" . $this->signature . "&username=" . urlencode($username);
        return $this->connect($action);
    }

    public function Transaksi(string $username, float $amount, string $type): ?array
    {
        $action = $this->urlRequest . "MakeTransfer.aspx?agent_code=" . $this->agentCode . "&secret_key=" . $this->signature . "&username=" . urlencode($username) . "&amount=" . $amount . "&type=" . $type;
        return $this->connect($action);
    }

    public function GetBalance(string $username): ?array
    {
        $action = $this->urlRequest . "GetBalance.ashx?agent_code=" . $this->agentCode . "&secret_key=" . $this->signature . "&username=" . urlencode($username);
        return $this->connect($action);
    }

    public function OpenGame(string $username, string $game_provider, string $game_type, string $game_code): ?array
    {
        $action = $this->urlRequest . "LaunchGame.aspx?agent_code=" . $this->agentCode . "&secret_key=" . $this->signature . "&username=" . urlencode($username) . "&provider=" . $game_provider . "&game_type=" . $game_type . "&game_code=" . $game_code;
        return $this->connect($action);
    }

    public function GetProviderList(): ?array
    {
        $action = $this->urlRequest . "GetProviderList.aspx?agent_code=" . $this->agentCode . "&secret_key=" . $this->signature;
        return $this->connect($action);
    }

    public function GetGameList(string $provider_code): ?array
    {
        $action = $this->urlRequest . "GetGameList.aspx?agent_code=" . $this->agentCode . "&secret_key=" . $this->signature . "&provider_code=" . urlencode($provider_code);
        return $this->connect($action);
    }

    public function InfoAgent(): ?array
    {
        $action = $this->urlRequest . "GetBalanceAgent.ashx?agent_code=" . $this->agentCode . "&secret_key=" . $this->signature;
        return $this->connect($action);
    }

    public function PlayerInGame(string $username, string $provider): ?array
    {
        $action = $this->urlRequest . "PlayerInGame.aspx?agent_code=" . $this->agentCode . "&secret_key=" . $this->signature . "&username=" . urlencode($username) . "&provider=" . urlencode($provider);
        return $this->connect($action);
    }

    private function connect(string $url): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.47 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
        curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 2);
        curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 3);
        $output = curl_exec($ch);
        // Check for cURL errors
        if (curl_errno($ch)) {
            // Optionally log curl_error($ch)
            curl_close($ch);
            return null; // Return null on cURL error
        }
        curl_close($ch);
        // Ensure $output is a string before decoding, though curl_exec should return string or false
        if (!is_string($output)) {
            return null; // Return null if $output is not a string (e.g., false from curl_exec failure not caught by curl_errno)
        }
        return json_decode($output, true);
    }
}

$WL = new zulhayker();

?>