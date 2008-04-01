/**
 * JS utility to manipulate trees in a HTML table
 * Sample usage:
 * <script language="JavaScript" type="text/javascript" src="/jslib/Naf/Table.js"></script>
 * <script language="JavaScript" type="text/javascript" src="/jslib/Naf/Table/Tree.js"></script>
 * <script language="JavaScript" type="text/javascript">
 * t = new Naf.Table.Tree('category_list', './server.php')
 * t.addCell(function(row) { return 'city' in row ? row.city : "--" })
 * t.draw([{"id": 1, "name": "Some name", "city": "New York"},{"id": 2, "name": "Another name", "city": "Los Angeles"}])
 * </script>
 * For every row to be drawn by Naf.Table.Tree, two elements are REQUIRED:
 * id - the unique ID for the row,
 * name - displayed name for the tree node.
 * Also, if the 'ntt_url' element is present in the row, then the node name renders as a 
 * hyperlink to that URL.
 */

if (! Naf.isLoaded('Naf.Table.Tree')) {
	Naf.include('Naf.Table')

	/**
	 * Constructor.
	 * @param string table <tbody> element #id
	 * @param string serverUrl
	 */
	Naf.Table.Tree = function(table, serverUrl) {
		this.table = new Naf.Table(table)
		this.id = $(table).getAttribute('id')
		Naf.Table.Tree.instances[this.id] = this
		this.serverUrl = serverUrl
		this.cache = {}
		this.init()
	}
	Naf.Table.Tree.prototype.init = function()
	{
		this.table.addCell(Naf.Table.Tree.drawName)
	}
	Naf.Table.Tree.instances = {}
	/**
	 * Callback for the cell containing node name and control button
	 * @return string
	 */
	Naf.Table.Tree.drawName = function(row) {
		if (Naf.Table.Tree.childCount(row))
			return '<input id="btn' + row.id + '" class="ntt_button" type="button" value="+" onclick="Naf.Table.Tree.instance(this).toggle(this)" />' + Naf.Table.Tree._name(row)
		else
			return Naf.Table.Tree._name(row)
	}
	/**
	 * @return Naf.Table.Tree
	 */
	Naf.Table.Tree.instance = function(el) {
		while (el && (el.tagName.toLowerCase() != 'tbody'))
			el = el.parentNode
		
		return Naf.Table.Tree.instances[el.getAttribute('id')]
	}
	/**
	 * Add a cell to the tree.
	 * @param callback callback
	 * @param string cssClass
	 */
	Naf.Table.Tree.prototype.addCell = function(callback, cssClass) {
		return this.table.addCell(callback, cssClass)
	}
	/**
	 * Draw a rowset
	 * @param row[] rowset
	 * @param int (optional) level
	 * @param HTMLTableRowElement(optional) parentRow
	 */
	Naf.Table.Tree.prototype.draw = function(rowset, level, parentRow) {
		if (arguments.length < 2) level = 0
		if (arguments.length < 3) prev = null
		else prev = parentRow.nextSibling
		var row
		for (var i = 0; i < rowset.length; ++i)
		{
			row = this.table.createRow(rowset[i])
			row.childNodes.item(0).style.paddingLeft = level + 'em'
			row.setAttribute('id', this.rowId(rowset[i].id))
			row.setAttribute('level', level)
			row.setAttribute('child_count', Naf.Table.Tree.childCount(rowset[i]))
			this.table.appendRow(row, prev)
		}
	}
	/**
	 * Generate a unique ID for row
	 * @return string
	 */
	Naf.Table.Tree.prototype.rowId = function(id) {
		return 'node_' + this.id + '_' + id
	}
	/**
	 * Toggle node's children visibility
	 * @param HTMLInputElement button the button that triggered toggle
	 */
	Naf.Table.Tree.prototype.toggle = function(button) {
		var row = button.parentNode.parentNode
		if (! parseInt(row.getAttribute('child_count')))
			return ;
		else if (row.getAttribute('expanded') && row.getAttribute('expanded').length)
			this.collapse(row, button)
		else
			this.expand(row, button)
	}
	/**
	 * Collapse node
	 */
	Naf.Table.Tree.prototype.collapse = function(row, button) {
		var id = row.getAttribute('id')
		if (id in this.cache)
		{
			var childRow
			for (var i = 0; i < this.cache[id].length; ++i)
			{
				childRow = $(this.rowId(this.cache[id][i].id))
				this.collapse(childRow, false)
				this.table.kill(childRow)
			}
		}
		if (button) button.value = '+'
		row.removeAttribute('expanded')
	}
	/**
	 * Expand node
	 */
	Naf.Table.Tree.prototype.expand = function(row, button) {
		var id = row.getAttribute('id'), level = parseInt(row.getAttribute('level')), children
		++level
		if (id in this.cache)
			children = this.cache[id]
		else
			return this.request(row, id, level, button)
		
		button.value = '-'
		row.setAttribute('expanded', 'yes')
		this.draw(children, level, row)
	}
	/**
	 * Request node's children from the ajax server
	 */
	Naf.Table.Tree.prototype.request = function(row, id, level, button) {
		var t = this
		new Ajax.Request(
			this.serverUrl,
			{
				method:'post',
				parameters:{parent: id},
				onSuccess:function(r) {
					try {
//						alert(r.responseText)
						eval('var json = ' + r.responseText)
						if ('error' == json.code)
						{
							alert("- " + json.error_list.join("\n- "))
						} else {

							button.value = '-'
							row.setAttribute('expanded', 'yes')	
							t.cache[id] = json.data
							t.draw(json.data, level, row)
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
	/**
	 * How many children does a row have?
	 * @return int
	 */
	Naf.Table.Tree.childCount = function(row) {
		return ('child_count' in row) ? parseInt(row.child_count) : 0
	}
	/**
	 * render the node's name
	 * @return string
	 */
	Naf.Table.Tree._name = function(row) {
		if ('ntt_url' in row)
			return '<a href="' + row.ntt_url + '">' + row.name + '</a>'
		else
			return row.name
	}
	
	Naf.load('Naf.Table.Tree')
}