<?php
namespace Home\Controller;
use Think\Controller;
use Think\Exception;
class LoginController extends Controller {
    public function index(){
        $this->display();
    }
    // 函数中所有跳转均使用success主要是为了刷新页面
    public function login()
    {
        $signlogReadOK = 0;
        $usertable=M('user');
    	if (!IS_POST) {
    		$this->success("What do u want?",U('login/index'));
    	}else{
            if (!$usertable->autoCheckToken($_POST)){
                //令牌验证错误
                $this->success("请不要重复提交！",U('login/index'));
            }else{
                $logininfotable=M('signlog');
                $user=$usertable->where("id='%s' and password='%s'",array(I('post.username'),I('post.password')))->find();
                switch ($user['usertype'])
                {
                    case '3':
                        $zongshe=$usertable->where("id='%s'",array($user['superior']))->find();
                        if (!$zongshe['enable']) {
                            session(null);
                            $this->success("总社帐号已禁用请联系管理员。",U('login/index'));
                            exit();
                        }
                        break;
                    case '4':
                        $fenshe=$usertable->where("id='%s'",array($user['superior']))->find();
                        if (!$fenshe['enable']) {
                            session(null);
                            $this->success("分社帐号已禁用请联系管理员。",U('login/index'));
                            exit();
                        }else{
                            $zongshe=$usertable->where("id='%s'",array($fenshe['superior']))->find();
                            if (!$zongshe['enable']) {
                                session(null);
                                $this->success("总社帐号已禁用请联系管理员。",U('login/index'));
                                exit();
                            }
                        }
                        break;
                }
                if ($user['enable']=='0') {
                    $this->success("帐号已禁用请联系管理员。",U('login/index'));
                }else{
                    if (!$user) {
                        $this->success("帐号密码错误！",U('login/index'));
                    }
                    else{
                        $timenow=date('Y-m-d H:i:s',time());
                        session('id',$user['id']);
                        session('name',$user['name']);
                        session('username',$user['username']);
                        session('userType',$user['usertype']);
                        session('regDate',$user['regdate']);
                        session('code',$user['code']);
                        session('priceenable',$user['priceenable']);
                        try {
                            $logininfo=$logininfotable->where("userid='".I('post.username')."' AND id=".$logininfotable->max('id'))->find();
                            $signlogReadOK=1;
                        } catch (Exception $e) {
                            session('lastLoginTime','');
                        }
                        if ($signlogReadOK) {
                            session('lastLoginTime',$logininfo['logintime']);
                        }
                        session('loginTime',$timenow);
                        $logininfodata['userid']=$user['id'];
                        $logininfodata['loginip']=I('server.REMOTE_ADDR');
                        $logininfodata['logintime']=$timenow;
                        $logininfotable->data($logininfodata)->add();
                        if (!($this->check_verify(I('post.verify')))) {
                            $this->success("验证码错误！",U('login/index'));
                        }else{
                            switch ($user['usertype']) {
                                case '1':
                                    $this->success("登录成功",U('index/admindex'));
                                    break;
                                case '2':
                                    $this->success("登录成功",U('index/HeadOfficeIndex'));
                                    break;
                                case '3':
                                    $this->success("登录成功",U('index/BranchIndex'));
                                    break;
                                case '4':
                                    $this->success("登录成功",U('index/SellsRoomIndex'));
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }
    public function verify()
    {
    	$Verify= new \Think\Verify();
    	$Verify->codeSet='12345';
    	$Verify->fontSize='18';
    	$Verify->length=4;
    	$Verify->entry();
    }
    protected function check_verify($code)
    {
    	$Verify= new \Think\Verify();
    	return $Verify->check($code);
    }
}