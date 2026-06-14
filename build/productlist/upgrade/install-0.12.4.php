<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_12_4($module)
{
    if (Configuration::get('PRODUCTLIST_GRID_COLUMNS_MOBILE') === false) {
        return Configuration::updateValue('PRODUCTLIST_GRID_COLUMNS_MOBILE', 1);
    }

    return true;
}
