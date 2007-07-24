/**
 * Simple JavaScript pager.
 * Usage:
 * var p = new Pager('pagerContainerId', totalRows)
 * p.setPageSize(pageSize).setPageNumber(pageNumber).draw()
 */
if (! Naf.isLoaded('Naf.Pager')) {
	Naf.Pager = function(container, totalRows, callback) {
		this.container = []
		var containerType = typeof(container)
		if (('string' == containerType.toLowerCase()) || 
			('undefined' == typeof(container.length)))
		{
			this.container.push(container)
		}
		else
		{
			for (var i = 0; i < container.length; ++i)
			{
				this.container.push(container[i])
			}
		}
		this.html = ''
		this.setTotalRows(totalRows)
		this.setCallback(callback)
		this.setPageSize(20)
		this.setPageNumber(1)
		this.index = Naf.Pager.instances.length
		this.pageWindow = 10
		Naf.Pager.instances[this.index] = this
	}
	
	/**
	 * Pager instances storage
	 */
	Naf.Pager.instances = []
	
	/**
	 * Localization
	 */
	Naf.Pager.statusFormat = '<span class="info">[ Rows {FIRST} - {LAST} of {TOTAL} ]</span>'
	
	/**
	 * @param int totalRows
	 */
	Naf.Pager.prototype.setTotalRows = function(totalRows) {
		this.totalRows = totalRows
		return this
	}
	
	/**
	 * @param function-reference callback
	 */
	Naf.Pager.prototype.setCallback = function(callback) {
		if ('undefined' == typeof(callback))
			this.callback = Naf.Pager.defaultCallback
		else
			this.callback = callback
		
		return this
	}
	
	/**
	 * @param int pageNumber
	 */
	Naf.Pager.prototype.setPageNumber = function(pageNumber) {
		if (('undefined' == typeof(pageNumber)) || ! pageNumber)
			pageNumber = 1
		
		this.pageNumber = pageNumber
		return this
	}
	
	/**
	 * WARNING: Alwayse be sure to call pagerInstance.setPageSize() BEFORE calling pagerInstance.setPageNumber() !!!
	 * @param int pageSize
	 */
	Naf.Pager.prototype.setPageSize = function(pageSize) {
		this.pageSize = pageSize
		return this
	}
	
	/**
	 * Are we on te very first page?
	 */
	Naf.Pager.prototype.first = function() {
		return 1 == this.pageNumber
	}
	
	/**
	 * Are we on te very last page?
	 */
	Naf.Pager.prototype.last = function() {
		return this.pageNumber == this.totalPages
	}
	
	/**
	 * Draw pager
	 */
	Naf.Pager.prototype.draw = function() {
		
		this.html = ''
		
		this.totalPages = Math.ceil(this.totalRows/this.pageSize)
		
		if (this.totalPages > 1)
		{
			this.drawElipses()
		
			this.drawStatus()
			
			for (var i = 0; i < this.container.length; ++i)
			{
				$(this.container[i]).innerHTML = this.html
			}
		}
	}
	
	Naf.Pager.prototype.drawElipses = function() {
		if (this.first())
			this.drawElipse('<span class="first">&laquo;&laquo;</span>')
		else
			this.drawElipse('<a class="first" href="" onclick="Naf.Pager.call(' + this.index + ', ' + (this.pageNumber - 1) + '); return false;">&laquo;&laquo;</a>')
		
		var start, end
		if (this.pageWindow > 0)
		{
			start = (Math.floor((this.pageNumber - 1)/ this.pageWindow) * this.pageWindow) + 1
			end = start + this.pageWindow - 1
			if (end > this.totalPages)
			{
				end = this.totalPages
			}
		}
		else
		{
			start = 1
			end = this.totalPages
		}
		
		for (var i = start; i <= end; ++i) {
			if (i == this.pageNumber)
				this.drawElipse('<span class="selected">' + i + '</span>')
			else
				this.drawElipse('<a href="" onclick="Naf.Pager.call(' + this.index + ', ' + i + '); return false;">' + i + '</a>')
		}
		
		if (this.last())
			this.drawElipse('<span class="first">&raquo;&raquo;</span>')
		else
			this.drawElipse('<a class="last" href="" onclick="Naf.Pager.call(' + this.index + ', ' + (this.pageNumber + 1) + '); return false;">&raquo;&raquo;</a>')
	}
	
	Naf.Pager.prototype.drawStatus = function() {
		var firstRowNumber = ((this.pageNumber - 1) * this.pageSize) + 1
		var lastRowNumber = this.pageNumber * this.pageSize
		if (lastRowNumber > this.totalRows)
			lastRowNumber = this.totalRows
		
		var status = Naf.Pager.statusFormat.replace(/\{FIRST\}/, firstRowNumber)
			.replace(/\{LAST\}/, lastRowNumber)
			.replace(/\{TOTAL\}/, this.totalRows);
		this.drawElipse(status)
	}
	
	Naf.Pager.prototype.drawElipse = function(html) {
		this.html += '<span class="pager_elipse">' + html + '</span>'
	}
	
	/**
	 * Call a function with page as argument.
	 * This function doesn't need to be called externally.
	 * @param int index Pager instance index in storage (Pager.instances)
	 * @param int page
	 */
	Naf.Pager.call = function(index, page) {
		Naf.Pager.instances[index].callback.call(Naf.Pager.instances[index], page)
	}
	
	/**
	 * Default callback: goto URL containing a "page" parameter.
	 * @param int page
	 */
	Naf.Pager.defaultCallback = function(page) {
		var l = location.toString()
		var append = 'page=' + page
		if (l.match(/(\?|&(amp;)?)?page=(\d+)?/))
		{
			l = l.replace(/(\?|&(amp;)?)page=(\d+)?/, "$1"+append)
		}
		else
		{
			if (l.match(/\?/))
			{
				l += '&' + append
			}
			else
			{
				l += '?' + append
			}
		}
		window.location.href  = l
	}
}