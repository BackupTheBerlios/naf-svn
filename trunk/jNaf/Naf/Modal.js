/* Pseudo-modal window */
Naf.Modal = function(width, height) {
	var id = this.id = Naf.Modal.id++
	this.id = 'n_m_bg' + this.id
	Naf.Modal.instances.push(this.id)
	this.width = width
	this.height = height
	/* background - hide underlying elements */
	this.overall = document.createElement('div')
	this.overall.style.position = 'absolute'
	this.overall.style.width = '100%'
	this.overall.className = 'n_m_bg'
	this.overall.style.top = '0'
	this.overall.style.left = '0'
	this.overall.style.zIndex = 1000
	this.overall.style.margin = '0'
	this.overall.style.padding = '0'
	this.overall.setAttribute('id', this.id)
	
	Event.observe(this.overall, 'click', function(e) {
		Naf.Modal.close(this.getAttribute('id'));
	}, false);
//	this.overall.onclick = 'Naf.Modal.close("' + this.id + '")'
	/* let the user close this window */
	var closeButton = this.overall.appendChild(document.createElement('button'))
	closeButton.innerHTML = 'x'
	closeButton.style.cssFloat = 'right'
	closeButton.style.margin = '1em'
	Event.observe(closeButton, 'click', function(e) {
		Naf.Modal.close(this.parentNode.getAttribute('id'));
	}, false);
	/* Content area */
	this.content = document.createElement('div')
	this.content.style.width = width;
	this.content.style.height = height;
	this.content.style.zIndex = 1001
	this.content.style.margin = '1em auto'
	this.content.style.overflow = 'auto'
	this.content.className = 'n_m_c'
	this.content.innerHTML = 'Loading content...'
	Event.observe(this.content, 'click', function(e) {
		e.stopPropagation()
	}, false);
	
	this.overall.appendChild(this.content)
}
/* load URL into content area */
Naf.Modal.prototype.load = function(url) {
	new Ajax.Updater(this.content, url)
	return this
}
Naf.Modal.prototype.setHTML = function(html) {
	this.content.innerHTML = html
	return this
}
/* Display pseudo-modal window */
Naf.Modal.prototype.display = function() {
	document.body.appendChild(this.overall)
	this.content.innerHTML.evalScripts()
	Naf.Modal.trackResize()
	document.body.scrollTo(document.body.scrollLeft, 0)
}
/* Close current modal window */
Naf.Modal.close = function(id) {
	var is = []
	for (var i = 0; i < Naf.Modal.instances.length; ++i)
	{
		if (Naf.Modal.instances[i] == id)
			document.body.removeChild($(Naf.Modal.instances[i]))
		else
			is.push(Naf.Modal.instances[i])
	}
	Naf.Modal.instances = is
}
Naf.Modal.trackResize = function() {
	for (var i = 0; i < Naf.Modal.instances.length; ++i)
		$(Naf.Modal.instances[i]).style.height = document.body.scrollHeight + 'px'
}
Naf.Modal.id = 1
Naf.Modal.instances = []

/* Display help, load text from url */
Naf.help = function(url, width, height) {
	var m = new Naf.Modal(width, height)
	m.load(url).display()
}