<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_11_0($module)
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
    $result = Configuration::updateValue('PRODUCTLIST_ENABLE_MASONRY', 1);

    foreach ($fields as $field) {
        $result = $result && Configuration::updateValue('PRODUCTLIST_CARD_MASONRY_' . $field, 1);
    }

    return $result;
}
