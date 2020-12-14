[{if $bottom_buttons->Styla_Paths_new}]
<li>
    <a [{if !$firstitem}]class="firstitem" [{assign var="firstitem" value="1"}][{/if}] id="btn.new" href="#"
       onClick="top.oxid.admin.editThis( -1 );return false" target="edit">
        [{ oxmultilang ident="Styla_Paths_new" }]
    </a> |
</li>
[{/if}]

[{$smarty.block.parent}]

