<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_7_0($module)
{
    $views = array('grid', 'list', 'table');
    $result = true;

    foreach ($views as $view) {
        $result = $result && Configuration::updateValue(
            'PRODUCTLIST_CARD_' . strtoupper($view) . '_QUANTITY',
            1
        );
    }

    return $result;
}
