<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 

xmlns:tal="http://xml.zope.org/namespaces/tal"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:xslout="whatever" 
xmlns:xhtml="http://www.w3.org/1999/xhtml" 
xmlns="http://www.w3.org/1999/xhtml">

<xsl:namespace-alias stylesheet-prefix="xslout" result-prefix="xsl"/> 


<xsl:template match="/">
    <xslout:stylesheet version="1.0">
    
    
    <xslout:template match="/">
    
        <xsl:apply-templates/>
        
        </xslout:template>
    
    
    </xslout:stylesheet>
</xsl:template>

<xsl:template match="xhtml:*[@tal:content]">

    <xslout:copy-of select="{concat(&quot;/bx/plugin[@name='xhtml']&quot;,@tal:content)}"/>
</xsl:template>

<xsl:template match="xhtml:*">
<xsl:copy>
        <xsl:for-each select="@*">
            <xsl:copy/>
        </xsl:for-each>
        <xsl:apply-templates/>
    </xsl:copy>
</xsl:template>
</xsl:stylesheet>