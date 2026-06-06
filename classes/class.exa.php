<?php

class GameXaAPI {
    private $baseURL = "https://api.httpsgamexaglobal.net";
    private $jwtToken = null; // Ubah ini menjadi null agar otomatis dipanggil
    private $agentCode = "AG1772576397689D2S8"; // Ganti dengan agent code Anda
    private $password = "123@superbone"; // Ganti dengan password Anda

    public function __construct($jwtToken = null, $agentCode = null, $password = null) {
        if ($jwtToken) {
            $this->jwtToken = $jwtToken;
        }
        if ($agentCode) {
            $this->agentCode = $agentCode;
        }
        if ($password) {
            $this->password = $password;
        }
    }

    /**
     * Metode inti untuk melakukan panggilan API.
     * Akan otomatis mengautentikasi jika token belum ada atau dianggap perlu.
     */
    private function callAPI($method, $endpoint, $headers = [], $body = [], $query = []) {
        // --- Otentikasi Otomatis ---
        // Jika token belum ada atau kosong, coba dapatkan token terlebih dahulu
        if (empty($this->jwtToken) && $endpoint !== '/api/auth/login') {
            $authResponse = $this->authenticateAgent();
            if (!$authResponse['success']) {
                // Jika otentikasi gagal, kembalikan error
                return [
                    'success' => false,
                    'message' => 'Gagal mengautentikasi agen sebelum memanggil API.',
                    'code' => $authResponse['code'] ?? 0,
                    'data' => $authResponse['data'] ?? []
                ];
            }
        }
        // --- Akhir Otentikasi Otomatis ---

        $url = $this->baseURL . $endpoint;

        if (!empty($query)) {
            $url .= "?" . http_build_query($query);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        $defaultHeaders = [
            'Content-Type: application/json',
        ];

        // Hanya tambahkan header Authorization jika token ada dan bukan endpoint login
        if (!empty($this->jwtToken) && $endpoint !== '/api/auth/login') {
            $defaultHeaders[] = 'Authorization: Bearer ' . $this->jwtToken;
        }

        $allHeaders = array_merge($defaultHeaders, $headers);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $allHeaders);

        if (!empty($body)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $responseData = json_decode($response, true);

        if ($httpCode >= 400) {
            // Handle API errors
            error_log("API Error: HTTP Code {$httpCode}, Response: {$response}");
            return [
                'success' => false,
                'message' => isset($responseData['message']) ? $responseData['message'] : 'Unknown error',
                'code' => $httpCode,
                'data' => $responseData
            ];
        }

        return [
            'success' => true,
            'data' => $responseData
        ];
    }

    // --- Autentikasi ---

    /**
     * Mengautentikasi agen dan mendapatkan access token.
     * @return array Respon dari API
     */
    public function authenticateAgent() {
        $body = [
            "agent_code" => $this->agentCode,
            "password" => $this->password
        ];
        $response = $this->callAPI("POST", "/api/auth/login", [], $body);
        if ($response['success'] && isset($response['data']['token'])) {
            $this->jwtToken = $response['data']['token'];
        }
        return $response;
    }

    /**
     * Mendapatkan informasi agen saat ini.
     * @return array Respon dari API
     */
    public function getCurrentAgentInfo() {
        return $this->callAPI("GET", "/api/auth/me");
    }

    // --- Manajemen Pemain ---

    /**
     * Membuat akun pemain/anggota baru.
     * @param string $username Nama pengguna pemain
     * @param string $email Email pemain
     * @param string $password Kata sandi pemain
     * @param string $fullName Nama lengkap pemain
     * @param string $phone Nomor telepon pemain
     * @param string $currency Mata uang pemain (misal: IDR)
     * @return array Respon dari API
     */
    public function createPlayer($username, $email, $password, $fullName, $phone, $currency) {
        $body = [
            "username" => $username,
            "email" => $email,
            "password" => $password,
            "full_name" => $fullName,
            "phone" => $phone,
            "currency" => $currency
        ];
        return $this->callAPI("POST", "/api/players", [], $body);
    }

    /**
     * Mendapatkan daftar semua pemain dengan paginasi dan filter.
     * @param int $page Nomor halaman
     * @param int $limit Batas pemain per halaman
     * @param string $search Kata kunci pencarian opsional
     * @param string $status Status pemain (active, inactive, dll.)
     * @return array Respon dari API
     */
    public function getPlayers($page = 1, $limit = 10, $search = null, $status = null) {
        $query = [
            "page" => $page,
            "limit" => $limit
        ];
        if ($search) {
            $query["search"] = $search;
        }
        if ($status) {
            $query["status"] = $status;
        }
        return $this->callAPI("GET", "/api/players", [], [], $query);
    }

    /**
     * Mendapatkan saldo saat ini dari pemain tertentu.
     * @param int $playerId ID pemain
     * @return array Respon dari API
     */
    public function getPlayerBalance($playerId) {
        return $this->callAPI("GET", "/api/players/{$playerId}/balance");
    }

    // --- Transaksi ---

    /**
     * Menyimpan uang ke akun pemain.
     * @param int $playerId ID pemain
     * @param float $amount Jumlah yang akan disetor
     * @param string $referenceId ID referensi transaksi
     * @return array Respon dari API
     */
    public function depositToPlayer($playerId, $amount, $referenceId) {
        $body = [
            "amount" => $amount,
            "reference_id" => $referenceId
        ];
        return $this->callAPI("POST", "/api/players/{$playerId}/deposit", [], $body);
    }

    /**
     * Menarik uang dari akun pemain. Jumlah yang ditarik akan dikembalikan ke saldo agen.
     * @param int $playerId ID pemain
     * @param float $amount Jumlah yang akan ditarik
     * @param string $referenceId ID referensi transaksi
     * @return array Respon dari API
     */
    public function withdrawFromPlayer($playerId, $amount, $referenceId) {
        $body = [
            "amount" => $amount,
            "reference_id" => $referenceId
        ];
        return $this->callAPI("POST", "/api/players/{$playerId}/withdraw", [], $body);
    }

    /**
     * Mendapatkan riwayat transaksi untuk pemain tertentu.
     * @param int $playerId ID pemain
     * @param int $page Nomor halaman
     * @param int $limit Batas transaksi per halaman
     * @param string $type Jenis transaksi (deposit|withdrawal)
     * @return array Respon dari API
     */
    public function getPlayerTransactions($playerId, $page = 1, $limit = 50, $type = null) {
        $query = [
            "page" => $page,
            "limit" => $limit
        ];
        if ($type) {
            $query["type"] = $type;
        }
        return $this->callAPI("GET", "/api/players/{$playerId}/transactions", [], [], $query);
    }

    // --- Manajemen Penyedia Game ---

    /**
     * Mendapatkan daftar semua penyedia game aktif dengan jumlah game dan detail.
     * @return array Respon dari API
     */
    public function getGameProviders() {
        return $this->callAPI("GET", "/api/games/providers");
    }

    /**
     * Mendapatkan semua game untuk penyedia tertentu berdasarkan kode penyedia.
     * @param string $providerCode Kode penyedia (misal: PRAGMATIC)
     * @param int $page Nomor halaman
     * @param int $limit Batas game per halaman
     * @param string $status Status game (active|inactive|maintenance)
     * @return array Respon dari API
     */
    public function getGamesByProvider($providerCode, $page = 1, $limit = 50000, $status = null) {
        $query = [
            "page" => $page,
            "limit" => $limit
        ];
        if ($status) {
            $query["status"] = $status;
        }
        return $this->callAPI("GET", "/api/games/provider/{$providerCode}", [], [], $query);
    }

    // --- Manajemen Game ---

    /**
     * Mendapatkan daftar semua game yang tersedia dengan filtering dan paginasi.
     * @param int $page Nomor halaman
     * @param int $limit Batas game per halaman
     * @param string $search Nama game atau game_uid
     * @param string $provider Kode penyedia (misal: PRAGMATIC)
     * @param string $type Jenis game (slot|table|card|lottery|sports)
     * @param string $status Status game (active|inactive|maintenance)
     * @return array Respon dari API
     */
    public function getAllGames($page = 1, $limit = 50000, $search = null, $provider = null, $type = null, $status = null) {
        $query = [
            "page" => $page,
            "limit" => $limit
        ];
        if ($search) {
            $query["search"] = $search;
        }
        if ($provider) {
            $query["provider"] = $provider;
        }
        if ($type) {
            $query["type"] = $type;
        }
        if ($status) {
            $query["status"] = $status;
        }
        return $this->callAPI("GET", "/api/games", [], [], $query);
    }

    /**
     * Meluncurkan game untuk pemain tertentu.
     * @param int $playerId ID pemain
     * @param string $gameUid UID game (misal: GATE_OF_OLYMPUS)
     * @param float $creditAmount Jumlah kredit untuk game
     * @return array Respon dari API
     */
public function launchGame($playerId, $gameUid, $currency = 'IDR', $lobbyUrl = "https://indahpemper.icu/home") {
    $body = [
        "player_id" => $playerId,
        "game_uid" => $gameUid,
        "currency" => $currency,
        "lobby_url" => $lobbyUrl
    ];
    return $this->callAPI("POST", "/api/games/launch", [], $body);
}

    // --- Manajemen Transaksi Umum ---

    /**
     * Mendapatkan daftar semua transaksi dengan filtering dan paginasi.
     * @param int $page Nomor halaman
     * @param int $limit Batas transaksi per halaman
     * @param string $search Kata kunci pencarian opsional
     * @param string $type Jenis transaksi (deposit|withdrawal|bet|win)
     * @param string $status Status transaksi (completed|pending|failed)
     * @param string $startDate Tanggal mulai (YYYY-MM-DD)
     * @param string $endDate Tanggal akhir (YYYY-MM-DD)
     * @return array Respon dari API
     */
    public function getAllTransactions($page = 1, $limit = 500, $search = null, $type = null, $status = null, $startDate = null, $endDate = null) {
        $query = [
            "page" => $page,
            "limit" => $limit
        ];
        if ($search) {
            $query["search"] = $search;
        }
        if ($type) {
            $query["type"] = $type;
        }
        if ($status) {
            $query["status"] = $status;
        }
        if ($startDate) {
            $query["start_date"] = $startDate;
        }
        if ($endDate) {
            $query["end_date"] = $endDate;
        }
        return $this->callAPI("GET", "/api/transactions", [], [], $query);
    }

    /**
     * Mendapatkan statistik transaksi dan laporan harian.
     * @param string $startDate Tanggal mulai (YYYY-MM-DD)
     * @param string $endDate Tanggal akhir (YYYY-MM-DD)
     * @return array Respon dari API
     */
    public function getTransactionStats($startDate, $endDate) {
        $query = [
            "start_date" => $startDate,
            "end_date" => $endDate
        ];
        return $this->callAPI("GET", "/api/transactions/stats", [], [], $query);
    }

    /**
     * Fungsi alias untuk membuat user baru, dengan asumsi otentikasi sudah ditangani secara internal.
     * Ini akan secara otomatis melakukan otentikasi jika token belum ada atau tidak valid.
     * @param string $username Nama pengguna pemain
     * @param string $email Email pemain
     * @param string $password Kata sandi pemain
     * @param string $fullName Nama lengkap pemain
     * @param string $phone Nomor telepon pemain
     * @param string $currency Mata uang pemain (misal: IDR)
     * @return array Respon dari API
     */
    public function CreateUser($username, $email = null, $password = null, $fullName = null, $phone = null, $currency = "IDR") {
        // Otentikasi otomatis sudah ditangani di callAPI, jadi tidak perlu di sini lagi
        // Default nilai jika tidak disediakan
        if ($email === null) {
            $email = $username . "@sigmabet.pro";
        }
        if ($password === null) {
            $password = "defaultpassword123"; // Kata sandi default yang aman
        }
        if ($fullName === null) {
            $fullName = ucfirst($username);
        }
        if ($phone === null) {
            $phone = "+62" . rand(100000000, 999999999); // Nomor telepon acak Indonesia
        }

        return $this->createPlayer($username, $email, $password, $fullName, $phone, $currency);
    }
}

?>
