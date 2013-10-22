<?php

$module = array( 'name' => 'translate' );
 
$ViewList = array();
$ViewList['list'] = array( 'script' => 'list.php',
									'default_navigation_part' => 'translatenavigationpart',
									'params' => array( 'language'),
                               		'functions' => array( 'read' ));
                               		                     		
$ViewList['generation'] = array( 'script' => 'generation.php',
									'default_navigation_part' => 'translatenavigationpart',
                               		'functions' => array( 'edit' ));
                               		
$ViewList['export'] = array( 'script' => 'export.php',
									'default_navigation_part' => 'translatenavigationpart',
                               		'functions' => array( 'read' ));
                               		                               		
$ViewList['edit'] = array( 'script' => 'edit.php',
									'default_navigation_part' => 'translatenavigationpart',
                               		'functions' => array( 'read' ));                               		
                               		
$ViewList['ajax_edit'] = array( 'script' => 'ajax_edit.php',
									'default_navigation_part' => 'translatenavigationpart',
                               		'functions' => array( 'read' ));
                               		
$ViewList['search'] = array( 'script' => 'search.php',
									'default_navigation_part' => 'translatenavigationpart',
                               		'functions' => array( 'read' ));                                 		                               		

$FunctionList = array(); 
$FunctionList['read'] = array();
$FunctionList['edit'] = array();


?>
