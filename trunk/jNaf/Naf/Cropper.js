/* Image-Cropper-util */
Naf.Cropper = function(src, container, size) {
	
	var id = Naf.Registry.put(this)
	
	/* The image top be cropped */
	this.img = document.createElement('img')
	this.img.src = src
	this.img.style.display = 'block'
	this.img.style.border = '1px solid silver'
	this.img.style.margin = '1em'
	
	this.scaleFactor = 1
	this.active = false

	/* selection-area */
	this.selection = document.createElement('div')
	this.selection.style.position = 'absolute'
	this.selection.style.border = '1px solid green'
	this.selection.style.display = 'none'
	
	this.selectionPos = null
	
	var ce = $(container)// Container Element
	this._drawTracker(ce)
	
	ce.appendChild(this.img)
	this.imgPos = Position.cumulativeOffset(this.img)
	
	this._applyScaleFactor(size)// scale the image down id needed
	
	ce.appendChild(this.selection)
	
	this._registerEventHandlers(id)
}

/* Private methods */

/* @param ce - Container Element */
Naf.Cropper.prototype._drawTracker = function(ce) {
	ce.innerHTML = '<input type="hidden" name="nis_top" id="nis_top" />'
	ce.innerHTML += '<input type="hidden" name="nis_left" id="nis_left" />'
	ce.innerHTML += '<input type="hidden" name="nis_width" id="nis_width" />'
	ce.innerHTML += '<input type="hidden" name="nis_height" id="nis_height" />'
	ce.innerHTML += '<br />'
	ce.innerHTML += '[ Selection : x=<span id="nis_topdisplay">0</span>, y=<span id="nis_leftdisplay">0</span>, '
	ce.innerHTML += 'w=<span id="nis_widthdisplay">0</span>, h=<span id="nis_heightdisplay">0</span> px ]'
	ce.innerHTML += '<br />'
}
/* Should the image appear larger than size, we scale it down and
	apply a scale-factor to coords */
Naf.Cropper.prototype._applyScaleFactor = function(size) {
	var imageSize = Element.getDimensions(this.img)
	if ((imageSize.width > size) || (imageSize.height > size))
	{
		if (imageSize.width > imageSize.height)
			this.scaleFactor = size/imageSize.width
		else
			this.scaleFactor = size/imageSize.height
		
		this.img.style.width = Math.round(this.scaleFactor * imageSize.width) + 'px'
		this.img.style.height = Math.round(this.scaleFactor * imageSize.height) + 'px'
	}
}
/* Get real size, with respest to scale-factor */
Naf.Cropper.prototype._realSize = function(scaledSize) {
	return Math.round(scaledSize / this.scaleFactor)
}
Naf.Cropper.prototype._registerEventHandlers = function(id) {
	/* Start selection */
	this.img.onmousedown = function(event) {
		c = Naf.Registry.get(id)
		c.active = true
		c.selectionPos = [Event.pointerX(event), Event.pointerY(event)]
		
		var x = c.selectionPos[0] - c.imgPos[0]
		var y = c.selectionPos[1] - c.imgPos[1]
		
		c.selection.style.left = c.selectionPos[0] + 'px'
		c.selection.style.top = c.selectionPos[1] + 'px'
		$('nis_top').value = c._realSize(y)
		$('nis_left').value = c._realSize(x)
		$('nis_topdisplay').innerHTML = c._realSize(y)
		$('nis_leftdisplay').innerHTML = c._realSize(x)
		c.img.onmousemove(event)
		Element.show(c.selection)
	}
	/* Transform selection */
	this.img.onmousemove = function(event) {
		c = Naf.Registry.get(id)
		if (! c.active) return;
		
		var w = (Event.pointerX(event) - c.selectionPos[0])
		var h = (Event.pointerY(event) - c.selectionPos[1])
		
		if ((w <= 0) || (h <= 0)) return
		
		c.selection.style.width = w + 'px'
		c.selection.style.height = h + 'px'
		$('nis_width').value = c._realSize(w)
		$('nis_height').value = c._realSize(h)
		$('nis_widthdisplay').innerHTML = c._realSize(w)
		$('nis_heightdisplay').innerHTML = c._realSize(h)
	}
	this.selection.onmousemove = this.img.onmousemove
	/* Finalize selection */
	this.img.onmouseup = function(event) {
		c = Naf.Registry.get(id)
		c.img.onmousemove(event)
		c.active = false
	}
	this.selection.onmouseup = this.img.onmouseup
}