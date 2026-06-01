<?php
// Bagian ini akan menangani permintaan AJAX
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'check_account') {
    header('Content-Type: application/json');

    $baseAPIurl = "https://cekrekening-api.belibayar.online/api/v1/account-inquiry";
    $bankKey = $_POST['account_bank'] ?? '';
    $accountNumber = $_POST['account_number'] ?? '';

    if (empty($bankKey) || empty($accountNumber)) {
        echo json_encode(['status' => 'error', 'message' => 'Bank and account number are required.']);
        exit();
    }

    $payload = json_encode([
        'account_bank' => $bankKey,
        'account_number' => $accountNumber
    ]);

    $ch = curl_init($baseAPIurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($payload)]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo json_encode(['status' => 'error', 'message' => 'API Error: ' . $error]);
        exit();
    }
    
    $data = json_decode($response, true);

    if ($http_code == 200 && isset($data['data']['account_holder'])) {
        echo json_encode(['status' => 'success', 'message' => $data['data']['account_holder']]);
    } else {
        echo json_encode(['status' => 'warning', 'message' => $data['message'] ?? 'Failed to get account holder information.']);
    }
    
    exit();
}

// Data untuk merender halaman awal
$rekeningData = [
    "banks" => [
        // Bank-bank diurutkan berdasarkan label untuk kemudahan navigasi
        ["key" => "aceh", "label" => "Bank Aceh Syariah"],
        ["key" => "agris", "label" => "Bank IBK Indonesia"],
        ["key" => "agroniaga", "label" => "BRI Agroniaga"],
        ["key" => "aladin", "label" => "Bank Aladin Syariah"],
        ["key" => "allo", "label" => "Allo Bank / Bank Harda Internasional"], // Perbaikan key & label
        ["key" => "amar", "label" => "Bank Amar Indonesia"],
        ["key" => "america_na", "label" => "Bank of America NA"],
        ["key" => "antardaerah", "label" => "Bank Antardaerah"],
        ["key" => "anz", "label" => "ANZ Indonesia"],
        ["key" => "artha", "label" => "Bank Artha Graha Internasional"],
        ["key" => "artos", "label" => "Jago / Artos"], // Perbaikan label
        ["key" => "artos_syr", "label" => "Bank Jago Syariah"],
        ["key" => "bali", "label" => "BPD Bali"],
        ["key" => "banten", "label" => "BPD Banten"],
        ["key" => "bca", "label" => "BCA (Bank Central Asia)"],
        ["key" => "bca_syr", "label" => "BCA Syariah"], // Lebih ringkas
        ["key" => "bengkulu", "label" => "Bank Bengkulu"],
        ["key" => "bjb", "label" => "Bank BJB"], // Penambahan "Bank"
        ["key" => "bjb_syr", "label" => "Bank BJB Syariah"],
        ["key" => "blu_bca_digital", "label" => "Blu by BCA Digital"], // Perbaikan key & label
        ["key" => "bni", "label" => "BNI (Bank Negara Indonesia)"],
        ["key" => "bnp_paribas", "label" => "BNP Paribas Indonesia"],
        ["key" => "boc", "label" => "Bank of China (Hong Kong) Limited"],
        ["key" => "bri", "label" => "BRI (Bank Rakyat Indonesia)"], // Penambahan "BRI"
        ["key" => "bsm", "label" => "BSI (Bank Syariah Indonesia)"],
        ["key" => "btn", "label" => "BTN (Bank Tabungan Negara)"], // Penambahan "BTN"
        ["key" => "btn_syr", "label" => "BTN Syariah"],
        ["key" => "btpn", "label" => "BTPN"],
        ["key" => "btpn_syr", "label" => "Bank BTPN Syariah"],
        ["key" => "bukopin", "label" => "Wokee by Bukopin"], // Perbaikan label
        ["key" => "bukopin_syr", "label" => "Bank Bukopin Syariah"],
        ["key" => "bumi_arta", "label" => "Bank Bumi Arta"],
        ["key" => "capital", "label" => "Bank Capital Indonesia"],
        ["key" => "ccb", "label" => "Bank China Construction Bank Indonesia"],
        ["key" => "cimb", "label" => "CIMB Niaga"], // Disarankan pisahkan syariah jika API berbeda
        ["key" => "cimb_syr", "label" => "CIMB Niaga Syariah"], // Ditambahkan jika perlu dipisah
        ["key" => "citibank", "label" => "Citibank"],
        ["key" => "cnb", "label" => "Bank CNB (Centratama Nasional Bank)"],
        ["key" => "commonwealth", "label" => "Commonwealth Bank"],
        ["key" => "ctbc", "label" => "CTBC (Chinatrust) Indonesia"],
        ["key" => "daerah_istimewa", "label" => "Bank BPD DIY"],
        ["key" => "daerah_istimewa_syr", "label" => "Bank BPD DIY Syariah"],
        ["key" => "danamon", "label" => "Bank Danamon"], // Disarankan pisahkan syariah jika API berbeda
        ["key" => "danamon_syr", "label" => "Bank Danamon Syariah"], // Ditambahkan jika perlu dipisah
        ["key" => "dbs", "label" => "DBS Indonesia"],
        ["key" => "dinar", "label" => "Bank Dinar Indonesia"],
        ["key" => "dki", "label" => "Bank DKI"],
        ["key" => "dki_syr", "label" => "Bank DKI Syariah"],
        ["key" => "eka", "label" => "BPR EKA (Bank Eka)"],
        ["key" => "ganesha", "label" => "Bank Ganesha"],
        ["key" => "hana", "label" => "LINE Bank / KEB Hana"], // Perbaikan label
        ["key" => "hsbc", "label" => "HSBC Indonesia"],
        ["key" => "icbc", "label" => "ICBC Indonesia"],
        ["key" => "index_selindo", "label" => "Bank Index Selindo"],
        ["key" => "india", "label" => "Bank of India Indonesia"],
        ["key" => "ina_perdana", "label" => "Bank Ina Perdana"],
        ["key" => "jambi", "label" => "Bank Jambi"],
        ["key" => "jambi_syr", "label" => "Bank Jambi Syariah"],
        ["key" => "jasa_jakarta", "label" => "Bank Jasa Jakarta"],
        ["key" => "jawa_tengah", "label" => "Bank Jateng"],
        ["key" => "jawa_tengah_syr", "label" => "Bank Jateng Syariah"],
        ["key" => "jawa_timur", "label" => "Bank Jatim"],
        ["key" => "jawa_timur_syr", "label" => "Bank Jatim Syariah"],
        ["key" => "kalimantan_barat", "label" => "Bank Kalbar"],
        ["key" => "kalimantan_barat_syr", "label" => "Bank Kalbar Syariah"],
        ["key" => "kalimantan_selatan", "label" => "Bank Kalsel"],
        ["key" => "kalimantan_selatan_syr", "label" => "Bank Kalsel Syariah"],
        ["key" => "kalimantan_tengah", "label" => "Bank Kalteng"],
        ["key" => "kalimantan_timur", "label" => "Bank Kaltimtara"],
        ["key" => "kalimantan_timur_syr", "label" => "Bank Kaltim Syariah"],
        ["key" => "kesejahteraan_ekonomi", "label" => "Seabank / Bank BKE"], // Perbaikan label
        ["key" => "krom", "label" => "Krom Bank Indonesia"],
        ["key" => "lampung", "label" => "Bank Lampung"],
        ["key" => "mandiri", "label" => "Bank Mandiri"],
        ["key" => "mantap", "label" => "Bank MANTAP (Mandiri Taspen)"],
        ["key" => "mas", "label" => "Bank Multi Arta Sentosa (Bank MAS)"],
        ["key" => "maspion", "label" => "Bank Maspion Indonesia"],
        ["key" => "mayapada", "label" => "Bank Mayapada"],
        ["key" => "maybank_indonesia", "label" => "Maybank Indonesia"],
        ["key" => "maybank_syr", "label" => "Maybank Syariah"],
        ["key" => "mayora", "label" => "Bank Mayora Indonesia"],
        ["key" => "mega", "label" => "Bank Mega"],
        ["key" => "mega_syr", "label" => "Bank Mega Syariah"],
        ["key" => "mestika_dharma", "label" => "Bank Mestika Dharma"],
        ["key" => "mizuho", "label" => "Bank Mizuho Indonesia"],
        ["key" => "mnc_internasional", "label" => "Motion / MNC Bank"], // Perbaikan label
        ["key" => "muamalat", "label" => "Bank Muamalat"], // Penambahan "Bank"
        ["key" => "mutiara", "label" => "Bank Mutiara"],
        ["key" => "nationalnobu", "label" => "Nobu (Nationalnobu) Bank"],
        ["key" => "nusa_tenggara_barat", "label" => "Bank NTB Syariah"],
        ["key" => "nusa_tenggara_timur", "label" => "Bank NTT"],
        ["key" => "nusantara_parahyangan", "label" => "Bank Nusantara Parahyangan"],
        ["key" => "ocbc", "label" => "Bank OCBC NISP"],
        ["key" => "ocbc_syr", "label" => "Bank OCBC NISP Syariah"],
        ["key" => "panin", "label" => "Panin Bank"],
        ["key" => "panin_syr", "label" => "Panin Dubai Syariah"],
        ["key" => "papua", "label" => "Bank Papua"],
        ["key" => "permata", "label" => "Bank Permata"], // Penambahan "Bank"
        ["key" => "permata_syr", "label" => "Bank Permata Syariah"],
        ["key" => "prima", "label" => "Bank Prima Master"],
        ["key" => "qnb_kesawan", "label" => "QNB Indonesia"],
        ["key" => "rabobank", "label" => "Rabobank International Indonesia"],
        ["key" => "resona_perdania", "label" => "Bank Resona Perdania"],
        ["key" => "riau_dan_kepri", "label" => "Bank Riau Kepri"],
        ["key" => "sahabat_sampoerna", "label" => "Bank Sahabat Sampoerna"],
        ["key" => "sbi_indonesia", "label" => "SBI Indonesia"],
        ["key" => "shinhan", "label" => "Bank Shinhan Indonesia"],
        ["key" => "sinarmas", "label" => "Bank Sinarmas"],
        ["key" => "sinarmas_syr", "label" => "Bank Sinarmas Syariah"],
        ["key" => "standard_chartered", "label" => "Standard Chartered Bank"],
        ["key" => "sulawesi", "label" => "Bank Sulteng"],
        ["key" => "sulawesi_tenggara", "label" => "Bank Sultra"],
        ["key" => "sulselbar", "label" => "Bank Sulselbar"],
        ["key" => "sulselbar_syr", "label" => "Bank Sulselbar Syariah"],
        ["key" => "sulut", "label" => "Bank SulutGo"],
        ["key" => "sumatera_barat", "label" => "Bank Nagari"],
        ["key" => "sumatera_barat_syr", "label" => "Bank Nagari Syariah"],
        ["key" => "sumsel_dan_babel", "label" => "Bank Sumsel Babel"],
        ["key" => "sumsel_dan_babel_syr", "label" => "Bank Sumsel Babel Syariah"],
        ["key" => "sumut", "label" => "Bank Sumut"],
        ["key" => "sumut_syr", "label" => "Bank Sumut Syariah"],
        ["key" => "super_bank", "label" => "Superbank"],
        ["key" => "tmrw_uob", "label" => "TMRW by UOB"], // Perbaikan label
        ["key" => "tokyo", "label" => "Bank of Tokyo Mitsubishi UFJ"],
        ["key" => "victoria_internasional", "label" => "Bank Victoria International"],
        ["key" => "victoria_syr", "label" => "Bank Victoria Syariah"],
        ["key" => "woori", "label" => "Bank Woori Saudara"],
        ["key" => "yudha_bakti", "label" => "Neo Commerce / Yudha Bhakti"] // Perbaikan label
    ],
    "ewallets" => [
        ["key" => "dana", "label" => "DANA"],
        ["key" => "gopay", "label" => "GoPay"],
        ["key" => "linkaja", "label" => "LinkAja"],
        ["key" => "ovo", "label" => "OVO"],
        ["key" => "shopeepay", "label" => "ShopeePay"],
        ["key" => "iskk", "label" => "ISKK Wallet"], // Contoh penambahan
        ["key" => "jenius_pay", "label" => "Jenius Pay"] // Contoh penambahan
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Nama Rekening Bank/e-Wallet Indonesia</title>
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />


    <style>
        :root {
            --bg-color: #1a1a1a;
            --surface-color: #262626;
            --text-color: #e0e0e0;
            --subtle-text: #a0a0a0;
            --primary-color: #2ecc71; /* Hijau */
            --primary-hover: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --border-color: #444;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Form Styling */
        label {
            color: var(--subtle-text);
            font-weight: 600;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            margin-top: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background-color: var(--surface-color);
            color: var(--text-color);
            transition: border-color 0.3s;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        /* Button Styling */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
            color: #111;
            background-color: var(--primary-color);
            transition: background-color 0.3s;
            cursor: pointer;
            border: none;
        }
        .btn:hover { background-color: var(--primary-hover); }
        .btn:disabled { background-color: #555; color: #999; cursor: not-allowed; }

        /* Result Alert Styling */
        .alert {
            width: 100%;
            max-width: 42rem;
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 0.375rem;
            font-weight: bold;
            text-align: center;
            border: 1px solid;
        }
        .alert-success { background-color: rgba(46, 204, 113, 0.1); color: var(--primary-color); border-color: var(--primary-color); }
        .alert-warning { background-color: rgba(243, 156, 18, 0.1); color: var(--warning-color); border-color: var(--warning-color); }
        .alert-danger { background-color: rgba(231, 76, 60, 0.1); color: var(--danger-color); border-color: var(--danger-color); }

        /* Select2 Dark Theme */
        .select2-container--default .select2-selection--single { background-color: var(--surface-color); border: 1px solid var(--border-color); height: 46px; color: var(--text-color); }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-color); line-height: 44px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow b { border-color: var(--subtle-text) transparent transparent transparent; }
        .select2-dropdown { background-color: var(--surface-color); border: 1px solid var(--border-color); }
        .select2-container--default .select2-search--dropdown .select2-search__field { background-color: #333; border: 1px solid var(--border-color); color: var(--text-color); }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background-color: var(--primary-color); color: #111; }
        
        /* Documentation Section */
        .documentation {
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        .documentation h2 {
            font-size: 1.875rem; /* text-3xl */
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        .documentation h3 {
            font-size: 1.25rem; /* text-xl */
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--subtle-text);
        }
        .documentation p {
            margin-bottom: 1rem;
        }
        .documentation code {
            font-family: 'Courier New', Courier, monospace;
            background-color: var(--bg-color);
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            color: var(--primary-color);
        }
        .documentation pre {
            background-color: #0d0d0d;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            white-space: pre-wrap;
            word-wrap: break-word;
            color: var(--text-color);
            font-size: 0.875rem;
        }
        .documentation .method {
            font-weight: bold;
            color: #fff;
            background-color: #e67e22; /* orange */
            padding: 4px 8px;
            border-radius: 4px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="flex flex-col items-center justify-center gap-4 pt-8 pb-4">
            <div class="inline-block max-w-lg text-center justify-center items-center">
                <h1 class="text-4xl font-bold">Cek Nama Rekening</h1>
                <h2 class="text-3xl font-bold" style="color: var(--primary-color);">Bank/e-Wallet Indonesia</h2>
            </div>

            <form id="checkForm" class="w-full gap-6 mt-8 flex flex-col justify-center items-center">
                <div class="w-full max-w-4xl">
                    <label for="account_bank" class="block text-sm mb-2">Pilih Bank / e-Wallet</label>
                    <select name="account_bank" id="account_bank" required class="form-input">
                        <option></option>
                        <optgroup label="Bank Name">
                            <?php foreach ($rekeningData['banks'] as $bank): ?>
                                <option value="<?= htmlspecialchars($bank['key']) ?>"><?= htmlspecialchars($bank['label']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="e-Wallet Name">
                            <?php foreach ($rekeningData['ewallets'] as $ewallet): ?>
                                <option value="<?= htmlspecialchars($ewallet['key']) ?>"><?= htmlspecialchars($ewallet['label']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <div class="w-full max-w-4xl">
                    <label for="account_number" class="block text-sm mb-2">Nomor Rekening / Telepon</label>
                    <input type="number" name="account_number" id="account_number" required class="form-input" />
                </div>

                <div id="result" class="alert" style="display: none;"></div>

                <button type="submit" id="submitBtn" class="btn mt-4">
                    <i class="fas fa-search"></i>&nbsp; Check Account
                </button>
            </form>
        </section>

        <section class="documentation">
            <h2><i class="fas fa-book"></i> Dokumentasi API</h2>
            <p>Anda dapat menggunakan layanan ini sebagai API untuk diintegrasikan ke aplikasi Anda sendiri. Cukup kirim permintaan ke endpoint yang sama dengan halaman ini.</p>
            
            <h3>Endpoint</h3>
            <pre><code class="method">POST</code> https://<?php echo $_SERVER['HTTP_HOST'] . htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=check_account</pre>

            <h3>Parameter (Body)</h3>
            <p>Kirim data dalam format <code>application/x-www-form-urlencoded</code> atau <code>multipart/form-data</code>.</p>
            <ul>
                <li><code>account_bank</code> (string, wajib) - Kode bank atau e-wallet. Lihat daftar di dropdown.</li>
                <li><code>account_number</code> (string, wajib) - Nomor rekening atau telepon.</li>
            </ul>

            <h3>Contoh Respons Sukses</h3>
            <pre><?php echo json_encode([
    "status" => true,
    "code" => 200,
    "message" => "Informasi rekening berhasil ditemukan.",
    "data" => [
        "bank_code" => "linkaja",
        "account_number" => "081357390584",
        "account_holder" => "VEGON SHELLA FIRMANTULLOH"
    ]
], JSON_PRETTY_PRINT); ?></pre>

            <h3>Contoh Respons Gagal</h3>
            <pre><?php echo json_encode([
    "status" => false,
    "code" => 404,
    "message" => "Informasi nama pemilik rekening tidak ditemukan."
], JSON_PRETTY_PRINT); ?></pre>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#account_bank').select2({
            placeholder: 'Cari dan pilih bank atau e-wallet',
            width: '100%'
        });

        $('#checkForm').on('submit', function(e) {
            e.preventDefault();
            const submitBtn = $('#submitBtn');
            const resultDiv = $('#result');
            const originalBtnHtml = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>&nbsp; Checking...');
            resultDiv.hide().removeClass('alert-success alert-warning alert-danger');

            $.ajax({
                url: '?action=check_account', // Mengirim ke halaman ini sendiri
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        resultDiv.html(`<i class="fas fa-check-circle"></i>&nbsp; ${response.message}`);
                        resultDiv.addClass('alert-success');
                    } else if (response.status === 'warning') {
                        resultDiv.html(`<i class="fas fa-exclamation-triangle"></i>&nbsp; ${response.message}`);
                        resultDiv.addClass('alert-warning');
                    } else {
                        resultDiv.html(`<i class="fas fa-times-circle"></i>&nbsp; ${response.message}`);
                        resultDiv.addClass('alert-danger');
                    }
                    resultDiv.show();
                },
                error: function() {
                    resultDiv.html('<i class="fas fa-times-circle"></i>&nbsp; Terjadi kesalahan. Silakan coba lagi.');
                    resultDiv.addClass('alert-danger');
                    resultDiv.show();
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                }
            });
        });
    });
    </script>
</body>
</html>