var tabulator = {
	tabs: {
		initialize: function() {
			// find our tab area
			var t = $('tabarea');

			// create the scrollbar
			t.scrollbar = new Scrollbar(t, $('tabcontrols'));

			// fetch the data we need to create initial tabs (in a failsafe way)
			var data;
			try {
				data = t.firstDescendant().innerHTML.evalJSON();
				data = (data.tabs) ? data.tabs : [];
			} catch (e) {
				data = [];
			}

			// setup the tab container
			t.update();	// clear the JSON stuff - we don't need it any longer
			t.appendChild(t.ol = new Element('ol'));

			// create initial tabs
			data.each(function(i) {
				tabulator.tabs.add(i.url, i.title, i.icon);
			});

			// we need to manually fix the tab frame height whenever the window
			// size changes
			Event.observe(window, 'resize', tabulator.tabs.sizeFrame);

			// and we may need to show/hide the tab controls
			Event.observe(window, 'resize', tabulator.tabs.updateControls);
		},

		// this stores the order in which tabs are accessed
		order: Array(),

		activeTab: false,

		add: function(src, title, icon) {
			var close = new Element('a', {
				'href': 'javascript:;',
				'class': 'close'
			});
			close.observe('click', function() {
				this.blur();
				tabulator.tabs.remove(this.parentNode);
			});
			close.appendChild(new Element('span').update('(close item)'));

			var tab=document.createElement('a');
			tab.href='javascript:;';
			tab.onclick=function() { this.blur(); tabulator.tabs.activate(this.parentNode); }
			tab.className='item';
			tab.appendChild(document.createElement('span'));
			tab.firstChild.className='itemcontent';
			tab.firstChild.appendChild(document.createElement('span'));
			tab.firstChild.firstChild.style.backgroundImage='url('+icon+')';
			tab.firstChild.firstChild.style.backgroundRepeat='no-repeat';
			tab.firstChild.appendChild(document.createTextNode(title));
			tab.appendChild(document.createElement('span'));
			tab.firstChild.nextSibling.className='s1';

			var li = new Element('li').addClassName('last');
			li.appendChild(tab);
			li.appendChild(close);

			li.tabframe = new Element('iframe', {'src': src, 'frameBorder': 0});	// the frameBorder property is needed to get rid of an additional IE-specific border - notice the capital 'B'

			$('tabarea').getElementsByTagName('ol')[0].appendChild(li);
			$('tabframes').appendChild(li.tabframe);

			var previous = li.previous('#tabarea ol li');
			if (previous) previous.removeClassName('last');

			this.activate(li);

			// adjust the container width and update controls
			this.updateControls();
		},

		remove: function(obj) {
			// remove this tab from the tab click order
			this.order = this.order.without(obj);

			// repair the last tab
			if (obj.hasClassName('last')) {
				var previous = obj.previous('#tabarea ol li');
				if (previous) previous.addClassName('last');
			}

			// remove the tab and the tab frame
			obj.tabframe.remove();
			obj.remove();

			// activate the previously viewed tab
			var tab = this.order.pop();
			if (tab) this.activate(tab);

			// adjust the container width and update controls
			this.updateControls();
		},

		activate: function(obj) {
			var t = $('tabarea');

			// deactivate all tabs
			t.ol.childElements().each(function(i) {
				i.removeClassName('active');
				i.tabframe.style.zIndex = '0';
			});

			// then activate the right one
			with (obj) {
				addClassName('active');
				tabframe.style.zIndex = '1';
				tabframe.focus();
			}
			this.activeTab = obj;

			// repair the frame height
			this.sizeFrame();

			// remember that we accessed this tab
			this.order.push(obj);

			// scroll into view
			this.scrollTabIntoView(obj);

			t.scrollbar.update();
		},

		scrollTabIntoView: function(tab) {
			var t = $('tabarea');
			if (tab.offsetLeft < t.scrollLeft) {
				// scroll left
				t.scrollLeft = tab.offsetLeft;
			} else if (tab.offsetLeft + tab.offsetWidth + 32 > t.scrollLeft + t.offsetWidth) {
				// scroll right
				t.scrollLeft = tab.offsetLeft + tab.offsetWidth + 32 - t.offsetWidth;
			}

			// scroll back if we got out of range
			if (t.scrollLeft > t.scrollWidth - t.offsetWidth) t.scrollLeft = t.scrollWidth - t.offsetWidth;
		},

		sizeFrame: function() {
			var t = tabulator.tabs.activeTab;
			if (t) {
				var activeFrame = t.tabframe;
				var frameHeight = document.viewport.getHeight() - activeFrame.cumulativeOffset().top;

				activeFrame.style.height = frameHeight + 'px';
			}
		},

		updateControls: function() {
			var t = $('tabarea');

			// calculate the width of our container and add 10px for the
			// rightmost edge
			var olWidth = t.ol.childElements().inject(0, function(acc, e) { return acc + e.getWidth() + 24; }) + 8;
			t.ol.style.width = olWidth + 'px';

			// see whether we need to show the tab controls
			var c = $('tabcontrols');
			if (t.scrollWidth > t.offsetWidth + c.offsetWidth) {
				c.removeClassName('hidden');
				t.style.marginRight = c.offsetWidth + 'px';
			} else {
				c.addClassName('hidden');
				t.style.marginRight = 0;
			}

			// scroll the active tab back into view
			var a = tabulator.tabs.activeTab;
			if (a) tabulator.tabs.scrollTabIntoView(a);
			t.scrollbar.update();
		}
	},

	search: {
		initialize: function() {
			// give the search bar focus
			$('search')['search'].focus();

			// set our submit handler
			$('search').observe('submit', tabulator.search.submitHandler);
		},

		submitHandler: function() {
			var searchString = this.search.value;
			var url = '';

			// check the search string
			if (/^www\./.test(searchString)) url = 'http://' + searchString;
			else if (/^(?:mod_|)((?!mod_)[a-z0-9][a-z0-9_]{2,})(?::(?:met_|)((?!met_)[a-zA-Z][a-zA-Z0-9_]*)|)$/.test(searchString)) url = 'index.php?mm=' + searchString;
			else url = searchString;

			tabulator.tabs.add(url, searchString);
		}
	}
}

Event.observe(document, 'dom:loaded', tabulator.tabs.initialize);
Event.observe(document, 'dom:loaded', tabulator.search.initialize);

var Scrollbar = Class.create({
	initialize: function(scrollable, container) {
		var div = new Element('div').addClassName('scrollbar');
		this.div = div;

		this.scrollable = scrollable;

		var slider = new Element('div');
		slider.appendChild(document.createElement('span'));

		div.appendChild(slider);
		this.slider = slider;

		this.update();

		container.appendChild(div);

		slider.move = this.mousemove.bindAsEventListener(this);
		slider.stop_capture_mouseup = this.stop_scroll.bindAsEventListener(slider);

		div.observe('mousedown', this.bar_click.bindAsEventListener(this));
		slider.observe('mousedown', this.slider_click.bindAsEventListener(this));

		// hover effect
		slider.observe('mouseover', function() { this.addClassName('hover'); });
		slider.observe('mouseout', function() { this.removeClassName('hover'); });

		scrollable.scrollLeft = 0;
	},

	/* update width and position of the slider. Meant to be called externally,
	 * e.g. when the content of the scrollable area got bigger)
	 */
	update: function() {
		// update the slider width
		var width = (this.div.clientWidth * this.scrollable.clientWidth / this.scrollable.scrollWidth).toFixed(0);

		// preserve a minimum width
		if(width < 37) width = 37;

		this.slider.style.width = width + 'px';

		// update the slider position
		this.slider.style.left = (this.div.clientWidth * this.scrollable.scrollLeft / this.scrollable.scrollWidth).toFixed(0) + 'px';
	},

	mousemove: function(event)
	{
		var xdiff = Event.pointerX(event) - this.slider.xstart;
		var x = (this.slider.offsetstart + xdiff) / (this.div.clientWidth - this.slider.clientWidth);

		this.scrollTo(x);

		Event.stop(event);
	},

	stop_scroll: function(event)
	{
		// hover effect
		this.removeClassName('scroll');

		Event.stopObserving(document, 'mousemove', this.move);
		Event.stopObserving(document, 'mouseup', this.stop_capture_mouseup);

		Event.stop(event);
	},

	/* handle clicks on the bar itself (besides the slider) */
	bar_click: function(event)
	{
		var x = Event.pointerX(event) - this.div.cumulativeOffset()[0];

		x -= this.slider.clientWidth / 2;
		xscroll = x / (this.div.clientWidth - this.slider.clientWidth);

		this.scrollTo(xscroll);

		Event.stop(event);
	},

	/* handle clicks on our slider */
	slider_click: function(event)
	{
		if(Event.isLeftClick(event))
		{
			var slider = this.slider;

			// hover effect
			slider.addClassName('scroll');

			slider.xstart = Event.pointerX(event);
			slider.offsetstart = slider.offsetLeft;

			Event.observe(document, 'mousemove', slider.move);
			Event.observe(document, 'mouseup', slider.stop_capture_mouseup);
		}
		Event.stop(event);
	},

	/* scroll to a certain offset (interval [0, 1]) */
	scrollTo: function(x)
	{
		if(x < 0) x = 0;
		if(x > 1) x = 1;

		// scroll our target
		this.scrollable.scrollLeft = (x * (this.scrollable.scrollWidth - this.scrollable.clientWidth)).toFixed(0);

		// place our scrollbar scroller
		this.slider.style.left = (x * (this.div.clientWidth - this.slider.clientWidth)).toFixed(0) + 'px';
	}
});
