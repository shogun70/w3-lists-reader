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

<xsl:template match="//*[@id='navbar']">
</xsl:template>

<xsl:template match="//*[@id='navbarfoot']">
</xsl:template>

<xsl:template match="//*[@id='upper']/p">
	<xsl:copy>
		<xsl:apply-templates select="@*"/>
		<xsl:apply-templates select="a[contains(., 'home')]" />
		&gt;
		<xsl:apply-templates select="a[contains(., 'Public')]" />
		&gt;
		<xsl:apply-templates select="a[contains(@title, 'Index')]" />
	</xsl:copy>
</xsl:template>

</xsl:stylesheet>
