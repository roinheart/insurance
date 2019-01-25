<?php
namespace Home\Controller;
use Behavior\TokenBuildBehavior;
use Think\Controller;

class RegUserController extends Controller {
	public function index() {
		$this->display();
	}
	public function checkUserId() {
		$res = array('state' => 0);
		$user = M('user');
		if (!$user->autoCheckToken($_POST)) {
			//令牌验证错误
			$this->error('请不要重复提交！');
		}
		$result = $user->where("id='%s'", array(I('post.data')))->find();
		if ($result['id'] == I('post.data')) {
			$res['state'] = 1;
			echo json_encode($res, JSON_PRETTY_PRINT);
		}
		//由于需要通过header带上新的__hash__值，所以这里需要先销毁当前页的url token ，再重新生成，才会调用getToken方法中的 header 设定
		$tokenKey = md5($_SERVER['REQUEST_URI']);
		unset($_SESSION['__hash__'][$tokenKey]);
		import('Behavior.TokenBuildBehavior');
		$behavior = new TokenBuildBehavior();
		$behavior::getToken();
		if ($res['state'] == 0) {
			$this->ajaxReturn($res);
		}
	}
	public function addUser() {
		$rule = array(
			array('id', '5,25', '用户名长度错误', 3, 'length'),
			array('password', '5,25', '密码长度错误', 3, 'length'),
			array('code', '8,8', '邀请码长度错误', 3, 'length'),
		);
		$user = M('user');
		if (!$user->field('id,name,password')->validate($rule)->create()) {
			// 如果创建失败 表示验证没有通过 输出错误提示信息
			//本函数所有跳转都使用success函数主要是为了刷新页面，error函数是返回上一页。
			$this->success($user->getError(), U('RegUser/index'));
		} else {
			// 验证通过 可以进行其他数据操作
			$upuserinfo = $user->where('code=%s', array(I('post.code')))->find();
			if (!$upuserinfo) {
				$this->success("邀请码错误", U('RegUser/index'));
			} else {
				//生成新邀请码,保证邀请码得唯一性
				makecode:
				$newcode = mt_rand(10000000, 99999999);
				$codeexsit = $user->where('code=%s', array($newcode))->find();
				if ($codeexsit) {
					goto makecode;
				}
				$usertype = '5';
				switch ($upuserinfo['usertype']) {
				case '1':
					$usertype = '2';
					break;
				case '2':
					$usertype = '3';
					break;
				case '3':
					$usertype = '4';
					break;
				}
				$usersql = "INSERT INTO user (id,name,username,password,code,usertype,regdate,enable,superior) VALUES ('" . I('post.id') . "','" . I('post.name') . "','" . I('post.username') . "','" . I('post.password') . "','" . $newcode . "','" . $usertype . "','" . date('Y-m-d') . "','1','" . $upuserinfo['id'] . "')";
				$Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
				if ($Model->execute($usersql)) {
					$this->success("注册成功<br>恭喜您成为我们得会员", U('Login/index'));
				} else {
					$this->success("注册失败", U('RegUser/index'));
				}
			}
		}
	}

}