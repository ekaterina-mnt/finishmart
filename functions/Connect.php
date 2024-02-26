<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Throwable;

class Connect
{
    static function guzzleConnect(string $link, $encoding = null): Document
    {
        $proxies = [
            'http://74vy0Q:RJ8SWP@192.168.16.1:10', //https://shopproxy.net/lk/
            'http://5LIZu3:C8V5mJmxxY@46.8.16.94', //https://ru.dashboard.proxy.market/proxy
            'http://RHex48:EEHsHg@185.147.130.119:9274',
        ];
        $client = new GuzzleClient(['verify' => false]);

        try {
            $response = $client->request(
                'GET',
                $link,
                [
                    'headers' => [
                        'X-Requested-With' => 'XMLHttpRequest',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36 OPR/107.0.0.0 (Edition Yx 05)',
                        'Cookie' => 'PHPSESSID=ihL3J3deF7yTLvk76klExYWkepcDXTR3; ABTEST_NEWSITE=1; region=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0; BITRIX_SM_ab_test_list_buttons=A; BITRIX_SM_REASPEKT_LAST_IP=195.218.137.73%2C%20212.193.156.12%2C%20212.193.152.15; BITRIX_SM_REASPEKT_GEOBASE=false; BITRIX_SM_FIRST_SITE_VIZIT=otherpage; _ym_uid=1708935788215770826; _ym_d=1708935788; _ym_isad=2; advcake_trackid=b0eac1c0-fa11-b407-bb9b-97f12b391a5b; advcake_session_id=18bb9f85-5920-ef10-304c-043c0f39d018; _ym_visorc=w; BITRIX_SM_H2O_COOKIE_USER_ID=56bc4c09b0e283b8f70f2ddbf6034bba',
                        'Referer' => 'https://yandex.ru/',
                    ],
                    // 'proxy' => 'http://RHex48:EEHsHg@185.147.130.119:9274', // https://proxy6.net/user/proxy
                    'proxy' => 'http://UQTk2ea5:Q4TraHz1@194.226.126.3:64142', // https://panel.proxyline.net/all/?orders=3729008
                ],
            );
        } catch (\Throwable $e) {
            var_dump($e);
        }

        $document = self::getHTML($response, $encoding ?? null);

        return $document;
    }

    static function getHTML(ResponseInterface $response, $encoding = null): Document
    {
        $document = $response->getBody()->getContents();
        if ($encoding) {
            $document = new Document(string: $document, encoding: $encoding);
        } else {
            $document = new Document(string: $document);
        }
        return $document;
    }
}
