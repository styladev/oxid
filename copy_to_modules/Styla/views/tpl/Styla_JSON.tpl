[{if $haserror}]
    {"error":true, "message":"[{$errmsg}]"}
[{else}]
    [{$data|@json_encode}]
[{/if}]
