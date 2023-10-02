<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$arComponentParameters = [
    'PARAMETERS' => [
        'CACHE_TIME' => ['DEFAULT' => 36000000],
        'ITEM_COUNT' => array(
            'NAME' => 'Количество элементов',
            'TYPE' => 'INTEGER',
            'MULTIPLE' => 'N',
        ),
        'AJAX' => array(
            'NAME' => 'AJAX?',
            'TYPE' => 'BOOLEAN',
            'MULTIPLE' => 'N',
        ),
        'PAGE' => array(
            'NAME' => 'Страница',
            'TYPE' => 'INTEGER',
            'MULTIPLE' => 'N',
        ),
        "LANGUAGE" => Array(
            "NAME" => "Язык",
            "TYPE" => "STRING",
            'DEFAULT' => "RU",
        ),
    ]
];

