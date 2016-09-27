<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= $list['name'] ?>@w3.org Mail Archives</title>
<link rel="stylesheet" href="/StyleSheets/Mail/public-mainindex.css" />
</head>
<body>
    <nav title="Navigation bar to upper levels" class="upper">
        <p>
			<a href="//www.w3.org/" title="W3C home">W3C home</a> &gt; 
			<a href="../">Public Mailing lists</a>
		</p>
      </nav> 

<header>

		<h1><?= $list['name'] ?> Mail Archives</h1>
		<?= $list['details'] ?>
</header>

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

<footer>
</footer>

</body></html>
