{*?template charset=UTF-8*}
{def
    $localeGet = false()
    $sourceKeyGet = false()
    $dataKeyGet = false()
}
{if ezhttp_hasvariable( 'locale', 'get' )}
    {set $localeGet = ezhttp( 'locale', 'get' )}
{else}
    {set $localeGet = $locale}        
{/if}
{if ezhttp_hasvariable( 'sourceKey', 'get' )}
    {set $sourceKeyGet = ezhttp( 'sourceKey', 'get' )}
{else}
    {set $sourceKeyGet = $sourceKey}        
{/if}
{if ezhttp_hasvariable( 'dataKey', 'get' )}
    {set $dataKeyGet = ezhttp( 'dataKey', 'get' )}    
{/if}
<div class="box-header">
    <div class="button-left">
        <h2 class="context-title">{'Translator / List'|i18n('owtranslate')}</h2>
    </div>
    <br class="clearfloat" />
    <div class="search-form">
{include uri='design:translate/searchtranslation.tpl'}    
    </div>
    <div class="float-break"></div>
</div>
<div class="context-block">
    <div class="box-header">
        <div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl">
	        <div class="box-tr">
	            <h1 class="context-title">{$numberTotal}&nbsp;{'translations'|i18n('owtranslate')}</h1><div class="header-subline"></div>
	        </div>
        </div></div></div></div>
    </div>
    <div class="box-ml"><div class="box-mr"><div class="box-content">
{include uri='design:translate/toptoolbar.tpl'}        
        <div class="content-navigation-childlist">                            
{if is_set($dataList)}
            <table class="list" cellspacing="0">
                <tr class="bgdark">
                    <th>{'Tranlation name'|i18n('owtranslate')}</a></td>
                    <th class="class">{'Context name Tranlation'|i18n('owtranslate')}</td>
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
                    <td><a title="{$data}" href={concat('translate/edit/', '(sourceKey)/', $sourceKey, '/(dataKey)/', $data)|ezurl()}>{$data|shortenw(30, '...')}</a></td>
                    <td title="{$sourceKey}" class="class"><a href={concat('translate/list/(sourceKey)/', $sourceKey)|ezurl()}>{$sourceKey|shortenw(30, '...')}</a></td>
            {if or(lt($languageList|count(), 6), $localeGet)}
                {foreach $dataValues as $localeKey => $values}
                    <td {if and($localeGet, ne($languageList.$localeKey.locale, $localeGet))}style="display:none"{/if} id="{$localeKey}|{$sourceKey}|{$data}" class="edit {cond($values.$data|eq(''), 'empty_edit')}">{$values.$data}</td>
                {/foreach}
            {else}
                    <td class="click-to-open" id="to-{$compteur}">{'Click to open'|i18n('owtranslate')}</td>
            {/if}
                </tr>
                
            {if and(ge($languageList|count(), 6), $localeGet|not())}
                {include uri='design:translate/traductionline.tpl' class=cond($compteur|mod(2)|eq(0), 'bglight', 'bgdark') id=$compteur}
            {/if}    
                            
            {set $compteur = $compteur|inc()} 
        {/foreach}
    {/foreach}
    {if eq($numberTotal, 0)}
                <tr class="{cond($compteur|mod(2)|eq(0), 'bgdark', 'bglight')}">
                <td colspan="2" style="text-align:center">{'No found translation'|i18n('owtranslate')}</td>
                </tr>
    {/if}
            </table>
{/if}                                        
        </div>
{if and(is_set($numberTotal), gt($numberTotal, $nbPage))}                 
    {include uri='design:translate/pagination.tpl'}
{/if}                
                
    </div></div></div>
</div>
{undef}