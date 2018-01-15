
$('#file-0c').fileinput({
    language: 'zh',
    uploadUrl: "/index.php?m=admin&c=image&a=ajaxUploadImage", //上传的
}).on("fileuploaded", function(event, data) {
        if(data.response) {
            var imageUrl = '/' + data.response.res.file_data.savepath + data.response.res.file_data.savename
            $("#hide-image-div").append('<input  type="hidden" value= ' + imageUrl + ' name="image[]">');
        }
    });

//Date range picker with time picker
$('#reservationtime').daterangepicker({
    timePicker: true,
    timePickerIncrement: 1,
    timePicker24Hour : true, //是否使用12小时制来显示时间
    timePickerSeconds: true, //时间选择框是否显示秒数
    locale: {
        format: 'YYYY-MM-DD HH:mm:ss',
        applyLabel: '确认',
        cancelLabel: '取消',
        fromLabel : '起始时间',
        toLabel : '结束时间',
        customRangeLabel : '自定义',
        daysOfWeek : [ '日', '一', '二', '三', '四', '五', '六' ],
        monthNames : [ '一月', '二月', '三月', '四月', '五月', '六月',
            '七月', '八月', '九月', '十月', '十一月', '十二月' ],
        firstDay : 1,
        separator : ' 至 ',
    },
});

//Date range picker with time picker
$('#reservationtime2').daterangepicker({
    timePicker: true,
    timePickerIncrement: 1,
    timePicker24Hour : true, //是否使用12小时制来显示时间
    timePickerSeconds: true, //时间选择框是否显示秒数
    locale: {
        format: 'YYYY-MM-DD HH:mm:ss',
        applyLabel: '确认',
        cancelLabel: '取消',
        fromLabel : '起始时间',
        toLabel : '结束时间',
        customRangeLabel : '自定义',
        daysOfWeek : [ '日', '一', '二', '三', '四', '五', '六' ],
        monthNames : [ '一月', '二月', '三月', '四月', '五月', '六月',
            '七月', '八月', '九月', '十月', '十一月', '十二月' ],
        firstDay : 1,
        separator : ' 至 ',
    },
});

$('#create-match').click(function () {
    var match_title = $('input[name = "match_title"]').val();
    var event_id = $('#event_id').val();
    var sponsor = $('input[name = "sponsor"]').val();
    var undertake = $('input[name = "undertake"]').val();
    var leader = $('input[name = "leader"]').val();
    var leader_tel = $('input[name = "leader_tel"]').val();
    var headcount = $('input[name = "headcount"]').val();
    var register_max = $('input[name = "register_max"]').val();
    var match_time = $('input[name = "match_time"]').val();
    var enrol_time = $('input[name = "enrol_time"]').val();
    var match_detail = $('textarea[name = "match_detail"]').val();
    var address = $('textarea[name = "address"]').val();
    var category = $('input[name = "category"]').val();
    var valArr = new Array();

    $('input[name = "image[]"]').each(function(i){
        valArr[i] = $(this).val();
    });

    if(!match_title) {
        return dialog.msg('请输入赛事名称');
    }
    if(event_id == 0) {
        return dialog.msg('请选择赛事类型');
    }
    if(!sponsor) {
        return dialog.msg('请输入赛事主办方名称');
    }
    if(!undertake) {
        return dialog.msg('请输入赛事承办方名称');
    }
    if(!leader) {
        return dialog.msg('请输入赛事负责人姓名');
    }
    if(!leader_tel) {
        return dialog.msg('请输入赛事负责人手机号码');
    }
    if(!headcount) {
        return dialog.msg('请输入最大参赛人数');
    }
    if(!register_max) {
        return dialog.msg('请输入每人至多可报项数');
    }
    if(!match_time) {
        return dialog.msg('请输入比赛开始截止时间');
    }
    if(!enrol_time) {
        return dialog.msg('请输入报名开始截止时间');
    }
    if(!match_detail) {
        return dialog.msg('请输入详细的赛事规程');
    }
    if(!address) {
        return dialog.msg('请输入比赛地址');
    }
    if(!category) {
        return dialog.msg('请输入比赛项目及报名费用');
    }
    if(valArr.length == 0) {
        return dialog.msg('请输入至少上传一张封面图片');
    }

    var data = {
        'match_title': match_title,
        'event_id': event_id,
        'sponsor': sponsor,
        'leader': leader,
        'headcount': headcount,
        'match_time': match_time,
        'match_detail': match_detail,
        'category': category,
        'undertake': undertake,
        'leader_tel': leader_tel,
        'register_max': register_max,
        'enrol_time': enrol_time,
        'address': address,
        'valArr' : valArr,
    };
    console.log(data);
    var postUrl = 'index.php?m=admin&c=match&a=createCheck';
    var jumpUrl = 'index.php?m=admin&c=match';

    $.post(postUrl,data,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        } else if(result.status == 1) {
            return dialog.msg_url(result.message,jumpUrl);
        }
    },'JSON');
});

$('#eidt-match').click(function () {
    var match_title = $('input[name = "match_title"]').val();
    var event_id = $('#event_id').val();
    var sponsor = $('input[name = "sponsor"]').val();
    var undertake = $('input[name = "undertake"]').val();
    var leader = $('input[name = "leader"]').val();
    var leader_tel = $('input[name = "leader_tel"]').val();
    var headcount = $('input[name = "headcount"]').val();
    var register_max = $('input[name = "register_max"]').val();
    var match_time = $('input[name = "match_time"]').val();
    var enrol_time = $('input[name = "enrol_time"]').val();
    var match_detail = $('textarea[name = "match_detail"]').val();
    var address = $('textarea[name = "address"]').val();
    var category = $('input[name = "category"]').val();
    var match_id = $('input[name = "match_id"]').val();


    if(!match_id) {
        return dialog.msg('赛事ID为空');
    }
    if(!match_title) {
        return dialog.msg('请输入赛事名称');
    }
    if(event_id == 0) {
        return dialog.msg('请选择赛事类型');
    }
    if(!sponsor) {
        return dialog.msg('请输入赛事主办方名称');
    }
    if(!undertake) {
        return dialog.msg('请输入赛事承办方名称');
    }
    if(!leader) {
        return dialog.msg('请输入赛事负责人姓名');
    }
    if(!leader_tel) {
        return dialog.msg('请输入赛事负责人手机号码');
    }
    if(!headcount) {
        return dialog.msg('请输入最大参赛人数');
    }
    if(!register_max) {
        return dialog.msg('请输入每人至多可报项数');
    }
    if(!match_time) {
        return dialog.msg('请输入比赛开始截止时间');
    }
    if(!enrol_time) {
        return dialog.msg('请输入报名开始截止时间');
    }
    if(!match_detail) {
        return dialog.msg('请输入详细的赛事规程');
    }
    if(!address) {
        return dialog.msg('请输入比赛地址');
    }
    if(!category) {
        return dialog.msg('请输入比赛项目及报名费用');
    }

    var data = {
        'match_id' : match_id,
        'match_title': match_title,
        'event_id': event_id,
        'sponsor': sponsor,
        'leader': leader,
        'headcount': headcount,
        'match_time': match_time,
        'match_detail': match_detail,
        'category': category,
        'undertake': undertake,
        'leader_tel': leader_tel,
        'register_max': register_max,
        'enrol_time': enrol_time,
        'address': address,
    };

    var postUrl = 'index.php?m=admin&c=match&a=editCheck';
    var jumpUrl = 'index.php?m=admin&c=match';

    $.post(postUrl,data,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        } else if(result.status == 1) {
            return dialog.msg_url(result.message,jumpUrl);
        }
    },'JSON');
});

$(function () {
    $('.select2').select2({
        language: "zh-CN",
    });
    $('#example2').DataTable({
        language: {
            "sProcessing": "处理中...",
            "sLengthMenu": "显示 _MENU_ 项结果",
            "sZeroRecords": "没有匹配结果",
            "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
            "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
            "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
            "sInfoPostFix": "",
            "sSearch": "搜索:",
            "sUrl": "",
            "sEmptyTable": "表中数据为空",
            "sLoadingRecords": "载入中...",
            "sInfoThousands": ",",
            "oPaginate": {
                "sFirst": "首页",
                "sPrevious": "上页",
                "sNext": "下页",
                "sLast": "末页"
            },
            "oAria": {
                "sSortAscending": ": 以升序排列此列",
                "sSortDescending": ": 以降序排列此列"
            }
        },
        responsive: true,    //自适应
        paging: true,       //分页
        searching:true,    //搜索
        ordering:true,     //排序
        info:true,         //信息
        lengthChange: true,
        autoWidth   : false,
    })
});

/**
 * 点击修改状态
 */
$('body').on('click','.text-center #change-status',function () {
    var id = $(this).attr('attr-id');
    var status = $(this).attr('attr-status');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    if(status == 0) {
        status = 1;
    }else if(status == 1){
        status = 0;
    }

    var postData = {
        'id' : id,
        'status' : status,
    };

    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;
    var jumpUrl = '';

    layer.open({
        type : 0,
        title : '请确定',
        btn : ['是','否'],
        icon : 3,
        closeBtn : 2,
        content : "是否切换状态",
        scrollbar : true,
        yes : function () {
            //执行跳转
            ajaxPost(postUrl,postData,jumpUrl);   //抛送ajax请求
        }
    });
});

/**
 * 删除数据
 */
$('body').on('click','.text-center #delete-info',function () {
    var id = $(this).attr('attr-id');
    var name = $(this).attr('attr-name');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {
        'id' : id,
    };
    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;
    var jumpUrl = '';
    layer.open({
        type : 0,
        title : '请确定',
        btn : ['是','否'],
        icon : 3,
        closeBtn : 2,
        content : "是否删除"+name,
        scrollbar : true,
        yes : function () {
            //执行跳转
            ajaxPost(postUrl,postData,jumpUrl);   //抛送ajax请求
        }
    });
});

/**
 * 编辑数据
 */
$('body').on('click','.text-center #edit-info',function () {
    var id = $(this).attr('attr-id');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');
    var name = $(this).attr('attr-name');
    var jumpUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a + "&" + name + "=" + id;
    window.location.href = jumpUrl;

});

function ajaxPost(postUrl,postData,jumpUrl) {
    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.error(result.message);
        }
        if(result.status == 1) {
            return dialog.success(result.message,jumpUrl);
        }
    },'JSON');
}

$('#export-enrol').click(function () {
    var match_id = $(this).attr('match_id');
    window.location.href = 'index.php?m=admin&c=enrol&a=export&match_id='+match_id;
});