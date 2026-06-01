<?php
	include 'connectAPI.php';
	
	class API {
		
		private $user_agent;
		private $signature;
		private $base_url = "https://api.nexusggr.com/";
		
		public function __construct($user_agent, $signature) {
			$this->user_agent = $user_agent;
			$this->signature = $signature;
		}
		
		private function postdata($method, $additional_data = []) {
			$base_data = [
            'agent_code' => $this->user_agent,
            'agent_token' => $this->signature,
            'method' => $method
			];
			return array_merge($base_data, $additional_data);
		}
		
		public function money_info()
		{
			$postdata = $this->postdata('money_info');
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		public function money_info_all()
		{
			$postdata = $this->postdata('money_info', [
            'all_users' => true
			]);
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}		
		public function money_info_user($user_code)
		{
			$postdata = $this->postdata('money_info', [
            'user_code' => $user_code
			]);
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		
		public function user_deposit($user_code, $amount)
		{
			$postdata = $this->postdata('user_deposit', [
            'user_code' => $user_code,
            'amount' => $amount
			]);
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		
		public function user_create($user_code)
		{
			$postdata = $this->postdata('user_create', [
            'user_code' => $user_code
			]);
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		
		public function game_launch($user_code, $provider_code, $game_code, $lang = 'en')
		{
			$postdata = $this->postdata('game_launch', [
            'user_code' => $user_code,
            'provider_code' => $provider_code,
            'game_code' => $game_code,
            'lang' => $lang
			]);
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		
		public function user_withdraw($user_code, $amount)
		{
			$postdata = $this->postdata('user_withdraw', [
            'user_code' => $user_code,
            'amount' => $amount
			]);
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		
		public function provider_list()
		{
			$postdata = $this->postdata('provider_list');
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		
		public function game_list($provider_code)
		{
			$postdata = $this->postdata('game_list', [
            'provider_code' => $provider_code
			]);
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		
		public function history_bet()
		{
			$currentDate = date('Y-m-d');
			$postdata = $this->postdata('get_game_log', [
			'game_type' => 'slot',
			'start' => $currentDate . ' 00:00:00',
			'end' => $currentDate . ' 23:59:59',
			'page' => 0,
			'perPage' => 1000
			]);
			
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		public function history_game($user_code, $game_type, $start, $end, $page = 0, $perPage = 1000)
		{
			$postdata = $this->postdata('get_game_log', [
				'user_code' => $user_code,
				'game_type' => $game_type,
				'start' => $start,
				'end' => $end,
				'page' => $page,
				'perPage' => $perPage
			]);
			$url = $this->base_url;
			return $this->send_request($postdata, $url);
		}
		
		
		public function send_request($postdata, $url){
			
			$jsonData = json_encode($postdata);
			
			$headerArray = ['Content-Type: application/json'];
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			// Mengaktifkan penggunaan cookies
			curl_setopt($ch, CURLOPT_COOKIEFILE, '');
			$res = curl_exec($ch);
			if ($res === false) {
				$error = curl_error($ch);
				curl_close($ch);
				throw new Exception("Kesalahan cURL: " . $error);
			}
			
			curl_close($ch);
			
			$decodedResponse = json_decode($res, true);
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new Exception("Kesalahan dalam mendekode JSON: " . json_last_error_msg());
			}
			
			return $decodedResponse;
		}
	}
	
	// Inisialisasi objek API
	$DEV = new API($user_agent, $signature);
?>