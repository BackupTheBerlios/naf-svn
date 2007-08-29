/**
 * Much like dojo's TitlePane widget.
 */
if (! Naf.isLoaded('Naf.Widget.TabSwitch')) {
	
	Naf.Widget.TabSwitch = {
		build: function(d) {
			
			d.addClassName('naf_tabswitch_container')
			
			var menu = document.createElement('ul')
			menu.className = 'horizontal'
			d.insertBefore(menu, d.firstChild)
			
			d.tabs = []
			
			for (var i = 0; i < d.childNodes.length; ++i)
			{
				var pane = d.childNodes.item(i)
				if (pane.tagName && ('undefined' != typeof(pane.tagName)) && 'div' == pane.tagName.toLowerCase())
				{
					Element.addClassName(pane, 'naf_tabswitch_tab_pane')
					var l = Naf.Widget.TabSwitch.getLabelNode(pane)
					var menuItem = menu.appendChild(document.createElement('li'))
					menuItem.innerHTML = l.innerHTML
					l.parentNode.removeChild(l)
					
					var index = d.tabs.length
					if (0 == index)
					{
						Element.addClassName(menuItem, 'first-child')
					}
					menuItem.setAttribute('naf_tab_index', index)
					
					d.tabs.push([menuItem, pane])
					
					Event.observe(menuItem, 'click', function(e) {
						Naf.Widget.TabSwitch.toggle(d, parseInt(Event.element(e).getAttribute('naf_tab_index')))
					}, false);
				}
			}
			var last = menu.appendChild(document.createElement('br'))
			last.style.clear = 'both'
			// make first-child active
			Naf.Widget.TabSwitch.toggle(d, 0)
			
		},
		getLabelNode: function(p) {
			for (var i = 0; i < p.childNodes.length; ++i)
			{
				var c = p.childNodes.item(i)
				if (c.tagName && 'label' == c.tagName.toLowerCase())
				{
					return c
				}
			}
			
			throw "No label specified!"
		},
		toggle: function(container, index) {
			for (var i = 0; i < container.tabs.length; ++i)
			{/* hide all tabs */
				Element.removeClassName(container.tabs[i][0], 'naf_tabswitch_tab_active')
				Element.hide(container.tabs[i][1])
			}
			/* show selected tab */
			Element.addClassName(container.tabs[index][0], 'naf_tabswitch_tab_active')
			Element.show(container.tabs[index][1])
			Naf.Ajax.load(container.tabs[index][1])
		}
	}
	Naf.load('Naf.Widget.TabSwitch')
}