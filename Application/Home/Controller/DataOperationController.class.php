<?php
namespace Home\Controller;

use Think\Controller;

class DataOperationController extends Controller
{
    public function _initialize()
    {
        if (I('session.userType') != '1') {
            redirect(U('Login/index'));
        }
    }
    public function ajaxDel()
    {
        $id      = I('post.id');
        $dataObj = M('travelerinfo');
        if ($dataObj->where('sysnum=' . $id)->delete()) {
            $dataObj = M('insureinfo');
            if ($dataObj->where('id=' . $id)->delete()) {
                $this->ajaxReturn(true);
            }
        }
        $this->ajaxReturn(false);
    }
}
