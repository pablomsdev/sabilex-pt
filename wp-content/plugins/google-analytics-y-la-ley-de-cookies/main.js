if(ObtGAParams.idGA!==null && ObtGAParams.idGA!==""){
  var _gaq = _gaq || [];

  _gaq.push(['_setAccount', ObtGAParams.idGA]);

  _gaq.push(['_trackPageview']);
}


function obt_ga_activate(){
	obt_ga_createCookie('obtga_valid',1,365);
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
}

function obt_ga_createCookie(name, value, days) {
  var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}
function obt_ga_getCookie(c_name) {
    if (document.cookie.length > 0) {
        var c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
}

function obt_ga_showBanner(){
  console.log('Intrusivo ' + parseInt(ObtGAParams.intrusivo));

  obt_ga_createCookie('obtga_paso',0,null);

  if(parseInt(ObtGAParams.intrusivo)){
    var wrap = document.createElement("div");
    wrap.id = "obt_mask_overall";

    var box = document.createElement("div");
    box.className = "obt_ga_popup_intrusive obt_ga_popup_intrusive_centered";

    var h2 = document.createElement("h2");
    h2.innerHTML = ObtGAParams.titulo;

    var p = document.createElement("p");
    p.innerHTML= ObtGAParams.texto.replace('%button%','<a href="'+ObtGAParams.url+'">'+ObtGAParams.titulo_pagina+'</a>');

    var boton = document.createElement("div");
    boton.id="obt_ga_boton";
    boton.className = "btn_intrusive";
    boton.onclick = function(e){obt_ga_hideBanner();obt_ga_activate();};
    boton.innerHTML = ObtGAParams.acepto;

    box.appendChild(h2);
    box.appendChild(p);
    box.appendChild(boton);
    wrap.appendChild(box);

  }else{
    var wrap = document.createElement("div");
    while(document.getElementsByTagName("body")[0].childNodes.length>0)wrap.appendChild(document.getElementsByTagName("body")[0].childNodes[0]);
    var banner = document.createElement("div");
    banner.id = "obt_ga_banner";
    var contenido = document.createElement("div");
    contenido.id = "obt_ga_contenido";

    var boton = document.createElement("div");
    boton.id="obt_ga_boton";
    boton.onclick = function(e){obt_ga_hideBanner();obt_ga_activate();};
    boton.innerHTML = ObtGAParams.acepto;
    //Crear evento de boton de cierre
    
    var h2 = document.createElement("h2");
    h2.innerHTML = ObtGAParams.titulo;

    var p = document.createElement("p");
    p.innerHTML= ObtGAParams.texto.replace('%button%','<a href="'+ObtGAParams.url+'">'+ObtGAParams.titulo_pagina+'</a>');
    
    contenido.appendChild(h2);
    contenido.appendChild(p);
    banner.appendChild(contenido);
    banner.appendChild(boton);
    var clear = document.createElement("div");
    clear.id="obt_ga_clear";
    banner.appendChild(clear);
    document.getElementsByTagName("body")[0].appendChild(banner);
  }
  
  document.getElementsByTagName("body")[0].appendChild(wrap);  

}

function obt_ga_hideBanner(){
    if(parseInt(ObtGAParams.intrusivo)){
        document.getElementById("obt_mask_overall").parentElement.removeChild(document.getElementById("obt_mask_overall"));
    }else{
  	   document.getElementById("obt_ga_banner").parentElement.removeChild(document.getElementById("obt_ga_banner"));
    }
}

function obt_ga_comprobar(){
	if (window.opener && window.opener.closed) return;
	var uri=parseUri (document.location);
	var locationMainDomain = uri.authority.split('.');

	var rootDomain =locationMainDomain[locationMainDomain.length-2] + '.' +locationMainDomain[locationMainDomain.length-1];
  var availablesReferrerDomains=new Array(rootDomain); // List of availables referrer domains. By default the main domain is include


  Array.prototype.inArray = function (value){
    for (var i=0; i < this.length; i++){
      if (this[i] === value)
        return true;
    }
    return false;
  };

  if (document.referrer !== '' && parseInt(obt_ga_getCookie('obtga_paso'),10)===0 && document.URL!=ObtGAParams.url){
    parseUri.options.strictMode = true;
    var ref_uri=parseUri (document.referrer);
    var referrerMainDomain = ref_uri.authority.split('.');
    var rootReferrerDomain =referrerMainDomain[referrerMainDomain.length-2] + '.' +referrerMainDomain[referrerMainDomain.length-1];
    if( availablesReferrerDomains.inArray(rootReferrerDomain) ){
      obt_ga_activate();
    } else {
      obt_ga_showBanner();
    }
  }else {
    obt_ga_showBanner();
  }
}
parseUri.options = {
        strictMode: false,
        key: ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
        q:   {
                name:   "queryKey",
                parser: /(?:^|&)([^&=]*)=?([^&]*)/g
        },
        parser: {
                strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                loose:  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
        }
};
function parseUri (str) {
  var     o   = parseUri.options,
  m   = o.parser[o.strictMode ? "strict" : "loose"].exec(str),
  uri = {},
  i   = 14;

  while (i--) uri[o.key[i]] = m[i] || "";

  uri[o.q.name] = {};
  uri[o.key[12]].replace(o.q.parser, function ($0, $1, $2) {
    if ($1) uri[o.q.name][$1] = $2;
  });

  return uri;
}
function deleteAllCookies() {
    var cookies = document.cookie.split(";");

    for (var i = 0; i < cookies.length; i++) {
      var cookie = cookies[i];
      var eqPos = cookie.indexOf("=");
      var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
      document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
    }
}

window.onload = function(e){
  if(ObtGAParams.idGA!==null && ObtGAParams.idGA!==""){
    if(parseInt(obt_ga_getCookie('obtga_valid'),10)==1 || parseInt(obt_ga_getCookie('obtga_paso'),10)>=1){
      obt_ga_activate();
    }else{
      obt_ga_comprobar();
    }
  }
};


