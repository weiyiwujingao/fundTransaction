// JavaScript Document
function pwdStre(pstrID){
	var pStr = $('#'+pstrID).val();
	var scroe = 0;
	
    if(pStr.length && pStr.length < 8) {
        scroe += 5;
    } else if(pStr.length < 12 && pStr.length >= 8) {
        scroe += 10;
    } else if(pStr.length >= 12){
        scroe += 25;
    }
    if(!/[A-z]+/g.test(pStr)) {
        scroe += 0;        
    } else if(/[A-Z]+/.test(pStr) && /[a-z]+/.test(pStr)){
        scroe += 20;                 
    } else if(/[A-Z]+|[a-z]+/.test(pStr)) {
        scroe += 10;                        
    }
    if(!/\d+/.test(pStr)){
        scroe += 0;
    } else if(/\d{2,}/.test(pStr)){
        scroe += 20;
    } else if(/\d/.test(pStr)){
        scroe += 10;
    }
    if(!/\W/g.test(pStr)){
        scroe += 0;                            
    } else if(/(\w*\W+){2,}\w*/g.test(pStr)){
        scroe += 25;                
    } else if(/\W{1}/g.test(pStr)){
        scroe += 10;                
    }
    if(/[a-z]/.test(pStr) && /[A-Z]/.test(pStr) && /\d/.test(pStr) && /\W/.test(pStr)){
        scroe += 5;
    } else if(/[A-z]/.test(pStr) && /\d/.test(pStr) && /\W/.test(pStr)){
        scroe += 3;
    } else if(/[A-z]\d|\d[A-z]/.test(pStr)){
        scroe += 2;
    }
    
    if(scroe >=0 ){
        $('.qiang').removeClass('stacur');
        $('.zhong').removeClass('stacur');
    	$('.ruo').addClass('stacur');
    }
    if(scroe >=50 ){
        $('.qiang').removeClass('stacur');
        $('.ruo').addClass('stacur');
        $('.zhong').addClass('stacur');
    }
    
    if(scroe >=90 ){
        $('.ruo').addClass('stacur');
        $('.zhong').addClass('stacur');
        $('.qiang').addClass('stacur');
    }
	
}