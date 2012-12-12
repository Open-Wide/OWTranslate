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
        <h2 class="context-title">{'Translator / Search'|i18n('owtranslate')}</h2>
    </div>
    <br class="clearfloat" />
    <div class="search-form">
{include uri='design:translate/searchtranslationform.tpl'}    
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
{include uri='design:translate/toptoolbar.tpl' view='search'}        
        <div class="content-navigation-childlist">                            
{if is_set($dataList)}
    {include uri='design:translate/translationtable.tpl'}
{/if}                                        
        </div>
        
{if and(is_set($numberTotal), gt($numberTotal, $limit))}                 

    <div class="context-toolbar">
        {include name=navigator
             uri='design:navigator/google.tpl'
             page_uri='/translate/search'
             item_count=$numberTotal
             view_parameters=$view_parameters
             item_limit=$limit}
    </div>
{/if}
                
    </div></div></div>
</div>
{undef}
