<?php
require_once('../../../inc/init.php');

class feedData {
    var $meta_data;  // array of meta data entries from meta/newsfeed:pagedata.ser
    var $rss_data;   // array of data items, titles, anchor names from current file
	
	var $feedDataBaseNames;  // array of md5 basenames of files holding feed data
	var $currentMD5BaseName;
	var $currentDataArray;
	var $currentMetaArray;	
	var $newsFeedDate;
	
	function feedData() {
	
   		$metafile = metaFN('newsfeed:pagedata', '.ser');	         		
		$this->meta_data = $this->_readFile($metafile, true);	
		$this->feedDataBaseNames = array_keys($this->meta_data);	
		$metafile = metaFN('newsfeed:timestamp', '.meta');	         		
        $this->newsFeedDate	= $this->_readFile($metafile);
		$this->next_data_file(); //initialize
		
	}

	function _readFile($file, $ser=false) {
	    $ret = io_readFile($file,$ser);
		if($ser) {
		  if(!$ret) return array();
		  return unserialize($ret);
		  }
		return $ret;  
		
	}
    function description() {
            $this->currentDataArray['item'] = 			 
			   preg_replace('#(href|src)\s*=\s*([\'\"])/.*?/#ms', "$1=$2" . $this->news_feed_url(), $this->currentDataArray['item']);

	  		return $this->currentDataArray['item'];
    } 
	
    function rss_id() {
		return $this->currentDataArray['name'];
    }
    function title() {
		return $this->currentDataArray['title'];
	     
    }
   
   function id() {
		return $this->currentMetaArray['id'];
    }
	
    function url() {	   
		return $this->currentMetaArray['url']; 
	}
	
    function timestamp() {
		return $this->currentMetaArray['time'];
	}
	
    function date($which='gm') {
		if($which == 'gm') return $this->currentMetaArray['gmtime'];
		return date('r',$this->timestamp());
    }      
    
	function news_feed_url() {
	   list($server,$rest) = explode('?', $this->url());
	   return str_replace("doku.php", "",$server);
	}
	
	function news_feed_date($which='gm') {
	    if($which == 'gm') return gmdate('r',$this->newsFeedDate);
	    return date('r',$this->newsFeedDate);
	}
	
    function _dataFN() {
		$md5 = array_shift($this->feedDataBaseNames);
		if(!$md5) return false;
		$this->currentMD5BaseName = $md5;		
		return  metaFN("newsfeed:$md5", '.gz');	         		
   }
   
   function testDataElements() {   	
   	
		$file = $this->_dataFN();
		$ar = $this->_readFile($file, true);
        for($i=0;$i<count($ar);$i++) {
			echo "Name: " . $ar[$i]['name'] ."\n";
			echo "Title: " . $ar[$i]['title'] ."\n";
			//echo "Item: " . $ar[$i]['item'] ."\n\n";
        }

   }
   
   function next_data_file() {
        $file = $this->_dataFN(); 
		if(!$file) {
		    $this->currentMetaArray = array();
			return;
		}
   		$this->rss_data = $this->_readFile($file, true);	
		$this->currentMetaArray = $this->meta_data[$this->currentMD5BaseName];	
	}
   
   
   function feed_data() { 
        if(is_array($this->currentDataArray)) {
			   $this->currentDataArray = array_shift($this->rss_data);
		}
		
       if(!$this->currentDataArray) {
	           $this->next_data_file();
			   $this->currentDataArray = array_shift($this->rss_data);
    	 }	   


		if(!$this->currentDataArray) return false;
		return true;

    }
}

?>
