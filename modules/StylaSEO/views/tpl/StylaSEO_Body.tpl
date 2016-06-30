[{capture append="oxidBlock_pageHead"}]

    [{foreach from=$meta item=entry}]
        [{if $entry->attributes->property}]
            [{* Prints special meta tags (FB, Twitter) *}]
            <[{$entry->tag}] property="[{$entry->attributes->property}]" content="[{$entry->attributes->content}]" />
        [{elseif $entry->attributes->rel}]
            [{* Prints link tags *}]
            <[{$entry->tag}] rel="[{$entry->attributes->rel}]" type="[{$entry->attributes->type}]" href="[{$entry->attributes->href}]" />
        [{/if}]
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
