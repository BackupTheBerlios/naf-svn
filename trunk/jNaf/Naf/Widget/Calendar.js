if (! Naf.isLoaded('Naf.Widget.Calendar')) {
	Naf.Widget.Calendar = {
		build: function(i) {
			
			if ('undefined' == typeof(window.Calendar))
			{
				alert('jscalendar not yet loaded')
				return
			}
			
			if (! i.getAttribute('id'))
				i.setAttribute('id', 'naf_calendar_input_' + Naf.nextId())
			
			switch (i.tagName.toLowerCase()) {
				case 'div' :
					Calendar.setup({
						flat: i.getAttribute('id'),
						flatCallback: eval('('+i.getAttribute('callback')+')')
					});
					break;
				case 'input':
					var btn = document.createElement('button')
					var btnId = 'naf_calendar_' + Naf.nextId()
					btn.setAttribute('id', btnId)
					btn.className = 'naf_calendar'
					i.parentNode.insertBefore(btn, i.nextSibling)
					Calendar.setup({
						inputField: i.getAttribute('id'),
						button: btnId
					});
					break;
				default:
					break;
			}
		}
	}
	
	Naf.load('Naf.Widget.Calendar')
}