<?php
$string = "CREATE TABLE `masterdom_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `link` text NOT NULL,
  `views` int DEFAULT NULL,
  `type` text NOT NULL,
  `product_views` int DEFAULT NULL,
  `date_edit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `link` (`link`(200))
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

  $result = str_replace(["COLLATE utf8mb4_0900_ai_ci", "COLLATE=utf8mb4_0900_ai_ci"], "", $string);

  echo $result;