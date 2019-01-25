<?php
namespace Home\Controller;

use Think\Controller;

class InsureCaseController extends Controller
{
    public function _initialize()
    {
        $this->userauth();
    }
    public function userAuth()
    {
        if (!I('session.userType')) {
            redirect(U('Login/index'));
        }
    }
    public function index()
    {
        $this->display();
    }
    public function ReportCase($id)
    {
        $this->userauth();
        $travelerinfo=M('travelerinfo');
        $info=$travelerinfo->where('id='.$id)->select();
        $this->assign('orderNo', $info[0]['sysnum']);
        $this->assign('travelerName', $info[0]['name']);
        $this->assign('idcard', $info[0]['idcard']);
        $this->display();
    }
    public function AdminCheckCase()
    {
        if (I('session.userType') != 1) {
            redirect(U('Login/index'));
        }
        $this->display();
    }
    public function AdminDetail($id)
    {
        $accident = M('accident');
        $data     = $accident->where('id=' . I('get.id'))->select();
        $this->assign('id', I('get.id'));
        $this->assign('reportDate', $data[0]['reportdate']);
        $this->assign('accidentDate', $data[0]['accidentdate']);
        $this->assign('sysNum', $data[0]['sysnum']);
        if ($data[0]['sms']) {
            $this->assign('sendMsg', '已发送');
        } else {
            $this->assign('sendMsg', '未发送');
        }
        $this->assign('accidentPosition', $data[0]['position']);
        if ($data[0]['gameover']) {
            $this->assign('gameOver', '是');
        } else {
            $this->assign('gameOver', '否');
        }
        $this->assign('travelerName', $data[0]['travelername']);
        $this->assign('travelerMobile', $data[0]['travelerphoneno']);
        $this->assign('idNo', $data[0]['idcard']);
        $this->assign('injured', $data[0]['injured']);
        $this->assign('reportName', $data[0]['reportname']);
        $this->assign('reportMobile', $data[0]['reportphoneno']);
        $this->assign('familyName', $data[0]['familyname']);
        $this->assign('familyMobile', $data[0]['familyphoneno']);
        if ($data[0]['checked']) {
            $this->assign('status', '已处理');
        } else {
            $this->assign('status', '未处理');
        }

        $this->assign('remarks', $data[0]['remarks']);
        $this->display();
    }
    public function submit()
    {
        if (I('session.userType') != 1) {
            redirect(U('Login/index'));
        }
        $accident = M('accident');
        if (!$accident->autoCheckToken($_POST)) {
            //令牌验证错误
            $this->error('请不要重复提交！');
        }
        $accident->checked = 1;
        if ($accident->where('id=' . I('post.userid'))->save()) {
            $this->success("处理完成。", U('InsureCase/AdminCheckCase'));
        } else {
            $this->success("处理失败。", U('InsureCase/AdminCheckCase'));
        }
    }
    public function report()
    {
        $table = M('accident');
        if (!$table->autoCheckToken($_POST)) {
            //令牌验证错误
            $this->error('请不要重复提交！');
        }
        try {
            $data = array(
                'gameover'        => I('post.gameover'), #死亡
                'sysnum'          => I('post.orderNo'), #订单号
                'accidentdate'    => I('post.accidentDate'), #事故发生日期
                'position'        => I('post.position'), #事故发生地点
                'travelername'    => I('post.travelerName'), #游客名称
                'idcard'          => I('post.idcard'), #游客证件号码
                'travelerphoneno' => I('post.travelerPhoneNo'), #游客电话号码
                'injured'         => I('post.injured'), #受伤部位
                'reportname'      => I('post.reportName'), #报案人姓名
                'reportphoneno'   => I('post.reportPhoneNo'), #报案人电话号码
                'familyname'      => I('post.familyName'), #家属姓名
                'familyphoneno'   => I('post.familyPhoneNO'), #家属电话号码
                'remarks'         => I('post.remarks'), #备注
                'reportdate'      => date("Y-m-d", time()), #报案日期
                'userid'          => I('post.userid'), #用户账号
            );
        } catch (Exception $e) {
            $data = array(
                'sysnum'          => I('post.orderNo'), #订单号
                'accidentdate'    => I('post.accidentDate'), #事故发生日期
                'position'        => I('post.position'), #事故发生地点
                'travelername'    => I('post.travelerName'), #游客名称
                'idcard'          => I('post.idcard'), #游客证件号码
                'travelerphoneno' => I('post.travelerPhoneNo'), #游客电话号码
                'injured'         => I('post.injured'), #受伤部位
                'reportname'      => I('post.reportName'), #报案人姓名
                'reportphoneno'   => I('post.reportPhoneNo'), #报案人电话号码
                'remarks'         => I('post.remarks'), #备注
                'reportdate'      => date("Y-m-d", time()), #报案日期
                'userid'          => I('post.userid'), #用户账号
            );
        }
        if ($table->add($data)) {
            $this->success("录入完成。", U('InsureCase/index'));
        } else {
            $this->success("报案失败。", U('InsureCase/index'));
        }
    }
    public function ajaxCheckRepeatReport()
    {
        $this->userAuth();
        $accident = M('accident');
        $sqlStr   = "select idcard from accident where sysnum='" . I('post.orderNo') . "' and idcard='" . I('post.idcard') . "'";
        $date     = $accident->query($sqlStr);
        echo json_encode($date, JSON_PRETTY_PRINT);
    }
    public function ajaxCheckCase()
    {
        $this->userAuth();
        $sqlParseArray = array(
            'userid'            => I('post.userid'),
            'reportStartDate'   => I('post.reportStartDate'),
            'reportEndDate'     => I('post.reportEndDate'),
            'accidentStartDate' => I('post.accidentStartDate'),
            'accidentEndDate'   => I('post.accidentEndDate'),
            'sysnum'            => I('post.ordernum'),
            'travelername'      => I('post.name'),
            'idcard'            => I('post.idcard'),
        );
        if ($sqlParseArray['userid'] != 'admin') {
            $map['userid'] = array('eq', $sqlParseArray['userid']);
        }
        if ($sqlParseArray['reportStartDate'] != '' && $sqlParseArray['reportEndDate'] != '') {
            $map['reportdate'] = array('between', $sqlParseArray['reportStartDate'].",".$sqlParseArray['reportEndDate']);
        }

        if ($sqlParseArray['accidentStartDate'] != '' && $sqlParseArray['accidentEndDate'] != '') {
            $map['accidentdate'] = array('between', $sqlParseArray['accidentStartDate'] . " 00:00:00," . $sqlParseArray['accidentEndDate'] . " 23:59:59");
        }

        if ($sqlParseArray['sysnum'] != '') {
            $map['sysnum'] = array('eq', $sqlParseArray['sysnum']);
        }

        if ($sqlParseArray['travelername'] != '') {
            $map['travelername'] = array('like', '%' . $sqlParseArray['travelername'] . '%');
        }

        if ($sqlParseArray['idcard'] != '') {
            $map['idcard'] = array('eq', $sqlParseArray['idcard']);
        }

        $accident = M('accident');
        $data     = $accident->where($map)->select();
        $data     = json_encode($data, JSON_PRETTY_PRINT);
        $this->ajaxReturn($data);
    }
    public function Detail($id)
    {
        $this->userAuth();

        $accident = M('accident');
        $data     = $accident->where('id=' . I('get.id'))->select();
        $this->assign('reportDate', $data[0]['reportdate']);
        $this->assign('accidentDate', $data[0]['accidentdate']);
        $this->assign('sysNum', $data[0]['sysnum']);
        if ($data[0]['sms']) {
            $this->assign('sendMsg', '已发送');
        } else {
            $this->assign('sendMsg', '未发送');
        }
        $this->assign('accidentPosition', $data[0]['position']);
        if ($data[0]['gameover']) {
            $this->assign('gameOver', '是');
        } else {
            $this->assign('gameOver', '否');
        }
        $this->assign('travelerName', $data[0]['travelername']);
        $this->assign('travelerMobile', $data[0]['travelerphoneno']);
        $this->assign('idNo', $data[0]['idcard']);
        $this->assign('injured', $data[0]['injured']);
        $this->assign('reportName', $data[0]['reportname']);
        $this->assign('reportMobile', $data[0]['reportphoneno']);
        $this->assign('familyName', $data[0]['familyname']);
        $this->assign('familyMobile', $data[0]['familyphoneno']);
        if ($data[0]['checked']) {
            $this->assign('status', '已处理');
        } else {
            $this->assign('status', '未处理');
        }

        $this->assign('remarks', $data[0]['remarks']);
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
        $fileaddress['fileaddress'] = "/new/export/" . $filename;
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
}
