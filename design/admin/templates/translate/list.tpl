{*?template charset=UTF-8*}
<div class="box-header">
    <div class="button-left">
        <h2 class="context-title">{'Translator Generator / List'|i18n('owtranslate')}</h2>
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
    {foreach $dataValues as $localeKey => $values}
                    <th class="class">{$languageList.$localeKey.name}</td>
    {/foreach}
                </tr>
    {def $compteur = 0}
    {foreach $dataList as $sourceKey => $dataSource}
        {foreach $dataSource as $data}
                <tr class="{cond($compteur|mod(2)|eq(0), 'bgdark', 'bglight')}">
                    <td><a title="{$data}" href={concat('translate/edit/', '(sourceKey)/', $sourceKey, '/(dataKey)/', $data)|ezurl()}>{$data|shortenw(30, '...')}</a></td>
                    <td title="{$sourceKey}" class="class">{$sourceKey|shortenw(30, '...')}</td>
            {foreach $dataValues as $localeKey => $values}
                    <td id="{$localeKey}|{$sourceKey}|{$data}" class="edit {cond($values.$data|eq(''), 'empty_edit')}">{$values.$data}</td>
            {/foreach}
                </tr>
            {set $compteur = $compteur|inc()} 
        {/foreach}
    {/foreach}
            </table>
{/if}                                        
        </div>
{if and(is_set($numberTotal), gt($numberTotal, $nbPage))}                 
    {include uri='design:translate/pagination.tpl'}
{/if}                
                
    </div></div></div>
</div>
{undef}