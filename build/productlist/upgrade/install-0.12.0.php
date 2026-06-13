<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_12_0($module)
{
    if (Configuration::get('PRODUCTLIST_IMAGE_MODE') === false) {
        return Configuration::updateValue('PRODUCTLIST_IMAGE_MODE', 'none');
    }

    return true;
}
