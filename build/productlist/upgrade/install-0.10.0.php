<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_10_0($module)
{
    $fields = array(
        'IMAGE',
        'TITLE',
        'DESCRIPTION',
        'REFERENCE',
        'BRAND',
        'AVAILABILITY',
        'FLAGS',
        'QUICKVIEW',
        'COLORS',
        'QUANTITY',
        'PRICE',
        'ACTIONS',
    );
    $result = Configuration::updateValue('PRODUCTLIST_ENABLE_COMPACT', 1)
        && Configuration::updateValue('PRODUCTLIST_ENABLE_SHOWCASE', 1);

    foreach (array('COMPACT', 'SHOWCASE') as $view) {
        foreach ($fields as $field) {
            $result = $result && Configuration::updateValue('PRODUCTLIST_CARD_' . $view . '_' . $field, 1);
        }
    }

    return $result;
}
