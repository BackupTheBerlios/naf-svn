/**
 * Much like dojo's TitlePane widget.
 */
if (! Naf.isLoaded('Naf.Widget.TitlePane')) {
	
	Naf.Widget.TitlePane = {
		build: function(d) {
			d.addClassName('naf_title_pane_content')
			var titleNode = document.createElement('div')
			titleNode.innerHTML = d.getAttribute('title')
			titleNode.className = 'naf_title_pane_header'
			
			titleNode.style.marginTop = d.getStyle('margin-top')
			d.style.marginTop = '0'
			
			Naf.Ajax.load(d)
			
			/* we're inside a meta-widget - accordeon */
			var a = d.getAttribute('accordeon')
			if (a)
			{
				if (! (a in Naf.Widget.TitlePane.accordeon))
				{
					Naf.Widget.TitlePane.accordeon[a] = []
				}
				Naf.Widget.TitlePane.accordeon[a].push(d)
			}

			Event.observe(titleNode, 'click', function(e) {
				if (! d.visible())
				{/* hide accordeon members */
					var a = d.getAttribute('accordeon')
					if (a)
					{
						for (var i = 0; i < Naf.Widget.TitlePane.accordeon[a].length; ++i)
						{
							var ad = Naf.Widget.TitlePane.accordeon[a][i]
							if ((d != ad) && ad.visible())
								Element.hide(ad)
						}
					}
				}
				d.toggle()
				setTimeout(function(){Naf.Ajax.load(d)}, 100)
			}, false)
			
			d.parentNode.insertBefore(titleNode, d)
		},
		accordeon: {}
	}
	
	Naf.load('Naf.Widget.TitlePane')
}