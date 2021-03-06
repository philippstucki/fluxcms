/**
 * The parent class for all fields
 *
 * Defines all required methods and members and provides a common set of functionality.
 *
 */
function dbforms2_field(DOMNode) {
    var defaultValue = '';
    var id = '';
    var form = null;
    var changed = false;
    var disabled = false;

    this.initField = function(DOMNode) {
        this.hasFocus = false;
        this.value = null;
        this.DOMNode = DOMNode;
        this.resetChanged();
    }

    this.init = function(DOMNode) {
        this.initField(DOMNode);
    }

    this.setValue = function(value) {
        this.value = value;
        this.updateDOMNodeValue();
    }

    this.updateDOMNodeValue = function() {
        this.DOMNode.value = this.value;
    }


    this.getValue = function() {
        return this.DOMNode.value;
    }

    this.resetValue = function() {
        this.setValue(this.defaultValue);
    }

    this.isValid = function() {
        return true;
    }

    this.enable = function() {
        if(this.disabled == false) {
            this.DOMNode.disabled = false;
        }
    }

    this.disable = function() {
        this.DOMNode.disabled = true;
    }

    this.show = function() {
        this.DOMNode.style.visibility = 'visible';
    }

    this.hide = function() {
        this.DOMNode.style.visibility = 'hidden';
    }

    this.focus = function() {
        try {
            this.DOMNode.focus();
        } catch (e) {
            //if we can't set the focus, we don't care
        }
    }

    this.onChange = function() {
        this.changed = true;
    }

    this.hasChanged = function() {
        return this.changed;
    }

    this.resetChanged = function() {
        this.changed = false;
    }

    this.setParentFormId = function() {
    }

    /**
     * e_XXX methods are called from onXXX handlers from a HTML element
     *
     */
    this.e_onFocus = function() {
        jQuery(this.DOMNode).addClass('hasfocus');
        this.hasFocus = true;
        this.form.updateFocus(this);
    }

    this.e_onBlur = function() {
        jQuery(this.DOMNode).removeClass('hasfocus');
        this.hasFocus = false;
    }

    this.e_onMouseOver = function() {
        jQuery(this.DOMNode).addClass('hasfocus');
    }

    this.e_onMouseOut = function() {
        if(!this.hasFocus)
            jQuery(this.DOMNode).removeClass('hasfocus');
    }

    this.e_onChange = function() {
        dbforms2_log.log(this.id + '.onChange()');
        this.onChange();
    }

}

function dbforms2_field_hidden(DOMNode) {
}
dbforms2_field_hidden.prototype = new dbforms2_field();


/**
 * Simple text field
 *
 */
function dbforms2_field_text(DOMNode) {
    this.init = function(DOMNode) {
        this.initField(DOMNode);
        this.DOMNode.setAttribute('autocomplete', 'off');
    }
}
dbforms2_field_text.prototype = new dbforms2_field();



/**
 * Field for editing passwords
 *
 */
function dbforms2_field_password(DOMNode) {
}
dbforms2_field_password.prototype = new dbforms2_field();



/**
 * Fields for MD5-hashed passwords
 *
 */
function dbforms2_field_password_md5(DOMNode) {
}
dbforms2_field_password_md5.prototype = new dbforms2_field();



/**
 * A simple checkbox field
 *
 */
function dbforms2_field_checkbox(DOMNode) {

    this.setValue = function(value) {
        if(value == 1) {
            this.DOMNode.checked = true;
        } else {
            this.DOMNode.checked = false;
        }
    }

    this.getValue = function() {
        if(this.DOMNode.checked == true)
            return 1;

        return 0;
    }

}
dbforms2_field_checkbox.prototype = new dbforms2_field();



/**
 * Field for selecting a color
 *
 */
function dbforms2_field_color(DOMNode) {

    this.init = function(DOMNode) {
        this.initField(DOMNode);
        this.previewDOMNode = document.getElementById('anchor_' + this.DOMNode.id);
    }

    this.updateColorPreview = function() {
        var color = this.value;
        if(color == null || color == '') {
            color = 'ffffff';
        }
		this.previewDOMNode.style.backgroundColor = '#' + color;
    }

    this.setValue = function(value) {
        this.value = value;
        this.updateDOMNodeValue();
        this.updateColorPreview();
    }

    this.onChange = function() {
        this.changed = true;
        this.updateColorPreview();
    }

}
dbforms2_field_color.prototype = new dbforms2_field();



/**
 * Field for text areas
 *
 */
function dbforms2_field_text_area(DOMNode) {
}
dbforms2_field_text_area.prototype = new dbforms2_field();



/**
 * Field for editing text in fck
 *
 */
function dbforms2_field_text_wysiwyg(DOMNode) {

    var editorInstance = null;
    var height = 0;
    var fckId = null;
    var delayedSetValue;

    this.init = function(DOMNode) {
		this.initField(DOMNode);

        this.fckId = this.form.name+'_'+this.id;

        dbforms2_fckEditors[this.fckId] = {
            context : this,
            method : this.eFCK_OnComplete
        };

        var oFCKeditor = new FCKeditor(this.fckId);
        oFCKeditor.BasePath	= fckBasePath;

        if(DOMNode.hasAttribute && DOMNode.hasAttribute('height') && DOMNode.getAttribute('height') !== '' && DOMNode.getAttribute('height') !== '0') {
            this.height = DOMNode.getAttribute('height');
        }

        if(this.height !== 0) {
            oFCKeditor.Height = this.height;
        }

        oFCKeditor.Config['CustomConfigurationsPath'] = bx_webroot + 'webinc/plugins/dbforms2/fckconfig.js';
        oFCKeditor.ToolbarSet = 'BxCMS';
        oFCKeditor.ReplaceTextarea();
    }

    this.eFCK_OnComplete = function(einstance) {
        this.editorInstance = einstance;
        if (this.delayedSetValue === true) {
            this.delayedSetValue = false;
            this.setValue(this.value);
        }
    }

	this.setValue = function(value) {
		// Get the editor instance that we want to interact with.
		if (typeof FCKeditorAPI  !== 'undefined') {
			var oEditor = FCKeditorAPI.GetInstance(this.fckId) ;

            // Set the editor contents (replace the actual one).
			if (oEditor.Status === FCK_STATUS_COMPLETE && oEditor.SetHTML) {
                if(value === null) {
                    value = '';
                }
				oEditor.SetHTML(value);
                oEditor.ResetIsDirty();
			} else {
				this.DOMNode.value = value;
			}

            if (oEditor.Status !== FCK_STATUS_COMPLETE) {
                this.delayedSetValue = true;
            }

		} else {
			this.DOMNode.value = value;
            this.delayedSetValue = true;
		}

        this.delayedSetValue = true;
        this.value = value;
	}

	this.getValue = function(value) {
		// Get the editor instance that we want to interact with.
		var oEditor = FCKeditorAPI.GetInstance(this.fckId) ;
		return oEditor.GetXHTML(true);
	}

    this.hasChanged = function() {
        var oEditor;
        if (typeof FCKeditorAPI  !== "undefined") {
            oEditor = FCKeditorAPI.GetInstance(this.fckId);
            return oEditor.IsDirty();
        } else {
            return true;
        }
    }

}
dbforms2_field_text_wysiwyg.prototype = new dbforms2_field();



/**
 * Field for small text areas
 *
 */
function dbforms2_field_text_area_small(DOMNode) {
}
dbforms2_field_text_area_small.prototype = new dbforms2_field();



/**
 * Field for dropdowns
 *
 */
function dbforms2_field_select(DOMNode) {

    this.init = function(DOMNode) {
		this.initField(DOMNode);
    };

    this.getValue = function() {
        var nodeValue = jQuery(this.DOMNode).val();

        if (nodeValue !== null && typeof nodeValue === 'object') {
            value = '';
            for (var i=0; i<nodeValue.length; i++) {
                value = value + nodeValue[i] + ',';
            }
            value = value.substring(0, value.length - 1);
        } else {
            value = nodeValue;
        }
        return value;
    }

    this.setValue = function(value) {
        this.value = value;
        if (value !== null && typeof value === 'string' && this.multiple === true) {
            jQuery(this.DOMNode).val(value.split(','));
        } else {
            this.updateDOMNodeValue();
        }
    }
}
dbforms2_field_select.prototype = new dbforms2_field();



/**
 * Field for dates
 *
 */
function dbforms2_field_date(DOMNode) {
    this.init = function(DOMNode) {
        this.initField(DOMNode);
        jQuery(DOMNode).datepicker({
            duration: 'fast',
            yearRange: '-30:+30',
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            showOn: 'button'
        });
    }
}
dbforms2_field_date.prototype = new dbforms2_field();



/**
 * Field for static text
 *
 */
function dbforms2_field_fixed(DOMNode) {

	this.setValue = function(value) {
		var sp = document.getElementById( this.DOMNode.id + "_fixed");
		sp.innerHTML = value;
		this.value = value;
        this.updateDOMNodeValue();
	}

}
dbforms2_field_fixed.prototype = new dbforms2_field();



/**
 * Field for managing n2m relations
 *
 */
function dbforms2_field_relation_n2m(DOMNode) {

    this.init = function(DOMNode) {
        this.initField(DOMNode);

        this.valuesNode = document.getElementById(this.DOMNode.id+"_values");

        var cf_onLiveChoose = new ContextFixer(this.onLiveChoose, this);
        this.liveSelect = new dbforms2_liveselect();

        // init live select
        this.liveSelect.enablePager = true;
        this.liveSelect.onChooseAction = cf_onLiveChoose.execute;
        this.liveSelect.dataURI = dbforms2.liveSelectRootURI + '/' + this.id;
        this.liveSelect.autoExpandResultsOnFocus = false;
        this.liveSelect.init(
            document.getElementById(this.id + '_lsqueryfield') ,
            document.getElementById(this.id + '_lsresults') ,
            null ,
            document.getElementById(this.id + '_lspager')
        );

    }

	this.setValue = function(values) {
		this.resetFieldValues();
		for (var i in values) {
			this.addFieldValue( values[i].id , values[i].value );
		}
	}

    this.onLiveChoose = function(entry) {
        this.addFieldValue(entry.id, entry.title);
        this.changed = true;
    }

    this.addFieldValue = function( id , title ) {
        if( id !== 0 ) {
            var $values = $( this.valuesNode );
            var $li = $( '<li><img src="' + DBFORMS2_IMG_ROOT + 'delete.png" class="delete"><img src="' + DBFORMS2_IMG_ROOT + 'table_edit.png" class="edit">' + title + '</li>' );
            $li.attr( 'class' , 'n2mvalue' );
            $li.attr( '_value_id' , id );

            var that = this;

            $li.find('.delete').click( function () {
                that.changed = true;
                $(this).parent().remove();
            });

            $li.find('.edit').click( function () {
                dbforms2.openSubForm(that.linktothat, id);
            });

            $values.append($li);
            $values.sortable({
                'update' : function() {
                    that.changed = true;
                }
                });
        }
    }

    this.removeFieldValue = function(id) {
        if(id != null) {
            this.changed = true;
            this.valuesNode.removeChild(document.getElementById(this.DOMNode.id+"_value_id_"+id));
        }
    }

	this.getValue = function() {
		var values = new Array();

        $(this.valuesNode).find('li').each( function() {
            values.push($(this).attr('_value_id'));
        });

		return values;
	}

	this.resetFieldValues = function() {
		this.valuesNode.innerHTML = "";
	}

    this.enable = function() {
        this.liveSelect.enable();
    }

    this.disable = function() {
        this.liveSelect.disable();
    }

    this.focus = function() {
        this.liveSelect.focus();
    }


}
dbforms2_field_relation_n2m.prototype = new dbforms2_field();


/**
 * Field for managing n->1 relations, only use this in subforms
 *
 */
function dbforms2_field_relation_n21(DOMNode) {

    this.init = function(DOMNode) {
        this.initField(DOMNode);
        // init live select
        this.liveSelect = new dbforms2_liveselect();
        var wev = new bx_helpers_contextfixer(this.onLiveChoose, this);
        this.liveSelect.onChooseAction = wev.execute;
        this.liveSelect.dataURI = this.form.liveSelectRootURI + '/' + this.id;
        this.liveSelect.autoExpandResultsOnFocus = true;
        this.liveSelect.enablePager = true;
        this.liveSelect.showSelectedEntry = true;
        //this.liveSelect.readOnly = true;
        this.liveSelect.init(document.getElementById(this.DOMNode.id + '_lsqueryfield'), document.getElementById(this.DOMNode.id + '_lsresults'), null, document.getElementById(this.DOMNode.id + '_pd'));

        this.form.registerInternalEventHandler(DBFORMS2_EVENT_FORM_DELETE_POST, this, this.eventDelete);
        this.form.registerInternalEventHandler(DBFORMS2_EVENT_FORM_SAVE_POST, this, this.eventSave);
        this.form.registerInternalEventHandler(DBFORMS2_EVENT_FORM_NEW_PRE, this, this.eventNew);
    }

    this.onLiveChoose = function(entry) {
        this.form.loadFormDataByID(entry.id);
        this.setParentIdField(entry.id);
    }

    this.setParentFormId = function(id) {
        var thisid = this.form.parentForm.getFieldByID(this.form.thisidfield).getValue();
        this.form.loadFormDataByID(thisid);
        //this.liveSelect.setCurrentEntryById(thisid);
        //alert(this.form.getFieldByID('id').value);
    }

    this.setParentIdField = function(id) {
        this.form.parentForm.getFieldByID(this.form.thisidfield).setValue(id);
        this.form.parentForm.getFieldByID(this.form.thisidfield).changed = true;
    }

    this.eventDelete = function() {
        this.liveSelect.reloadCurrentQuery();
        this.setParentIdField(0);
    }

    this.eventSave = function() {
        this.liveSelect.reloadCurrentQuery();
        this.setParentIdField(this.form.currentID);
    }

    this.eventNew = function() {
        this.liveSelect.reloadCurrentQuery();
        this.setParentIdField(0);
    }

    this.focus = function() {
    }

}
dbforms2_field_relation_n21.prototype = new dbforms2_field();


/**
 * Fixed date/time field
 *
 */
function dbforms2_field_fixed_datetime(DOMNode) {

	this.setValue = function(value) {
		var sp = document.getElementById( this.DOMNode.id + "_fixed");
		if (sp) {
		value = value.replace(/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/,"$2/$3/$1 $4:$5:$6");

		var date = new Date( Date.parse(value));

		sp.innerHTML = date.toLocaleString();;
		}
        this.updateDOMNodeValue();
	}
}
dbforms2_field_fixed_datetime.prototype = new dbforms2_field_fixed();



/**
 * File Field
 *
 */
function dbforms2_field_file(DOMNode) {

    var uploadDir = '';

	this.init = function(DOMNode) {
        this.initField(DOMNode);
        this.uploadDir = this.DOMNode.getAttribute('uploaddir');

        this.previewSmallDOMNode = document.getElementById(this.DOMNode.id + "_previewSmall");
        this.previewLargeDOMNode = document.getElementById(this.DOMNode.id + "_previewLarge");
    }

    this.setValue = function(value) {
        this.value = value;
        this.resetChanged();
        this.updateDOMNodeValue();
        this.updatePreviewAndTooltip();
    }

    this.updatePreviewAndTooltip = function() {
        if(dbforms2_helpers.isImage(this.value)) {
            this.previewSmallDOMNode.src = DBFORMS2_IMG_PREVIEW_SMALL_DIR + this.uploadDir + this.value;
            bx_tooltip.prepare(this.previewLargeDOMNode, DBFORMS2_IMG_PREVIEW_LARGE_DIR + this.uploadDir + this.value);
        } else {
            this.previewSmallDOMNode.src = DBFORMS2_IMG_NULLIMG;
            bx_tooltip.remove(this.previewLargeDOMNode);
        }
    }

    this.onChange = function() {
        this.changed = true;
        this.value = this.DOMNode.value;
        this.updatePreviewAndTooltip();
    }

	this.setIframe = function() {

		var iframe = document.getElementById(this.DOMNode.id + "_iframe");
		if (iframe.style.display == "block") {
			this.closeIframe();
		} else {
			iframe.style.display = "block";
			iframe.DOMNode = this.DOMNode;
			iframe.onload = function() {
				var doc = this.contentDocument;
				doc.forms.upload.action = document.location.protocol + '//' + document.location.host + document.location.pathname + "/upload/";
				var fu = doc.getElementById("fieldname");
				fu.value = this.DOMNode.name;
				this.onload = null;
			}

			iframe.contentWindow.location = bx_webroot + "webinc/plugins/dbforms2/emptyupload.html";
		}
	}

	this.closeIframe = function() {
		var iframe = document.getElementById(this.DOMNode.id + "_iframe")
		iframe.style.display = "none";
	}

}
dbforms2_field_file.prototype = new dbforms2_field();
//in trunk is called upload
dbforms2_field_upload = dbforms2_field_file;


/**
 * File Browser Field
 *
 * Provides a field which has an integrated browser to all the files in the cms.
 *
 */
function dbforms2_field_file_browser(DOMNode) {
    var isImage;

    this.init = function(DOMNode) {
        this.initField(DOMNode);
        this.isImage = false;

        if(DOMNode.getAttribute('isImage') == '1') {
            this.isImage = true;
            this.previewSmallDOMNode = document.getElementById(this.DOMNode.id + "_previewSmall");
            this.previewLargeDOMNode = document.getElementById(this.DOMNode.id + "_previewLarge");
        }

    }

    this.setValue = function(value) {
        this.value = value;
        this.resetChanged();
        this.updateDOMNodeValue();
        this.updatePreviewAndTooltip();
    }

    this.setUrl = function(url) {
        this.value = url;
        this.changed = true;
        this.updateDOMNodeValue();
        this.updatePreviewAndTooltip();
    }

    this.updatePreviewAndTooltip = function() {
        if(this.isImage) {
            if(dbforms2_helpers.isImage(this.value)) {
                this.previewSmallDOMNode.src = DBFORMS2_IMG_PREVIEW_SMALL_DIR + this.value;
                bx_tooltip.prepare(this.previewLargeDOMNode, DBFORMS2_IMG_PREVIEW_LARGE_DIR + this.value);
            } else {
                this.previewSmallDOMNode.src = DBFORMS2_IMG_NULLIMG;
                bx_tooltip.remove(this.previewLargeDOMNode);
            }
        }
    }

    this.onChange = function() {
        this.changed = true;
        this.value = this.DOMNode.value;
        this.updatePreviewAndTooltip();
    }

}
dbforms2_field_file_browser.prototype = new dbforms2_field();



/**
 * Listview base class
 *
 */
function dbforms2_field_listview(DOMNode) {

    this.listData = new dbforms2_formData();
    this.transport = new dbforms2_transport();
    this.dataUri = '';

    this.init = function(DOMNode) {
        this.initField(DOMNode);
        this.initListView(DOMNode);
        this.reloadEntries();
    }

    this.initListView = function(DOMNode) {

        var cf_onChoose = new bx_helpers_contextfixer(this.onChoose, this);
        var cf_onDelete = new bx_helpers_contextfixer(this.onDelete, this);
        var cf_onUpdateOrder = new bx_helpers_contextfixer(this.onUpdateOrder, this);

        this.listview = new dbforms2_listview();
        this.listview.onChooseAction = cf_onChoose.execute;
        this.listview.onDeleteAction = cf_onDelete.execute;
        this.listview.onUpdateOrderAction= cf_onUpdateOrder.execute;
        this.listview.dataURI = this.form.listViewRootURI + '/' + this.id;
        this.listview.init(document.getElementById(this.id + '_lvresultstable'));

        this.form.registerInternalEventHandler(DBFORMS2_EVENT_FORM_DELETE_POST, this, this.eventFormDeletePost);
        this.form.registerInternalEventHandler(DBFORMS2_EVENT_FORM_NEW_POST, this, this.eventFormNewPost);
        this.form.registerInternalEventHandler(DBFORMS2_EVENT_FORM_SAVE_POST, this, this.eventFormSavePost);
    }

    this.setValue = function(value) {
    }

    this.onChoose = function(entry) {
        this.form.loadFormDataByID(entry.id);
        dbforms2_helpers.showSubForm(document.getElementById('_form_'+this.form.name+'_toggleLink'), '_form_'+this.form.name);
    }

    this.onDelete = function(entry) {
        this.listview.results.removeEntry(entry);
        this.form.deleteEntryByID(entry.id);
    }

    this.onUpdateOrder = function(entries) {
        var orderfield = this.form.parentForm.getFieldByID( this.form.name + '_entry_order' );
        if( typeof orderfield !== 'undefined' ) {
            orderfield.setValue( entries );
            orderfield.changed = true;
        }
    }

    this.eventFormSavePost = function() {
        this.reloadEntries();
    }

    this.eventFormNewPost = function() {
        this.reloadEntries();
    }

    this.eventFormDeletePost = function() {
        this.reloadEntries();
    }

    this.reloadEntries = function() {
        this.listview.loadEntries();
    }

    this.focus = function() {
    }

}
dbforms2_field_listview.prototype = new dbforms2_field();



/**
 * Listview for 12n relations.
 *
 */
function dbforms2_field_listview_12n(DOMNode) {
    this.thisid = '';
    this.listData = new dbforms2_formData();
    this.transport = new dbforms2_transport();
    this.dataUri = '';

    this.init = function(DOMNode) {
        this.initField(DOMNode);
        this.initListView(DOMNode);
        this.form.registerInternalEventHandler(DBFORMS2_EVENT_PARENTFORM_NEW, this, this.eventParentFormNew);
    }

    this.setParentFormId = function(id) {
        this.listview.loadEntries({thatid:id});
    }

    this.eventParentFormNew = function() {
        this.listview.results.removeAllEntries();
    }

    this.reloadEntries = function() {
        dbforms2_log.log('reloadEntries() for ' + this.id);
        if(this.form.parentForm.currentID == 0 && this.form.parentForm.insertID != 0) {
            this.listview.loadEntries({thatid:this.form.parentForm.insertID});
        } else if(this.form.parentForm.currentID != 0) {
            this.listview.loadEntries({thatid:this.form.parentForm.currentID});
        }
    }


}
dbforms2_field_listview_12n.prototype = new dbforms2_field_listview();


