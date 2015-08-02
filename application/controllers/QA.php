<?php

class qa extends CI_Controller {

	function __construct() {
    	parent::__construct();
	}

	public function index() {
    	$this->load->view('pages/QA/formOne.php');
	}

	public function login() {
    	$this->load->view('pages/QA/login.php');
	}

	public function register() {
		$this->load->view('pages/QA/register.php');
	}

	public function forgetPassword() {
		$this->load->view('pages/QA/forgetPassword.php');
	}

	public function warn() {
		$this->load->view('pages/QA/warn.php');
	}

	public function StudentInformation() {
		$this->load->view('pages/QA/StudentInformation.php');
	}

}


