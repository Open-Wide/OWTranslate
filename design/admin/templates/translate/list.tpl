{*?template charset=UTF-8*}

{if $numberTotal|not|and( ezhttp_hasvariable( 'todo', 'get' )|not )}
	<div class="box-header">
	    <div class="button-left">
	        <h2 class="context-title">{'Translation'|i18n('owtranslate')}</h2>
	    </div>
	    <div class="float-break"></div>
	</div>
	 
	<div class="box-content">    
	    <div class="content-navigation-childlist">
	       <p>{"This extension aims at generating a file of translation by language, and at being able to modify them in the interface of the backOffice of eZ."|i18n('owtranslate')}</p>
	       <p>{"Before anything else, you have to generate the files of translations to be homogeneous."|i18n('owtranslate')}<a href={'/translate/generation'|ezurl()}>{'Generation'|i18n('owtranslate')}</a></p>
	    </div>
	</div>
{else}
			
	{def
	    $localeGet    = false()
	    $sourceKeyGet = false()
	    $dataKeyGet   = false()
	}
	{if ezhttp_hasvariable( 'sourceKey', 'get' )}
	    {set $sourceKeyGet = ezhttp( 'sourceKey', 'get' )}
	{else}
	    {set $sourceKeyGet = $sourceKey}        
	{/if}
	<div class="box-header">
	    <div class="button-left">
	        <h2 class="context-title">{'Translator / List'|i18n('owtranslate')}</h2>
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
	{include uri='design:translate/toptoolbar.tpl' view='list'}        
	        <div class="content-navigation-childlist">                            
	{if is_set($dataList)}
        {include uri='design:translate/translationtable.tpl'}	   
	{/if}                                        
	        </div>
	        
	{if and(is_set($numberTotal), gt($numberTotal, $limit))}                 
	
		<div class="context-toolbar">
			{include name=navigator
		         uri='design:navigator/google.tpl'
		         page_uri='/translate/list'
		         item_count=$numberTotal
		         view_parameters=$view_parameters
		         item_limit=$limit}
		</div>
	{/if}
	                
	    </div></div></div>
	</div>
	{undef}
{/if}