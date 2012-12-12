{*?template charset=UTF-8*}
{def 
    $choose1 = ezini( 'NumberPerPage', 'choose1', 'owtranslate.ini' )
    $choose2 = ezini( 'NumberPerPage', 'choose2', 'owtranslate.ini' )
    $choose3 = ezini( 'NumberPerPage', 'choose3', 'owtranslate.ini' )
}
<div class="context-toolbar">
    <div class="button-left">
        <p class="table-preferences">     
{if eq($limit, $choose1)}<span class="current">{$choose1}</span>{else}<a href={concat('translate/',$view , '/(limit)/', $choose1, cond(ne($sourceKeyGet, ''), concat('/(sourceKey)/', $sourceKeyGet)), cond($localeGet, concat('/(locale)/', $localeGet)))|ezurl()} title="Display {$choose1} elements per page.">{$choose1}</a>{/if}
{if eq($limit, $choose2)}<span class="current">{$choose2}</span>{else}<a href={concat('translate/',$view , '/(limit)/', $choose2, cond(ne($sourceKeyGet, ''), concat('/(sourceKey)/', $sourceKeyGet)), cond($localeGet, concat('/(locale)/', $localeGet)))|ezurl()} title="Display {$choose2} elements per page.">{$choose2}</a>{/if}
{if eq($limit, $choose3)}<span class="current">{$choose3}</span>{else}<a href={concat('translate/',$view , '/(limit)/', $choose3, cond(ne($sourceKeyGet, ''), concat('/(sourceKey)/', $sourceKeyGet)), cond($localeGet, concat('/(locale)/', $localeGet)))|ezurl()} title="Display {$choose3} elements per page.">{$choose3}</a>{/if}          
        </p>
    </div>
    <div class="break"></div>
</div>
{undef}