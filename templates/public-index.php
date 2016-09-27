<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>W3C Public Mailing List Archives</title>
	<link rel="stylesheet" href="/StyleSheets/base.css" type="text/css">
	<link rel="stylesheet" href="/StyleSheets/home.css" type="text/css">
	<link rel="Stylesheet" href="/StyleSheets/Mail/public-mainindex.css">
</head>

<body>


<header>
<p><a href="//www.w3.org">
<img src="//www.w3.org/Icons/w3c_home" alt="W3C" title="The world Wide Web Consortium" width="72" height="48"></a></p>
<h1><acronym title="the World Wide Web Consortium">W3C</acronym>
Public Mailing List Archives</h1>
</header>


<main>
<dl>
	<?php foreach ($lists as $list): ?>
	<dt id="<?= $list['name'] ?>">
		<a href="<?= $list['path'] ?>"><?= $list['name'] ?></a>
	</dt>
	<dd><?= $list['details'] ?></dd>
	<?php endforeach; ?>
</dl>
</main>


<footer>
<p>
<a href="http://validator.w3.org/check/referer"></a> 
</p>

<p class="policyfooter">
<a href="https://www.w3.org/Consortium/Legal/ipr-notice.html">Copyright</a> 
© 1994-2016 <a href="https://www.w3.org">W3C</a> (<a href="http://www.csail.mit.edu">MIT</a>,
<a href="http://www.ercim.eu/">ERCIM</a>,
<a href="http://www.keio.ac.jp/">Keio</a>, 
<a href="http://ev.buaa.edu.cn/">Beihang</a>), All Rights Reserved. W3C <a href="https://www.w3.org/Consortium/Legal/ipr-notice.html">liability,</a> <a href="https://www.w3.org/Consortium/Legal/ipr-notice.html">trademark</a>, <a href="https://www.w3.org/Consortium/Legal/copyright-documents.html">document use</a> 
and 
<a href="https://www.w3.org/Consortium/Legal/copyright-software.html">software
licensing</a> rules apply. Your interactions with this site are in accordance
with our <a href="https://www.w3.org/Consortium/Legal/privacy-statement.html#Public">Public</a>
and <a href="https://www.w3.org/Consortium/Legal/privacy-statement.html#Members">Member</a>
privacy statements.</p>
<!-- a href="mailto:public-lists-potter@w3.org">do not use this address.</a--> 
</footer>

</body>
</html>





