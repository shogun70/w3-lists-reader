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

<xsl:param name="entry" />

<xsl:template match="@*|node()">
	<xsl:copy>
		<xsl:apply-templates select="@*|node()" />
	</xsl:copy>
</xsl:template>

<xsl:template match="atom:feed">
	<xsl:copy>
		<xsl:apply-templates select="@*|node()" />
		<xsl:apply-templates select="document($entry)//atom:entry" />
	</xsl:copy>
</xsl:template>

</xsl:stylesheet>
