<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method. Please use POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['aid']) || !isset($input['cid']) || !isset($input['sessdata'])) {
    echo json_encode(['error' => 'Missing required parameters: aid, cid, or sessdata.']);
    exit;
}

$aid = $input['aid'];
$cid = $input['cid'];
// $sessdata = $input['sessdata'];

$aid = '11111';
$cid = '11111';
$sessdata = '';


class BilibiliAPI
{
    private $headers = [];
    private $response = null;
    private $error = null;

    public function __construct($sessdata)
    {
        $this->headers = [
            "Referer" => "https://www.bilibili.com/",
            "Cookie" => "SESSDATA=$sessdata",
            "X-Real-IP" => $this->generateFakeIP(),
            "Accept" => "application/json, text/plain, */*",
            "Content-Type" => "application/json; charset=utf-8",
        ];
    }

    private function generateFakeIP()
    {
        return long2ip(mt_rand(0x0A000000, 0xDF000000));
    }

    public function request($url, $method = 'GET', $params = [])
    {
        $headers = array_map(function ($key, $value) {
            return "$key: $value";
        }, array_keys($this->headers), $this->headers);

        $ch = curl_init();

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        $this->response = curl_exec($ch);
        $this->error = curl_error($ch);

        curl_close($ch);

        if ($this->error) {
            return [
                'error' => $this->error,
            ];
        }

        return json_decode($this->response, true) ?: $this->response;
    }
}

$api = new BilibiliAPI($sessdata);
$url = 'https://api.bilibili.com/x/player/v2';
$response = $api->request($url, 'GET', ['aid' => $aid, 'cid' => $cid]);

if (isset($response['data']['subtitle']['subtitles'][0]['subtitle_url'])) {
    $subtitleUrl = $response['data']['subtitle']['subtitles'][0]['subtitle_url'];
    echo json_encode(['subtitle_url' => $subtitleUrl]);
} else {
    echo json_encode(['error' => 'Subtitle URL not found.']);
}

// if (isset($response)) {
//     echo json_encode($response);
// } else {
//     echo json_encode(['error' => 'Subtitle URL not found.']);
// }
