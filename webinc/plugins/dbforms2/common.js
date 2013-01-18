dbforms2_fBrowserField = null;
dbforms2_fBrowserLastLocation = '';

dbforms2_calendarCallback = null;
dbforms2_calendarField = null;

function dbforms2_common() {
}

dbforms2_common.serializeToString = function(dom) {
    var serializer = new XMLSerializer();
    return serializer.serializeToString(dom);
}

dbforms2_common.openFileBrowser = function(field) {
    var fBrowserUrl = bx_webroot + 'webinc/fck/editor/filemanager/browser/default/browser.html?Type=files&Connector=connectors/php/connector.php';

    var currentFile = field.getValue();

    if (currentFile === '' && dbforms2_fBrowserLastLocation) {
        currentFile = dbforms2_fBrowserLastLocation;
    }

    var filesDir = '/files';
    sParentFolderPath = currentFile.substring(filesDir.length, currentFile.lastIndexOf('/', currentFile.length - 2) + 1);

    if(sParentFolderPath != '' && (sParentFolderPath.indexOf('/') != -1))
        fBrowserUrl += '&RootPath=' + escape(sParentFolderPath);

    if(typeof fBrowserWindow != 'undefined' && !fBrowserWindow.closed) {
        fBrowserWindow.location.href = fBrowserUrl;
    } else {
        fBrowserWindow = window.open(fBrowserUrl, 'fBrowser', 'width=800,height=600,location=no,menubar=no');
    }

    fBrowserWindow.focus();

    dbforms2_fBrowserField = field;

    SetUrl = function(url) {
        if(dbforms2_fBrowserField != null) {
            var decodedUrl = decodeURI( url.replace('%23','#') );
            dbforms2_fBrowserField.setUrl( decodedUrl );
            dbforms2_fBrowserLastLocation = url;
        }
        dbforms2_fBrowserField = null;
    }

}

dbforms2_common.openCalendarPopup = function(field, id) {

    setDate = function(y, m, d) {
        dbforms2_calendarField.setDate(y, m, d);
        dbforms2_calendarCallback = null;
        dbforms2_calendarField = null;
    }

    dbforms2_calendarCallback = setDate;
    dbforms2_calendarField = field;
    cal.setReturnFunction('_calendarReturnFunction');
    cal.select(document.getElementById(id), 'anchor_' + id, 'dd.MM.yyyy');
}

function _calendarReturnFunction(y, m, d) {
    if(dbforms2_calendarCallback != null) {
        dbforms2_calendarCallback(y, m, d);
    }
}

/*
    This is a workaround because FCKeditor doesn't allow callbacks for the main OnComplete event.
    FCK always calls the global function 'FCKeditor_OnComplete' so we register the callback first
    and then launch it from here.
*/
function FCKeditor_OnComplete(einstance) {
    if(dbforms2_fckEditors[einstance.Name]) {
        var handler = dbforms2_fckEditors[einstance.Name];
        handler['method'].apply(handler['context'], [einstance]);

    }
}
