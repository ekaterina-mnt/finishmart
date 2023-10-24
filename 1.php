<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

class Data_1c
{
    static function getGroups(): array
    {
        $all_groups = [
            'Обои и настенные покрытия' => 'groupOboi',
            'Декоративные обои' => 'groupDecorativnye',
            'Плитка и керамогранит' => 'groupPlitka',
            'Керамическая плитка' => 'groupKeramicheskaya',
            'Керамогранит' => 'groupKeramogranit',
            'Мозаика' => 'groupMozaika',
            'Натуральный камень' => 'groupNaturalKamen',
            'Сантехника' => 'groupSantehnika',
            'Раковины' => 'groupRackoviny',
            'Ванны' => 'groupVanny',
            'Мебель для ванной комнаты' => 'groupSantMebel',
            'Полотенцесушители' => 'groupPolotencesuchiteli',
            'Смесители' => 'groupSmesiteli',
            'Унитазы, писсуары и биде' => 'groupUnitazy',
            'Комплектующие' => 'groupComplectuyuschie',
            'Душевые' => 'groupDushevye',
            'Аксессуары для ванной комнаты' => 'groupAksessuary',
        ];

        return $all_groups;
    }
}
