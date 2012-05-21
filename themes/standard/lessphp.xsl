<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:forms="http://bitflux.org/forms"
    xmlns:php="http://php.net/xsl"
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    exclude-result-prefixes="xhtml forms php i18n"
    >

    <xsl:template name="doLessPhpHref">
        <xsl:param name="lessFile" select="''"/>
        <xsl:variable name="targetFile" select="concat('themes/',$theme,'/',$lessFile)"/>
        <xsl:attribute name="href">
            <xsl:value-of select="concat($webroot, $targetFile, '?', php:functionString('bx_helpers_lessphp::getFileVersion', $targetFile))"/>
        </xsl:attribute>
    </xsl:template>

</xsl:stylesheet>
