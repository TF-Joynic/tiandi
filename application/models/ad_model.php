<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once(APPPATH . 'models/Base_model.php');

class Ad_model extends Base_model {	public function __construct() {		parent::__construct();		$this->table_name = 'ad';	}}