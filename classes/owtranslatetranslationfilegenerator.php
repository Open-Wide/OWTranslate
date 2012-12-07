<?php

class OWTranslateTranslationFileGenerator {
    
    public $tabPath;
    public $tabFile;
    public $languageList;
    
    private $tabKey;
    
    public function __construct() {
        try {
            $this->tabPath = array();
            $this->tabFile = array();
            $this->tabKey = array();
        } catch (Exception $e) {
            echo $e;
        }
    }
    
    public function addPath($path = null) {
        if ($path === null) {
            throw new Exception('A path can not be null !');
        } else {
            if (is_string($path)) {
                $this->tabPath[] = $path;
            }
        }
    }
    
    public function addFile($file = null) {
        if ($file === null) {
            throw new Exception('A file name can not be null !');
        } else {
            if (is_string($file)) {
                $this->tabFile[] = $file;
            }
        }
    }
    
    public function getTabPath() {
        $listPath = '';
        $i = 1;
        foreach ($this->tabPath as $path) {
            $listPath .= $path . ($i != sizeof($this->tabPath) ? "\n" : '');
            $i++;
        }
        return $listPath;   
    }
    
    public function getTabFile() {
        $listFile = '';
        $i = 1;
        foreach ($this->tabFile as $file) {
            $listFile .= $file . ($i != sizeof($this->tabFile) ? "\n" : '');
            $i++;
        }
        return $listFile;
    }
    
    public function analyseFiles() {
        $finalMatches = array();
        $tabTrad = array();
        foreach($this->tabFile as $file) {
            $matches = array();
            $fp = fopen($file,"r");
            while(!feof($fp)) {
                $buffer = fgets($fp);
                if (preg_match_all('#\{[\'"]([^\}]+)[\'"]\|i18n\([\'"]([^}]+)[\'"]\)\}#', $buffer, $tmpMatches)) {
                    $matches['template'][] = $tmpMatches;
                } else {
                	if (preg_match_all('#ezpI18n::tr\( ?[\'"]([^\}]+)[\'"] ?, ?[\'"]([^\}]+)[\'"] ?\)#', $buffer, $tmpMatches)) {
	                	$matches['php'][] = $tmpMatches;
                	}
                }
            }
            fclose($fp);
            $finalMatches = array_merge($finalMatches, $matches);            
            foreach($finalMatches as $fileType => $matchList) {
            	foreach ($matchList as $match) {					            	
	                $tradKeys = ($fileType == 'template'  ? $match[2] : $match[1]);
	                $tradValues = ($fileType == 'template'  ? $match[1] : $match[2]);
	                foreach ($tradKeys as $key => $value) {
	                    if (!isset($tabTrad[$value])) {	                    	
	                        $tabTrad[$value] = array();
	                    }
	                }
	                foreach ($tradKeys as $key => $value) {
	                    if (!in_array($tradValues[$key], $tabTrad[$value])) {
	                        $tabTrad[$value][] = $tradValues[$key];
	                    }
	                }
            	}
            }
        }
        $this->tabKey = $tabTrad;
        return $tabTrad;
    }
    
    public function generateXML() {
    	$this->languageList = eZContentLanguage::fetchList();
    	$directoryMainExtension = eZINI::instance('owtranslate.ini')->variable( 'MainExtension', 'directory');		
		$baseDirectory = eZExtension::baseDirectory().'/'.$directoryMainExtension.'/translations';
    	$this->createLocaleDirIfNotExist($baseDirectory);    
        
        // verification file translation exist
        foreach ($this->languageList as $language) {
        	if ($language->Locale == 'eng-GB') {
    			$locale = 'eng-GB@override';	
    		} else {
    			$locale = $language->Locale;
    		}
        	if (file_exists($baseDirectory.'/'.$locale.'/translation.ts')) {
				$saveXml = $this->addTranslationIfNotExist($baseDirectory.'/'.$locale.'/translation.ts');			
        	} else {        		
		        $saveXml = $this->addTranslationFile($baseDirectory.'/'.$locale.'/translation.ts');
        	}
        } 
        return $saveXml;
    }
    
    public function addTranslationIfNotExist($file) {
    	$tsFile = new DOMDocument();
		$tsFile->load($file);
		
    	$xpath = new DOMXpath($tsFile);
    	$ts = $tsFile->documentElement;
    	
    	foreach($this->tabKey as $sourceName => $tabElement) {    
            foreach ($tabElement as $element) {
            	if (!$xpath->query("//context[name='".$sourceName."']/message[source='".$element."']")->item(0)) {
            		
            		$message = $tsFile->createElement('message');
                	$source = $tsFile->createElement('source', $element);
                	$translation = $tsFile->createElement('translation');
            		
            		if (!$xpath->query("//context[name='".$sourceName."']")->item(0)) {
            			$context = $tsFile->createElement('context');
            			$name = $tsFile->createELement('name', $sourceName);
            			
            			$context->appendChild($name);
	                	$message->appendChild($source);
	                	$message->appendChild($translation);
	                	$context->appendChild($message);
	                	$ts->appendChild($context);
	                	
            		} else {
            			$name = $xpath->query("//context[name='".$sourceName."']")->item(0);
            			$context = $xpath->query("//context[name='".$sourceName."']/..")->item(0);
	                	
	                	$message->appendChild($source);
	                	$message->appendChild($translation);
	                	$name->appendChild($message);
            		}
            	}            	
            }
        }   
        try {
        	if ($unlinkFile = unlink($file)) {
        		$saveXml = $tsFile->save($file, LIBXML_NOEMPTYTAG);
        	}
        } catch (exception $e) {
        	echo $e;
        }
       	return $saveXml;
    }
    
    public function addTranslationFile($file) {
    	
		$doctype = DOMImplementation::createDocumentType("TS"); 
        $tsFile = DOMImplementation::createDocument(null, 'TS', $doctype);
        $tsFile->encoding = 'UTF-8';
        $tsFile->formatOutput = true;
    	
    	$ts = $tsFile->documentElement;
    	foreach($this->tabKey as $sourceName => $tabElement) {          
    		$context = $tsFile->createElement('context');  
            $name = $tsFile->createELement('name', $sourceName);            
            $context->appendChild($name);
            foreach ($tabElement as $element) {
                $message = $tsFile->createElement('message');
                $source = $tsFile->createElement('source', $element);
                $translation = $tsFile->createElement('translation');
                
                $message->appendChild($source);
                $message->appendChild($translation);
                $context->appendChild($message);             
            }
            $ts->appendChild($context);
        }     
        $saveXml = $tsFile->save($file, LIBXML_NOEMPTYTAG);
        return $saveXml;
    }
    
    public function createLocaleDirIfNotExist($baseDirectory) {
		if (!is_dir($baseDirectory)) {
			eZDir::mkdir($baseDirectory, octdec('0775'));
		}
    	foreach ($this->languageList as $language) {
    		if ($language->Locale == 'eng-GB') {
    			$locale = 'eng-GB@override';	
    		} else {
    			$locale = $language->Locale;
    		}
    		if (!is_dir($baseDirectory.'/'.$locale)) {
    			eZDir::mkdir($baseDirectory.'/'.$locale, octdec('0775'));
    		}
    	}
    }
    
    public function scanDirectory($directory = null) {
        if ($directory === null) {
            throw new Exception('Directory param can not be null');
        }
        
        try {
            $openDirectory = opendir($directory);
            $tabFile = array();
            $tabExclude = array(
                '.',
                '..',
                '.svn',
                'stylesheets',
                'images',
                'javascript',
                'flash',
            );
            while($element = readdir($openDirectory)) {
                $path = $directory .'/'. $element;   
                if (is_dir($path) && !in_array($element, $tabExclude)) {
                    $tabFile = array_merge($tabFile, self::scanDirectory($path));
                } else {
                    if (preg_match('#\.tpl#', $element)) {
                        $tabFile[] = $path;
                    } elseif (preg_match('#^((?!ini).)*.php#', $element)) {
                    	$tabFile[] = $path;
                    }
                }
            }
        } catch (Exception $e) {
            echo $e;
        }
        return $tabFile;
    }
}
?>