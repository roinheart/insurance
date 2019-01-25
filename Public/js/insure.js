//insertOrEdit:0为插入数据，1为修改数据,2为模版批量上传
var jsonObj, tplCount = insertOrEdit = index = sumTraveler = sumKids = errtplCount = 0,
    ajaxResult = false,
    idcardExsist = false,
    tipsDate = '';
var X = XLSX;

$(document).ready(function() {
    $('#scheme').val($.cookie('scheme'));
    $('#schemename').text($.cookie('schemename'));
    $('#scheme_name').val($.cookie('schemename'));
    // 出境未成年人时间
    if ($('#schemename').text == '未成年人方案') {
        $('.selopt').css('display', 'none');
    }
    switch ($('.usertype').val()) {
        case '3':
            $('.SellsRoomInclude').css('display', 'none');
            break;
        case '4':
            $('.BranchInclude').css('display', 'none');
            break;
    }
    if ($('#priceshow').val() == '0') {
        $('#divtotalprice').css('display', 'none');
    }
    fnSetStartDate();
    fnSetDate();
    //Date picker
    $('#startdate').datepicker({
        autoclose: true,
        todayHighlight: true,
        language: "zh-CN",
        format: "yyyy-mm-dd",
    });
    $('#startdate').change(function() {
        if (sumTraveler != 0) {
            $('.sectioninfo').each(function(index, el) {
                fnAjaxPostCheckTraveler($(el).find('.idcard').val(), $('#startdate').val());
                if (!ajaxResult) {
                    alert($(el).find('.travelerName').val() + '游客在' + tipsDate + '有未结束行程，游客信息已删除。');
                    sumTraveler--;
                    $('#total').text(sumTraveler + ' 人');
                    fnCalcPrice();
                    $(el).remove();
                }
            });
        }
        var timesTamp = new Date($('#startdate').val().replace(/\-/g, "/"));
        var today = new Date();
        today = new Date(today.getFullYear() + "/" + (today.getMonth() + 1) + "/" + today.getDate());
        if (timesTamp >= today) {
            fnSetDate();
        } else {
            $('#startdate').datepicker("setDate", today);
            $('#msg1').text('日期选择错误');
            $("#modal-info1").modal("show");
        }
    });
    $('#day').change(function() {
        fnSetDate();
        if (sumTraveler != 0) {
            $('.sectioninfo').each(function(index, el) {
                fnAjaxPostCheckTraveler($(el).find('.idcard').val(), $('#startdate').val());
                if (!ajaxResult) {
                    alert($(el).find('.travelerName').val() + '游客在' + tipsDate + '有未结束行程，游客信息已删除。');
                    $(el).remove();
                }
            });
            fnCalcPrice();
        }
    });
    document.getElementById('stdupload').addEventListener('change', handleFile);
});

function fnOnsubmit() {
    var d1 = new Date($('#startdate').val().replace(/\-/g, "\/"));
    var currentTime = new Date();
    var today = new Date();
    var todayTowOclock = new Date(today.getFullYear() + "/" + (today.getMonth() + 1) + "/" + today.getDate() + ' 22:00:00');
    today = new Date(today.getFullYear() + "/" + (today.getMonth() + 1) + "/" + today.getDate());
    if (currentTime >= todayTowOclock && (d1 - today) == 0) {
        alert('22点后不可录入当天订单！');
        return false;
    } else {
        if (sumTraveler <= 0) {
            alert("未添加游客，不能保存订单。")
            return false;
        } else {
            if (confirm('是否提交？')) {
                return true;
            } else {
                return false;
            }
        }
    }
}

function fnIsIOS() {
    var u = navigator.userAgent;
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    return isiOS;
}

function fnSetStartDate() {
    var d1 = new Date(); //获取当前时间
    var date = fnAppendzero(d1.getFullYear()) + "-" + fnAppendzero(d1.getMonth() + 1) + "-" + fnAppendzero(d1.getDate());
    $("#startdate").val(date);
}

function fnSetDate() {
    var date = fnAddDate($('#startdate').val(), $('#day').val());
    $("#enddate").val(date);
    $("[name='enddate']").val(date);
}


//计算增加日期，date为日期，days为增加天数
function fnAddDate(date, days) {
    date = date.replace(/\-/g, "/");
    var timesTamp = Date.parse(new Date(date));
    var timesTamp2 = (days - 1) * 86400;
    timesTamp = timesTamp / 1000;
    timesTamp2 = timesTamp + timesTamp2;
    var newDate = new Date();
    newDate.setTime(timesTamp2 * 1000);
    var result = fnAppendzero(newDate.getFullYear()) + "-" + fnAppendzero(newDate.getMonth() + 1) + "-" + fnAppendzero(newDate.getDate())
    return result;
}

function fnAppendzero(obj) {
    if (obj < 10) return "0" + "" + obj;
    else return obj;
}
var l = 0;

function handleFile(e) {
    do_file(e.target.files);
    $('#stdupload').attr('type', 'text');
    $('#stdupload').attr('type', 'file');
}

function do_file(files) {
    var f = files[0];
    var reader = new FileReader();
    reader.onload = function(e) {
        var data = e.target.result;
        process_wb(X.read(data, { type: 'binary' }));
    };
    reader.readAsBinaryString(f);
}

function process_wb(wb) {
    $('#errlist').html('');
    var output = "";
    output = to_json(wb);
    output = JSON.parse(JSON.stringify(output));
    output = eval('(' + output.replace(/\s+/g, "") + ')');
    getTravelerList(output);
    fnCalcPrice();
}

function fnCalcPrice() {
    $.ajax({
        type: 'POST',
        url: ajaxCalcPrice,
        data: { 'total': sumTraveler, 'scheme': $('#scheme').val(), 'day': $('#day').val(), 'travelclass': $('#travelclass').val(), 'schemename': $('#schemename').text() },
        async: false,
        complete: function(e) {
            $('#totalprice').text(e.responseText + " 元");
            $('#pricesum').val(e.responseText);
        },
        dataType: 'text'
    });
}

function to_json(workbook) {
    var result = {};
    workbook.SheetNames.forEach(function(sheetName) {
        var roa = X.utils.sheet_to_json(workbook.Sheets[sheetName], { header: 1 });
        if (roa.length) result[sheetName] = roa;
    });
    return JSON.stringify(result, 2, 2);
}


function getTravelerList(obj) {
    var jsonObj = obj;
    var firstRow = ['序号', '游客姓名', '证件类型', '证件号码', '出生日期', '性别'];
    $.each(jsonObj, function(idx, obj1) {
        $.each(obj1, function(idx, obj2) {
            // if (obj2[1] != null) 
            if (idx == 9) {
                for (var i = 0; i < obj2.length; i++) {
                    if (obj2[i] != firstRow[i]) {
                        $('#msg2').text("请勿修改模版结构！重新上传。");
                        $("#modal-info2").modal("show");
                        return;
                    }
                }
            }
            if (idx > 9) {
                for (var i = 0; i < obj2.length; i++) {
                    if (obj2[1] != null && obj2[2] != null && obj2[3] != null && obj2[4] != null && obj2[5] != null) {
                        insertOrEdit = 2;
                        fnInsertInfo(obj2);
                        return;
                    }
                }
            }

        });
    });
}

function fnInsertTpl(name, cardtype, idcard, birthday, sex) {
    sumTraveler++;
    var traveler = new Array(fnHiddenTravelerInfo(name, idcard));
    var showname = traveler[0][0];
    var showidcard = traveler[0][1];
    if (!tplCount) {
        tplCount++;
        var jsonStr = { "No": tplCount, "name": name, "cardtype": cardtype, "idcard": idcard, "birthday": birthday, "sex": sex, "showname": showname, "showidcard": showidcard };
        var tpl = document.getElementById('tpl').innerHTML;
        document.getElementById('list').innerHTML = doT.template(tpl)(jsonStr);
    } else {
        tplCount++;
        var jsonStr = { "No": tplCount, "name": name, "cardtype": cardtype, "idcard": idcard, "birthday": birthday, "sex": sex, "showname": showname, "showidcard": showidcard };
        var tpl = document.getElementById('tpl').innerHTML;
        document.getElementById('list').innerHTML = document.getElementById('list').innerHTML + doT.template(tpl)(jsonStr);
        window.location.href = "#info" + tplCount;
    }
    $('#total').text(sumTraveler + ' 人');
    $('#sumtraveler').val(sumTraveler);
    fnResetEle('modal');
}

function fnInsertErrTpl(name, reason) {
    if (!errtplCount) {
        errtplCount++;
        var jsonStr = { "name": name, "reason": reason };
        var tpl = document.getElementById('errtpl').innerHTML;
        document.getElementById('errlist').innerHTML = doT.template(tpl)(jsonStr);
    } else {
        errtplCount++;
        var jsonStr = { "name": name, "reason": reason };
        var tpl = document.getElementById('errtpl').innerHTML;
        document.getElementById('errlist').innerHTML = document.getElementById('errlist').innerHTML + doT.template(tpl)(jsonStr);
    }
}

function fnDelTraveler(no) {
    if (confirm("是否删除" + $('#name' + no).val() + "游客信息")) {
        $('#info' + no).remove();
        sumTraveler--;
        $('#total').text(sumTraveler + ' 人');
        $('#sumtraveler').val(sumTraveler);
        fnCalcPrice();
    }
}

function fnEdit(no) {
    index = no;
    insertOrEdit = 1;
    $('#name').val($('#name' + index).val());
    $('#cardtype').val($('#cardtype' + index).val());
    $('#idcard').val($('#idcard' + index).val());
    $('#birthday').val($('#birthday' + index).val());
    $('#sex').val($('#sex' + index).val());
    $('#modal-traveler').modal('show');
}

function fnSelectChange() {
    if ($('#cardtype').val() != "身份证") {
        $('#idcard').val('');
        $('#birthday').attr('disabled', false);
        $('#sex').attr('disabled', false);
        $('#birthday').datepicker({
            autoclose: true,
            todayHighlight: true,
            language: "zh-CN",
            format: "yyyy-mm-dd",
        });
    } else {
        $('#birthday').attr('disabled', true);
        $('#sex').attr('disabled', true);
        $('#idcard').val('');
        $('#birthday').val('');
        $('#sex').val('');
    }
}

function fnCheckIdCard() {
    if ($('#idcard').val().length == 18 && !fnSetTravelerInfo()) {
        if ($('#cardtype').val() == "身份证") {
            alert("请输入正确的身份证号！");
            $("#idcard").val("");
            $("#birthday").val("");
            $("#sex").val("");
        }
    }
}
//此函数需要提高效率，做到批量提交
function fnAjaxPostCheckTraveler(idcard, startdate) {
    ajaxResult = false;
    $.ajax({
        type: 'POST',
        url: ajaxCheckTraveler,
        data: { 'idcard': idcard, 'startdate': startdate },
        async: false,
        complete: function(e) {
            try {
                if (e['responseJSON'][0]['max(enddate)'] == null) {
                    ajaxResult = true;
                    return;
                }
                if (e['responseJSON'][0]['max(enddate)'] != 'null') {
                    var d1 = Date.parse(new Date($('#startdate').val().replace(/\-/g, "\/")));
                    var d2 = Date.parse(new Date(e['responseJSON'][0]['max(startdate)'].replace(/\-/g, "\/")));
                    var d3 = Date.parse(new Date(e['responseJSON'][0]['max(enddate)'].replace(/\-/g, "\/")));
                    if (d1 > d3 || Number($('#day').val()) <= ((d2 - d1) / 86400000)) {
                        ajaxResult = true;
                        return;
                    }
                    tipsDate = e['responseJSON'][0]['max(startdate)'] + "-" + e['responseJSON'][0]['max(enddate)'];
                }
            } catch (e) {
                console.log(ajaxResult);
            }
        },
        dataType: 'json'
    });
}

function fnResetEle(eleClass) {
    switch (eleClass) {
        case 'modal':
            $('#name').val('');
            $('#cardtype').val('身份证');
            $('#idcard').val('');
            $('#birthday').val('');
            $('#sex').val('');
            $('#scanIdcard').val('');
            $('#birthday').attr('disabled', true);
            $('#sex').attr('disabled', true);
    }
}

function fnHiddenTravelerInfo(name, idcard) {
    var result = new Array(1);
    result[0] = '*' + name.substring(1, name.length);
    if (idcard.length == 18) {
        result[1] = idcard.substring(0, 3) + '************' + idcard.substring(15, idcard.length);
    } else {
        result[1] = idcard.substring(0, 3) + '*********' + idcard.substring(15, idcard.length);
    }
    return result;
}

function fnCheckCardType() {
    if ($('#cardtype').val() != "身份证") {
        $('#msg2').text("证件类型错误，请选择'身份证'");
        $("#modal-info2").modal("show");
        return false;
    } else {
        return true;
    }
}

function fnCheckFileType(img) {
    if (!(/\.(?:jpg|jpeg|png|bmp|gif)$/i.test(img.files[0].name))) {
        alert("身份证只支持jpg,jpeg,bmp,bnp,gif格式上传！");
        return false;
    } else {
        fnCreateURL(img);
    }
}

function fnCreateURL(source) {
    var file = source.files[0];
    if (window.FileReader) {
        var fr = new FileReader();
        fr.onload = function(e) {
            if (file.size > 200000) {
                fnGetBase64(e.target.result);
            } else {
                fnAjaxPost(e.target.result);
            }

        }
        fr.readAsDataURL(file);
    }
}

function fnGetBase64(URL) {
    var img = new Image,
        width = 640,
        quality = 0.6,
        canvas = document.createElement("canvas"),
        drawer = canvas.getContext("2d"),
        dataURL = '';
    img.src = URL;
    img.onload = function() {
        canvas.width = width;
        canvas.height = width * (img.height / img.width);
        drawer.drawImage(img, 0, 0, canvas.width, canvas.height);
        dataURL = canvas.toDataURL("image/jpeg", quality);
        fnAjaxPost(dataURL);
    }
}

function fnAjaxPost(dataURL) {
    $.ajax({
        type: 'POST',
        url: ajaxScanIdcard,
        data: { 'dataURL': dataURL },
        async: false,
        success: function(result) {
            //返回身份证信息
            var msg = JSON.parse(JSON.stringify(result));
            getJson(msg);
        },
        dataType: 'json'
    });
}

function getJson(msg) {
    var jsonObj = [msg];
    $.each(jsonObj, function(idx, obj1) {
        if (obj1.image_status == "normal") {
            $.each(obj1.words_result, function(idx, obj2) {
                switch (idx) {
                    case '姓名':
                        $('#name').val(obj2.words);
                        break;
                    case '公民身份号码':
                        $('#idcard').val(obj2.words);
                        break;
                }
            });
            fnSetTravelerInfo();
        } else {
            alert("照片拍摄不清楚。请保持照片清晰！");
            return false;
        }
    });
}

function fnSetTravelerInfo() {
    var tshenfenID = $("#idcard").val();
    if (IdCardValidate(tshenfenID)) {
        $("#birthday").val(birthDayByIdCard(tshenfenID));
        if (maleOrFemalByIdCard(tshenfenID) == "男") {
            $("#sex").val('男');
        } else {
            $("#sex").val('女');
        }
        return true;
    } else {
        return false;
    }
}
/*根据出生日期算出年龄*/
function fnGetAge(strBirthday) {
    if (strBirthday == undefined) { return };
    var returnAge;
    var strBirthdayArr = strBirthday.split("-");
    if (strBirthdayArr.length == 1) {
        var strBirthdayArr = strBirthday.split("/");
    }
    var birthYear = strBirthdayArr[0];
    var birthMonth = strBirthdayArr[1];
    var birthDay = strBirthdayArr[2];

    d = new Date($('#startdate').val()); //按起保时间计算年龄
    var nowYear = d.getFullYear();
    var nowMonth = d.getMonth() + 1;
    var nowDay = d.getDate();
    if (nowYear == birthYear) {
        returnAge = 0; //同年 则为0岁  
    } else {
        var ageDiff = nowYear - birthYear; //年之差
        if (ageDiff > 0) {
            if (nowMonth == birthMonth) {
                var dayDiff = nowDay - birthDay; //日之差  
                if (dayDiff < 0) {
                    returnAge = ageDiff - 1;
                } else {
                    returnAge = ageDiff;
                }
            } else {
                var monthDiff = nowMonth - birthMonth; //月之差  
                if (monthDiff < 0) {
                    returnAge = ageDiff - 1;
                } else {
                    returnAge = ageDiff;
                }
            }
        } else {
            returnAge = -1; //返回-1 表示出生日期输入错误 晚于今天  
        }
    }
    return returnAge; //返回周岁年龄  
}

function fnDiscern2(data) {
    $('#errlist').html('');
    var restr = /.*(\n|$)/g;
    var sub = data.match(restr);
    for (var i = 0; i < sub.length - 1; i++) {
        var info = new Array();
        var name = '',
            gender = '',
            certCode = '',
            certTp = '',
            birthday = '';
        name = $.copyVTypesCls.cnname(sub[i]);
        if (name == '') {
            name = $.copyVTypesCls.enname(sub[i]);
        }
        if (name == '') {
            fnInsertErrTpl(sub[i], '名称未输入');
            continue;
        }
        info.push(name);
        gender = $.copyVTypesCls.gender(sub[i]);
        switch (gender) {
            case '男':
            case '男性':
            case 'Male':
            case 'M':
            case 'MR':
                gender = '男';
                break;
            case '女':
            case '女性':
            case 'Famale':
            case 'Female':
            case 'F':
            case 'MS':
            case 'MRS':
            case 'MISS':
                gender = '女';
                break;
        }
        info.push(gender);
        certCode = $.copyVTypesCls.certCode(sub[i]);
        if (certCode == '') {
            fnInsertErrTpl(sub[i], '证件号码未输入');
            continue;
        } else {
            if (certCode.length == 18 & !IdCardValidate(certCode)) {
                fnInsertErrTpl(sub[i], '身份证号码错误');
                continue;
            }
        }
        info.push(certCode);
        certTp = $.copyVTypesCls.certTp(sub[i]);
        if (certTp = 'CEP') {
            certTp = '护照';
        }
        if (certTp == '') {
            fnInsertErrTpl(sub[i], '证件类别未录入');
            continue;
        }
        info.push(certTp);
        birthday = $.copyVTypesCls.birth(sub[i]);
        if (certCode.length < 18 && birthday == '') {
            fnInsertErrTpl(sub[i], '出生年月日未输入');
            continue;
        }
        info.push(birthday);
        insertOrEdit = 3;
        fnInsertInfo(info);
    }
    fnCalcPrice();
    $('#pastelist').val('');
}

function check(name, idno) {
    fnAjaxPostCheckTraveler(idno, $('#startdate').val());
    if (ajaxResult) {
        alert('该游客在' + tipsDate + '有未结束行程，不能录入。');
        fnResetEle('modal');
        return;
    }
    $('section').each(function(index, el) {
        if (idno == $(el).find('.idcard').val() & $(el).find('.idcard').val() != '') {
            fnInsertErrTpl(name, '请勿重复录入');
            idcardExsist = true;
            return;
        }
    });
}

function OutOfRang(bir) {
    mindate = fnAddDate($('#birthday').val(), 30);
    var min = new Date(mindate); //最小投保出生日期
    var startdate = new Date($('#startdate').val()); //按起保时间计算
    if (min >= startdate) {
        alert('出生未满30天，很抱歉本公司不能为您提供服务！')
        return;
    }
    if (fnGetAge(obj[13]) < 18 && $('#schemename').text().replace(/ /g, '') != '未成年人方案') {
        alert('未成年人使用《未成年人方案》！');
        return;
    }
    if (fnGetAge($('#birthday').val()) > 85) {
        alert('年龄大于85岁，很抱歉本公司不能为您提供服务！');
        return;
    }
}
(function($, document) {
    //通过正则表达式->指定数值
    $.copyVTypesCls = {
        mobile: function(a) {
            var b = /\s+(1[3587]\d{9})(?=\s+|$)/,
                c = a.match(b);
            return c && c.length > 0 ? $.trim(c[0]) : ""
        },
        cnname: function(a) {
            var c, d, b = /男性|女性|Famale|Female|Male|护照|身份证|其他|其它|\s+男|\s+女/gi;
            return a = a.replace(b, ""), c = /((?!\d)(?![a-zA-Z])[一-龿]\s*){2,4}(?=[a-zA-Z\d.\/\s]+|$)/, d = a.match(c), d && d.length > 0 ? $.trim(d[0].replace(/\s*/g, "")) : ""
        },
        enname: function(a) {
            var d, e, b = /\s*(([a-zA-Z]{2,}[\s\/]*)+)(?=[.\s]+|$)/,
                c = a.replace(/\,|\，|\.|\。|\//g, " ").match(b);
            return c && c.length > 0 ? (d = /ADULT|CHILD|ADT|CHD|Famale|Female|Male|CHN|\s+MR\s*|\s+MS\s*|(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\/+/gi, e = $.trim(c[0].replace(d, "")), e.indexOf("/") < 0 ? e.replace(/,|，|\s+/g, " ") : e.replace(/,|，/g, " ")) : ""
        },
        birth: function(a) {
            var b, c, d, e, f, g, h, i, j, k, l, m;
            if (i = /\s+(19\d{2}|20[0-2][0-9]|201[0-9])[\/.-]((0{0,1}[1-9])|(1[0-2]))[\/.-](([1-2][0-9])|(3[0-1])|(0{0,1}[1-9]))/, b = a.match(i), b && b.length > 0) return $.trim(b[0].replace(/[.\/]/g, "-"));
            if (j = /\s+(19\d{2}|20[0-2][0-9]|201[0-9])[年]((0{0,1}[1-9])|(1[0-2]))[月](([1-2][0-9])|(3[0-1])|(0{0,1}[1-9]))/, b = a.match(j), b && b.length > 0) return $.trim(b[0].replace(/[年月]/g, "-"));
            if (k = /\s+(19\d{2}|20[0-2][0-9]|201[0-9])(0[1-9]|1[0-2]{1})([1-2][0-9]|3[0-1]|0[1-9])/, b = a.match(k), b && b.length > 0) return $.trim(b[1] + "-" + b[2] + "-" + b[3]);
            if (l = /\s+(0{0,1}[1-9]|[1-2][0-9]|3[0-1])[\/.-](0{0,1}[1-9]|1[0-2]|[a-zA-Z]{3,})[\/.-]((19\d{2}|20[0][0-9]|201[012345])|(1[012345]|[03456789]\d))/, b = a.match(l), b && b.length > 3) return c = b[2].toUpperCase(), e = {
                JAN: 1,
                FEB: 2,
                MAR: 3,
                APR: 4,
                MAY: 5,
                JUN: 6,
                JUL: 7,
                AUG: 8,
                SEP: 9,
                OCT: 10,
                NOV: 11,
                DEC: 12
            }, e[c] && (c = e[c]), f = b[3], 2 == f.length && (m = f.substr(0, 1), f = "0" == m || "1" == m ? "20" + f : "19" + f), $.trim(f + "-" + c + "-" + b[1]);
            return ""
        },
        gender: function(a) {
            var b = /\s+(男|女|男性|女性|Famale|Female|Male|F|M|MR|MS|MRS|MISS)(?=\s+|$)/i,
                c = a.match(b);
            return c && c.length > 0 ? $.trim(c[0]) : ""
        },
        certCode: function(a) {
            var b, c;
            return a = a.replace(/х|ｘ/g, "X"), b = /(\d{15,18}[xX]?|[a-zA-Z]{1,2}\d{6,11}|[Ll]\d{17}|\d{2}[a-zA-Z]{2}\d{5}|\s+(?!19|20)\d{7,10})(?=[.\/\s]+|$)/, c = a.match(b), c && c.length > 0 ? $.trim(c[0]) : ""
        },
        certTp: function(a) {
            var b = /\s+(护照|身份证|其他|CEP)(?=\s+|$)/,
                c = a.match(b);
            return c && c.length > 0 ? $.trim(c[0]) : ""
        }
    }
})(jQuery, document);