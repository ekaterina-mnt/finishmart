<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class Connect
{
    static function guzzleConnect(string $link, $encoding = null): Document
    {
        $proxies = [
            'http://74vy0Q:RJ8SWP@192.168.16.1:10', //https://shopproxy.net/lk/
            'http://5LIZu3:C8V5mJmxxY@46.8.16.94', //https://ru.dashboard.proxy.market/proxy
        ];
        $client = new GuzzleClient(['verify' => false]);

        try {
            $client->request('GET', $link);
        } catch (RequestException $e) {
            //Access the message or other type of errors and react to them
            $e->getMessage();
            if (! $e->hasResponse()) {
                //No response from server. Assume the host is offline or server is overloaded.
            }
        }
        exit;
        $response = $client->request(
            'GET',
            $link,
            [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36 OPR/104.0.0.0 (Edition Yx 05)',
                    'Cookie' => 'BITRIX_SM_FIRST_SITE_VIZIT=otherpage; BITRIX_SM_H2O_COOKIE_USER_ID=0c7b53e54c7a1261c53b057b9344953b; BITRIX_SM_REASPEKT_GEOBASE=false; BITRIX_SM_REASPEKT_LAST_IP=54.86.50.139%2C%2046.235.188.17%2C%20212.193.152.15; BITRIX_SM_ab_test_list_buttons=A; PHPSESSID=6MCzH3hwXwzcVmd6iqznkLxR76Ci6yJA; region=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0',
                ],
            ],
        );

        var_dump($response);
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
