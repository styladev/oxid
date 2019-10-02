[{*
  * Styla base_style
  *
  * Extends the base_style block to load Styla styles and scripts
  *
  * ---------------------------------------------------------------------------------------------------------------------
  *
  *}]

[{* get parent block *}]
[{$smarty.block.parent}]

[{* get productive mode *}]
[{assign var="productive_mode" value=$oxcmp_shop->oxshops__oxproductive->value}]

[{capture name="Styla_config"}]
    Styla_ajaxToBasket_baseURL = "[{ $oView->getBaseLink() }]";
[{/capture}]
[{oxscript add=$smarty.capture.Styla_config priority=12}]

[{* Include JS and CSS
---------------------------------------------------------------------------------------------------------------------*}]
[{if $productive_mode }]
    [{oxscript include=$oViewConf->getModuleUrl('Styla','out/src/js/ajaxToBasket.min.js') priority=10}]
[{else}]
    [{oxscript include=$oViewConf->getModuleUrl('Styla','out/src/js/ajaxToBasket.js') priority=10}]
[{/if}]