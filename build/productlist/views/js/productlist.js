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

    // Respect an explicit per-page value already present in the URL (the
    // theme's "items per page" selector or a previous visit). Only apply the
    // configured default when the visitor has not chosen a value yet, so the
    // customer can always override it.
    if (url.searchParams.has('resultsPerPage')) {
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

  function getImageMode() {
    var mode = window.productlistImageMode;

    if (mode === 'hover' || mode === 'carousel') {
      return mode;
    }

    return 'none';
  }

  function createImageCarousel(data) {
    var images = data.images || [];
    var carousel = document.createElement('div');
    var track = document.createElement('div');
    var dots = document.createElement('div');
    var prev = document.createElement('button');
    var next = document.createElement('button');
    var index = 0;
    var startX = null;

    carousel.className = 'productlist-card__carousel';
    track.className = 'productlist-card__carousel-track';
    dots.className = 'productlist-card__carousel-dots';

    images.forEach(function (img, i) {
      var slide = document.createElement('a');
      var image = document.createElement('img');
      var dot = document.createElement('button');

      slide.className = 'productlist-card__carousel-slide';
      slide.href = data.url || '#';
      slide.setAttribute('aria-label', data.title);
      image.className = 'productlist-card__carousel-image';
      image.src = img.src;
      image.alt = img.alt || data.title || '';
      image.loading = i === 0 ? 'eager' : 'lazy';
      slide.appendChild(image);
      track.appendChild(slide);

      dot.type = 'button';
      dot.className = 'productlist-card__carousel-dot';
      dot.setAttribute('aria-label', String(i + 1));
      dot.addEventListener('click', function (event) {
        event.preventDefault();
        goTo(i);
      });
      dots.appendChild(dot);
    });

    function goTo(target) {
      index = (target + images.length) % images.length;
      track.style.transform = 'translateX(' + (-index * 100) + '%)';

      Array.prototype.forEach.call(dots.children, function (dot, dotIndex) {
        dot.classList.toggle('is-active', dotIndex === index);
      });
    }

    prev.type = 'button';
    next.type = 'button';
    prev.className = 'productlist-card__carousel-nav productlist-card__carousel-nav--prev';
    next.className = 'productlist-card__carousel-nav productlist-card__carousel-nav--next';
    prev.setAttribute('aria-label', getLabel('previousImage', 'Previous image'));
    next.setAttribute('aria-label', getLabel('nextImage', 'Next image'));
    prev.innerHTML = '<span aria-hidden="true">‹</span>';
    next.innerHTML = '<span aria-hidden="true">›</span>';

    prev.addEventListener('click', function (event) {
      event.preventDefault();
      goTo(index - 1);
    });

    next.addEventListener('click', function (event) {
      event.preventDefault();
      goTo(index + 1);
    });

    track.addEventListener('touchstart', function (event) {
      startX = event.touches[0].clientX;
    }, { passive: true });

    track.addEventListener('touchend', function (event) {
      if (startX === null) {
        return;
      }

      var delta = event.changedTouches[0].clientX - startX;

      if (Math.abs(delta) > 40) {
        goTo(index + (delta < 0 ? 1 : -1));
      }

      startX = null;
    }, { passive: true });

    carousel.appendChild(track);
    carousel.appendChild(prev);
    carousel.appendChild(next);
    carousel.appendChild(dots);
    goTo(0);

    return carousel;
  }

  function createLinkedImage(data) {
    var mode = getImageMode();
    var images = data.images || [];

    if (mode === 'carousel' && images.length > 1) {
      return createImageCarousel(data);
    }

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

    if (mode === 'hover' && images.length > 1) {
      var hoverImage = document.createElement('img');

      hoverImage.className = 'productlist-card__image-hover';
      hoverImage.src = images[1].src;
      hoverImage.alt = '';
      hoverImage.loading = 'lazy';
      hoverImage.setAttribute('aria-hidden', 'true');
      link.appendChild(hoverImage);
      link.classList.add('has-hover-image');
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
      images: embedded.images || [],
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
    var hasImage = isCardPartVisible('masonry', 'image');

    card.className = 'productlist-enhanced-card productlist-enhanced-card--masonry ' + getCardStateClass('masonry');
    imageArea.className = 'productlist-card__image';
    body.className = 'productlist-card__body';

    if (hasImage) {
      var imageLink = createLinkedImage(data);
      var image = imageLink.querySelector('img');

      // Real image ratios drive the masonry rhythm, so load them eagerly to
      // keep the measured column heights correct instead of collapsing to zero
      // while the image is still lazy.
      if (image) {
        image.loading = 'eager';
      }

      imageArea.appendChild(imageLink);
      // Flags sit over the image as floating badges in this view.
      appendProductFlags(imageArea, 'masonry', data);
      card.appendChild(imageArea);
    } else {
      appendProductFlags(body, 'masonry', data);
    }

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

  var cardBuilders = {
    grid: buildGridCard,
    list: buildListCard,
    table: buildTableCard,
    compact: buildCompactCard,
    showcase: buildShowcaseCard,
    masonry: buildMasonryCard
  };

  // Keeps the parsed product data so other views can be built lazily without
  // re-scraping the (now hidden) original miniature.
  var productDataCache = new WeakMap();

  function ensureProductData(product) {
    var data = productDataCache.get(product);

    if (data) {
      return data;
    }

    if (product.getAttribute('data-productlist-done') === '1') {
      return null;
    }

    data = getProductData(product);

    if (!data) {
      return null;
    }

    productDataCache.set(product, data);
    product.setAttribute('data-productlist-done', '1');
    product.classList.add('productlist-enhanced-ready');

    var miniature = product.querySelector('.product-miniature');
    if (miniature) {
      miniature.style.setProperty('display', 'none', 'important');
    }

    return data;
  }

  // Builds only the card for the requested view. Each view is built at most
  // once per product, the first time the visitor actually opens that view, so
  // a product never carries more than the cards it has shown (instead of all
  // six). This keeps the DOM light and avoids duplicating the listing markup.
  function renderEnhancedCards(view) {
    var products = getProductsContainer();
    var builder = cardBuilders[view];
    var items;

    if (!products || !builder) {
      return;
    }

    items = products.querySelectorAll('.product');

    Array.prototype.forEach.call(items, function (product) {
      var data = ensureProductData(product);
      var builtViews;

      if (!data) {
        return;
      }

      builtViews = product.getAttribute('data-productlist-views') || '';

      if (builtViews.split(' ').indexOf(view) !== -1) {
        return;
      }

      product.appendChild(builder(data));
      product.setAttribute('data-productlist-views', (builtViews + ' ' + view).trim());
    });
  }

  // ---- Real masonry layout (Pinterest-style, order-preserving) ----
  // Each product is absolutely positioned into the currently shortest column
  // using its real rendered height, so cards pack tightly with no row gaps
  // while keeping the original order left-to-right, top-to-bottom.
  var masonryGap = 16;
  var masonryActive = false;
  var masonryBound = false;
  var masonryFrame = null;

  function getMasonryColumnCount(containerWidth) {
    var layout = window.productlistLayout || {};
    var desktop = parseInt(layout.gridColumnsDesktop, 10) || 4;
    var tablet = parseInt(layout.gridColumnsTablet, 10) || 3;

    if (containerWidth < 480) {
      return 1;
    }

    if (containerWidth < 768) {
      return 2;
    }

    if (containerWidth < 992) {
      return Math.max(2, tablet);
    }

    return Math.max(2, desktop);
  }

  function watchMasonryImages(container) {
    var images = container.querySelectorAll('.productlist-enhanced-card--masonry img');

    Array.prototype.forEach.call(images, function (image) {
      if (image.getAttribute('data-productlist-masonry-watched') === '1') {
        return;
      }

      image.setAttribute('data-productlist-masonry-watched', '1');

      if (image.complete) {
        return;
      }

      // Relayout once the image reports its real size.
      image.addEventListener('load', scheduleMasonryLayout);
      image.addEventListener('error', scheduleMasonryLayout);
    });
  }

  function layoutMasonry() {
    var container = getProductsContainer();
    var items;
    var styles;
    var paddingLeft;
    var paddingRight;
    var innerWidth;
    var columns;
    var columnWidth;
    var columnHeights = [];
    var tallest = 0;
    var i;

    if (!container || !document.body.classList.contains('productlist-view-masonry')) {
      return;
    }

    items = container.querySelectorAll('.product');
    styles = window.getComputedStyle(container);
    paddingLeft = parseFloat(styles.paddingLeft) || 0;
    paddingRight = parseFloat(styles.paddingRight) || 0;
    innerWidth = container.clientWidth - paddingLeft - paddingRight;

    if (innerWidth <= 0) {
      return;
    }

    columns = getMasonryColumnCount(innerWidth);
    columnWidth = (innerWidth - masonryGap * (columns - 1)) / columns;

    for (i = 0; i < columns; i++) {
      columnHeights.push(0);
    }

    watchMasonryImages(container);
    container.style.setProperty('position', 'relative', 'important');

    // Pass 1: equalize widths so the next pass can read final heights at once.
    Array.prototype.forEach.call(items, function (item) {
      item.style.setProperty('position', 'absolute', 'important');
      item.style.setProperty('top', '0', 'important');
      item.style.setProperty('left', '0', 'important');
      item.style.setProperty('margin', '0', 'important');
      item.style.setProperty('width', columnWidth + 'px', 'important');
    });

    // Pass 2: drop each card into the shortest column.
    Array.prototype.forEach.call(items, function (item) {
      var shortest = 0;
      var offsetX;
      var offsetY;

      for (i = 1; i < columns; i++) {
        if (columnHeights[i] < columnHeights[shortest] - 0.5) {
          shortest = i;
        }
      }

      offsetX = paddingLeft + shortest * (columnWidth + masonryGap);
      offsetY = columnHeights[shortest];
      item.style.setProperty('transform', 'translate3d(' + offsetX + 'px, ' + offsetY + 'px, 0)', 'important');
      columnHeights[shortest] += item.offsetHeight + masonryGap;
    });

    for (i = 0; i < columns; i++) {
      if (columnHeights[i] > tallest) {
        tallest = columnHeights[i];
      }
    }

    container.style.setProperty('height', (tallest > 0 ? tallest - masonryGap : 0) + 'px', 'important');
  }

  function scheduleMasonryLayout() {
    if (masonryFrame) {
      return;
    }

    masonryFrame = window.requestAnimationFrame(function () {
      masonryFrame = null;
      layoutMasonry();
    });
  }

  function clearMasonryLayout() {
    var container = getProductsContainer();

    if (!container) {
      return;
    }

    container.style.removeProperty('position');
    container.style.removeProperty('height');

    Array.prototype.forEach.call(container.querySelectorAll('.product'), function (item) {
      ['position', 'top', 'left', 'margin', 'width', 'transform'].forEach(function (prop) {
        item.style.removeProperty(prop);
      });
    });
  }

  function enableMasonry() {
    masonryActive = true;

    if (!masonryBound) {
      masonryBound = true;
      window.addEventListener('resize', scheduleMasonryLayout, { passive: true });
    }

    scheduleMasonryLayout();
  }

  function disableMasonry() {
    if (!masonryActive) {
      return;
    }

    masonryActive = false;
    clearMasonryLayout();
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

  // Returns a controller with a refresh() method. The products container and
  // the next-page URL are re-resolved on every refresh so that infinite scroll
  // keeps working after PrestaShop replaces the listing (faceted search, sort
  // or AJAX pagination), instead of holding on to a detached node.
  function setupInfiniteScroll(getCurrentView) {
    var products = null;
    var nextPageUrl = '';
    var loading = false;
    var bound = false;
    var scrollScheduled = false;
    var status = null;

    function loadNextPage() {
      if (loading || !nextPageUrl || !products) {
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
          var imported = document.importNode(product, true);

          // Avoid duplicating ids that already exist on the current page.
          removeDuplicateIds(imported);
          products.appendChild(imported);
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

    function onScroll() {
      if (scrollScheduled) {
        return;
      }

      scrollScheduled = true;
      window.requestAnimationFrame(function () {
        scrollScheduled = false;

        if (!products) {
          return;
        }

        if (products.getBoundingClientRect().bottom - window.innerHeight < 700) {
          loadNextPage();
        }
      });
    }

    function refresh() {
      if (window.productlistPaginationMode !== 'infinite') {
        return;
      }

      products = getProductsContainer();
      nextPageUrl = getNextPageUrl(document);

      if (!products || !nextPageUrl) {
        return;
      }

      status = ensureInfiniteScrollStatus(products);
      hideNativePagination();
      updateInfiniteStatus(status, products, '');

      if (!bound) {
        window.addEventListener('scroll', onScroll, { passive: true });
        bound = true;
      }
    }

    return { refresh: refresh };
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
    renderEnhancedCards(view);
    ensureTableHeader(view);
    annotateTableColumns();

    if (view === 'masonry') {
      enableMasonry();
    } else {
      disableMasonry();
    }

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

  var _initialized = false;

  function init() {
    if (_initialized) {
      return;
    }
    _initialized = true;

    var switcher = document.querySelector('[data-productlist-default]') || createSwitcher();
    var currentView;

    applyProductsPerPageConfig();
    applyLayoutConfig();

    if (!switcher) {
      return;
    }

    currentView = resolveInitialView(switcher);
    applyView(currentView);

    var infiniteScroll = setupInfiniteScroll(function () {
      return currentView;
    });
    infiniteScroll.refresh();

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
          // The listing was replaced by the theme; re-resolve the container
          // and next-page URL so infinite scroll keeps working.
          infiniteScroll.refresh();
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