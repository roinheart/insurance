<?php
namespace Home\Controller;

use Think\Controller;

class DataStatisticsController extends Controller
{
    public $exportPath = '/new/export/';
    public function index()
    {
        $this->userAuth();
        $this->display();
    }
    public function userAuth()
    {
        if (!I('session.userType')) {
            redirect(U('Login/index'));
        }
    }
    public function getUserId()
    {
        if (I('session.userType') != '1') {
            redirect(U('Login/index'));
        }
        $user            = M('user');
        $map['name']     = array('like', '%' . I('post.name') . '%');
        $map['superior'] = array('eq', 'admin');
        $userList        = $user->where($map)->select();
        echo json_encode($userList, JSON_PRETTY_PRINT);
    }
    public function TravelCheck()
    {
        $this->userAuth();
        switch (I('session.userType')) {
            case '3':
                // var_dump(I('post.'));
                $user     = M('user');
                $userList = $user->query("SELECT * FROM user WHERE id='" . I('session.id') . "' or superior='" . I('post.id') . "' UNION SELECT * FROM user WHERE superior IN (SELECT id FROM user WHERE superior='" . I('post.id') . "')");
                for ($i = 0; $i < count($userList); $i++) {
                    if (I('post.ordernum') != '') {
                        $map['sysnum'] = array('like', '%' . I('post.ordernum') . '%');
                    }
                    if (I('post.name') != '') {
                        $map['name'] = array('like', '%' . I('post.name') . '%');
                    }
                    if (I('post.idcard') != '') {
                        $map['idcard'] = array('like', '%' . I('post.idcard') . '%');
                    }
                    $map['startdate'] = array('egt', I('post.startdate'));
                    $map['enddate']   = array('elt', I('post.enddate'));
                    $map['userid']    = array('eq', $userList[$i]['id']);
                    $data             = $this->allTraveler($map);
                    if (count($data) == 0) {
                        continue;
                    }
                    for ($j = 0; $j < count($data); $j++) {
                        $dataList[] = $data[$j];
                    }
                }
                $result = $this->insureInfo($dataList);
                $this->ajaxReturn($result);
                break;
            case '4':
                if (I('post.ordernum') != '') {
                    $map['sysnum'] = array('like', '%' . I('post.ordernum') . '%');
                }
                if (I('post.name') != '') {
                    $map['name'] = array('like', '%' . I('post.name') . '%');
                }
                if (I('post.idcard') != '') {
                    $map['idcard'] = array('like', '%' . I('post.idcard') . '%');
                }
                $map['startdate'] = array('egt', I('post.startdate'));
                $map['enddate']   = array('elt', I('post.enddate'));
                $map['userid']    = array('eq', I('session.id'));
                $data             = $this->allTraveler($map);
                if (count($data) == 0) {
                    continue;
                }
                for ($j = 0; $j < count($data); $j++) {
                    $dataList[] = $data[$j];
                }
                $result = $this->insureInfo($dataList);
                $this->ajaxReturn($result);
                break;
        }
    }
    public function adminCheck()
    {
        $this->userAuth();
        $diff = $this->postLimit();
        if ($diff < 2) {
            exit;
        }
        if (I('post.id') != '' && I('post.name') == '' && I('post.idcard') == '') {
            $userIdList = $this->allHeadOffice(I('post.id'));
            for ($i = 0; $i < count($userIdList); $i++) {
                $map['startdate'] = array('egt', I('post.startdate'));
                $map['enddate']   = array('elt', I('post.enddate'));
                $map['userid']    = array('eq', $userIdList[$i]['id']);
                $data             = $this->allTraveler($map);
                if (count($data) == 0) {
                    continue;
                }
                for ($j = 0; $j < count($data); $j++) {
                    $dataList[] = $data[$j];
                }
            }
            $result = $this->insureInfo($dataList);
            $this->ajaxReturn($result);
            return;
        }
        if (I('post.id') == '' && I('post.name') != '' && I('post.idcard') == '') {
            $map['startdate'] = array('egt', I('post.startdate'));
            $map['enddate']   = array('elt', I('post.enddate'));
            $map['name']      = array('like', '%' . I('post.name') . '%');
            $data             = $this->allTraveler($map);
            if (count($data) == 0) {
                return;
            }
            for ($j = 0; $j < count($data); $j++) {
                $dataList[] = $data[$j];
            }
            $result = $this->insureInfo($dataList);
            $this->ajaxReturn($result);
            return;
        }
        if (I('post.id') == '' && I('post.name') == '' && I('post.idcard') != '') {
            $map['startdate'] = array('egt', I('post.startdate'));
            $map['enddate']   = array('elt', I('post.enddate'));
            $map['idcard']    = array('like', '%' . I('post.idcard') . '%');
            $data             = $this->allTraveler($map);
            if (count($data) == 0) {
                return;
            }
            for ($j = 0; $j < count($data); $j++) {
                $dataList[] = $data[$j];
            }
            $result = $this->insureInfo($dataList);
            $this->ajaxReturn($result);
            return;
        }
        if (I('post.id') != '' && I('post.name') != '' && I('post.idcard') == '') {
            $userIdList = $this->allHeadOffice(I('post.id'));
            for ($i = 0; $i < count($userIdList); $i++) {
                $map['startdate'] = array('egt', I('post.startdate'));
                $map['enddate']   = array('elt', I('post.enddate'));
                $map['userid']    = array('eq', $userIdList[$i]['id']);
                $map['name']      = array('like', '%' . I('post.name') . '%');
                $data             = $this->allTraveler($map);
                if (count($data) == 0) {
                    continue;
                }
                for ($j = 0; $j < count($data); $j++) {
                    $dataList[] = $data[$j];
                }
            }
            $result = $this->insureInfo($dataList);
            $this->ajaxReturn($result);
            return;
        }
        if (I('post.id') != '' && I('post.name') == '' && I('post.idcard') != '') {
            $userIdList = $this->allHeadOffice(I('post.id'));
            for ($i = 0; $i < count($userIdList); $i++) {
                $map['startdate'] = array('egt', I('post.startdate'));
                $map['enddate']   = array('elt', I('post.enddate'));
                $map['userid']    = array('eq', $userIdList[$i]['id']);
                $map['idcard']    = array('like', '%' . I('post.idcard') . '%');
                $data             = $this->allTraveler($map);
                if (count($data) == 0) {
                    continue;
                }
                for ($j = 0; $j < count($data); $j++) {
                    $dataList[] = $data[$j];
                }
            }
            $result = $this->insureInfo($dataList);
            $this->ajaxReturn($result);
            return;
        }
        if (I('post.id') != '' && I('post.name') != '' && I('post.idcard') != '') {
            $userIdList = $this->allHeadOffice(I('post.id'));
            for ($i = 0; $i < count($userIdList); $i++) {
                $map['startdate'] = array('egt', I('post.startdate'));
                $map['enddate']   = array('elt', I('post.enddate'));
                $map['userid']    = array('eq', $userIdList[$i]['id']);
                $map['name']      = array('like', '%' . I('post.name') . '%');
                $map['idcard']    = array('like', '%' . I('post.idcard') . '%');
                $data             = $this->allTraveler($map);
                if (count($data) == 0) {
                    continue;
                }
                for ($j = 0; $j < count($data); $j++) {
                    $dataList[] = $data[$j];
                }
            }
            $result = $this->insureInfo($dataList);
            $this->ajaxReturn($result);
            return;
        }
        if (I('post.id') == '' && I('post.name') != '' && I('post.idcard') != '') {
            $map['name']   = array('like', '%' . I('post.name') . '%');
            $map['idcard'] = array('like', '%' . I('post.idcard') . '%');
            $data          = $this->allTraveler($map);
            if (count($data) == 0) {
                return;
            }
            for ($j = 0; $j < count($data); $j++) {
                $dataList[] = $data[$j];
            }
            $result = $this->insureInfo($dataList);
            $this->ajaxReturn($result);
            return;
        }
        if (I('post.id') == '' && I('post.name') == '' && I('post.idcard') == '') {
            $map['startdate'] = array('egt', I('post.startdate'));
            $map['enddate']   = array('elt', I('post.enddate'));
            $data             = $this->allTraveler($map);
            if (count($data) == 0) {
                return;
            }
            for ($j = 0; $j < count($data); $j++) {
                $dataList[] = $data[$j];
            }
            $result = $this->insureInfo($dataList);
            $this->ajaxReturn($result);
            return;
        }
    }

    public function branchCheck()
    {
        $userIdList = $this->allHeadOffice(I('post.id'));
        for ($i = 0; $i < count($userIdList); $i++) {
            $map['startdate'] = array('egt', I('post.startdate'));
            $map['enddate']   = array('elt', I('post.enddate'));
            $map['sysnum']    = array('like', '%' . I('post.ordernum') . '%');
            $map['userid']    = array('eq', $userIdList[$i]['id']);
            $data             = $this->allTraveler($map);
            if (count($data) == 0) {
                continue;
            }
            for ($j = 0; $j < count($data); $j++) {
                $dataList[] = $data[$j];
            }
        }
        $result = $this->insureInfo($dataList);
        $this->ajaxReturn($result);
        return;
    }

    public function OrderDetail($no)
    {
        $this->userAuth();
        $table     = M('insureinfo');
        $map['id'] = I('get.no');
        $insure    = $table->where($map)->select();
        unset($map);
        $map['sysnum'] = I('get.no');
        $table         = M('travelerinfo');
        $travelerList  = $table->field('id,name,cardtype,idcard,sex,birthday')->where($map)->select();
        unset($map);
        $map['id']  = $insure[0]['priceschemeid'];
        $table      = M('pricescheme');
        $schemename = $table->field('name')->where($map)->select();
        $this->assign('sysnum', $insure[0]['id']);
        $this->assign('userid', $insure[0]['userid']);
        $this->assign('username', $insure[0]['username']);
        $this->assign('startdate', $insure[0]['startdate']);
        $this->assign('enddate', $insure[0]['enddate']);
        $this->assign('travelline', $insure[0]['travelline']);
        $this->assign('guide', $insure[0]['guide']);
        $this->assign('teamnum', $insure[0]['teamnum']);
        $this->assign('pricesum', $insure[0]['pricesum']);
        $this->assign('total', $insure[0]['total']);
        if ($insure[0]['travelclass'] == '0') {
            $this->assign('travelclass', '境内旅游');
        } else {
            $this->assign('travelclass', '境外旅游');
        }
        $this->assign('day', $insure[0]['day']);
        $this->assign('ordertime', $insure[0]['ordertime']);
        $this->assign('schemename', $schemename[0]['name']);
        $this->assign('list', $travelerList);
        // var_dump($insure);
        // var_dump($travelerList);
        // var_dump($schemename);
        $this->display();
    }

    public function travelerExport()
    {
        $this->userAuth();
        $filename      = I('post.sysnum') . '.xls';
        $table         = M('travelerinfo');
        $map['sysnum'] = I('post.sysnum');
        $list          = $table->field('name,cardtype,idcard,sex,birthday')->where($map)->select();
        foreach ($list as $key => $val) {
            $name[]     = $val['name'];
            $cardtype[] = $val['cardtype'];
            $idcard[]   = $val['idcard'];
            $sex[]      = $val['sex'];
            $birthday[] = $val['birthday'];
        }
        $data[] = $name;
        $data[] = $cardtype;
        $data[] = $idcard;
        $data[] = $sex;
        $data[] = $birthday;
        Vendor("PHPExcel.PHPExcel");
        Vendor("PHPExcel.PHPExcel.Writer.Excel5");
        Vendor("PHPExcel.PHPExcel.IOFactory");
        $headArr                    = array("序号", "姓名", "证件类别", "证件号码", "性别", "出生日期");
        $fileaddress['fileaddress'] = $this->exportPath . $filename;
        $this->getExcel($filename, $headArr, $data);
        echo json_encode($fileaddress, JSON_PRETTY_PRINT);
        return;
    }
    public function export()
    {
        $this->userAuth();
        $filename = date('Y-m-d-h-i-s-') . mt_rand(10, 99) . '.xls';
        $data[]   = explode(',', I('post.id')[0]);
        $data[]   = explode(',', I('post.username')[0]);
        $data[]   = explode(',', I('post.teamnum')[0]);
        $data[]   = explode(',', I('post.total')[0]);
        Vendor("PHPExcel.PHPExcel");
        Vendor("PHPExcel.PHPExcel.Writer.Excel5");
        Vendor("PHPExcel.PHPExcel.IOFactory");
        $headArr                    = array("序号", "订单号", "签发人员姓名", "团队号", "团队人数");
        $fileaddress['fileaddress'] = $this->exportPath . $filename;
        $this->getExcel($filename, $headArr, $data);
        echo json_encode($fileaddress, JSON_PRETTY_PRINT);
        return;
    }

    public function getExcel($fileName, $headArr, $data)
    {
        $this->userAuth();
        //对数据进行检验
        if (empty($data) || !is_array($data)) {
            die("data must be a array");
        }
        //检查文件名
        if (empty($fileName)) {
            exit;
        }

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps    = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        foreach ($headArr as $v) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $key += 1;
        }

        $column      = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        // 置转二维数据
        $col = count($data);
        $row = count($data[0]);
        $insertDBdata[$col + 1][$row];
        //转置后写入DB
        for ($i = 0; $i < $col; $i++) {
            for ($j = 0; $j < $row; $j++) {
                switch ($i) {
                    case 0:
                        $insertDBdata[$j]['id'] = $data[$i][$j];
                        break;
                    case 1:
                        $insertDBdata[$j]['username'] = $data[$i][$j];
                        break;
                    case 2:
                        $insertDBdata[$j]['teamnum'] = $data[$i][$j];
                        break;
                    case 3:
                        $insertDBdata[$j]['total'] = $data[$i][$j];
                        break;
                }

            }
        }
        //转置后写入EXCEL
        for ($i = 0; $i < $row; $i++) {
            //行号
            $span = ord("B");
            for ($j = 0; $j < $col; $j++) {
                //列号
                $n = chr($span);
                $objActSheet->setCellValue("A" . (string) ($i + 2), (string) ($i + 1));
                $objActSheet->setCellValueExplicit($n . (string) ($i + 2), $data[$j][$i], \PHPExcel_Cell_DataType::TYPE_STRING);
                $span++;
            }
        }
        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        // $objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        // header('Content-Type: application/vnd.ms-excel');
        // header('Content-Disposition: attachment;filename=\"$fileName\"');
        // header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save("./export/" . $fileName);
        $fileaddress = "./export/" . $fileName;
    }

    public function insureInfo($sysnumArray)
    {
        $this->userAuth();
        foreach ($sysnumArray as $sysnum => $val) {
            $map['id'][] = array('eq', $val['sysnum']);
        }
        $map['id'][] = 'or';
        $table       = M('insureinfo');
        try {
            $insureList = $table->field('id, username, teamnum, total')->where($map)->select();
        } catch (exception $e) {

        }
        $insureList = json_encode($insureList, JSON_PRETTY_PRINT);
        ob_clean();
        return $insureList;
    }
    public function allTraveler($map)
    {
        $this->userAuth();
        $table        = M('travelerinfo');
        $travelerinfo = $table->distinct(true)->field('sysnum')->where($map)->select();
        return $travelerinfo;
    }
    public function allHeadOffice($id)
    {
        $this->userAuth();
        $user     = M('user');
        $userList = $user->query("SELECT * FROM user WHERE superior='" . $id . "' UNION SELECT * FROM user WHERE superior IN (SELECT id FROM user WHERE superior='" . $id . "')");
        return $userList;
    }

}
