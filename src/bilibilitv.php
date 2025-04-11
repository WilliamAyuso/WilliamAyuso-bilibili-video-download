<?php
/**
 * get bilibilitv video url
 */

$videolink="https://www.bilibili.tv/en/video/4791236418470402";
if (strpos($videolink,"bilibili") !== false) {
  if (preg_match("/\/video\/(\d+)/",$videolink,$m))
    $l="https://api.bilibili.tv/intl/gateway/web/playurl?s_locale=en&platform=web&aid=".$m[1];
  elseif (preg_match("/\/play(\/\d+)?\/(\d+)/",$videolink,$m))
    $l="https://api.bilibili.tv/intl/gateway/web/playurl?s_locale=en&platform=web&ep_id=".$m[2];
  $head=array('User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.5672.92 Safari/537.36',
  'Accept: application/json, text/plain, */*',
  'Accept-Language: ja,en-JP;q=0.95,en-US;q=0.9,en;q=0.8',
  'Accept-Encoding: deflate',
  'Referer: https://www.bilibili.tv/',
  'Origin: https://www.bilibili.tv',
  'Connection: keep-alive',
  'Sec-Fetch-Dest: empty',
  'Sec-Fetch-Mode: cors',
  'Sec-Fetch-Site: same-site');
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $l);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $h = curl_exec($ch);
  curl_close($ch);
  $r=json_decode($h,1);
  $x=$r['data']['playurl'];
  $video=$x['video'][0]['video_resource']['url'];
  $audio=$x['audio_resource'][0]['url'];
}
