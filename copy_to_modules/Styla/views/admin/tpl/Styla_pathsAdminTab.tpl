[{include file="headitem.tpl" title="STYLA_PATHS_PATH"|oxmultilangassign}]

<script type="text/javascript">
<!--
    function homechecked(oCheckbox){
        var oStylaPAth = document.getElementById('styla_path');
        if(oCheckbox.checked){
            oStylaPAth.value = 'home';
            oStylaPAth.readonly = true;
            oStylaPAth.setAttribute('readonly','true');
            oStylaPAth.setAttribute('style','background-color:ligthgray;');
        }
        else{
            oStylaPAth.value = '';
            oStylaPAth.readonly = false;
            oStylaPAth.removeAttribute('readonly');
            oStylaPAth.removeAttribute('style');
        }
    }
//-->
</script>

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink() }]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="Styla_PathsTabController">
    <input type="hidden" name="editlanguage" value="[{$editlanguage }]">
</form>
<br/>
<form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="Styla_PathsTabController">
    <input type="hidden" name="fnc" value="">
    <input type="hidden" name="language" value="[{$actlang }]">
    <fieldset>
        <legend>[{ oxmultilang ident="STYLA_PATHS_PATH" }]</legend>
        <br/>
        <table>
            <tbody>
            <tr>
                <td class="edittext">
                    <label for="styla_path">[{ oxmultilang ident="STYLA_PATHS_PATH" }]</label>
                </td>
                <td class="edittext">
                    <input type="text" class="editinput" size="50" name="editval[stylapath]" id="styla_path"
                           value="[{$edit->styla_paths__stylapath->value}]"
                           [{if $edit->styla_paths__styla_home->value}]readonly style="background-color: lightgray"[{/if}]>
                </td>
                <td>[{ oxinputhelp ident="STYLA_PATHS_PATHHELP" }]</td>
            </tr>
            <tr>
                <td class="edittext">
                    <label for="iPosX">[{ oxmultilang ident="STYLA_PATHS_USER" }]</label>
                </td>
                <td class="edittext">
                    <input type="text" class="editinput" size="50" name="editval[stylauser]" id="styla_user"
                           value="[{$edit->styla_paths__stylauser->value}]" [{$readonly}]>
                </td>
                <td>[{ oxinputhelp ident="STYLA_PATHS_USERHELP" }]</td>
            </tr>
            <tr>
                <td class="edittext">
                    <label for="styla_home">[{ oxmultilang ident="STYLA_PATHS_HOME" }]</label>
                </td>
                <td class="edittext">
                    <input type="checkbox" class="editinput" name="editval[styla_home]" id="styla_home"
                           onchange="homechecked(this)"
                           [{if $edit->styla_paths__styla_home->value}] checked [{/if}] [{$readonly}]>
                </td>
                <td>[{ oxinputhelp ident="STYLA_PATHS_HOMEHELP" }]</td>
            </tr>
            <tr>
                <td class="edittext" colspan="2">
                    <br>[{include file="language_edit.tpl"}]<br>
                </td>
            </tr>
            </tbody>
        </table>
    </fieldset>

    <input type="submit" class="edittext" id="oLockButton" name="save" value="[{ oxmultilang ident="GENERAL_SAVE" }]"
        onclick="document.myedit.fnc.value='save'" [{$readonly}] >

</form>
[{assign var="loadnewbutton" value="1"}]
[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]
