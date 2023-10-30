<?php
$string = "CREATE TABLE `mosplitka_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `articul` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `category` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `subcategory` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `link` text NOT NULL,
  `price` int DEFAULT NULL,
  `edizm` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `stock` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `producer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `collection` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `provider_id` int DEFAULT NULL,
  `length` float DEFAULT NULL,
  `width` float DEFAULT NULL,
  `height` float DEFAULT NULL,
  `depth` float DEFAULT NULL,
  `thickness` float DEFAULT NULL,
  `format` varchar(100) DEFAULT NULL,
  `material` varchar(300) DEFAULT NULL,
  `images` json DEFAULT NULL,
  `variants` json DEFAULT NULL,
  `characteristics` json DEFAULT NULL,
  `product_usages` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `complectation` varchar(500) DEFAULT NULL,
  `type` varchar(500) DEFAULT NULL,
  `form` varchar(500) DEFAULT NULL,
  `design` varchar(500) DEFAULT NULL,
  `color` varchar(500) DEFAULT NULL,
  `orientation` varchar(500) DEFAULT NULL,
  `surface` varchar(500) DEFAULT NULL,
  `pattern` varchar(500) DEFAULT NULL,
  `montage` varchar(500) DEFAULT NULL,
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_edit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `link` (`link`(600)),
  UNIQUE KEY `articul` (`articul`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
";

  $result = str_replace(["COLLATE utf8mb4_0900_ai_ci", "COLLATE=utf8mb4_0900_ai_ci"], "", $string);

  echo $result;