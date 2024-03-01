<?php

namespace functions\GoogleSheets;

require_once __DIR__ . '/../../vendor/autoload.php';

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Google_Service_Sheets_BatchUpdateValuesRequest;
use Google_Service_Sheets_ClearValuesRequest;
use Google_Service_Sheets_SheetProperties;
use Google_Service_Sheets_AddSheetRequest;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;

class Sheet
{
    private static $service;

    static function get_connect($service_account)
    {
        if (!self::$service) {
            $service_json_title = 'service_account_key.json'; // бывший oboi_raw
            // Наш ключ доступа к сервисному аккаунту
            $googleAccountKeyFilePath = __DIR__ . '/../../google_sheets/' . $service_json_title;
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

    static function get_sheetID($service_account)
    {
        // ID таблицы
        if ($service_account == 'napolnye_raw') {
            $spreadsheetId = '15BAlc52xS_7RxYYCrM5Jw4g9TEiS7EoeUwcLPYqPdyc';
        } elseif ($service_account == 'plitka_raw') {
            $spreadsheetId = '1AOoMtVpA2SP6gR5UEjZWOqk-BuzP0YXxos2UoPj1qBw';
        } elseif ($service_account == 'oboi_raw') {
            $spreadsheetId = '1zY-Obyh3HXz8rNZ5FhKmEDmvy-2SX01BZpliDwBTiHs';
        } elseif ($service_account == 'lepnina_raw') {
            $spreadsheetId = '1XjZjM82CpVp9_5TakR5Rf1mZOxMglmKtVaelaBlUbqo';
        } elseif ($service_account == 'kraski_raw') {
            $spreadsheetId = '1u3FYfC6hfARUpeerj92GZwK6HPHmL3-SfGsFtc2YnBM';
        } elseif ($service_account == 'santechnika_raw') {
            $spreadsheetId = '1Lqh8l_rXF3olLl1T91-dSQqiZQ9peOHmYp0ZELd0w78';
        } else {
            die("<br>Не определен $spreadsheetId<br>");
        }


        return $spreadsheetId;
    }

    //Пример $range = 'Лист1!G2:G3'
    static function get_data($range, $service_account)
    {
        $service = self::get_connect($service_account);
        $response = $service->spreadsheets_values->get(self::get_sheetID($service_account), $range);
        return $response;
    }

    static function update_data($range, $values, $service_account)
    {
        $values = array($values);

        // $values = [
        //     ["Eric", "3", "3", "3", "3"],
        // ];


        $service = self::get_connect($service_account);

        $ValueRange = new Google_Service_Sheets_ValueRange();
        // $ValueRange->setMajorDimension('COLUMNS'); //по колонкам
        $ValueRange->setValues($values);
        $options = ['valueInputOption' => 'USER_ENTERED'];

        $service->spreadsheets_values->update(self::get_sheetID($service_account), $range, $ValueRange, $options);
        echo "Данные успешно обновлены";
    }

    static function update_few_data($data, $service_account)
    {
        // $data = [[   'range'=> 'Sheet1!A4',
        //              'values'=> array(array('1234'))  ]];

        $service = self::get_connect($service_account);

        // Additional ranges to update ...
        $body = new Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'USER_ENTERED',
            'data' => $data
        ]);

        //   MyHelpers::pre($data);
        $service->spreadsheets_values->batchUpdate(self::get_sheetID($service_account), $body);
    }

    static function clear_data($range, $service_account)
    {
        // $range = '2020-10!A5:E5'
        $service = self::get_connect($service_account);

        $clear = new Google_Service_Sheets_ClearValuesRequest();
        $response = $service->spreadsheets_values->clear(self::get_sheetID($service_account), $range, $clear);
    }

    static function create_new_page($title, $service_account)
    {
        $service = self::get_connect($service_account);
        $spreadsheetId = self::get_sheetID($service_account);

        //Создаем новый объект с типом свойство листа
        $SheetProperties = new Google_Service_Sheets_SheetProperties();
        // Указываем имя листа
        $SheetProperties->setTitle($title);

        // Объект - запрос на добавление листа
        $AddSheetRequests = new Google_Service_Sheets_AddSheetRequest();
        $AddSheetRequests->setProperties($SheetProperties);

        // Объект - запрос
        $SheetRequests = new Google_Service_Sheets_Request();
        $SheetRequests->setAddSheet($AddSheetRequests);

        // Объект - запрос на обновление электронной таблицы
        $requests = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
        $requests->setRequests($SheetRequests);

        // Выполняем запрос на обновление таблицы
        $response = $service->spreadsheets->BatchUpdate($spreadsheetId, $requests);
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
