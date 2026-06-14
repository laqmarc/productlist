# Changelog

All notable changes to this module are documented here.

## 0.12.2

### Fixed
- Infinite scroll no longer reloads the current page when the theme exposes the
  real pagination URL through `data-search-url` while `href` is only `#`.
- Infinite scroll tracks loaded listing URLs and product identities, preventing
  duplicate products if a theme returns a page that has already been appended.
- The defensive DOM observer no longer refreshes infinite-scroll state just
  because infinite scroll appended products to the existing container.

## 0.12.1

### Fixed
- The selected product-list view is now propagated to pagination, sorting and
  faceted-search URLs with `pl_view`, so changing page keeps the chosen card
  layout even when the browser storage preference is unavailable.
- Product-list DOM replacements that do not emit PrestaShop's
  `updatedProductList` event are observed and re-applied defensively.

## 0.12.0

### Added
- Real masonry layout (Pinterest-style): cards pack into the shortest column by
  their actual height, preserving order. Recomputes on resize, image load, view
  change and infinite-scroll loads. CSS-columns fallback when JS is unavailable.
- Product image options (new "Product image behavior" setting):
  - **Second image on hover** — fades in the product's second image.
  - **Carousel of all images** — arrows, dot indicators and touch swipe.

### Fixed
- Infinite scroll keeps working after faceted search, sorting or AJAX pagination
  (the products container and next-page URL are re-resolved on update).
- "Products per page" no longer overrides a per-page value already present in
  the URL, so the theme's selector keeps working.
- Quick add-to-cart is now correct: simple, orderable products are added with
  their combination id; products that need a combination, are customizable or
  are not orderable link to the product page instead of adding a wrong variant.
- The distributable ZIP uses POSIX (forward-slash) paths, so it installs
  correctly when extracted on Linux servers.

### Changed
- Enhanced cards are built lazily for the active view instead of all six per
  product — lighter DOM, less duplication for SEO.
- Carousel is keyboard- and screen-reader-accessible and honours
  `prefers-reduced-motion`.
- Back office configuration styles/scripts moved to external asset files (no
  inline JS/CSS) for PrestaShop validator compliance.

### Tooling
- GitHub Actions CI: PHP and JS syntax checks plus a ZIP-integrity check.
- `.editorconfig` for consistent formatting.
