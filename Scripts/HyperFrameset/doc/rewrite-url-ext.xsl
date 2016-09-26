<!DOCTYPE html>
<xsl:stylesheet 
	version="1.0" 
	exclude-result-prefixes="#default html xsl"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	extension-element-prefixes="func str"
	xmlns:func="http://exslt.org/functions"
	xmlns:str="http://exslt.org/strings">

<xsl:output 
	method="xml" 
	indent="yes"
	encoding="UTF-8"
	omit-xml-declaration="yes" />

<xsl:param name="src" />
<xsl:param name="dst" />

<xsl:template match="@* | node()">
	<xsl:copy>
		<xsl:apply-templates select="@* | node()" />
	</xsl:copy>
</xsl:template>

<xsl:template match="html:*/@*[name()='href' or name()='src']">
	<xsl:attribute name="{name()}">
		<xsl:value-of select="str:replace(., concat('.', $src), concat('.', $dst))" />
	</xsl:attribute>
</xsl:template>

</xsl:stylesheet>
