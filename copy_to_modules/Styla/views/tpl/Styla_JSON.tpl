[{if $haserror}]
{"error":"[{$haserror}]", "message":"[{$errmsg}]"}
[{else}]
[{$data|@json_encode}]
[{/if}]