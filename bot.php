<?php
error_reporting(0);

const
title = "ourcoincash",
versi = "1.0.2",
class_require = "1.0.1",
host = "https://ourcoincash.xyz/",
refflink = "https://ourcoincash.xyz/?r=3408",
youtube = "https://youtu.be/fWr9o2nF-FQ";

function DownloadSc($server) {
	$colors = [
		"\033[48;5;16m",  // Black
		"\033[48;5;24m",  // Dark blue
		"\033[48;5;34m",  // Green
		"\033[48;5;44m",  // Blue
		"\033[48;5;54m",  // Light blue
		"\033[48;5;64m",  // Violet
		"\033[48;5;74m",  // Purple
		"\033[48;5;84m",  // Purple-Blue
		"\033[48;5;94m",  // Light purple
		"\033[48;5;104m"  // Pink
	];
	$text = "Proses Download Script...";
	$textLength = strlen($text);

	for ($i = 1; $i <= $textLength; $i++) {
		usleep(150000);  // Delay 150.000 mikrodetik = 0.15 detik
		$percent = round(($i / $textLength) * 100); 
		$bgColor = $colors[$i % count($colors)];
		$coloredText = substr($text, 0, $i);
		$remainingText = substr($text, $i);
		echo $bgColor . $coloredText . "\033[0m" . $remainingText . " {$percent}% \r";
		flush();
	}
	file_put_contents($server."\iewilofficial\class.php",file_get_contents("https://raw.githubusercontent.com/iewilmaestro/myFunctions/refs/heads/main/Class.php"));
	echo "\n\033[48;5;196mProses selesai!,jalankan ulang script\033[0m\n";
	exit;
}

$server = $_SERVER["TMP"];
if(!$server){
	$server = $_SERVER["TMPDIR"];
}

update:
if(!file_exists($server."\iewilofficial\class.php")){
	system("mkdir ".$server."\iewilofficial");
	DownloadSc($server);
}
require $server."\iewilofficial\class.php";

if(class_version < class_require){
	print "\033[1;31mVersi class sudah kadaluarsa\n";
	unlink($server."\iewilofficial\class.php");
	DownloadSc($server);
}


class Bot {
	public $cookie,$uagent;
	public function __construct(){
		$this->server = Functions::Server(title);
		if($this->server['data']['status'] != "online"){
			Display::Ban(title, versi);
			print Display::Error("Status Script is offline\n");
			exit;
		}
		$this->update = ($this->server['data']['version'] == versi)?false:true;
		Display::Ban(title, versi);
		if($this->update > null){
			print m."---[".p."^".m."]".h." Update sc Detect\n";
			print m."---[".p."version ".m."] ".p.$this->server['data']['version'].n;
			print m."---[".p."download".m."] ".p.$this->server['data']['link'].n;
			Display::Line();
		}
		cookie:
		if(empty(Functions::getConfig('cookie'))){
			Display::Cetak("Register",refflink);
			Display::Line();
		}
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		$this->captcha = new Captcha();
		Functions::view(youtube);
		
		Display::Ban(title, versi);
		
		$r = $this->Dashboard();
		if(!$r['bal']){
			Functions::removeConfig("cookie");
			print Display::Error("Cookie Expired\n");
			Display::Line();
			goto cookie;
		}
		
		Display::Cetak("Balance",$r['bal']);
		Display::Cetak("Bal_Api",$this->captcha->getBalance());
		Display::Line();
		if($this->Claim()){
			Functions::removeConfig("cookie");
			goto cookie;
		}
	}
	
	public function headers(){
		$h = [
			"user-agent: ".$this->uagent,
			"cookie: ".$this->cookie
		];
		return $h;
	}
	public function Dashboard(){
		$r = Requests::get(host."dashboard",$this->headers())[1];
		$data['bal'] = explode('</p>', explode('<i class="fas fa-coins"></i> ', $r)[1])[0];
		return $data;
	}
	public function Claim(){
		while(true){
			$r = Requests::get(host."faucet",$this->headers())[1];
			if(preg_match('/Just a moment/',$r)){print Display::Error("Cloudflare\n");return 1;}
			preg_match('/(\d{1,})\/(\d{1,})/',$r,$limit);
			if($limit[2]){
				$sisa = $limit[1];
				$limit = $limit[2];
			}
			if($sisa < 1){exit(Display::Error("Limit Claim Faucet\n"));}
			$csrf = explode('"',explode('id="token" value="',$r)[1])[0];
			$token = explode('"',explode('name="token" value="',$r)[1])[0];
			
			if(explode('rel=\"',$r)[1]){
				$antibot = $this->captcha->AntiBot($r);
				if(!$antibot)continue;
				$data = "antibotlinks=$antibot&csrf_token_name=".$csrf."&token=".$token;
			}else{
				$data = "csrf_token_name=".$csrf."&token=".$token;
			}
			
			$r = Requests::post(host."faucet/verify", $this->headers(),$data)[1];
			$tmr = explode('-',explode('let wait = ',$r)[1])[0];
			$ss = explode('has',explode("text: '",$r)[1])[0];
			$r = $this->Dashboard();
			if($ss){
				print Display::Sukses($ss);
				Display::Cetak("Limit",$sisa."/".$limit);
				Display::Cetak("Blanace",$r['bal']);
				Display::Cetak("Bal_Api",$this->captcha->getBalance());
				Display::Line();
			}else{
				print Display::Error("Not found\n");
				Display::Cetak("Limit",$sisa."/".$limit);
				Display::Cetak("Blanace",$r['bal']);
				Display::Line();
			}
			if($tmr){Functions::tmr($tmr);}
		}
	}
}

new Bot();