<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_2_0($module)
{
    $module->registerHook('displayProductListTop');

    return Configuration::updateValue(Productlist::CONFIG_GRID_COLUMNS_DESKTOP, 4)
        && Configuration::updateValue(Productlist::CONFIG_GRID_COLUMNS_TABLET, 3)
        && Configuration::updateValue(Productlist::CONFIG_LIST_IMAGE_WIDTH, 160)
        && Configuration::updateValue(Productlist::CONFIG_TABLE_IMAGE_WIDTH, 112)
        && Configuration::updateValue(Productlist::CONFIG_REMEMBER_VIEW, 1)
        && Configuration::updateValue(Productlist::CONFIG_AUTO_INJECT, 1);
}
