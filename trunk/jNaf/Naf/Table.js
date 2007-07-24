/**
 * Basic class for creating arbitrary item Naf.Tables (i. e. tables)
 */

if (! Naf.isLoaded('Naf.Table')) {
	/**
	 * JS helper for manipulating tables.
	 */
	Naf.Table = function(table) {
		this._table = $(table)
		this._cells = []
	}
	
	/**
	 * Add a cell to the table
	 * @param callback callback
	 * @param string cssClass
	 */
	Naf.Table.prototype.addCell = function(callback, cssClass) {
		this._cells.push({'callback': callback, 'cssClass': cssClass})
	}
	
	/**
	 * Draw the Naf.Table.
	 * @param ROWDATA[] rowset
	 */
	Naf.Table.prototype.draw = function(rowset) {
		for (var i = 0; i < rowset.length; ++i)
			this.row(rowset[i])
	}
	
	/**
	 * Draw single row
	 * @param ROWDATA data
	 */
	Naf.Table.prototype.row = function(data, replace) {
		if ('undefined' == typeof(replace)) replace = false
		var prev 
		if (replace)
			prev = this.kill(replace)
		else
			prev = null
		
		this.appendRow(this.createRow(data), prev)
	}
	
	Naf.Table.prototype.appendRow = function(row, prev)
	{
		if (prev == null)
			this._table.appendChild(row)
		else
			this._table.insertBefore(row, prev)
	}
	
	/**
	 * Create a table row
	 * @return HTMLTableRowElement
	 */
	Naf.Table.prototype.createRow = function(data)
	{
		var row = document.createElement('tr')
		for (var i = 0; i < this._cells.length; ++i)
			this._cell(row, this._cells[i].callback(data), this._cells[i].cssClass)
		
		return row
	}
	
	/**
	 * Remove a row from the table
	 * @return DOMNode nextSibling
	 */
	Naf.Table.prototype.kill = function(el) {
		el = $(el)
		if ((! el) || 'undefined' == typeof(el)) return;
		while ('tr' != el.tagName.toLowerCase())
			el = el.parentNode
		
		var next = el.nextSibling
		el.parentNode.removeChild(el)
		return next
	}
	Naf.Table.prototype._cell = function(row, content, className) {
		var td = row.appendChild(document.createElement('td'))
		td.innerHTML = content
		if ('undefined' != typeof(className))
			td.className = className

		return td
	}
	
	Naf.load('Naf.Table')
}