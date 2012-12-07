<?php
/**
*	@desc 		class OWTranslateModuleView		
*	@author 	David LE RICHE <david.leriche@openwide.fr>
*	@copyright	2012
*	@version 	1.1
*/
class OWTranslateModuleView {
	
	/**
	*	@desc		Return the view to the module
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@param		string $view => the template you want  
	*				mixed $tpl => ezTemplate class loaded
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/	
	public static function getView($view, $tpl=false) {
		if (!$tpl) {
			$tpl = eZTemplate::factory();
		}
		$Result = array();
		$Result['content'] = $tpl->fetch( 'design:translate/'.$view.'.tpl' ); 
		$Result['left_menu'] = "design:translate/leftmenu.tpl"; 
 
		$Result['path'] = array( array( 
			'url' => 'translate/'.$view,
    		'text' => 'Translations Generator' 
		));
		return $Result;
		
	}
	
	/**
	*	@desc		The view : list
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@param		array $Params => view parameter array 
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/	
	public static function translationList($Params) {
		// get the list of translation file
		$fileTranslationList = self::getTranslationListFile();
		
		// parse file
		$parseFileParams = array(
			'fileTranslationList'	=> $fileTranslationList,
			'nbPage'				=> isset($Params['UserParameters']['nbPage']) ? $Params['UserParameters']['nbPage'] : '10', 
			'page'					=> isset($Params['UserParameters']['page']) ? $Params['UserParameters']['page'] : '1',
			'sourceKey'				=> (isset($Params['UserParameters']['sourceKey']) ? $Params['UserParameters']['sourceKey'] : (isset($_GET['sourceKey']) && $_GET['sourceKey'] != '' ? $_GET['sourceKey'] : '')),
			'dataKey'				=> isset($_GET['dataKey']) && $_GET['dataKey'] != '' ? $_GET['dataKey'] : '',
		);
		
		try {
			$parseFile = new OWTranslateParseFile($parseFileParams);
			$dataList = $parseFile->getListToShow();	
			$dataValues = $parseFile->getDataValues();	
			
			// get data for search
			$dataToSearch = $parseFile->getDataToSearch();		
			
			// return the view
			$tpl = eZTemplate::factory();
			$tpl->setVariable('dataList', $dataList);
			$tpl->setVariable('dataValues', $dataValues);
			$tpl->setVariable('languageList', $parseFile->languageList);
			$tpl->setVariable('nbPage', $parseFileParams['nbPage']);
			$tpl->setVariable('page', $parseFileParams['page']);
			$tpl->setVariable('sourceKey', $parseFileParams['sourceKey']);
			$tpl->setVariable('numberTotal', $parseFile->getNumberTranslation());
			$tpl->setVariable('dataToSearch', $dataToSearch);
			$tpl->setVariable('locale', (isset($Params['UserParameters']['locale']) ? $Params['UserParameters']['locale'] : (isset($_GET['locale']) && $_GET['locale'] != '' ? $_GET['locale'] : false)));
			$Result = self::getView('list', $tpl);
			
			return $Result;
		} catch (Exception $e) {
			eZLog::write($e, 'owtranslate.log');
		}
	} 
	
	/**
	*	@desc		The view : edit
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@param		array $Params => view parameter array 
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/	
	public function editTranslation($Params) {
		// get the list of translation file
		$fileTranslationList = self::getTranslationListFile();	
		
		if (isset($_POST['todo']) && $_POST['todo'] == 'validEdit') {
			$params = array();
			unset($_POST['todo']);
			foreach ($_POST as $key => $value) {
				$params[$key] = $value;	
			}		
			$params['fileTranslationList'] = $fileTranslationList;
			
			try {
				$parseFile = new OWTranslateParseFile($params);
				$parseFile->setTranslation();
				eZHTTPTool::redirect('/translate/list');
			} catch (Exception $e) {
				eZLog::write($e, 'owtranslate.log');
			}
		} else {
			// parse file
			$parseFileParams = array(
				'fileTranslationList'	=> $fileTranslationList,
				'sourceKey'				=> isset($Params['UserParameters']['sourceKey']) ? $Params['UserParameters']['sourceKey'] : '',
				'dataKey'				=> isset($Params['UserParameters']['dataKey']) ? $Params['UserParameters']['dataKey'] : '',
			);
			
			try {
				$parseFile = new OWTranslateParseFile($parseFileParams);
				$dataforEdit = $parseFile->getTranslationForEdit();	
				
				
				// return the view
				$tpl = eZTemplate::factory();
				$tpl->setVariable('dataforEdit', $dataforEdit);
				$tpl->setVariable('sourceKey', $parseFileParams['sourceKey']);
				$tpl->setVariable('dataKey', $parseFileParams['dataKey']);
				$tpl->setVariable('languageList', $parseFile->languageList);
				$Result = self::getView('edit', $tpl);
				
				return $Result;
			} catch (Exception $e) {
				eZLog::write($e, 'owtranslate.log');
			}
		}
	}
	
	/**
	*	@desc		The view : generation
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@param		array $params => view parameter array 
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/	
	public static function generateTranslation($Params) {
		$tpl = eZTemplate::factory();
		
		if (isset($_POST['todo']) && $_POST['todo'] == 'chooseExtension') {
			try {
				$tabFileDir = array();
				$tfGene = new OWTranslateTranslationFileGenerator();
				foreach ($_POST['extension'] as $extension) {
				    $tabFileDir = array_merge($tabFileDir, $tfGene->scanDirectory(eZExtension::baseDirectory().'/'.$extension));
				    $tfGene->tabFile = array_merge($tfGene->tabFile, $tabFileDir);			    			     	
				}
				$tfGene->analyseFiles();			    
			    $isGenerate = $tfGene->generateXML();
				
				$tpl->setVariable('generation', ($isGenerate ? true : false));
			} catch (Exception $e) {
				eZLog::write($e, 'owtranslate.log');
			}
			
		} else {
			$tpl->setVariable('extensionList', eZExtension::activeExtensions());
		}
		// return the view
		$Result = self::getView('generation', $tpl);
		return $Result;
	}
	
	/**
	*	@desc		The view : ajax_edit
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/	
	public static function ajaxEditTranslation() {
		if (isset($_POST['id']) && $_POST['value']) {
			$fileTranslationList = self::getTranslationListFile();
			
			$id = explode('|', $_POST['id']);
			$localeKey = $id[0];
			$sourceKey = $id[1];
			$dataKey = $id[2]; 
			
			$params = array(
				'fileTranslationList' 	=> $fileTranslationList,
				'sourceKey'			  	=> $sourceKey,
				'dataKey'			  	=> $dataKey,
				'translate'				=> array($localeKey => $_POST['value'])
			);
			
			try {
				$parseFile = new OWTranslateParseFile($params);
				if ($parseFile->setTranslation()) {
					echo $_POST['value'];
				} else {
					$currentTranslation = $parseFile->getTranslationForEdit();					
					echo $currentTranslation[$localeKey];
				}
				
				$Result = array('pagelayout' => false);
				return $Result;
			} catch (Exception $e) {
				eZLog::write($e, 'owtranslate.log');
			}
		} else {
			$Result = array('pagelayout' => false);
			return $Result;
		}
	}
	
	/**
	*	@desc		Get the file list translation
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/	
	public static function getTranslationListFile() {
		$extensionIni = eZINI::instance('owtranslate.ini');
		$directoryMainExtension = $extensionIni->variable( 'MainExtension', 'directory');
		$rootExtensionDirectory = eZExtension::baseDirectory();
		$baseDirectory = $rootExtensionDirectory.'/'.$directoryMainExtension.'/translations'; 
		$dirTranslationList = eZDir::findSubitems($baseDirectory, false, true);
		$fileTranslationList = array();
		
		foreach ($dirTranslationList as $dir) {
			$locale = substr($dir, (strripos($dir, '/') +1));
			$fileList = eZDir::findSubitems($dir, false, true);
			foreach ($fileList as $file) {
				$fileTranslationList[$locale] = $file;
			}
		}
		return $fileTranslationList;
	}
}

?>