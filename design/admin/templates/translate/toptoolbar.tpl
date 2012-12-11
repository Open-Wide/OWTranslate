{*?template charset=UTF-8*}
{def 
    $choose1 = ezini( 'NumberPerPage', 'choose1', 'owtranslate.ini' )
    $choose2 = ezini( 'NumberPerPage', 'choose2', 'owtranslate.ini' )
    $choose3 = ezini( 'NumberPerPage', 'choose3', 'owtranslate.ini' )
}
<div class="context-toolbar">
    <div class="button-left">
        <p class="table-preferences">     
{if eq($limit, $choose1)}<span class="current">10</span>{else}<a href={concat('translate/list/(limit)/', $choose1, cond(ne($sourceKeyGet, ''), concat('/(sourceKey)/', $sourceKeyGet)), cond($localeGet, concat('/(locale)/', $localeGet)))|ezurl()} title="Affichez {$choose1} éléments par page.">{$choose1}</a>{/if}
{if eq($limit, $choose2)}<span class="current">25</span>{else}<a href={concat('translate/list/(limit)/', $choose2, cond(ne($sourceKeyGet, ''), concat('/(sourceKey)/', $sourceKeyGet)), cond($localeGet, concat('/(locale)/', $localeGet)))|ezurl()} title="Affichez {$choose2} éléments par page.">{$choose2}</a>{/if}
{if eq($limit, $choose3)}<span class="current">50</span>{else}<a href={concat('translate/list/(limit)/', $choose3, cond(ne($sourceKeyGet, ''), concat('/(sourceKey)/', $sourceKeyGet)), cond($localeGet, concat('/(locale)/', $localeGet)))|ezurl()} title="Affichez {$choose3} éléments par page.">{$choose3}</a>{/if}          
        </p>
    </div>
    <div class="break"></div>
</div>
{undef}