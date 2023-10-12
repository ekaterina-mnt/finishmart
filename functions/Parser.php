<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Parser
{
    static function guzzleConnect(string $link): Document
    {
        $client = new GuzzleClient();
        $response = $client->request(
            'GET',
            $link,
            [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                ]
            ]
        );

        $document = self::getHTML($response);

        return $document;
    }

    static function getHTML(ResponseInterface $response): Document
    {
        $document = $response->getBody()->getContents();
        $document = new Document($document);
        return $document;
    }

    static function nextLink(string $link, int $limit): string|null
    {
        preg_match("#(.+offset=)(\d+)(.*)#", $link, $matches);
        if ($matches) {
            $new_offset_value = $matches[2] + $limit;
            $new_link = $matches[1] . $new_offset_value . $matches[3];

            $document = self::guzzleConnect($new_link);
            $api_data = self::getApiData($document);

            if (boolval(count($api_data) > 0)) {
                return $new_link;
            }
        }

        return null;
    }

    static function getApiData(Document $document): array
    {
        $api_data = json_decode($document->text(), 1);
        $api_data = $api_data[array_keys($api_data)[0]];
        return $api_data;
    }

    static function masterdomProducersCollections(string $needed): array
    {
        $all_tile_producers = [];
        $all_tile_collections = [];

        $url = "https://plitka.masterdom.ru";
        $document = self::guzzleConnect($url);

        $api_data_arr = $document->find('script')[9]->text();
        $api_data_arr = rtrim(str_replace("window.__initialData=", "", $api_data_arr), ";");
        $api_data_arr = json_decode($api_data_arr, 1);
        $api_data_country = $api_data_arr['store']['references']['data']['countries'];

        foreach ($api_data_country as $country) {
            if (!isset($country['nested'])) continue;
            $producer = $country['nested']['items'];

            foreach ($producer as $fabric_id => $fabric_name) {
                $all_tile_producers[$fabric_id] = [
                    'fabric_id' => $fabric_id,
                    'fabric_name' => $fabric_name['name'],
                    'country' => $country['name'],
                    'collections' => [],
                ];
                $collections = $fabric_name['nested']['items'];
                foreach ($collections as $collection) {
                    $all_tile_producers[$fabric_id]['collections'][] = $collection['id'];
                    $all_tile_collections[$collection['id']] = [
                        'collection_id' => $collection['id'],
                        'collection_name' => $collection['name'],
                        'fabric_id' => $collection['fabric_id'],
                        'country' => $country['name'],
                    ];
                }
            }
        }

        if ($needed == 'producers') {
            return $all_tile_producers;
        } elseif ($needed == 'collections') {
            return $all_tile_collections;
        }
    }
}
