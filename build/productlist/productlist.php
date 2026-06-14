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
    const CONFIG_IMAGE_MODE = 'PRODUCTLIST_IMAGE_MODE';

    private $views = array('grid', 'list', 'table', 'compact', 'showcase', 'masonry');

    public function __construct()
    {
        $this->name = 'productlist';
        $this->tab = 'front_office_features';
        $this->version = '0.12.2';
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
            && Configuration::updateValue(self::CONFIG_IMAGE_MODE, 'none')
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
            && Configuration::deleteByName(self::CONFIG_IMAGE_MODE)
            && $this->uninstallCardConfig()
            && parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        // Admin styles/scripts as external assets (no inline JS/CSS) so the
        // configuration screen passes the PrestaShop validator.
        $this->context->controller->addCSS($this->_path . 'views/css/productlist-admin.css');
        $this->context->controller->addJS($this->_path . 'views/js/productlist-admin.js');

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
            $imageMode = Tools::getValue(self::CONFIG_IMAGE_MODE, 'none');

            if (!in_array($paginationMode, array('pagination', 'infinite'))) {
                $paginationMode = 'pagination';
            }

            if (!in_array($imageMode, array('none', 'hover', 'carousel'))) {
                $imageMode = 'none';
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
                Configuration::updateValue(self::CONFIG_IMAGE_MODE, $imageMode);

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
            'productlistImageMode' => $this->getImageMode(),
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
                'viewProduct' => $this->l('View product'),
                'addingToCart' => $this->l('Adding...'),
                'addedToCart' => $this->l('Added'),
                'addToCartError' => $this->l('Could not add to cart'),
                'loadingMore' => $this->l('Loading more products...'),
                'noMoreProducts' => $this->l('No more products'),
                'loadMoreError' => $this->l('Could not load more products'),
                'loadedProducts' => $this->l('Loaded products'),
                'previousImage' => $this->l('Previous image'),
                'nextImage' => $this->l('Next image'),
                'productImages' => $this->l('Product images'),
                'goToImage' => $this->l('Go to image'),
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
                    array(
                        'type' => 'select',
                        'label' => $this->l('Product image behavior'),
                        'name' => self::CONFIG_IMAGE_MODE,
                        'desc' => $this->l('Show the second image on hover or a carousel with all product images. Needs more than one image per product.'),
                        'options' => array(
                            'query' => array(
                                array('id' => 'none', 'name' => $this->l('Single image')),
                                array('id' => 'hover', 'name' => $this->l('Second image on hover')),
                                array('id' => 'carousel', 'name' => $this->l('Carousel of all images')),
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
            self::CONFIG_IMAGE_MODE => $this->getImageMode(),
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

    private function escapeHtml($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function buildProductPayload($product)
    {
        $imageMode = $this->getImageMode();

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
            // Only resolve the full image set when a multi-image mode is active,
            // to avoid an extra query per product when the feature is off.
            'images' => $imageMode === 'none' ? array() : $this->buildProductImages($product),
            // Used by the JS quick add-to-cart to stay correct: products with
            // combinations or customization are sent to the product page so the
            // customer can pick options instead of adding a wrong variant.
            'idProductAttribute' => (int) $this->getProductValue($product, array('id_product_attribute')),
            'customizable' => (bool) $this->getProductValue($product, array('customizable', 'customization_required')),
            'availableForOrder' => $this->isAvailableForOrder($product),
        );
    }

    private function isAvailableForOrder($product)
    {
        $value = $this->getProductValue($product, array('available_for_order'));

        // Default to true when the listing data does not expose the flag, so we
        // never hide a buy button for a product that is actually orderable.
        if ($value === '' || $value === null) {
            return true;
        }

        return (bool) $value;
    }

    private function getImageMode()
    {
        $mode = Configuration::get(self::CONFIG_IMAGE_MODE);

        if (in_array($mode, array('none', 'hover', 'carousel'), true)) {
            return $mode;
        }

        return 'none';
    }

    private function buildProductImages($product)
    {
        $idProduct = (int) $this->getProductValue($product, array('id_product', 'id'));

        if ($idProduct <= 0) {
            return array();
        }

        $linkRewrite = (string) $this->getProductValue($product, array('link_rewrite'));

        if ($linkRewrite === '') {
            // getImageLink only uses the rewrite for the SEO filename, not the
            // lookup, so a placeholder still resolves to the right image.
            $linkRewrite = 'product';
        }

        $idLang = (int) $this->context->language->id;
        $imageType = $this->resolveImageType();
        $entries = array();

        try {
            $images = Image::getImages($idLang, $idProduct);
        } catch (Exception $e) {
            return array();
        }

        if (!is_array($images)) {
            return array();
        }

        foreach ($images as $image) {
            if (!isset($image['id_image'])) {
                continue;
            }

            $url = $this->context->link->getImageLink($linkRewrite, (int) $image['id_image'], $imageType);

            if (!$url) {
                continue;
            }

            $entries[] = array(
                'src' => (string) $url,
                'alt' => isset($image['legend']) ? (string) $image['legend'] : '',
            );
        }

        return $entries;
    }

    private function resolveImageType()
    {
        if (method_exists('ImageType', 'getFormattedName')) {
            $name = ImageType::getFormattedName('home');

            if (!empty($name)) {
                return $name;
            }
        }

        return 'home_default';
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
