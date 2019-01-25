<?php
namespace Home\Controller;

use Think\Controller;

class InfoInsertController extends Controller
{
    public function _initialize()
    {
        if (!I('session.userType')) {
            $this->userauth();
        }
    }
    public function index()
    {
        $this->display();
    }
    public function getUserPriceScheme()
    {
        $diff = $this->postLimit();
        if ($diff < 2) {
            exit;
        }
        $scheme = M('pricescheme');
        switch (I('session.userType')) {
            case '3':
                if (I('post.rang') == '1') {
                    $sqlStr = "SELECT * FROM pricescheme WHERE rang='1' and userid=(SELECT superior FROM user WHERE id='" . I('session.id') . "')" . " and enable='1'";
                } else {
                    $sqlStr = "SELECT * FROM pricescheme WHERE rang='0' and userid=(SELECT superior FROM user WHERE id='" . I('session.id') . "')" . " and enable='1'";
                }
                break;
            case '4':
                if (I('post.rang') == '1') {
                    $sqlStr = "SELECT * FROM pricescheme WHERE rang='1' and userid=(SELECT superior FROM user WHERE id IN (SELECT superior FROM user WHERE id='" . I('session.id') . "'))" . " and enable='1'";
                } else {
                    $sqlStr = "SELECT * FROM pricescheme WHERE rang='0' and userid=(SELECT superior FROM user WHERE id IN (SELECT superior FROM user WHERE id='" . I('session.id') . "'))" . " and enable='1'";
                }
                break;
        }

        $schemeList = $scheme->query($sqlStr);
        echo json_encode($schemeList, JSON_PRETTY_PRINT);
    }
    public function ajaxScanIdcard()
    {
        $diff = $this->postLimit();
        if ($diff < 2) {
            exit;
        }
        header("Content-Type:application/x-www-form-urlencoded;charset=utf-8");
        $tmp      = substr(I('post.dataURL'), 22);
        $tmp      = base64_decode($tmp);
        $filename = "IDCARD" . date("YmdHis", time()) . '_' . I('session.code');
        $filepath = "E:/new/IDCard/" . $filename . ".jpg";
        $fhwnd    = fopen($filepath, 'x');
        if ($fhwnd != null) {
            fwrite($fhwnd, $tmp);
        } else {
            echo "文件建立失败!";
        }
        fclose($fhwnd);
        Vendor("ocr.AipOcr");
        // baidu ocr 定义常量
        $APP_ID     = '10023415';
        $API_KEY    = 'e9U1gLK6GiekxHPikoKeZdrl';
        $SECRET_KEY = 'VWRGIVlWaKDWU5C4i8rN5wTUXo4yd3d3';
        // 初始化
        $aipOcr  = new \AipOcr($APP_ID, $API_KEY, $SECRET_KEY);
        $options = array('detect_direction' => 'true', 'accuracy' => 'high');
        // 身份证识别
        echo json_encode($aipOcr->idcard(file_get_contents($filepath), true, $options), JSON_PRETTY_PRINT);
    }
    public function ajaxCheckTraveler()
    {
        // $diff=$this->postLimit();
        // if ($diff<2) {
        //     exit;
        // }
        $traveler = M('travelerinfo');
        $sqlStr   = "SELECT max(startdate),max(enddate) FROM travelerinfo WHERE enddate in(SELECT enddate FROM travelerinfo WHERE idcard='" . I('post.idcard') . "')";
        $date     = $traveler->query($sqlStr);
        // if (strtotime(I('post.startdate'))>strtotime($date[0]['max(enddate)'])) {
        //可以录入
        echo json_encode($date, JSON_PRETTY_PRINT);
    }
    public function ajaxCalcPrice()
    {
        gettype(I('post.total'));
        $sumTraveler = I('post.total');
        $scheme      = I('post.scheme');
        $schemename  = I('post.schemename');
        $day         = I('post.day');
        $schemeList  = M('pricescheme');
        $sqlStr      = "select * from pricescheme where id='" . $scheme . "'";
        $scheme      = $schemeList->query($sqlStr);
        switch (I('post.travelclass')) {
            // 国内游
            case '0':
                if ((int) $day <= 2) {
                    $price = (float) $scheme[0]['price1'] * (int) $sumTraveler;
                }
                if ((int) $day >= 3 && (int) $day <= 4) {
                    $price = (float) $scheme[0]['price2'] * (int) $sumTraveler;
                }
                if ((int) $day >= 5 && (int) $day <= 7) {
                    $price = (float) $scheme[0]['price3'] * (int) $sumTraveler;
                }
                if ((int) $day >= 8 && (int) $day <= 15) {
                    $price = (float) $scheme[0]['price4'] * (int) $sumTraveler;
                }
                if ((int) $day >= 16) {
                    $diff  = (int) $day - 15;
                    $price = ((float) $scheme[0]['price4'] + $diff * (float) $scheme[0]['price5']) * (int) $sumTraveler;
                }
                break;
            case '1':
                if ($schemename != '未成年人方案') {
                    if ((int) $day <= 10) {
                        $price = (float) $scheme[0]['price1'] * (int) $sumTraveler;
                    }
                    if ((int) $day >= 11 && (int) $day <= 15) {
                        $price = (float) $scheme[0]['price2'] * (int) $sumTraveler;
                    }
                    if ((int) $day >= 16 && (int) $day <= 30) {
                        $price = (float) $scheme[0]['price3'] * (int) $sumTraveler;
                    }
                    if ((int) $day >= 31) {
                        $diff  = (int) $day - 30;
                        $price = ((float) $scheme[0]['price3'] + $diff * (float) $scheme[0]['price4']) * (int) $sumTraveler;
                    }
                } else {

                    if ((int) $day <= 2) {
                        $price = (float) $scheme[0]['price1'] * (int) $sumTraveler;
                    }
                    if ((int) $day >= 3 && (int) $day <= 4) {
                        $price = (float) $scheme[0]['price2'] * (int) $sumTraveler;
                    }
                    if ((int) $day >= 5 && (int) $day <= 7) {
                        $price = (float) $scheme[0]['price3'] * (int) $sumTraveler;
                    }
                    if ((int) $day >= 8 && (int) $day <= 15) {
                        $price = (float) $scheme[0]['price4'] * (int) $sumTraveler;
                    }
                    if ((int) $day >= 16) {
                        $diff  = (int) $day - 15;
                        $price = ((float) $scheme[0]['price4'] + $diff * (float) $scheme[0]['price5']) * (int) $sumTraveler;
                    }
                }

                break;
        }
        echo $price;
    }
    public function submit()
    {
        $sysnum = date("YmdHis") . $newcode = mt_rand(10, 99);

        $infoData = array('id' => $sysnum, #订单号
        'userid'               => session('id'), #投保帐号
        'username'             => session('username'), #投保人名称
        'priceschemeid'        => I('post.scheme'), #价格方案编号
        'startdate'            => I('post.startdate'), #开始日期
        'enddate'              => I('post.enddate'), #结束日期
        'travelline'           => I('post.travelline'), #旅游线路
        'guide'                => I('post.guide'), #导游名称
        'teamnum'              => I('post.teamnum'), #旅游团号
        'pricesum'             => I('post.pricesum'), #合计金额
        'travelclass'          => I('post.travelclass'), #国内游，出境游
        'day'                  => I('post.day'), #旅游天数
        'total'                => I('sumtraveler'), #总人数
        'ordertime'            => substr($sysnum, 0, 4) . '-' . substr($sysnum, 4, 2) . '-' . substr($sysnum, 6, 2) . ' ' . substr($sysnum, 8, 2) . ':' . substr($sysnum, 10, 2) . ':' . substr($sysnum, 12, 2));
        $table = M('insureinfo');
        if (!$table->autoCheckToken($_POST)) {
            //令牌验证错误
            $this->error('请不要重复提交！');
        }
        if ($table->add($infoData)) {

            $data        = array();
            $table       = M('travelerinfo');
            $sumTraveler = (int) I('post.sumTraveler');
            foreach ($_POST as $key => $val) {
                switch ($key) {
                    case substr($key, 0, 4) == 'name':
                        $data['name'] = $val;
                        break;
                    case substr($key, 0, 8) == 'cardtype':
                        $data['cardtype'] = $val;
                        break;
                    case substr($key, 0, 6) == 'idcard':
                        $data['idcard'] = $val;
                        break;
                    case substr($key, 0, 8) == 'birthday':
                        $data['birthday'] = str_replace('/', '-', $val);
                        break;
                    case substr($key, 0, 3) == 'sex':
                        $data['sex']       = $val;
                        $data['startdate'] = I('post.startdate');
                        $data['enddate']   = I('post.enddate');
                        $data['sysnum']    = $sysnum;
                        $data['userid']    = I('session.id');
                        $datalist[]        = $data;
                        break;
                }
                if ($key == '__hash__') {
                    break;
                }
            }
            if ($table->addAll($datalist)) {
                $this->success("录入完成。", U('InfoInsert/Index'));
                $this->sendMail($infoData, $datalist, I('post.scheme_name'));
            } else {
                $table->where('sysnum=' . $sysnum)->delete();
                $this->success("名单录入失败，请重新输入。", U('InfoInsert/Index'));
            }
        } else {
            $table->delete($sysnum);
            $this->success("基础信息录入失败，请重新输入。", U('InfoInsert/Index'));
        }

        // var_dump(I('post.'));
    }
    public function sendMail($orderInfo, $travelerList, $schemeName)
    {
        $body        = "姓名 证件类别 证件号码 出生年月 性别 开始时间 结束时间 订单编号 价格方案 总社名称" . "\r\n";
        $user        = M('user');
        $sqlStr      = "SELECT findManageId('" . $orderInfo['userid'] . "') from user limit 1";
        $superior    = $user->query($sqlStr);
        $filePath    = 'D:/autoinsert/file/' . $orderInfo['id'] . '.txt';
        $autoit_file = fopen($filePath, x);
        fwrite($autoit_file, iconv('UTF-8', 'GB2312//IGNORE', "姓名 证件类别 证件号码 出生年月 性别 开始时间 结束时间 订单编号 价格方案 总社名称") . "\r\n");
        foreach ($travelerList as $travelerInfo) {
            $travelerInfoStr = '';
            foreach ($travelerInfo as $key => $val) {
                if ($key=='userid') {
                    continue;
                }
                if ($travelerInfoStr == '') {
                    $travelerInfoStr = $val;
                } else {
                    $travelerInfoStr = $travelerInfoStr . ' ' . $val;
                }
            }
            $travelerInfoStr = $travelerInfoStr . ' ' . $schemeName . ' ' . $superior[0]["findmanageid('" . $orderInfo['userid'] . "')"];
            $body .= $travelerInfoStr . "\r\n";
            fwrite($autoit_file, iconv('UTF-8', 'GB2312//IGNORE', $travelerInfoStr) . "\r\n");
        }
        fclose($autoit_file);
        sendMail($orderInfo['id'], $body, $filePath);
    }
    public function jiangTaiInsert($orderInfo, $travelerList, $schemeName)
    {
        $body        = "姓名 证件类别 证件号码 出生年月 性别 开始时间 结束时间 订单编号 投保账号 价格方案 总社名称" . "\r\n";
        $user        = M('user');
        $sqlStr      = "SELECT findManageId('" . $orderInfo['userid'] . "') from user limit 1";
        $superior    = $user->query($sqlStr);
        $filePath    = 'D:/autoinsert/file/' . $orderInfo['id'] . '.txt';
        $autoit_file = fopen($filePath, x);
        fwrite($autoit_file, iconv('UTF-8', 'GB2312//IGNORE', "姓名 证件类别 证件号码 出生年月 性别 开始时间 结束时间 订单编号 投保账号 价格方案 总社名称") . "\r\n");
        foreach ($travelerList as $travelerInfo) {
            $travelerInfoStr = '';
            foreach ($travelerInfo as $key => $val) {
                if ($travelerInfoStr == '') {
                    $travelerInfoStr = $val;
                } else {
                    $travelerInfoStr = $travelerInfoStr . ' ' . $val;
                }
            }
            $travelerInfoStr = $travelerInfoStr . ' ' . $schemeName . ' ' . $superior[0]["findmanageid('" . $orderInfo['userid'] . "')"];
            $body .= $travelerInfoStr . "\r\n";
            fwrite($autoit_file, iconv('UTF-8', 'GB2312//IGNORE', $travelerInfoStr) . "\r\n");
        }
        fclose($autoit_file);
        sendMail($orderInfo['id'], $body, $filePath);
    }
}
