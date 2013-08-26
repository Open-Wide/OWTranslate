<?php
/**
*	@desc 		class OWTranslateTranslationFileGenerator		
*	@author 	David LE RICHE <david.leriche@openwide.fr>
*	@copyright	2012
*	@version 	1.1
*/
class OWTranslateTranslationFileGenerator {
    
    public $tabPath;
    public $tabFile;
    public $languageList;
    
    private $tabKey;
    
    /**
	*	@desc		Constructeur
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/	
    public function __construct() {
        try {
            $this->tabPath = array();
            $this->tabFile = array();
            $this->tabKey = array();
        } catch (Exception $e) {
            echo $e;
        }
    }
    
    /**
	*	@desc		Add path to the list 
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@params		string	$path => path for check files
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/
    public function addPath($path = null) {
        if ($path === null) {
            throw new Exception('A path can not be null !');
        } else {
            if (is_string($path)) {
                $this->tabPath[] = $path;
            }
        }
    }
    
    /**
	*	@desc		Add file to the list 
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@params		string	$file => file for checing
	*	@return		void
	*	@copyright	2012
	*	@version 	1.1
	*/
    public function addFile($file = null) {
        if ($file === null) {
            throw new Exception('A file name can not be null !');
        } else {
            if (is_string($file)) {
                $this->tabFile[] = $file;
            }
        }
    }
    
    /**
	*	@desc		Get the list of path
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/
    public function getTabPath() {
        $listPath = '';
        $i = 1;
        foreach ($this->tabPath as $path) {
            $listPath .= $path . ($i != sizeof($this->tabPath) ? "\n" : '');
            $i++;
        }
        return $listPath;   
    }
    
    /**
	*	@desc		Get the list of file
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/
    public function getTabFile() {
        $listFile = '';
        $i = 1;
        foreach ($this->tabFile as $file) {
            $listFile .= $file . ($i != sizeof($this->tabFile) ? "\n" : '');
            $i++;
        }
        return $listFile;
    }
    
    /**
	*	@desc		Analyse all files to find translation
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		array
	*	@copyright	2012
	*	@version 	1.1
	*/
    public function analyseFiles() {
        $finalMatches = array();
        $tabTrad = array();
        foreach($this->tabFile as $file) {
            $matches = array();
            $fp = fopen($file,"r");
            while(!feof($fp)) {
                $buffer = fgets($fp);
                if (preg_match_all('#\{[\'"]([^\}]+)[\'"]\|i18n\(\s*[\'"]([^}]+)[\'"]\s*\)\}#', $buffer, $tmpMatches)) {
                    $matches['template'][] = $tmpMatches;
                } else {
                	if (preg_match_all('#ezpI18n::tr\([ ]*[\'"]([^\)\}]+)[\'"][ ]*,[ ]*[\'"]([^\)\}]+)[\'"][ ]*\)#', $buffer, $tmpMatches)) {
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
    
    /**
	*	@desc		Generate xml file for all locale on your site with all translation found
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@return		bool
	*	@copyright	2012
	*	@version 	1.1
	*/
    public function generateXML() {
    	$this->languageList = eZContentLanguage::fetchList();
    	$directoryMainExtension = eZINI::instance('owtranslate.ini')->variable( 'MainExtension', 'directory');		
		$baseDirectory = eZExtension::baseDirectory().'/'.$directoryMainExtension.'/translations';
    	$this->createLocaleDirIfNotExist($baseDirectory);
    	
        $localeOverride = eZINI::instance('owtranslate.ini')->variable( 'LocaleOverride', 'locale');
        
        // verification file translation exist
        foreach ($this->languageList as $language) {
        	$locale = (array_key_exists($language->Locale, $localeOverride) ? $localeOverride[$language->Locale] : $language->Locale);

        	if (file_exists($baseDirectory.'/'.$locale.'/translation.ts')) {
				$saveXml = $this->addTranslationIfNotExist($baseDirectory.'/'.$locale.'/translation.ts');
        	} else {
		        $saveXml = $this->addTranslationFile($baseDirectory.'/'.$locale.'/translation.ts');
        	}
        }
        return $saveXml;
    }

    /**
	*	@desc		Add the new translation found in the existing file of translation
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@params		string	$file => the file where the translation is adding
	*	@return		bool
	*	@copyright	2012
	*	@version 	1.1
	*/
    public function addTranslationIfNotExist($file) {
    	$tsFile = new DOMDocument();
		$tsFile->load($file);

    	$xpath = new DOMXpath($tsFile);
    	$ts = $tsFile->documentElement;

    	foreach($this->tabKey as $sourceName => $tabElement) {
            foreach ($tabElement as $element) {
            	$query = "//context[name=".(strpos($sourceName, "'") === false ? "'$sourceName'" : "\"$sourceName\"")."]/message[source=".(strpos($element, "'") === false ? "'$element'" : "\"$element\"")."]";
            	try {
            		if ($xpath->query($query) && !$xpath->query($query)->item(0)) {

	            		$querySourceName = "//context[name=".(strpos($sourceName, "'") === false ? "'$sourceName'" : "\"$sourceName\"")."]";

                        //create context if not exists
                        if (!$xpath->query($querySourceName)->item(0)) {
                            $context = $tsFile->createElement('context');
                            $ts->appendChild($context);
                            $name = $tsFile->createELement('name', $sourceName);
                            $context->appendChild($name);
	            		} else {
                            $context = $xpath->query($querySourceName)->item(0);
	            		}

                        $message = $tsFile->createElement('message');
                        $context->appendChild($message);

                        $source = $tsFile->createElement('source', htmlspecialchars($element));
                        $message->appendChild($source);

                        $translation = $tsFile->createElement('translation');
                        $message->appendChild($translation);


            		}
        		} catch (Exception $e) {
					eZLog::write($e, 'owtranslate.log');
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

    /**
	*	@desc		Create translation file with all translation found
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@params		string	$file => the file where the translation is adding
	*	@return		bool
	*	@copyright	2012
	*	@version 	1.1
	*/
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
                $source = $tsFile->createElement('source', htmlspecialchars($element));
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
    
    /**
	*	@desc		Create all local directory for every language of your site
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@params		string	$baseDirectory => the base folder you want to create local dir
	*	@copyright	2012
	*	@version 	1.1
	*/
    public function createLocaleDirIfNotExist($baseDirectory) {
		if (!is_dir($baseDirectory)) {
			eZDir::mkdir($baseDirectory, octdec('0775'));
		}
		$localeOverride = eZINI::instance('owtranslate.ini')->variable( 'LocaleOverride', 'locale');
    	foreach ($this->languageList as $language) {
    		$locale = (array_key_exists($language->Locale, $localeOverride) ? $localeOverride[$language->Locale] : $language->Locale);
    		if (!is_dir($baseDirectory.'/'.$locale)) {
    			eZDir::mkdir($baseDirectory.'/'.$locale, octdec('0775'));
    		}
    	}
    }
    
    /**
	*	@desc		Scan directory to find translation
	*	@author 	David LE RICHE <david.leriche@openwide.fr>
	*	@params		string	$directory => the base folder you want to scan
	*	@return 	array
	*	@copyright	2012
	*	@version 	1.1
	*/
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
