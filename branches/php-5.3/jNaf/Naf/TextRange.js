if (! Naf.isLoaded('Naf.TextRange'))
{
	Naf.TextRange = {
		paste: function(obj, text) {
			Naf.TextRange.transform(obj, function(){return text;})
		},
		toggleWrap: function(obj, open, close) {
			Naf.TextRange.transform(obj, function(selection) {
				var io = selection.indexOf(open)
				if (0 == io)
				{
					var ss = selection.substring(io + 1)
					var ic = ss.indexOf(close)
					if ((ss.length - close.length) == ic)
						return ss.substring(0, ic)
				}
				
				return open + selection + close;
			});
		},
		wrap: function(obj, open, close) {
			Naf.TextRange.transform(obj, function(selection){return open + selection + close;})
		},
		setCaretTo: function (obj, pos) {
			if(obj.createTextRange) {
				var range = obj.createTextRange();
				range.move('character', pos);
				range.select();
			} else if(obj.selectionStart) {
				obj.focus();
				obj.setSelectionRange(pos, pos);
			}
		},
		transform: function(obj, callback)
		{
			try {
				obj = $(obj)
				var text
				if(document.selection) {
					obj.focus();
					var orig = obj.value.replace(/\r\n/g, "\n");
					var range = document.selection.createRange();
		
					if(range.parentElement() != obj) {
						return false;
					}
		
					text = callback(range.text)
					range.text = text
					start = obj.value.indexOf(text)
					
				} else if('undefined' != typeof(obj.selectionStart)) {
					var start = obj.selectionStart;
					var end   = obj.selectionEnd;
					var length = end - start;
					
					text = callback(obj.value.substr(start, length))
					obj.value = obj.value.substr(0, start) 
						+ text 
						+ obj.value.substr(end, obj.value.length);
				}
				
				if(start != null) {
					Naf.TextRange.setCaretTo(obj, start + text.length);
				} else {
					obj.value += text;
				}
			} catch (e) {
				alert(e.message)
				return false
			}
		}
	}
}