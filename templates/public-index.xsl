<xsl:stylesheet 
	version="1.0" 
	exclude-result-prefixes="#default html xsl atom"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:atom="http://www.w3.org/2005/Atom">

<xsl:output 
	method="html" 
	encoding="UTF-8"
	omit-xml-declaration="yes" />

<xsl:template match="/ | @* | node()">
	<xsl:copy>
		<xsl:apply-templates select="@* | node()" />
	</xsl:copy>
</xsl:template>

<!-- remove inline-styles -->
<xsl:template match="style | @style">
</xsl:template>

<!-- remove embedded content -->
<xsl:template match="img | video | audio | iframe | object | embed">
</xsl:template>

<!-- except for the w3c logo -->
<xsl:template match="img[@alt='W3C']">
	<xsl:copy>
		<xsl:apply-templates select="@*" />
	</xsl:copy>
</xsl:template>

<!-- pretty-print -->
<xsl:template match="html | head | body">
<xsl:text>
</xsl:text>
	<xsl:copy>
		<xsl:apply-templates select="@* | node()" />
	</xsl:copy>
<xsl:text>
</xsl:text>
</xsl:template>

<xsl:template match="head/*">
	<xsl:text>
	</xsl:text>
	<xsl:copy>
		<xsl:apply-templates select="@* | node()" />
	</xsl:copy>
</xsl:template>

<xsl:template match="dt">
	<xsl:copy>
		<xsl:apply-templates select="@* | a[1]" />
	</xsl:copy>
</xsl:template>

<xsl:template match="*[@id='about']">
</xsl:template>

<xsl:template match="*[@id='info']">
</xsl:template>

<xsl:template match="*[@id='info-search']">
</xsl:template>

<xsl:template match="*[./*[@id='lists']]">
</xsl:template>


<xsl:template match="address">
</xsl:template>

</xsl:stylesheet>
