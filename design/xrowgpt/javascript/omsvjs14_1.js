

if(typeof oms_site=='undefined'){oms_site=''};
if(typeof btcode=='undefined'){btcode=''};
if(typeof oms_zone=='undefined'){oms_zone=''};
if(typeof WLRCMD=='undefined'){WLRCMD=''};
var wsite=oms_site;
var ccat=btcode;
var oms_random=Math.floor(Math.random()*10000000000);

document.write('<scr'+'ipt src="https://oms.nuggad.net/rc?nuggn=1615459509&nuggtg='+encodeURIComponent(oms_zone)+'" type="text/javascript"><\/scr'+'ipt>');


ada=true;

if(ada==true){


document.write('<scr'+'ipt type="text/javascript" language="JavaScript" src="https://ad.yieldlab.net/yp/26865,26867,26869,26871?ts='+ oms_random +'"><\/scr'+'ipt>');



document.write('<scr'+'ipt class="kxct" data-id="Ip_eDfBc" data-timing="async" data-version="1.9" >');

try{

  window.Krux||((Krux=function(){Krux.q.push(arguments)}).q=[]);

  (function(){

    var k=document.createElement('script');k.type='text/javascript';k.async=true;

    var m,src=(m=location.href.match(/\bkxsrc=([^&]+)/))&&decodeURIComponent(m[1]);

    k.src = /^https?:\/\/([^\/]+\.)?krxd\.net(:\d{1,5})?\//i.test(src) ? src : src === "disable" ? "" :

      (location.protocol==="https:"?"https:":"http:")+"//cdn.krxd.net/controltag?confid=Ip_eDfBc";

    var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(k,s);

  }());
}
catch(err){}

document.write('<\/sc'+'ript>');


  
document.write('<scr'+'ipt class="kxint" >')

try{
  
  window.Krux||((Krux=function(){Krux.q.push(arguments);}).q=[]);

  (function(){

    

    // Namespace centro

    var Krux = this.Krux.adaudience = this.Krux.adaudience || {};

    

    function retrieve(n){

      var m, k='kxadaudience_'+n;

      if (window.localStorage) {

          return window.localStorage[k] || "";

      } else if (navigator.cookieEnabled) {

          m = document.cookie.match(k+'=([^;]*)');

          return (m && unescape(m[1])) || "";

      } else {

          return '';

      }

    }

 

    Krux.segments = retrieve('segs') ? retrieve('segs').split(',') : [];

    

    // DFP Premium – hier müssten dann die Variablen von Smart eingetragen werden

    var dfpp = [];

  

    for (var i = 0; i < Krux.segments.length; i++ ) {

      dfpp.push('ksg=' + Krux.segments[i]);

    }

    Krux.dfppKeyValues = dfpp.length ? dfpp.join(';') + ';' : '';

    

   

  })();
}
catch(err){};

document.write('<\/sc'+'ript>');


var crtg_nid = '1001';
var crtg_cookiename = 'crt_oms';
var crtg_varname = 'crtg_content';
function crtg_getCookie(c_name){ var i,x,y,ARRCookies=document.cookie.split(";");for(i=0;i<ARRCookies.length;i++){x=ARRCookies[i].substr(0,ARRCookies[i].indexOf("="));y=ARRCookies[i].substr(ARRCookies[i].indexOf("=")+1);x=x.replace(/^\s+|\s+$/g,"");if(x==c_name){return unescape(y);} }return'';}
var crtg_content = crtg_getCookie(crtg_cookiename);
var crtg_rnd=Math.floor(Math.random()*99999999999);
(function(){
var crtg_url=location.protocol+'//rtax.criteo.com/delivery/rta/rta.js?netId='+escape(crtg_nid);
crtg_url +='&cookieName='+escape(crtg_cookiename);
crtg_url +='&rnd='+crtg_rnd;
crtg_url +='&varName=' + escape(crtg_varname);
var crtg_script=document.createElement('script');crtg_script.type='text/javascript';crtg_script.src=crtg_url;crtg_script.async=true;
if(document.getElementsByTagName("head").length>0)document.getElementsByTagName("head")[0].appendChild(crtg_script);
else if(document.getElementsByTagName("body").length>0)document.getElementsByTagName("body")[0].appendChild(crtg_script);
})();


	

 


var rsi_segs = [];
var segs_beg=document.cookie.indexOf('rsi_segs=');
if (segs_beg>=0)
{
  segs_beg=document.cookie.indexOf('=',segs_beg)+1;
  if(segs_beg>0)
  {
    var segs_end=document.cookie.indexOf(';',segs_beg);
    if(segs_end==-1) segs_end=document.cookie.length;
    rsi_segs=document.cookie.substring(segs_beg,segs_end) .split('|');
  }
}
var segLen=35;
var segQS="";
if (rsi_segs.length<segLen)
{
  segLen=rsi_segs.length
}
for (var i=0;i<segLen;i++)
{
  segQS+=("rsi"+"="+rsi_segs[i]+";")
}

segQS+=Krux.adaudience.dfppKeyValues 

segQS = segQS.substr(0,200);
 
segQS+= ';';

otempstr=segQS;

segQS = otempstr.replace(";;", ";")  

}//ada









