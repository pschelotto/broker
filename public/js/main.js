$(document).ready(function(){
	$('#change_environment select').change(function(ev){
		ev.stopPropagation();
		location.href=$(this).val();
	});

	$('#supermercados').on("select2:unselect", function (e) { 
		if(0==$('#supermercados').val().length)
			$('#supermercados').val(null).trigger("change");
	});

	$('#supermercados').change(f=function() { 
		setTimeout(function(){
			var h = $('nav.navbar').height()-40;
			$('section.content-header').css({'padding-top':`${h}px`}); 
		},100);
	});
	f();

});

function openWindowBottomRight(url,x,y) {
	var xMax = screen.width-18, yMax = screen.height-112;
	var xOffset = xMax - x, yOffset = yMax - y;
	msgWindow = window.open(url,'newWin','width='+x+',height='+y+',screenY='+yOffset+',screenX='+xOffset);
}       	

function getUrlParams()
{
	var params = '';
	if(search = location.search.match(/\?(.*)/))
		params = '"'+search[1].replace(/&/g, '", "').replace(/=/g, '":"')+'"';

	return JSON.parse('{'+params+'}');
}
