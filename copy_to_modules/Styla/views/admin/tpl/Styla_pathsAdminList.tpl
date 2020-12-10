[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign box="list"}]
[{assign var="where" value=$oView->getListFilter()}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
[{/if}]

<script type="text/javascript">
    <!--
    window.onload = function ()
    {
        top.reloadEditFrame();
        [{ if $updatelist == 1}]
        top.oxid.admin.updateList('[{$oxid }]');
        [{ /if}]
    }
    //-->
</script>

[{block name="STYLA_PATHS_ADMINLIST_CSS"}]
    <style type="text/css">
        #search .listedit {
            float: left;
            width: 25%;
            margin-left: 0px;
        }

        #search table th {
            text-align: left;
        }

        #search table th input[type=submit] {
            border: 0;
            color: transparent;
            background: #f0f0f0 url(../out/admin/src/bg/ico_find.gif) center center no-repeat;
            width: 20px;
            height: 20px;
        }

        #search #styla_list_table{
            border-spacing: 0;
        }
    </style>
[{/block}]


<div id="liste">
    <form name="search" id="search" action="[{$oViewConf->getSelfLink()}]" method="post">
        [{include file="_formparams.tpl" cl="Styla_PathsListController" lstrt=$lstrt actedit=$actedit oxid=$oxid fnc="" language=$actlang editlanguage=$actlang}]
        <table id="styla_list_table"  cellspacing="0" cellpadding="0" border="0" width="100%">
            <colgroup>
                [{block name="admin_styla_list_colgroup"}]
                <col width="35%">
                <col width="35%">
                <col width="10%">
                <col width="20%">
              [{/block}]
            </colgroup>
            <tr class="listitem">
                <td valign="top" class="listfilter first" align="right">
                    <div class="r1">
                        <div class="b1">
                            <input class="listedit" type="text" maxlength="255" name="where[styla_paths][stylapath]"
                                   value="[{ $where.styla_paths.stylapath }]">
                        </div>
                    </div>
                </td>
                <td valign="top" class="listfilter" align="left">
                    <div class="r1">
                        <div class="b1">
                            <input class="listedit" type="text" maxlength="255" name="where[styla_paths][stylauser]"
                                   value="[{ $where.styla_paths.stylauser }]">
                        </div>
                    </div>
                </td>
                <td valign="top" class="listfilter" align="left">
                    <div class="r1">
                        <div class="b1">
                            <select name="changelang" class="editinput" onChange="Javascript:top.oxid.admin.changeLanguage();">
                                [{foreach from=$languages item=lang}]
                                <option value="[{ $lang->id }]" [{if $lang->selected}]SELECTED[{/if}]>[{$lang->name }]</option>
                                [{/foreach}]
                            </select>
                        </div>
                    </div>
                </td>
                <td valign="top" class="listfilter" align="left">
                    <div class="r1">
                        <div class="b1">
                            <input class="listedit" type="submit" name="submitit"
                                   value="[{oxmultilang ident="GENERAL_SEARCH" }]">
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="listitem">
                <td class="listheader first">
                    <a href="Javascript:top.oxid.admin.setSorting( document.search, 'styla_paths', 'stylapath', 'asc');document.search.submit();"
                       class="listheader">
                        [{oxmultilang ident="STYLA_PATHS_PATH" }]
                    </a>
                </td>
                <td class="listheader">
                    <a href="Javascript:top.oxid.admin.setSorting( document.search, 'styla_paths', 'stylauser', 'asc');document.search.submit();"
                       class="listheader">
                        [{oxmultilang ident="STYLA_PATHS_USER" }]
                    </a>
                </td>
                <td class="listheader">
                    <a href="Javascript:top.oxid.admin.setSorting( document.search, 'styla_paths', 'styla_home', 'asc');document.search.submit();"
                       class="listheader">
                        [{oxmultilang ident="STYLA_PATHS_HOME" }]
                    </a>
                </td>
                <td class="listheader"></td>
            </tr>
            [{assign var="blWhite" value=""}]
            [{assign var="_cnt" value=0}]
            [{foreach from=$mylist item=listitem}]
                [{assign var="_cnt" value=$_cnt+1}]
                [{if $listitem->blacklist == 1}]
                    [{assign var="listclass" value=listitem3 }]
                [{else}]
                    [{assign var="listclass" value=listitem$blWhite }]
                [{/if}]
                [{if $listitem->styla_paths__oxid->value == $oxid }]
                    [{assign var="listclass" value=listitem4 }]
                [{/if}]
                <tr id="row.[{$_cnt}]">
                    <td valign="top" class="[{ $listclass}]" height="15">
                        <div class="listitemfloating">
                            <a href="Javascript:top.oxid.admin.editThis('[{ $listitem->styla_paths__oxid->value}]');"
                               class="[{ $listclass}]">
                                [{$listitem->styla_paths__stylapath->value }]
                            </a>
                        </div>
                    </td>
                    <td valign="top" class="[{ $listclass}]" height="15">
                        <div class="listitemfloating">
                            <a href="Javascript:top.oxid.admin.editThis('[{ $listitem->styla_paths__oxid->value}]');"
                               class="[{ $listclass}]">
                                [{$listitem->styla_paths__stylauser->value }]
                            </a>
                        </div>
                    </td>
                    <td valign="top" class="[{ $listclass}]" height="15">
                        <div class="listitemfloating">
                            <a href="Javascript:top.oxid.admin.editThis('[{ $listitem->styla_paths__oxid->value}]');"
                               class="[{ $listclass}]">
                                [{if $listitem->styla_paths__styla_home->value eq 0}] Nein [{else}] Ja [{/if}]
                            </a>
                        </div>
                    </td>
                    <td align="right" class="[{ $listclass}]">
                        <a href="Javascript:top.oxid.admin.deleteThis('[{ $listitem->styla_paths__oxid->value }]');"
                           class="delete" id="del.[{$_cnt}]" title=""
                           [{include file="help.tpl" helpid=item_delete}]></a>
                    </td>
                </tr>
                [{if $blWhite == "2"}]
                    [{assign var="blWhite" value=""}]
                [{else}]
                    [{assign var="blWhite" value="2"}]
                [{/if}]
                [{/foreach}]
                [{include file="pagenavisnippet.tpl" colspan="4"}]
        </table>
    </form>
</div>


[{include file="pagetabsnippet.tpl"}]

</body>
</html>