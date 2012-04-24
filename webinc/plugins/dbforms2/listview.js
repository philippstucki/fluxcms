function dbforms2_listview() {
    
    this.transport = new dbforms2_transport();
    this.onChooseAction = null;
    this.onDeleteAction = null;
    this.disabled = false;
    
    // set this to the data uri
    this.dataURI = '';

    this.init = function(resultsDOMNode) {
        this.results = new dbforms2_listview_results(resultsDOMNode, this);
    }
    
    this.loadEntries = function(arguments) {
        
        var uri =  this.dataURI;
        if(arguments !== undefined) {
            if(arguments['thatid'] != undefined && arguments['thatid'] != null && arguments['thatid'] != 0) {
                uri = uri+'?thatid=' + escape(arguments['thatid']);
            }
        }
        
        dbforms2_log.log('loading ' + uri + '...')
        this.dataLoaded = false;
        jQuery.ajax({
            url: uri,
            context: this,
            success: function(d, s, xhr) {
                this.data = xhr.responseXML;
                this.dataLoaded = true;
                this.loadEntriesFromXML();
            }
        });
        
    }

    this.loadEntriesFromXML = function() {
        var entry = this.data.documentElement.firstChild.firstChild;
        this.results.removeAllEntries();
        while(entry) {
            idNS = entry.getElementsByTagName('_id');
            titleNS = entry.getElementsByTagName('_title');
            
            title = 'Parse Error';
            id = 0;

            if(titleNS.length > 0 && titleNS.item(0).childNodes[0])
                title = titleNS.item(0).childNodes[0].data

            if(idNS.length > 0 && idNS.item(0).childNodes[0])
                id = idNS.item(0).childNodes[0].data;
            
            this.results.addEntry(id, title);
            
            entry = entry.nextSibling;
        }
    }

    this.onChoose = function(entry) {
        this.onChooseAction(entry);
    }
    
    this.onDelete = function(entry) {
        this.onDeleteAction(entry);
    }

    this.onUpdateOrder = function( entries ) {
        this.onUpdateOrderAction( entries );
    }
    
}

function dbforms2_listview_results(DOMNode, listview) {
    this.entries = new Array();

    this.DOMNode = DOMNode;
    this.listview = listview;
    
    this.hidden = true;
    
    this.show = function() {
    }
    
    this.hide = function() {
    }

    this.addEntry = function( id , title ) {
        var entry = new dbforms2_listview_entry();
        entry.id = id;
        entry.title = title;
        entry.results = this;

        var $values = $( this.DOMNode );
        var $li = $( '<li><img src="' + DBFORMS2_IMG_ROOT + 'delete.png" class="delete"><img src="' + DBFORMS2_IMG_ROOT + 'table_edit.png" class="edit">' + title + '</li>' );
        $li.attr( 'class' , 'n2mvalue' );
        $li.attr( '_value_id' , id );

        var that = this;
        entry.DOMNode = $li.get(0);

        $li.find('.delete').click( function () {
            that.listview.onDelete( entry );
        });
        
        $li.find('.edit').click( function () {
            that.listview.onChoose( entry );
        });

        $values.append($li);
        $values.sortable({
            'update' : function() {
                var values = new Array();
                $(that.DOMNode).find('li').each( function() {
                    values.push($(this).attr('_value_id'));
                });
                that.listview.onUpdateOrder( values );
            }
        });

    }
    
    this.removeEntry = function(entry) {
        this.DOMNode.removeChild(entry.DOMNode);
    }
    
    this.removeAllEntries = function() {
        while(this.DOMNode.hasChildNodes()) {
            this.DOMNode.removeChild(this.DOMNode.firstChild);
        }
        this.entries = new Array();
    }
    
    this.length = function() {
        return this.entries.length;
    }
    
}

function dbforms2_listview_entry() {
    var DOMNode = null;
    var id = 0;
    var title = '';
    var results = null;
    
    this.e_choose_onMouseDown = function() {
        this.results.listview.onChoose(this);
    }
    
    this.e_delete_onMouseDown = function() {
        this.results.listview.onDelete(this);
    }
    
}

