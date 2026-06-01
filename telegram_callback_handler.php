<?php
// telegram_callback_handler.php

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/classes/class.exa.php';

$telegramBotToken = '8194212776:AAFrjOU3uJWBqg3LIKKFX7CZ-bTJwA8Bc3w';
$adminTelegramUserId = '1244924745';

// Fungsi untuk mengirim pesan ke Telegram tetap sama
function sendTelegramApiResponse($botToken, $method, array $params) {
    $url = "https://api.telegram.org/bot{$botToken}/{$method}";
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($params),
            'ignore_errors' => true,
            'timeout' => 5
        ],
    ];
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        error_log("Callback Handler: Gagal mengirim API response ke Telegram method {$method}. " . (error_get_last()['message'] ?? ''));
        return null;
    }
    return json_decode($result, true);
}


$updateJson = file_get_contents('php://input');
file_put_contents(__DIR__ . '/callback_debug.log', "[" . date("Y-m-d H:i:s") . "] Raw Input CB Handler: " . $updateJson . "\n", FILE_APPEND);


$update = json_decode($updateJson, true);

if (!$update || !isset($update['callback_query'])) {
    error_log("Callback Handler: Update tidak valid atau bukan callback query. Input: " . $updateJson);
    http_response_code(200);
    exit("Not a callback query.");
}

$callbackQuery = $update['callback_query'];
$callbackQueryId = $callbackQuery['id'];
$callbackData = $callbackQuery['data'];
$messageId = $callbackQuery['message']['message_id'] ?? null;
$chatId = $callbackQuery['message']['chat']['id'] ?? null;
$userIdFrom = $callbackQuery['from']['id'] ?? null;
$admin_username_telegram = $callbackQuery['from']['username'] ?? ($callbackQuery['from']['first_name'] ?? 'AdminTele');


// Keamanan Tambahan
if (!empty($adminTelegramUserId) && $userIdFrom != $adminTelegramUserId) {
    sendTelegramApiResponse($telegramBotToken, 'answerCallbackQuery', [
        'callback_query_id' => $callbackQueryId,
        'text' => 'Aksi ditolak. Anda tidak berwenang.',
        'show_alert' => true
    ]);
    error_log("Callback Handler: Aksi ditolak untuk user ID {$userIdFrom}. Callback Data: {$callbackData}");
    exit("Unauthorized user.");
}

$parts = explode('_', $callbackData, 3);
$actionType = $parts[0] ?? null;
$transactionType = $parts[1] ?? null;
$transactionId = isset($parts[2]) ? (int)$parts[2] : 0;

$responseText = "Aksi tidak diketahui atau format callback salah.";

if (!$koneksi) {
    error_log("Callback Handler: Gagal koneksi database. Callback: " . $callbackData);
    sendTelegramApiResponse($telegramBotToken, 'answerCallbackQuery', ['callback_query_id' => $callbackQueryId, 'text' => 'Error: Database down.', 'show_alert' => true]);
    exit("Database connection failed.");
}

if (!class_exists('GameXaAPI')) {
    error_log("Callback Handler: Kelas 'GameXaAPI' tidak ditemukan.");
    sendTelegramApiResponse($telegramBotToken, 'answerCallbackQuery', ['callback_query_id' => $callbackQueryId, 'text' => 'Error: Komponen sistem (Exa) hilang.', 'show_alert' => true]);
    exit("Exa class not found.");
}
$exaAPI = new GameXaAPI();


if ($transactionId > 0 && $chatId && $messageId && $actionType && $transactionType) {
    if ($transactionType === 'depo') { 
        $stmt_get_deposit = $koneksi->prepare("SELECT d.*, a.id_sigma FROM deposit d JOIN anggota a ON d.id_anggota_deposit = a.id_anggota WHERE d.id_deposit = ?");
        if (!$stmt_get_deposit) {
            error_log("Callback Handler (DEPO): Gagal prepare statement get_deposit. Error: " . $koneksi->error);
            $responseText = "Error internal server (DBPD1).";
            goto answer_callback_depo; 
        }
        $stmt_get_deposit->bind_param("i", $transactionId);
        $stmt_get_deposit->execute();
        $result_deposit = $stmt_get_deposit->get_result();
        $data_deposit_db = $result_deposit->fetch_assoc();
        $stmt_get_deposit->close();

        if (!$data_deposit_db) {
            $responseText = "Deposit ID {$transactionId} tidak ditemukan.";
            error_log("Callback Handler (DEPO): Deposit ID {$transactionId} tidak ditemukan. Callback: {$callbackData}");
            goto answer_callback_depo;
        }

        $id_sigma_depo = $data_deposit_db['id_sigma'];
        if (empty($id_sigma_depo)) {
            $responseText = "Error: ID Sigma tidak ditemukan untuk deposit ini.";
            error_log("Callback Handler (DEPO): ID Sigma kosong untuk Deposit ID {$transactionId}.");
            goto answer_callback_depo;
        }

        $status_deposit_sebelumnya = $data_deposit_db['status_deposit'];
        $nama_anggota_pengguna_deposit = $data_deposit_db['nama_pengguna_anggota_deposit'];
        $jumlah_deposit_val = floatval($data_deposit_db['jumlah_deposit']);
        $id_anggota_deposit_val = $data_deposit_db['id_anggota_deposit'];

        if ($actionType === 'approve') {
            if ($status_deposit_sebelumnya === 'disetujui') {
                $responseText = "Deposit ID {$transactionId} sudah disetujui.";
            } elseif ($status_deposit_sebelumnya === 'dibatalkan') {
                $responseText = "Deposit ID {$transactionId} sudah dibatalkan, tidak bisa disetujui.";
            } else {
                $api_transaksi_response_depo = $exaAPI->depositToPlayer($id_sigma_depo, $jumlah_deposit_val, 'deposit_' . uniqid());
                
                if ($api_transaksi_response_depo && isset($api_transaksi_response_depo['success']) && $api_transaksi_response_depo['success'] === true) {
                    $saldo_final_untuk_db_depo = null;
                    if (isset($api_transaksi_response_depo['data']['data']['balance_after'])) {
                         $saldo_final_untuk_db_depo = floatval($api_transaksi_response_depo['data']['data']['balance_after']);
                    } else {
                        $getBalance_response_depo = $exaAPI->getPlayerBalance($id_sigma_depo);
                        if ($getBalance_response_depo && isset($getBalance_response_depo['success']) && $getBalance_response_depo['success'] === true && isset($getBalance_response_depo['data']['balance'])) {
                            $saldo_final_untuk_db_depo = floatval($getBalance_response_depo['data']['balance']);
                        } else {
                            error_log("Callback Handler (DEPO): Gagal GetBalance API setelah transaksi sukses. User: {$nama_anggota_pengguna_deposit}, Depo ID: {$transactionId}. Respon GetBalance: " . json_encode($getBalance_response_depo));
                            $responseText = "Transaksi API OK, GAGAL update saldo lokal. Cek Log!";
                            sendTelegramApiResponse($telegramBotToken, 'sendMessage', ['chat_id' => $chatId, 'text' => "⚠️ *Kritis Deposit ID {$transactionId}*: API Sukses, Gagal GetBalance u/ {$nama_anggota_pengguna_deposit}. Saldo lokal mungkin tidak sinkron!", 'parse_mode' => 'MarkdownV2']);
                        }
                    }

                    $koneksi->begin_transaction();
                    try {
                        if ($saldo_final_untuk_db_depo !== null) {
                            $stmt_update_saldo_depo = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?");
                            $stmt_update_saldo_depo->bind_param("di", $saldo_final_untuk_db_depo, $id_anggota_deposit_val);
                            $stmt_update_saldo_depo->execute();
                            $stmt_update_saldo_depo->close();
                        }
                        $stmt_update_deposit_db = $koneksi->prepare("UPDATE deposit SET status_deposit = 'disetujui' WHERE id_deposit = ?");
                        $stmt_update_deposit_db->bind_param("i", $transactionId);
                        $stmt_update_deposit_db->execute();
                        if ($stmt_update_deposit_db->affected_rows > 0) {
                            $koneksi->commit();
                            $responseText = "Deposit ID {$transactionId} berhasil disetujui oleh {$admin_username_telegram}.";
                            $newTextDepo = $callbackQuery['message']['text'] . "\n\n✅ <b>Disetujui oleh:</b> " . htmlspecialchars($admin_username_telegram) . " (" . date('d-m-Y H:i:s') . ")";
                            sendTelegramApiResponse($telegramBotToken, 'editMessageText', ['chat_id' => $chatId, 'message_id' => $messageId, 'text' => $newTextDepo, 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['inline_keyboard' => []])]);
                        } else { throw new Exception("Gagal update status deposit di DB (affected_rows=0)."); }
                        $stmt_update_deposit_db->close();
                    } catch (Exception $e_depo) {
                        $koneksi->rollback();
                        error_log("Callback Handler (DEPO): Exception saat approve Deposit ID {$transactionId}. Error: " . $e_depo->getMessage());
                        $responseText = "Error DB saat menyetujui depo: " . substr($e_depo->getMessage(), 0, 100);
                    }
                } else {
                    $pesan_error_api_depo = "Gagal API Transaksi Deposit.";
                    if ($api_transaksi_response_depo && isset($api_transaksi_response_depo['message'])) { $pesan_error_api_depo .= " Pesan: " . htmlspecialchars($api_transaksi_response_depo['message']); }
                    error_log("Callback Handler (DEPO): API Transaksi gagal untuk Deposit ID {$transactionId}. Respon: " . json_encode($api_transaksi_response_depo));
                    $responseText = $pesan_error_api_depo;
                }
            }
        } elseif ($actionType === 'cancel') {
            if ($status_deposit_sebelumnya === 'dibatalkan') {
                $responseText = "Deposit ID {$transactionId} sudah dibatalkan.";
            } elseif ($status_deposit_sebelumnya === 'disetujui') {
                $responseText = "Deposit ID {$transactionId} sudah disetujui, tidak bisa dibatalkan.";
            } else {
                $stmt_cancel_deposit_db = $koneksi->prepare("UPDATE deposit SET status_deposit = 'dibatalkan' WHERE id_deposit = ? AND status_deposit = 'diproses'");
                $stmt_cancel_deposit_db->bind_param("i", $transactionId);
                $stmt_cancel_deposit_db->execute();
                if ($stmt_cancel_deposit_db->affected_rows > 0) {
                    $responseText = "Deposit ID {$transactionId} berhasil dibatalkan oleh {$admin_username_telegram}.";
                    $newTextDepoCancel = $callbackQuery['message']['text'] . "\n\n❌ <b>Dibatalkan oleh:</b> " . htmlspecialchars($admin_username_telegram) . " (" . date('d-m-Y H:i:s') . ")";
                    sendTelegramApiResponse($telegramBotToken, 'editMessageText', ['chat_id' => $chatId, 'message_id' => $messageId, 'text' => $newTextDepoCancel, 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['inline_keyboard' => []])]);
                } else {
                    $responseText = "Gagal batalkan deposit ID {$transactionId} (status mungkin sudah berubah).";
                    error_log("Callback Handler (DEPO): Gagal batalkan deposit ID {$transactionId}. Error DB: " . $stmt_cancel_deposit_db->error);
                }
                $stmt_cancel_deposit_db->close();
            }
        } else {
             $responseText = "Aksi deposit '{$actionType}' tidak dikenal.";
        }
        answer_callback_depo:
        sendTelegramApiResponse($telegramBotToken, 'answerCallbackQuery', ['callback_query_id' => $callbackQueryId, 'text' => $responseText]);


    } elseif ($transactionType === 'wd') {
        $stmt_get_withdraw = $koneksi->prepare("SELECT w.*, a.id_sigma FROM withdraw w JOIN anggota a ON w.id_anggota_withdraw = a.id_anggota WHERE w.id_withdraw = ?");
        if (!$stmt_get_withdraw) {
            error_log("Callback Handler (WD): Gagal prepare statement get_withdraw. Error: " . $koneksi->error);
            $responseText = "Error internal server (DBPW1).";
            goto answer_callback_wd;
        }
        $stmt_get_withdraw->bind_param("i", $transactionId);
        $stmt_get_withdraw->execute();
        $result_withdraw = $stmt_get_withdraw->get_result();
        $data_withdraw = $result_withdraw->fetch_assoc();
        $stmt_get_withdraw->close();

        if (!$data_withdraw) {
            $responseText = "Withdraw ID {$transactionId} tidak ditemukan.";
            error_log("Callback Handler (WD): Withdraw ID {$transactionId} tidak ditemukan. Callback: {$callbackData}");
            goto answer_callback_wd;
        }

        $id_sigma_wd = $data_withdraw['id_sigma'];
        if (empty($id_sigma_wd)) {
            $responseText = "Error: ID Sigma tidak ditemukan untuk withdraw ini.";
            error_log("Callback Handler (WD): ID Sigma kosong untuk Withdraw ID {$transactionId}.");
            goto answer_callback_wd;
        }

        $status_withdraw_sebelumnya = $data_withdraw['status_withdraw'];
        $id_anggota_withdraw = $data_withdraw['id_anggota_withdraw'];
        $jumlah_withdraw = floatval($data_withdraw['jumlah_withdraw']);
        $nama_pengguna_wd = $data_withdraw['nama_pengguna_withdraw'] ?? null;
        if (empty($nama_pengguna_wd)) {
            $stmt_get_user = $koneksi->prepare("SELECT nama_pengguna_anggota FROM anggota WHERE id_anggota = ?");
            $stmt_get_user->bind_param("i", $id_anggota_withdraw);
            $stmt_get_user->execute();
            $result_user = $stmt_get_user->get_result();
            $user_data = $result_user->fetch_assoc();
            $stmt_get_user->close();
            $nama_pengguna_wd = $user_data['nama_pengguna_anggota'] ?? 'UserTidakDitemukan';
        }


        if ($actionType === 'approve') {
            if ($status_withdraw_sebelumnya === 'disetujui') {
                $responseText = "Withdraw ID {$transactionId} sudah disetujui sebelumnya.";
            } elseif ($status_withdraw_sebelumnya === 'dibatalkan') {
                $responseText = "Withdraw ID {$transactionId} sudah dibatalkan, tidak bisa disetujui.";
            } else {
                $koneksi->begin_transaction();
                try {
                    $stmt_update_wd = $koneksi->prepare("UPDATE withdraw SET status_withdraw = 'disetujui' WHERE id_withdraw = ? AND status_withdraw = 'diproses'");
                    $stmt_update_wd->bind_param("i", $transactionId);
                    $stmt_update_wd->execute();
                    
                    if ($stmt_update_wd->affected_rows > 0) {
                        $koneksi->commit();
                        $responseText = "Withdraw ID {$transactionId} untuk {$nama_pengguna_wd} berhasil disetujui oleh {$admin_username_telegram}.";
                        
                        $newTextWd = $callbackQuery['message']['text'] . "\n\n✅ <b>WD Disetujui oleh:</b> " . htmlspecialchars($admin_username_telegram) . " (" . date('d-m-Y H:i:s') . ")";
                        sendTelegramApiResponse($telegramBotToken, 'editMessageText', [
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => $newTextWd,
                            'parse_mode' => 'HTML',
                            'reply_markup' => json_encode(['inline_keyboard' => []])
                        ]);
                    } else {
                        $koneksi->rollback();
                        $responseText = "Gagal setujui WD ID {$transactionId} (status mungkin sudah berubah atau tidak ditemukan).";
                        error_log("Callback Handler (WD): Gagal setujui WD ID {$transactionId}, affected_rows=0. Error DB: " . $stmt_update_wd->error);
                    }
                    $stmt_update_wd->close();
                } catch (Exception $e_wd_approve) {
                    $koneksi->rollback();
                    error_log("Callback Handler (WD): Exception saat approve WD ID {$transactionId}. Error: " . $e_wd_approve->getMessage());
                    $responseText = "Error DB saat menyetujui WD: " . substr($e_wd_approve->getMessage(), 0, 100);
                }
            }

        } elseif ($actionType === 'cancel') {
            if ($status_withdraw_sebelumnya === 'dibatalkan') {
                $responseText = "Withdraw ID {$transactionId} sudah dibatalkan sebelumnya.";
            } elseif ($status_withdraw_sebelumnya === 'disetujui') {
                $responseText = "Withdraw ID {$transactionId} sudah disetujui, pembatalan memerlukan proses manual/API khusus.";
            } else {
                $api_refund_response = $exaAPI->depositToPlayer($id_sigma_wd, $jumlah_withdraw, 'refund_wd_' . uniqid());

                if ($api_refund_response && isset($api_refund_response['success']) && $api_refund_response['success'] === true) {
                    $saldo_final_setelah_refund_db = null;
                     if (isset($api_refund_response['data']['data']['balance_after'])) {
                         $saldo_final_setelah_refund_db = floatval($api_refund_response['data']['data']['balance_after']);
                     } else {
                        $getBalance_after_refund_response = $exaAPI->getPlayerBalance($id_sigma_wd);
                        if ($getBalance_after_refund_response && isset($getBalance_after_refund_response['success']) && $getBalance_after_refund_response['success'] === true && isset($getBalance_after_refund_response['data']['balance'])) {
                            $saldo_final_setelah_refund_db = floatval($getBalance_after_refund_response['data']['balance']);
                        } else {
                            error_log("Callback Handler (WD Cancel): Gagal GetBalance API setelah refund sukses. User: {$nama_pengguna_wd}, WD ID: {$transactionId}. Respon GetBalance: " . json_encode($getBalance_after_refund_response));
                            $responseText = "API Refund OK, GAGAL update saldo lokal. Cek Log!";
                            sendTelegramApiResponse($telegramBotToken, 'sendMessage', ['chat_id' => $chatId, 'text' => "⚠️ *Kritis Pembatalan WD ID {$transactionId}*: API Refund Sukses, tapi Gagal GetBalance untuk {$nama_pengguna_wd}. Saldo lokal mungkin tidak sinkron!", 'parse_mode' => 'MarkdownV2']);
                        }
                    }

                    $koneksi->begin_transaction();
                    try {
                        if ($saldo_final_setelah_refund_db !== null) {
                            $stmt_update_saldo_refund = $koneksi->prepare("UPDATE anggota SET saldo_anggota = ? WHERE id_anggota = ?");
                            $stmt_update_saldo_refund->bind_param("di", $saldo_final_setelah_refund_db, $id_anggota_withdraw);
                            $stmt_update_saldo_refund->execute();
                            $stmt_update_saldo_refund->close();
                        }

                        $stmt_cancel_wd = $koneksi->prepare("UPDATE withdraw SET status_withdraw = 'dibatalkan' WHERE id_withdraw = ? AND status_withdraw = 'diproses'");
                        $stmt_cancel_wd->bind_param("i", $transactionId);
                        $stmt_cancel_wd->execute();

                        if ($stmt_cancel_wd->affected_rows > 0) {
                            $koneksi->commit();
                            $responseText = "Withdraw ID {$transactionId} untuk {$nama_pengguna_wd} berhasil dibatalkan oleh {$admin_username_telegram}. Saldo dikembalikan.";
                            $newTextWdCancel = $callbackQuery['message']['text'] . "\n\n❌ <b>WD Dibatalkan & Saldo Dikembalikan oleh:</b> " . htmlspecialchars($admin_username_telegram) . " (" . date('d-m-Y H:i:s') . ")";
                            sendTelegramApiResponse($telegramBotToken, 'editMessageText', [
                                'chat_id' => $chatId,
                                'message_id' => $messageId,
                                'text' => $newTextWdCancel,
                                'parse_mode' => 'HTML',
                                'reply_markup' => json_encode(['inline_keyboard' => []])
                            ]);
                        } else {
                            throw new Exception("Gagal update status withdraw menjadi 'dibatalkan' di DB (affected_rows=0).");
                        }
                        $stmt_cancel_wd->close();
                    } catch (Exception $e_wd_cancel_db) {
                        $koneksi->rollback();
                        error_log("Callback Handler (WD Cancel): Exception saat update DB setelah refund API sukses. WD ID {$transactionId}. Error: " . $e_wd_cancel_db->getMessage());
                        $responseText = "Error DB saat membatalkan WD & refund: " . substr($e_wd_cancel_db->getMessage(), 0, 100);
                        sendTelegramApiResponse($telegramBotToken, 'sendMessage', ['chat_id' => $chatId, 'text' => "⚠️ *Peringatan Kritis Pembatalan WD ID {$transactionId}*: API Refund Sukses, GetBalance OK, tapi Gagal update DB Lokal! Periksa saldo & status {$nama_pengguna_wd} secara manual.", 'parse_mode' => 'MarkdownV2']);

                    }
                } else {
                    $pesan_error_api_refund = "Gagal API pengembalian dana (refund) untuk pembatalan withdraw.";
                    if ($api_refund_response && isset($api_refund_response['message'])) {
                        $pesan_error_api_refund .= " Pesan: " . htmlspecialchars($api_refund_response['message']);
                    }
                    error_log("Callback Handler (WD Cancel): API Refund gagal untuk WD ID {$transactionId}. User: {$nama_pengguna_wd}. Respon: " . json_encode($api_refund_response));
                    $responseText = $pesan_error_api_refund . " Saldo belum dikembalikan.";
                }
            }
        } else {
            $responseText = "Aksi withdraw '{$actionType}' tidak dikenal.";
            error_log("Callback Handler (WD): Aksi withdraw tidak dikenal '{$actionType}' untuk WD ID {$transactionId}.");
        }
        answer_callback_wd:
        sendTelegramApiResponse($telegramBotToken, 'answerCallbackQuery', ['callback_query_id' => $callbackQueryId, 'text' => $responseText]);

    } else {
        error_log("Callback Handler: TransactionType tidak dikenal '{$transactionType}'. CallbackData: {$callbackData}");
        sendTelegramApiResponse($telegramBotToken, 'answerCallbackQuery', ['callback_query_id' => $callbackQueryId, 'text' => 'Tipe transaksi tidak dikenal.']);
    }
} else {
    error_log("Callback Handler: Data callback tidak lengkap atau format salah. CallbackData: {$callbackData}, ChatID: {$chatId}, MessageID: {$messageId}, ActionType: {$actionType}, TransType: {$transactionType}, TransID: {$transactionId}");
    sendTelegramApiResponse($telegramBotToken, 'answerCallbackQuery', ['callback_query_id' => $callbackQueryId, 'text' => 'Data callback tidak lengkap atau format salah.']);
}


if (php_sapi_name() !== 'cli') {
    http_response_code(200);
}

if (isset($koneksi) && $koneksi && mysqli_ping($koneksi)) {
    mysqli_close($koneksi);
}
?>