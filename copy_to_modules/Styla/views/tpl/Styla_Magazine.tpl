[{capture append="oxidBlock_pageHead"}]
    [{foreach from=$meta item=entry}]
        [{$entry}]
    [{/foreach}]
[{/capture}]

[{capture append="oxidBlock_pageHead"}]
    [{$js_embed}]
    [{$css_embed}]
[{/capture}]

[{capture append="oxidBlock_content"}]
    [{$styla_div}]

    [{oxscript add="var Styla_ajaxToBasket_baseURL = '`$oView->getBaseLink()`';" priority=12}]
[{/capture}]

[{include file="layout/page.tpl"}]
