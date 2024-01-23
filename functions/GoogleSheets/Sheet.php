<?php

namespace functions\GoogleSheets;

require_once __DIR__ . '/../../vendor/autoload.php';

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Google_Service_Sheets_BatchUpdateValuesRequest;
use Google_Service_Sheets_ClearValuesRequest;

class Sheet
{
    private static $service;

    static function get_connect()
    {
            if (!self::$service) {
                // Наш ключ доступа к сервисному аккаунту
                $googleAccountKeyFilePath = __DIR__ . '/../../google_sheets/service_key.json';
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath);
                // Создаем новый клиент
                $client = new Google_Client();
                // Устанавливаем полномочия
                $client->useApplicationDefaultCredentials();
                // Добавляем область доступа к чтению, редактированию, созданию и удалению таблиц
                $client->addScope('https://www.googleapis.com/auth/spreadsheets');
                $service = new Google_Service_Sheets($client);
                self::$service = $service;
            }

            return self::$service;
    }

    static function get_sheetID()
    {
        // ID таблицы
        $spreadsheetId = '15BAlc52xS_7RxYYCrM5Jw4g9TEiS7EoeUwcLPYqPdyc';
        return $spreadsheetId;
    }

    //Пример $range = 'Лист1!G2:G3'
    static function get_data($range)
    {
            $service = self::get_connect();
            $response = $service->spreadsheets_values->get(self::get_sheetID(), $range);
            return $response;
    }

    static function update_data($range, $values)
    {
            $service = self::get_connect();

            var_dump($values);
            var_dump($range);

            $ValueRange = new Google_Service_Sheets_ValueRange();
            // $ValueRange->setMajorDimension('COLUMNS'); //по колонкам
            $ValueRange->setValues($values);
            $options = ['valueInputOption' => 'USER_ENTERED'];

            $service->spreadsheets_values->update(self::get_sheetID(), $range, $ValueRange, $options);
            echo "Данные успешно обновлены";
    }

    static function update_few_data($data)
    {
        // $data = [[   'range'=> 'Sheet1!A4',
        //              'values'=> array(array('1234'))  ]];

        $service = self::get_connect();

        // Additional ranges to update ...
        $body = new Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'USER_ENTERED',
            'data' => $data
        ]);

        //   MyHelpers::pre($data);
        $service->spreadsheets_values->batchUpdate(self::get_sheetID(), $body);
    }

    static function clear_data($range)
    {
        // $range = '2020-10!A5:E5'
        $service = self::get_connect();

        $clear = new Google_Service_Sheets_ClearValuesRequest();
        $response = $service->spreadsheets_values->clear(self::get_sheetID(), $range, $clear);
    }

    static function get_month_columns()
    {
        $months = array();
        $word = 'G';
        for ($i = 1; $i < 13; $i++) {
            $months[$i] = $word++;
        }
        return $months;
    }
}
