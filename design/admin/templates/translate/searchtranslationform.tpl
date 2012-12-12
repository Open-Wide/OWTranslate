{*?template charset=UTF-8*}
<fieldset>
    <form action={'translate/search'|ezurl()} method="get">
        <input type="hidden" name="todo" value="search" />   
        <div class="fields">
            <label>{'Choose context'|i18n('owtranslate')}&nbsp;:&nbsp;</label>
            <select name="sourceKey">
                <option value="">{'Choose context'|i18n('owtranslate')}</option>
{foreach $contextList as $context}
                <option title="{$context}" value="{$context}" {if eq($sourceKeyGet, $context)}selected{/if}>{$context|shorten(30)}</option>
{/foreach}                
            </select>
        </div>
        <div class="fields">
            <label>{'Choose language'|i18n('owtranslate')}&nbsp;:&nbsp;</label>
            <select name="locale">
                <option value="">{'Choose language'|i18n('owtranslate')}</option>
{foreach $languageList as $language}
                <option style="background:url({concat('/share/icons/flags/', $language.locale|extract( 0, 6 ), '.gif')}) no-repeat;padding-left:25px;" title="{$language.name}" value="{$language.locale}" {if eq($localeGet, $language.locale)}selected{/if}>{$language.name}</option>
{/foreach}                
            </select>
        </div>
        <div class="fields"><label>{'Search translation key'|i18n('owtranslate')}&nbsp;:&nbsp;</label><input type="text" name="dataKey" value="{cond($dataKeyGet, $dataKeyGet)}" /></div>
        <div class="fields">
            <input type="submit" title="{'Search'|i18n('owtranslate')}" value="{'Search'|i18n('owtranslate')}" class="defaultbutton">
            <a href={'translate/list'|ezurl()} class="defaultbutton">{'Reset'|i18n('owtranslate')}</a>
        </div>  
    </form>
</fieldset>