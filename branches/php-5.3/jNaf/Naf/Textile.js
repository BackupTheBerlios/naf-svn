/* Simple editor for textile markup */
if (! Naf.isLoaded('Naf.Textile'))
{
	Naf.include('Naf.Modal')
	Naf.include('Naf.TextRange')
	/* Constructor
		@param string id textarea ID */
	Naf.Textile = function(id) {
		this.textarea = $(id)
		this.cssClass = null
		this.width = null
		this.height = null
		this.ctrlDiv = document.createElement('div')
		this.styles = []
		this.previewStylesheets = []
		this.imageListUrl = false
		this.imageList = []
		this.imcButton = this.imc = null
		this.delay = false
		this.delayed = false
		this.buttonList = []
		this.id = Naf.Registry.put(this)
		Naf.Textile.instances[id] = this
	}
	Naf.Textile.instances = {}
	Naf.Textile.prototype.setWidth = function(width) {
		this.width = width
		return this
	}
	Naf.Textile.prototype.setHeight = function(height) {
		this.height = height
		return this
	}
	Naf.Textile.prototype.addCssClass = function(className) {
		this.styles.push(className)
		return this
	}
	Naf.Textile.prototype.addImage = function(spec) {
		this.imageList.push(spec)
		return this
	}
	Naf.Textile.prototype.addButton = function(html, callback) {
		this.buttonList.push([html, callback])
		return this
	}
	/* Load image list from a externally specified URL */
	Naf.Textile.prototype.loadImageList = function() {
		this.imageList = []
		if (! this.imageListUrl) return
		var tx = this
		new Ajax.Request(
			this.imageListUrl,
			{
				method:'get',
				onSuccess:function(r) {
					try {
						eval('var json = ' + r.responseText)
						if ('error' == json.code)
						{
							alert("- " + json.error_list.join("\n- "))
						} else {
							for (var i = 0; i < json.data.length; ++i)
								tx.addImage(json.data[i])
							
							tx.drawImageList()
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
		return this
	}
	Naf.Textile.prototype.draw =  function() {
		
		if (null != this.cssClass)
			this.textarea.className = this.cssClass
			
		if (null != this.width)
			this.textarea.style.width = this.width + 'px'
		if (null != this.height)
			this.textarea.style.height = this.height + 'px'
	
		this.button('<strong>B</strong>', 'strong', this.id)
		this.button('<em>I</em>', 'italic', this.id)
		this.button('<u>U</u>', 'underline', this.id)
		
		this.imcButton = this.button('&lt;IMG&gt;', 'imagelist', this.id)
		this.drawImageList()
		
		for (var i = 0; i < this.buttonList.length; ++i)
			this.button(this.buttonList[i][0], this.buttonList[i][1], this.id)
		
		this.button('?', 'help', this.id)
		
		this.textarea.parentNode.insertBefore(this.ctrlDiv, this.textarea)
		
	}
	Naf.Textile.prototype.drawImageList = function() {
		
		if (this.imc != null)
		{
			var d = document.createElement('div')
			this.imc.parentNode.replaceChild(d, this.imc)
			this.imc = d
		} else {
			this.imc = this.textarea.parentNode.insertBefore(document.createElement('div'), this.textarea.nextSibling)
		}
		
		Element.hide(this.imc)
		
		this.textarea.style.cssFloat = 'left'
		
		this.imc.style.width = '250px'
		this.imc.style.fontSize = 'x-small'
		this.imc.style.overflow = 'auto'
		this.imc.style.cssFloat = 'left'
		this.imc.style.border = '1px outset threedface'
		this.imc.style.background = 'threedface'
		this.imc.style.textAlign = 'left'
		
		var tid = this.textarea.getAttribute('id')
		var id = tid + 'il'
		this.imc.setAttribute('id', id)
		
		ul = this.imc.appendChild(document.createElement('ul'))
		for (var i = 0; i < this.imageList.length; ++i)
		{
			var item = this.imageList[i]
			var li = ul.appendChild(document.createElement('li'))
			li.innerHTML = item.name + ' &rarr; '
			var bs = []
			if ('thumbnail' in item)
				bs.push(this._insertImageLink('T', item.thumbnail, item.full))
			if ('preview' in item)
				bs.push(this._insertImageLink('P', item.preview, item.full))
			
			bs.push(this._insertImageLink('F', item.full))
			
			li.innerHTML += '<br />' + bs.join(' | ')
		}
		
	}
	Naf.Textile.prototype.button = function(text, callback, id) {
		if (null == this.ctrlDiv) {
			return
		}
		var b = this.ctrlDiv.appendChild(document.createElement('button'))
		Event.observe(b, 'click', function(e) {
			eval('Naf.Textile.' + callback + '(' + id + ')')
			Event.stop(e)
			return false
		}, false);
		b.innerHTML = text
		return b
	}
	Naf.Textile.prototype.delimiter = function() {
		this.ctrlDiv.appendChild(document.createTextNode('|'))
	}
	
	Naf.Textile.strong = function(id) {
		Naf.TextRange.toggleWrap(Naf.Registry.get(id).textarea, '*', '*')
	}
	Naf.Textile.italic = function(id) {
		Naf.TextRange.toggleWrap(Naf.Registry.get(id).textarea, '_', '_')
	}
	Naf.Textile.underline = function(id) {
		Naf.TextRange.toggleWrap(Naf.Registry.get(id).textarea, '__', '__')
	}
	Naf.Textile.imagelist = function(id) {
		Element.toggle(Naf.Registry.get(id).textarea.getAttribute('id') + 'il')
	}
	Naf.Textile.insertimage = function(id, src, fullsrc) {
		var t = "!" + src + "!"
		if ((arguments.length > 2) && fullsrc != src)
			t += ":" + fullsrc
		
		var ta = Naf.Registry.get(id).textarea
		Naf.TextRange.paste(ta, t)
		Element.hide(ta.getAttribute('id') + 'il')
	}
	Naf.Textile.help = function(id) {
		var m = new Naf.Modal('20em', '45em')
		var html = '<h3>Phrase modifiers:</h3>'
		html += '<p>'
		html += '<em>_emphasis_</em><br />'
		html += '<strong>*strong*</strong><br />'
		html += '<i>__italic__</i></br />'
		html += '<b>**bold**</b></br />'
		html += '<cite>??citation??</cite><br />'
		html += '-<del>deleted text</del>-<br />'
		html += '+<ins>inserted text</ins>+<br />'
		html += '^<sup>superscript</sup>^<br />'
		html += '~<sub>subscript</sub>~<br />'
		html += '<span>%span%</span><br />'
		html += '<code>@code@</code><br />'
		html += '</p>'
		html += '<h3>Block modifiers:</h3>'
		html += '<p>'
		html += '<b>h<i>n</i>.</b> heading<br />'
		html += '<b>bq.</b> Blockquote<br />'
		html += '<b>fn<i>n</i>.</b> Footnote<br />'
		html += '<b>p.</b> Paragraph<br />'
		html += '<b>bc.</b> Block code<br />'
		html += '<b>pre.</b> Pre-formatted<br />'
		html += '<b>#</b> Numeric list<br />'
		html += '<b>*</b> Bulleted list<br />'
		html += '</p>'
		html += '<h3>Links:</h3>'
		html += '<p>'
		html += '"linktext":http://&#8230;<br />'
		html += '</p>'
		html += '<h3>Punctuation:</h3>'
		html += '<p>'
		html += '<b>"quotes"</b> &rarr; &#8220;quotes&#8221;<br />'
		html += '<b>\'quotes\'</b> &rarr; &#8216;quotes&#8217;<br />'
		html += '<b>it\'s</b> &rarr; it&#8217;s<br />'
		html += '<b>em -- dash</b> &rarr; em &#8212; dash<br />'
		html += '<b>en - dash</b> &rarr; en &#8211; dash<br />'
		html += '<b>2 x 4</b> &rarr; 2 &#215; 4<br />'
		html += '<b>foo(tm)</b> &rarr; foo&#8482;<br />'
		html += '<b>foo(r)</b> &rarr; foo&#174;<br />'
		html += '<b>foo(c)</b> &rarr; foo&#169;<br />'
		html += '</p>'
		html += '<h3>Attributes:</h3>'
		html += '<p>'
		html += '(class)<br />'
		html += '(#id)<br />'
		html += '{style}<br />'
		html += '[language]<br />'
		html += '</p>'
		html += '<h3>Alignment:</h3>'
		html += '<p>'
		html += '&gt; right<br />'
		html += '&lt; left<br />'
		html += '= center<br />'
		html += '&lt;&gt; justify<br />'
		html += '</p>'
		html += '<h3>Tables:</h3>'
		html += '<p>'
		html += '|_. a|_. table|_. header|<br />'
		html += '|a|table|row|<br />'
		html += '|a|table|row|<br />'
		html += '</p>'
		html += '<h3>Images:</h3>'
		html += '<p>'
		html += '!imageurl!<br />'
		html += '!imageurl!:http://&#8230;<br />'
		html += '</p>'
		html += '<h3>Acronyms:</h3>'
		html += '<p>'
		html += 'ABC(Always Be Closing)<br />'
		html += '</p>'
		html += '<h3>Footnotes:</h3>'
		html += '<p>'
		html += 'See foo[<i>1</i>].<br />'
		html += '<br />'
		html += 'fn1. Foo.<br />'
		html += '</p>'
		html += '<h3>Raw HTML:</h3>'
		html += '<p>'
		html += '==no &lt;b&gt;textile&lt;/b&gt;==<br />'
		html += '<br />'
		html += 'notextile. no &lt;b&gt;textile<br />'
		html += 'here&lt;/b&gt;<br />'
		html += '</p>'
		html += '<h3>Extended blocks:</h3>'
		html += '<p>'
		html += 'bq.. quote<br />'
		html += '&nbsp;<br />'
		html += 'continued quote<br />'
		html += '&nbsp;<br />'
		html += 'p. paragraph<br />'
		html += '</p>'
		html += '<h3>About Textile</h3>'
		html += '<p>Textile takes plain text with *simple* markup and produces valid XHTML.  It\'s used in web applications, content management systems, blogging software and online forums.  Try it for yourself with the Textile quick reference and preview.</p>'
		html += '<h3>Get Textile</h3>'
		html += '<p>Download Textile 2.0.0:</p>'
		html += '<p><a href="http://textile.thresholdstate.com/file_download/2/textile-2.0.0.tar.gz&#38;">'
		html += 'textile-2.0.0.tar.gz'
		html += '</a>'
		html += '<br />'
		html += '<a href="http://textile.thresholdstate.com/file_download/1/textile-2.0.0.zip">'
		html += 'textile-2.0.0.zip'
		html += '</a>'
		html += '<br /></p>'
		m.setHTML(html).display()
	}
	Naf.Textile.prototype._insertImageLink = function(name, src, fullsrc) {
		var argstr = "'" + this.id + "', '" + src + "'"
		if (arguments.length > 2)
			argstr += ", '" + fullsrc + "'"
		return '<a href="" onclick="Naf.Textile.insertimage(' + argstr + '); Element.hide(\'' + this.textarea.getAttribute('id') + 'il\'); return false;">&lt;' + name + '&gt;</a>'
	}
	Naf.load('Naf.Textile')
}