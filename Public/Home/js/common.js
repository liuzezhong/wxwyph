/**
 * Created by Administrator on 2017/7/4.
 */

$('.sandbox-container').datepicker({
    language: "zh-CN",
    orientation: "auto",
    todayHighlight: true,
    format:"yyyy-mm-dd"
});

$('.sandbox-container-spacile').datepicker({
    language: "zh-CN",
    orientation: "bottom auto",
    todayHighlight: true,
    format:"yyyy-mm",
    startView: 1,
    maxViewMode: 3,
    minViewMode:1,
});

$('.selectpicker').selectpicker({

});

$('#add-info-modal').on('click',function () {
    var myDate = new Date();
    //获取当前年
    var year=myDate.getFullYear();
    //获取当前月
    var month=myDate.getMonth()+1;
    //获取当前日
    var date=myDate.getDate();
    var h=myDate.getHours();       //获取当前小时数(0-23)
    var m=myDate.getMinutes();     //获取当前分钟数(0-59)
    var s=myDate.getSeconds();

    if (m<10) {
        m = '0'+m;
    }
    if(month < 10) {
        month = '0'+month;
    }
    if(date < 10) {
        date = '0'+date;
    }

    var now=year+'-'+month+"-"+date+" "+h+':'+m;
    $('#gmt_repay').val(now);
});

$('#add-customer').click(function () {
    var name = $('input[name = "name"]').val();
    var phone = $('input[name = "phone"]').val();
    var idcard = $('input[name = "idcard"]').val();
    var address = $('textarea[name = "address"]').val();
    var recommender = $('#recommender').val();
    var company_id = $('#company_id').val();
    var remark = $('textarea[name = "remark"]').val();

    //正则表达式验证手机号码
    var zmobile=/^(1[34578]\d{9})$/;  //手机号码验证
    var zidcard=/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/;

    if(!name) {
        return dialog.msg('请输入客户姓名！');
    }
    if(!phone) {
        return dialog.msg('请输入客户手机号码！');
    }
    if(!zmobile.test(phone)) {
        return dialog.msg('手机号码格式有误！');
    }
    if(!idcard) {
        return dialog.msg('请输入客户身份证号码！');
    }
    if(!zidcard.test(idcard)) {
        return dialog.msg('身份证号码格式有误！');
    }
    if(!address) {
        return dialog.msg('请输入客户家庭住址！');
    }
    if(recommender == 0) {
        return dialog.msg('请选择首贷经理！');
    }

    var data = {
        'name' : name,
        'phone': phone,
        'idcard' : idcard,
        'address' : address,
        'recommender' : recommender,
        'company_id' : company_id,
        'remark' : remark,
    };

    var postUrl = 'index.php?m=home&c=customer&a=addCheck';
    var jumpUrl = 'index.php?m=home&c=customer&a=index';

    //进行Ajax异步请求
    $.post(postUrl,data,function (result) {
        if(result.status == 0) {
            //return dialog.error(result.message);
            return dialog.msg(result.message);
        } else if(result.status == 1) {
            return dialog.msg_url(result.message,jumpUrl);
        }
    },'JSON');
});

$('body').on('click','.text-center #change-status',function () {
    var id = $(this).attr('attr-id');
    var status = $(this).attr('attr-status');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    if(status == 0) {
        status = -1;
    }else if(status == -1){
        status = 0;
    }

    var postData = {
        'id' : id,
        'status' : status,
    };

    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;
    var jumpUrl = window.location.href;

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

$('body').on('click','.text-center #customer-edit',function () {
    var id = $(this).attr('customer-id');
    var postData = {
        'id' : id,
    };
    var postUrl = "index.php?m=home&c=customer&a=editCustomer";

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.error(result.message);
        }
        if(result.status == 1) {
            $('.modal-body .form-group #name').attr('value',result.data.name);
            $('.modal-body .form-group #phone').attr('value',result.data.phone);
            $('.modal-body .form-group #idcard').attr('value',result.data.idcard);
            $('.modal-body .form-group #address').val(result.data.address);
            $('.modal-body .form-group #remark').val(result.data.remark);
            $('.modal-body .form-group #recommender').val(result.data.recommender);
            $('#customer-id').val(id);
        }
    },'JSON');
});

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
    var jumpUrl = window.location.href;
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

$('#save-customer-change').click(function () {
    var name = $('input[name = "name"]').val();
    var phone = $('input[name = "phone"]').val();
    var idcard = $('input[name = "idcard"]').val();
    var address = $('textarea[name = "address"]').val();
    var recommender = $('#recommender').val();
    var id = $('#customer-id').val();
    var remark = $('textarea[name = "remark"]').val();

    //正则表达式验证手机号码
    var zmobile=/^(1[34578]\d{9})$/;  //手机号码验证
    var zidcard=/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/;
    if(!id) {
        return dialog.msg('数据不存在，请重试！');
    }
    if(!name) {
        return dialog.msg('请输入客户姓名！');
    }
    if(!phone) {
        return dialog.msg('请输入客户手机号码！');
    }
    if(!zmobile.test(phone)) {
        return dialog.msg('手机号码格式有误！');
    }
    if(!idcard) {
        return dialog.msg('请输入客户身份证号码！');
    }
    if(!zidcard.test(idcard)) {
        return dialog.msg('身份证号码格式有误！');
    }
    if(!address) {
        return dialog.msg('请输入客户家庭住址！');
    }
    if(recommender == 0) {
        return dialog.msg('请选择首贷经理！');
    }

    var data = {
        'id' : id,
        'name' : name,
        'phone': phone,
        'idcard' : idcard,
        'address' : address,
        'recommender' : recommender,
        'remark' : remark,
    };

    var postUrl = 'index.php?m=home&c=customer&a=checkEditCustomer';
    var jumpUrl = window.location.href;

    //进行Ajax异步请求
    $.post(postUrl,data,function (result) {
        if(result.status == 0) {
            //return dialog.error(result.message);
            return dialog.msg(result.message);
        } else if(result.status == 1) {
            return dialog.msg_url(result.message,jumpUrl);
        }
    },'JSON');
});

$('#add-record').click(function () {
    var data = $("#baoju-form").serializeArray();   //获取form表单数据
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {};
    $(data).each(function () {
        postData[this.name] = this.value;
    });

    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;
    //var jumpUrl = "index.php?m="+ m + "&c=" + c + "&a=index";
    var jumpUrl = window.location.href;

    ajaxPost_msg(postUrl,postData,jumpUrl);

});

$('#add-record-loan').click(function () {
    var data = $("#baoju-form").serializeArray();   //获取form表单数据
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {};
    $(data).each(function () {
        postData[this.name] = this.value;
    });

    var valArr = new Array();

    $('input[name = "image[]"]').each(function(i){
        valArr[i] = $(this).val();
    });

    postData['valArr'] = valArr;

    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;
    //var jumpUrl = "index.php?m="+ m + "&c=" + c + "&a=index";
    var jumpUrl = window.location.href;

    ajaxPost_msg(postUrl,postData,jumpUrl);
});

$('#edit-record').click(function () {
    var data = $("#baoju-edit-form").serializeArray();   //获取form表单数据
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {};
    $(data).each(function () {
        postData[this.name] = this.value;
    });

    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;
    //var jumpUrl = "index.php?m="+ m + "&c=" + c + "&a=index";
    var jumpUrl = window.location.href;

    ajaxPost_msg(postUrl,postData,jumpUrl);

});

$('body').on('click','.text-center #edit-repayments',function () {
    $('#baoju-form')[0].reset();

    var id = $(this).attr('attr-id');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {
        'id' : id,
    };
    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.error(result.message);
        }
        if(result.status == 1) {
            console.log(result);
            $('#edit-customer-name').val(result.customer.name);
            $('#edit-customer-phone').val(result.customer.phone);
            $('#edit-customer-idcard').val(result.customer.idcard);
            $('#edit-loan-info').val(result.loan.principal + ',' + result.loan.create_time);
            $('#edit-loan-principal').val(result.loan.principal);
            $('#edit-cyclical').val(result.loan.cyclical);

            $('#edit-s_money').val(result.repayments.s_money);
            $('#edit-r_money').val(result.repayments.r_money);
            $('#edit-b_money').val(result.repayments.b_money);
            $('#edit-staff_id').selectpicker('val',result.repayments.staff_id);
            $('#edit-pay_style').selectpicker('val',result.repayments.pay_style);
            $('#edit-gmt_repay').val(result.repayments.gmt_repay);
            $('#edit-remark').val(result.repayments.remark);
            $('#edit-repayments_id').val(result.repayments.repayments_id);

            $('#edit-cycles').html('');
            var i = 1;
            for(i in result.repayCycles) {
                if(result.repayCycles[i].disabled == 0) {
                    if(result.repayCycles[i].selected == 0) {
                        $('#edit-cycles').append(
                            ' <option value="' + result.repayCycles[i].value + '">第' + result.repayCycles[i].value + '期</option>'
                        );
                    }else if(result.repayCycles[i].selected == 1) {
                        $('#edit-cycles').append(
                            ' <option value="' + result.repayCycles[i].value + '" selected>第' + result.repayCycles[i].value + '期</option>'
                        );
                    }

                }else if(result.repayCycles[i].disabled == 1) {
                    if(result.repayCycles[i].selected == 0) {
                        $('#edit-cycles').append(
                            ' <option value="' + result.repayCycles[i].value + '" disabled>第' + result.repayCycles[i].value + '期</option>'
                        );
                    }else if(result.repayCycles[i].selected == 1) {
                        $('#edit-cycles').append(
                            ' <option value="' + result.repayCycles[i].value + '" disabled selected>第' + result.repayCycles[i].value + '期</option>'
                        );
                    }
                }
            }
            $('#edit-cycles').selectpicker('refresh');

        }
    },'JSON');
});


$('body').on('click','.text-center #edit-charge',function () {
    $('#baoju-form')[0].reset();

    var id = $(this).attr('attr-id');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {
        'id' : id,
    };
    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.error(result.message);
        }
        if(result.status == 1) {
            console.log(result);
            $('#edit-money').val(result.charge.money);
            $('#edit-matter').val(result.charge.matter);
            $('#edit-gmt_create').val(result.charge.gmt_create);
            $('#edit-remark').val(result.charge.remark);
            $('#edit-charge_id').val(result.charge.charge_id);


        }
    },'JSON');
});

$('body').on('click','.text-center #edit-Admin',function () {
    $('#baoju-form')[0].reset();

    var id = $(this).attr('attr-id');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {
        'id' : id,
    };
    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.error(result.message);
        }
        if(result.status == 1) {
            console.log(result);
            $('#edit-username').val(result.user.username);
            $('#edit-phone').val(result.user.phone);
            $('#edit-email').val(result.user.email);
            $('#edit-department').val(result.user.department);
            $('#edit-position').val(result.user.position);
            $('#edit-user_id').val(result.user.user_id);
            $('#edit-company_id').selectpicker('val',result.user.company_id);
            $('#edit-jurisdiction').selectpicker('val',result.user.jurisdiction);

        }
    },'JSON');
});

$('body').on('click','.text-center #edit-Wxuser',function () {
    $('#baoju-edit-form')[0].reset();

    var id = $(this).attr('attr-id');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {
        'id' : id,
    };
    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;
    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.error(result.message);
        }
        if(result.status == 1) {
            console.log(result);
            $('#edit-real_name').val(result.user.real_name);
            $('#edit-status').selectpicker('val',result.user.status);
            $('#edit-depart_id').selectpicker('val',result.user.depart_id);
            $('#edit-company_id').selectpicker('val',result.user.company_id);
            $('#edit-user_id').val(result.user.user_id);

        }
    },'JSON');
});

$('body').on('click','.text-center #edit-wageinfo',function () {
    $('#baoju-form')[0].reset();

    var id = $(this).attr('attr-id');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {
        'id' : id,
    };
    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.error(result.message);
        }
        if(result.status == 1) {
            console.log(result);
            $('#edit-gmt_wage').val(result.wage.gmt_wage);
            $('#edit-number').val(result.wage.number);
            $('#edit-wage').val(result.wage.wage);
            $('#edit-insur').val(result.wage.insur);
            $('#edit-remark').val(result.wage.remark);
            $('#edit-wage_id').val(result.wage.wage_id);


        }
    },'JSON');
});

$('body').on('click','.text-center #edit-tour',function () {
    $('#baoju-form')[0].reset();

    var id = $(this).attr('attr-id');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');

    var postData = {
        'id' : id,
    };
    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.error(result.message);
        }
        if(result.status == 1) {
            console.log(result);
            $('#edit-customer_id').selectpicker('val',result.tour.customer_id);
            $('#edit-location_id').selectpicker('val',result.tour.location_id);
            $('#edit-staff_id').selectpicker('val',result.tour.staff_id);
            $('#edit-money').val(result.tour.money);
            $('#edit-style_id').selectpicker('val',result.tour.paystyle_id);
            $('#edit-gmt_tour').val(result.tour.gmt_tour);
            $('#edit-remark').val(result.tour.remark);
            $('#edit-tour_id').val(result.tour.tour_id);

            if(result.tour.is_loan == 0) {
                $('#edit-no_loan').attr("checked",true);
            }else {
                $('#edit-is_loan').attr("checked",true);
            }

        }
    },'JSON');
});


$('body').on('click','.text-center #loan-sendmessage',function (){
    var id = $(this).attr('attr-id');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');
    var style = $(this).attr('attr-style');
    var nowdate = $(this).attr('attr-nowdate');
    var key = 0;

    var that = this;
    var postData = {
        'id' : id,
        'style' : style,
        'nowdate' : nowdate,
    };
    console.log(postData);
    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;
    $.post(postUrl,postData,function (result) {
        if (result.status == 0) {
            return dialog.msg(result.message);
        }else if (result.status == 1) {
            layer.prompt({
                formType: 2,
                value: result.phone_message,
                title: '发送以下信息至' + result.customer_phone,
                area: ['320px', '150px'] //自定义文本域宽高
            }, function(value, index, elem){
                var post2Url = "index.php?m=home&c=message&a=checkLoanMessage";
                var jumpUrl = window.location.href;
                var post2Data = {
                    'loan_message' : value,
                    'id' : id,
                    'nowdate' : nowdate,
                };
                $.post(post2Url,post2Data,function (result) {
                    if (result.status == 0) {
                        return dialog.msg(result.message);
                    }else if (result.status == 1) {
                        $(that).attr('class','btn btn-success btn-xs');
                        $(that).text('再发一次');
                        return dialog.msg_fast(result.message);
                    }
                });
                layer.close(index);
            });
        }
    });
});

$('body').on('click','.text-center #edit-info',function () {
    $('#baoju-form')[0].reset();
    $('#poundage-append').html('');
    var id = $(this).attr('attr-id');
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');
    var key = 0;
    var postData = {
        'id' : id,
    };
    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.error(result.message);
        }
        if(result.status == 1) {
            if(result.data.is_image == 0) {
                // 已有
                $('#upload-image').show();
            }else {
                $('#upload-image').hide();
            }
            $('#staff_id').val(id);
            $('#cycle-append').html('');
            /*$('#cycle-append').append(
                '<div class="form-group col-lg-6"> ' +
                '<label>具体时间</label>' +
                '<input type="text" class="form-control" id="juti_data" name="juti_data" placeholder="请输入具体日期" >' +
                '</div>');*/
            for(key in result.data) {
                if(key == 'customer_id' || key == 'staff_id' || key == 'foreign_id' || key == 'product_id') {
                    $('#'+key).selectpicker('val', result.data[key]);
                }else if(key == 'poundage_arrays'){
                    var poundageID = new Array();

                    console.log(result.data[key]);
                    for(var i in result.data[key]) {
                        poundageID[i] = result.data[key][i]['poundage_id'];
                    }
                    $('#poundage').selectpicker('val',poundageID);

                    for(var i in result.data[key]) {
                        $('#poundage-append').append(
                            '<div class="form-group col-lg-6 poundage-append-div"> ' +
                            '<label>'+ result.data[key][i]['poundage_name'] +'</label>' +
                            '<input type="text" class="form-control" name="'+ result.data[key][i]['poundage_id'] + '" placeholder="请输入'+ result.data[key][i]['poundage_name'] +'" value="'+ result.data[key][i]['money'] +'">' +
                            '</div>');
                    }
                }else if(key == 'cycle_id') {
                    $('#'+key).selectpicker('val', result.data[key]);

                    if(result.data[key] == '2') {

                        if(result.data['juti_data'] == 1) {
                            var option = '  <option value="1" selected>周一</option>' +
                                '  <option value="2">周二</option>' +
                                '  <option value="3">周三</option>' +
                                '  <option value="4">周四</option>' +
                                '  <option value="5">周五</option>' +
                                '  <option value="6">周六</option>' +
                                '  <option value="7">周日</option>' ;
                        }else if(result.data['juti_data'] == 2) {
                            var option = '  <option value="1">周一</option>' +
                                '  <option value="2"  selected>周二</option>' +
                                '  <option value="3">周三</option>' +
                                '  <option value="4">周四</option>' +
                                '  <option value="5">周五</option>' +
                                '  <option value="6">周六</option>' +
                                '  <option value="7">周日</option>' ;
                        }else if(result.data['juti_data'] == 3) {
                            var option = '  <option value="1">周一</option>' +
                                '  <option value="2">周二</option>' +
                                '  <option value="3"  selected>周三</option>' +
                                '  <option value="4">周四</option>' +
                                '  <option value="5">周五</option>' +
                                '  <option value="6">周六</option>' +
                                '  <option value="7">周日</option>' ;
                        }else if(result.data['juti_data'] == 4) {
                            var option = '  <option value="1">周一</option>' +
                                '  <option value="2">周二</option>' +
                                '  <option value="3">周三</option>' +
                                '  <option value="4"  selected>周四</option>' +
                                '  <option value="5">周五</option>' +
                                '  <option value="6">周六</option>' +
                                '  <option value="7">周日</option>' ;
                        }else if(result.data['juti_data'] == 5) {
                            var option = '  <option value="1" >周一</option>' +
                                '  <option value="2">周二</option>' +
                                '  <option value="3">周三</option>' +
                                '  <option value="4">周四</option>' +
                                '  <option value="5" selected>周五</option>' +
                                '  <option value="6">周六</option>' +
                                '  <option value="7">周日</option>' ;
                        }else if(result.data['juti_data'] == 6) {
                            var option = '  <option value="1">周一</option>' +
                                '  <option value="2">周二</option>' +
                                '  <option value="3">周三</option>' +
                                '  <option value="4">周四</option>' +
                                '  <option value="5">周五</option>' +
                                '  <option value="6"  selected>周六</option>' +
                                '  <option value="7">周日</option>' ;
                        }else if(result.data['juti_data'] == 7) {
                            var option = '  <option value="1">周一</option>' +
                                '  <option value="2">周二</option>' +
                                '  <option value="3">周三</option>' +
                                '  <option value="4">周四</option>' +
                                '  <option value="5">周五</option>' +
                                '  <option value="6">周六</option>' +
                                '  <option value="7"  selected>周日</option>' ;
                        }
                        $('#cycle-append').append(
                            '<div class="form-group col-lg-6"> ' +
                            '<label>具体时间</label>' +
                            '<select class="form-control" id="juti_data" name="juti_data">' +
                            option
                            +
                            '</select>' +
                            '</div>');
                    }
                    if(result.data[key] == '5') {
                        $('#cycle-append').append(
                            '<div class="form-group col-lg-6"> ' +
                            '<label>具体时间</label>' +
                            '<input type="text" class="form-control" id="juti_data" name="juti_data" placeholder="请输入具体日期" value="'+ result.data['juti_data'] +'">' +
                            '</div>');
                    }
                }else {
                    $('.modal-body .form-group #'+key).val(result.data[key]);
                }

            }
        }
    },'JSON');
});

$('body').on('click','.text-center #change-loan-status',function () {
    var loan_id = $(this).attr('attr-id');
    var loan_status = $(this).attr('attr-status');
    var pass = '';
    layer.msg('将交易状态修改为', {
        time: 10000, //20s后自动关闭
        btn: ['已逾期','还款中','已结清','取消'],
        area: '380px',
        yes: function (index, layero) {
            layer.prompt({title: "请输入逾期日期，格式：2018-01-01", formType: 3}, function(pass, index){
                layer.close(index);
                changeLoanStatus(pass,loan_id,loan_status,-1,0); //已逾期
            });


        },
        btn2: function (index,layero) {
            changeLoanStatus(pass,loan_id,loan_status,0,0); //还款中
        },
        btn3: function (index,layero) {
            layer.prompt({title: "请输入结清日期，格式：2018-01-01", formType: 3}, function(pass, index){
                layer.close(index);
                layer.confirm('是否退还保证金', {
                    btn: ['是','否'] //按钮
                }, function(){
                    changeLoanStatus(pass,loan_id,loan_status,1,1);
                }, function(){
                    changeLoanStatus(pass,loan_id,loan_status,1,0);

                });
            });

            // changeLoanStatus(loan_id,loan_status,1); //已结清
        }
    });

});

$('body').on('click','.text-center #change-repay-status',function () {
    var loan_id = $(this).attr('attr-id');
    var now_cyclical = $(this).attr('attr-cyclical');
    var re_status = $(this).attr('attr-status');
    var b_money = 0;
    var that = this;
    layer.msg('将交易状态修改为', {
        time: 10000, //20s后自动关闭
        area: '400px',
        btn: ['正常还款','超时还款','逾期未还','取消'],
        yes: function (index, layero) {
            //addRepaymentsAuto(this,loan_id,now_cyclical,re_status,1,b_money); //已逾期
            var new_re_status = 1;
            if(new_re_status == re_status) {
                return dialog.msg('未做修改！');
            }
            var postUrl = 'index.php?m=home&c=repayments&a=addRepaymentsAuto';
            var postData = {
                'loan_id' : loan_id,
                'now_cyclical' : now_cyclical,
                'new_re_status' : new_re_status,
                'b_money' : b_money,
            };

            $.post(postUrl,postData,function (result) {
                if(result.status == 0) {
                    return dialog.msg(result.message);
                }
                if(result.status == 1) {
                    $(that).attr('class','label label-success');
                    $(that).text('已还款');
                    return dialog.msg_fast(result.message);

                    /*return dialog.msg_url(result.message,jumpUrl);*/
                }
            },'JSON');
        },
        btn2: function (index,layero) {
            layer.prompt({title: '请输入违约金额', formType: 3}, function(pass, index){
                layer.close(index);
                b_money = pass;
                //addRepaymentsAuto(loan_id,now_cyclical,re_status,2,b_money); //未还款
                var new_re_status = 2;
                if(new_re_status == re_status) {
                    return dialog.msg('未做修改！');
                }
                var postUrl = 'index.php?m=home&c=repayments&a=addRepaymentsAuto';
                var postData = {
                    'loan_id' : loan_id,
                    'now_cyclical' : now_cyclical,
                    'new_re_status' : new_re_status,
                    'b_money' : b_money,
                };

                $.post(postUrl,postData,function (result) {
                    if(result.status == 0) {
                        return dialog.msg(result.message);
                    }
                    if(result.status == 1) {
                        $(that).attr('class','label label-success');
                        $(that).text('已还款');
                        return dialog.msg_fast(result.message);
                        /*return dialog.msg_url(result.message,jumpUrl);*/
                    }
                },'JSON');
            });

        },
        btn3: function (index,layero) {
            //addRepaymentsAuto(loan_id,now_cyclical,re_status,-1,b_money); //已还款
            var new_re_status = -1;
            if(new_re_status == re_status) {
                return dialog.msg('未做修改！');
            }
            var postUrl = 'index.php?m=home&c=repayments&a=addRepaymentsAuto';
            var postData = {
                'loan_id' : loan_id,
                'now_cyclical' : now_cyclical,
                'new_re_status' : new_re_status,
                'b_money' : b_money,
            };

            $.post(postUrl,postData,function (result) {
                if(result.status == 0) {
                    return dialog.msg(result.message);
                }
                if(result.status == 1) {
                    $(that).attr('class','label label-danger');
                    $(that).text('已逾期');

                    return dialog.msg_fast(result.message);
                    /*return dialog.msg_url(result.message,jumpUrl);*/
                }
            },'JSON');
        }
    });

});

function addRepaymentsAuto(that,loan_id,now_cyclical,re_status,new_re_status,b_money) {
    if(new_re_status == re_status) {
        return dialog.msg('未做修改！');
    }
    var postUrl = 'index.php?m=home&c=repayments&a=addRepaymentsAuto';
    var postData = {
        'loan_id' : loan_id,
        'now_cyclical' : now_cyclical,
        'new_re_status' : new_re_status,
        'b_money' : b_money,
    };

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        }
        if(result.status == 1) {
            $(that).attr('class','label-success');
            /*return dialog.msg_url(result.message,jumpUrl);*/
        }
    },'JSON');
}

$('.form-group .automatic').change(function () {

    var principal = 0;  //本金
    var cyc_interest = 0;
    var cyclical = 0;
    var product_id = 0;
    var poundage = 0; //手续费
    var bond = 0; //保证金
    var rebate = 0; //同行返点
    product_id = $('#product_id').val();
    principal = $('#principal').val();  //本金
    cyc_interest = $('#cyc_interest').val();  //每期利息
    cyclical = $('#cyclical').val();  //还款周期
    bond = $('#bond').val();  //保证金
    rebate = $('#rebate').val();  //同行返点

    if(principal != 0 && cyc_interest != 0 && cyclical != 0) {
        if(product_id == 0) {
            return dialog.msg('请选择产品类型！');
        }
        var postData = {
            'product_id' : product_id,
        };
        var postUrl = "index.php?m=home&c=product&a=getProductByID";
        $.post(postUrl,postData,function (result) {
            if(result.status == 0) {
                return dialog.msg('该产品信息不存在！');
            }
            if(result.status == 1) {
                if(result.product['methods'] == 0) {
                    //每期应还= 本金/周期+每期利息
                    var cyc_principal = parseInt(principal / cyclical) + parseInt(cyc_interest);
                    $('#cyc_principal').val(cyc_principal);

                }else if(result.product['methods'] == 1) {
                    //先利息，后本金
                    $('#cyc_principal').val(cyc_interest);
                }
            }
        },'JSON');

        //预计利息 = 周期 * 每期利息
        var interest = parseInt(cyclical * cyc_interest);
        $('#interest').val(interest);
    }


    $('#poundage-append input[type="text"]').each(function () {
        poundage = poundage + parseInt($(this).val());
    });

    if(principal != 0 && poundage != 0) {
        var arrival = parseInt(principal) - parseInt(poundage) - parseInt(bond);  //实际到账
        $('#arrival').val(arrival);
    }

    if(principal != 0 && poundage != 0 ) {
        var expenditure = parseInt(principal) - parseInt(poundage) - parseInt(bond) + parseInt(rebate);  //实际支出
        $('#expenditure').val(expenditure);
    }
});

$('#poundage').change(function () {
    var poundageSelect = $(this).val();
    var poundageText = new Array();
    var optionSelected = $(this).find('option:selected');

    for(var i=0;i<optionSelected.length;i++) {
        poundageText[i] = optionSelected[i].text;
    }

    $('.poundage-append-div').remove();
    for (var i=0;i< poundageSelect.length;i++) {
        $('#poundage-append').append(
            '<div class="form-group col-lg-6 poundage-append-div"> ' +
            '<label>'+ poundageText[i] +'</label>' +
            '<input type="text" class="form-control" name="'+ poundageSelect[i] + '" placeholder="请输入'+ poundageText[i] +'">' +
            '</div>');
    }
});
function changeLoanStatus(pass,loan_id,loan_status,new_loan_status,bond) {
    // if(new_loan_status == loan_status) {
    //     return dialog.msg('未修改状态！');
    // }
    var postUrl = 'index.php?m=home&c=loan&a=changeStatus';
    var postData = {
        'loan_id' : loan_id,
        'loan_status' : new_loan_status,
        'bond' : bond,
        'pass' : pass,
    };
    ajaxPost_msg(postUrl,postData,'');
}


function ajaxPost_msg(postUrl,postData,jumpUrl) {
    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        }
        if(result.status == 1) {
            return dialog.msg_url(result.message,jumpUrl);
        }
    },'JSON');
}

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

function addLoanSelectCustomer() {

    var customer_id = $('#customer_id').val();
    var postData = {
        id : customer_id,
    };
    var postUrl = "index.php?m=home&c=customer&a=getCustomerByID";

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        }
        if(result.status == 1) {
            $('#phone').val(result.customer.phone);
            $('#phone').attr('readonly','readonly');
            $('#idcard').val(result.customer.idcard);
            $('#idcard').attr('readonly','readonly');
            $('#company_name').val(result.customer.company.smallname);
            $('#company_id').val(result.customer.company.company_id);
            $('#kefu_phone').val(result.customer.company.kefu_phone);
        }
    },'JSON');
}

function addRepaymentsSelectCustomer() {
    var customer_id = $('#customer_id').val();
    console.log(customer_id);
    var postData = {
        id : customer_id,
    };
    var postUrl = "index.php?m=home&c=customer&a=getCustomerAndRepaymentsByID";

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        }
        if(result.status == 1) {
            $('#phone').val(result.customer.phone);
            $('#phone').attr('readonly','readonly');
            $('#idcard').val(result.customer.idcard);
            $('#idcard').attr('readonly','readonly');
            $('#staff_id').selectpicker('val',result.staff.staff_id);
            $('#staff_id').selectpicker('refresh');
            $('#loan_id').html('');
            var loans = result.loan;
            var loan = 0;
            for(loan in result.loan) {
                if(loan == 0) {
                    $('#loan_id').append(' <option value="' + loans[loan].loan_id + '" selected>' + loans[loan].principal + '元，' + loans[loan].create_time + '</option>');

                }else {
                    $('#loan_id').append(' <option value="' + loans[loan].loan_id + '">' + loans[loan].principal + '元，' + loans[loan].create_time + '</option>');

                }
            }
            $('#loan_id').selectpicker('refresh');

            var loan_id = loans[0].loan_id;
            var postData = {
                'loan_id' : loan_id,
            };
            var postUrl = "index.php?m=home&c=loan&a=getLoanByID";


            $.post(postUrl,postData,function (result) {
                if(result.status == 0) {
                    return dialog.msg(result.message);
                }
                if(result.status == 1) {
                    $('#principal').val(result.loan.principal);
                    $('#cyclical').val(result.loan.cyclical);
                    $('#s_money').val(result.loan.cyc_principal);
                    $('#r_money').val(result.loan.cyc_principal);
                    $('#b_money').val('0.00');
                    $('#principal').attr('readonly','readonly');
                    $('#cyclical').attr('readonly','readonly');
                    $('#s_money').attr('readonly','readonly');
                    $('#cycles').html('');

                    var i = 1;
                    for(i in result.repayCycles) {
                        if(result.repayCycles[i].disabled == 0) {
                            if(result.repayCycles[i].selected == 0) {
                                $('#cycles').append(
                                    ' <option value="' + result.repayCycles[i].value + '">第' + result.repayCycles[i].value + '期</option>'
                                );
                            }else if(result.repayCycles[i].selected == 1) {
                                $('#cycles').append(
                                    ' <option value="' + result.repayCycles[i].value + '" selected>第' + result.repayCycles[i].value + '期</option>'
                                );
                            }

                        }else if(result.repayCycles[i].disabled == 1) {
                            if(result.repayCycles[i].selected == 0) {
                                $('#cycles').append(
                                    ' <option value="' + result.repayCycles[i].value + '" disabled>第' + result.repayCycles[i].value + '期</option>'
                                );
                            }else if(result.repayCycles[i].selected == 1) {
                                $('#cycles').append(
                                    ' <option value="' + result.repayCycles[i].value + '" disabled selected>第' + result.repayCycles[i].value + '期</option>'
                                );
                            }
                        }
                    }
                    $('#cycles').selectpicker('refresh');
                }
            },'JSON');


        }
    },'JSON');
}

function addRepaymentsSelectLoan() {
    var loan_id = $('#loan_id').val();
    var postData = {
        'loan_id' : loan_id,
    };
    var postUrl = "index.php?m=home&c=loan&a=getLoanByID";

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        }
        if(result.status == 1) {
            $('#principal').val(result.loan.principal);
            $('#cyclical').val(result.loan.cyclical);
            $('#s_money').val(result.loan.cyc_principal);

            $('#r_money').val(result.loan.cyc_principal);
            $('#b_money').val('0');

            $('#principal').attr('readonly','readonly');
            $('#cyclical').attr('readonly','readonly');
            $('#s_money').attr('readonly','readonly');
            $('#cycles').html('');
            var i = 1;
            for(i in result.repayCycles) {
                if(result.repayCycles[i].disabled == 0) {
                    if(result.repayCycles[i].selected == 0) {
                        $('#cycles').append(
                            ' <option value="' + result.repayCycles[i].value + '">第' + result.repayCycles[i].value + '期</option>'
                        );
                    }else if(result.repayCycles[i].selected == 1) {
                        $('#cycles').append(
                            ' <option value="' + result.repayCycles[i].value + '" selected>第' + result.repayCycles[i].value + '期</option>'
                        );
                    }

                }else if(result.repayCycles[i].disabled == 1) {
                    if(result.repayCycles[i].selected == 0) {
                        $('#cycles').append(
                            ' <option value="' + result.repayCycles[i].value + '" disabled>第' + result.repayCycles[i].value + '期</option>'
                        );
                    }else if(result.repayCycles[i].selected == 1) {
                        $('#cycles').append(
                            ' <option value="' + result.repayCycles[i].value + '" disabled selected>第' + result.repayCycles[i].value + '期</option>'
                        );
                    }
                }
            }
            $('#cycles').selectpicker('refresh');
        }
    },'JSON');


}

function addLoanSelectProduct() {

    var customer_id = $('#customer_id').val();
    var foreign_id = $('#foreign_id').val();

    $('#cycle-append').html('');
    var postData = {
        'product_id' : product_id,
    };
    var postUrl = "index.php?m=home&c=cycle&a=getCycleByProductID";

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        }
        if(result.status == 1) {
            // $('.form-control #cycle_id').val(result.cycle.cycle_id);
            $('#cycle_id').selectpicker('val', result.cycle.cycle_id);
            if(result.cycle.cycle_id == '2') {
                $('#cycle-append').append(
                    '<div class="form-group col-lg-6"> ' +
                    '<label>具体时间</label>' +
                    '<select class="form-control" id="juti_data" name="juti_data">' +
                    '  <option value="1">周一</option>' +
                    '  <option value="2">周二</option>' +
                    '  <option value="3">周三</option>' +
                    '  <option value="4">周四</option>' +
                    '  <option value="5">周五</option>' +
                    '  <option value="6">周六</option>' +
                    '  <option value="7">周日</option>' +
                    '</select>' +
                    '</div>');
            }
            if(result.cycle.cycle_id == '5') {
                $('#cycle-append').append(
                    '<div class="form-group col-lg-6"> ' +
                    '<label>具体时间</label>' +
                    '<input type="text" class="form-control" id="juti_data" name="juti_data" placeholder="请输入具体日期" >' +
                    '</div>');
            }

        }
    },'JSON');

    var principal = 0;
    var cyc_interest = 0;
    var cyclical = 0;

    principal = $('#principal').val();  //本金
    cyc_interest = $('#cyc_interest').val();  //每期利息
    cyclical = $('#cyclical').val();  //还款周期


}

function addLoanSelectProduct() {

    var product_id = $('#product_id').val();
    $('#cycle-append').html('');
    var postData = {
        'product_id' : product_id,
    };
    var postUrl = "index.php?m=home&c=cycle&a=getCycleByProductID";

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        }
        if(result.status == 1) {
            // $('.form-control #cycle_id').val(result.cycle.cycle_id);
            $('#cycle_id').selectpicker('val', result.cycle.cycle_id);
            if(result.cycle.cycle_id == '2') {
                $('#cycle-append').append(
                    '<div class="form-group col-lg-6"> ' +
                    '<label>具体时间</label>' +
                    '<select class="form-control" id="juti_data" name="juti_data">' +
                    '  <option value="1">周一</option>' +
                    '  <option value="2">周二</option>' +
                    '  <option value="3">周三</option>' +
                    '  <option value="4">周四</option>' +
                    '  <option value="5">周五</option>' +
                    '  <option value="6">周六</option>' +
                    '  <option value="7">周日</option>' +
                    '</select>' +
                    '</div>');
            }
            if(result.cycle.cycle_id == '5') {
                $('#cycle-append').append(
                    '<div class="form-group col-lg-6"> ' +
                    '<label>具体时间</label>' +
                    '<input type="text" class="form-control" id="juti_data" name="juti_data" placeholder="请输入具体日期" >' +
                    '</div>');
            }

        }
    },'JSON');

    var principal = 0;
    var cyc_interest = 0;
    var cyclical = 0;

    principal = $('#principal').val();  //本金
    cyc_interest = $('#cyc_interest').val();  //每期利息
    cyclical = $('#cyclical').val();  //还款周期

    if(principal != 0 && cyc_interest != 0 && cyclical != 0) {

        var postUrl2 = "index.php?m=home&c=product&a=getProductByID";
        $.post(postUrl2,postData,function (result) {
            if(result.status == 0) {
                return dialog.msg('该产品信息不存在！');
            }
            if(result.status == 1) {
                if(result.product['methods'] == 0) {
                    //每期应还= 本金/周期+每期利息
                    var cyc_principal = parseInt(principal / cyclical) + parseInt(cyc_interest);
                    $('#cyc_principal').val(cyc_principal);

                }else if(result.product['methods'] == 1) {
                    //先利息，后本金
                    $('#cyc_principal').val(cyc_interest);
                }
            }
        },'JSON');

        //预计利息 = 周期 * 每期利息
        var interest = parseInt(cyclical * cyc_interest);
        $('#interest').val(interest);
    }
}

$("#data-dismiss").on("click.dismiss.bs.modal",function(e){$(this).removeData();});


$('#b_money').on('click',function () {
    var r_money = $('#r_money').val();
    var s_money = $('#s_money').val();
    var b_money = 0;
    if(r_money && r_money != null && r_money != '' && s_money && s_money != null && s_money != '') {
        console.log(r_money);
        if(r_money >= s_money) {
            b_money = r_money - s_money;
        }
        $('#b_money').val(b_money);
    }
});

$('#edit-b_money').on('click',function () {
    var r_money = $('#edit-r_money').val();
    var s_money = $('#edit-s_money').val();
    var b_money = 0;
    if(r_money && r_money != null && r_money != '' && s_money && s_money != null && s_money != '') {
        console.log(r_money);
        if(r_money >= s_money) {
            b_money = r_money - s_money;
        }
        $('#edit-b_money').val(b_money);
    }
});

$('#gmt_repay').datetimepicker({
    format: 'yyyy-mm-dd hh:ii',
    language: 'zh-CN',
    todayBtn: 'linked',
    todayHighlight: true,
    pickerPosition: "top-left"
});

$/*('#gmt_create').datetimepicker({
    format: 'yyyy-mm-dd hh:ii',
    language: 'zh-CN',
    todayBtn: 'linked',
    todayHighlight: true,
});*/

/*$('#edit-gmt_create').datetimepicker({
    format: 'yyyy-mm-dd hh:ii',
    language: 'zh-CN',
    todayBtn: 'linked',
    todayHighlight: true,
});*/

$('#edit-gmt_repay').datetimepicker({
    format: 'yyyy-mm-dd hh:ii',
    language: 'zh-CN',
    todayBtn: 'linked',
    todayHighlight: true,
    pickerPosition: "top-left"
});

$('#reservationtime').daterangepicker({
    timePicker: true,
    timePickerIncrement: 1,
    timePicker24Hour : true, //是否使用12小时制来显示时间
    timePickerSeconds: true, //时间选择框是否显示秒数
    autoUpdateInput: false,
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

$('#overduetime').daterangepicker({
    timePicker: true,
    timePickerIncrement: 1,
    timePicker24Hour : true, //是否使用12小时制来显示时间
    timePickerSeconds: true, //时间选择框是否显示秒数
    autoUpdateInput: false,
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

// 设置daterangerpicker的默认值为空
$('#reservationtime').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' 至 ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
});

// 设置daterangerpicker的默认值为空
$('#overduetime').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' 至 ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
});

$('#reservationtime').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
});

$('#overduetime').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
});

$('#get-search').on('click',function () {
    var customer_id = $('#search_customer_id').val();
    var product_id = $('#search_product_id').val();
    var staff_id = $('#search_staff_id').val();
    var foreign_id = $('#search_foreign_id').val();
    var loan_status = $('#search_loan_status').val();
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();

    var getUrl = '/index.php?m=home&c=loan&a=index&customer_id=' + customer_id + '&product_id=' + product_id + '&staff_id=' + staff_id
            + '&loan_status=' + loan_status + '&reservationtime=' + reservationtime + '&foreign_id=' + foreign_id + '&company_id=' + company_id;

    window.location.href =getUrl;
});


$('#get-search-overdue').on('click',function () {
    var customer_id = $('#search_customer_id').val();
    var product_id = $('#search_product_id').val();
    var staff_id = $('#search_staff_id').val();
    var reservationtime = $('#reservationtime').val();
    var overduetime = $('#overduetime').val();
    var company_id = $('#search_company').val();

    var getUrl = '/index.php?m=home&c=overdue&a=index&customer_id=' + customer_id + '&product_id=' + product_id + '&staff_id=' + staff_id
        + '&reservationtime=' + reservationtime + '&company_id=' + company_id + '&overduetime=' + overduetime ;

    window.location.href =getUrl;
});

$('#get-search-settle').on('click',function () {
    var customer_id = $('#search_customer_id').val();
    var product_id = $('#search_product_id').val();
    var staff_id = $('#search_staff_id').val();
    var reservationtime = $('#reservationtime').val();
    var overduetime = $('#overduetime').val();
    var company_id = $('#search_company').val();

    var getUrl = '/index.php?m=home&c=settle&a=index&customer_id=' + customer_id + '&product_id=' + product_id + '&staff_id=' + staff_id
        + '&reservationtime=' + reservationtime + '&company_id=' + company_id + '&overduetime=' + overduetime ;

    window.location.href =getUrl;
});


$('#export-loan').on('click',function () {
    var customer_id = $('#search_customer_id').val();
    var product_id = $('#search_product_id').val();
    var staff_id = $('#search_staff_id').val();
    var loan_status = $('#search_loan_status').val();
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=loan&a=export&customer_id=' + customer_id + '&product_id=' + product_id + '&staff_id=' + staff_id
        + '&loan_status=' + loan_status + '&reservationtime=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;
});

$('#export-overdue').on('click',function () {
    var customer_id = $('#search_customer_id').val();
    var product_id = $('#search_product_id').val();
    var staff_id = $('#search_staff_id').val();
    var reservationtime = $('#reservationtime').val();
    var overduetime = $('#overduetime').val();
    var company_id = $('#search_company').val();

    var getUrl = '/index.php?m=home&c=overdue&a=export&customer_id=' + customer_id + '&product_id=' + product_id + '&staff_id=' + staff_id
        + '&reservationtime=' + reservationtime + '&company_id=' + company_id + '&overduetime=' + overduetime ;

    window.location.href =getUrl;
});

$('#export-settle').on('click',function () {
    var customer_id = $('#search_customer_id').val();
    var product_id = $('#search_product_id').val();
    var staff_id = $('#search_staff_id').val();
    var reservationtime = $('#reservationtime').val();
    var overduetime = $('#overduetime').val();
    var company_id = $('#search_company').val();

    var getUrl = '/index.php?m=home&c=settle&a=export&customer_id=' + customer_id + '&product_id=' + product_id + '&staff_id=' + staff_id
        + '&reservationtime=' + reservationtime + '&company_id=' + company_id + '&overduetime=' + overduetime ;

    window.location.href =getUrl;
});



$('#get-search-customer').on('click',function () {
    var name = $('input[name = "search_name"]').val();
    var phone = $('input[name = "search_phone"]').val();
    var idcard = $('input[name = "search_idcard"]').val();
    var recommender = $('#search_recommender').val();
    var status = $('#search_loan_status').val();
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();

    var getUrl = '/index.php?m=home&c=customer&a=index&name=' + name + '&phone=' + phone + '&idcard=' + idcard
        + '&recommender=' + recommender + '&status=' + status + '&reservationtime=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;
});

$('#export-customer').on('click',function () {
    var name = $('input[name = "search_name"]').val();
    var phone = $('input[name = "search_phone"]').val();
    var idcard = $('input[name = "search_idcard"]').val();
    var recommender = $('#search_recommender').val();
    var status = $('#search_loan_status').val();
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=customer&a=export&name=' + name + '&phone=' + phone + '&idcard=' + idcard
        + '&recommender=' + recommender + '&status=' + status + '&reservationtime=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;
});

$('#get-search-staff').on('click',function () {
    var staff_name = $('input[name = "search_name"]').val();
    var phone_number = $('input[name = "search_phone"]').val();
    var idcard = $('input[name = "search_idcard"]').val();
    var department_id = $('#search_department').val();
    var company_id = $('#search_company').val();
    var status = $('#search_loan_status').val();
    var reservationtime = $('#reservationtime').val();

    var getUrl = '/index.php?m=home&c=staff&a=index&staff_name=' + staff_name + '&phone_number=' + phone_number + '&idcard=' + idcard
        + '&department_id=' + department_id + '&status=' + status + '&reservationtime=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;
});

$('#export-staff').on('click',function () {
    var staff_name = $('input[name = "search_name"]').val();
    var phone_number = $('input[name = "search_phone"]').val();
    var idcard = $('input[name = "search_idcard"]').val();
    var department_id = $('#search_department').val();
    var company_id = $('#search_company').val();
    var status = $('#search_loan_status').val();
    var reservationtime = $('#reservationtime').val();

    var getUrl = '/index.php?m=home&c=staff&a=export&staff_name=' + staff_name + '&phone_number=' + phone_number + '&idcard=' + idcard
        + '&department_id=' + department_id + '&status=' + status + '&reservationtime=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;
});

$('#search-repayment').on('click',function () {
    var search_loan_id = $('input[name = "search_loan_id"]').val();
    var search_customer_name = $('input[name = "search_customer_name"]').val();
    var search_customer_phone = $('input[name = "search_customer_phone"]').val();
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=repayments&a=index&search_loan_id=' + search_loan_id + '&search_customer_name=' + search_customer_name + '&search_customer_phone=' + search_customer_phone
        + '&reservationtime=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;

});

$('#export-repayments').on('click',function () {
    var search_loan_id = $('input[name = "search_loan_id"]').val();
    var search_customer_name = $('input[name = "search_customer_name"]').val();
    var search_customer_phone = $('input[name = "search_customer_phone"]').val();
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=repayments&a=exportExcel&search_loan_id=' + search_loan_id + '&search_customer_name=' + search_customer_name + '&search_customer_phone=' + search_customer_phone
        + '&reservationtime=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;

});

$('#search-charge').on('click',function () {
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=charge&a=index&search_datepicker=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;

});

$('#search-image').on('click',function () {
    var customer_id = $('#search_customer_id').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=image&a=index&customer_id=' + customer_id + '&company_id=' + company_id;

    window.location.href =getUrl;

});

$('#search-message').on('click',function () {
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var search_customer_phone = $('input[name = "search_customer_phone"]').val();
    var getUrl = '/index.php?m=home&c=message&a=index&search_datepicker=' + reservationtime + '&company_id=' + company_id
        + '&search_customer_phone=' + search_customer_phone;
    window.location.href =getUrl;

});

$('#search-tour').on('click',function () {
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var customer_id = $('#search_customer_id').val();
    var location_id = $('#search_location_id').val();
    var staff_id = $('#search_staff_id').val();
    var paystyle_id = $('#search_paystyle_id').val();
    var is_loan = $('#search_is_loan').val();

    var getUrl = '/index.php?m=home&c=tour&a=index&search_datepicker=' + reservationtime + '&company_id=' + company_id + '&customer_id=' + customer_id + '&location_id=' + location_id
        + '&staff_id=' + staff_id + '&staff_id=' + staff_id + '&staff_id=' + staff_id + '&paystyle_id=' + paystyle_id + '&is_loan=' + is_loan;

    window.location.href =getUrl;

});

$('#search-lirun').on('click',function () {
    var search_datepicker_start = $('#search_datepicker_start').val();
    var search_datepicker_end = $('#search_datepicker_end').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=profit&a=index&search_datepicker_start=' + search_datepicker_start + '&search_datepicker_end=' + search_datepicker_end + '&company_id=' + company_id;
    window.location.href =getUrl;

});

$('#search-admin').on('click',function () {
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=other&a=adminList&company_id=' + company_id;
    window.location.href =getUrl;

});

$('#search-wage').on('click',function () {
    var search_datepicker_start = $('#search_datepicker_start').val();
    var search_datepicker_end = $('#search_datepicker_end').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=wage&a=index&search_datepicker_start=' + search_datepicker_start + '&search_datepicker_end=' + search_datepicker_end + '&company_id=' + company_id;
    window.location.href =getUrl;

});

$('#export-wage').on('click',function () {
    var search_datepicker_start = $('#search_datepicker_start').val();
    var search_datepicker_end = $('#search_datepicker_end').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=wage&a=export&search_datepicker_start=' + search_datepicker_start + '&search_datepicker_end=' + search_datepicker_end + '&company_id=' + company_id;
    window.location.href =getUrl;

});

$('#export-lirun').on('click',function () {
    var search_datepicker_start = $('#search_datepicker_start').val();
    var search_datepicker_end = $('#search_datepicker_end').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=profit&a=export&search_datepicker_start=' + search_datepicker_start + '&search_datepicker_end=' + search_datepicker_end + '&company_id=' + company_id;
    window.location.href =getUrl;

});

$('#export-charge').on('click',function () {
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var getUrl = '/index.php?m=home&c=charge&a=exportExcel&search_datepicker=' + reservationtime + '&company_id=' + company_id;;

    window.location.href =getUrl;

});

$('#export-message').on('click',function () {
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var search_customer_phone = $('input[name = "search_customer_phone"]').val();

    var getUrl = '/index.php?m=home&c=message&a=export&search_datepicker=' + reservationtime + '&company_id=' + company_id
        + '&search_customer_phone=' + search_customer_phone;

    window.location.href = getUrl;

});

$('#export-tour').on('click',function () {
    var reservationtime = $('#reservationtime').val();
    var company_id = $('#search_company').val();
    var customer_id = $('#search_customer_id').val();
    var location_id = $('#search_location_id').val();
    var staff_id = $('#search_staff_id').val();
    var paystyle_id = $('#search_paystyle_id').val();
    var is_loan = $('#search_is_loan').val();

    var getUrl = '/index.php?m=home&c=tour&a=export&search_datepicker=' + reservationtime + '&company_id=' + company_id + '&customer_id=' + customer_id + '&location_id=' + location_id
        + '&staff_id=' + staff_id + '&staff_id=' + staff_id + '&staff_id=' + staff_id + '&paystyle_id=' + paystyle_id + '&is_loan=' + is_loan;

    window.location.href =getUrl;

});

$('#get-search-collection').on('click',function () {
    var loan_status = $('#search_loan_status').val();
    var reservationtime = $('#search_datepicker').val();
    var company_id = $('#search_company').val();

    var getUrl = '/index.php?m=home&c=collection&a=index&loan_status=' + loan_status + '&reservationtime=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;
});

$('#export-collection').on('click',function () {
    var loan_status = $('#search_loan_status').val();
    var reservationtime = $('#search_datepicker').val();
    var company_id = $('#search_company').val();

    //var getUrl = '/index.php?m=home&c=collection&a=exportExcel&loan_status=' + loan_status + '&reservationtime=' + reservationtime;
    var getUrl = '/index.php?m=home&c=collection&a=exportExcel&loan_status=' + loan_status + '&reservationtime=' + reservationtime + '&company_id=' + company_id;

    window.location.href =getUrl;
});

$('#param_yulan').on('click',function () {
    var name = $("#customer_id").find("option:selected").text();  //获取Select选择的Text
    var phone = $('#phone').val();
    var idcard = $('#idcard').val();
    var money = $('#money').val();
    var gmt_create = $('#gmt_create').val();
    var company_name = $('#company_name').val();
    var kefu_phone = $('#kefu_phone').val();
    if(!name) {
        return dialog.msg('请先选择客户名');
    }
    if(!phone) {
        return dialog.msg('请先选择客户名');
    }
    /*if(!idcard) {
        return dialog.msg('请先选择客户名');
    }*/
    if(!company_name || !kefu_phone) {
        return dialog.msg('请先选择客户名');
    }
    if(!money) {
        return dialog.msg('请输入应还金额');
    }
    if(!gmt_create) {
        return dialog.msg('请选择提醒还款时间');
    }
    var gmt_create1 = gmt_create;
    var gmt_create2 = gmt_create;
    var gmt_create3 = gmt_create;
    var newdate = gmt_create1.substring(0,4) + '年' + gmt_create2.substring(5,7) + '月' + gmt_create3.substring(8,11) + '日';

    $message = '【' + company_name + '】尊敬的' + name + '：您有一期账单共计' + parseFloat(money).toFixed(2) + '元，请在' + newdate + '下午16：00' +
        '前转账至本公司微信、支付宝或银行卡账户内。' + '（若有疑问请致电客服' + kefu_phone + '，若本期已转请忽略此条短信）';

    $('#param_yulan').val($message);

});


$('#file-0c').fileinput({
    language: 'zh',
    uploadUrl: "/index.php?m=home&c=image&a=ajaxUploadImage", //上传的
}).on("fileuploaded", function(event, data) {
    console.log(data.response);
    if(data.response) {
        //var imageUrl = '/' + data.response.res.file_data.savepath + data.response.res.file_data.savename;
        var imageUrl = data.response.res.file_data.url;
        $("#hide-image-div").append('<input  type="hidden" value= ' + imageUrl + ' name="image[]">');
    }
});

$('#file-0c2').fileinput({
    language: 'zh',
    uploadUrl: "/index.php?m=home&c=image&a=ajaxUploadImage", //上传的
}).on("fileuploaded", function(event, data) {
    console.log(data.response);
    if(data.response) {
        //var imageUrl = '/' + data.response.res.file_data.savepath + data.response.res.file_data.savename;
        var imageUrl = data.response.res.file_data.url;
        $("#hide-image-div2").append('<input  type="hidden" value= ' + imageUrl + ' name="image[]">');
    }
});

$('.text-center #foreign_image').on('click',function () {
    var loan_id = $(this).attr('attr-id');
    var postData = {
        loan_id : loan_id,
    };
    var postUrl = "index.php?m=home&c=image&a=getLoanImage";

    $.post(postUrl,postData,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        }
        if(result.status == 1) {
            console.log(result.photos);
            layer.photos({
                photos: result.photos,
                anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机
            });
        }
    },'JSON');
});

$('.col-md-2 #image-detail').on('click',function () {
    var loan_id = $(this).attr('attr-loan_id');
    var postUrl = "index.php?m=home&c=image&a=detail&loan_id=" + loan_id;

    window.location.href =postUrl;
});

$('.col-lg-12 #image-div').on('click',function () {
    layer.photos({
        photos: '#layer-photos-demo',
        anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
    });
});

$('#image-all-checked').on('click',function () {
    $("input[name='checkbox']").each(function(){
        if($(this).attr("checked"))
        {
            $(this).removeAttr("checked");
        }
        else
        {
            $(this).attr("checked","true");
        }
    })
});

$('#checkbox-loanstatus').on('click',function () {
    var nowdate = $(this).attr('attr-date');
    var imageValue =[];
    $('input[name="checkbox"]:checked').each(function(){
        imageValue.push($(this).val());
    });

    if(imageValue.length == 0) {
        return dialog.msg('请选择一条记录');
    }
    console.log(imageValue);

    var postData = {
        'imageValue' : imageValue,
        'nowdate' : nowdate,
    };
    var postUrl = "index.php?m=home&c=repayments&a=addRepaymentsAutoCheckbox";
    var jumpUrl = window.location.href;
    layer.open({
        type : 0,
        title : '请确定',
        btn : ['是','否'],
        icon : 3,
        closeBtn : 2,
        content : "是否批量收款",
        scrollbar : true,
        yes : function () {
            //执行跳转
            ajaxPost(postUrl,postData,jumpUrl);   //抛送ajax请求
        }
    });
});


$('#image-delete').on('click',function () {
    var imageValue =[];
    $('input[name="checkbox"]:checked').each(function(){
        imageValue.push($(this).val());
    });

    if(imageValue.length == 0) {
        return dialog.msg('请选择一张照片');
    }
    console.log(imageValue);

    var postData = {
        'imageValue' : imageValue,
    };
    var postUrl = "index.php?m=home&c=image&a=deleteImage";
    var jumpUrl = window.location.href;
    layer.open({
        type : 0,
        title : '请确定',
        btn : ['是','否'],
        icon : 3,
        closeBtn : 2,
        content : "是否删除图片",
        scrollbar : true,
        yes : function () {
            //执行跳转
            ajaxPost(postUrl,postData,jumpUrl);   //抛送ajax请求
        }
    });
});

$('#add-record-image').click(function () {
    var m = $(this).attr('attr-m');
    var c = $(this).attr('attr-c');
    var a = $(this).attr('attr-a');
    var loan_id = $(this).attr('attr-loan_id');

    var postData = {};
    var valArr = new Array();

    $('input[name = "image[]"]').each(function(i){
        valArr[i] = $(this).val();
    });

    postData['valArr'] = valArr;
    postData['loan_id'] = loan_id;

    console.log(postData);

    var postUrl = "index.php?m="+ m + "&c=" + c + "&a=" + a;
    //var jumpUrl = "index.php?m="+ m + "&c=" + c + "&a=index";
    var jumpUrl = window.location.href;

    ajaxPost_msg(postUrl,postData,jumpUrl);
});

$('#image-guanlu').click(function () {
    $(".image-check-div").css("display","inline");
    $("#image-all-checked").css("display","inline");
    $("#image-delete").css("display","inline");
    $("#export-charge").css("display","inline");
    $("#image-quxiaoguanli").css("display","inline");
    $(this).css("display","none");
});

$('#image-quxiaoguanli').click(function () {
    $(".image-check-div").css("display","none");
    $("#image-all-checked").css("display","none");
    $("#image-delete").css("display","none");
    $("#export-charge").css("display","none");
    $("#image-guanlu").css("display","inline");
    $(this).css("display","none");
});

$('.text-center #xiugaipwd').click(function () {
   var user_id = $(this).attr('attr-id');


    layer.prompt({title: '请输入密码', formType: 1}, function(pass, index){
        layer.close(index);

        var postData = {
            user_id : user_id,
            password : pass,
        };
        var postUrl = "index.php?m=home&c=other&a=changePasswordList";

        $.post(postUrl,postData,function (result) {
            if(result.status == 0) {
                return dialog.msg(result.message);
            }
            if(result.status == 1) {
                return dialog.msg(result.message,'');
            }
        },'JSON');

    });
});