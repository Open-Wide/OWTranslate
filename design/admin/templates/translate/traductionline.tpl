{*?template charset=UTF-8*}
<tr id="from-{$id}" class="close">
    <td colspan="3">
        <table width="100%">
            <tr>          
{foreach $dataValues as $localeKey => $values}
                <th class="class"><img src="{concat('/share/icons/flags/', $languageList.$localeKey.locale|extract( 0, 6 ), '.gif')}" />&nbsp;{$languageList.$localeKey.name}</td>
{/foreach}        
            </tr>
            <tr class="{$class}">
{foreach $dataValues as $localeKey => $values}
                <td id="{$localeKey}|{$sourceKey}|{$data}" class="edit {cond($values.$data|eq(''), 'empty_edit')}">{$values.$data}</td>
{/foreach}
            </tr>
        </table>
    </td>
</tr>