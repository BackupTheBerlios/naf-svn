/**
 * Naf (Not A Framework javascript toolkit)
 * @see http://opensvn.scie.org/Naf
 */
Naf = {
	include: function(module) {
		if (Naf.isLoaded(module)) return
		while (module.indexOf('.') > 0) module = module.replace(/\./, "/")
		if (null == Naf.includeBase) Naf.includeBase = Naf._getIncludeBase()
		document.write('<sc'+'ript type="text/javascript" src="' + Naf.includeBase + '/' + module + '.js"></scr'+'ipt>')
//		while (! Naf.isLoaded(module))
	},
	modules: [],
	load: function(module) {
		if (! Naf.isLoaded(module)) Naf.modules.push(module)
	},
	isLoaded: function(module) {
		for (var i = 0; i < Naf.modules.length; ++i)
			if (Naf.modules[i] == module)
				return true
		
		return false
	},
	/* Registry - an object storage */
	Registry: {
		collection: [],
		put: function(obj) {
			id = Naf.Registry.collection.length
			Naf.Registry.collection.push(obj)
			return id
		},
		get: function(id) {
			if (id in Naf.Registry.collection)
				return Naf.Registry.collection[id]
			
			throw "Object not registered: " + id
		},
		drop: function(id) {
			if (id in Naf.Registry.collection)
				Naf.Registry.collection[id] = null
		}
	},
	currId: 0,
	nextId: function() {
		return ++Naf.currId
	},
	/* Trace object properties and methods */
	trace: function(obj) {
		o = []
		for (i in obj) o.push(i)
		alert(o.join(", "))
	},
	_getIncludeBase: function()
    {
        var scripts = document.getElementsByTagName("script");
        for (var i = 0; i < scripts.length; ++i)
        {
            if (scripts[i].src.indexOf("Naf.js") != -1)
            {
                var lastSlash = scripts[i].src.lastIndexOf("/");
                return scripts[i].src.substr(0, lastSlash);
            }
        }
    },
    includeBase: null,
    
    /**
     * Navigate to URL
     * @param string url
     */
    redirect: function(url) {
    	window.location.href = url
    },
    
    /**
     * Widgets? Widgets... Widgets!
     */
    
    Widget: {
    	all:['TitlePane', 'Calendar', 'TabSwitch']
    },
    widgetize: function() {
    	for (var i = 0; i < Naf.Widget.all.length; ++i)
    		Naf.include('Naf.Widget.' + Naf.Widget.all[i])

    	Event.observe(window, 'load', Naf.parseWidgets, false)
    },
    parseWidgets: function(nodeList) {

    	if (! nodeList.item)// suppose we're called as event handler
    		nodeList = document.getElementsByTagName("div")
    	
        for (var i = 0; i < nodeList.length; ++i)
        {
        	var d = nodeList.item(i)
            if (d.getAttribute('naf:widget') && ! d.getAttribute('naf:rendered'))
            {
            	d.setAttribute('naf:rendered', 1)
				var wName = 'Naf.Widget.' + d.getAttribute('naf:widget')
				try {
					eval('var w = ' + wName)
					Element.extend(d)
					w.build(d)
				} catch (e) {
					alert("parseWidgets: "+e.message+"\n"+wName)
				}
            }
        }
    },
    
    /**
     * Load HTML into <element> - with a help of XHR.
     * Apply necessary parsing also.
     */
    
    Ajax: {
    	load: function(element, src) {
    		if (arguments.length < 2)
    			src = element.getAttribute('src')
    		
    		if ((! src) || element.getAttribute('loaded') || ! element.visible()) return;
    		
    		var loading = element.appendChild(document.createElement('div'))
    		loading.className = 'loading'

    		element.setAttribute('loaded', '1')
			new Ajax.Request(src, {
				method:"get",
				onSuccess:function(r) {
					try {
						element.innerHTML = r.responseText
						Naf.parseWidgets(element.getElementsByTagName('div'))
						Naf.Form.ajaxifyAll(null, element.getElementsByTagName('form'))
					} catch (e) {
						alert(e.message)
					}
				},
				onFailure:function(r) {
					alert("Request failed")
				}
			});
    	}
    },
    
    /**
     * Extending HTML forms
     */
    
    Form: {
		/**
		 * Trigger form submit. Doesn't use submit() method, because if we did,
		 * no "submit" event would be dispatched.
		 */
		triggerSubmit: function (form)
		{
			Naf.Event.trigger('submit', $(form), false)
		},
		/**
		 * the name 'ajaxify' sux: we're not only ajaxifying,
		 * but also we validate the form.
		 */
		ajaxify: function(f)
		{
			if (! f.action.length)
				f.action = location.href.toString()
			
			Naf.Form.validate(f)
			Naf.Form.markRequired(f)

			Event.observe(f, 'submit', function(e) {
				
				if (! e)
					e = window.event

				Event.stop(e)
				
				if (! Naf.Form.validate(f, true))
				{
					return
				}
				
				Naf.Form.disableSubmission(f)
				
				/* do AJAX */
				Form.request(f, {
					onSuccess:function(r) {
						try {
							json = r.responseText.evalJSON()
							if ('error' == json.code)
							{
								Naf.Form.error(f, json.error_list)
							} else {
								Naf.Form.done(f, json.data)
							}
						} catch (e) {
							alert(e.message)
						}
						Naf.Form.enableSubmission(f)
					},
					onFailure:function(r) {
						Naf.Form.enableSubmission(f)
						Naf.Form.error(f, ['HTTP request failed!'])
					}
				});
				
				for (var i = 0; i < f.elements.length; ++i)
				{
					if (('input' == f.elements[i].tagName.toLowerCase()) && 
						('submit' == f.elements[i].getAttribute('type')))
					{
						f.elements[i].disabled = true
						f.elements[i].setAttribute('enable_me', '1')
					}
				}
				
			}, false);
		},
		ajaxifyAll: function(e, nodeList)
		{
			if (2 > arguments.length)
				nodeList = document.getElementsByTagName('form')
			
			for (var i = 0; i < nodeList.length; ++i)
			{
				var f = nodeList.item(i)
				if ('client' != f.getAttribute('runat'))
				{
					Naf.Form.ajaxify(f)
				}
				Naf.parseWidgets(f.getElementsByTagName('input'))
			}
		},
		validate: function(f, modal)
		{
			if (2 > arguments.length)
				modal = false
			
			return ! Naf.Form.walk(f, function(fe) {
				if (fe.getAttribute('required') || 
					fe.getAttribute('pattern') || 
					fe.getAttribute('date'))
				{
					Event.observe(fe, 'change', Naf.Form.pass, false)
					if (! Naf.Form.pass(null, fe) && modal)
					{
						alert(fe.getAttribute('title'))
						return true
					}
				}
			});
		},
		markRequired: function(f) {
			Naf.Form.walk(f, function(fe){
				if (fe.getAttribute('required'))
				{
					var s = document.createElement('span')
					s.className = 'required'
					s.innerHTML = "*"
					fe.parentNode.insertBefore(s, fe.nextSibling)
				}
			});
		},
		disableSubmission: function(f)
		{
			Naf.Form.walk(f, function(fe) {
				if (('input' == fe.tagName.toLowerCase()) && 
					('submit' == fe.getAttribute('type')))
				{
					Event.observe(fe, 'click', function(e) {
						var s = Event.element(e)
						var f = s.form
						for (var j = 0; j < f.elements.length; ++j)
						{
							if (('input' == f.elements[j].tagName.toLowerCase()) && 
								('submit' == f.elements[j].getAttribute('type')))
							{
								if (s == f.elements[j]) continue;
								f.elements[j].disabled = true
								f.elements[j].setAttribute('enable_me', '1')
							}
						}
					}, false);
				}
			});
		},
		walk: function(f, callback) {
			for (var i = 0; i < f.elements.length; ++i)
				if (callback(f.elements[i]))
					return true
			
			return false
		},
		done: function(f) {
			Naf.Form.removeErrorList()
			var callbackString = f.getAttribute('done')
			if (callbackString)
			{
				eval('var c = ' + callbackString + ';')
				c(json.data)
			}
			else
			{
				location.reload()
			}
		},
		/**
		 * @param FormNode f
		 * @param string[] list
		 */
		error: function(f, list)
		{
			Naf.Form.removeErrorList()
			var callbackString = f.getAttribute('error')
			if (callbackString)
			{
				eval('var c = ' + callbackString + ';')
				c(list)
			}
			else
			{
				var ul = document.createElement('ul')
				ul.setAttribute('id', 'displayErrorList')
				
				for (var i = 0; i < list.length; ++i)
				{
					var li = document.createElement('li')
					li.innerHTML = list[i]
					ul.appendChild(li)
				}
				
				f.parentNode.insertBefore(ul, f)
			}
		},
		removeErrorList: function() {
			var ul = $('displayErrorList')
			if (ul)
			{
				ul.parentNode.removeChild(ul)
			}
		},
		pass: function(e, element) {
			if (2 > arguments.length)// we're called as event handler
			{
				element = Event.element(e)
				var ret = true
			} else {
				var ret = false
			}
			
			if (element.getAttribute('required') && ! Naf.Form.passRequired(element))
			{
				return ret
			}
			if (element.getAttribute('pattern') && ! Naf.Form.passPattern(element)) {
				return ret
			}
			if (element.getAttribute('date') && ! Naf.Form.passDate(element)) {
				return ret
			}
			
			return true
		},
		passRequired: function(element) {
			if (Naf.Form.filled(element))
			{
				Element.removeClassName(element, 'invalid')
				element.removeAttribute('title')
				return true
			} else {
				Element.addClassName(element, 'invalid')
				element.setAttribute('title', Naf.Form.message(element, 'required', "Required field(s) left empty"))
				return false
			}
		},
		filled: function(element) {
			if (! element.value) return false
			if ('checkbox' == element.getAttribute('type'))
				return element.checked
			return null != element.value.match(/[^\s]/)
		},
		passPattern: function(element, pattern) {
			if (! Naf.Form.filled(element))
			{
				return true
			}
			
			if (2 > arguments.length)
				pattern = element.getAttribute('pattern')
			
			try {
				eval('var r = ' + pattern)
				if (! r.test)
					throw "Invalid pattern!"
			} catch (e) {
				return false
			}
			if (null == element.value.match(r))
			{
				Element.addClassName(element, 'invalid')
				element.setAttribute('title', Naf.Form.message(element, 'pattern', "Pattern match failed"))
				return false
			} else {
				Element.removeClassName(element, 'invalid')
				element.removeAttribute('title')
				return true
			}
		},
		passDate: function(element) {
			pattern = Naf.datePattern || '/^\\d{4}-\\d{2}-\\d{2}$/'
			if (! Naf.Form.passPattern(element, pattern))
			{
				Element.addClassName(element, 'invalid')
				element.setAttribute('title', Naf.Form.message(element, 'date', "Invalid date"))
				return false
			} else if (Naf.Form.filled(element)) {
				Element.removeClassName(element, 'invalid')
				element.removeAttribute('title')
				return true
			}
		},
		message: function(element, concrete, defaultString)
		{
			return element.getAttribute(concrete + '-message') || element.getAttribute('message') || defaultString
		},
		enableSubmission: function(f)
		{
			for (var i = 0; i < f.elements.length; ++i)
			{
				if (f.elements[i].hasAttribute('enable_me'))
				{
					f.elements[i].disabled = false
					f.elements[i].removeAttribute('enable_me')
				}
			}
		}
	},
	Event: {
		trigger: function(name, element, useCapture) {
			if (Prototype.Browser.IE)
			{
				element.fireEvent('on' + name)
			}
			else
			{
				var event = document.createEvent('HTMLEvents')
				event.initEvent(name, true, true);
				element.dispatchEvent(event);
			}
		}
	}
}

Event.observe(window, 'load', Naf.Form.ajaxifyAll, false)