<?php
set_time_limit(0);
ini_set('memory_limit', '1G');

// Generator untuk scan file besar
function scan_directory_generator($directory, $suspicious_patterns){
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach($iterator as $file){
        if($file->isFile()){
            $issue = [];
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $dangerous_ext = ['php','phtml','php5','php7','phar'];
            if(in_array($ext,$dangerous_ext)){
                $content = '';
                $handle = fopen($file->getRealPath(), 'r');
                $line_number = 0;
                while(!feof($handle)){
                    $chunk = fread($handle, 8192);
                    $lines = explode("\n", $chunk);
                    foreach($lines as $ln){
                        $line_number++;
                        foreach($suspicious_patterns as $pattern=>$level){
                            if(preg_match($pattern, $ln, $matches)){
                                $issue[] = ['type'=>$level,'msg'=>$matches[0],'line'=>$line_number];
                            }
                        }
                    }
                    $content .= $chunk;
                }
                fclose($handle);

                // SQL Injection & hardcoded keys
                if(preg_match('/\$_(GET|POST|REQUEST).*=\s*["\'].*["\']/i', $content)){
                    $issue[] = ['type'=>'High','msg'=>'Potential SQL Injection / unsanitized input','line'=>1];
                }
                if(preg_match('/(api_key|password|token)\s*=\s*[\'"].+[\'"]/i', $content)){
                    $issue[] = ['type'=>'High','msg'=>'Hardcoded sensitive information','line'=>1];
                }

                // File permission
                $perm = substr(sprintf('%o',$file->getPerms()),-3);
                if(in_array($perm,['777','666'])){
                    $issue[] = ['type'=>'High','msg'=>"Permission too open: $perm",'line'=>1];
                }

                if($issue){
                    yield ['file'=>$file->getRealPath(),'issues'=>$issue];
                }
            }
        }
    }
}

// Pola backdoor
$suspicious_patterns = [
    '/phpinfo\s*\(\)/i'=>'Low',
    '/chmod\s*\(/i'=>'Low',
    '/base64_decode\s*\(/i'=>'Medium',
    '/file_put_contents\s*\(\$_(POST|GET|REQUEST)/i'=>'Medium',
    '/fwrite\s*\(\$_(POST|GET|REQUEST)/i'=>'Medium',
    '/eval\s*\(/i'=>'High',
    '/exec\s*\(/i'=>'High',
    '/shell_exec\s*\(/i'=>'High',
    '/system\s*\(/i'=>'High',
    '/popen\s*\(/i'=>'High',
    '/proc_open\s*\(/i'=>'High',
    '/preg_replace\s*\(.*\/e.*\)/i'=>'High',
    '/assert\s*\(/i'=>'High',
    '/create_function\s*\(/i'=>'High'
];

$directory = __DIR__;
$suspect_files = iterator_to_array(scan_directory_generator($directory,$suspicious_patterns));

// Scan config sensitif
$sensitive_files = ['.env','config.php','wp-config.php'];
foreach($sensitive_files as $file){
    $full_path = $directory.'/'.$file;
    if(file_exists($full_path)){
        $suspect_files[] = ['file'=>$full_path,'issues'=>[['type'=>'Medium','msg'=>'Sensitive config file exists','line'=>1]]];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MetaScan Ultra Cepat</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
body { background:#0a0b0f; margin:0; font-family:'Share Tech Mono',monospace; color:#00ff00; }
header{background:#000; padding:15px 20px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #222; flex-wrap:wrap;}
header h1{color:#fff; font-size:22px; letter-spacing:1px;}
.mode-toggle button{margin-left:10px;padding:8px 14px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;}
.mode-toggle button.active{background:#27c93f;color:#000;}
footer{background:#000; color:#888; text-align:center; padding:12px; font-size:13px; border-top:1px solid #222;}
.terminal, .ui-mode{border-radius:10px; box-shadow:0 0 20px #111; width:80%; max-width:900px; margin:30px auto; overflow:hidden;}
.terminal{background:#000; height:70vh; display:flex; flex-direction:column;}
.terminal-header{display:flex; align-items:center; padding:6px 12px; background:#1a1a1a;}
.btn{width:12px; height:12px; border-radius:50%; margin-right:6px;}
.red{background:#ff5f56;}
.yellow{background:#ffbd2e;}
.green{background:#27c93f;}
#scanner{flex:1; padding:12px; overflow-y:auto; font-size:14px; line-height:1.5em; white-space:pre-wrap;}
.cursor{display:inline-block; width:10px; background:#00ff00; animation:blink 1s infinite;}
@keyframes blink{0%{opacity:1;}50%{opacity:0;}100%{opacity:1;}}
.low{color:#72ff7a;}
.medium{color:#ffd86b;}
.high{color:#ff6b6b;}

/* UI Mode ala Starlink */
.ui-mode {
    display:none;
    background: #000;
    background-size: cover;
    min-height: 70vh;
    padding: 40px;
    color:#fff;
    font-family: 'Share Tech Mono', monospace;
}
.ui-wrapper {
    backdrop-filter: blur(12px);
    background: rgba(20, 30, 50, 0.6);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.6);
    max-width: 1000px;
    margin: auto;
}
.ui-header {
    text-align:center;
    font-size:20px;
    margin-bottom:20px;
    color:#00d4ff;
    letter-spacing:1px;
}
.loading {display:flex; flex-direction:column; align-items:center; justify-content:center; height:50vh; font-size:18px; color:#00ffea;}
.progress-bar {width:80%; height:20px; background:rgba(255,255,255,0.1); border-radius:12px; overflow:hidden; margin-top:20px;}
.progress-inner {height:100%; width:0%; background:linear-gradient(90deg,#00d4ff,#00ffae); transition:width 0.2s;}
.ui-card {background: rgba(255,255,255,0.08); backdrop-filter: blur(10px); padding:15px 20px; border-radius:15px; margin-bottom:15px; display:flex; align-items:flex-start; gap:12px; box-shadow:0 4px 15px rgba(0,0,0,0.3); animation: fadeIn 0.5s ease;}
.ui-card i {font-size:20px;}
.ui-card .content {flex:1; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; white-space: pre-wrap;}
.low i {color:#72ff7a;}
.medium i {color:#ffd86b;}
.high i {color:#ff6b6b;}
@keyframes fadeIn {from {opacity:0; transform:translateY(10px);} to {opacity:1; transform:translateY(0);}}
</style>
</head>
<body>
<header>
<h1><i class="fa-solid fa-shield-halved"></i> MetaShell Backdoor</h1>
<div class="mode-toggle">
<button id="shellBtn" class="active">Shell Mode</button>
<button id="uiBtn">UI Mode</button>
</div>
</header>

<!-- Shell Mode -->
<div class="terminal" id="shellMode">
<div class="terminal-header">
<div class="btn red"></div><div class="btn yellow"></div><div class="btn green"></div>
</div>
<div id="scanner"></div>
</div>

<!-- UI Mode -->
<div class="ui-mode" id="uiMode">
  <div class="ui-wrapper">
    <div class="ui-header">
      <i class="fa-solid fa-satellite-dish"></i> Scan Backdoor Shell
    </div>
    <div class="loading" id="uiLoading">
      <i class="fa-solid fa-hourglass-half fa-spin"></i> Scanning files...
      <div class="progress-bar"><div class="progress-inner" id="progressInner"></div></div>
    </div>
    <div id="uiResults"></div>
  </div>
</div>

<footer>
© 2025 MetaShell | Powered with XJaguar
</footer>

<script>
const results = <?php echo json_encode($suspect_files); ?>;

// Mode Toggle
const shellBtn = document.getElementById('shellBtn');
const uiBtn = document.getElementById('uiBtn');
const shellMode = document.getElementById('shellMode');
const uiMode = document.getElementById('uiMode');

shellBtn.onclick = ()=>{
    shellMode.style.display='flex';
    uiMode.style.display='none';
    shellBtn.classList.add('active');
    uiBtn.classList.remove('active');
};
uiBtn.onclick = ()=>{
    shellMode.style.display='none';
    uiMode.style.display='block';
    uiBtn.classList.add('active');
    shellBtn.classList.remove('active');
};

// Terminal Mode
const scanner = document.getElementById("scanner");
const lines = [];
lines.push("user@orbital:~$ scanning system recursively...");
if(results.length){
    results.forEach(file=>{
        file.issues.forEach(issue=>{
            lines.push(`[${issue.type.toUpperCase()}] ${file.file}:${issue.line} => ${issue.msg}:::${issue.type.toLowerCase()}`);
        });
    });
    lines.push("user@orbital:~$ scan complete.");
}else{
    lines.push("✅ Tidak ada file mencurigakan. Sistem aman!");
    lines.push("user@orbital:~$");
}

const totalChars = lines.reduce((sum,line)=>sum+line.length,0);
const totalTime = 5000;
const intervalTime = totalTime/totalChars;
let i=0;

function typeLine(){
    if(i<lines.length){
        let text = lines[i];
        let cls = "";
        if(text.includes(":::")){
            const parts = text.split(":::");
            text = parts[0];
            cls = parts[1];
        }
        const line = document.createElement("div");
        line.innerHTML = `<span class="${cls}"></span><span class="cursor"></span>`;
        scanner.appendChild(line);
        const span = line.querySelector("span");
        const cursor = line.querySelector(".cursor");
        let charIndex=0;
        const interval=setInterval(()=>{
            span.textContent=text.slice(0,charIndex);
            charIndex++;
            if(charIndex>text.length){
                clearInterval(interval);
                cursor.remove();
                i++;
                typeLine();
            }
        },intervalTime);
        scanner.scrollTop = scanner.scrollHeight;
    }
}
typeLine();

// UI Mode dengan Loading 3 Detik + Progress
const uiResults = document.getElementById('uiResults');
const uiLoading = document.getElementById('uiLoading');
const progressInner = document.getElementById('progressInner');

let progress = 0;
const loader = setInterval(()=>{
    progress += 2;
    if(progress > 100) progress = 100;
    progressInner.style.width = progress+"%";
}, 60);

setTimeout(()=>{
    clearInterval(loader);
    uiLoading.style.display='none';
    results.forEach(file=>{
        file.issues.forEach(issue=>{
            const card = document.createElement('div');
            card.className = `ui-card ${issue.type.toLowerCase()}`;
            card.innerHTML = `<i class="fa-solid fa-bug"></i><div class="content"><strong>${file.file}</strong><br>[${issue.type}] Line ${issue.line}: ${issue.msg}</div>`;
            uiResults.appendChild(card);
        });
    });
    if(results.length===0){
        const card = document.createElement('div');
        card.className = 'ui-card low';
        card.innerHTML = `<i class="fa-solid fa-check"></i><div class="content">Tidak ada file mencurigakan. Sistem aman!</div>`;
        uiResults.appendChild(card);
    }
}, 3000);
</script>
</body>
</html>
