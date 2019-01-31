<?php
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');
if($_GET['get-info']){
	$g = file_get_contents('http://dynupdate.no-ip.com/ip.php');
	die(json_encode(['ip_server' => $g]));
}
$cookie = trim($_POST['cookie']);
$bio = trim($_POST['bio'].' - '.time().' <3');

if ($_POST['gioithieu'] == 1) {
	$html = httpGet($cookie, 'https://mbasic.facebook.com/profile/basic/intro/bio/');

	preg_match('#<input type="hidden" name="fb_dtsg" value="(.+?)" autocomplete="off" />#', $html, $match);
	$a_fields['fb_dtsg'] = $match[1];

	preg_match('#<input type="hidden" name="jazoest" value="(.+?)" autocomplete="off" />#', $html, $match);
	$a_fields['jazoest'] = $match[1];
	$up = httpPost($cookie, 'https://mbasic.facebook.com/profile/intro/bio/save/', '&jazoest='.$a_fields['jazoest'].'&fb_dtsg='.$a_fields['fb_dtsg'].'&bio='.urlencode($bio));
	die ('Giới Thiệu: '.$bio);
}

$html = httpGet($cookie, 'https://mbasic.facebook.com/profile/questions/view/');
$a_fields = array();
preg_match('#<input type="hidden" name="fb_dtsg" value="(.+?)" autocomplete="off" />#', $html, $match);
$a_fields['fb_dtsg'] = $match[1];

preg_match('#<input type="hidden" name="jazoest" value="(.+?)" autocomplete="off" />#', $html, $match);
$a_fields['jazoest'] = $match[1];

$a_fields['query_history'] = '';

$a_fields['search_query'] = '';

preg_match('#<input type="hidden" name="typeahead_sid" value="(.+?)" />#', $html, $match);
$a_fields['typeahead_sid'] = $match[1];
if (empty($a_fields['typeahead_sid'])) {
	die('Done');
}
preg_match('#<input type="hidden" name="question" value="(.+?)" />#', $html, $match);
$a_fields['question'] = $match[1];

preg_match('#<input type="hidden" name="question_section_id" value="(.+?)" />#', $html, $match);
$a_fields['question_section_id'] = $match[1];

preg_match('#<input type="hidden" name="session_id" value="(.+?)" />#', $html, $match);
$a_fields['session_id'] = $match[1];

preg_match('#<input type="hidden" name="privacy\[(.+?)\]" value="(.+?)" />#', $html, $match);
$a_fields['privacy['.$match[1].']'] = $match[2];

preg_match('#<input type="hidden" name="privacy_x" value="(.+?)" />#', $html, $match);
$a_fields['privacy_x'] = $match[1];
$ch = $a_fields['question'];
$ch = explode("_", $ch)[1];
if ($ch == '1') {
	$type = 'Trường Học';
	$file = './highschool.txt';
} elseif ($ch == '2') {
	$type = 'Quê Hương';
	$file = './hometown.txt';
} elseif ($ch == '3') {
	$type = 'Nơi Sinh Sống';
	$file = './city.txt';
} elseif ($ch == '4') {
	$type = 'Đại Học';
	$file = './college.txt';
} elseif ($ch == '5') {
	$type = 'Nơi Làm Việc';
	$file = './work.txt';
} else {
	die('Done');
}

$g = file_get_contents($file);
$ar = explode("\n", $g);
$str = explode("|", $ar[array_rand($ar)]);
$hub_id_inference = trim($str[0]);
$hub_name_inference = trim($str[1]);

$a_fields['hub_id_inference'] = $hub_id_inference;

$fields = '';
foreach ($a_fields as $key => $value) {
	$fields .= '&'.$key.'='.$value;
}
if (isset($a_fields['hub_id_inference'])) {
	$up = httpPost($cookie, 'https://mbasic.facebook.com/_mupload_/profile/edit/questions/save/', $fields);
	echo $type.': '.$hub_name_inference;
} else {
	'Done';
}
function httpGet ($cookie, $url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Host: mbasic.facebook.com',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1',
	'User-Agent: '.getRandomUserAgent(),
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
	'Accept-Language: vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5',
	'Cookie: '.$cookie,

	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}
function httpPost ($cookie,$url, $fields) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Host: mbasic.facebook.com',
	'Connection: keep-alive',
	'Content-Length: '.strlen($fields),
	'Cache-Control: max-age=0',
	'Origin: https://mbasic.facebook.com',
	'Upgrade-Insecure-Requests: 1',
	'Content-Type: application/x-www-form-urlencoded',
	'User-Agent: '.getRandomUserAgent(),
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
	'Referer: https://mbasic.facebook.com/profile/questions/view/',
	'Accept-Language: vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5',
	'Cookie: '.$cookie,
	));
	curl_setopt($ch, CURLOPT_POST, 1);
  	curl_setopt($ch, CURLOPT_POSTFIELDS,$fields);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}
function getRandomUserAgent(){
    $userAgents = array(
        'Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
        'Mozilla/5.0 (Windows NT 5.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.63 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.65 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
        'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:50.0) Gecko/20100101 Firefox/50.0',
        'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:50.0) Gecko/20100101 Firefox/50.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:46.0) Gecko/20100101 Firefox/46.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0',
        'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0',
        'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
        'Mozilla/5.0 (Windows NT 6.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0',
        'Mozilla/5.0 (iPad; CPU OS 9_3_2 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13F69 Safari/601.1',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/601.7.7 (KHTML, like Gecko) Version/9.1.2 Safari/601.7.7',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
        'Mozilla/5.0 (iPad; CPU OS 10_2_1 like Mac OS X) AppleWebKit/602.4.6 (KHTML, like Gecko) Version/10.0 Mobile/14D27 Safari/602.1',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/601.4.4 (KHTML, like Gecko) Version/9.0.3 Safari/601.4.4',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 10_2_1 like Mac OS X) AppleWebKit/602.4.6 (KHTML, like Gecko) Version/10.0 Mobile/14D27 Safari/602.1',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/602.4.8 (KHTML, like Gecko) Version/10.0.3 Safari/602.4.8',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 9_3 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13E188a Safari/601.1',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.1 Safari/603.1.30',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_4) AppleWebKit/601.5.17 (KHTML, like Gecko) Version/9.1 Safari/601.5.17',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/603.2.4 (KHTML, like Gecko) Version/10.1.1 Safari/603.2.4',
        'Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/601.6.17 (KHTML, like Gecko) Version/9.1.1 Safari/601.6.17'
    );
    return $userAgents[array_rand($userAgents)];
}
?>