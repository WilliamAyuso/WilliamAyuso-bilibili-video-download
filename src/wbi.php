<?php
/**
 * bilibili Wbi get
 */

class Bilibili {

    private $mixinKeyEncTab = [
        46, 47, 18, 2, 53, 8, 23, 32, 15, 50, 10, 31, 58, 3, 45, 35, 27, 43, 5, 49,
        33, 9, 42, 19, 29, 28, 14, 39, 12, 38, 41, 13, 37, 48, 7, 16, 24, 55, 40,
        61, 26, 17, 0, 1, 60, 51, 30, 4, 22, 25, 54, 21, 56, 59, 6, 63, 57, 62, 11,
        36, 20, 34, 44, 52
    ];

    function __construct() {
    }

    public function reQuery(array $query) {
        $wbi_keys = $this->getWbiKeys();
        return $this->encWbi($query, $wbi_keys['img_key'], $wbi_keys['sub_key']);
    }

    private function getMixinKey($orig) {
        $t = '';
        foreach ($this->mixinKeyEncTab as $n) $t .= $orig[$n];
        return substr($t, 0, 32);
    }

    private function encWbi($params, $img_key, $sub_key) {
        $mixin_key = $this->getMixinKey($img_key . $sub_key);
        $curr_time = time();
        $chr_filter = "/[!'()*]/";

        $query = [];
        $params['wts'] = $curr_time;

        ksort($params);

        foreach ($params as $key => $value) {
            $value = preg_replace($chr_filter, '', $value);
            $query[] = urlencode($key) . '=' . urlencode($value);
        }

        $query = implode('&', $query);
        $wbi_sign = md5($query . $mixin_key);

        return $query . '&w_rid=' . $wbi_sign;
    }

    private function getWbiKeys() {
        $resp = @json_decode(
            $this->curl_get(
                'https://api.bilibili.com/x/web-interface/nav',
                null,
                'https://www.bilibili.com/'
            ), true
        );

        if (!$resp) throw new Exception('Request failed');

        $img_url = $resp['data']['wbi_img']['img_url'];
        $sub_url = $resp['data']['wbi_img']['sub_url'];

        return [
            'img_key' => substr(basename($img_url), 0, strpos(basename($img_url), '.')),
            'sub_key' => substr(basename($sub_url), 0, strpos(basename($sub_url), '.'))
        ];
    }

    private function curl_get($url, $cookies = null, $referer = 'https://www.bilibili.com/', $ua = null, $proxy = null, $header = []) {
        $ch = curl_init();
        $header[] = "Accept: */*";
        $header[] = "Accept-Language: ja,en-JP;q=0.95,en-US;q=0.9,en;q=0.8";
        $header[] = "Connection: close";
        $header[] = "Referer:https://www.bilibili.com/";
        $header[] = "Cache-Control: max-age=0";
        curl_setopt_array($ch, [
            CURLOPT_HTTPGET         =>  1,
            CURLOPT_CUSTOMREQUEST   =>  'GET',
            CURLOPT_RETURNTRANSFER  =>  1,
            CURLOPT_HTTPHEADER      =>  $header,
            CURLOPT_ENCODING        =>  '',
            CURLOPT_URL             =>  $url,
            CURLOPT_USERAGENT       =>  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.5672.92 Safari/537.36',
            CURLOPT_TIMEOUT         =>  50
        ]);

        if ($cookies) curl_setopt(
            $ch,
            CURLOPT_COOKIE,
            $cookies
        );

        if ($referer) curl_setopt_array($ch, [
            CURLOPT_AUTOREFERER =>  $referer,
            CURLOPT_REFERER     =>  $referer
        ]);

        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
}

$c = new Bilibili();
echo $c->reQuery(['foo' => '114', 'bar' => '514', 'baz' => 1919810]);
