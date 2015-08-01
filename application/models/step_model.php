<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once(APPPATH . 'models/base_model.php');

class step_model extends base_model {		public function __construct() {		parent::__construct();		$this->tableName = 'step';	}    public function create($params) {		$this->db->insert('course_step', $params);    }	public function remove($id) {		$this->db->delete('course_step', array(			'id' => $id		));	}	public function edit($id, $params) {		$this->db->where('id', $id)->update('course_step', $params);	}	public function isExist($params) {		return $this->db->select('id')->where($params)->get('course_step')->num_rows() > 0;	}	public function get($id) {		return $this->db->where('id', $id)->get('course_step')->row_array();	}}