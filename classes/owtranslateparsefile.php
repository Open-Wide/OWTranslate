<?php
/**
*	@desc 		class OWTranslateParseFile		
*	@author 	David LE RICHE <david.leriche@openwide.fr>
*	@copyright	2012
*	@version 	1.1
*/	
class OWTranslateParseFile {
	
	public $fileList = array();	
	public $languageList = array();
	public $xmlList = false;
	public $datas = array();
	public $dataValues = array();
	
	public $numberPerPage = 10;
	public $page = 10;	
	public $numberTotal = 0;
	
	public $currentSourceContext = false;
	public $currentNameTranslate = false; 
	public $currentValuesTranslate = array();
	public $futureValuesTranslate = array();
	
	/**
	*	@desc		Constructeur
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@param		array $params => 
	*				contains :  - fileTranslationList (for settings local use in your site)
	*							- nbPage (number total of pages)
	*							- page (current number page)
	*							- sourceKey (key of source context translation's file)
	*							- dataKey (key of source message translation's file)
	*							- translate (future value for source message translation's file)
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/	
	public function __construct($params) {
		if (is_array($params) && isset($params['fileTranslationList'])) {
			$this->setFileListById($params['fileTranslationList']);

			$this->numberPerPage = (isset($params['nbPage']) ? $params['nbPage'] : $this->numberPerPage);
			$this->page = (isset($params['page']) ? $params['page'] : $this->page);

			$this->currentSourceContext = (isset($params['sourceKey']) ? $params['sourceKey'] : $this->currentSourceContext);
			$this->currentNameTranslate = (isset($params['dataKey']) ? $params['dataKey'] : $this->currentNameTranslate);			
			
			$this->futureValuesTranslate = (isset($params['translate']) ? $params['translate'] : $this->futureValuesTranslate);
		} else {
			throw new Exception('Le constructeur doit avoir un tableau en paramétre.');
		}
	} 
	
	/**
	*	@desc		settings local use in your site
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@param		array $fileTranslationList => translation's file list
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function setFileListById($fileTranslationList) {
		$languageListe = eZContentLanguage::fetchList();
		foreach ($languageListe as $language) {
			$this->languageList[$language->ID] = array(
				'locale' 	=> ($language->Locale == 'eng-GB' ? 'eng-GB@override' : $language->Locale),
				'name'		=> $language->Name,
			); 
		}
		foreach ($fileTranslationList as $fileKey => $file) {
			foreach ($this->languageList as $key => $language) {
				if (substr($fileKey, 0,  6) == substr($language['locale'], 0, 6)) {
					$this->fileList[$key] = $file;		
				}
			}
		}
	}
	
	/**
	*	@desc		Get the translation's list (source and values for all languages) you want to see
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array 
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getListToShow() {
		$this->parse();		
		$this->sortTranslationListFile();
		$this->getTranslationValuesToFileList();
		return $this->datas;
	}
	
	/**
	*	@desc		xml parse for all translation's file
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function parse() {
		foreach ($this->fileList as $key => $file) {
			if (file_exists($file)) {
				try {
					$this->xmlList[$key] = simplexml_load_file($file);
				} catch (Exception $e) {
					eZLog::write($e, 'owtranslate.log');
				}
			}
		}
	}
	
	/**
	*	@desc		Get the  translation source list you want to see
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function sortTranslationListFile() {	
		$this->datas = array();	
		$offset = ($this->page * $this->numberPerPage) - $this->numberPerPage;
		$compteur = 0;
		$countMessagePerContext = 0;
		
		// get the main locale key
		$mainLocaleKey = $this->getLanguageIdByLocale(eZINI::instance('owtranslate.ini')->variable( 'MainLocale', 'locale'));
				
		foreach($this->xmlList[$mainLocaleKey] as $context) {
			if ($compteur >= ($this->numberPerPage * $this->page)) {
				break;	
			}
			if ($this->currentSourceContext && (string)$context->name != $this->currentSourceContext) {
				continue;
			}
			$countMessagePerContext += count($context->message);
			if ($offset < $countMessagePerContext) {
				foreach ($context->message as $message) {
					if ($this->currentNameTranslate && (string)$message->source != $this->currentNameTranslate) {						
						continue;
					}
					if ($compteur >= $offset && $compteur < ($this->numberPerPage * $this->page)) {
						$this->datas[(string)$context->name][] = (string)$message->source;
					}
					$compteur++;
					if ($compteur >= ($this->numberPerPage * $this->page)) {
						break;
					}
				}
			} else {
				$compteur = $countMessagePerContext;
			}
		}	
	}
	
	/**
	*	@desc		Get the translation values corresponds to the  source list you want to see
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getTranslationValuesToFileList() {		
		foreach($this->xmlList as $localeKey => $xml) {
			foreach ($this->datas as $sourceKey => $messageList) {
				if ($this->currentSourceContext && (string)$sourceKey != $this->currentSourceContext) {
					continue;
				}
				foreach ($messageList as $message) {
					if ($this->currentNameTranslate && (string)$message != $this->currentNameTranslate) {
						continue;
					}
					try {
						$query = "//context[name=".(strpos($sourceKey, "'") === false ? "'$sourceKey'" : "\"$sourceKey\"")."]/message[source=".(strpos($message, "'") === false ? "'$message'" : "\"$message\"")."]/translation";
						$element = $xml->xpath($query);
						$this->dataValues[$localeKey][$message] = (string)$element[0];
					} catch (Exception $e) {
						eZLog::write($e, 'owtranslate.log');
					}
				}
			}
		}
	}
	
	/**
	*	@desc		Get the total number of translation
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		int
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getNumberTranslation() {
		// get the main locale key
		$mainLocaleKey = $this->getLanguageIdByLocale(eZINI::instance('owtranslate.ini')->variable( 'MainLocale', 'locale'));
		if ($this->numberTotal == 0) {
			if ($this->currentNameTranslate) {
				$this->numberTotal = count($this->datas);
			} else {
				foreach($this->xmlList[$mainLocaleKey] as $context) {
					if ($this->currentSourceContext && (string)$context->name != $this->currentSourceContext) {
						continue;
					}
					$this->numberTotal += count($context->message);
				}
			}
		}
		return $this->numberTotal;
	}
	
	/**
	*	@desc		Get all the translation for one source message for edit
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getTranslationForEdit() {
		$this->parse();
		$query = "//context[name=".(strpos($this->currentSourceContext, "'") === false ? "'$this->currentSourceContext'" : "\"$this->currentSourceContext\"")."]/message[source=".(strpos($this->currentNameTranslate, "'") === false ? "'$this->currentNameTranslate'" : "\"$this->currentNameTranslate\"")."]/translation";
		foreach($this->xmlList as $keyXml => $xml) {
			try {
				$element = $xml->xpath($query);
				$this->currentValuesTranslate[$keyXml] = (string)$element[0];
			} catch (Exception $e) {
				eZLog::write($e, 'owtranslate.log');
			}
		}
		return $this->currentValuesTranslate;
	}	
	
	/**
	*	@desc		Set the translation value for one or all language found for message source
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		bool
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function setTranslation() {
		$returnValue = false;
		$this->parse();
		$query = "//context[name=".(strpos($this->currentSourceContext, "'") === false ? "'$this->currentSourceContext'" : "\"$this->currentSourceContext\"")."]/message[source=".(strpos($this->currentNameTranslate, "'") === false ? "'$this->currentNameTranslate'" : "\"$this->currentNameTranslate\"")."]/translation";
		foreach($this->xmlList as $keyXml => $xml) {
			if (isset($this->futureValuesTranslate[$keyXml])) {
				try {
					$element = $xml->xpath($query);
					$newValue = $this->futureValuesTranslate[$keyXml];
					$element[0][0] = $newValue;
					if (file_exists($this->fileList[$keyXml])) {
						$returnValue = true;
						$fp = fopen($this->fileList[$keyXml], 'w');
						fwrite($fp, $xml->asXML());
						fclose($fp);
					}
				} catch (Exception $e) {
					eZLog::write($e, 'owtranslate.log');
				}
			}
		}
		return $returnValue;
	}
	
	/**
	*	@desc		Get the translation's list you want to search
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getDataToSearch() {
		$datasToSearch = array(
			'context' 		=> array(),
			'translation' 	=> array()
		);	
		
		// get the main locale key
		$mainLocaleKey = $this->getLanguageIdByLocale(eZINI::instance('owtranslate.ini')->variable( 'MainLocale', 'locale'));
				
		foreach($this->xmlList[$mainLocaleKey] as $context) {
			if (!in_array((string)$context->name, $datasToSearch['context'])) {
				$datasToSearch['context'][] = (string)$context->name; 
			}
		}
		
		return $datasToSearch;
	}
	
	/**
	*	@desc		Get values translation'list 
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getDataValues() {
		return $this->dataValues;
	}
	
	/**
	*	@desc		Get the language name by an id
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		string
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getLanguageNameById($id) {
		return $this->languageList[$id]['name'];
	}
	
	/**
	*	@desc		Get the language's id by local
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		int
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getLanguageIdByLocale($locale) {
		$language = eZContentLanguage::fetchByLocale($locale);
		return $language->ID;
	}
}
	
?>