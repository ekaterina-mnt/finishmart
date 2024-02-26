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

        try {
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
                        'Cookie' => 'sc_1708935285205=mosplitka:mosplitka.ru:%2Fsearch%2F:1708935281595422-14506463775709912956-balancer-l7leveler-kubr-yp-sas-189-BAL; yandexuid=606244811651658333; yabs-sid=2372547551651658880; yuidss=606244811651658333; gdpr=0; my=YwA=; _ym_uid=1673767916587972835; _ym_d=1693670950; yandex_login=penzeva.catherine; yashr=1243547561696333505; ymex=2014211337.yrts.1698851337; skid=251359301701364163; amcuid=7840210811703675415; is_gdpr=0; is_gdpr_b=COOiFBCF7AEoAg==; i=z3RQgoOrDwl2pLpoCsYMvQQDgUdaqcLaucmYokb1K6wI7V5Y6OCIrmz4ZmygpKyZTPNgZHi/sizzuF5SEpw7abHCZkA=; yabs-vdrf=yPTPb403Jlwm1lSzb30Fems010Sfb1m1l5hu1pibb1m1ke1O0GyDb6m09hb81RhTb8m2JNfW1DRLbGm2L-eW1ChLb1m1sA901Jx9b3W1g3NK1jwzb3W3zlLG1aAvb3W1xQd01Twvb1m07F8i1DQvb1m1kYXm0GAPb1m3Vcm002A1b3W2mvOa1JPHb1m0Fcm00-7zb5m1wTd41-tbb1m2IHtG1jsLbmW22e9S1zbrbPG0hkba0EafbjW3hP8i16qPb6W16yW005K9b1W1IdCC1MJzb7W2SJm007pfbf01w6LK0g3Tb7W3dUsa1ApLb7W1fc9G1-Ynb301GGXe1tonb1W1zlW001nzbVGRJfem1nWvb4G5MaVK0CWbb602SKi01XmLbFm0wfP01-WDbYm3-T8m1zmDbG03QJre0BVza1W2IrLy1BVza1W1gHwi1oFfa4m1nxN01e_baAGJxt0m0d_ba3027lnK0QFba1W3DbLy13lXa1W2CDQ01JlTa1W0yC5u1yFLa3018TXC0nhza9G0wXce1f9faSG0F6Ke1LvTa2W0L1QK0k9La1G3kJeS1seHa3m5l4YS1bdra1G2eoem10; _ym_isad=2; Session_id=3:1708872519.5.1.1662203806508:omjobQ:5.1.2:1|1474732927.20888621.2.2:20888621.3:1683092427|1000141041.14154966.2.2:14154966.3:1676358772|1791078267.22209059.2.2:22209059.3:1684412865|1876588732.28973792.2.2:28973792.3:1691177598|3:10283631.549321.9zM7uIAawMGzknH4F0yVk3ydgHQ; sessar=1.1187.CiAtSl7Yfxv12qAbk9P4-cj-rO0zTvz13ChERwfWszFmOA.Gkb1nBnTMCKTzQKKfFTTszoqUJvGwUDq9UCarXAO4zE; sessionid2=3:1708872519.5.1.1662203806508:omjobQ:5.1.2:1|1474732927.20888621.2.2:20888621.3:1683092427|1000141041.14154966.2.2:14154966.3:1676358772|1791078267.22209059.2.2:22209059.3:1684412865|1876588732.28973792.2.2:28973792.3:1691177598|3:10283631.549321.fakesign0000000000000000000; bh=EjciTm90IEEoQnJhbmQiO3Y9Ijk5IiwiT3BlcmEiO3Y9IjEwNyIsIkNocm9taXVtIjt2PSIxMjEiGgUieDg2IiIPIjEwNy4wLjUwNDUuMjEiKgI/MDoJIldpbmRvd3MiQggiMTUuMC4wIkoEIjY0IlJTIk5vdCBBKEJyYW5kIjt2PSI5OS4wLjAuMCIsIk9wZXJhIjt2PSIxMDcuMC41MDQ1LjIxIiwiQ2hyb21pdW0iO3Y9IjEyMS4wLjYxNjcuMTYwIiI=; ys=udn.cDrQldC60LDRgtC10YDQuNC90LAg0J8u#wprid.1708935281595422-14506463775709912956-balancer-l7leveler-kubr-yp-sas-189-BAL#c_chck.3239468714; yp=1711550920.hdrc.0#2008005536.multib.1#1736852656.p_cl.1705316656#1738329033.p_sw.1706793033#1738409613.p_undefined.1706873613#2024295285.pcs.1#1725206951.sp.family%3A0#1723825463.stltp.serp_bk-map_1_1692289463#1711772724.szm.1_2699999809265137%3A1210x681%3A1446x657#2011430688.udn.cDrQldC60LDRgtC10YDQuNC90LAg0J8u#1709988350.v_smr_onb.t%3D6%3A1702212350092#1714067405.v_sum_b_onb.1%3A1706291404876',
                        'Referer' => 'https://yandex.ru/',
                    ],
                ],
            );
        } catch (\Throwable $e) {
            var_dump($e->getMessage());
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
