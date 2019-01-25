<?php
namespace Home\Controller;
use Think\Controller;
class UserManageController extends Controller {
	public function _initialize()
	{
		if (I('session.userType')!=1 and I('session.userType')!=2) {
    		$this->userauth();
    	}
	}
	public function adminIndex(){
        $this->display();
    }
    public function adminEditUserInfo(){
        $this->display();
    }
    public function editUser()
    {
        $user = M("user");
        if (!IS_POST) {
            $this->error("What do U want?");
        }else{
            if (!$user->autoCheckToken($_POST)){
                //令牌验证错误
                $this->success("请不要重复提交！",U('UserManage/adminEditUserInfo'));
            }
        }
        // var_dump(I("post."));
        // // 要修改的数据对象属性赋值
        $data['name'] = I('post.name');
        $data['password'] = I('post.password');
        $data['mobile'] = I('post.mobile');
        $data['email'] = I('post.email');
        if (I('post.onOff')) {
            $data['enable'] = 0;
        }else{
            $data['enable'] = 1;
        }
        if (!$user->where("id='".I('post.id')."'")->save($data)) {
            //验证没有通过 输出错误提示信息
            $this->success($user->getError(),U('UserManage/adminEditUserInfo'));
        }else{
            $this->success("编辑成功!",U('UserManage/adminEditUserInfo'));
        }
    }
    public function getUserId()
    {
        $user=M('user');
        
        switch (I('session.userType')) {
            case '1':
                $map['name']  = array('like','%'.I('post.name').'%');
                //价格设置页面只查询总社
                if (I('post.page')=='price') {
                    $map['superior']  = array('eq','admin');
                }
                $userList=$user->where($map)->select();
                break;
            
            case '2':
                $userList=$user->query("SELECT * FROM user WHERE superior='".I('session.id')."' and name LIKE '%".I('post.name')."%' UNION SELECT * FROM user WHERE superior IN (SELECT id FROM user WHERE superior='".I('session.id')."') and name LIKE '%".I('post.name')."%'");
                break;
        }
        
        // echo $userList;
        echo json_encode($userList,JSON_PRETTY_PRINT);
    }
    public function getUserPriceScheme()
    {
        $user=M('pricescheme');
        $map['userid']  = array('eq',I('post.name'));
        $userList=$user->where($map)->select();
        // echo $userList;
        echo json_encode($userList,JSON_PRETTY_PRINT);
    }
    public function setPriceScheme()
    {
        $scheme = M("pricescheme");
        if (!IS_POST) {
            $this->error("What do U want?");
            exit;
        }
        if (!$scheme->autoCheckToken($_POST)){
            //令牌验证错误
            $this->success("请不要重复提交！",U('UserManage/AdminPriceScheme'));
            exit;
        }
        switch (I('post.scheme')) {
            case 'new':
            // 设置新方案
                if (I('post.hiderang')=='1') {
                    $data['price1'] = I("post.price1");
                    $data['price2'] = I("post.price2");
                    $data['price3'] = I("post.price3");
                    $data['price4'] = I("post.price4");
                    $data['price5'] = I("post.price5");
                }else{
                    $data['price1'] = I("post.price1");
                    $data['price2'] = I("post.price2");
                    $data['price3'] = I("post.price3");
                    $data['price4'] = I("post.price4");
                }
                $data['userid'] = I("post.id");
                $data['name'] = I("post.name");
                $data['rang'] = I("post.hiderang");
                $result=$scheme->add($data);
                if (!$result) {
                    $this->success($scheme->getError(),U('UserManage/AdminPriceScheme'));
                }else{
                    $this->success('新方案设置成功！',U('UserManage/AdminPriceScheme'));
                }
                break;
            default:
                if (I('post.hiderang')=='1') {
                    $data['price1'] = I("post.price1");
                    $data['price2'] = I("post.price2");
                    $data['price3'] = I("post.price3");
                    $data['price4'] = I("post.price4");
                    $data['price5'] = I("post.price5");
                }else{
                    $data['price1'] = I("post.price1");
                    $data['price2'] = I("post.price2");
                    $data['price3'] = I("post.price3");
                    $data['price4'] = I("post.price4");
                }
                $data['userid'] = I("post.id");
                $data['name'] = I("post.name");
                $data['rang'] = I("post.hiderang");
                $result=$scheme->where('id='.I('post.scheme'))->save($data);
                if (!$result) {
                    $this->success($scheme->getError(),U('UserManage/AdminPriceScheme'));
                }else{
                    $this->success('方案编辑成功！',U('UserManage/AdminPriceScheme'));
                }
                break;
        }
    }
}