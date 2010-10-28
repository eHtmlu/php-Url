
document.observe('dom:loaded',function(){
	$$('fieldset').each(function(f){
		var l=f.down('legend');
		var d=f.down('div');
		if (!l || !d) return;

		var o=function(){
			if (d.offsetHeight > 0)
				d.setStyle({height:'0'});
			else
				d.setStyle({height:''});
		};
		l.observe('click',o);
		o();
	});

	var radiochange=function(){
		var value=this.i.getValue();
		['true','false','inherit'].each(function(a){
			if (value == a)
				this.r.addClassName('setting-'+a);
			else
				this.r.removeClassName('setting-'+a);
		}.bind(this));
	};

	$$('.right').each(function(r){
		/*r.observe('mousedown',function(e){
			e.stop();

			document.observe('mousemove',function(){
				console.log(11);
			});
		});*/
		r.select('input,label').each(function(i){
			if (i.nodeName == 'input')
				i.observe('change',radiochange.bind({i:i,r:r}));
			else
				i.observe('click',radiochange.bind({i:i,r:r}));

			if (i.getAttribute('checked') != 'checked' && i.checked)
				radiochange.bind({i:i,r:r})();
		});
	});
});
