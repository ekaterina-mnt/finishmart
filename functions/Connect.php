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
        ];
        $client = new GuzzleClient(['verify' => false]);

        $client->request('GET', $link);

        $response = $client->request(
            'GET',
            $link,
            [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36 OPR/107.0.0.0 (Edition Yx 05)',
                    // 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
                    // 'Cookie' => '_ym_uid=1695039947128925397; _ym_d=1695039947; tt_deduplication_cookie=yandex; tt_deduplication_cookie=yandex; region=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0; BITRIX_SM_ab_test_list_buttons=B; BITRIX_SM_FIRST_SITE_VIZIT=otherpage; advcake_trackid=746a3b64-4ef9-0a08-16bc-a8b664d88f6a; advcake_session_id=194ae9a6-365a-58fd-6336-4795c202d513; BITRIX_SM_H2O_COOKIE_USER_ID=65484055ea6141a4c73a0370d1bad19f; BITRIX_SM_SALE_UID=414544818; _cmg_csstYEDrk=1705926481; _comagic_idYEDrk=8068200191.11865503636.1705926480; ABTEST_NEWSITE=1; _ym_isad=2; PHPSESSID=J5X1b2UuGJ5H5WtJBO2wIhbT3qB8aUWl; BITRIX_SM_REASPEKT_LAST_IP=79.104.6.123%2C%2037.220.161.11%2C%20212.193.152.21; BITRIX_SM_REASPEKT_GEOBASE=%7B%22ID%22%3A%22766%22%2C%22UF_ACTIVE%22%3A%221%22%2C%22UF_BLOCK_BEGIN%22%3A%221332216576%22%2C%22UF_BLOCK_END%22%3A%221332218367%22%2C%22INETNUM%22%3A%2279.104.3.0%20-%2079.104.9.255%22%2C%22COUNTRY_CODE%22%3A%22RU%22%2C%22UF_CITY_ID%22%3A%222097%22%2C%22UF_XML_ID%22%3A%222097%22%2C%22CITY%22%3A%22%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0%22%2C%22REGION%22%3A%22%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0%22%2C%22OKRUG%22%3A%22%D0%A6%D0%B5%D0%BD%D1%82%D1%80%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9%20%D1%84%D0%B5%D0%B4%D0%B5%D1%80%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9%20%D0%BE%D0%BA%D1%80%D1%83%D0%B3%22%2C%22LAT%22%3A%2255.755787%22%2C%22LON%22%3A%2237.617634%22%7D',
                    'Cookie' => 'PHPSESSID=ihL3J3deF7yTLvk76klExYWkepcDXTR3; ABTEST_NEWSITE=1; region=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0; BITRIX_SM_ab_test_list_buttons=A; BITRIX_SM_REASPEKT_LAST_IP=195.218.137.73%2C%20212.193.156.12%2C%20212.193.152.15; BITRIX_SM_REASPEKT_GEOBASE=false; BITRIX_SM_FIRST_SITE_VIZIT=otherpage; _ym_uid=1708935788215770826; _ym_d=1708935788; _ym_isad=2; advcake_trackid=b0eac1c0-fa11-b407-bb9b-97f12b391a5b; advcake_session_id=18bb9f85-5920-ef10-304c-043c0f39d018; _ym_visorc=w; BITRIX_SM_H2O_COOKIE_USER_ID=56bc4c09b0e283b8f70f2ddbf6034bba',
                    'Referer' => 'https://yandex.ru/',
                    'Sec-Ch-Ua' => '"Not A(Brand";v="99", "Opera";v="107", "Chromium";v="121"',
                    'Sec-Ch-Ua-Mobile' => '?0',
                    'Sec-Ch-Ua-Platform' => '"Windows"',
                    'Sec-Fetch-Dest' => 'document',
                    'Sec-Fetch-Mode' => 'navigate',
                    'Sec-Fetch-Site' => 'cross-site',
                    'Sec-Fetch-User' => '?1',
                    'Upgrade-Insecure-Requests' => '1',
                ],
            ],
        );

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
