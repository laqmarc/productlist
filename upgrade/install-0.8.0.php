<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_8_0($module)
{
    $views = array('grid', 'list', 'table');
    $fields = array('quickview', 'colors');
    $result = true;

    foreach ($views as $view) {
        foreach ($fields as $field) {
            $result = $result && Configuration::updateValue(
                'PRODUCTLIST_CARD_' . strtoupper($view) . '_' . strtoupper($field),
                1
            );
        }
    }

    return $result;
}
