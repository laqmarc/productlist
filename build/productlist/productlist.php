<?php
/**
 * Product List Views
 *
 * Adds category product list view switcher controls.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Productlist extends Module
{
    const CONFIG_DEFAULT_VIEW = 'PRODUCTLIST_DEFAULT_VIEW';
    const CONFIG_ENABLE_GRID = 'PRODUCTLIST_ENABLE_GRID';
    const CONFIG_ENABLE_LIST = 'PRODUCTLIST_ENABLE_LIST';
    const CONFIG_ENABLE_TABLE = 'PRODUCTLIST_ENABLE_TABLE';
    const CONFIG_ENABLE_COMPACT = 'PRODUCTLIST_ENABLE_COMPACT';
    const CONFIG_ENABLE_SHOWCASE = 'PRODUCTLIST_ENABLE_SHOWCASE';
    const CONFIG_ENABLE_MASONRY = 'PRODUCTLIST_ENABLE_MASONRY';
    const CONFIG_REMEMBER_VIEW = 'PRODUCTLIST_REMEMBER_VIEW';
    const CONFIG_AUTO_INJECT = 'PRODUCTLIST_AUTO_INJECT';
    const CONFIG_GRID_COLUMNS_DESKTOP = 'PRODUCTLIST_GRID_COLUMNS_DESKTOP';
    const CONFIG_GRID_COLUMNS_TABLET = 'PRODUCTLIST_GRID_COLUMNS_TABLET';
    const CONFIG_LIST_IMAGE_WIDTH = 'PRODUCTLIST_LIST_IMAGE_WIDTH';
    const CONFIG_TABLE_IMAGE_WIDTH = 'PRODUCTLIST_TABLE_IMAGE_WIDTH';
    const CONFIG_PRODUCTS_PER_PAGE = 'PRODUCTLIST_PRODUCTS_PER_PAGE';
    const CONFIG_INFINITE_SCROLL = 'PRODUCTLIST_INFINITE_SCROLL';
    const CONFIG_PAGINATION_MODE = 'PRODUCTLIST_PAGINATION_MODE';

    private $views = array('grid', 'list', 'table', 'compact', 'showcase', 'masonry');

    public function __construct()
    {
        $this->name = 'productlist';
        $this->tab = 'front_office_features';
        $this->version = '0.11.1';
        $this->author = 'Modulspresata';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product List Views');
        $this->description = $this->l('Adds multiple product list views to category product listings.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayProductListReviews')
            && $this->registerOptionalHook('displayProductListTop')
            && Configuration::updateValue(self::CONFIG_DEFAULT_VIEW, 'grid')
            && Configuration::updateValue(self::CONFIG_ENABLE_GRID, 1)
            && Configuration::updateValue(self::CONFIG_ENABLE_LIST, 1)
            && Configuration::updateValue(self::CONFIG_ENABLE_TABLE, 1)
            && Configuration::updateValue(self::CONFIG_ENABLE_COMPACT, 1)
            && Configuration::updateValue(self::CONFIG_ENABLE_SHOWCASE, 1)
            && Configuration::updateValue(self::CONFIG_ENABLE_MASONRY, 1)
            && Configuration::updateValue(self::CONFIG_REMEMBER_VIEW, 1)
            && Configuration::updateValue(self::CONFIG_AUTO_INJECT, 1)
            && Configuration::updateValue(self::CONFIG_GRID_COLUMNS_DESKTOP, 4)
            && Configuration::updateValue(self::CONFIG_GRID_COLUMNS_TABLET, 3)
            && Configuration::updateValue(self::CONFIG_LIST_IMAGE_WIDTH, 160)
            && Configuration::updateValue(self::CONFIG_TABLE_IMAGE_WIDTH, 112)
            && Configuration::updateValue(self::CONFIG_PRODUCTS_PER_PAGE, 0)
            && Configuration::updateValue(self::CONFIG_INFINITE_SCROLL, 0)
            && Configuration::updateValue(self::CONFIG_PAGINATION_MODE, 'pagination')
            && $this->installCardConfig();
    }

    public function uninstall()
    {
        return Configuration::deleteByName(self::CONFIG_DEFAULT_VIEW)
            && Configuration::deleteByName(self::CONFIG_ENABLE_GRID)
            && Configuration::deleteByName(self::CONFIG_ENABLE_LIST)
            && Configuration::deleteByName(self::CONFIG_ENABLE_TABLE)
            && Configuration::deleteByName(self::CONFIG_ENABLE_COMPACT)
            && Configuration::deleteByName(self::CONFIG_ENABLE_SHOWCASE)
            && Configuration::deleteByName(self::CONFIG_ENABLE_MASONRY)
            && Configuration::deleteByName(self::CONFIG_REMEMBER_VIEW)
            && Configuration::deleteByName(self::CONFIG_AUTO_INJECT)
            && Configuration::deleteByName(self::CONFIG_GRID_COLUMNS_DESKTOP)
            && Configuration::deleteByName(self::CONFIG_GRID_COLUMNS_TABLET)
            && Configuration::deleteByName(self::CONFIG_LIST_IMAGE_WIDTH)
            && Configuration::deleteByName(self::CONFIG_TABLE_IMAGE_WIDTH)
            && Configuration::deleteByName(self::CONFIG_PRODUCTS_PER_PAGE)
            && Configuration::deleteByName(self::CONFIG_INFINITE_SCROLL)
            && Configuration::deleteByName(self::CONFIG_PAGINATION_MODE)
            && $this->uninstallCardConfig()
            && parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitProductlistConfig')) {
            $defaultView = Tools::getValue(self::CONFIG_DEFAULT_VIEW, 'grid');
            $enableGrid = (int) Tools::getValue(self::CONFIG_ENABLE_GRID);
            $enableList = (int) Tools::getValue(self::CONFIG_ENABLE_LIST);
            $enableTable = (int) Tools::getValue(self::CONFIG_ENABLE_TABLE);
            $enableCompact = (int) Tools::getValue(self::CONFIG_ENABLE_COMPACT);
            $enableShowcase = (int) Tools::getValue(self::CONFIG_ENABLE_SHOWCASE);
            $enableMasonry = (int) Tools::getValue(self::CONFIG_ENABLE_MASONRY);
            $gridColumnsDesktop = $this->clampInteger(Tools::getValue(self::CONFIG_GRID_COLUMNS_DESKTOP), 2, 6, 4);
            $gridColumnsTablet = $this->clampInteger(Tools::getValue(self::CONFIG_GRID_COLUMNS_TABLET), 2, 4, 3);
            $listImageWidth = $this->clampInteger(Tools::getValue(self::CONFIG_LIST_IMAGE_WIDTH), 80, 320, 160);
            $tableImageWidth = $this->clampInteger(Tools::getValue(self::CONFIG_TABLE_IMAGE_WIDTH), 60, 220, 112);
            $productsPerPage = $this->clampInteger(Tools::getValue(self::CONFIG_PRODUCTS_PER_PAGE), 0, 120, 0);
            $paginationMode = Tools::getValue(self::CONFIG_PAGINATION_MODE, 'pagination');

            if (!in_array($paginationMode, array('pagination', 'infinite'))) {
                $paginationMode = 'pagination';
            }

            if (!in_array($defaultView, $this->views)) {
                $defaultView = 'grid';
            }

            if (!$enableGrid && !$enableList && !$enableTable && !$enableCompact && !$enableShowcase && !$enableMasonry) {
                $output .= $this->displayError($this->l('At least one view must be enabled.'));
            } else {
                Configuration::updateValue(self::CONFIG_DEFAULT_VIEW, $defaultView);
                Configuration::updateValue(self::CONFIG_ENABLE_GRID, $enableGrid);
                Configuration::updateValue(self::CONFIG_ENABLE_LIST, $enableList);
                Configuration::updateValue(self::CONFIG_ENABLE_TABLE, $enableTable);
                Configuration::updateValue(self::CONFIG_ENABLE_COMPACT, $enableCompact);
                Configuration::updateValue(self::CONFIG_ENABLE_SHOWCASE, $enableShowcase);
                Configuration::updateValue(self::CONFIG_ENABLE_MASONRY, $enableMasonry);
                Configuration::updateValue(self::CONFIG_REMEMBER_VIEW, (int) Tools::getValue(self::CONFIG_REMEMBER_VIEW));
                Configuration::updateValue(self::CONFIG_AUTO_INJECT, (int) Tools::getValue(self::CONFIG_AUTO_INJECT));
                Configuration::updateValue(self::CONFIG_GRID_COLUMNS_DESKTOP, $gridColumnsDesktop);
                Configuration::updateValue(self::CONFIG_GRID_COLUMNS_TABLET, $gridColumnsTablet);
                Configuration::updateValue(self::CONFIG_LIST_IMAGE_WIDTH, $listImageWidth);
                Configuration::updateValue(self::CONFIG_TABLE_IMAGE_WIDTH, $tableImageWidth);
                Configuration::updateValue(self::CONFIG_PRODUCTS_PER_PAGE, $productsPerPage);
                Configuration::updateValue(self::CONFIG_PAGINATION_MODE, $paginationMode);
                Configuration::updateValue(self::CONFIG_INFINITE_SCROLL, (int) ($paginationMode === 'infinite'));

                $output .= $this->displayConfirmation($this->l('Settings updated.'));
            }
        }

        if (Tools::isSubmit('submitProductlistCardConfig')) {
            $this->saveCardConfig();
            $output .= $this->displayConfirmation($this->l('Card visibility updated.'));
        }

        return $output . $this->renderForm() . $this->renderCardConfigTabs();
    }

    public function hookDisplayHeader()
    {
        if (!$this->shouldLoadAssets()) {
            return;
        }

        $this->context->controller->registerStylesheet(
            'module-productlist-views',
            'modules/' . $this->name . '/views/css/productlist.css',
            array('media' => 'all', 'priority' => 150)
        );

        $this->context->controller->registerJavascript(
            'module-productlist-views',
            'modules/' . $this->name . '/views/js/productlist.js',
            array('position' => 'bottom', 'priority' => 150)
        );

        Media::addJsDef(array(
            'productlistDefaultView' => $this->getDefaultView(),
            'productlistViews' => $this->getEnabledViews(),
            'productlistRememberView' => (bool) Configuration::get(self::CONFIG_REMEMBER_VIEW),
            'productlistAutoInject' => (bool) Configuration::get(self::CONFIG_AUTO_INJECT),
            'productlistLayout' => $this->getLayoutConfig(),
            'productlistProductsPerPage' => $this->clampInteger(Configuration::get(self::CONFIG_PRODUCTS_PER_PAGE), 0, 120, 0),
            'productlistPaginationMode' => $this->getPaginationMode(),
            'productlistInfiniteScroll' => $this->getPaginationMode() === 'infinite',
            'productlistCardConfig' => $this->getCardConfig(),
            'productlistLabels' => array(
                'view' => $this->l('View'),
                'productView' => $this->l('Product view'),
                'image' => $this->l('Image'),
                'product' => $this->l('Product'),
                'reference' => $this->l('Reference'),
                'brand' => $this->l('Brand'),
                'availability' => $this->l('Availability'),
                'flags' => $this->l('Flags'),
                'price' => $this->l('Price'),
                'action' => $this->l('Action'),
                'addToCart' => $this->l('Add to cart'),
                'addingToCart' => $this->l('Adding...'),
                'addedToCart' => $this->l('Added'),
                'addToCartError' => $this->l('Could not add to cart'),
                'loadingMore' => $this->l('Loading more products...'),
                'noMoreProducts' => $this->l('No more products'),
                'loadMoreError' => $this->l('Could not load more products'),
                'loadedProducts' => $this->l('Loaded products'),
            ),
        ));
    }

    public function hookDisplayProductListReviews($params)
    {
        if (empty($params['product'])) {
            return '';
        }

        $productData = $this->buildProductPayload($params['product']);

        if (empty($productData['id'])) {
            return '';
        }

        $this->context->smarty->assign(array(
            'productlist_product_json' => json_encode($productData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
        ));

        return $this->display(__FILE__, 'views/templates/hook/product-data.tpl');
    }

    public function hookDisplayProductListTop()
    {
        $enabledViews = $this->getEnabledViews();

        if (count($enabledViews) < 2) {
            return '';
        }

        $this->context->smarty->assign(array(
            'productlist_views' => $enabledViews,
            'productlist_default_view' => $this->getDefaultView(),
        ));

        return $this->display(__FILE__, 'views/templates/hook/view-switcher.tpl');
    }

    private function renderForm()
    {
        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Category product views'),
                    'icon' => 'icon-th-large',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default view'),
                        'name' => self::CONFIG_DEFAULT_VIEW,
                        'options' => array(
	                            'query' => array(
	                                array('id' => 'grid', 'name' => $this->l('Grid')),
	                                array('id' => 'list', 'name' => $this->l('List')),
		                                array('id' => 'table', 'name' => $this->l('Table')),
		                                array('id' => 'compact', 'name' => $this->l('Compact')),
		                                array('id' => 'showcase', 'name' => $this->l('Showcase')),
		                                array('id' => 'masonry', 'name' => $this->l('Masonry')),
	                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable grid view'),
                        'name' => self::CONFIG_ENABLE_GRID,
                        'is_bool' => true,
                        'values' => $this->getSwitchValues(),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable list view'),
                        'name' => self::CONFIG_ENABLE_LIST,
                        'is_bool' => true,
                        'values' => $this->getSwitchValues(),
                    ),
	                    array(
	                        'type' => 'switch',
	                        'label' => $this->l('Enable table view'),
	                        'name' => self::CONFIG_ENABLE_TABLE,
	                        'is_bool' => true,
	                        'values' => $this->getSwitchValues(),
	                    ),
	                    array(
	                        'type' => 'switch',
	                        'label' => $this->l('Enable compact view'),
	                        'name' => self::CONFIG_ENABLE_COMPACT,
	                        'is_bool' => true,
	                        'values' => $this->getSwitchValues(),
	                    ),
		                    array(
		                        'type' => 'switch',
		                        'label' => $this->l('Enable showcase view'),
		                        'name' => self::CONFIG_ENABLE_SHOWCASE,
		                        'is_bool' => true,
		                        'values' => $this->getSwitchValues(),
		                    ),
	                    array(
	                        'type' => 'switch',
	                        'label' => $this->l('Enable masonry view'),
	                        'name' => self::CONFIG_ENABLE_MASONRY,
	                        'is_bool' => true,
	                        'values' => $this->getSwitchValues(),
	                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Remember customer view'),
                        'name' => self::CONFIG_REMEMBER_VIEW,
                        'desc' => $this->l('Stores the selected view in the customer browser.'),
                        'is_bool' => true,
                        'values' => $this->getSwitchValues(),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Automatic selector fallback'),
                        'name' => self::CONFIG_AUTO_INJECT,
                        'desc' => $this->l('Adds the selector with JavaScript if the active theme does not render the product list hook.'),
                        'is_bool' => true,
                        'values' => $this->getSwitchValues(),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Grid columns on desktop'),
                        'name' => self::CONFIG_GRID_COLUMNS_DESKTOP,
                        'desc' => $this->l('Allowed range: 2 to 6.'),
                        'class' => 'fixed-width-sm',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Grid columns on tablet'),
                        'name' => self::CONFIG_GRID_COLUMNS_TABLET,
                        'desc' => $this->l('Allowed range: 2 to 4.'),
                        'class' => 'fixed-width-sm',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('List image width'),
                        'name' => self::CONFIG_LIST_IMAGE_WIDTH,
                        'suffix' => 'px',
                        'desc' => $this->l('Allowed range: 80 to 320 pixels.'),
                        'class' => 'fixed-width-sm',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Table image width'),
                        'name' => self::CONFIG_TABLE_IMAGE_WIDTH,
                        'suffix' => 'px',
                        'desc' => $this->l('Allowed range: 60 to 220 pixels.'),
                        'class' => 'fixed-width-sm',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Products per page'),
                        'name' => self::CONFIG_PRODUCTS_PER_PAGE,
                        'desc' => $this->l('Set 0 to keep the theme default. The module applies this with the resultsPerPage URL parameter.'),
                        'class' => 'fixed-width-sm',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Pagination behavior'),
                        'name' => self::CONFIG_PAGINATION_MODE,
                        'desc' => $this->l('Choose normal pagination or infinite scroll. Both use the products per page value above.'),
                        'options' => array(
                            'query' => array(
                                array('id' => 'pagination', 'name' => $this->l('Pagination')),
                                array('id' => 'infinite', 'name' => $this->l('Infinite scroll')),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProductlistConfig';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
        );

        return $helper->generateForm(array($fieldsForm));
    }

    private function getConfigFieldsValues()
    {
        return array(
            self::CONFIG_DEFAULT_VIEW => $this->getDefaultView(),
	            self::CONFIG_ENABLE_GRID => (int) Configuration::get(self::CONFIG_ENABLE_GRID),
	            self::CONFIG_ENABLE_LIST => (int) Configuration::get(self::CONFIG_ENABLE_LIST),
	            self::CONFIG_ENABLE_TABLE => (int) Configuration::get(self::CONFIG_ENABLE_TABLE),
	            self::CONFIG_ENABLE_COMPACT => (int) Configuration::get(self::CONFIG_ENABLE_COMPACT),
	            self::CONFIG_ENABLE_SHOWCASE => (int) Configuration::get(self::CONFIG_ENABLE_SHOWCASE),
	            self::CONFIG_ENABLE_MASONRY => (int) Configuration::get(self::CONFIG_ENABLE_MASONRY),
            self::CONFIG_REMEMBER_VIEW => (int) Configuration::get(self::CONFIG_REMEMBER_VIEW),
            self::CONFIG_AUTO_INJECT => (int) Configuration::get(self::CONFIG_AUTO_INJECT),
            self::CONFIG_GRID_COLUMNS_DESKTOP => (int) Configuration::get(self::CONFIG_GRID_COLUMNS_DESKTOP),
            self::CONFIG_GRID_COLUMNS_TABLET => (int) Configuration::get(self::CONFIG_GRID_COLUMNS_TABLET),
            self::CONFIG_LIST_IMAGE_WIDTH => (int) Configuration::get(self::CONFIG_LIST_IMAGE_WIDTH),
            self::CONFIG_TABLE_IMAGE_WIDTH => (int) Configuration::get(self::CONFIG_TABLE_IMAGE_WIDTH),
            self::CONFIG_PRODUCTS_PER_PAGE => (int) Configuration::get(self::CONFIG_PRODUCTS_PER_PAGE),
            self::CONFIG_INFINITE_SCROLL => (int) Configuration::get(self::CONFIG_INFINITE_SCROLL),
            self::CONFIG_PAGINATION_MODE => $this->getPaginationMode(),
        );
    }

    private function getSwitchValues()
    {
        return array(
            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled')),
        );
    }

    private function renderCardConfigTabs()
    {
        $action = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules');
        $cardConfig = $this->getCardConfig();
        $views = $this->getCardViewLabels();
        $fields = $this->getCardFieldLabels();
        $html = '<div class="panel">';

        $html .= $this->renderCardConfigStyles();
        $html .= '<div class="panel-heading"><i class="icon-columns"></i> ' . $this->escapeHtml($this->l('Card content by view')) . '</div>';
        $html .= '<form method="post" action="' . $this->escapeHtml($action) . '" class="productlist-card-config">';
        $html .= '<div class="productlist-admin-tabs" role="tablist" aria-label="' . $this->escapeHtml($this->l('Card views')) . '">';

        foreach ($views as $view => $label) {
            $active = $view === 'grid' ? ' is-active' : '';
            $selected = $view === 'grid' ? 'true' : 'false';
            $html .= '<button type="button" class="productlist-admin-tab' . $active . '" role="tab" aria-selected="' . $selected . '" data-productlist-admin-tab="' . $view . '">';
            $html .= '<span class="productlist-admin-tab__icon productlist-admin-tab__icon--' . $view . '"></span>';
            $html .= '<span>' . $this->escapeHtml($label) . '</span>';
            $html .= '</button>';
        }

        $html .= '</div>';
        $html .= '<div class="productlist-admin-tabpanels">';

        foreach ($views as $view => $label) {
            $active = $view === 'grid' ? ' is-active' : '';
            $html .= '<section class="productlist-admin-tabpanel' . $active . '" data-productlist-admin-panel="' . $view . '" role="tabpanel">';
            $html .= '<div class="productlist-admin-panel-head">';
            $html .= '<h3>' . sprintf($this->escapeHtml($this->l('%s card')), $this->escapeHtml($label)) . '</h3>';
            $html .= '<p>' . sprintf($this->escapeHtml($this->l('Choose what is visible in the %s card.')), $this->escapeHtml($label)) . '</p>';
            $html .= '</div>';
            $html .= '<div class="productlist-admin-toggle-grid">';

            foreach ($fields as $field => $fieldLabel) {
                $name = $this->getCardFieldConfigKey($view, $field);
                $isChecked = !empty($cardConfig[$view][$field]);
                $checked = $isChecked ? ' checked="checked"' : '';
                $enabledClass = $isChecked ? ' is-enabled' : '';
                $html .= '<label class="productlist-admin-toggle' . $enabledClass . '" for="' . $name . '">';
                $html .= '<input type="hidden" name="' . $name . '" value="0">';
                $html .= '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="1"' . $checked . '>';
                $html .= '<span class="productlist-admin-toggle__switch" aria-hidden="true">';
                $html .= '<span class="productlist-admin-toggle__state productlist-admin-toggle__state--off">OFF</span>';
                $html .= '<span class="productlist-admin-toggle__state productlist-admin-toggle__state--on">ON</span>';
                $html .= '</span>';
                $html .= '<span class="productlist-admin-toggle__body">';
                $html .= '<span class="productlist-admin-toggle__title">' . $this->escapeHtml($fieldLabel) . '</span>';
                $html .= '<span class="productlist-admin-toggle__hint">' . $this->escapeHtml($this->getCardFieldHelp($field)) . '</span>';
                $html .= '</span>';
                $html .= '</label>';
            }

            $html .= '</div>';
            $html .= '</section>';
        }

        $html .= '</div>';
        $html .= '<div class="panel-footer">';
        $html .= '<button type="submit" name="submitProductlistCardConfig" class="btn btn-default pull-right">';
        $html .= '<i class="process-icon-save"></i> ' . $this->escapeHtml($this->l('Save'));
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= $this->renderCardConfigScript();
        $html .= '</div>';

        return $html;
    }

    private function getEnabledViews()
    {
        $views = array();

        if ((int) Configuration::get(self::CONFIG_ENABLE_GRID)) {
            $views[] = array('id' => 'grid', 'label' => $this->l('Grid'));
        }

        if ((int) Configuration::get(self::CONFIG_ENABLE_LIST)) {
            $views[] = array('id' => 'list', 'label' => $this->l('List'));
        }

	        if ((int) Configuration::get(self::CONFIG_ENABLE_TABLE)) {
	            $views[] = array('id' => 'table', 'label' => $this->l('Table'));
	        }

	        if ((int) Configuration::get(self::CONFIG_ENABLE_COMPACT)) {
	            $views[] = array('id' => 'compact', 'label' => $this->l('Compact'));
	        }

	        if ((int) Configuration::get(self::CONFIG_ENABLE_SHOWCASE)) {
	            $views[] = array('id' => 'showcase', 'label' => $this->l('Showcase'));
	        }

	        if ((int) Configuration::get(self::CONFIG_ENABLE_MASONRY)) {
	            $views[] = array('id' => 'masonry', 'label' => $this->l('Masonry'));
	        }

        return $views;
    }

    private function getDefaultView()
    {
        $defaultView = Configuration::get(self::CONFIG_DEFAULT_VIEW);
        $enabledViews = $this->getEnabledViews();
        $enabledIds = array();

        foreach ($enabledViews as $view) {
            $enabledIds[] = $view['id'];
        }

        if (in_array($defaultView, $enabledIds)) {
            return $defaultView;
        }

        return count($enabledIds) ? $enabledIds[0] : 'grid';
    }

    private function shouldLoadAssets()
    {
        $controller = isset($this->context->controller->php_self) ? $this->context->controller->php_self : '';

        return in_array($controller, array('category', 'search', 'manufacturer', 'supplier', 'prices-drop', 'new-products', 'best-sales'));
    }

    private function getLayoutConfig()
    {
        return array(
            'gridColumnsDesktop' => $this->clampInteger(Configuration::get(self::CONFIG_GRID_COLUMNS_DESKTOP), 2, 6, 4),
            'gridColumnsTablet' => $this->clampInteger(Configuration::get(self::CONFIG_GRID_COLUMNS_TABLET), 2, 4, 3),
            'listImageWidth' => $this->clampInteger(Configuration::get(self::CONFIG_LIST_IMAGE_WIDTH), 80, 320, 160),
            'tableImageWidth' => $this->clampInteger(Configuration::get(self::CONFIG_TABLE_IMAGE_WIDTH), 60, 220, 112),
        );
    }

    private function getPaginationMode()
    {
        $mode = Configuration::get(self::CONFIG_PAGINATION_MODE);

        if (in_array($mode, array('pagination', 'infinite'))) {
            return $mode;
        }

        return (int) Configuration::get(self::CONFIG_INFINITE_SCROLL) ? 'infinite' : 'pagination';
    }

    private function getCardConfig()
    {
        $config = array();

        foreach ($this->views as $view) {
            $config[$view] = array();

            foreach (array_keys($this->getCardFieldLabels()) as $field) {
                $config[$view][$field] = (bool) Configuration::get($this->getCardFieldConfigKey($view, $field));
            }
        }

        return $config;
    }

    private function saveCardConfig()
    {
        foreach ($this->views as $view) {
            foreach (array_keys($this->getCardFieldLabels()) as $field) {
                Configuration::updateValue(
                    $this->getCardFieldConfigKey($view, $field),
                    (int) Tools::getValue($this->getCardFieldConfigKey($view, $field))
                );
            }
        }
    }

    private function installCardConfig()
    {
        $result = true;

        foreach ($this->views as $view) {
            foreach (array_keys($this->getCardFieldLabels()) as $field) {
                $result = $result && Configuration::updateValue($this->getCardFieldConfigKey($view, $field), 1);
            }
        }

        return $result;
    }

    private function uninstallCardConfig()
    {
        $result = true;

        foreach ($this->views as $view) {
            foreach (array_keys($this->getCardFieldLabels()) as $field) {
                $result = $result && Configuration::deleteByName($this->getCardFieldConfigKey($view, $field));
            }
        }

        return $result;
    }

    private function getCardFieldConfigKey($view, $field)
    {
        return 'PRODUCTLIST_CARD_' . strtoupper($view) . '_' . strtoupper($field);
    }

    private function getCardViewLabels()
    {
	        return array(
	            'grid' => $this->l('Grid'),
	            'list' => $this->l('List'),
	            'table' => $this->l('Table'),
	            'compact' => $this->l('Compact'),
	            'showcase' => $this->l('Showcase'),
	            'masonry' => $this->l('Masonry'),
	        );
    }

    private function getCardFieldLabels()
    {
        return array(
            'image' => $this->l('Image'),
            'title' => $this->l('Title'),
            'description' => $this->l('Description'),
            'reference' => $this->l('Reference'),
            'brand' => $this->l('Brand'),
            'availability' => $this->l('Availability'),
            'flags' => $this->l('Flags'),
            'quickview' => $this->l('Quick view'),
            'colors' => $this->l('Colors / combinations'),
            'quantity' => $this->l('Quantity'),
            'price' => $this->l('Price'),
            'actions' => $this->l('Actions'),
        );
    }

    private function getCardFieldHelp($field)
    {
        $help = array(
            'image' => $this->l('Product image block.'),
            'title' => $this->l('Clickable product name.'),
            'description' => $this->l('Short description when available.'),
            'reference' => $this->l('SKU or product reference.'),
            'brand' => $this->l('Manufacturer or brand when available.'),
            'availability' => $this->l('Stock or delivery message.'),
            'flags' => $this->l('Product flags such as new, discount or pack.'),
            'quickview' => $this->l('Quick view link or button.'),
            'colors' => $this->l('Color swatches or variant links.'),
            'quantity' => $this->l('Quantity selector with plus and minus buttons.'),
            'price' => $this->l('Price block from the theme.'),
            'actions' => $this->l('Add-to-cart button or quick actions from the theme.'),
        );

        return isset($help[$field]) ? $help[$field] : '';
    }

    private function renderCardConfigStyles()
    {
        return '<style>
            .productlist-card-config { margin: -15px; }
            .productlist-admin-tabs {
                display: flex;
                gap: 8px;
                padding: 18px 18px 0;
                border-bottom: 1px solid #d8dde3;
                background: linear-gradient(180deg, #f8fafc 0%, #eef3f7 100%);
            }
            .productlist-admin-tab {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-height: 44px;
                padding: 0 18px;
                border: 1px solid #d8dde3;
                border-bottom: 0;
                border-radius: 8px 8px 0 0;
                background: #e8edf3;
                color: #4b5563;
                font-weight: 700;
                cursor: pointer;
                transition: background 160ms ease, color 160ms ease, transform 160ms ease;
            }
            .productlist-admin-tab:hover {
                background: #f7f9fb;
                color: #111827;
            }
            .productlist-admin-tab.is-active {
                position: relative;
                bottom: -1px;
                background: #fff;
                color: #111827;
                box-shadow: 0 -1px 0 #fff, 0 -4px 10px rgba(31, 41, 55, .06);
            }
            .productlist-admin-tab__icon {
                display: inline-block;
                width: 14px;
                height: 14px;
                color: currentColor;
            }
            .productlist-admin-tab__icon--grid {
                background:
                    linear-gradient(currentColor 0 0) 0 0 / 6px 6px,
                    linear-gradient(currentColor 0 0) 100% 0 / 6px 6px,
                    linear-gradient(currentColor 0 0) 0 100% / 6px 6px,
                    linear-gradient(currentColor 0 0) 100% 100% / 6px 6px;
                background-repeat: no-repeat;
            }
	            .productlist-admin-tab__icon--list,
	            .productlist-admin-tab__icon--table,
	            .productlist-admin-tab__icon--compact {
	                background:
	                    linear-gradient(currentColor 0 0) 0 2px / 14px 2px,
	                    linear-gradient(currentColor 0 0) 0 6px / 14px 2px,
	                    linear-gradient(currentColor 0 0) 0 10px / 14px 2px;
	                background-repeat: no-repeat;
	            }
	            .productlist-admin-tab__icon--showcase {
	                border: 2px solid currentColor;
	                border-radius: 2px;
	            }
	            .productlist-admin-tab__icon--masonry {
	                background:
	                    linear-gradient(currentColor 0 0) 0 0 / 5px 12px,
	                    linear-gradient(currentColor 0 0) 7px 0 / 5px 7px,
	                    linear-gradient(currentColor 0 0) 7px 9px / 5px 5px;
	                background-repeat: no-repeat;
	            }
            .productlist-admin-tabpanels { padding: 20px 18px 10px; background: #fff; }
            .productlist-admin-tabpanel { display: none; }
            .productlist-admin-tabpanel.is-active { display: block; }
            .productlist-admin-panel-head {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 14px;
            }
            .productlist-admin-panel-head h3 {
                margin: 0;
                font-size: 16px;
                font-weight: 700;
            }
            .productlist-admin-panel-head p {
                margin: 0;
                color: #6b7280;
            }
            .productlist-admin-toggle-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 14px;
            }
            .productlist-admin-toggle {
                display: flex;
                align-items: center;
                gap: 14px;
                min-height: 76px;
                margin: 0;
                padding: 14px;
                border: 1px solid #dbe3ea;
                border-radius: 8px;
                background: #f9fafb;
                cursor: pointer;
                box-shadow: 0 1px 2px rgba(31, 41, 55, .04);
                transition: border-color 160ms ease, background 160ms ease, box-shadow 160ms ease, transform 160ms ease;
            }
            .productlist-admin-toggle:hover {
                border-color: #b8c2cc;
                background: #fff;
                box-shadow: 0 6px 16px rgba(31, 41, 55, .07);
                transform: translateY(-1px);
            }
            .productlist-admin-toggle input[type="checkbox"] {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }
            .productlist-admin-toggle__switch {
                position: relative;
                flex: 0 0 86px;
                width: 86px;
                height: 34px;
                border-radius: 999px;
                background: #dc2626;
                box-shadow: inset 0 0 0 1px rgba(0,0,0,.08);
                transition: background 160ms ease, box-shadow 160ms ease;
            }
            .productlist-admin-toggle__switch:after {
                position: absolute;
                top: 4px;
                left: 4px;
                width: 39px;
                height: 26px;
                border-radius: 999px;
                background: #fff;
                box-shadow: 0 2px 6px rgba(0,0,0,.24);
                content: "";
                transition: transform 160ms ease, box-shadow 160ms ease;
                z-index: 2;
            }
            .productlist-admin-toggle__state {
                position: absolute;
                top: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                width: 39px;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .02em;
                line-height: 1;
                z-index: 3;
                pointer-events: none;
            }
            .productlist-admin-toggle__state--off {
                right: 4px;
                color: #dc2626;
            }
            .productlist-admin-toggle__state--on {
                left: 4px;
                color: rgba(255,255,255,.72);
            }
            .productlist-admin-toggle.is-enabled {
                border-color: #9bd8e5;
                background: #f0fbfd;
            }
            .productlist-admin-toggle.is-enabled .productlist-admin-toggle__switch {
                background: #16a34a;
                box-shadow: inset 0 0 0 1px rgba(0,0,0,.06), 0 0 0 3px rgba(22, 163, 74, .12);
            }
            .productlist-admin-toggle.is-enabled .productlist-admin-toggle__switch:after {
                transform: translateX(39px);
            }
            .productlist-admin-toggle.is-enabled .productlist-admin-toggle__state--off {
                color: rgba(255,255,255,.72);
            }
            .productlist-admin-toggle.is-enabled .productlist-admin-toggle__state--on {
                color: #16a34a;
            }
            .productlist-admin-toggle__body { display: grid; gap: 2px; min-width: 0; }
            .productlist-admin-toggle__title { color: #1f2937; font-weight: 700; }
            .productlist-admin-toggle__hint { color: #6b7280; font-size: 12px; line-height: 1.35; }
            @media (max-width: 767px) {
                .productlist-admin-tabs { flex-wrap: wrap; }
                .productlist-admin-tab { flex: 1 1 auto; justify-content: center; }
                .productlist-admin-panel-head { display: block; }
                .productlist-admin-panel-head p { margin-top: 6px; }
            }
        </style>';
    }

    private function renderCardConfigScript()
    {
        return '<script>
            (function () {
                var root = document.currentScript ? document.currentScript.closest(".panel") : document;
                var tabs = root.querySelectorAll("[data-productlist-admin-tab]");
                var panels = root.querySelectorAll("[data-productlist-admin-panel]");
                var toggles = root.querySelectorAll(".productlist-admin-toggle input[type=\"checkbox\"]");

                Array.prototype.forEach.call(tabs, function (tab) {
                    tab.addEventListener("click", function () {
                        var view = tab.getAttribute("data-productlist-admin-tab");

                        Array.prototype.forEach.call(tabs, function (item) {
                            var active = item === tab;
                            item.classList.toggle("is-active", active);
                            item.setAttribute("aria-selected", active ? "true" : "false");
                        });

                        Array.prototype.forEach.call(panels, function (panel) {
                            panel.classList.toggle("is-active", panel.getAttribute("data-productlist-admin-panel") === view);
                        });
                    });
                });

                Array.prototype.forEach.call(toggles, function (toggle) {
                    toggle.addEventListener("change", function () {
                        var label = toggle.closest(".productlist-admin-toggle");

                        if (label) {
                            label.classList.toggle("is-enabled", toggle.checked);
                        }
                    });
                });
            }());
        </script>';
    }

    private function escapeHtml($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function buildProductPayload($product)
    {
        return array(
            'id' => (int) $this->getProductValue($product, array('id_product', 'id')),
            'name' => (string) $this->getProductValue($product, array('name')),
            'url' => (string) $this->getProductValue($product, array('url', 'link')),
            'descriptionShort' => (string) $this->getProductValue($product, array('description_short', 'descriptionShort')),
            'reference' => (string) $this->getProductValue($product, array('reference')),
            'brand' => (string) $this->getProductValue($product, array('manufacturer_name', 'manufacturerName', 'brand_name', 'brand')),
            'availability' => (string) $this->getProductValue($product, array('availability_message', 'availability', 'stock_availability')),
            'condition' => (string) $this->getProductValue($product, array('condition')),
            'flags' => $this->normalizeProductFlags($this->getProductValue($product, array('flags'))),
        );
    }

    private function getProductValue($product, array $keys)
    {
        foreach ($keys as $key) {
            if (is_array($product) && isset($product[$key])) {
                return $product[$key];
            }

            if ($product instanceof ArrayAccess && isset($product[$key])) {
                return $product[$key];
            }

            if (is_object($product) && isset($product->{$key})) {
                return $product->{$key};
            }
        }

        return '';
    }

    private function normalizeProductFlags($flags)
    {
        $normalized = array();

        if (!is_array($flags)) {
            return $normalized;
        }

        foreach ($flags as $flag) {
            if (is_array($flag)) {
                $label = isset($flag['label']) ? $flag['label'] : (isset($flag['type']) ? $flag['type'] : '');
            } elseif ($flag instanceof ArrayAccess) {
                $label = isset($flag['label']) ? $flag['label'] : (isset($flag['type']) ? $flag['type'] : '');
            } else {
                $label = (string) $flag;
            }

            $label = trim((string) $label);

            if ($label !== '') {
                $normalized[] = $label;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function clampInteger($value, $min, $max, $fallback)
    {
        $value = (int) $value;

        if ($value < $min || $value > $max) {
            return $fallback;
        }

        return $value;
    }

    private function registerOptionalHook($hookName)
    {
        $this->registerHook($hookName);

        return true;
    }
}
