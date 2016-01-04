[{capture append="oxidBlock_pageHead"}]

    [{if $feed_type == 'user' || $feed_type == 'magazine' || $feed_type == 'story'}]
        [{$meta_fb_app_id}]
        [{$meta_og}]
    [{/if}]

    [{if $feed_type == 'user' || $feed_type == 'magazine'}]
        [{$meta_author}]
    [{/if}]

[{/capture}]

[{capture append="oxidBlock_content"}]
    [{$js_embed}]
    <noscript>
        [{$noscript_content}]
    </noscript>

[{/capture}]
[{include file="layout/page.tpl"}]
