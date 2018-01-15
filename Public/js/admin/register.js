$('#register').click(function () {

    var username = $('input[name = "username"]').val();
    var email = $('input[name = "email"]').val();
    var password = $('input[name = "password"]').val();
    var repassword = $('input[name = "repassword"]').val();
    // 邮箱正则表达式
    var zemail = /^[a-z0-9]+([._\\\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/;

    if(!username) {
        return dialog.msg('请填写您的姓名');
    }
    if(!email) {
        return dialog.msg('请填写您的电子邮箱');
    }
    if(!zemail.test(email)) {
        return dialog.msg('电子邮箱格式有误');
    }
    if(!password) {
        return dialog.msg('请输入您的账号密码');
    }
    if(password.length < 6) {
        return dialog.msg('账号密码必须大于6位字符');
    }
    if(password.length > 20) {
        return dialog.msg('账号密码必须不得超过20位字符');
    }
    if(!repassword) {
        return dialog.msg('请再次输入您的账号密码');
    }
    if(password != repassword) {
        return dialog.msg('两次输入的账号密码不一致');
    }

    var data = {
        'username' : username,
        'email' : email,
        'password' : password,
    }

    var postUrl = 'index.php?m=home&c=register&a=registerCheck';
    var jumpUrl = 'index.php?m=home&c=login';

    //进行Ajax异步请求
    $.post(postUrl,data,function (result) {
        if(result.status == 0) {
            return dialog.msg(result.message);
        } else if(result.status == 1) {
            return dialog.msg_url(result.message,jumpUrl);
        }
    },'JSON');
});