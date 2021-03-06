/*
 * Copyright 2004 ThoughtWorks, Inc
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 */

// This script contains some HTML utility functions that
// make it possible to handle elements in a way that is
// compatible with both IE-like and Mozilla-like browsers

String.prototype.trim = function() {
  var result = this.replace( /^\s+/g, "" );// strip leading
  return result.replace( /\s+$/g, "" );// strip trailing
};
String.prototype.lcfirst = function() {
   return this.charAt(0).toLowerCase() + this.substr(1);
};
String.prototype.ucfirst = function() {
   return this.charAt(0).toUpperCase() + this.substr(1);
};
String.prototype.startsWith = function(str) {
    return this.indexOf(str) == 0;
};

// Returns the text in this element
function getText(element) {
    var text = "";

    if(browserVersion.isFirefox && browserVersion.firefoxVersion >= "1.5")
    {
        var dummyElement = element.cloneNode(true);
        renderWhitespaceInTextContent(dummyElement);
        text = dummyElement.textContent;
    } else if (browserVersion.isOpera) {
    	var dummyElement = element.cloneNode(true);
        renderWhitespaceInTextContent(dummyElement);
        text = dummyElement.innerText;
        text = xmlDecode(text);
    }
    else if(element.textContent)
    {
        text = element.textContent;
    }
    else if(element.innerText)
    {
        text = element.innerText;
    }

    text = normalizeNewlines(text);
    text = normalizeSpaces(text);

    return text.trim();
}

function renderWhitespaceInTextContent(element) {
    // Remove non-visible newlines in text nodes
    if (element.nodeType == Node.TEXT_NODE)
    {
        element.data = element.data.replace(/\n|\r|\t/g, " ");
        return;
    }

    if (element.nodeType == Node.COMMENT_NODE)
    {
        element.data = "";
        return;
    }

    // Don't modify PRE elements
    if (element.tagName == "PRE")
    {
        return;
    }

    // Handle inline element that force newlines
    if (tagIs(element, ["BR", "HR"]))
    {
        // Replace this element with a newline text element
        element.parentNode.replaceChild(element.ownerDocument.createTextNode("\n"), element)
    }

    for (var i = 0; i < element.childNodes.length; i++)
    {
        var child = element.childNodes.item(i)
        renderWhitespaceInTextContent(child);
    }

    // Handle block elements that introduce newlines
// -- From HTML spec:
//<!ENTITY % block
//     "P | %heading; | %list; | %preformatted; | DL | DIV | NOSCRIPT |
//      BLOCKQUOTE | FORM | HR | TABLE | FIELDSET | ADDRESS">
    if (tagIs(element, ["P", "DIV"]))
    {
        element.appendChild(element.ownerDocument.createTextNode("\n"), element)
    }

}

function tagIs(element, tags)
{
    var tag = element.tagName;
    for (var i = 0; i < tags.length; i++)
    {
        if (tags[i] == tag)
        {
            return true;
        }
    }
    return false;
}

/**
 * Convert all newlines to \m
 */
function normalizeNewlines(text)
{
    return text.replace(/\r\n|\r/g, "\n");
}

/**
 * Replace multiple sequential spaces with a single space, and then convert &nbsp; to space.
 */
function normalizeSpaces(text)
{
    // IE has already done this conversion, so doing it again will remove multiple nbsp
    if (browserVersion.isIE)
    {
        return text;
    }

    // Replace multiple spaces with a single space
    // TODO - this shouldn't occur inside PRE elements
    text = text.replace(/\ +/g, " ");

    // Replace &nbsp; with a space
    var pat = String.fromCharCode(160); // Opera doesn't like /\240/g
   	var re = new RegExp(pat, "g");
    return text.replace(re, " ");
}

function xmlDecode(text) {
	text = text.replace(/&quot;/g, '"');
	text = text.replace(/&apos;/g, "'");
	text = text.replace(/&lt;/g, "<");
	text = text.replace(/&gt;/g, ">");
	text = text.replace(/&amp;/g, "&");
	return text;
}

// Sets the text in this element
function setText(element, text) {
    if(element.textContent) {
        element.textContent = text;
    } else if(element.innerText) {
        element.innerText = text;
    }
}

// Get the value of an <input> element
function getInputValue(inputElement) {
    if (inputElement.type.toUpperCase() == 'CHECKBOX' ||
        inputElement.type.toUpperCase() == 'RADIO')
    {
        return (inputElement.checked ? 'on' : 'off');
    }
    return inputElement.value;
}

/* Fire an event in a browser-compatible manner */
function triggerEvent(element, eventType, canBubble) {
    canBubble = (typeof(canBubble) == undefined) ? true : canBubble;
    if (element.fireEvent) {
        element.fireEvent('on' + eventType);
    }
    else {
        var evt = document.createEvent('HTMLEvents');
        evt.initEvent(eventType, canBubble, true);
        element.dispatchEvent(evt);
    }
}

function triggerKeyEvent(element, eventType, keycode, canBubble) {
    canBubble = (typeof(canBubble) == undefined) ? true : canBubble;
    if (element.fireEvent) {
    		var w = parent.frames['myiframe']
                if (w==null) {
                	w = window;
                }
		keyEvent = w.document.createEventObject();
		keyEvent.keyCode=keycode;
		element.fireEvent('on' + eventType, keyEvent);
    }
    else {
    	var evt;
    	if( window.KeyEvent ) {
			evt = document.createEvent('KeyEvents');
			evt.initKeyEvent(eventType, true, true, window, false, false, false, false, keycode, keycode);
		} else {
			evt = document.createEvent('UIEvents');
			evt.initUIEvent( eventType, true, true, window, 1 );
			evt.keyCode = keycode;
		}

        element.dispatchEvent(evt);
    }
}

/* Fire a mouse event in a browser-compatible manner */
function triggerMouseEvent(element, eventType, canBubble, clientX, clientY) {
    clientX = clientX ? clientX : 0;
    clientY = clientY ? clientY : 0;
    
    // TODO: set these attributes -- they don't seem to be needed by the initial test cases, but that could change...
    var screenX = 0;
    var screenY = 0;
    
    canBubble = (typeof(canBubble) == undefined) ? true : canBubble;
    if (element.fireEvent) {
    	if (!screenX && !screenY && !clientX && !clientY) {
        	element.fireEvent('on' + eventType);
        }
        else {
        	var ieEvent = document.createEventObject();
                ieEvent.detail = 0;
                ieEvent.screenX = screenX;
                ieEvent.screenY = screenY;
                ieEvent.clientX = clientX;
                ieEvent.clientY = clientY;
                ieEvent.ctrlKey = false;
                ieEvent.altKey = false;
                ieEvent.shiftKey = false;
                ieEvent.metaKey = false;
                ieEvent.button = 0;
                ieEvent.relatedTarget = null;
                         
                // when we go this route, window.event is never set to contain the event we have just created.
                // ideally we could just slide it in as follows in the try-block below, but this normally
                // doesn't work.  This is why I try to avoid this code path, which is only required if we need to 
                // set attributes on the event (e.g., clientX).
                try {
        	        window.event = ieEvent;
                }
                catch(e) {
        		// getting an "Object does not support this action or property" error.  Save the event away
                        // for future reference.
                        // TODO: is there a way to update window.event?
                         
                        // work around for http://jira.openqa.org/browse/SEL-280 -- make the event available somewhere:
                        selenium.browserbot.getCurrentWindow().selenium_event = ieEvent;
                }
                element.fireEvent('on' + eventType, ieEvent);
        }
    }
    else {
        var evt = document.createEvent('MouseEvents');
        if (evt.initMouseEvent)
        {
            evt.initMouseEvent(eventType, canBubble, true, document.defaultView, 1, screenX, screenY, clientX, clientY, false, false, false, false, 0, null)
        }
        else
        {
            // Safari
            // TODO we should be initialising other mouse-event related attributes here
            evt.initEvent(eventType, canBubble, true);
        }
        element.dispatchEvent(evt);
    }
}

function removeLoadListener(element, command) {
    if (window.removeEventListener)
        element.removeEventListener("load", command, true);
    else if (window.detachEvent)
        element.detachEvent("onload", command);
}

function addLoadListener(element, command) {
    if (window.addEventListener && !browserVersion.isOpera)
        element.addEventListener("load",command, true);
    else if (window.attachEvent)
        element.attachEvent("onload",command);
}

function addUnloadListener(element, command) {
    if (window.addEventListener)
        element.addEventListener("unload",command, true);
    else if (window.attachEvent)
        element.attachEvent("onunload",command);
}

/**
 * Override the broken getFunctionName() method from JsUnit
 * This file must be loaded _after_ the jsunitCore.js
 */
function getFunctionName(aFunction) {
  var regexpResult = aFunction.toString().match(/function (\w*)/);
  if (regexpResult && regexpResult[1]) {
      return regexpResult[1];
  }
  return 'anonymous';
}

function getDocumentBase(doc) {
	var bases = document.getElementsByTagName("base");
	if (bases && bases.length && bases[0].href) {
		return bases[0].href;
	}
	return "";
}

function describe(object, delimiter) {
    var props = new Array();
    for (var prop in object) {
        props.push(prop + " -> " + object[prop]);
    }
    return props.join(delimiter || '\n');
}

var PatternMatcher = function(pattern) {
    this.selectStrategy(pattern);
};
PatternMatcher.prototype = {

    selectStrategy: function(pattern) {
        this.pattern = pattern;
        var strategyName = 'glob'; // by default
        if (/^([a-z-]+):(.*)/.test(pattern)) {
            var possibleNewStrategyName = RegExp.$1;
            var possibleNewPattern = RegExp.$2;
            if (PatternMatcher.strategies[possibleNewStrategyName]) {
            	strategyName = possibleNewStrategyName;
                pattern = possibleNewPattern;
            }
        }
        var matchStrategy = PatternMatcher.strategies[strategyName];
        if (!matchStrategy) {
            throw new SeleniumError("cannot find PatternMatcher.strategies." + strategyName);
        }
        this.strategy = matchStrategy;
        this.matcher = new matchStrategy(pattern);
    },

    matches: function(actual) {
        return this.matcher.matches(actual + '');
        // Note: appending an empty string avoids a Konqueror bug
    }

};

/**
 * A "static" convenience method for easy matching
 */
PatternMatcher.matches = function(pattern, actual) {
    return new PatternMatcher(pattern).matches(actual);
};

PatternMatcher.strategies = {

    /**
     * Exact matching, e.g. "exact:***"
     */
    exact: function(expected) {
        this.expected = expected;
        this.matches = function(actual) {
            return actual == this.expected;
        };
    },

    /**
     * Match by regular expression, e.g. "regexp:^[0-9]+$"
     */
    regexp: function(regexpString) {
        this.regexp = new RegExp(regexpString);
        this.matches = function(actual) {
            return this.regexp.test(actual);
        };
    },

    /**
     * "globContains" (aka "wildmat") patterns, e.g. "glob:one,two,*",
     * but don't require a perfect match; instead succeed if actual
     * contains something that matches globString.
     * Making this distinction is motivated by a bug in IE6 which
     * leads to the browser hanging if we implement *TextPresent tests
     * by just matching against a regular expression beginning and
     * ending with ".*".  The globcontains strategy allows us to satisfy
     * the functional needs of the *TextPresent ops more efficiently
     * and so avoid running into this IE6 freeze.
     */
    globContains: function(globString) {
        this.regexp = new RegExp(PatternMatcher.regexpFromGlobContains(globString));
        this.matches = function(actual) {
            return this.regexp.test(actual);
        };
    },


    /**
     * "glob" (aka "wildmat") patterns, e.g. "glob:one,two,*"
     */
    glob: function(globString) {
        this.regexp = new RegExp(PatternMatcher.regexpFromGlob(globString));
        this.matches = function(actual) {
            return this.regexp.test(actual);
        };
    }

};

PatternMatcher.convertGlobMetaCharsToRegexpMetaChars = function(glob) {
    var re = glob;
    re = re.replace(/([.^$+(){}\[\]\\|])/g, "\\$1");
    re = re.replace(/\?/g, "(.|[\r\n])");
    re = re.replace(/\*/g, "(.|[\r\n])*");
    return re;
};

PatternMatcher.regexpFromGlobContains = function(globContains) {
    return PatternMatcher.convertGlobMetaCharsToRegexpMetaChars(globContains);
};

PatternMatcher.regexpFromGlob = function(glob) {
    return "^" + PatternMatcher.convertGlobMetaCharsToRegexpMetaChars(glob) + "$";
};

var Assert = {

    fail: function(message) {
        throw new AssertionFailedError(message);
    },

    /*
     * Assert.equals(comment?, expected, actual)
     */
    equals: function() {
        var args = new AssertionArguments(arguments);
        if (args.expected === args.actual) {
            return;
        }
        Assert.fail(args.comment +
                    "Expected '" + args.expected +
                    "' but was '" + args.actual + "'");
    },

    /*
     * Assert.matches(comment?, pattern, actual)
     */
    matches: function() {
        var args = new AssertionArguments(arguments);
        if (PatternMatcher.matches(args.expected, args.actual)) {
            return;
        }
        Assert.fail(args.comment +
                    "Actual value '" + args.actual +
                    "' did not match '" + args.expected + "'");
    },

    /*
     * Assert.notMtches(comment?, pattern, actual)
     */
    notMatches: function() {
        var args = new AssertionArguments(arguments);
        if (!PatternMatcher.matches(args.expected, args.actual)) {
            return;
        }
        Assert.fail(args.comment +
                    "Actual value '" + args.actual +
                    "' did match '" + args.expected + "'");
    }

};

// Preprocess the arguments to allow for an optional comment.
function AssertionArguments(args) {
    if (args.length == 2) {
        this.comment = "";
        this.expected = args[0];
        this.actual = args[1];
    } else {
        this.comment = args[0] + "; ";
        this.expected = args[1];
        this.actual = args[2];
    }
}



function AssertionFailedError(message) {
    this.isAssertionFailedError = true;
    this.isSeleniumError = true;
    this.message = message;
    this.failureMessage = message;
}

function SeleniumError(message) {
    var error = new Error(message);
    error.isSeleniumError = true;
    return error;
};


var Effect = new Object();

Object.extend(Effect, {
    highlight : function(element) {
        var highLightColor = "yellow";
        var originalColor = Element.getStyle(element, "background-color");
        Element.setStyle(element, {"background-color" : highLightColor});
        window.setTimeout(function() {
            Element.setStyle(element, {"background-color" : originalColor});
        }, 300);
    }
});


// for use from vs.2003 debugger
function objToString(obj) {
	var s = "";
        for (key in obj) {
        	var line = key + "->" + obj[key];
                line.replace("\n", " ");
    	    	s += line + "\n";
        }
        return s;
}
