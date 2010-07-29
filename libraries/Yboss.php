<?php defined('SYSPATH') or die('No direct script access.'); 

/*
* @created_at: oct 11, 2009
* @created_by: Fabian Tollenaar
* @licence: MIT
* @mail: fabian [at] dotbrilliance [dot] nl
*/

class Yboss_Core {
	private $api_key;
	private $api_url;
	private $api_format;
	private $results_pp;
	
	private $type = 'WEB';
	private $query = '';
	private $start = 0;
	
	private $currentpage = null;
	private $responsecode = null;
	private $totalhits = null;
	private $deephits = null;
	private $count = null;
	private $totalpages = null;
	private $nextpage = null;
	private $prevpage = null;
	private $results_arr = null;
	private $output = null;
	private $next = null;
	private $prev = null;
	private $lang = null;
	
	public function __construct($init) {
		if(!is_array($init)) return false;
		
		$this->api_key = $init['api'];
		$this->api_url = $init['uri'];
		$this->api_format = $init['format'];
		$this->results_pp = $init['results'];
		$this->lang = isset($init['lang']) ? $init['lang'] : 'eng';
	}
	
	public function query($str, $type = 'web', $start = 0) {
		$this->type = $type;
		$this->query = $str;
		$this->start = $start;
		
		$url = "{$this->api_url}/$type/v1/";
		$url.= urlencode($this->query);
		$url.= '?appid=' . $this->api_key;
		$url.= '&format=' . $this->api_format;
		$url.= '&start=' . $this->start;
		$url.= '&count=' . $this->results_pp;
		$url.= '&lang=' . $this->lang;
		
		$out = $this->exec($url);
		
		if($out != false) $process = $this->process($out);
		else $process = false;
		
		return $process;
	}
	
	public function page($mp = 1) {
		$next = ($this->next) * $mp;
		return $this->query($this->query, $this->type, $next);
	}
	
	private function exec($uri) {
		$c = curl_init();
		
		curl_setopt($c, CURLOPT_URL, $uri); 
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		
		$o = curl_exec($c);
		
		if(!$o) return false;
		return $o;
	}
	
	private function process($in) {
		$ysearch = json_decode($in);
		$obj = $ysearch->ysearchresponse;
		
		$this->responsecode = $obj->responsecode;
		$this->totalhits = $obj->totalhits;
		$this->deephits = $obj->deephits;
		$this->count = $obj->count;
		
		if($this->start < $obj->totalhits - 20) $this->next = $this->start + $this->count;
		if($this->start >= 20) $this->prev = $this->start - $this->count;
		
		$this->totalpages = ceil($obj->totalhits/$obj->count);
		
		if($obj->start > 0) $this->currentpage = ceil($obj->start/$obj->count) + 1;
		else $this->currentpage = 1;
		
		if($obj->start < $this->totalpages) $this->nextpage = $obj->nextpage;
		else $this->nextpage = 0;
		
		if($obj->start > 0) $this->prevpage = $obj->prevpage;
		else $this->prevpage = 0;
		
    $this->results_arr = $obj->{"resultset_{$this->type}"};
		
		$this->output = array(
			'responsecode' => $this->responsecode,
			'type' => strtolower($this->type),
			'start' => $this->start,
			'hits' => $this->totalhits,
			'deephits' => $this->deephits,
			'page' => $this->currentpage,
			'pages' => $this->totalpages,
			'next' => array(
				'start' => $this->next,
				'url' => $this->nextpage
			), 
			'prev' => array(
				'start' => $this->prev,
				'url' => $this->prevpage
			),
			'results' => $this->results_arr
		);
		
		return $this->output;
	}
}