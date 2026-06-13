<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_9_0($module)
{
    return Configuration::updateValue('PRODUCTLIST_PRODUCTS_PER_PAGE', 0)
        && Configuration::updateValue('PRODUCTLIST_INFINITE_SCROLL', 0);
}
