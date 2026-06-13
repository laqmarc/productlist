(function () {
  'use strict';

  var storageKey = 'productlistSelectedView';
  var viewClasses = [
    'productlist-view-grid',
    'productlist-view-list',
    'productlist-view-table',
    'productlist-view-compact',
    'productlist-view-showcase',
    'productlist-view-masonry'
  ];

  function getLabel(key, fallback) {
    var labels = window.productlistLabels || {};

    return labels[key] || fallback;
  }

  function isCardPartVisible(view, part) {
    var config = window.productlistCardConfig || {};

    if (!config[view] || typeof config[view][part] === 'undefined') {
      return true;
    }

    return config[view][part] === true || config[view][part] === 1 || config[view][part] === '1';
  }

  function getCardStateClass(view) {
    var parts = ['image', 'title', 'description', 'reference', 'brand', 'availability', 'flags', 'quickview', 'colors', 'quantity', 'price', 'actions'];
    var classes = [];

    parts.forEach(function (part) {
      if (!isCardPartVisible(view, part)) {
        classes.push('productlist-card--no-' + part);
      }
    });

    return classes.join(' ');
  }

  function shouldShowActionArea(view) {
    return isCardPartVisible(view, 'actions') ||
      isCardPartVisible(view, 'quickview') ||
      isCardPartVisible(view, 'colors');
  }

  function applyLayoutConfig() {
    var layout = window.productlistLayout || {};
    var root = document.documentElement;

    if (layout.gridColumnsDesktop) {
      root.style.setProperty('--productlist-grid-columns-desktop', layout.gridColumnsDesktop);
    }

    if (layout.gridColumnsTablet) {
      root.style.setProperty('--productlist-grid-columns-tablet', layout.gridColumnsTablet);
    }

    if (layout.listImageWidth) {
      root.style.setProperty('--productlist-list-image-width', layout.listImageWidth + 'px');
    }

    if (layout.tableImageWidth) {
      root.style.setProperty('--productlist-table-image-width', layout.tableImageWidth + 'px');
    }
  }

  function canUseStorage() {
    try {
      return !!(window.localStorage && window.productlistRememberView !== false);
    } catch (error) {
      return false;
    }
  }

  function readStoredView() {
    if (!canUseStorage()) {
      return null;
    }

    return window.localStorage.getItem(storageKey);
  }

  function readUrlView() {
    try {
      return new window.URLSearchParams(window.location.search).get('pl_view');
    } catch (error) {
      return null;
    }
  }

  function storeView(view) {
    if (!canUseStorage()) {
      return;
    }

    window.localStorage.setItem(storageKey, view);
  }

  function applyProductsPerPageConfig() {
    var productsPerPage = parseInt(window.productlistProductsPerPage, 10);
    var url;

    if (!productsPerPage || productsPerPage < 1) {
      return;
    }

    try {
      url = new URL(window.location.href);
    } catch (error) {
      return;
    }

    if (url.searchParams.get('resultsPerPage') === String(productsPerPage)) {
      return;
    }

    url.searchParams.set('resultsPerPage', productsPerPage);
    url.searchParams.delete('page');
    window.location.replace(url.toString());
  }

  function normalizeListingUrl(urlValue) {
    var productsPerPage = parseInt(window.productlistProductsPerPage, 10);
    var url;

    try {
      url = new URL(urlValue, window.location.href);
    } catch (error) {
      return urlValue;
    }

    if (productsPerPage && productsPerPage > 0) {
      url.searchParams.set('resultsPerPage', productsPerPage);
    }

    return url.toString();
  }

  function createSwitcher() {
    var views = window.productlistViews || [];
    var listing = document.querySelector('#js-product-list') || document.querySelector('#products');
    var target = document.querySelector('#js-product-list-top') || listing;

    if (
      window.productlistAutoInject === false ||
      !target ||
      views.length < 2 ||
      document.querySelector('[data-productlist-default]')
    ) {
      return null;
    }

    var switcher = document.createElement('div');
    switcher.className = 'productlist-view-switcher';
    switcher.setAttribute('data-productlist-default', window.productlistDefaultView || 'grid');

    var label = document.createElement('span');
    label.className = 'productlist-view-switcher__label';
    label.textContent = getLabel('view', 'View');
    switcher.appendChild(label);

    var group = document.createElement('div');
    group.className = 'productlist-view-switcher__buttons';
    group.setAttribute('role', 'group');
    group.setAttribute('aria-label', getLabel('productView', 'Product view'));

    views.forEach(function (view) {
      var button = document.createElement('button');
      var icon = document.createElement('span');
      var text = document.createElement('span');

      button.type = 'button';
      button.className = 'productlist-view-switcher__button';
      button.setAttribute('data-productlist-view', view.id);
      button.setAttribute('aria-pressed', 'false');
      icon.className = 'productlist-view-switcher__icon';
      icon.setAttribute('aria-hidden', 'true');
      text.className = 'productlist-view-switcher__text';
      text.textContent = view.label;
      button.appendChild(icon);
      button.appendChild(text);
      group.appendChild(button);
    });

    switcher.appendChild(group);
    target.parentNode.insertBefore(switcher, target);

    return switcher;
  }

  function getListingRoot() {
    return document.querySelector('#js-product-list') || document.querySelector('#products') || document.body;
  }

  function getProductsContainer() {
    return getListingRoot().querySelector('.products');
  }

  function ensureTableHeader(view) {
    var products = getProductsContainer();
    var existingHeader = document.querySelector('.productlist-table-head');
    var labels = [];
    var header;

    if (view !== 'table') {
      if (existingHeader) {
        existingHeader.parentNode.removeChild(existingHeader);
      }

      return;
    }

    if (!products || existingHeader) {
      return;
    }

    if (isCardPartVisible('table', 'image')) {
      labels.push(getLabel('image', 'Image'));
    }

    if (isCardPartVisible('table', 'title')) {
      labels.push(getLabel('product', 'Product'));
    }

    if (isCardPartVisible('table', 'price')) {
      labels.push(getLabel('price', 'Price'));
    }

    if (isCardPartVisible('table', 'actions')) {
      labels.push(getLabel('action', 'Action'));
    }

    header = document.createElement('div');
    header.className = 'productlist-table-head ' + getCardStateClass('table');

    labels.forEach(function (label) {
      var column = document.createElement('span');
      column.textContent = label;
      header.appendChild(column);
    });

    products.parentNode.insertBefore(header, products);
  }

  function annotateTableColumns() {
    var products = getProductsContainer();
    var rows;

    if (!products) {
      return;
    }

    rows = products.querySelectorAll('.product-miniature');

    Array.prototype.forEach.call(rows, function (row) {
      var title = row.querySelector('.product-title');
      var price = row.querySelector('.product-price-and-shipping, .price');
      var action = row.querySelector('.product-actions, .highlighted-informations, .quick-view');

      if (title) {
        title.setAttribute('data-productlist-column', getLabel('product', 'Product'));
      }

      if (price) {
        price.setAttribute('data-productlist-column', getLabel('price', 'Price'));
      }

      if (action) {
        action.setAttribute('data-productlist-column', getLabel('action', 'Action'));
      }
    });
  }

  function removeDuplicateIds(element) {
    var nodes = element.querySelectorAll('[id]');

    if (element.id) {
      element.removeAttribute('id');
    }

    Array.prototype.forEach.call(nodes, function (node) {
      node.removeAttribute('id');
    });
  }

  function createHtmlElement(tagName, className, html) {
    var element = document.createElement(tagName);

    element.className = className;
    element.innerHTML = html;

    return element;
  }

  function createLinkedImage(data) {
    var link = document.createElement('a');
    var image = data.image ? data.image.cloneNode(true) : null;

    link.className = 'productlist-card__image-link';
    link.href = data.url || '#';
    link.setAttribute('aria-label', data.title);

    if (image) {
      image.removeAttribute('width');
      image.removeAttribute('height');
      link.appendChild(image);
    }

    return link;
  }

  function createTitle(data) {
    var title = document.createElement('a');

    title.className = 'productlist-card__title';
    title.href = data.url || '#';
    title.textContent = data.title;

    return title;
  }

  function createPrice(data) {
    var price = document.createElement('div');

    price.className = 'productlist-card__price';

    if (data.priceHtml) {
      price.innerHTML = data.priceHtml;
    }

    return price;
  }

  function syncActionQuantity(actions, quantity) {
    var form = actions.matches && actions.matches('form') ? actions : actions.querySelector('form') || actions.closest('form');
    var quantityInput = actions.querySelector('input[name="qty"], input[name="quantity_wanted"]');

    if (!quantityInput && form) {
      quantityInput = document.createElement('input');
      quantityInput.type = 'hidden';
      quantityInput.name = 'qty';
      form.appendChild(quantityInput);
    }

    if (quantityInput) {
      quantityInput.value = quantity;
    }
  }

  function hasAddToCartAction(actions) {
    if (!actions) {
      return false;
    }

    return !!actions.querySelector(
      'form[action*="cart"], [data-button-action="add-to-cart"], button.add-to-cart, .add-to-cart'
    );
  }

  function stripAuxiliaryActions(actions) {
    var selectors = [
      '.quick-view',
      '[data-link-action="quickview"]',
      '[data-button-action="quickview"]',
      '.variant-links',
      '.color_to_pick_list',
      '.product-variants',
      '.variant-links-wrapper'
    ];

    if (!actions) {
      return;
    }

    selectors.forEach(function (selector) {
      Array.prototype.forEach.call(actions.querySelectorAll(selector), function (element) {
        if (element.parentNode) {
          element.parentNode.removeChild(element);
        }
      });
    });
  }

  function getCartUrl() {
    if (window.prestashop && window.prestashop.urls && window.prestashop.urls.pages) {
      return window.prestashop.urls.pages.cart || '';
    }

    return '';
  }

  function getCartToken() {
    if (window.prestashop && window.prestashop.static_token) {
      return window.prestashop.static_token;
    }

    return '';
  }

  function createFallbackAddToCartButton(data, quantityInput) {
    var button = document.createElement('button');
    var cartUrl = getCartUrl();

    button.type = 'button';
    button.className = 'productlist-card__add-to-cart';
    button.textContent = getLabel('addToCart', 'Add to cart');

    if (!data.id || !cartUrl) {
      button.disabled = true;
      return button;
    }

    button.addEventListener('click', function () {
      var quantity = parseInt(quantityInput ? quantityInput.value : 1, 10) || 1;
      var params = new URLSearchParams();

      button.disabled = true;
      button.textContent = getLabel('addingToCart', 'Adding...');
      params.set('ajax', '1');
      params.set('action', 'update');
      params.set('add', '1');
      params.set('id_product', data.id);
      params.set('qty', quantity);

      if (getCartToken()) {
        params.set('token', getCartToken());
      }

      window.fetch(cartUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: params.toString()
      }).then(function (response) {
        return response.json();
      }).then(function (response) {
        button.textContent = getLabel('addedToCart', 'Added');

        if (window.prestashop && window.prestashop.emit) {
          window.prestashop.emit('updateCart', {
            reason: {
              idProduct: data.id,
              idProductAttribute: 0,
              linkAction: 'add-to-cart'
            },
            resp: response
          });
        }

        window.setTimeout(function () {
          button.disabled = false;
          button.textContent = getLabel('addToCart', 'Add to cart');
        }, 1200);
      }).catch(function () {
        button.disabled = false;
        button.textContent = getLabel('addToCartError', 'Could not add to cart');

        window.setTimeout(function () {
          button.textContent = getLabel('addToCart', 'Add to cart');
        }, 1600);
      });
    });

    return button;
  }

  function createQuantityControl(actions) {
    var control = document.createElement('div');
    var minus = document.createElement('button');
    var input = document.createElement('input');
    var plus = document.createElement('button');

    control.className = 'productlist-card__quantity';
    minus.type = 'button';
    minus.className = 'productlist-card__quantity-button';
    minus.setAttribute('aria-label', '-');
    minus.textContent = '-';
    input.type = 'number';
    input.className = 'productlist-card__quantity-input';
    input.min = '1';
    input.step = '1';
    input.value = '1';
    plus.type = 'button';
    plus.className = 'productlist-card__quantity-button';
    plus.setAttribute('aria-label', '+');
    plus.textContent = '+';

    function setQuantity(value) {
      var quantity = parseInt(value, 10);

      if (!quantity || quantity < 1) {
        quantity = 1;
      }

      input.value = quantity;
      syncActionQuantity(actions, quantity);
    }

    minus.addEventListener('click', function () {
      setQuantity(parseInt(input.value, 10) - 1);
    });

    plus.addEventListener('click', function () {
      setQuantity(parseInt(input.value, 10) + 1);
    });

    input.addEventListener('change', function () {
      setQuantity(input.value);
    });

    control.appendChild(minus);
    control.appendChild(input);
    control.appendChild(plus);
    syncActionQuantity(actions, 1);

    return control;
  }

  function cloneActions(data, view) {
    var actions = document.createElement('div');
    var clonedActions;
    var clonedQuickView;
    var clonedColors;
    var quantityControl = null;
    var quantityInput = null;

    actions.className = 'productlist-card__actions';

    if (!data.actions && !data.quickView && !data.colors) {
      if (isCardPartVisible(view, 'actions') && isCardPartVisible(view, 'quantity')) {
        quantityControl = createQuantityControl(actions);
        quantityInput = quantityControl.querySelector('.productlist-card__quantity-input');
        actions.appendChild(quantityControl);
      }

      if (isCardPartVisible(view, 'actions')) {
        actions.appendChild(createFallbackAddToCartButton(data, quantityInput));
      }

      return actions;
    }

    if (data.actions) {
      clonedActions = data.actions.cloneNode(true);
      removeDuplicateIds(clonedActions);
      stripAuxiliaryActions(clonedActions);
      clonedActions.classList.add('productlist-card__actions-source');
    }

    if (isCardPartVisible(view, 'actions') && isCardPartVisible(view, 'quantity')) {
      quantityControl = createQuantityControl(clonedActions || actions);
      quantityInput = quantityControl.querySelector('.productlist-card__quantity-input');
      actions.appendChild(quantityControl);
    }

    if (isCardPartVisible(view, 'actions') && !hasAddToCartAction(clonedActions)) {
      actions.appendChild(createFallbackAddToCartButton(data, quantityInput));
    }

    if (clonedActions && isCardPartVisible(view, 'actions')) {
      actions.appendChild(clonedActions);
    }

    if (data.quickView && isCardPartVisible(view, 'quickview')) {
      clonedQuickView = data.quickView.cloneNode(true);
      removeDuplicateIds(clonedQuickView);
      clonedQuickView.classList.add('productlist-card__quickview');
      actions.appendChild(clonedQuickView);
    }

    if (data.colors && isCardPartVisible(view, 'colors')) {
      clonedColors = data.colors.cloneNode(true);
      removeDuplicateIds(clonedColors);
      clonedColors.classList.add('productlist-card__colors');
      actions.appendChild(clonedColors);
    }

    return actions;
  }

  function parseEmbeddedProductData(original) {
    var script = original ? original.querySelector('.productlist-product-data') : null;

    if (!script) {
      return {};
    }

    try {
      return JSON.parse(script.textContent || '{}');
    } catch (error) {
      return {};
    }
  }

  function createMetaItem(label, value) {
    var item = document.createElement('span');
    var labelNode = document.createElement('span');
    var valueNode = document.createElement('span');

    item.className = 'productlist-card__meta-item';
    labelNode.className = 'productlist-card__meta-label';
    valueNode.className = 'productlist-card__meta-value';
    labelNode.textContent = label;
    valueNode.textContent = value;
    item.appendChild(labelNode);
    item.appendChild(valueNode);

    return item;
  }

  function appendProductMeta(container, view, data) {
    var meta = document.createElement('div');
    var hasMeta = false;

    meta.className = 'productlist-card__meta';

    if (isCardPartVisible(view, 'reference') && data.reference) {
      meta.appendChild(createMetaItem(getLabel('reference', 'Reference'), data.reference));
      hasMeta = true;
    }

    if (isCardPartVisible(view, 'brand') && data.brand) {
      meta.appendChild(createMetaItem(getLabel('brand', 'Brand'), data.brand));
      hasMeta = true;
    }

    if (isCardPartVisible(view, 'availability') && data.availability) {
      meta.appendChild(createMetaItem(getLabel('availability', 'Availability'), data.availability));
      hasMeta = true;
    }

    if (hasMeta) {
      container.appendChild(meta);
    }
  }

  function appendProductFlags(container, view, data) {
    var flags = document.createElement('div');

    if (!isCardPartVisible(view, 'flags') || !data.flags || !data.flags.length) {
      return;
    }

    flags.className = 'productlist-card__flags';

    data.flags.forEach(function (flag) {
      var item = document.createElement('span');
      item.className = 'productlist-card__flag';
      item.textContent = flag;
      flags.appendChild(item);
    });

    container.appendChild(flags);
  }

  function getDatasetDescription(product, original) {
    var sources = [product, original];
    var keys = ['descriptionShort', 'description', 'productDescriptionShort', 'productDescription'];
    var value = '';

    sources.forEach(function (source) {
      if (!source || value) {
        return;
      }

      keys.forEach(function (key) {
        if (!value && source.dataset && source.dataset[key]) {
          value = source.dataset[key];
        }
      });
    });

    return value;
  }

  function getDescriptionHtml(product, original) {
    var descriptionSelectors = [
      '.product-description-short',
      '.description-short',
      '.product-desc',
      '.product-description-short-text',
      '.product-miniature-description',
      '.product-list-description',
      '[itemprop="description"]',
      '[data-product-description-short]',
      '[data-description-short]'
    ];
    var datasetDescription = getDatasetDescription(product, original);
    var description = null;

    if (datasetDescription) {
      return datasetDescription;
    }

    descriptionSelectors.some(function (selector) {
      description = original.querySelector(selector);

      return !!description;
    });

    if (!description) {
      return '';
    }

    return description.innerHTML || description.textContent || '';
  }

  function getProductData(product) {
    var original = product.querySelector('.product-miniature');
    var embedded = parseEmbeddedProductData(original);
    var image = original ? original.querySelector('.thumbnail-container img, .product-thumbnail img, img') : null;
    var imageLink = image ? image.closest('a') : null;
    var titleLink = original ? original.querySelector('.product-title a, h2 a, h3 a, a.product-name') : null;
    var titleNode = titleLink || (original ? original.querySelector('.product-title, h2, h3') : null);
    var price = original ? original.querySelector('.product-price-and-shipping, .price') : null;
    var descriptionHtml = embedded.descriptionShort || (original ? getDescriptionHtml(product, original) : '');
    var actions = original ? original.querySelector('.product-actions, form[action*="cart"], [data-button-action="add-to-cart"], button.add-to-cart, .add-to-cart') : null;
    var quickView = original ? original.querySelector('.quick-view, [data-link-action="quickview"], [data-button-action="quickview"]') : null;
    var colors = original ? original.querySelector('.variant-links, .color_to_pick_list, .product-variants, .variant-links-wrapper') : null;
    var title = titleNode ? titleNode.textContent.replace(/\s+/g, ' ').trim() : '';
    var url = titleLink ? titleLink.href : imageLink ? imageLink.href : '';

    if (!original || !title) {
      return null;
    }

    return {
      actions: actions,
      availability: embedded.availability || '',
      brand: embedded.brand || '',
      colors: colors,
      descriptionHtml: descriptionHtml.replace(/\s+/g, ' ').trim(),
      flags: embedded.flags || [],
      id: embedded.id || '',
      image: image,
      priceHtml: price ? price.innerHTML : '',
      quickView: quickView,
      reference: embedded.reference || '',
      title: title,
      url: url
    };
  }

  function buildGridCard(data) {
    var card = document.createElement('article');
    var imageArea = document.createElement('div');
    var body = document.createElement('div');

    card.className = 'productlist-enhanced-card productlist-enhanced-card--grid ' + getCardStateClass('grid');
    imageArea.className = 'productlist-card__image';
    body.className = 'productlist-card__body';

    if (isCardPartVisible('grid', 'image')) {
      imageArea.appendChild(createLinkedImage(data));
      card.appendChild(imageArea);
    }

    if (isCardPartVisible('grid', 'title')) {
      body.appendChild(createTitle(data));
    }

    if (isCardPartVisible('grid', 'description') && data.descriptionHtml) {
      body.appendChild(createHtmlElement('div', 'productlist-card__description', data.descriptionHtml));
    }

    appendProductMeta(body, 'grid', data);
    appendProductFlags(body, 'grid', data);

    if (isCardPartVisible('grid', 'price')) {
      body.appendChild(createPrice(data));
    }

    if (shouldShowActionArea('grid')) {
      body.appendChild(cloneActions(data, 'grid'));
    }

    card.appendChild(body);

    return card;
  }

  function buildListCard(data) {
    var card = document.createElement('article');
    var imageArea = document.createElement('div');
    var main = document.createElement('div');
    var aside = document.createElement('div');

    card.className = 'productlist-enhanced-card productlist-enhanced-card--list ' + getCardStateClass('list');
    imageArea.className = 'productlist-card__image';
    main.className = 'productlist-card__main';
    aside.className = 'productlist-card__aside';

    if (isCardPartVisible('list', 'image')) {
      imageArea.appendChild(createLinkedImage(data));
      card.appendChild(imageArea);
    }

    if (isCardPartVisible('list', 'title')) {
      main.appendChild(createTitle(data));
    }

    if (isCardPartVisible('list', 'description') && data.descriptionHtml) {
      main.appendChild(createHtmlElement('div', 'productlist-card__description', data.descriptionHtml));
    }

    appendProductMeta(main, 'list', data);
    appendProductFlags(main, 'list', data);

    if (isCardPartVisible('list', 'price')) {
      aside.appendChild(createPrice(data));
    }

    if (shouldShowActionArea('list')) {
      aside.appendChild(cloneActions(data, 'list'));
    }

    card.appendChild(main);

    if (aside.childNodes.length) {
      card.appendChild(aside);
    }

    return card;
  }

  function buildTableCard(data) {
    var card = document.createElement('article');
    var imageArea = document.createElement('div');
    var product = document.createElement('div');
    var price = isCardPartVisible('table', 'price') ? createPrice(data) : null;
    var actions = shouldShowActionArea('table') ? cloneActions(data, 'table') : null;

    card.className = 'productlist-enhanced-card productlist-enhanced-card--table ' + getCardStateClass('table');
    imageArea.className = 'productlist-card__image';
    product.className = 'productlist-card__main';

    if (isCardPartVisible('table', 'image')) {
      imageArea.appendChild(createLinkedImage(data));
      card.appendChild(imageArea);
    }

    if (isCardPartVisible('table', 'title')) {
      product.setAttribute('data-productlist-column', getLabel('product', 'Product'));
      product.appendChild(createTitle(data));
      if (isCardPartVisible('table', 'description') && data.descriptionHtml) {
        product.appendChild(createHtmlElement('div', 'productlist-card__description', data.descriptionHtml));
      }
      appendProductMeta(product, 'table', data);
      appendProductFlags(product, 'table', data);
      card.appendChild(product);
    }

    if (price) {
      price.setAttribute('data-productlist-column', getLabel('price', 'Price'));
      card.appendChild(price);
    }

    if (actions) {
      actions.setAttribute('data-productlist-column', getLabel('action', 'Action'));
      card.appendChild(actions);
    }

    return card;
  }

  function buildCompactCard(data) {
    var card = document.createElement('article');
    var product = document.createElement('div');
    var actions = shouldShowActionArea('compact') ? cloneActions(data, 'compact') : null;

    card.className = 'productlist-enhanced-card productlist-enhanced-card--compact ' + getCardStateClass('compact');
    product.className = 'productlist-card__main';

    if (isCardPartVisible('compact', 'image')) {
      card.appendChild(createLinkedImage(data));
    }

    if (isCardPartVisible('compact', 'title')) {
      product.appendChild(createTitle(data));
    }

    if (isCardPartVisible('compact', 'description') && data.descriptionHtml) {
      product.appendChild(createHtmlElement('div', 'productlist-card__description', data.descriptionHtml));
    }

    appendProductMeta(product, 'compact', data);
    appendProductFlags(product, 'compact', data);

    if (product.childNodes.length) {
      card.appendChild(product);
    }

    if (isCardPartVisible('compact', 'price')) {
      card.appendChild(createPrice(data));
    }

    if (actions) {
      card.appendChild(actions);
    }

    return card;
  }

  function buildShowcaseCard(data) {
    var card = document.createElement('article');
    var imageArea = document.createElement('div');
    var body = document.createElement('div');
    var footer = document.createElement('div');

    card.className = 'productlist-enhanced-card productlist-enhanced-card--showcase ' + getCardStateClass('showcase');
    imageArea.className = 'productlist-card__image';
    body.className = 'productlist-card__body';
    footer.className = 'productlist-card__footer';

    if (isCardPartVisible('showcase', 'image')) {
      imageArea.appendChild(createLinkedImage(data));
      card.appendChild(imageArea);
    }

    appendProductFlags(body, 'showcase', data);

    if (isCardPartVisible('showcase', 'title')) {
      body.appendChild(createTitle(data));
    }

    if (isCardPartVisible('showcase', 'description') && data.descriptionHtml) {
      body.appendChild(createHtmlElement('div', 'productlist-card__description', data.descriptionHtml));
    }

    appendProductMeta(body, 'showcase', data);

    if (isCardPartVisible('showcase', 'price')) {
      footer.appendChild(createPrice(data));
    }

    if (shouldShowActionArea('showcase')) {
      footer.appendChild(cloneActions(data, 'showcase'));
    }

    card.appendChild(body);

    if (footer.childNodes.length) {
      card.appendChild(footer);
    }

    return card;
  }

  function buildMasonryCard(data) {
    var card = document.createElement('article');
    var imageArea = document.createElement('div');
    var body = document.createElement('div');

    card.className = 'productlist-enhanced-card productlist-enhanced-card--masonry ' + getCardStateClass('masonry');
    imageArea.className = 'productlist-card__image';
    body.className = 'productlist-card__body';

    if (isCardPartVisible('masonry', 'image')) {
      imageArea.appendChild(createLinkedImage(data));
      card.appendChild(imageArea);
    }

    appendProductFlags(body, 'masonry', data);

    if (isCardPartVisible('masonry', 'title')) {
      body.appendChild(createTitle(data));
    }

    if (isCardPartVisible('masonry', 'description') && data.descriptionHtml) {
      body.appendChild(createHtmlElement('div', 'productlist-card__description', data.descriptionHtml));
    }

    appendProductMeta(body, 'masonry', data);

    if (isCardPartVisible('masonry', 'price')) {
      body.appendChild(createPrice(data));
    }

    if (shouldShowActionArea('masonry')) {
      body.appendChild(cloneActions(data, 'masonry'));
    }

    card.appendChild(body);

    return card;
  }

  function renderEnhancedCards() {
    var products = getProductsContainer();
    var items;

    if (!products) {
      return;
    }

    items = products.querySelectorAll('.product');

    Array.prototype.forEach.call(items, function (product) {
      var data;

      if (product.querySelector('.productlist-enhanced-card')) {
        return;
      }

      data = getProductData(product);

      if (!data) {
        return;
      }

      product.classList.add('productlist-enhanced-ready');
      product.appendChild(buildGridCard(data));
      product.appendChild(buildListCard(data));
      product.appendChild(buildTableCard(data));
      product.appendChild(buildCompactCard(data));
      product.appendChild(buildShowcaseCard(data));
      product.appendChild(buildMasonryCard(data));
    });
  }

  function getNextPageUrl(scope) {
    var root = scope || document;
    var next = root.querySelector('.pagination .next a, .pagination a.next, a[rel="next"]');

    if (next && next.href) {
      return normalizeListingUrl(next.href);
    }

    return '';
  }

  function getLoadedProductCount(products) {
    return products ? products.querySelectorAll('.product').length : 0;
  }

  function updateInfiniteStatus(status, products, message) {
    var count = getLoadedProductCount(products);
    var countText = getLabel('loadedProducts', 'Loaded products');

    status.textContent = message ? countText + ': ' + count + ' - ' + message : countText + ': ' + count;
  }

  function hideNativePagination() {
    var pagination = document.querySelector('#js-product-list .pagination, #products .pagination, .pagination');

    if (pagination) {
      pagination.style.display = 'none';
    }
  }

  function ensureInfiniteScrollStatus(root) {
    var status = document.querySelector('.productlist-infinite-status');

    if (status) {
      return status;
    }

    status = document.createElement('div');
    status.className = 'productlist-infinite-status';
    status.setAttribute('aria-live', 'polite');
    status.textContent = '';
    root.parentNode.insertBefore(status, root.nextSibling);

    return status;
  }

  function initInfiniteScroll(getCurrentView) {
    var products = getProductsContainer();
    var nextPageUrl = getNextPageUrl(document);
    var loading = false;
    var status;

    if (window.productlistPaginationMode !== 'infinite' || !products || !nextPageUrl) {
      return;
    }

    status = ensureInfiniteScrollStatus(products);
    hideNativePagination();
    updateInfiniteStatus(status, products, '');

    function loadNextPage() {
      if (loading || !nextPageUrl) {
        return;
      }

      loading = true;
      updateInfiniteStatus(status, products, getLabel('loadingMore', 'Loading more products...'));

      window.fetch(nextPageUrl, {
        credentials: 'same-origin'
      }).then(function (response) {
        return response.text();
      }).then(function (html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var nextProducts = doc.querySelectorAll('#js-product-list .products .product, #products .products .product');

        Array.prototype.forEach.call(nextProducts, function (product) {
          products.appendChild(document.importNode(product, true));
        });

        nextPageUrl = getNextPageUrl(doc);
        applyView(getCurrentView());
        hideNativePagination();
        updateInfiniteStatus(status, products, nextPageUrl ? '' : getLabel('noMoreProducts', 'No more products'));
      }).catch(function () {
        updateInfiniteStatus(status, products, getLabel('loadMoreError', 'Could not load more products'));
      }).then(function () {
        loading = false;
      });
    }

    window.addEventListener('scroll', function () {
      var rect = products.getBoundingClientRect();
      var distanceToBottom = rect.bottom - window.innerHeight;

      if (distanceToBottom < 700) {
        loadNextPage();
      }
    }, { passive: true });
  }

  function getAvailableViews() {
    return Array.prototype.slice.call(document.querySelectorAll('[data-productlist-view]')).map(function (button) {
      return button.getAttribute('data-productlist-view');
    });
  }

  function applyView(view) {
    var root = getListingRoot();
    var buttons = document.querySelectorAll('[data-productlist-view]');

    viewClasses.forEach(function (className) {
      root.classList.remove(className);
      document.body.classList.remove(className);
    });

    root.classList.add('productlist-view-' + view);
    document.body.classList.add('productlist-view-' + view);
    renderEnhancedCards();
    ensureTableHeader(view);
    annotateTableColumns();

    Array.prototype.forEach.call(buttons, function (button) {
      var active = button.getAttribute('data-productlist-view') === view;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function resolveInitialView(switcher) {
    var availableViews = getAvailableViews();
    var urlView = readUrlView();
    var savedView = readStoredView();
    var defaultView = switcher.getAttribute('data-productlist-default') || window.productlistDefaultView || 'grid';

    if (availableViews.indexOf(urlView) !== -1) {
      return urlView;
    }

    if (availableViews.indexOf(savedView) !== -1) {
      return savedView;
    }

    if (availableViews.indexOf(defaultView) !== -1) {
      return defaultView;
    }

    return availableViews[0] || 'grid';
  }

  function init() {
    var switcher = document.querySelector('[data-productlist-default]') || createSwitcher();
    var currentView;

    applyProductsPerPageConfig();
    applyLayoutConfig();

    if (!switcher) {
      return;
    }

    currentView = resolveInitialView(switcher);
    applyView(currentView);
    initInfiniteScroll(function () {
      return currentView;
    });

    document.addEventListener('click', function (event) {
      var button = event.target.closest('[data-productlist-view]');

      if (!button) {
        return;
      }

      var view = button.getAttribute('data-productlist-view');
      currentView = view;
      applyView(view);

      storeView(view);
    });

    if (window.prestashop && window.prestashop.on) {
      window.prestashop.on('updatedProductList', function () {
        window.setTimeout(function () {
          switcher = document.querySelector('[data-productlist-default]') || createSwitcher();

          applyView(currentView);
        }, 0);
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}());
