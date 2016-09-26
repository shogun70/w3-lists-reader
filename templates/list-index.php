<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>public-houdini@w3.org Mail Archives</title>
<link rel="help" href="/Help/" />
<link rel="contents" title="List of public mailing lists" href="../" />
<link rel="stylesheet" href="//www.w3.org/StyleSheets/Mail/public-mainindex" />
<link rel="alternate" type="application/rss+xml" title="RSS 1.0" href="feed.rss" />
</head>
<body>
    <div class="head">
      <map title="Navigation bar to upper levels" id="upper"
      name="upper">
        <p>
			<a href="//www.w3.org/" title="W3C home">W3C home</a> &gt; 
			<a href="../">Public Mailing lists</a>
		</p>
      </map> 

<h1><?= $list_name ?> Mail Archives</h1>
<div class="header">
<p>This mailing list serves as the discussion forum for the Houdini joint task force between CSS WG and TAG on the design of styling APIs. </p>
<p>the official wiki for this TF can be found at <a href="https://wiki.css-houdini.org">https://wiki.css-houdini.org</a>.</p>

</div><!--class=header-->
      <map title="Navigation bar" id="navbar" name="navbar">
        <ul>
       </ul>
      </map>
    </div>
<?php 
	$month_strings = preg_split('/\s+/', <<<'END'
January February March April May June July August September October November December
END
	, 12);
	$month_names = [];
	foreach ($month_strings as $i => $name) {
		$index = "" . ($i + 1);
		$month_names[$index] = $name;
	}
?>
<table border="0" cellspacing="3" cellpadding="2"><!-- FIXME use CSS -->

 <thead>
   <tr>
     <th>Year</th>
	<?php foreach ($month_names as $name): ?>
     <th title="<?= $name ?>"><?= substr($name, 0, 3) ?></th>
	<?php endforeach; ?>
   </tr>
</thead>
<tbody>

<?php foreach ($years as $year => $months): ?>
<tr>
<th><?= $year ?></th>

<?php foreach ($months as $i => $results): ?>
<?php $messages = $results['messages']; $threads = $results['threads']; ?>
<td class="<?= $results['position'] ?>">

<?php if ($messages && $messages['count']): ?>
<strong class="messages">
<a href="<?= $messages['path'] ?>" title="<?= $messages['count'] ?> messages">
<?= $messages['count'] ?>
</a>
</strong>
<?php endif; ?>

<?php if ($threads && $threads['count']): ?>
<em class="threads">
<span>(</span>
<a href="<?= $threads['path'] ?>" title="<?= $threads['count'] ?> new threads">
<?= $threads['count'] ?>
</a>
<span>)</span>
</em>
<?php endif; ?>

</td>
<?php endforeach; ?>

</tr>
<?php endforeach; ?>

</tbody>

</table>
<div class="footer">

</div><!--class=footer-->
<hr />
<address>
<a href="//www.w3.org/Help/Webmaster">Webmaster</a><!--a href="mailto:team-lists-torimochi@w3.org">do not post to me</a--><br />
Last update on: Thu Sep 22 16:19:34 2016
</address>
</body></html>
