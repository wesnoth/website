<?php

include('config.php');
include('functions.php');
include('langs.php');

global $langs;
global $branch;

$existing_packs         = explode(' ', $packages);
$existing_corepacks     = explode(' ', $corepackages);
$existing_extra_packs_t = explode(' ', $extratpackages);
$existing_extra_packs_b = explode(' ', $extrabpackages);

sort($existing_extra_packs_t);
sort($existing_extra_packs_b);

$stats = [];

// FIXME: not the same default as index.php for some reason?
$version = isset($_GET['version']) ? parameter_get('version') : 'master';

$lang = isset($_GET['lang']) ? parameter_get('lang') : '';

if ($lang != '')
{
	$j = 0;

	for ($i = 0; $i < 2; ++$i)
	{
		if ($i == 0)
		{
			$packs = $existing_packs;
		}
		else
		{
			$packs = ($version == 'master') ? $existing_extra_packs_t : $existing_extra_packs_b;
		}

		foreach ($packs as $pack)
		{
			if ($i == 1)
			{
				$pack = getdomain($pack);
			}

			$statsfile = $version . 'stats';

			if (!file_exists('stats/' . $pack . '/' . $statsfile))
			{
				continue;
			}

			$serialized = file_get_contents('stats/' . $pack . '/' . $statsfile);
			$tmpstats = unserialize($serialized);

			$stat = $tmpstats[$lang];
			$stats[$j] = [];
			$stats[$j][0] = $stat[0];	//errors
			$stats[$j][1] = $stat[1];	//translated
			$stats[$j][2] = $stat[2];	//fuzzy
			$stats[$j][3] = $stat[3];	//untranslated
			$stats[$j][4] = $pack;		//package name
			$stats[$j][5] = $tmpstats['_pot'][1] + $tmpstats['_pot'][2] + $tmpstats['_pot'][3];
			$stats[$j][6] = $i;			//official

			++$j;
		}
	}

	$nostats = 0;
}
else
{
	$nostats = 1;
	unset($lang);
}

$firstpack = $existing_packs[0];
$statsfile = $version . 'stats';
$filestat = stat('stats/' . $firstpack . '/' . $statsfile);
$date = $filestat[9];

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="content-type" content="text/xhtml; charset=utf-8" />
	<link rel="shortcut icon" type="image/png" href="/mw/skins/glamdrol/ico.png" />
	<link rel="stylesheet" href="/mw/skins/glamdrol/main.css" />
	<link rel="stylesheet" type="text/css" href="styles/old.css" />
	<title>Translation statistics - Battle for Wesnoth</title>
</head>

<body>

<div id="global">

<div id="header">
	<div id="logo">
		<a href="/"><img alt="Wesnoth logo" src="/mw/skins/glamdrol/wesnoth-logo.jpg" /></a>
	</div>
</div>

<div id="nav">
	<ul>
		<li><a href="/">Home</a></li>
		<li><a href="//wiki.wesnoth.org/Play">Play</a></li>
		<li><a href="//wiki.wesnoth.org/Create">Create</a></li>
		<li><a href="//forums.wesnoth.org/">Forums</a></li>
		<li><a href="//wiki.wesnoth.org/Support">Support</a></li>
		<li><a href="//wiki.wesnoth.org/Project">Project</a></li>
		<li><a href="//wiki.wesnoth.org/Credits">Credits</a></li>
		<li><a href="//wiki.wesnoth.org/UsefulLinks">Links</a></li>
	</ul>
</div>

<h2 style="display:inline">Wesnoth translation stats</h2>
(last update: <strong><?php echo date('r', $date) ?></strong>)

<table class="main" cellpadding="1" cellspacing="0" border="0" width="100%"><tr><td>

<table class="title" cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td>

<table cellpadding="2" cellspacing="0" border="0" width="100%"><!-- language and package selection -->
	<tr>
		<td align="left">Version:<?php
			if ($version == 'master')
			{
				?><strong>Development</strong> || <a href="?version=branch&amp;package=<?php echo $package ?>&amp;lang=<?php echo $lang ?>"><?php echo $branch ?></a><?php
			}
			else
			{
				?><a href="?version=master&amp;package=<?php echo $package ?>&amp;lang=<?php echo $lang ?>">Development</a>  || <strong><?php echo $branch ?></strong><?php
			}
		?></td>
	</tr>
	<tr>
		<td align="left">Show:
			<a href="index.php?package=alloff&amp;order=trans&amp;version=<?php echo $version ?>">Official packages</a>
			|| <a href="index.php?package=allcore&amp;order=trans&amp;version=<?php echo $version ?>">Official core packages</a>
			|| <a href="index.php?package=all&amp;order=trans&amp;version=<?php echo $version ?>">All packages</a>
			|| <a href="index.php?package=allun&amp;order=trans&amp;version=<?php echo $version ?>">All unofficial packages</a>
			|| <strong>By language</strong>
			<br/>Language:<?php
			$first = true;
			foreach ($langs as $code => $langname)
			{
				if ($first)
				{
					$first = false;
				}
				else
				{
					echo "||";
				}

				if ($code == $lang)
				{
					?><strong><?php echo $langname ?></strong><?php
				}
				else
				{
					?><a href="?lang=<?php echo $code ?>&amp;version=<?php echo $version ?>"><?php echo $langname ?></a> <?php
				}
			}
		?></td>
	</tr>
</table><!-- language and package selection -->
</td></tr></table>
</td></tr></table>

<!-- FIXME WHY -->
<div> <br/> </div><?php

if (!$nostats)
{
	?><table class="main" cellspacing="0" cellpadding="0" border="0" width="100%"><tr><td>
	<table cellspacing="1" cellpadding="2" border="0" width="100%">
	<tr class="header">
		<td class="title">package</td>
		<td class="translated">translated</td>
		<td class="translated">%</td>
		<td class="fuzzy"><strong>fuzzy</strong></td>
		<td class="fuzzy"><strong>%</strong></td>
		<td class="untranslated"><strong>untranslated</strong></td>
		<td class="untranslated"><strong>%</strong></td>
		<td class="title">total</td>
		<td class="title">graph</td>
	</tr><?php

	$i=0;
	$sumstat[1]=0;
	$sumstat[2]=0;
	$sumstat[3]=0;
	$sumstat[5]=0;

	$official = 0;		//0 == official, 1 == not official

	foreach ($stats as $stat)
	{
		$oldofficial = $official;
		$official = $stat[6];

		$sumstat[1] += $stat[1];
		$sumstat[2] += $stat[2];
		$sumstat[3] += $stat[3];
		$sumstat[5] += $stat[5];

		$total = $stat[1] + $stat[2] + $stat[3];

		$class = '-' . ($i % 2);

		if ($oldofficial != $official)
		{
			?><tr><td></td></tr><?php
		}

		?><tr class="row<?php echo $class ?>">
			<td><?php
				# FIXME-GIT: this will need to be updated
				if ($official == 0)
				{
					$repo = ($version == 'master') ? 'master' : $branch;
					echo "<strong><a href='https://raw.github.com/wesnoth/wesnoth/$repo/po/" . $stat[4]. "/$lang.po'>" . $stat[4] . '</a></strong>';
				}
				else
				{
					$packname = getpackage($stat[4]);
					$repo = ($version == 'master') ? $wescamptrunkversion : $wescampbranchversion;
					$reponame = "$packname-$repo";
					echo "<strong><a href='https://raw.github.com/wescamp/$reponame/master/po/$lang.po'>" . $stat[4] . '</a></strong>';
				}
			?></td><?php

			if (($stat[0] == 1) || ($total == 0) || ($stat[5] == 0))
			{
				?><td colspan="8">Error in <?php echo $stat[4] ?> translation files</td><?php
			}
			else
			{
				?><td align="right"><?php echo $stat[1] ?></td>
				<td class="percentage<?php echo $class ?>" align="right"><?php printf("%0.2f", ($stat[1]*100)/$stat[5]) ?></td>
				<td align="right"><?php echo $stat[2] ?></td>
				<td class="percentage<?php echo $class ?>" align="right"><?php printf("%0.2f", ($stat[2]*100)/$stat[5]) ?></td>
				<td align="right"><?php echo ($stat[5] - $stat[1] - $stat[2]) ?></td>
				<td class="percentage<?php $class ?>" align="right"><?php printf("%0.2f", (($stat[5]-$stat[1]-$stat[2])*100)/$stat[5]) ?></td>
				<td align="right"><?php echo $total ?></td><?php

				$trans = sprintf("%d", ($stat[1]*200)/$stat[5]);
				$fuzzy = sprintf("%d", ($stat[2]*200)/$stat[5]);
				$untrans = 200 - $trans - $fuzzy;

				?><td><img src="images/green.png" height="15" width="<?php echo $trans ?>" alt="translated"/><img src="images/blue.png" height="15" width="<?php echo $fuzzy ?>" alt="fuzzy"/><img src="images/red.png" height="15" width="<?php echo $untrans ?>" alt="untranslated"/></td><?php
			}

		?></tr><?php

		++$i;
	}
	?><tr class="title">
		<td>Total</td>
		<td align="right"><?php echo $sumstat[1] ?></td>
		<td></td>
		<td align="right"><?php echo $sumstat[2] ?></td>
		<td></td>
		<td align="right"><?php echo $sumstat[3] ?></td>
		<td></td>
		<td align="right"><?php echo $sumstat[5] ?></td>
		<td></td>
	</tr>
</table>
</td>
</tr>
</table><?php
}
else
{
	if (isset($lang))
	{
		?><h2>No available stats for lang <?php echo $lang ?></h2><?php
	}
}
?><div> <br/> </div>
<div id="footer">
<div id="footnote">
&copy; 2003&#8211;2016 The Battle for Wesnoth
</div>
</div>
</div>

</body>
</html>
