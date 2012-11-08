<?php
$ini = eZINI::instance();
$ini->setVariable( 'DebugSettings', 'DebugOutput', 'disabled' );
$ini->setVariable( 'TemplateSettings', 'Debug', 'disabled' );
$ini->setVariable('DebugSettings', 'DebugRedirection', 'disabled' );
$ini->setVariable('TemplateSettings', 'ShowXHTMLCode', 'disabled' );
$ini->setVariable('TemplateSettings', 'ShowUsedTemplates', 'disabled' );
$ini->setVariable('DatabaseSettings', 'SQLOutput', 'disabled' );

$Result = OWTranslateModuleView::ajaxEditTranslation();
?>