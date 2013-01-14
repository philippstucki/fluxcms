<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns:php="http://php.net/xsl"
    exclude-result-prefixes="php xhtml"
>
    <xsl:attribute-set name="standardInputElement">
        <xsl:attribute name="onfocus">if(dbforms2_globalObj[this.id]) dbforms2_globalObj[this.id].e_onFocus();</xsl:attribute>
        <xsl:attribute name="onblur">if(dbforms2_globalObj[this.id]) dbforms2_globalObj[this.id].e_onBlur();</xsl:attribute>
        <xsl:attribute name="onmouseover">if(dbforms2_globalObj[this.id]) dbforms2_globalObj[this.id].e_onMouseOver();</xsl:attribute>
        <xsl:attribute name="onmouseout">if(dbforms2_globalObj[this.id]) dbforms2_globalObj[this.id].e_onMouseOut();</xsl:attribute>
        <xsl:attribute name="onchange">if(dbforms2_globalObj[this.id]) dbforms2_globalObj[this.id].e_onChange();</xsl:attribute>
        <xsl:attribute name="class"></xsl:attribute>
    </xsl:attribute-set>

    <xsl:template match="input" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                <label for="{@id}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <input xsl:use-attribute-sets="standardInputElement" id="{@id}">
                    <xsl:apply-templates select="@*[name() != 'descr']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="input[@type='hidden']" mode="fields"/>

    <xsl:template match="input[@type='hidden']" mode="hidden">
        <input xsl:use-attribute-sets="standardInputElement" id="{@id}">
             <xsl:apply-templates select="@*[name() != 'descr']" mode="xhtml"/>
             <xsl:apply-templates mode="xhtml"/>
        </input>
    </xsl:template>

    <xsl:template match="textarea" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                <label for="{@id}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <textarea xsl:use-attribute-sets="standardInputElement" id="{@id}" name="field_{@name}">
                    <xsl:apply-templates select="@*[name() != 'descr']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </textarea>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="remark" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                <label for="{@id}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <label for="{@id}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="select" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                <label for="{@id}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <select xsl:use-attribute-sets="standardInputElement" id="{@id}">
                    <xsl:apply-templates select="@*[name() != 'descr']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </select>
            </td>
        </tr>
    </xsl:template>


    <xsl:template match="select[@type='relation_n2m']" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                <label for="{@id}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <div class="liveselectcontainer" style="z-index: {1000-position()} !important;">
                    <div class="liveselect">
                        <input type="text" id="{@name}_lsqueryfield" size="40"/>
                        <div class="liveselectResults" id="{@name}_lsresults">
                            <ul></ul>
                            <div id="{@name}_lspager" class="liveselectPager"></div>
                        </div>
                    </div>
                </div>

                <xsl:if test="@linktothat != ''">
                    <a onclick="dbforms2.openSubForm('{@linktothat}');" style="margin-left: 415px; cursor: pointer; text-decoration:underline;">Create New Entry</a>
                </xsl:if>

                <input type="hidden" id="{@id}"/>
                <ul class="n2mvalues" id="{@id}_values">
                </ul>

            </td>
        </tr>
    </xsl:template>

    <xsl:template match="input[@type='date']" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                <label for="{@id}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <input xsl:use-attribute-sets="standardInputElement" id="{@id}" type="text">
                    <xsl:apply-templates select="@*[name() != 'descr' and name() != 'type']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="input[@type='color']" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                <label for="{@id}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <input xsl:use-attribute-sets="standardInputElement" id="{@id}">
                    <xsl:apply-templates select="@*[name() != 'descr']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
                <xsl:text> </xsl:text>
                <input type="button" value="..." onclick="colorPicker_show('{@id}', dbforms2_globalObj['{@id}']); return false;"/>&#160;
                <span style="width: 40px;" id="anchor_{@id}" ><img src="{$DBFORMS2_IMG_NULLIMG}" height="15" width="80"/></span>
            </td>
        </tr>
    </xsl:template>

    <!-- in fact this is fields_file and not fields_upload -->
    <xsl:template match="input[@type='upload']" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                <label for="{@id}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput">
                <input xsl:use-attribute-sets="standardInputElement" id="{@id}">
                    <xsl:apply-templates select="@*[name() != 'descr']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
                <xsl:text> </xsl:text>

                <input type="button" onclick="openUploadIframe('{@name}')" value="..."/>

                <span id="{@id}_previewLarge" class="pic">
                    <img id="{@id}_previewSmall" src="{$DBFORMS2_IMG_NULLIMG}" border="0"/>
                </span>

                <iframe id="{@id}_iframe" width="400" height="50" style="display: none"></iframe>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="input[@type='file_browser']" mode="fields">
        <tr class="formRow">
            <td class="formHeader">
                <label for="field_{@name}">
                    <xsl:value-of select="@descr"/>
                </label>
            </td>
            <td class="formInput" >
                <input xsl:use-attribute-sets="standardInputElement" id="{@id}" type="text">
                    <xsl:apply-templates select="@*[name() != 'descr' and name() != 'type']" mode="xhtml"/>
                    <xsl:apply-templates mode="xhtml"/>
                </input>
                <xsl:text> </xsl:text>

                <input type="button" onclick="dbforms2_common.openFileBrowser(dbforms2_globalObj['{@id}'])" value="..."/>
                <xsl:if test="1 or @isImage = '1'">
                    <span id="{@id}_previewLarge" class="pic">
                        <img id="{@id}_previewSmall" src="{$DBFORMS2_IMG_NULLIMG}" border="0"/>
                    </span>
                </xsl:if>

            </td>
        </tr>
    </xsl:template>

</xsl:stylesheet>
