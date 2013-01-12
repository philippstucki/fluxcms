
function editloaded() {

	var edit = document.getElementById('edit');

	edit.onload = editloaded;
	var loc = edit.contentWindow.location;
	if (loc.search) {
		var _params = loc.search.substring(1,loc.search.length).split("&");
		var params = new Array();
		for (var param in _params)
		{
			var p = _params[param].split("=");
			if (typeof p[1] != "undefined") {
				params[p[0]] = p[1];
			}
		}

		if (params['updateTree'] && params['updateTree'] != "parent") {
			var commonPath = "";
			var updateTree = params['updateTree'].split(";");
			if (updateTree.length > 1) {
				trees = new Array();
				for (var i in updateTree) {
					trees[i] = updateTree[i].split("/");
				}
				//if on same level (not sure about that, but
				// it should be enough).
				if (trees[0].length == trees[1].length) {
					for (var j in trees[0]) {
						if (trees[0][j] == trees[1][j]) {
							commonPath += trees[0][j]  +"/";
						} else {
							break;
						}
					}
				}


			}

			if (commonPath.length > 1) {
				window.navi.expandAndReload(commonPath);
				window.navi.Navitree.reload(commonPath);
			} else {
				for (var i in updateTree) {
					window.navi.expandAndReload(updateTree[i]);
					window.navi.Navitree.reload(updateTree[i]);
				}
			}
		}
	}


}

function framesetloaded() {
    $.get('i18n/js.xml', function (data, ts, jqxhr) {
        var i18n = new bx_i18n(data);
        i18n.init();
        window.i18n = i18n;
    });
}

function BX_debug(object)
{
    var win = window.open("","debug");
	bla = "";
    for (b in object)
    {

        bla += b;
        try {

            bla +=  ": "+object.eval(b) ;
        }
        catch(e)
        {
            bla += ": NOT EVALED";
        };
        bla += "\n";
    }
    win.document.innerHTML = "";

    win.document.writeln("<pre>");
    win.document.writeln(bla);
    win.document.writeln("<hr>");
}

