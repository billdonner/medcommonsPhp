<?php
require_once "urls.inc.php";
require_once "settings.php";

/**
 * Convenience function for creating templates
 */
function template($src) {
	return new Template($src);
}

class Template {

	var $vars; /// Holds all the template variables
	var $topicfile= ''; //force explicit specificatoin 
	var $keywords = 'medcommons, personal health records,ccr, phr, privacy, patient, health, records, medical records,
						emergencyccr';
	var $description ='MedCommons Home';
	var $title = 'MedCommons - Interoperable and Private Personal Health Records';
	var $phtml = 'php'; // generate php links unless overridden
	var $searchdef = ''; // default search string
	/**
     * Constructor
     *
     * @param $file string the file name you want to load
     */
	function Template($file = null) {
		$this->file = $file;
		$this->vars = array();
		return $this;
	}

	/**
     * Set a template variable.
     */
	function set($name, $value) {
		$this->vars[$name] = (is_object($value) && method_exists($value,"fetch")) ? $value->fetch() : $value;
		return $this;
	}

  /**
   * Set a nested template to be evaluated at render time
   */
	function nest($name, &$t) {
    $this->vars[$name] = $t;
		return $this;
  }

  function is_set($name) {
    return isset($this->vars[$name]);
  }

	/**
     * Set and escape a template variable.
     */
	function esc($name, $value) {
		$this->vars[$name] = htmlspecialchars($value);
    return $this;
	}

  /**
   * Format the difference between the two given times
   * as an age and return the result as a string.  Times 
   * are passed in seconds since 1/1/1970 GMT.
   */
  function formatAge($itemTime, $refTime) {
    $age = $refTime - $itemTime;
    $days = floor($age / 86400);
    $hours = floor(($age - ($days * 86400)) / 3600);
    $mins = floor( ($age % 3600) / 60);

    $dateTime = ($days > 0) ? "$days days, " : "";
    $dateTime .= "$hours hrs $mins mins ago";

    if($days<1) {
      if($hours > 1) {
        $dateTime =  "$hours hours ago";
      }
      else
        $dateTime = $hours > 0 ? "$hours hour ago" : "$mins mins ago";
    }
    else
      if($days < 7) {
        $dateTime = "$days days ago";
      }
      else {
        $dateTime = htmlspecialchars(strftime('%m/%d/%y',$itemTime));
      }
    return $dateTime;
  }

	function set_keywords ($s) {$this->keywords=$s;}
	function set_description ($s) {$this->description=$s;}
	function set_title ($s) {$this->title=$s;}
	function set_phtml ($s) {$this->phtml=$s;}
	function set_searchdef ($s) {$this->searchdef=$s;}
	function set_topicfile ($s) {$this->topicfile=$s;}

	/**
   * Open, parse, and return the template file.
   *
   * @param $file string the template file name
   */
	function fetch($file = null, $pageHasNoAds = true ) {
    global $acTemplateFolder;

		if(!$file) $file = $this->file;

		$prefix = null;
		if(preg_match("/^[\.\/]+/",$file, $prefix)) {
			$this->set("relPath", $prefix[0]);
      error_log("setting prefix  ".$prefix[0]." for path ". $this->file);
		}
		else {
      $this->set("relPath", "./");
      // error_log("no prefix  for path ".$this->file);
    }

    if(!$pageHasNoAds) {
      $this->set('noAds','false'); 
    }
    else 
      $this->set('noAds','true');

		$this->set("g",$GLOBALS);
    $this->vars["template"]=$this;
    
    if(isset($GLOBALS['use_combined_files']) && ($GLOBALS['use_combined_files']==true)) {
      $this->set("enableCombinedFiles",true);
      if(isset($GLOBALS['Acct_Combined_File_Base'])) {
        $this->set("httpUrl",rtrim($GLOBALS['Acct_Combined_File_Base'],"/"));
      }
      else
        $this->set("httpUrl",rtrim($GLOBALS['Secure_Url'],"/")."/acct");
    }
    else {
      $this->set("enableCombinedFiles",false);
      $this->set("httpUrl",rtrim($this->vars["relPath"],"/"));
    }

		if ($this->topicfile!='')
      $this->set ("__topics",file_exists($this->topicfile) ? file_get_contents($this->topicfile) : ""); // incorporate plain html
		else
      $this->set ("__topics",'');
		$this->set ("__keywords",$this->keywords);
		$this->set ("__description",$this->description);
		$this->set ("__title",$this->title);
			$this->set ("__phtml",$this->phtml);
		$this->set ("__searchdef",$this->searchdef);

    $outputvars = array();
    foreach($this->vars as $key => $value) {
      if(is_object($value) && method_exists($value,"fetch") && ($key != "template")) {
        $outputvars[$key] = $value->fetch();
      }
      else
        $outputvars[$key] = $value;
    }

		extract($outputvars);          // Extract the vars to local namespace
		ob_start();                    // Start output buffering

    // Always search mc_templates folder
    $resolvedFile = $file;
    if(file_exists($acTemplateFolder . $file) && !file_exists($resolvedFile)) {
      $resolvedFile = $acTemplateFolder . $file;
    }

		include($resolvedFile);                // Include the file
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean();                // End buffering and discard
		
		
		return $contents;              // Return the contents
	}
}

/**
 * An extension to Template that provides automatic caching of
 * template contents.
 */
class CachedTemplate extends Template {
	var $cache_id;
	var $expire;
	var $cached;

	/**
     * Constructor.
     *
     * @param $cache_id string unique cache identifier
     * @param $expire int number of seconds the cache will live
     */
	function CachedTemplate($cache_id = null, $expire = 900) {
		$this->Template();
		$this->cache_id = $cache_id ? 'cache/' . md5($cache_id) : $cache_id;
		$this->expire   = $expire;
	}

	/**
     * Test to see whether the currently loaded cache_id has a valid
     * corrosponding cache file.
     */
	function is_cached() {
		if($this->cached) return true;

		// Passed a cache_id?
		if(!$this->cache_id) return false;

		// Cache file exists?
		if(!file_exists($this->cache_id)) return false;

		// Can get the time of the file?
		if(!($mtime = filemtime($this->cache_id))) return false;

		// Cache expired?
		if(($mtime + $this->expire) < time()) {
			@unlink($this->cache_id);
			return false;
		}
		else {
			/**
       * Cache the results of this is_cached() call.  Why?  So
       * we don't have to double the overhead for each template.
       * If we didn't cache, it would be hitting the file system
       * twice as much (file_exists() & filemtime() [twice each]).
       */
			$this->cached = true;
			return true;
		}
	}

	/**
     * This function returns a cached copy of a template (if it exists),
     * otherwise, it parses it as normal and caches the content.
     *
     * @param $file string the template file
     */
	function fetch_cache($file) {
		if($this->is_cached()) {
			$fp = @fopen($this->cache_id, 'r');
			$contents = fread($fp, filesize($this->cache_id));
			fclose($fp);
			return $contents;
		}
		else {
			$contents = $this->fetch($file);

			// Write the cache
			if($fp = @fopen($this->cache_id, 'w')) {
				fwrite($fp, $contents);
				fclose($fp);
			}
			else {
				die('Unable to write cache.');
			}

			return $contents;
		}
	}
}
?>
