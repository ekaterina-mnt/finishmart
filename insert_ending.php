<?php

use functions\TechInfo;
            $print_result = [];
            foreach ($all_product_data as $key => $val) {
                $print_result[$key] = $val[0];
            }
            TechInfo::preArray($print_result);

            //Для передачи в MySQL

            $types = '';
            $values = array();
            foreach ($all_product_data as $key => $n) {
                $types .= $n[1];
                $values[$key] = $n[0];
            }

            // Parser::insertProductData1($types, $values, $url_parser);