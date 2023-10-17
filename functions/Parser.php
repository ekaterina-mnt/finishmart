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
}
