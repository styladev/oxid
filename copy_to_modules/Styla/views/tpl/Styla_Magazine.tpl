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

[{$noscript_content}]

<div id="stylaMagazine"></div>
    <div id="amazineEmbed"></div>
[{/capture}]

[{include file="layout/page.tpl"}]
