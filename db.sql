-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2019-01-25 07:59:13
-- 服务器版本： 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db`
--

DELIMITER $$
--
-- 函数
--
CREATE DEFINER=`root`@`localhost` FUNCTION `findManageId`(`userid` VARCHAR(50) CHARSET utf8) RETURNS varchar(50) CHARSET utf8
    NO SQL
begin
declare managerId,managerName varchar(50) default '';
set managerId=(select superior from user where id=userid);
set managerName=(select name from user where id=managerId);
while managerId <>'admin' do
    if (select superior from user where id=managerId)='admin' then
    	set managerName=(select name from user where id=managerId);
        return managerName;
    end if;
    set managerId=(select superior from user where id=managerId);
end while;
return managerName;
end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `accident`
--

CREATE TABLE IF NOT EXISTS `accident` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'AccidentID',
  `userid` varchar(50) NOT NULL COMMENT '用户账号',
  `sysnum` varchar(50) NOT NULL COMMENT '订单编号',
  `accidentdate` datetime NOT NULL COMMENT '事故发生时间',
  `position` varchar(200) NOT NULL COMMENT '事故发生地点',
  `travelername` varchar(50) NOT NULL COMMENT '游客姓名',
  `idcard` varchar(50) NOT NULL COMMENT '游客证件号码',
  `travelerphoneno` varchar(50) NOT NULL COMMENT '游客电话号码',
  `injured` varchar(200) NOT NULL COMMENT '游客受伤部位',
  `reportname` varchar(50) NOT NULL COMMENT '报案人姓名',
  `reportphoneno` varchar(50) NOT NULL COMMENT '报案人电话号码',
  `familyname` varchar(50) DEFAULT NULL COMMENT '家属姓名',
  `familyphoneno` varchar(50) DEFAULT NULL COMMENT '家属电话号码',
  `gameover` varchar(1) NOT NULL DEFAULT '0' COMMENT '死亡',
  `reportdate` date NOT NULL COMMENT '报案日期',
  `remarks` varchar(500) DEFAULT NULL COMMENT '备注',
  `checked` int(1) NOT NULL DEFAULT '0' COMMENT '管理员是否查看',
  `sms` int(1) NOT NULL DEFAULT '0' COMMENT '短信是否发送',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='报案表' AUTO_INCREMENT=78 ;

-- --------------------------------------------------------

--
-- 表的结构 `insureinfo`
--

CREATE TABLE IF NOT EXISTS `insureinfo` (
  `id` varchar(50) NOT NULL COMMENT '基本信息表编号',
  `userid` varchar(50) NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '签发人员名称',
  `priceschemeid` varchar(10) NOT NULL COMMENT '价格方案ID',
  `startdate` varchar(12) NOT NULL COMMENT '旅行开始时间',
  `enddate` varchar(12) NOT NULL COMMENT '旅行结束时间',
  `travelline` varchar(50) NOT NULL COMMENT '旅行线路',
  `guide` varchar(20) DEFAULT NULL COMMENT '导游',
  `teamnum` varchar(50) NOT NULL COMMENT '团队号',
  `pricesum` varchar(10) NOT NULL COMMENT '价格合计',
  `insurenum` varchar(50) DEFAULT NULL COMMENT '保单号',
  `pay` varchar(1) NOT NULL DEFAULT '0' COMMENT '付款标志，默认为未付款',
  `total` varchar(4) NOT NULL COMMENT '游客总数',
  `travelclass` varchar(1) NOT NULL COMMENT '旅游类型0：国内游，1：出境游',
  `day` varchar(5) NOT NULL COMMENT '旅游天数',
  `ordertime` varchar(30) NOT NULL COMMENT '下单时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `pricescheme`
--

CREATE TABLE IF NOT EXISTS `pricescheme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) NOT NULL COMMENT '产品名称',
  `name` varchar(50) NOT NULL,
  `price1` varchar(5) NOT NULL,
  `price2` varchar(5) NOT NULL,
  `price3` varchar(5) NOT NULL,
  `price4` varchar(5) NOT NULL,
  `price5` varchar(5) DEFAULT NULL,
  `rang` int(11) NOT NULL COMMENT '境内境外标志1境内，0境外',
  `enable` int(11) NOT NULL DEFAULT '1' COMMENT '是否禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=248 ;

-- --------------------------------------------------------

--
-- 表的结构 `signlog`
--

CREATE TABLE IF NOT EXISTS `signlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) NOT NULL,
  `logintime` datetime DEFAULT NULL,
  `logouttime` datetime DEFAULT NULL,
  `loginip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9355 ;

-- --------------------------------------------------------

--
-- 表的结构 `travelerinfo`
--

CREATE TABLE IF NOT EXISTS `travelerinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '游客信息编号',
  `name` varchar(20) NOT NULL COMMENT '游客名称',
  `cardtype` varchar(10) NOT NULL COMMENT '证件类型',
  `idcard` varchar(50) NOT NULL COMMENT '证件号码',
  `sex` varchar(2) NOT NULL COMMENT '性别',
  `birthday` date NOT NULL COMMENT '生日',
  `startdate` date NOT NULL,
  `enddate` date NOT NULL COMMENT '旅游结束日期',
  `sysnum` varchar(50) NOT NULL,
  `userid` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sysnum` (`sysnum`) COMMENT 'foreignkey'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=169860 ;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` varchar(50) NOT NULL COMMENT '会员编号',
  `name` varchar(50) NOT NULL COMMENT '用户名称',
  `username` varchar(50) NOT NULL COMMENT '使用人员名称',
  `password` varchar(50) NOT NULL COMMENT '密码',
  `question` varchar(50) DEFAULT NULL COMMENT '密码找回问题',
  `answer` varchar(50) DEFAULT NULL COMMENT '密码找回答案',
  `email` varchar(50) DEFAULT NULL COMMENT '邮箱50',
  `mobile` varchar(18) DEFAULT NULL COMMENT '手机号18',
  `code` varchar(10) DEFAULT NULL COMMENT '邀请码10',
  `usertype` varchar(2) NOT NULL COMMENT '会员类别',
  `regdate` date NOT NULL COMMENT '注册日期',
  `enable` varchar(1) NOT NULL COMMENT '是否禁用',
  `superior` varchar(50) DEFAULT NULL COMMENT '上级',
  `priceenable` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 限制导出的表
--

--
-- 限制表 `travelerinfo`
--
ALTER TABLE `travelerinfo`
  ADD CONSTRAINT `foreginkey` FOREIGN KEY (`sysnum`) REFERENCES `insureinfo` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
