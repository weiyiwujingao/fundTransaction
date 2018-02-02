var bankReg = /^\d{10,20}$/;
var identReg = /^[a-zA-Z0-9]{5,18}$/;
var phoneReg = /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
var emailReg = /^[\w\-\.]+\@[\w\-]+[\w\-\.]+$/;
var toolTest={
    IsPhoneNum:function(str){
            var reg = phoneReg;
            return reg.test(str);    
        },
    IsIdentNum:function(str){
    	var reg = identReg;
    	return reg.test(str);    
    },
    IsEmail:function(str){
    	var reg = emailReg;
    	return reg.test(str);    
    },
    IsBankCardNum:function(str){
    	var reg = bankReg;
    	return reg.test(str);    
    },
    IsTradPswd:function(str){
    	var reg = /^[\w\@\#\$]{6,20}$/;
    	return reg.test(str);    
    },
    ValPicCode:function(str){
        return str.length==4;
    },
    ValMsgCode:function(str){
    	return str.length==6&&parseInt(str);
    }
};

var T = 180,Tout;
function countDown(tTime, BtnID){
    if(!$(BtnID).hasClass('Time')){
        //clearInterval(Tout);
        $(BtnID).html('('+T+'S)重新获取');
        $(BtnID).addClass('Time');
        Tout = setInterval(function(){
            if(T == 0){
                clearInterval(Tout);
                $(BtnID).html('获取短信验证');
                $(BtnID).removeClass('Time')
                T = tTime;
            }else{
                T--;
                $(BtnID).html('('+T+'S)重新获取');
            }
        },1000);
    }else{
        if(T == tTime){
            clearInterval(Tout);
        }
    }
}
var X = 60, Xout;
function countDownX(tTime, BtnID){
    if(!$(BtnID).hasClass('Time')){
        //clearInterval(Tout);
        $(BtnID).html('('+X+'S)重新获取');
        $(BtnID).addClass('Time');
        Xout = setInterval(function(){
            if(X == 0){
                clearInterval(Xout);
                $(BtnID).html('获取短信验证');
                $(BtnID).removeClass('Time')
                X = tTime;
            }else{
                X--;
                $(BtnID).html('('+X+'S)重新获取');
            }
        },1000);
    }else{
        if(T == tTime){
            clearInterval(Xout);
        }
    }
}
var minPswdLength = 8;
var maxPswdLength = 20;
function checkPswd(str, pswdError){

    if(str.length < minPswdLength){
        return 0;
    }else if(str.length > maxPswdLength){
        return 1;
    } else if(! /^[\w\@\#\$]{8,20}$/.test(str) ){
        return 2;
    } else if( /^[0-9]{8,20}$/.test(str) || /^[a-zA-Z]{8,20}$/.test(str) || /^[\_\@\#\$]{8,20}$/.test(str) ){
        return 3;
    }
    return 10;
}

var arrPswdError = ['您输入少于'+minPswdLength+'个字符!','您输入多于'+maxPswdLength+'个字符!','密码由数字，字母和_@#$组成!','密码不能纯数字或纯字母！'];
//获取get值
var $_GET = (function(){
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if(typeof(u[1]) == "string"){
        u = u[1].split("&");
        var get = {};
        for(var i in u){
            var j = u[i].split("=");
            get[j[0]] = j[1];
        }
        return get;
    } else {
        return {};
    }
})();
//获取get值
function getvalue(par){
	if(typeof(par) == "string"){
        return $_GET[par];
    } else {
        return {};
    }
}

function oShowTips(obj, aClass, txt){
    $(obj).html(txt);
    $(obj).attr('class',aClass);
}