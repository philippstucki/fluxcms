<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:bxf="http://bitflux.org/functions" xmlns:tal="http://xml.zope.org/namespaces/tal" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xslout="whatever" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" extension-element-prefixes="func">


    <xsl:namespace-alias stylesheet-prefix="xslout" result-prefix="xsl"/>

    <func:function name="bxf:tales">
        <xsl:param name="path"/>
        <xsl:choose>
            <xsl:when test="starts-with($path,'$')">
                <func:result select="$path"/>
            </xsl:when>
            <xsl:when test="starts-with($path,'plugin/')">
                <xsl:variable name="afterPlugin" select="substring-after($path,'plugin/')"/>
                <xsl:variable name="xpath" select="substring-after($afterPlugin,'/')"/>

                <func:result select="concat('/bx/plugin[@name=&quot;',substring-before($afterPlugin,'/'),'&quot;]/',$xpath)"/>

            </xsl:when>
            <xsl:otherwise>
                <func:result select="$path"/>
            </xsl:otherwise>
        </xsl:choose>

    </func:function>

    <xsl:template match="/">
        <xslout:stylesheet version="1.0" exclude-result-prefixes="xhtml bxf tal">
            <xslout:output encoding="utf-8" method="xml" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>


            <xslout:template match="/">

                <xsl:apply-templates/>

            </xslout:template>
            <xslout:template match="*">
                <xslout:copy>
                    <xslout:apply-templates select="@*"/>
                    <xslout:apply-templates/>
                </xslout:copy>
            </xslout:template>

            <xslout:template match="@*">

                <xslout:copy-of select="."/>

            </xslout:template>

        </xslout:stylesheet>
    </xsl:template>

    <xsl:template match="xhtml:*[@tal:condition]" priority="10">
        <xslout:if test="{bxf:tales(@tal:condition)}">
            <xsl:apply-templates/>
        </xslout:if>
    </xsl:template>


    <xsl:template match="xhtml:*[@tal:content]">

        <xsl:copy>

            <xsl:apply-templates select="@*"/>
            <xslout:apply-templates select="{bxf:tales(@tal:content)}/*|{bxf:tales(@tal:content)}/text()"/>
        </xsl:copy>

    </xsl:template>


    <xsl:template match="xhtml:*[@tal:repeat]">
        <xsl:copy>
            <xsl:apply-templates select="@*"/>
            <xsl:variable name="v" select="substring-before(@tal:repeat,' ')"/>
            <xsl:variable name="x" select="substring-after(@tal:repeat,' ')"/>
            <xslout:for-each select="{bxf:tales($x)}">
                <xslout:variable name="{$v}" select="."/>
                <xsl:apply-templates/>
            </xslout:for-each>

        </xsl:copy>
    </xsl:template>

    <xsl:template match="@*">
        <xsl:if test="namespace-uri() != 'http://xml.zope.org/namespaces/tal'">
            <xsl:copy-of select="."/>
        </xsl:if>
    </xsl:template>

    <xsl:template match="@tal:attributes">
        <xsl:call-template name="talAttribute">
        <xsl:with-param name="attr" select="."/>
        </xsl:call-template>

    </xsl:template>

    <xsl:template name="talAttribute">
        <xsl:param name="attr"/>
        <xsl:choose>
            <xsl:when test="contains($attr,'; ')">
            
                <xsl:call-template name="talAttribute">
                    <xsl:with-param name="attr" select="substring-after($attr,'; ')"/>
                </xsl:call-template>
                <xsl:call-template name="outputTalAttribute">
                    <xsl:with-param name="attr" select="substring-before($attr,'; ')"/>
                </xsl:call-template>
            </xsl:when>

            <xsl:otherwise>
                <xsl:call-template name="outputTalAttribute">
                    <xsl:with-param name="attr" select="$attr"/>
                </xsl:call-template>
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>
  
  <xsl:template name="outputTalAttribute">
        <xsl:param name="attr"/>
        <xsl:variable name="name" select="substring-before($attr,' ')"/>
        <xsl:variable name="value" select="substring-after($attr,' ')"/>
        <xslout:attribute name="{$name}">
            <xslout:value-of select="{bxf:tales($value)}"/>
        </xslout:attribute>

    </xsl:template>

    <xsl:template match="xhtml:*">
        <xsl:copy>
            <xsl:apply-templates select="@*"/>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>
</xsl:stylesheet>