$('#login').click(function () {

    var username = $('input[name = "username"]').val();
    var password = $('input[name = "password"]').val();
    var vercode = $('input[name = "vercode"]').val();

    // 邮箱正则表达式
    // var zemail = /^[a-z0-9]+([._\\\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/;

    if(!username) {
        return dialog.msg('请输入手机号或电子邮箱');
    }

    if(!password) {
        return dialog.msg('请输入您的账号密码');
    }


    //设定正则表达式规则
    var zmobile = /^(1[34578]\d{9})$/;  //手机号码验证
    var zemail = /^(\w{1,}@\w{1,}\.\w{1,})$/;//邮箱验证
    var zpassword = /^.{6,20}$/;


    var user_mobile = '';
    var user_email = '';

    if(zmobile.test(username))
        user_mobile = username;
    if(zemail.test(username))
        user_email = username;

    if(!user_mobile && !user_email)
        return dialog.msg('输入的用户名不符合规则，请重新输入');
    if(!zpassword.test(password))
        return dialog.msg('输入的密码不符合规则，请重新输入');


    var data = {
        'mobile': user_mobile,
        'email': user_email,
        'password' : password,
        'vercode' : vercode,
    };

    var postUrl = 'index.php?m=home&c=login&a=loginCheck';
    var jumpUrl = 'index.php?m=home';

    //进行Ajax异步请求
    $.post(postUrl,data,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        } else if(result.status == 1) {
            return dialog.msg_url(result.message,jumpUrl);
        }
    },'JSON');
});