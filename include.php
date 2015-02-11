<?php
// Автозагрузка классов и регистрация обработчиков событий
\Bitrix\Main\Loader::registerAutoLoadClasses('itbiz.parser', array(
    "Itbiz\\Parser\\ParserInterface" => "lib/parser_interface.php",
));

// Регистрация обработчиков событий
$eventManager = \Bitrix\Main\EventManager::getInstance();
