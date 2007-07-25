/**
 * Naf (Not A Framework javascript toolkit)
 * @see http://opensvn.scie.org/Naf
 */
Naf = {
	include: function(module) {
		if (Naf.isLoaded(module)) return
		while (module.indexOf('.') > 0) module = module.replace(/\./, "/")
		document.write('<script type="text/javascript" src="' + Naf.includeBase + '/' + module + '.js"></script>')
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
    includeBase: Naf._getIncludeBase(),
}