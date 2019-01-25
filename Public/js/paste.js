var birthFmt = ''; // 英文格式=mdy 默认为空


function getValue() {
    var a = $("#pasteTxt").val();
    var array = new Array();
    var arrayCert = new Array();
    var errorStr = "";
    var orderCode = $("#applyOrderCode").val();

             $(".ToapPersonNumber").each(function () {
                 arrayCert.push($(this).val());
             });

    $.pasteCls.insuredPaste(a, function (retData, c, d, e, g) {

        if (retData && retData.length > 0) {
            $.each(retData, function (i, val) {

                //获取出生日期
                var birthVal = (val.birth == "") ? $.pasteCls.getBirthday(val.certCode) : val.birth;
                //获取性别
                var genderVal = (Validator.TypeValid("certCode", val.certCode) === !0) ? $.pasteCls.getGender(val.certCode) : val.gender;

                if (val.gender && "" != val.gender) {
                    switch (i = "M", val.gender.toLowerCase()) {
                        case "女":
                        case "f":
                        case "famale":
                        case "female":
                            i = "F";
                            break;
                        default:
                            i = "M"
                    }
                    genderVal = i;
                }
                ////获取证件类型
                var certTpVal = val.certTp;
                if (val.certTp && "" != val.certTp) {
                    switch (g = val.certTp, val.certTp) {
                        case "身份证":
                            g = "I";
                            break;
                        case "护照":
                            g = "P";
                            break;
                        case "其他":
                            g = "O"
                    }
                    certTpVal = g;
                }

                

                if (certTpVal == "I" && Validator.TypeValid("certCode", val.certCode) != true) {
                    errorStr = errorStr + "<tr><td> 姓名:" + val.name + "，&nbsp;证件号:" + val.certCode + "</td>&nbsp;<td>" + Validator.TypeValid("certCode", val.certCode) + "</td></tr>";
                } else if (certTpVal == "P" && Validator.TypeValid("passport", val.certCode) != true) {

                    errorStr = errorStr + "<tr><td> 姓名:" + val.name + "，&nbsp;护照号:" + val.certCode + "</td>&nbsp;<td>" + Validator.TypeValid("passport", val.certCode) + "</td></tr>";

                } else if (arrayCert.indexOf(val.certCode) < 0) {
                    if (birthVal == "")
                    {
                        birthVal = "1970-01-01";
                    }
                    var strNew = "cname=" + val.name + "&" + "cnum=" + val.certCode + "&" + "cgender=" + genderVal + "&" + "cbirthday=" + birthVal;
                    array.push(strNew);
                    arrayCert.push(val.certCode);
                } else {
                    errorStr = errorStr + "<tr><td> 姓名:" + val.name + "，&nbsp;证件号:" + val.certCode + "</td>&nbsp;<td>内容重复，请检查</td></tr>";
                }

                //var temp = '姓名:' + val.name + ',性别:' + genderVal + ',证件类型:' + certTpVal + ',证件号:' + val.certCode + ',生日:' + birthVal + ',手机号:' + val.mobile;
                //$("#returnData").append(temp + '<br/>')
            });
            if (array.length > 0) {
                AccidentOnlineIndex.SavePastePerson(array, orderCode);
                AccidentOnlineIndex.GetOrderPersons(orderCode, 0, 2000, protectedPerson.getPersonsCallback);
                array.length = 0;
            }

            if (errorStr !== "") {
                $("#paste").html("<table style='width:100%;'>" + errorStr + "</table>");
                $("#paste").show();

            } else {
                $("#paste").html("");
                $("#paste").hide();
               
            }
            $("#pasteTxt").val("");
            unpop({ success: !1 });


        }
    });
}


(function ($, document) {
    //通过正则表达式->指定数值
    $.copyVTypesCls = {
        mobile: function (a) {
            var b = /\s+(1[35879]\d{9})(?=\s+|$)/,
                c = a.match(b);
            return c && c.length > 0 ? $.trim(c[0]) : ""
        },
        cnname: function (a) {
            var c, d, b = /男性|女性|Famale|Female|Male|护照|身份证|其他|其它|\s+男|\s+女/gi;
            return a = a.replace(b, ""), c = /((?!\d)(?![a-zA-Z])[一-龿]\s*){2,4}(?=[a-zA-Z\d.\/\s]+|$)/, d = a.match(c), d && d.length > 0 ? $.trim(d[0].replace(/\s*/g, "")) : ""
        },
        enname: function (a) {
            var d, e, b = /\s*(([a-zA-Z]{2,}[\s\/]*)+)(?=[.\s]+|$)/,
                c = a.replace(/\,|\，|\.|\。|\//g, " ").match(b);
            return c && c.length > 0 ? (d = /ADULT|CHILD|ADT|CHD|Famale|Female|Male|CHN|\s+MR\s*|\s+MS\s*|(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\/+/gi, e = $.trim(c[0].replace(d, "")), e.indexOf("/") < 0 ? e.replace(/,|，|\s+/g, " ") : e.replace(/,|，/g, " ")) : ""
        },
        birth: function (a) {
            var b, c, d, e, f, g, h, i, j, k, l, m;
            if (birthFmt && "" != birthFmt) {
                if ("mdy" == birthFmt && (g = /\s+(0{0,1}[1-9]|1[0-2]|[a-zA-Z]{3,})[\/.-](0{0,1}[1-9]|[1-2][0-9]|3[0-1])[\/.-]((19\d{2}|20[0][0-9]|201[012345])|(1[012345]|[03456789]\d))/, b = a.match(g), b && b.length > 3)) return d = b[2], c = b[1].toUpperCase(), e = {
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
                }, e[c] && (c = e[c]), f = b[3], 2 == f.length && (h = f.substr(0, 1), f = "0" == h || "1" == h ? "20" + f : "19" + f), $.trim(f + "-" + c + "-" + d)
            } else {
                if (i = /\s+(19\d{2}|20[0][0-9]|201[012345])[\/.-]((0{0,1}[1-9])|(1[0-2]))[\/.-](([1-2][0-9])|(3[0-1])|(0{0,1}[1-9]))/, b = a.match(i), b && b.length > 0) return $.trim(b[0].replace(/[.\/]/g, "-"));
                if (j = /\s+(19\d{2}|20[0][0-9]|201[012345])[年]((0{0,1}[1-9])|(1[0-2]))[月](([1-2][0-9])|(3[0-1])|(0{0,1}[1-9]))/, b = a.match(j), b && b.length > 0) return $.trim(b[0].replace(/[年月]/g, "-"));
                if (k = /\s+(19\d{2}|20[0][0-9]|201[012345])(0[1-9]|1[0-2]{1})([1-2][0-9]|3[0-1]|0[1-9])/, b = a.match(k), b && b.length > 0) return $.trim(b[1] + "-" + b[2] + "-" + b[3]);
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
                }, e[c] && (c = e[c]), f = b[3], 2 == f.length && (m = f.substr(0, 1), f = "0" == m || "1" == m ? "20" + f : "19" + f), $.trim(f + "-" + c + "-" + b[1])
            }
            return ""
        },
        gender: function (a) {
            var b = /\s+(男|女|男性|女性|Famale|Female|Male|F|M|MR|MS|MRS|MISS)(?=\s+|$)/i,
                c = a.match(b);
            return c && c.length > 0 ? $.trim(c[0]) : ""
        },
        certCode: function (a) {
            var b, c;
            return a = a.replace(/х|ｘ/g, "X"), b = /(\d{15,18}[xX]?|[a-zA-Z]{1,2}\d{6,11}|[Ll]\d{17}|\d{2}[a-zA-Z]{2}\d{5}|\s+(?!19|20)\d{7,10})(?=[.\/\s]+|$)/, c = a.match(b), c && c.length > 0 ? $.trim(c[0]) : ""
        },
        certTp: function (a) {
            var b = /\s+(护照|P|身份证|I|其他|O)(?=\s+|$)/,
                c = a.match(b);
            return c && c.length > 0 ? $.trim(c[0]) : ""
        }
    }
})(jQuery, document);


(function ($, document) {
    $.pasteCls = {
        //获取出生日期
        getBirthday: function (a) {
            var b = "";
            return 15 == a.length ? (b = a.substr(6, 6), "19" + b.substr(0, 2) + "-" + b.substr(2, 2) + "-" + b.substr(4, 2)) : 18 == a.length ? (b = a.substr(6, 8), b.substr(0, 4) + "-" + b.substr(4, 2) + "-" + b.substr(6, 2)) : ""
        },
        //获取性别
        getGender: function (a) {
            var c, b = 1;
            return (15 == a.length || 18 == a.length) && (b = 15 == a.length ? a.substr(14, 1) : a.substr(16, 1)), c = Number(b) % 2, 1 == c ? "M" : "F"
        },
        //获取粘贴泛型方法
        getCopyValue: function (a, b) {
            var c = "",
                d = $.copyVTypesCls[a];
            return d && (c = d(b)), c
        },
        //
        insuredPaste: function (a, b) {

            var d, e, f, g, h, i, j, k, l, m, n;
            for (d = /上海|江苏|安徽|浙江|北京|天津|大连|成人|儿童|老人|领队|导游|夫妻|生效|[，,;；:：。‘’\042\t]/g, e = $.trim(a.replace(/男性/g, "男").replace(/女性/g, "女").replace(d, " ")).split(/\n/), f = [], g = "", h = "", i = 0; i < e.length; i++) if (j = e[i], k = {}, "" != j && /\S/.test(j))

                try {

                    if (k["certCode"] = this.getCopyValue("certCode", j), l = this.getCopyValue("cnname", j), m = this.getCopyValue("enname", j), k["name"] = "" != l && "" != m ? l + " " + m : l + m, k["certTp"] = this.getCopyValue("certTp", j), !k["certCode"] || k["certTp"] && "" != k["certTp"] || (k["certTp"] = k["certCode"].length >= 14 && k["certCode"].length <= 19 ? k["certCode"].toUpperCase().indexOf("L") < 0 ? "I" : "O" : Validator.TypeValid("passport", k["certCode"]) === !0 ? "P" : "O"), k["birth"] = this.getCopyValue("birth", j), "" == k["certCode"] && (k["certCode"] = k["birth"], k["certTp"] = "O"), k["name"] && k["certCode"]) {

                        if (k["mobile"] = this.getCopyValue("mobile", j), n = this.getCopyValue("gender", j), "" != n) switch (n.toLowerCase()) {
                            case "女":
                            case "女性":
                            case "f":
                            case "famale":
                            case "female":
                            case "ms":
                            case "mrs":
                            case "miss":
                                n = "F";
                                break;
                            default:
                                n = "M"
                        }
                        k["gender"] = n, f.push(k)
                    } else g += i + 1 + ",", h += j + "\n"

                } catch (o) { } else g += i + 1 + ",", h += j + "\n";
            "" != g && (g = g.substr(0, g.length - 1)), b && "function" == typeof b && b(f, e.length, f.length, g, h)
        }
    }
})(jQuery, document);