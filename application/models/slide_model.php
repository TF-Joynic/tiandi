<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once(APPPATH . 'models/base_model.php');

class slide_model extends base_model {

	public function __construct() {		parent::__construct();		$this->table_name = 'slide';	}	public function get_list($type, $page = 0, $count = 5) {		$list = parent::get_list(array(			'type' => $type		), $page, $count);		foreach($list as $slide) {			if (empty($slide['text'])) break;			$slide['text'] = json_decode($slide['text']);		}		return $list;	}}