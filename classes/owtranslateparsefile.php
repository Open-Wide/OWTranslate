<?php
	
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
	
	public function setFileListById($fileTranslationList) {
		$languageListe = eZContentLanguage::fetchList();
		foreach ($languageListe as $language) {
			$this->languageList[$language->ID] = array(
				'locale' 	=> $language->Locale,
				'name'		=> $language->Name,
			); 
		}
		foreach ($fileTranslationList as $fileKey => $file) {
			foreach ($this->languageList as $key => $language) {
				if (substr($fileKey, 0,  6) == $language['locale']) {
					$this->fileList[$key] = $file;		
				}
			}
		}
	}
	
	public function getListToShow() {
		$this->parse();		
		$this->sortTranslationListFile();
		if (count($this->languageList) <= 5) {
			$this->getTranslationValuesToFileList();
		}
		return $this->datas;
	}
	
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
			$countMessagePerContext += count($context->message);
			if ($offset < $countMessagePerContext) {
				foreach ($context->message as $message) {
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
	
	public function getTranslationValuesToFileList() {		
		foreach($this->xmlList as $localeKey => $xml) {
			foreach ($this->datas as $sourceKey => $messageList) {
				foreach ($messageList as $message) {
					try {
						$xpath = "//context[name='".$sourceKey."']/message[source='".$message."']/translation";
						$element = $xml->xpath($xpath);
						$this->dataValues[$localeKey][$message] = (string)$element[0];
					} catch (Exception $e) {
						eZLog::write($e, 'owtranslate.log');
					}
				}
			}
		}
	}
	
	public function getNumberTranslation() {
		// get the main locale key
		$mainLocaleKey = $this->getLanguageIdByLocale(eZINI::instance('owtranslate.ini')->variable( 'MainLocale', 'locale'));
		if ($this->numberTotal == 0) {
			foreach($this->xmlList[$mainLocaleKey] as $context) {
				$this->numberTotal += count($context->message);	
			}
		}
		return $this->numberTotal;
	}
	
	public function getTranslationForEdit() {
		$this->parse();
		$xpath = "//context[name='".$this->currentSourceContext."']/message[source='".$this->currentNameTranslate."']/translation";
		foreach($this->xmlList as $keyXml => $xml) {
			try {
				$element = $xml->xpath($xpath);
				$this->currentValuesTranslate[$keyXml] = (string)$element[0];
			} catch (Exception $e) {
				eZLog::write($e, 'owtranslate.log');
			}
		}
		return $this->currentValuesTranslate;
	}	
	
	public function setTranslation() {
		$returnValue = false;
		$this->parse();
		$xpath = "//context[name='".$this->currentSourceContext."']/message[source='".$this->currentNameTranslate."']/translation";
		foreach($this->xmlList as $keyXml => $xml) {
			if (isset($this->futureValuesTranslate[$keyXml])) {
				try {
					$element = $xml->xpath($xpath);
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
	
	public function getDataValues() {
		return $this->dataValues;
	}
	
	public function getLanguageNameById($id) {
		return $this->languageList[$id]['name'];
	}
	
	public function getLanguageIdByLocale($locale) {
		$language = eZContentLanguage::fetchByLocale($locale);
		return $language->ID;
	}
	
	public static function d($string) {
		echo '<pre>';
		var_dump($string);
		echo '</pre>';
	}
	
	public static function dd($string) {
		echo '<pre>';
		var_dump($string);
		echo '</pre>';
		exit;
	}
}
	
?>