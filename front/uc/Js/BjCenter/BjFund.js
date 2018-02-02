// JavaScript Document
(function($){
	var z = 1000;
	$.fn.QImage = function(tLen){
		$(this).each(function(i){
            var iT = 0,index = 0,
			dd = $(this).find('dd'),
			dt = $(this).find('dt'),
			nli = $(dt).children(),
			ali = $(dd).children();
			tLen = typeof tLen == 'number'?tLen:3000;
			$(nli).each(function(j){
                $(this).click(function(){
					clearInterval(iT);
					$(this).addClass('Cur').siblings('span').removeClass('Cur');
					$(ali).eq(j).css('display','block').siblings('a').css('display','none');
					index = j;
				})
            });
			auto();
			function auto(){
				clearInterval(iT);
				iT = setInterval(function(){
					index = index + 1;
					if(index == $(nli).length){
						index = 0;
					}
					$(ali).eq(index).css('display','block').siblings('a').css('display','none');
					$(nli).eq(index).addClass('Cur').siblings('span').removeClass('Cur');
				},tLen);
			}
        });
	}
	$.fn.uSelect = function(callback){//美化下拉选框，callback 触发下拉选框onChange事件
		$(this).each(function(ii){
			var T,pli,index = 0,fn,
			//w = $(this).width(),
			len = $(this).find('.SltBox').length,
			slt = $(this).find('select'),
			w = $(slt).outerWidth(),
			ops = $(slt).children(),
			inp = $('<input>'),
			box = $('<div>'),
			btn = $('<div>');
			w = w > $(this).width()?w:$(this).width();
			index = $(slt).find("option:selected").prevAll().length;
			//alert($(slt).innerWidth());
			if(len > 0){//$(slt).is(':hidden')//alert($(slt).is(':hidden'));
				$(slt).css({'visibility':'hidden'});
				inp = $(this).find('input');
				box = $(this).find('.SltBox');
				btn = $(this).find('.SltBtn');
				$(box).html('');
			}else{
				z--;
				$(this).css('width',w+'px');
				$(this).css('zIndex',z);
				$(box).addClass('SltBox');
				$(btn).addClass('SltBtn');
				$(inp).attr('readonly',true);
				$(inp).css('width',w+'px')
				$(this).append(inp);
				$(this).append(btn)
				$(this).append(box);
				$(slt).css('display','none');
				$(box).css('width',$(this).innerWidth()+'px');
				$(box).css('top',$(this).outerHeight()-1+'px');
				$(inp).css('left',($(this).innerWidth() - $(this).width())/2+'px')
				$(box).hover(
					function(){
						$(this).addClass('SltHov');
					},
					function(){
						$(this).removeClass('SltHov');
					}
				);
				$(inp).click(function(){
					if($(btn).hasClass('SltUp')){
						$(box).css('display','none');
						$(btn).removeClass('SltUp');
					}else{
						$(box).css('display','block');
						$(btn).addClass('SltUp');
					}
				});
				$(this).hover(
					function(){
						clearInterval(T);
					},
					function(){
						clearInterval(T);
						T = setTimeout(function(){
							$(box).css('display','none');
							$(btn).removeClass('SltUp');
						},500)
					}
				);
				$(btn).click(function(){
					if($(this).hasClass('SltUp')){
						$(box).css('display','none');
						$(btn).removeClass('SltUp');
					}else{
						$(box).css('display','block');
						$(btn).addClass('SltUp');
					}
				});
			}
			$(ops).each(function(i){
				var p = $('<p>');
				$(p).html($(this).html());
				$(box).append(p);
				if(i == index){
					$(inp).val($(this).html());
					if($(inp).is(':hidden')){
						$(btn).html($(this).html());
					}
					$(p).addClass('Ac');
				}
            });
			pli = $(box).find('p');
			$(pli).each(function(i) {
				$(this).click(function(){
					$(inp).val($(this).html());
					if($(inp).is(':hidden')){
						$(btn).html($(this).html());
					}
					$(ops).eq(i).attr("selected",true).siblings('option').removeAttr('selected');
					if(!$(this).hasClass('Ac')){
						//callback(slt);
						if(slt[0].fireEvent){
							slt[0].fireEvent('onchange');
						}else{
							var e = document.createEvent('HTMLEvents');
        					e.initEvent('change', false, false);
							slt[0].dispatchEvent(e);
						}
					}
					$(this).addClass('Ac').siblings('p').removeClass('Ac');
					$(box).css('display','none');
					$(btn).removeClass('SltUp');
				});
				$(this).hover(
					function(){
						$(this).addClass('Cur');
					},
					function(){
						$(this).removeClass('Cur');
					}
				);
            });
		});
	}
	$.fn.uRadio = function(){
		$(this).each(function(i){
			var obj = this,clk = false,
			inp = $(obj).find('input[type="radio"]'),
			b = $(obj).find('b');
			if($(b).length == 0){
				b = $('<b>');
				$(obj).append(b);
				clk = true;
			}			
			flag = $(inp).attr('checked');
			flag = typeof flag == 'undefined'?false:(flag == 'checked'?true:false);
			if(flag){
				$(this).addClass('Chk');
				$(b).css('display','blcok');
			}else{
				$(this).removeClass('Chk');
			}
			if(clk){
				$(obj).click(function(){
					if(!$(this).hasClass('Chk')){
						var inp = $(this).find('input[type="radio"]'),
						name = $(inp).attr('name'),
						inps = $("input[name='"+name+"']");
						
						$(inps).each(function(i){
							$(this).parents('.uRadio').removeClass('Chk');
							$(this).removeAttr('checked');
						});
						$(this).addClass('Chk');
						$(inp).attr('checked',true);
					}
					if(inp[0].fireEvent){
						inp[0].fireEvent('onclick');
					}else{
						var e = document.createEvent('HTMLEvents');
						e.initEvent('click', false, false);
						inp[0].dispatchEvent(e);
					}
				}); 
			}
        });
	}
	$.fn.clearRadio = function(){
		$(this).each(function(i){
			$(this).find('b').remove();
        });
	}
	$.fn.uCheckBox = function(){
		$(this).each(function(i){
			var inp = $(this).find('input[type="checkbox"]'),
			b = $('<b>'),
			//IE = (!document.createEvent && $.browser.msie)?false:true,
			flag = $(inp).attr('checked');
			flag = typeof flag == 'undefined'?false:(flag == 'checked'?true:false);
			
			if(flag){
				$(this).addClass('Chked');
				$(b).css('display','blcok');
			}else{
				$(this).removeClass('Chked');
			}		
						
			$(this).append(b);
			$(b).click(function(){
				var p = $(this).parents('.uChkBox'),
				inp = $(p).find('input[type="checkbox"]'),
				ieFlag = (typeof $.browser.msie == 'undefined')?false:true,
				flag = $(inp).attr('checked');
				if(flag){
					$(p).removeClass('Chked');
					if(ieFlag){
						$(inp).removeAttr('checked');
					}
				}else{
					$(p).addClass('Chked');
					if(ieFlag){
						$(inp).attr('checked',true);
					}
				}
				if(inp[0].fireEvent){
					inp[0].fireEvent('onclick');
				}else{
					/*var e = document.createEvent('HTMLEvents');
					e.initEvent('click', false, false);
					inp[0].dispatchEvent(e);*/
					var evObj = document.createEvent('MouseEvents');
					evObj.initEvent( 'click', true, true );
					inp[0].dispatchEvent(evObj);
				}				
			});
		})
	}
	$.fn.upFile = function(w,h){
		var obj = this[0],
		p = $(this).parent(),
		showBox = getBox(p),
		Img = $(showBox).find('img')[0],
		str = obj.value;
		Img.src = '';
		if(obj.files && obj.files[0]){
			var file =obj.files[0],
			reader = new FileReader();
			reader.onload = function(event){
				var e = event || window.event;
				$(showBox).html('<img src = "'+e.target.result+'" />');
			}
			reader.readAsDataURL(file)
		}else{
			var src = '',
			div = showBox[0];
			obj.select();
			if (top != self) {
				window.parent.document.body.focus();
			} else {
				obj.blur();
			}
			src = document.selection.createRange().text;
			document.selection.empty();
			$(Img).hide();
			$(div).css({
				'filter': 'progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale)',
				'width': w + 'px',
				'height': h + 'px'
			});
			div.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = src;
		}
		function getBox(o){
			var ul = $(o).find('.showImg');
			while($(ul).length == 0){
				o = $(o).parent();
				ul = $(o).find('.showImg');
			}
			return ul;
		}
		
	}
	/*银行卡号输入 start*/
	$.fn.bankInp = function(iReg){
		$(this).each(function(i){
			$(this).bind('keyup',function(event){
				var e = event || window.event,
				vstr = $(this).val(),
				len = vstr.length,
				code = e.keyCode,
				reg = /^[\d+\s?]+$/g;//验证输入为数字
				if(reg.test(vstr)){
					reg = /([^\d])/g;
					var i = 0,istr = '',j = 0,pos = $(this).getCursor(),
					str = vstr.replace(reg,''),
					len = str.length;
					//$('#mess').html(pos)
					if(len >= 4 && (code != 8 || code != 39 || code != 37)){
						while(i < len){
							var n = len-i >= 4?4:len-i,
							_str = str.substr(i,n),
							_split = n==4?' ':'';
							istr = istr + _str + _split;
							i = i + 4;
							if(n < 4){
								break;
							}
						}
					}else{
						istr = vstr;
					}					
					$(this).val(istr);
					$(this).next().html(istr);
					$(this).next().css('display','block');
					
					if(code == 8 || code == 46 || code == 37 || code == 39){
						$(this).setCursor(pos,code);
					}
				}else{
					reg = /[^\d\s]/;
					vstr =vstr.replace(reg,'');
					$(this).val(vstr);
				}			
			});
			$(this).bind('blur',function(){
				var str = $(this).val();
				$(this).next().css('display','none');
				if(str == ''){
					$(this).showTip('Error','银行卡号不能为空');
				}else{
					str = str.replace(/\s/g,'');
					reg = typeof iReg == 'undefined'?/^(\d{16}|\d{19})$/g:iReg;
					if(reg.test(str)){
						$(this).showTip('Right');
					}else{
						$(this).showTip('Error','银行卡号格式不正确!');
					}
				}
			});
        });
	}
	$.fn.getCursor = function(){//获取光标的位置
		var Pos = 0;
		$(this).each(function(i){
            var o = this;
			if(document.selection) {// IE Support 
				o.focus(); 
				var S1 = document.selection.createRange(),
				S2 = S1.duplicate();
				S1.moveStart("character", -event.srcElement.value.length);
				Pos = S1.text.length;
			}else if(o.selectionStart || o.selectionStart == '0'){// Firefox support 
				Pos = o.selectionStart;
			}
        });
		return Pos;
	}
	$.fn.setCursor = function(pos,code){//设置光标位置
		$(this).each(function(i){
            var o = this;
			if(o.setSelectionRange){ 
				o.focus(); 
				o.setSelectionRange(pos,pos); 
			}else if (o.createTextRange){ 
				o.focus(); 
				var range = o.createTextRange(); 
				range.collapse(true); 
				range.moveEnd('character',pos); 
				range.moveStart('character',pos); 
				range.select(); 
			} 
        });
	}
	/*银行卡号输入 end*/
	
	/*身份证号输入 start*/
	$.fn.inpIdent = function(iReg){
		return $(this).each(function(i){
        	$(this).bind('keyup',function(event){
				var o = this,
				str = $(o).val(),
				reg = /\d{0,17}[\d|x]|\d{0,15}/g,
				pos = $(this).getCursor();
				if(!reg.test(str)){
					var len = str.length -1;
					str = str.substr(0,pos-1)+str.substr(pos,len);//str.substr(0,len);
					$(this).val(str);
					$(this).setCursor(pos-1);
				}
			});
			$(this).bind('blur',function(){
				var str = $(this).val();
				if(str == ''){
					$(this).showTip('Error','身份证号不能为空');
				}else{
					reg = typeof iReg == 'undefined'?/\d{17}[\d|x]|\d{15}/g:iReg;
					if(reg.test(str)){
						$(this).showTip('Right');
					}else{
						$(this).showTip('Error','身份证号格式不正确!');
					}
				}
			});
        });
	}
	/*身份证号输入 end*/
	
	/*金额输入事件 start*/
	$.fn.inpCash = function(){
		return $(this).each(function(i){
            $(this).bind('keyup',function(event){
				var o = this,
				str = $(o).val(),
				reg = /^0$|^0\.\d{0,2}$|^[1-9]\d*\.?\d{0,2}$/g,
				pos = $(this).getCursor();
				//$(this).parent().next().html(pos)
				//reg = /^\d+\.?\d{0,2}$/g;
				if(!reg.test(str)){	
					var len = str.length -1,
					ireg = /[^\d\.]/g;
					str = str.substr(0,pos-1)+str.substr(pos,len);//str.substr(0,len);
					str = str.replace(ireg,'');
					ireg = /(^\d*\.\d{2})/g;
					var arr = str.match(ireg);
					if(arr != null){
						str = arr[0];
					}
					$(this).val(str);
					$(this).setCursor(pos-1);
				}else{
					//if(str.length > 10){
					var intPos = str.indexOf('.');
					$('#info').html(intPos);
					if(intPos == 11){							
						str = str.replace(/\.$/g,'');
					}
					$(this).val(str);
					//}
					if(str.length > 12){
						str = str.substr(0,pos-1)+str.substr(pos,len);
						$(this).val(str);
						$(this).setCursor(pos-1);						
					}
					var txt = $(this).cashTxt();//WordTxt(str);
					$('.Capital').html(txt);
				}
			});
		});
	}
	$.fn.cashTxt = function(){
		var num = $(this).val(),
		strOutput = "",
		strUnit = '仟佰拾亿仟佰拾万仟佰拾元角分';  
		num += "00";
		var intPos = num.indexOf('.');  
		if (intPos >= 0){  
			num = num.substring(0, intPos) + num.substr(intPos + 1, 2);
		}  
		strUnit = strUnit.substr(strUnit.length - num.length);  	
		for (var i=0; i < num.length; i++){
			strOutput += '零壹贰叁肆伍陆柒捌玖'.substr(num.substr(i,1),1) + strUnit.substr(i,1);
		}
		return strOutput.replace(/零角零分$/, '整').replace(/零[仟佰拾]/g, '零').replace(/零{2,}/g, '零').replace(/零([亿|万])/g, '$1').replace(/零+元/, '元').replace(/亿零{0,3}万/, '亿').replace(/^元/, "零元");
	}
	/*倒计时*/
	$.fn.codeTime = function(Len){//Len为空是启动计时器
		var o = this;
		if(typeof Len == 'undefined'){
			if(!$(o).hasClass('unClk')){
				var t = T;
				clearInterval(Time);
				$(o).addClass('unClk').html(T+'s后可重新获取');
				Time = setInterval(function(){
					T--;
					if(T == 0){
						$(o).removeClass('unClk').html('重新获取验证码');
						clearInterval(Time);
						T = t;
					}else{
						$(o).html(T+'s后可重新获取');
					}
				},1000);
			}
		}else{//重置计时器 Len为下一次启动计时器历时 例如60
			clearInterval(Time);
			T = Len;
			$(o).removeClass('unClk').html('获取短信验证码');
		}
	}
	$.fn.showTip = function(cls,msg){
		$(this).each(function(i){
			var em = $(this).parents('.formLi').find('em:last');
			msg = typeof msg == 'undefined'?'':msg;
			$(em).attr('class',cls);
			$(em).html(msg);
        });
	}
	/*手机号验证*/
	$.fn.inpTel = function(iReg){
		$(this).each(function(i){
            $(this).bind('keyup',function(){
				var str = $(this).val(),
				reg = /^1$|^1[3|4|5|7|8]$|^1[3|4|5|7|8]{1}\d{0,9}$/g;
				//em = $(this).parents('.formLi').find('em:last');
				if(!reg.test(str)){
					var len = str.length -1;
					str = str.substr(0,len);
					$(this).val(str);
				}
			});
			$(this).bind('blur',function(){
				var str = $(this).val(),
				reg = typeof iReg == 'undefined'?/^1[3|4|5|7|8]\d{9}$/g:iReg,
				em = $(this).parents('.formLi').find('em:last');
				if(str == ''){
					$(this).showTip('Error','手机号码不能为空!');
				}else if(reg.test(str)){
					$(this).showTip('Right','');
				}else{
					$(this).showTip('Error','手机号格式不正确!');
				}
				
			})
        });
	}
	/*选择基金下拉选框*/
	$.fn.Fund = function(args){
		var num = typeof args.number == 'undefined'?1:args.number;
		$(this).each(function(i){
            var fT = 0,
			fundSlt = $('.fund_suggest'),
			dt = $(fundSlt).children('dt'),
			cls = $(dt).find('span'),
			tli = $(dt).children('a'),
			dd = $(fundSlt).find('dd'),
			inp = $(this).find('input'),
			btnSlt = $(this).find('b'),
			l = $(this).offset().left,
			t = $(this).offset().top + $(this).outerHeight(true),
			tab = {
				init:function(){
					var th = this;
					$(fundSlt).css({'left':l+'px','top':t+'px','display':'none'});
					$(tli).each(function(j){
                        $(this).bind('click',function(){
							clearTimeout(fT);
							$(inp).focus();
							if(j == 0){
								var html = '',
								code = $(inp).val();
								if(code == ''){
									html = '<p>请在搜索框内输入“<span>代码</span>”“<span>拼音</span>”或“<span>简称</span>”</p>';
									$('.Inp').html(html);
								}else{
									if(typeof args.keyup == 'function'){
										args.keyup(inp,function(){
											th.table();
										});
									}									
 								}
								
							}
							th.showTo(j);
						});
                    });
				},
				showTo:function(n){
					$(tli).eq(n).addClass('CM').siblings('a').removeClass('CM');
					$(dd).eq(n).css('display','block').siblings('dd').css('display','none');
				},
				table:function(box){
					var table = $('.Inp').eq(0).find('tbody'),
					tr = $(table).find('tr');
					
					$(tr).each(function(_i) {
						$(this).hover(
							function(){
								var bojiaId = $(this).attr('datacode');
								$(this).addClass('fundCur');
								$(inp).val(bojiaId)
							},
							function(){
								$(this).removeClass('fundCur');
							}
						)
						$(this).click(function(){
							if(typeof args.click == 'function'){
								args.click(this);
							}
						});
					});
				}
			}
			tab.init();
						
			$(inp).bind('focus',function(){
				clearTimeout(fT);
				var str = $(this).val();
				if(str == '请输入基金代码或简称'){
					$(this).val('')
				}
				$(this).parent().addClass('redInp');
				$(fundSlt).css({'display':'block'});
			});
			$(inp).bind('blur',function(){
				var o = this;
				fT = setTimeout(function(){
					$(o).parent().removeClass('redInp');
					$(fundSlt).css('display','none');
				},300);
			});
			
			$(inp).bind('keyup',function(){
				tab.showTo(0);
				if(typeof args.keyup == 'function'){
					args.keyup(this,function(){
						tab.table();
					});
				}
			});
			$(btnSlt).bind('click',function(){
				tab.showTo(num);
				$(inp).focus();
			});
			
			$(fundSlt).mouseenter(function(){
				clearTimeout(fT);
				$(inp).focus();
			});
			
			$(dd).each(function(j){
                var ali = $(this).find('a');
				$(ali).each(function(n){
					$(this).bind('mouseover',function(){
						var code = $(this).attr('data-code');
						$(inp).val(code);
					});
					$(this).bind('click',function(){
						var _o = this;
						clearTimeout(fT);
						if(typeof args.click == 'function'){
							args.click(_o);
						}
						$(inp).parent().removeClass('redInp');
					});
                });
            });
        });
	}
	$.fn.QTab = function(num,clkType){
		$(this).each(function(i){
            var dt = $(this).children('dt'),
			//cls = $(dt).find('span'),
			tli = $(dt).children('a'),
			dd = $(this).children('dd');
			//clk = $(this).attr('clk');
			//clk = typeof clk == 'undefined'?true:clk;
			clkType = typeof clkType == 'undefined'?'mouseover':clkType;
			if(typeof num == 'undefined'){
				for(var j = 0; j < $(tli).length; j++){
					if($(tli).eq(j).hasClass('CM')){
						num = j;
						$(dd).eq(j).css('display','block').siblings('dd').css('display','none');
						break;
					}
				}
			}else{
				num = num >= $(dt).length?0:num;
				$(dt).eq(num).addClass('CM').siblings('a').removeClass('CM');
				$(dd).eq(num).css('display','block').siblings('dd').css('display','none');
			}
			//if(clk){
				/*$(this).attr('clk',false);
				$(cls).each(function(j){
                    $(this).bind('click',function(){alert('me')
						$(this).parents('dl').css('display','none');
					});
                });*/
				$(tli).each(function(j){				
					$(this).bind(clkType,function(){
						$(this).addClass('CM').siblings('a').removeClass('CM');
						$(dd).eq(j).css('display','block').siblings('dd').css('display','none');
					});
				});
			//}
        });
	}
	$.fn.sliDer = function(){
		$(this).each(function(i){
            var li = $(this).children('li');
			$(li).each(function(j){
                $(this).bind('click',function(){
					var h = $(this).height();
					if(h > 30){
						$(this).css('height','30px');
					}else{
						$(this).css('height','auto');
					}
				});
            });
        });
	} 
	/*弹窗事件处理*/
	$.fn.winInit = function(){//初始分关闭 按钮点击事件
		$(this).each(function(i){
            var cls = $(this).find('.qCls'),
			btn = $(this).find('.qWbtn'),
			bli = $(btn).children('a');
			$(cls).each(function(j){
                $(this).bind('click',function(){
					$(this).parents('.qWin').css('display','none');
					$('.qMask').css('display','none');
				})
            });
			$(bli).each(function(i){
				if(!$(this).hasClass('undo')){
					$(this).bind('click',function(){
						$(this).parents('.qWin').css('display','none');
						if($('.qWin:visible').length == 0){
							$('.qMask').css('display','none');
						}
					});
				}
            });
        });
	}
	$.fn.winPlace = function(){//初始化弹窗位置及高度
		$(this).each(function(i){
            var w = document.documentElement.clientWidth,
			h = document.documentElement.clientHeight,
			sh = $('body').outerHeight(true),
			_h = $(this).height(),
			_w = $(this).outerWidth(true),
			_ah = $(this).outerHeight(true) - $(this).height(),
			left = (w - _w)/2,
			top = (h - _h - _ah)/2;
			if(_h > h){
				var con = $(this).find('.qWmess'),
				_ch = $(con).prev().outerHeight(true) + $(con).next().outerHeight(true) + ($(con).outerHeight(true) - $(con).height()) + _ah;
				$(this).css('height',(h-_ah)+'px');
				$(con).css('height',(h-_ch)+'px');
			}else{
				var con = $(this).find('.qWmess');
				$(con).css('height','auto');
			}
			left = left < 0?0:left;
			top = top < 0?0:top;
			
			$(this).css({'left':left+'px','top':top+'px'});
        });
	}
	$.fn.showWin = function(msg,til){//显示弹窗
		$(this).each(function(i){
			if(typeof msg != 'undefined'){
				$(this).find('.qWmess').html(msg);
			}
			if(typeof til !== 'undefined'){
				$(this).find('h3>span').html(til);
			}
            $(this).css('display','block');
			var mask = $('.qMask'),
			w = document.documentElement.clientWidth,
			h = document.documentElement.clientHeight,
			sh = $('body').outerHeight(true);
			$(this).winPlace();
			$(mask).css({'height':(h<sh?sh:h)+'px','display':'block'});
        });	
	}
	$.fn.hidWin = function(){//隐藏弹窗
		$(this).each(function(i){
            $(this).css('display','none');
			$('.qMask').css('display','none');
        });
	}
	$('.qWin').winInit();//初始化弹窗关闭事件
	
})(jQuery)