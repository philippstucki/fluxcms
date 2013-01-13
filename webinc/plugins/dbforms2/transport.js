function dbforms2_transport() {

    this.data = null;
    this.dataLoaded = false;
    this.dataSaved = false;
    this.onLoadCallback = null;
    this.onSaveCallback = null;

    this.loadXML = function(dataURI) {

        dbforms2_log.log('loading ' + dataURI + '...')
        this.dataLoaded = false;

        jQuery.ajax({
            url: dataURI,
            context: this,
            success: function(d, s, xhr) {
                this.dataLoaded = true;

                this.data = xhr.responseXML;

                // call document ready callback when the document has been loaded
                if (this.onLoadCallback != null && typeof this.onLoadCallback == "function")
                    this.onLoadCallback();
            }
        });

    }

    this.saveXML = function(dataURI, xml) {

        dbforms2_log.log('saving ' + dataURI + '...')
        this.dataSaved = false;

        jQuery.ajax({
            type: 'POST',
            url: dataURI,
            processData: false,
            contentType: 'text/xml',
            data: xml,
            context: this,
            success: function(d, s, xhr) {
                this.dataSaved = true;

                response = new dbforms2_response();
                response.setXML(xhr.responseXML);

                // call document ready callback when the document has been saved
                if (this.onSaveCallback != null && typeof this.onSaveCallback == "function")
                    this.onSaveCallback(response);
            }
        });

    }

    this.saveXMLSync = function(dataURI, xml) {
        this.data = new XMLHttpRequest();
        this.data.open('POST', dataURI, false);
        response = new dbforms2_response();

        try {
            this.data.send(xml);
            response.setXML(this.data.responseXML);
        } catch (e) {
            response.responseText = 'Unable to establish a connection to the server.';
        }

        return response;

    }

}

function dbforms2_response() {

    this.xml = null;
    this.responseCode = 20;
    this.responseText = '';
    this.savedID = 0;
    this.responseData = null;

    this.setXML = function(xml) {
        this.xml = xml;
        this.parseXML();
    }

    this.parseXML = function() {
        // parse response code and message
        this.responseCode = this.xml.documentElement.getAttribute('code');
        this.savedID = this.xml.documentElement.getAttribute('id');
        this.responseText = this.xml.documentElement.getElementsByTagName('text')[0].childNodes[0].data;

        // check if the server has returned new data
        var dataNS = this.xml.documentElement.getElementsByTagName('data');
        if(dataNS[0]) {
            var xml = Sarissa.getDomDocument('', 'data');
            if(typeof xml.importNode == 'function') {
                newRoot = xml.importNode(dataNS[0], true);
                xml.documentElement.appendChild(newRoot);
            } else {
                xml.documentElement.appendChild(dataNS[0]);
            }

            this.responseData = xml;
        }
    }

    this.isError = function() {
        if(this.responseCode != 0)
            return true;

        return false;
    }

    this.getResponseText = function() {
        return this.responseText;
    }

    this.getResponseCode = function() {
        return this.responseCode;
    }

}
