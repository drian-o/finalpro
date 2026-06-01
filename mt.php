<?php include 'config/koneksi.php';?>
<?php
$sql = "SELECT status FROM maintenance WHERE id = 3";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $status = $row["status"];

    if ($status == 0) {
        // Mengarahkan pengguna kembali ke halaman sebelumnya
        header("Location: /");
        exit();
    } else {
        // Tindakan lain jika status bukan 1
    }
} else {
    // Tindakan lain jika tidak ada hasil dari query
}
?>
<?php
      $sql_chat = mysqli_query($conn,"SELECT * FROM `tb_livechat` WHERE cuid = 1") or die(mysqli_error());
      $sc = mysqli_fetch_array($sql_chat);
                $sql = "SELECT * FROM tb_social";
                $result = $conn->query($sql);

                // Menampilkan hasil query
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $wa = $row['wa'];   
                        $tele = $row['tele']; 
                        $fb = $row['facebook'];
                        $ig = $row['instagram'];
                    }
                } else {
                    echo "Tidak ada data WhatsApp ditemukan";
                }   
            ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>MAINTAINANCE</title>
  <meta name="description" content="Dalam Pemeliharaan - Silakan kembali lagi nanti">
  <meta name="author" content="Vamsi">
  <link href='//fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>

  <style>
     html {
      background: linear-gradient(to right, violet, indigo, blue, green, yellow, orange, red); /* ubah latar belakang menjadi efek pelangi */
     }
     body {
      background: #333; /* ubah warna background menjadi gelap */
      max-width: 70%;
      font-family: "Open Sans", sans-serif;
      font-size: 16px;
      padding: 2em;
      margin: 5em auto;
      text-align: center;
      border-radius: 10px;
      -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
      box-shadow: 0 1px 3px rgba(0,0,0,0.13);
     }
     h1 {
      color: #fff; /* ubah warna teks menjadi putih */
      font-size: 36px;
      margin-bottom: 20px;
     }
     p {
      color: #ccc; /* ubah warna teks menjadi abu-abu muda */
      line-height: 1.6;
      margin-bottom: 30px;
     }
     .logo {
      max-width: 200px;
      margin-bottom: 20px;
     }
     .footer {
      font-size: 14px;
      color: #999;
     }

     /* Responsif */
     @media screen and (max-width: 768px) {
      body {
        padding: 1em;
        margin: 2em auto;
        font-size: 14px;
      }
      h1 {
        font-size: 24px;
      }
      p {
        margin-bottom: 20px;
      }
      .logo {
        max-width: 150px;
      }
     }
  </style>

  <!--[if lt IE 9]>
  <script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>

<body>
  <h1>Maintenance Mode</h1>

  <p>Mohon Maaf, Kami saat ini sedang melakukan pemeliharaan rutin untuk meningkatkan pengalaman Anda.</p><p>Kami akan kembali segera setelah pemeliharaan selesai.</p>

  <div class="footer">
    <p>&copy; 77CAIR 2025. Hak Cipta Dilindungi Undang-Undang.</p>
  </div>

</body>
</html>
<!--<script>-->
<!--    window._lc = window._lc || {};-->
<!--    window._lc.license = <?php echo $sc['lc_mobile']; ?>;-->
<!--    window.__lc = window.__lc || {};-->
<!--    window.__lc.license = window._lc.license;-->
<!--    ;(function(n,t,c){function i(n){return e.h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0,n.type="text/javascript",n.src="https://cdn.livechatinc.com/tracking.js",t.head.appendChild(n)}};!n._lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice))-->
<!--</script>-->
<!--<noscript><a href="https://www.livechat.com/chat-with/<?php echo $sc['lc_mobile']; ?>/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechat.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a></noscript>-->
