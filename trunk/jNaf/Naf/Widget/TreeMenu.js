if (! Naf.isLoaded('Naf.Widget.TreeMenu')) {
	Naf.Widget.TreeMenu = {
		build: function(i) {
			ul = i.getAttribute("naf:target")
			if ((! ul))
				return
			
			ul = $(ul)
			if (! ul.tagName || ul.tagName.toLowerCase() != 'ul')
				return
			
			// go through all list-items
			var liNodeList = ul.getElementsByTagName('li')
			for (var i = 0; i < liNodeList.length; ++i) {
				li = liNodeList.item(i)
		
				// create toggle-button
				var b = document.createElement('button')
				li.insertBefore(b, li.firstChild)
				var toggle = li.getAttribute("menu:toggle")
				if (toggle)
				{// activate toggle-button
					b.innerHTML = '+'
					Event.observe(b, 'click', function(e) {
						var b = Event.element(e)
						var m = $(b.parentNode.getAttribute("menu:toggle"))
						Element.toggle(m)
						if (Element.visible(m)) {
							b.innerHTML = '-'
						} else {
							b.innerHTML = '+'
						}
					}, false);
				} else {
					b.innerHTML = '&middot;'
				}
		
				if (li.getAttribute("menu:selected"))
				{
					var pnode = li
					do {
						pnode = pnode.parentNode
						var tag = pnode.tagName.toLowerCase()
						if ('ul' == tag)
							Element.show(pnode)
						else if ('li' == tag)
						{
							pnode.firstChild.innerHTML = '-'
							Element.addClassName(pnode, 'selected')
						}					
					} while (pnode != ul)
				} else if (toggle) {
					Element.hide(toggle)
				}
			}
		}
	}
	
	Naf.load('Naf.Widget.TreeMenu')
}