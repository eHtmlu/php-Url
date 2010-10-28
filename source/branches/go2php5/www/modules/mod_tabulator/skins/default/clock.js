
function clock()
{
	var t=new Date();
	var h=t.getHours();
	var m=t.getMinutes();
	var s=t.getSeconds();

	// set digital
	$('clock_h').firstChild.firstChild.nodeValue=(h < 10 ? '0' : '')+h;
	$('clock_m').firstChild.firstChild.nodeValue=(m < 10 ? '0' : '')+m;
	$('clock_s').firstChild.firstChild.nodeValue=(s < 10 ? '0' : '')+s;

	// set analog
	if (h >= 12) h-=12;
	h*=5;
	h+=Math.floor(m/60*5);
	var h_pos=parseInt(-25-(h*25));
	var m_pos=parseInt(-25-(m*25));
	var s_pos=parseInt(-25-(s*25));
	$('clock_h').style.top=h_pos+'px';
	$('clock_m').style.top=m_pos+'px';
	$('clock_s').style.top=s_pos+'px';

//	$('dev').firstChild.nodeValue=h+':'+m+':'+s+' - '+h_pos+':'+m_pos+':'+s_pos;
}

window.setInterval('clock(); ', 1000);
