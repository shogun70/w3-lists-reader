<xsl:stylesheet 
	version="1.0" 
	exclude-result-prefixes="xsl str"
	xmlns="http://www.w3.org/2005/Atom"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:str="http://exslt.org/strings"
	xmlns:atom="http://www.w3.org/2005/Atom">

<xsl:output 
	method="xml" 
	indent="yes"
	encoding="UTF-8"
	omit-xml-declaration="yes" />

<xsl:param name="home_url">./</xsl:param>
<xsl:param name="home_title" />

<xsl:template match="/">
	<xsl:apply-templates select="//html:body" />
</xsl:template>

<xsl:template match="html:body">
	<xsl:param name="home_title" select="html:h1" />
<feed>
	<title><xsl:value-of select="$home_title" /></title>
	<link rel="alternate directory" type="text/html" href="{$home_url}" title="{$home_title}" />

	<xsl:apply-templates select="html:ul[1]/html:li" />
</feed>
</xsl:template>

<xsl:template match="html:li">
	<xsl:param name="self_title" select="html:a[1]" />
	<xsl:param name="self_url" select="html:a[1]/@href" />
	<xsl:param name="directory" select="ancestor::html:li" />
	<xsl:param name="directory_title" select="$directory/html:a[1]" />
	<xsl:param name="directory_url" select="$directory/html:a[1]/@href" />
	<xsl:param name="prev" select="preceding-sibling::html:li[1]" />
	<xsl:param name="prev_title" select="$prev/html:a[1]" />
	<xsl:param name="prev_url" select="$prev/html:a[1]/@href" />
	<xsl:param name="next" select="following-sibling::html:li[1]" />
	<xsl:param name="next_title" select="$next/html:a[1]" />
	<xsl:param name="next_url" select="$next/html:a[1]/@href" />
	<entry>
		<title><xsl:value-of select="$self_title" /></title>
		<link rel="self" href="{$self_url}" title="{$self_title}" />
		<link rel="home" href="{$home_url}" title="{$home_title}" />
<xsl:if test="$directory">
		<link rel="directory" href="{$directory_url}" title="{$directory_title}" />
</xsl:if>
<xsl:if test="$prev">
		<link rel="prev" href="{$prev_url}" title="{$prev_title}" />
</xsl:if>
<xsl:if test="$next">
		<link rel="next" href="{$next_url}" title="{$next_title}" />
</xsl:if>
	</entry>

	<xsl:apply-templates select="html:ul/html:li" />

</xsl:template>

</xsl:stylesheet>
