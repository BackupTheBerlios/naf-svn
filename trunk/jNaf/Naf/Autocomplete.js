if (! Naf.isLoaded('Naf.Autocomplete'))
{
	Naf.Autocomplete = function(id, url) {
		this.id = Naf.Registry.put(this)
		this.input = $(id)
		this.input.setAttribute('aid', this.id)
		this.url = url
		
		this.container = document.createElement('div')
		this.container.className = 'naf_autocomplete'
		this.container.style.position = 'absolute'
		
		Element.hide(this.container)
		this.input.parentNode.insertBefore(this.container, this.input.nextSibling)
		
		Event.observe(this.input, 'keyup', this.offer)
	},
	Naf.Autocomplete.prototype.offer = function(e) {
		var ac = Naf.Autocomplete.instance(e)
		if (Event.KEY_ESC == e.keyCode) Element.hide(ac.container)
		ac.clearOffers()
		var v = ac.input.value.strip()
		if (v.length < 3) return
		new Ajax.Request(
			ac.url,
			{
				method:'post',
				parameters:{q:v},
				onSuccess:function(r) {
					try {
						eval('var json = ' + r.responseText)
						if ('error' == json.code)
						{
							alert("- " + json.error_list.join("\n- "))
						} else {
							var url
							for (var i = 0; i < json.data.length; ++i)
							{
								if ('url' in json.data[i])
									url = json.data[i].url
								else
									url = ''
								
								ac.scaleContainer()
								ac.container.innerHTML += '<a href="' + url + '">' + json.data[i].text + '</a>'
							}
							if (ac.container.hasChildNodes())
								Element.show(ac.container)
						}
					} catch (e) {
						alert(r.responseText)
						alert(e.message)
					}
				},
				onFailure:function(r) {
					alert('HTTP request failed!')
				}
			});
	}
	Naf.Autocomplete.prototype.clearOffers = function() {
		this.container.innerHTML = ''
	}
	Naf.Autocomplete.prototype.scaleContainer = function() {
		this.container.style.width = Element.getWidth(this.input) + 'px'
		var h = Element.getHeight(this.input)
		var p = Position.cumulativeOffset(this.input)
		this.container.style.left = p[0] + 'px'
		this.container.style.top = (p[1] + h) + 'px'
	}
	Naf.Autocomplete.instance = function(e) {
		return Naf.Registry.get(parseInt(Event.element(e).getAttribute('aid')))
	}
}