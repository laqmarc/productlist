<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_9_1($module)
{
    $mode = (int) Configuration::get('PRODUCTLIST_INFINITE_SCROLL') ? 'infinite' : 'pagination';

    return Configuration::updateValue('PRODUCTLIST_PAGINATION_MODE', $mode);
}
