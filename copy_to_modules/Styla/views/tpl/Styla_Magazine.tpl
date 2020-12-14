[{capture append="oxidBlock_pageHead"}]
    [{foreach from=$meta item=entry}]
        [{$entry}]
    [{/foreach}]
[{/capture}]

[{capture append="oxidBlock_pageHead"}]
    [{$js_embed}]
[{/capture}]

[{capture append="oxidBlock_content"}]
    [{$styla_div}]
[{/capture}]

[{include file="layout/page.tpl"}]
