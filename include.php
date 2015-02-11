<?php
// Автозагрузка классов и регистрация обработчиков событий
\Bitrix\Main\Loader::registerAutoLoadClasses('itbiz.parser', array(

));

// Регистрация обработчиков событий
$eventManager = \Bitrix\Main\EventManager::getInstance();
