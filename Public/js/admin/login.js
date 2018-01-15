$('#login').click(function () {

    var email = $('input[name = "email"]').val();
    var password = $('input[name = "password"]').val();

    // 邮箱正则表达式
    var zemail = /^[a-z0-9]+([._\\\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/;

    if(!email) {
        return dialog.msg('请填写您的电子邮箱');
    }
    if(!zemail.test(email)) {
        return dialog.msg('电子邮箱格式有误');
    }
    if(!password) {
        return dialog.msg('请输入您的账号密码');
    }

    var data = {
        'email' : email,
        'password' : password,
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