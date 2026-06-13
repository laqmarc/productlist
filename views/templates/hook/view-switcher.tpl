{if isset($productlist_views) && $productlist_views|count > 1}
  <div class="productlist-view-switcher" data-productlist-default="{$productlist_default_view|escape:'html':'UTF-8'}">
    <span class="productlist-view-switcher__label">{l s='View' mod='productlist'}</span>
    <div class="productlist-view-switcher__buttons" role="group" aria-label="{l s='Product view' mod='productlist'}">
      {foreach from=$productlist_views item=view}
        <button
          type="button"
          class="productlist-view-switcher__button{if $view.id == $productlist_default_view} is-active{/if}"
          data-productlist-view="{$view.id|escape:'html':'UTF-8'}"
          aria-pressed="{if $view.id == $productlist_default_view}true{else}false{/if}"
        >
          <span class="productlist-view-switcher__icon" aria-hidden="true"></span>
          <span class="productlist-view-switcher__text">{$view.label|escape:'html':'UTF-8'}</span>
        </button>
      {/foreach}
    </div>
  </div>
{/if}
