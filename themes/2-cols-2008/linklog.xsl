<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
	xmlns:php="http://php.net/xsl"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:rss="http://purl.org/rss/1.0/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:blog="http://bitflux.org/doctypes/blog"
	xmlns:bxf="http://bitflux.org/functions"
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
	xmlns="http://www.w3.org/1999/xhtml"
	exclude-result-prefixes="xhtml rdf rss dc php blog">
	<xsl:import href="master.xsl" />
	<xsl:import href="../standard/common.xsl" />
	<xsl:output encoding="utf-8" method="xml" />
	<xsl:variable name="fixedFilename"
		select="php:functionString('bx_helpers_string::spacesToPlus', $filename)" />
	<!-- 
		if you want some "personal data" here:
		add this to namespace:
		xmlns:php="http://php.net/xsl"
		
		get e.g. the username 
		<xsl:variable name="username">
		<xsl:value-of select="php:functionString('bx_helpers_perm::getUsername')"/>
		</xsl:variable>	
		
		call as
		<xsl:value-of select="$username"/>
	-->


	<!-- 	this might be used somewhere, 
		also shows howto set a variable within xsl 
	-->
	<xsl:variable name="currenttag">
		<xsl:value-of
			select="/bx/plugin[@name='linklog']/links/meta/title" />
	</xsl:variable>

	<!-- <title>-tag in head: -->
	<xsl:template name="html_head_title">
		<xsl:if test="/bx/plugin[@name='linklog']/links/meta/title !=''">
		<xsl:apply-templates select="/bx/plugin[@name='linklog']/links/meta/title" />
		|
		</xsl:if>
		<xsl:value-of select="$sitename" />
	</xsl:template>

	<!-- 
		overwriting default template for content:
	-->
	<xsl:template name="content">
		<!-- Titel: -->
		<xsl:apply-templates
			select="/bx/plugin[@name='linklog']/links/meta" mode="meta" />

		<!-- Links: -->
		<xsl:apply-templates
			select="/bx/plugin[@name='linklog']/links/link" mode="links" />

		<xsl:apply-templates disable-output-escaping="yes"
			select="/bx/plugin[@name='linklog']/links/pager" mode="pager" />


	</xsl:template>
	
	<xsl:template match="pager" mode="pager">
		<div class="blog_pager">
			<span class="right">
				<xsl:if test="prev/@href != ''">
				<!-- check if there really is a pref/next -->
				<a href="{prev/@href}">
					<i18n:text>Prev</i18n:text>
				</a>
				</xsl:if>
				
				<xsl:if test="next/@href != ''">
				<a href="{next/@href}">
					<i18n:text>Next</i18n:text>
				</a>
				</xsl:if>
				
			</span>
			<xsl:value-of select="current" /> /
			<xsl:value-of select="total" />
		
		</div>
	</xsl:template>
	

	<!-- Construct the Heading of the title: -->
	<xsl:template match="meta" mode="meta">
		<h1>
			<xsl:value-of select="title" />
		</h1>
		<!-- this is not yet used, having a description for each tag
			<p>
			<xsl:value-of select="description" />
			</p>
		-->
	</xsl:template>

	<!-- here is the template for a single link -->

	<xsl:template match="link" mode="links">
		<!--		<div class="linkmanager_single_link"> -->
		<h2 class="post_title">
			<a href="{url}">
				<xsl:value-of select="title" />
			</a>
		</h2>

		<div class="post_meta_data">
			<span class="right">
				<xsl:call-template name="editlink" />
				<xsl:call-template name="linktags" />
			</span>
			<xsl:value-of select="archive"
				disable-output-escaping="yes" />
			<xsl:value-of select="substring(time, 12, 5)"
				disable-output-escaping="yes" />
		</div>
		<div class="post_content">
			<p>
				<xsl:value-of select="description" />
			</p>
			<div class="post_links">

				<a href="{url}">
					<xsl:value-of select="substring(url, 0, 80)" />
				</a>
				<!-- <br />
					
					Permalink -->
			</div>
		</div>


	</xsl:template>
	<xsl:template name="linktags">
		Tags:&#160;
		<xsl:for-each select="tags/tag">
			<a href="{fulluri}" rel="tag">
				<xsl:value-of select="name" />
			</a>
			,
		</xsl:for-each>
		...
	</xsl:template>

	<xsl:template name="editlink">
		<xsl:choose>
			<xsl:when test="edituri != ''">
				[
				<a href="{edituri}">edit</a>
				-
			</xsl:when>
		</xsl:choose>

		<xsl:choose>
			<xsl:when test="deleteuri != ''">
				<!-- here i shoud add a javascript warning -->
				<a href="{deleteuri}">delete</a>
				] &#160;
			</xsl:when>
		</xsl:choose>

	</xsl:template>
	<!-- This is the navigation, calls it's own plugin -->
	<xsl:template name="leftnavi">
		<div id="left">

			<!--		<xsl:value-of select="$filename"></xsl:value-of> 
				<xsl:value-of select="$fixedFilename"></xsl:value-of> -->

			<!--  to see output of this, just call 
				http://localhost/linklog/plugin=tags(yourtag/index.html).xml?XML=1
				or just 
				http://localhost/linklog/plugin=tags().xml?XML=1
				but be sure you are logged in ;)
			-->
			<xsl:apply-templates
				select="document(concat('portlet://',$collectionUri,'plugin=tags(',$fixedFilename,').xml'))/bx/plugin/collection" />

			<!-- this is not done yet - want an image only for current page, too -->
			<p>
				<a href="{$collectionUri}rss.xml"
					title="linklog as rss">
					<img src="/themes/2-cols/buttons/rss.png" border="0"
						alt="RSS Icon">
					</img>
				</a>
			</p>

			<!--   	     <xsl:call-template name="delicious" />    -->

		</div>
	</xsl:template>


	<!-- 
		this does not quite work out yet, but the idea is 
		to fetch links from del.icio.us tagged identical:   
		
		<xsl:template name="delicious">
		<h3 class="blog">
		<a href="http://del.icio.us/tag/freeflux">
		del.icio.us/tag/<xsl:value-of select="$currenttag"/>
		</a>
		</h3>
		<ul>
		<xsl:for-each select="document(concat('portlet://blog/plugin=deliciousrdf(tag/bitflux).xml'))/bx/plugin/rdf:RDF/rss:item[position() &lt; 11]">
		<li>
		<a title="{rss:description} - Categories: {dc:subject}" class="blogLinkPad" href="{rss:link}">
		<xsl:value-of select="rss:title"/>
		</a>
		</li>
		</xsl:for-each>
		</ul>
		</xsl:template>
	-->
</xsl:stylesheet>
