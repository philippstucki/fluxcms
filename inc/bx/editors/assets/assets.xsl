<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
 xmlns:xhtml="http://www.w3.org/1999/xhtml" 
 xmlns="http://www.w3.org/1999/xhtml"
 xmlns:php="http://php.net/xsl"
 xmlns:i18n="http://apache.org/cocoon/i18n/2.1">

<xsl:output
 encoding="utf-8"
 method="html"
 doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
 doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

<xsl:param name="webroot"/>
<xsl:variable name="assetpath" select="/bx/plugin/assets/@path"/>

<xsl:template match="/">
    <html>
    	<head>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    	<link rel="stylesheet" type="text/css" href="http://berggebiete/themes/standard/admin/css/formedit.css" />
    	<link rel="stylesheet" type="text/css" href="http://berggebiete/themes/standard/admin/css/admin.css" />
    	<link rel="stylesheet" type="text/css" media="screen" href="{$webroot}/themes/admin/css/assets.css"/>
    	<script type="text/javascript" language="javascript">
    		var bx_webroot = '<xsl:value-of select="$webroot"/>';
    	</script>
    	<script type="text/javascript" language="javascript"><![CDATA[
    	
    	function assetsPopup(id) {
    		valuef = document.getElementById('value['+id+']');
    		if (valuef) {
    			 var fBrowserUrl = bx_webroot + 'webinc/fck/editor/filemanager/browser/default/browser.html?Type=files&Connector=connectors/php/connector.php';
    			 fBrowserUrl+= "&RootPath=/files";
    		     fBrowserWin = window.open(fBrowserUrl, 'fBrowser', 'width=800,height=600,location=no,menubar=no');
    		}
    		
    		SetUrl = function(url)  {
    			valuef.value = url;	
    		}
    		
    	}
    	
    	]]>
    	</script>
    	</head>
    	<body>
    		<div id="admincontent">
    		<h2><i18n:text>Edit Assets for</i18n:text> <xsl:value-of select="$assetpath"/></h2>
    		
    		<div id="form">
    			<form name="assetsform" id="assetsform">
    				<xsl:apply-templates select="/bx/plugin/assets/entry"/>
    			</form>
    		</div>
    		
    		</div>
    	</body>
    </html>
</xsl:template>

<xsl:template match="entry">
	<p><xsl:value-of select="position()"/>)&#160;
	<input type="text" name="name[{id}]" value="{name}"/>&#160;
	<input type="text" name="value[{id}]" id="value[{id}]" value="{value}"/>&#160;
	<input type="button" name="" value="Select" onclick="assetsPopup({id})"/>&#160;
	<select name="type[{id}]">
		<option value="link">
			<xsl:if test="type='link'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
		Link
		</option>
		<option value="download">
			<xsl:if test="type='download'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
		Download
		</option>
	</select>
	&#160;
	
	</p>
</xsl:template>

</xsl:stylesheet>
