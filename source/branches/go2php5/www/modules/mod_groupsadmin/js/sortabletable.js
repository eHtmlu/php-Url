document.observe('dom:loaded',function(){
	$$('table.sortable').each(function(t){
		new sortabletable(t);
	});
});

var sortabletable=function(tableobj){
	tableobj.select('thead th').each(function(e,i){
		e.store('sortabletablecolindex', i);
		e.id=i;

		e.observe('mouseover',function(){ this.addClassName('mouseover'); });
		e.observe('mousedown',function(){ this.addClassName('mousedown'); });
		e.observe('mouseup',function(){ this.removeClassName('mousedown'); });
		e.observe('mouseout',function(){ this.removeClassName('mouseover'); this.removeClassName('mousedown'); });
		e.observe('click', function(e){
			var th=this.select('thead th');

			for (var a=0; th[a]; a++)
			{
				if (th[a] == e.element())
				{
					var i=a;
					if (th[a].hasClassName('downward'))
					{
						th[a].removeClassName('downward');
						th[a].addClassName('upward');
					}
					else
					{
						th[a].removeClassName('upward');
						th[a].addClassName('downward');
					}
					continue;
				}

				th[a].removeClassName('downward');
				th[a].removeClassName('upward');
			}

			var s1=this.select('tbody tr');
			var s2=s1.sortBy(function(s){
				var value=(s.select('td')[i].innerHTML).stripTags();
				if (value.search(/^\d+$/) == 0) value=parseInt(value);
				return value;
			}.bind(this));
			if ((e.element()).hasClassName('upward'))
				s2.reverse();

			var p=s1[0].parentNode;

			for (var a=0; s1[a]; a++) p.removeChild(s1[a]);
			for (var a=0; s2[a]; a++)
			{
				if (a%2 == 0) s2[a].removeClassName('second');
				else s2[a].addClassName('second');

				p.appendChild(s2[a]);
			}

		}.bind(tableobj));
	});
};
