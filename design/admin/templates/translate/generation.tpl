{*?template charset=UTF-8*}
<div class="box-header">
    <div class="button-left">
        <h2 class="context-title">{'Generation files translation'|i18n('owtranslate')}</h2>
    </div>
    <div class="float-break"></div>
</div>
 
{if is_set($generation)}
    {if $generation}
        <p>{'The generation passed well'|i18n('owtranslate')}</p>
        <p><a href={'/translate/list'|ezurl()}>{'Go to translation list'|i18n('owtranslate')}</a></p>
    {else}
        <p style="color:red">{"A problem arose during the files generation"|i18n('owtranslate')}</p>
    {/if} 
{else} 
    {def
        $exludeExtension = ezini( 'ExcludeExtension', 'extension', 'owtranslate.ini' )
    }
<div class="box-content">    
    <form action={'translate/generation'|ezurl()} method="post">
        <input type="hidden" name="todo" value="chooseExtension" />
        <div class="controlbar" id="controlbar-top"><div class="box-bc"><div class="box-ml">
            <div class="button-left">
                <input type="submit" title="{'Validate generation'|i18n('owtranslate')}" value="{'Send generation'|i18n('owtranslate')}" class="defaultbutton">
                <input type="submit" title="{"Cancel generation"|i18n('owtranslate')}" onclick="return confirm( '{'Do you really want to cancel the generation?'|i18n('owtranslate')}' );" value="{"Cancel generation"|i18n('owtranslate')}" class="button">
            </div>          
        <div class="float-break"></div></div></div></div>
        
        <div class="box-header">
            <h1 class="context-title">&nbsp;{'Choose the extensions to be crossed for the generation'|i18n('owtranslate')}</h1>
            <div class="header-mainline"></div>
        </div>
        
    {foreach $extensionList as $extension}        
        {if $exludeExtension|contains($extension)|not()}
        <div style="margin:10px 5px;">            
            <span style="float:left;margin-right:10px;"><input type="checkbox" value="{$extension}" name="extension[]" /></span>
            <span><label>{$extension}</label></span>
        </div>
        {/if}
    {/foreach}    
        <div class="controlbar">
            <div class="block">
                <input type="submit" title="{'Validate generation'|i18n('owtranslate')}" value="{'Send generation'|i18n('owtranslate')}" class="defaultbutton">
                <input type="submit" title="{"Cancel generation"|i18n('owtranslate')}" onclick="return confirm( '{'Do you really want to cancel the generation?'|i18n('owtranslate')}' );" value="{"Cancel generation"|i18n('owtranslate')}" class="button">
            </div>
        </div>       
    </form>
</div>
    {undef}
{/if}
