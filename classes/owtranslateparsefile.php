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
	public $mainLocaleKey = false;
	
	public $numberPerPage = 25;
	public $offset = 0;
	public $compteur = 0;
	public $countMessagePerContext = 0;
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
	*							- limit (number total of pages)
	*							- offset
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

			$this->numberPerPage = (isset($params['limit']) ? $params['limit'] : eZINI::instance('owtranslate.ini')->variable( 'NumberPerPage', 'default'));
			$this->offset = (isset($params['offset']) ? $params['offset'] : $this->offset);

			$this->currentSourceContext = (isset($params['sourceKey']) ? $params['sourceKey'] : $this->currentSourceContext);
			$this->currentNameTranslate = (isset($params['dataKey']) ? $params['dataKey'] : $this->currentNameTranslate);			
			
			$this->futureValuesTranslate = (isset($params['translate']) ? $params['translate'] : $this->futureValuesTranslate);
			
			// get the main locale key
			$this->mainLocaleKey = $this->getLanguageIdByLocale(eZINI::instance('owtranslate.ini')->variable( 'MainLocale', 'locale'));
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
		$localeOverride = eZINI::instance('owtranslate.ini')->variable( 'LocaleOverride', 'locale');
		foreach ($languageListe as $language) {
			$this->languageList[$language->ID] = array(
				'locale' 	=> (array_key_exists($language->Locale, $localeOverride) ? $localeOverride[$language->Locale] : $language->Locale),
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
		$this->dataValues = array();
		$this->compteur = 0;
		$this->countMessagePerContext = 0;
        
        if ($this->currentSourceContext) {
        	try {
				$query = "//context[name=".(strpos($this->currentSourceContext, "'") === false ? "'$this->currentSourceContext'" : "\"$this->currentSourceContext\"")."]";
				if ($context = $this->xmlList[$this->mainLocaleKey]->xpath($query)) {
					$context = $context[0];
					$this->getListByContext($context);
				}
			} catch (Exception $e) {
				eZLog::write($e, 'owtranslate.log');
			}
        } else {
			foreach($this->xmlList[$this->mainLocaleKey] as $context) {
				if ($this->compteur >= ($this->offset + $this->numberPerPage)) {
					break;	
				}
				$this->getListByContext($context);
			}
        }	
        $this->getListValuesByContext();
	}
	
	/**
	*	@desc		Get list message by source context
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getListByContext($context) {
		
		$this->countMessagePerContext += count($context->message);
		if ($this->offset < $this->countMessagePerContext) {
			foreach ($context->message as $message) {
				if ($this->currentNameTranslate) {
					if (strtolower((string)$message->source) != strtolower($this->currentNameTranslate) && strpos(strtolower((string)$message->source), strtolower($this->currentNameTranslate)) === false) {
						continue;
					}	
				}
				if ($this->compteur >= $this->offset && $this->compteur < ($this->offset + $this->numberPerPage)) {
					$this->datas[(string)$context->name][] = (string)$message->source;
				}
				$this->compteur++;
				if ($this->compteur >= ($this->offset + $this->numberPerPage)) {
					break;
				}
			}
		} else {
			$this->compteur = $this->countMessagePerContext;
		}
	}
	
	/**
	*	@desc		Get the translation values corresponds to the  source list you want to see
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getListValuesByContext() {
		// search
		foreach($this->xmlList as $localeKey => $xml) {
			if ($this->currentNameTranslate) {
				try {
					$query = "//context/message/source/text()[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), ".strtolower((strpos($this->currentNameTranslate, "'") === false ? "'$this->currentNameTranslate'" : "\"$this->currentNameTranslate\"")).")]/../..";
					if ($elements = $xml->xpath($query)) {
						foreach ($elements as $element) {
							$this->dataValues[$localeKey][(string)$element->source] = (string)$element->translation;
						} 
					}
				} catch (Exception $e) {
					eZLog::write($e, 'owtranslate.log');
				}
			} else {
				// list
				foreach ($this->datas as $sourceKey => $messageList) {
					foreach ($messageList as $message) {
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
	}
	
	/**
	*	@desc		Get the total number of translation
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		int
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getNumberTranslation() {
		if ($this->numberTotal == 0) {
			if ($this->currentNameTranslate) {
				foreach ($this->datas as $data) {
					$this->numberTotal += count($data);	
				}
			} else {
				foreach($this->xmlList[$this->mainLocaleKey] as $context) {
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
	*	@desc		Get All context
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/
	public function getAllContext() {
		$contextList = array();
				
		foreach($this->xmlList[$this->mainLocaleKey] as $context) {
			if (!in_array((string)$context->name, $contextList)) {
				$contextList[] = (string)$context->name; 
			}
		}
		sort($contextList);
		return $contextList;
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