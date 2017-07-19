<?php

include('config.php');
include('functions.php');
include('langs.php');

global $langs;

function cmp_translated($a, $b)
{
	if ($a[1] == $b[1])
	{
		if ($a[2] == $b[2])
		{
			return 0;
		}

		return ($a[2] < $b[2]) ? 1 : -1;
	}

	return ($a[1] < $b[1]) ? 1 : -1;
}

function cmp_alpha($a, $b)
{
	global $langs;

	return strcmp($langs[$a], $langs[$b]);
}

$official = true;
$nostats = false;

$existing_packs         = explode(' ', $packages);
$existing_corepacks     = explode(' ', $corepackages);
$existing_extra_packs_t = explode(' ', $extratpackages);
$existing_extra_packs_b = explode(' ', $extrabpackages);

$firstpack = $existing_packs[0];

$stats = [];

$package = isset($_GET['package']) ? parameter_get('package') : 'alloff';

// Set the default starting point when calling gettext.wesnoth.org:
//   'branch': show stats from the current stable branch
//   'master': show stats from master
$version = isset($_GET['version']) ? parameter_get('version') : 'branch';

$order = (!isset($_GET['order']) || $_GET['order'] != 'alpha')
         ? 'trans' : 'alpha';

switch ($package)
{
	case 'alloff':
	case 'allcore':
		$packs = ($package == 'alloff') ? $existing_packs : $existing_corepacks;

		foreach ($packs as $pack)
		{
			$statsfile = ($version == 'branch') ? 'branchstats' : 'masterstats';

			if (!file_exists('stats/' . $pack . '/' . $statsfile))
			{
				continue;
			}

			$serialized = file_get_contents('stats/' . $pack . '/' . $statsfile);
			$tmpstats = unserialize($serialized);

			foreach ($tmpstats as $lang => $stat)
			{
				if (isset($stats[$lang]))
				{
					$stats[$lang][0] += $stat[0];
					$stats[$lang][1] += $stat[1];
					$stats[$lang][2] += $stat[2];
					$stats[$lang][3] += $stat[3];
				}
				else
				{
					$stats[$lang] = array_slice($stat, 0, 4);
				}
			}
		}

		break;

	case 'all':
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
				$statsfile = ($version == 'branch') ? 'branchstats' : 'masterstats';

				if ($i == 1)
				{
					$pack = getdomain($pack);
				}

				if (!file_exists('stats/' . $pack . '/' . $statsfile))
				{
					continue;
				}

				$serialized = file_get_contents('stats/' . $pack . '/' . $statsfile);
				$tmpstats = unserialize($serialized);

				foreach ($tmpstats as $lang => $stat)
				{
					if (isset($stats[$lang]))
					{
						$stats[$lang][0] += $stat[0];
						$stats[$lang][1] += $stat[1];
						$stats[$lang][2] += $stat[2];
						$stats[$lang][3] += $stat[3];
					}
					else
					{
						$stats[$lang] = array_slice($stat, 0, 4);
					}
				}
			}
		}
		break;

	case 'allun':
		$packs = ($version == 'master') ? $existing_extra_packs_t : $existing_extra_packs_b;
		foreach ($packs as $pack)
		{
			$pack = getdomain($pack);
			$statsfile = $version . 'stats';

			if (!file_exists('stats/' . $pack . '/' . $statsfile))
			{
				continue;
			}

			$serialized = file_get_contents('stats/' . $pack . '/' . $statsfile);
			$tmpstats = unserialize($serialized);

			foreach ($tmpstats as $lang => $stat)
			{
				if (isset($stats[$lang]))
				{
					$stats[$lang][0] += $stat[0];
					$stats[$lang][1] += $stat[1];
					$stats[$lang][2] += $stat[2];
					$stats[$lang][3] += $stat[3];
				}
				else
				{
					$stats[$lang] = [];
					$stats[$lang][0] = $stat[0];
					$stats[$lang][1] = $stat[1];
					$stats[$lang][2] = $stat[2];
					$stats[$lang][3] = $stat[3];
				}
			}
		}
		break;

	default:
		$package = parameter_get('package');
		$statsfile = $version . 'stats';

		if (!file_exists('stats/' . $package . '/' . $statsfile))
		{
			$nostats = true;
		}
		else
		{
			$serialized = file_get_contents('stats/' . $package . '/' . $statsfile);
			$stats = unserialize($serialized);
		}
}

if (!$nostats)
{
	// get total number of strings
	$main_total = $stats['_pot'][1] + $stats['_pot'][2] + $stats['_pot'][3];
	unset($stats['_pot']);
	$statsfile = $version . 'stats';
	$filestat = stat('stats/' . $firstpack . '/' . $statsfile);
	$date = $filestat[9];

	if ($order == 'trans')
	{
		uasort($stats, 'cmp_translated');
	}
	else
	{
		uksort($stats, 'cmp_alpha');
	}
}

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

<h2 style="display:inline">Wesnoth translation stats</h2><?php

if (!$nostats)
{
	?>(last update: <strong><?php echo date('r', $date) ?></strong>)<?php
}

?>
<table class="main" cellpadding="1" cellspacing="0" border="0" width="100%"><tr><td>

<table class="title" cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td>

<table cellpadding="2" cellspacing="0" border="0" width="100%"><!-- display order and package selection -->
	<tr>
		<td align="left">Order by:<?php
			if ($order == 'trans')
			{
				?><strong># of translated strings</strong> || <a href="?order=alpha&amp;package=<?php echo $package ?>">Team name</a><?php
			}
			else
			{
				?><a href="?order=trans&amp;package=<?php echo $package ?>"># of translated strings</a> || <strong>Team name</strong><?php
			}
		?></td>
	</tr>
	<tr>
		<td align="left">Version:<?php
			if ($version == 'branch')
			{
				?><a href="?version=master&amp;package=<?php echo $package ?>">Development</a> || <strong><?php echo $branch ?></strong><?php
			}
			else
			{
				?><strong>Development</strong> || <a href="?version=branch&amp;package=<?php echo $package ?>"><?php echo $branch ?></a><?php
			}
		?></td>
	</tr>
	<tr>
		<td align="left">Show:<?php
			if ($package == 'alloff')
			{
				?><strong>All official packages</strong><?php
			}
			else
			{
				?><a href="?package=alloff&amp;order=<?php echo $order ?>&amp;version=<?php echo $version ?>">All official packages</a><?php
			}

			echo ' || ';

			if ($package == 'allcore')
			{
				?><strong>Official core packages</strong><?php
			}
			else
			{
				?><a href="?package=allcore&amp;order=<?php echo $order ?>&amp;version=<?php echo $version ?>">Official core packages</a><?php
			}

			echo ' || ';

			if ($package == 'all')
			{
				?><strong>All packages</strong><?php
			}
			else
			{
				?><a href="?package=all&amp;order=<?php echo $order ?>&amp;version=<?php echo $version ?>">All packages</a><?php
			}

			echo ' || ';

			if ($package == 'allun')
			{
				?><strong>All unofficial packages</strong><?php
			}
			else
			{
				?><a href="?package=allun&amp;order=<?php echo $order ?>&amp;version=<?php echo $version ?>">All unofficial packages</a><?php
			}

			echo ' || ';
			?><a href="index.lang.php?version=<?php echo $version ?>">By language</a><?php

			for ($i = 0; $i < 2; ++$i)
			{
				if ($i == 0)
				{
					$packs = $existing_packs;
					echo '<br/>Official: ';
				}
				else
				{
					$packs = ($version == 'master') ? $existing_extra_packs_t : $existing_extra_packs_b;
					echo '<br/>Unofficial: ';
				}

				$first = true;
				foreach ($packs as $pack)
				{
					if ($first)
					{
						$first = false;
					}
					else
					{
						echo '||';
					}

					$packdisplay = $pack;
					if ($i == 1)
					{
						$pack = getdomain($pack);
					}

					if ($pack == $package)
					{
						if ($i == 1)
						{
							$official = false;
						}

						?><strong><?php echo $packdisplay ?></strong><?php
					}
					else
					{
						?><a href="?package=<?php echo $pack ?>&amp;order=<?php echo $order?>&amp;version=<?php echo $version?>"><?php echo $packdisplay ?></a> <?php
					}
				}
			}
		?></td>
	</tr>
</table> <!-- display order and package selection -->
</td></tr></table>
</td></tr></table>

<!-- FIXME WHY -->
<div> <br/> </div><?php

if (!$nostats)
{
	?><table class="main" cellspacing="0" cellpadding="0" border="0" width="100%"><tr><td>
	<table cellspacing="1" cellpadding="2" border="0" width="100%">
	<tr class="header"><?php
	if ($order == 'trans')
	{
		?><td class="title">position</td><?php
	}
	?>
	<td class="title">team name</td>
	<td class="translated">translated</td>
	<td class="translated">%</td>
	<td class="fuzzy"><strong>fuzzy</strong></td>
	<td class="fuzzy"><strong>%</strong></td>
	<td class="untranslated"><strong>untranslated</strong></td>
	<td class="untranslated"><strong>%</strong></td>
	<td class="title">total</td>
	<td class="title">graph</td>
	</tr>
	<?php

	$i = 0;
	$pos = 1;
	$oldstat[0] = 0;
	$oldstat[1] = 0;
	$oldstat[2] = 0;

	foreach ($stats as $lang => $stat)
	{
		$total = $stat[1] + $stat[2] + $stat[3];

		$class = '-' . ($i % 2);
		if (cmp_translated($stat, $oldstat) != 0)
		{
			$pos = $i + 1;
		}

		?><tr class="row<?php echo $class ?>"><?php

		if ($order == 'trans')
		{
			?><td align="right"><?php echo $pos ?></td><?php
		}

		?><td><?php

		if ($package == 'alloff' || $package == 'allun' || $package == 'all' || $package == 'allcore')
		{
			echo "<strong><a href='index.lang.php?lang=$lang&amp;version=$version'>" . $langs[$lang] . '</a></strong> (' . $lang . ')';
		}
		else
		{
			if ($official)
			{
				$repo = ($version == 'master') ? 'master' : $branch;
				echo "<a href='https://raw.github.com/wesnoth/wesnoth/$repo/po/$package/$lang.po'>" . $langs[$lang] . '</a> (' .$lang . ')';
			}
			else
			{
				$packname = getpackage($package);
				$repo = ($version == 'master') ? $wescamptrunkversion : $wescampbranchversion;
				$reponame = "$packname-$repo";
				echo "<a href='https://raw.github.com/wescamp/$reponame/master/po/$lang.po'>" . $langs[$lang] . '</a> (' . $lang . ')';
			}
		}
		?></td><?php

		if (($stat[0] == 1) || ($total == 0))
		{
			?><td colspan="8">Error in <?php echo $langs[$lang] . "($lang)";  ?> translation files</td><?php
		}
		else
		{
			?><td align="right"><?php echo $stat[1] ?></td>
			<td class="percentage<?php echo $class ?>" align="right"><?php printf("%0.2f", ($stat[1]*100)/$main_total); ?></td>
			<td align="right"><?php echo $stat[2] ?></td>
			<td class="percentage<?php echo $class ?>" align="right"><?php printf("%0.2f", ($stat[2]*100)/$main_total); ?></td>
			<td align="right"><?php echo ($main_total - $stat[1] - $stat[2]) ?></td>
			<td class="percentage<?php echo $class ?>" align="right"><?php printf("%0.2f", (($main_total-$stat[1]-$stat[2])*100)/$main_total); ?></td>
			<td align="right"><?php echo $main_total ?></td><?php

			$trans = sprintf("%d", ($stat[1] * 200) / $main_total);
			$fuzzy = sprintf("%d", ($stat[2] * 200) / $main_total);
			$untrans = 200 - $trans - $fuzzy;

			?><td><img src="images/green.png" height="15" width="<?php echo $trans ?>" alt="translated"/><img src="images/blue.png" height="15" width="<?php echo $fuzzy ?>" alt="fuzzy"/><img src="images/red.png" height="15" width="<?php echo $untrans ?>" alt="untranslated"/></td><?php
		}

		?></tr><?php

		++$i;
		$oldstat = $stat;
	}

	?><tr class="title"><?php

	if ($order == 'trans')
	{
		?><td align="right"></td><?php
	}

	?><td><?php

	if ($package == 'alloff' || $package == 'allun' || $package == 'all' || $package == 'allcore')
	{
		echo '<strong>Template catalog</strong>';
	}
	else
	{
		if ($official)
		{
			$repo = ($version == 'master') ? 'master' : $branch;
			echo "<a href='https://raw.github.com/wesnoth/wesnoth/$repo/po/$package/$package.pot'>Template catalog</a>";
		}
		else
		{
			$packname = getpackage($package);
			$repo = ($version == 'master') ? $wescamptrunkversion : $wescampbranchversion;
			$reponame = "$packname-$repo";
			echo "<a href='https://raw.github.com/wescamp/$reponame/master/po/$package.pot'>Template catalog</a>";
		}
	}
	?></td>
	<td align="right"></td>
	<td align="right"></td>
	<td align="right"></td>
	<td></td>
	<td align="right"></td>
	<td></td>
	<td align="right"><?php echo $main_total ?></td>
	<td></td>
	</tr>
	</table>
	</td>
	</tr>
	</table><?php
}
else
{
	?><h2>No available stats for package <?php echo $package ?></h2><?php
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
