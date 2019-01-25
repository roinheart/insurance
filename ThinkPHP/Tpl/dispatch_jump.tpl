<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>__WEBNAME__提示</title>
        <!-- Bootstrap 3.3.7 -->
        <link rel="stylesheet" href="__B__/bootstrap/dist/css/bootstrap.min.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="__D__/css/AdminLTE.min.css">
        <link rel="stylesheet" href="__D__/css/loader.css">
        <style type="text/css">
        b,
        .h3 {
            font-size: 30px;
            color: #fff;
        }
        </style>
    </head>

    <body style="background-color: #3c4a87;">
        <div class="login-box">
            <div class="login-box-body" style="background-color: #4f4f90;">
                <div class="form-group has-feedback">
                    <h3><p align="center"><b>
                    <?php if(isset($message)) {
                            echo($message);
                          }else{
                            echo($error);
                    }?>
                    </b>
                </p>
                </h3>
                    <div class="loader" align="center"></div>
                    <h3><p align="center">
                    <b>页面将在</b><b id="wait"><?php echo($waitSecond); ?></b><b>秒后</b><br><b>页面自动</b><a id="href" href="<?php echo($jumpUrl); ?>"><b>跳转</b></a>

                </p>
                </h3>
                </div>
            </div>
        </div>
        <script type="text/javascript">
        (function() {
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
            var interval = setInterval(function() {
                var time = --wait.innerHTML;
                if (time <= 0) {
                    location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
        </script>
    </body>

    </html>