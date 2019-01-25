    var table;
    $(document).ready(function() {
        $('#name,#idcard').keyup(function(e) {
            if (e.currentTarget.value == '') {
                $('#ordernum').attr("disabled", false);
                $('#ordernum').attr("placeholder", "");
            } else {
                $('#ordernum').attr("disabled", true);
                $('#ordernum').attr("placeholder", "清空用户名和证件号码此项可查询。");
            }
        });
        $('#ordernum').keyup(function(e) {
            if (e.currentTarget.value == '') {
                $('#name,#idcard').attr("disabled", false);
                $('#name,#idcard').attr("placeholder", "");
            } else {
                $('#name,#idcard').attr("disabled", true);
                $('#name,#idcard').attr("placeholder", "清空订单号码此项可查询。");
            }
        });
        $('.modalbutton').click(function(e) {
            fnModalClosed($('#msg').text());
        });
        $('#startdate,#enddate').datepicker({
            autoclose: true,
            todayHighlight: true,
            language: "zh-CN",
            format: "yyyy-mm-dd",
        });
        var date = fnSetDate('today');
        $('#startdate').val(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
        var date = fnSetDate('enddate');
        $('#enddate').val(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
        $('#startdate,#enddate').change(function(event) {
            /* Act on the event */
            var d1 = new Date($('#startdate').val().replace(/\-/g, "\/"));
            var d2 = new Date($('#enddate').val().replace(/\-/g, "\/"));
            if (d2 - d1 > 60 * 86400000) {
                $("#msg").text('旅游开始时间和结束时间最大不得超过60天。');
                $("#modal-info").modal("show");
            } else if (d1 >= d2) {
                $("#msg").text('开始日期不得大于结束日期。');
                $("#modal-info").modal("show");
            }
        });
        $('.check').click(function(e) {
            if (table == undefined) {
                fnAjaxCheck();
            } else {
                fnUpdataDT();
            }
        });
        $('.export').click(function(e) {
            if (table == undefined) {
                $("#msg").text('请查询所需数据再导出。');
                $("#modal-info").modal("show");
                return;
            }
            fnExport()
        });
    });

    function fnModalClosed(str) {
        switch (str) {
            case '旅游开始时间和结束时间最大不得超过60天。':
                var date = fnSetDate('startdate');
                $('#startdate').val(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
                var date = fnSetDate('today');
                $('#enddate').val(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
                break;
            case '开始日期不得大于结束日期。':
                var date = fnSetDate('startdate');
                $('#startdate').val(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
                var date = fnSetDate('today');
                $('#enddate').val(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
                break;
        }
    }

    function fnSetDate(str) {
        var today = new Date();
        switch (str) {
            case 'enddate':
                var enddate = new Date(today.getTime() + (50 * 86400000));
                return enddate;
                break;
            case 'today':
                today = new Date(today.getFullYear() + "/" + (today.getMonth() + 1) + "/" + today.getDate());
                return today
                break;
        }
    }

    function fnExport() {
        var id = new Array();
        var username = new Array();
        var teamnum = new Array();
        var total = new Array();
        for (var i = table.context[0].aoData.length - 1; i >= 0; i--) {
            if (i != 0) {
                id = id + JSON.stringify(table.context[0].aoData[i]._aData.id).substr(1, JSON.stringify(table.context[0].aoData[i]._aData.id).length - 2) + ',';
                username = username + JSON.stringify(table.context[0].aoData[i]._aData.username).substr(1, JSON.stringify(table.context[0].aoData[i]._aData.username).length - 2) + ',';
                teamnum = teamnum + JSON.stringify(table.context[0].aoData[i]._aData.teamnum).substr(1, JSON.stringify(table.context[0].aoData[i]._aData.teamnum).length - 2) + ',';
                total = total + JSON.stringify(table.context[0].aoData[i]._aData.total).substr(1, JSON.stringify(table.context[0].aoData[i]._aData.total).length - 2) + ',';
            } else {
                id = id + JSON.stringify(table.context[0].aoData[i]._aData.id).substr(1, JSON.stringify(table.context[0].aoData[i]._aData.id).length - 2);
                username = username + JSON.stringify(table.context[0].aoData[i]._aData.username).substr(1, JSON.stringify(table.context[0].aoData[i]._aData.username).length - 2);
                teamnum = teamnum + JSON.stringify(table.context[0].aoData[i]._aData.teamnum).substr(1, JSON.stringify(table.context[0].aoData[i]._aData.teamnum).length - 2);
                total = total + JSON.stringify(table.context[0].aoData[i]._aData.total).substr(1, JSON.stringify(table.context[0].aoData[i]._aData.total).length - 2);

            }
        }
        id = [id];
        username = [username];
        teamnum = [teamnum];
        total = [total];
        var data = { "id": id, "username": username, "teamnum": teamnum, "total": total };
        $.ajax({
            type: 'POST',
            url: ExportURL,
            data: data,
            success: function(result) {
                $("#msg").html('<a href=' + result['fileaddress'] + ' style="color:#fff;">点击下载文件</a>');
                $("#modal-info").modal("show");
            },
            dataType: 'json'
        });
    }

    function fnInstallDT(data) {
        table = $('#orderlist').DataTable({
            "ordering": true,
            "order": [0, 'desc'],
            "data": data,
            "columns": [
                { data: 'id' },
                { data: 'username' },
                { data: 'teamnum' },
                { data: 'total' }
            ],
            "pagingType": "full",
            "language": {
                "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                "sZeroRecords": "没有匹配结果",
                "emptyTable": "当前无记录",
                "infoEmpty": "当前无记录",
                "info": " 当前第 _PAGE_ / _PAGES_ 页",
                "search": "在表格中搜索：_INPUT_",
                "lengthMenu": '每页显示 <select>' +
                    '<option value="10">10</option>' +
                    '<option value="20">20</option>' +
                    '<option value="30">30</option>' +
                    '<option value="40">40</option>' +
                    '<option value="50">50</option>' +
                    '<option value="100">100</option>' +
                    '<option value="-1">All</option>' +
                    '</select> 条记录',
                "paginate": {
                    "previous": "上一页",
                    "next": "下一页",
                    "first": "第一页",
                    "last": "最后一页"
                }
            },
            "columnDefs": [{
                "targets": 0,
                "data": "id",
                "render": function(data, type, full, meta) {
                    return "<a href=" + OrderDetailURL + "?no=" + data + '>' + data + '</a>';
                }
            }]
        });
    }