function addCommas(nStr) {
	nStr += '';
	var x = nStr.split('.');
	var x1 = x[0];
	var x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;

	while(rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}

	return x1 + x2;
}

function addIskCommas(nStr) {
	nStr += '';
	var x = nStr.split('.');
	var x1 = x[0];
	var x2 = x.length > 1 ? '.' + x[1] : '.00';
	var rgx = /(\d+)(\d{3})/;

	while(rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	if(x2.length<3) {
		x2=x2+'0';
	}
	return x1 + x2;
}

function rectime(secs) {
	var days = Math.floor(secs / 86400);
	if(days>0) {
		 secs=secs-(days*86400);
	}
	var hr = Math.floor(secs / 3600);
	var min = Math.floor((secs - (hr * 3600))/60);
	var sec = secs - (hr * 3600) - (min * 60);

	if(hr < 1) hr = '00';
	else if(hr < 10) hr = '0' + hr;
	if(min < 10) min = '0' + min;
	if(sec < 10) sec = '0' + sec;

	if(days>1) {
		hr= days+' days '+hr;
	} else if(days>0){
		hr= days+' day '+hr;
	}

	return hr + ':' + min + ':' + sec;
}

function createCookie(name, value, days) {
	if(days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = '; expires='+date.toGMTString();
	} else {
		var expires = '';
	}
	document.cookie = name+'='+value+expires+'; path=/';
}

function readCookie(name) {
	var nameEQ = name + '=';
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while(c.charAt(0)==' ') {
			c = c.substring(1,c.length);
		}
		if(c.indexOf(nameEQ) == 0) {
			return c.substring(nameEQ.length,c.length);
		}
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,'',-1);
}
