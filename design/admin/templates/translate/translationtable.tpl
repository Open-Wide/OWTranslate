{*?template charset=UTF-8*}
<table class="list" cellspacing="0">
    <tr class="bgdark">
        <th>{'Translation key'|i18n('owtranslate')}</a></td>
        <th class="class">{'Context name Translation'|i18n('owtranslate')}</td>
{if or(lt($languageList|count(), 6), $localeGet)}                    
    {foreach $dataValues as $localeKey => $values}            
        <th class="class" {if and($localeGet, ne($languageList.$localeKey.locale, $localeGet))}style="display:none"{/if}><img src="{concat('/share/icons/flags/', $languageList.$localeKey.locale|extract( 0, 6 ), '.gif')}" />&nbsp;{$languageList.$localeKey.name}</td>
    {/foreach}
{else}
        <th class="class"></td>
{/if}    
    </tr>
{def $compteur = 0}
{foreach $dataList as $sourceKey => $dataSource}
    {foreach $dataSource as $data}
    <tr class="{cond($compteur|mod(2)|eq(0), 'bgdark', 'bglight')}">
        <td><a title="{$data}" href={concat('translate/edit/', '(sourceKey)/', $sourceKey, '/(dataKey)/', $data)|ezurl()}>{$data|shorten(30)}</a></td>
        <td title="{$sourceKey}" class="class"><a href={concat('translate/list/(sourceKey)/', $sourceKey)|ezurl()}>{$sourceKey|shorten(30)}</a></td>
        {if or(lt($languageList|count(), 6), $localeGet)}
            {foreach $dataValues as $localeKey => $values}
        <td {if and($localeGet, ne($languageList.$localeKey.locale, $localeGet))}style="display:none"{/if} id="{$localeKey}|{$sourceKey}|{$data}" class="edit {cond($values.$data|eq(''), 'empty_edit')}">{$values.$data}</td>
            {/foreach}
        {else}
        <td class="click-to-open" id="to-{$compteur}">{'Click to open'|i18n('owtranslate')}</td>
        {/if}
    </tr>
        
        {if and(ge($languageList|count(), 6), $localeGet|not())}
            {include uri='design:translate/translationline.tpl' class=cond($compteur|mod(2)|eq(0), 'bglight', 'bgdark') id=$compteur}
        {/if}    
                    
        {set $compteur = $compteur|inc()} 
    {/foreach}
{/foreach}
</table>