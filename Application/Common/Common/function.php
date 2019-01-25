<?php
function sendMail($title,$body,$filePath)
{
    vendor('PHPMailer.class#phpmailer');

    vendor('PHPMailer.class#smtp');

    $mail = new \PHPMailer();
    // 使用smtp鉴权方式发送邮件
    $mail->isSMTP();
    // smtp需要鉴权 这个必须是true
    $mail->SMTPAuth = true;
    // 链接qq域名邮箱的服务器地址
    $mail->Host = 'smtp.qq.com';
    // 设置使用ssl加密方式登录鉴权
    $mail->SMTPSecure = 'ssl';
    // 设置ssl连接smtp服务器的远程服务器端口号
    $mail->Port = 465;
    // 设置发送的邮件的编码
    $mail->CharSet = 'UTF-8';
    // 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
    $mail->FromName = '战斗机器人';
    // smtp登录的账号 QQ邮箱即可
    $mail->Username = 'roinheart@qq.com';
    // smtp登录的密码 使用生成的授权码
    $mail->Password = 'nrsxryovypjkbijf';
    // 设置发件人邮箱地址 同登录账号
    $mail->From = 'roinheart@qq.com';
    // 邮件正文是否为html编码 注意此处是一个方法
    $mail->isHTML(true);
    // 设置收件人邮箱地址
    $mail->addAddress('449554798@qq.com');
    // $mail->addAddress('361381214@qq.com');
    // 添加该邮件的主题
    $mail->Subject = $title;
    // 添加邮件正文
    $mail->Body = $body;
    // 为该邮件添加附件
    $mail->addAttachment($filePath);
    // 发送邮件 返回状态
    $status = $mail->send();
}
