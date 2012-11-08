{*?template charset=UTF-8*}
{def 
    $nbPageMax = $numberTotal|div($nbPage)|ceil() 
}
<div class="context-toolbar">
  <div class="pagenavigator">
    <p>
      <span class="previous">{if gt($page, 1)}<a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $page|dec())|ezurl()}>{/if}<span class="text {if le($page, 1)}disabled{/if}">«&nbsp;{'previous'|i18n('owtranslate')}</span>{if gt($page, 1)}</a>{/if}</span>
      <span class="next">{if lt($page, $nbPageMax)}<a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $page|inc())|ezurl()}>{/if}<span class="text {if ge($page, $nbPageMax)}disabled{/if}">{'next'|i18n('owtranslate')}&nbsp;»</span>{if lt($page, $nbPageMax)}</a>{/if}</span>
      <span class="pages">
{if eq($page, 1)}      
        <span class="current">{$page}</span>        
    {if lt($page|inc(), $nbPageMax)}
        {def $pageInc = $page|inc()} 
        <span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $pageInc)|ezurl()}>{$pageInc}</a></span>
    {/if}
    {if lt($pageInc|inc(), $nbPageMax)}
        {set $pageInc = $pageInc|inc()} 
        <span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $pageInc)|ezurl()}>{$pageInc}</a></span>
    {/if}
        &nbsp;.....&nbsp;<span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $nbPageMax)|ezurl()}>{$nbPageMax}</a></span>
{elseif eq($page, $nbPageMax)}       
        <span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage)|ezurl()}>1</a></span>&nbsp;.....&nbsp;
    {if gt($page|dec()|dec(), 1)}
        {def $pageDec = $page|dec()|dec()} 
        <span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $pageDec)|ezurl()}>{$pageDec}</a></span>
    {/if}
    {if lt($page|dec(), 1)}
        {set $pageDec = $page|dec()} 
        <span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $pageDec)|ezurl()}>{$pageDec}</a></span>
    {/if}
    <span class="current">{$page}</span>
{else}
    {if le($page|dec()|dec(), 1)|not()} 
        <span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage)|ezurl()}>1</a></span>&nbsp;.....&nbsp;
    {/if}    
    {if lt($page|dec(), $nbPageMax)} 
        <span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $page|dec())|ezurl()}>{$page|dec()}</a></span>
    {/if}
        <span class="current">{$page}</span>    
    {if lt($page|inc(), $nbPageMax)}
        <span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $page|inc())|ezurl()}>{$page|inc()}</a></span>
    {/if}
    {if ge($page|inc()|inc(), $nbPageMax)|not()}
        &nbsp;.....&nbsp;<span class="other"><a href={concat('translate/list/(nbPage)/', $nbPage, '/(page)/', $nbPageMax)|ezurl()}>{$nbPageMax}</a></span>
    {/if}
{/if}
      </span>
    </p>
    <div class="break"></div>
  </div>
</div>
{undef}