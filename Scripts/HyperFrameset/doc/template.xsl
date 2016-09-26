<xsl:stylesheet 
	version="1.0" 
	exclude-result-prefixes="#default html xsl atom"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:atom="http://www.w3.org/2005/Atom">

<xsl:output 
	method="html"
	indent="yes"
	encoding="UTF-8"
	omit-xml-declaration="yes" />

<xsl:param name="frameset" />
<xsl:param name="bootscript" />
<xsl:param name="stylesheet" />
<xsl:param name="uri" />
<xsl:param name="index_doc" />

<xsl:template match="/">
	<xsl:apply-templates select="atom:feed[1]" />
</xsl:template>

<xsl:template match="atom:feed">
	<xsl:apply-templates select="atom:entry[1]" />
</xsl:template>

<xsl:template match="atom:entry">
<xsl:param name="entry" select="." />
<xsl:param name="index" select="document($index_doc)" />
<xsl:param name="index_entry" select="$index//atom:entry[atom:link[@rel='self'][1]/@href = $uri][1]" />
<xsl:param name="prev" select="$index_entry/atom:link[@rel='prev']" />
<xsl:param name="next" select="$index_entry/atom:link[@rel='next']" />
<html>
<head>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
<meta content="initial-scale=1.0, width=device-width" name="viewport" />
<script src="{$bootscript}"></script>
<link href="{$frameset}" rel="frameset" />
<link href="{$stylesheet}" rel="stylesheet" />
<title><xsl:value-of select="atom:title" /></title>
</head>
<body>
<nav role="navigation">
Location: 
<xsl:for-each select="$index_entry/atom:link[contains(@rel,'home')]">
<a href="{@href}" title="Home" rel="{@rel}"><xsl:value-of select="$index/atom:feed/atom:title" /></a> / 
</xsl:for-each>
<xsl:for-each select="$index_entry/atom:link[contains(@rel,'directory')]">
<a href="{@href}" rel="{@rel}"><xsl:value-of select="@title" /></a> / 
</xsl:for-each>
<xsl:for-each select="$index_entry/atom:link[contains(@rel,'self')][1]">
<a href="{@href}" rel="{@rel}"><xsl:value-of select="@title" /></a>
</xsl:for-each>
<br />
<xsl:if test="$prev">
Previous: <a href="{$prev/@href}" title="Previous"><xsl:value-of select="$prev/@title" /></a>
</xsl:if>
<xsl:if test="$prev and $next">
, 
</xsl:if>
<xsl:if test="$next">
Next: <a href="{$next/@href}" title="Next"><xsl:value-of select="$next/@title" /></a>
</xsl:if>
</nav>
<main id="mk_main" role="main">
<xsl:copy-of select="atom:content/html:div/*" />
</main>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
