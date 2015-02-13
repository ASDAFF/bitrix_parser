<?php
// Автозагрузка классов и регистрация обработчиков событий
\Bitrix\Main\Loader::registerAutoLoadClasses('itbiz.parser', array(
    "Itbiz\\Parser\\ParserInterface" => "lib/parser_interface.php",
    "Itbiz\\Parser\\ParserActions" => "lib/parser_actions.php",
    "Itbiz\\Parser\\simple_html_dom" => "lib/simple_html_dom.php",
));

// Регистрация обработчиков событий
$eventManager = \Bitrix\Main\EventManager::getInstance();
