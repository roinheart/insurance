<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
	public function _initialize() {
		if (!I('session.userType')) {
			$this->userauth();
		}
	}
	public function index() {
		$this->userauth();
	}
	public function admindex() {
		if (I('session.userType') != 1) {
			$this->userauth();
		}
		$this->display();
	}
	//旅行社登录
	public function BranchIndex() {
		if (I('session.userType') < 2 or I('session.userType') > 4) {
			$this->userauth();
		}
		$this->display();
	}
	public function logout() {
		$this->userauth();
	}
}