<!DOCTYPE html>
<xsl:stylesheet 
	xml:space="preserve"
	version="1.0" 
	exclude-result-prefixes="#default html xsl atom"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:str="http://exslt.org/strings"
	xmlns:atom="http://www.w3.org/2005/Atom">

<xsl:output 
	method="html" 
	encoding="UTF-8"
	omit-xml-declaration="yes" />

<xsl:template match="/">
	<xsl:apply-templates select="//body" />
</xsl:template>

<xsl:template match="body">
<html>
<head>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
<meta content="initial-scale=1.0, width=device-width" name="viewport" />
<title>Index</title>
</head>
<body>
<nav role="navigation">
</nav>
<main id="mk_main" role="main">
	<ul>
<xsl:for-each select="str:tokenize(.)">
		<li>
			<a href="./{str:replace(., '.md', '.html')}">
<xsl:value-of select="str:replace(str:replace(., '.md', ''), '-', ' ')" />
			</a>
		</li>
</xsl:for-each>
	</ul>
</main>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
