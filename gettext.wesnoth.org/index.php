<?php

define('IN_WESNOTH_LANGSTATS', true);

include('config.php');
include('functions.php');
include('functions-web.php');
include('langs.php');
include('wesmere.php');

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
					$stats[$lang] = array_slice($stat, 0, 4);
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

wesmere_emit_header();

?>

<h1>Translation Statistics</h1>

<div id="gettext-display-options"><?php

if (!$nostats)
{
	?><div id="lastmod" class="fr">Last updated on <?php echo date('r', $date) ?></div><?php
}

?><div id="orderby">Order by:
	<ul class="gettext-switch"
		><li><?php ui_self_link($order == 'trans', 'Translated strings count', "?order=trans&package=$package") ?></li
		><li><?php ui_self_link($order != 'trans', 'Team name', "?order=alpha&package=$package") ?></li
	></ul>
</div>

<div id="version">Branch:
	<ul class="gettext-switch"
		><li><?php ui_self_link($version == 'branch', 'Stable/1.12', "?version=branch&package=$package") ?></li
		><li><?php ui_self_link($version != 'branch', 'Development/master', "?version=master&package=$package") ?></li
	></ul>
</div>

<?php
function ui_package_set_link($package_set, $label)
{
	global $package, $order, $version;
	ui_self_link($package == $package_set, $label, '?package=' . $package_set . '&order=' . $order . '&version=' . $version);
}

?><div id="package-set">Show:
	<ul class="gettext-switch"
		><li><?php ui_package_set_link('alloff',  'All mainline textdomains')   ?></li
		><li><?php ui_package_set_link('allcore', 'Mainline core textdomains')  ?></li
		><li><?php ui_package_set_link('all',     'All textdomains')            ?></li
		><li><?php ui_package_set_link('allun',   'All unofficial textdomains') ?></li
		><li><a href="index.lang.php?version=<?php echo $version ?>">By language</a></li
	></ul>
</div><?php

for ($i = 0; $i < 2; ++$i)
{
	if ($i == 0)
	{
		$packs = $existing_packs;
		echo '<div id="textdomains-mainline">Official: ';
	}
	else
	{
		$packs = ($version == 'master') ? $existing_extra_packs_t : $existing_extra_packs_b;
		echo '<div id="textdomains-umc">Unofficial: ';
	}

	echo '<ul class="gettext-switch">';

	$first = true;
	foreach ($packs as $pack)
	{
		$packdisplay = $pack;
		if ($i == 1)
		{
			$pack = getdomain($pack);
		}

		echo '<li>';

		if ($pack == $package)
		{
			if ($i == 1)
			{
				$official = false;
			}
		}

		ui_self_link($pack == $package, $packdisplay, "?package=$pack&order=$order&version=$version");

		echo '</li>';
	}

	echo '</ul></div>';
}

?></div><!-- gettext-display-options --><?php

if (!$nostats)
{
	?><table class="gettext-stats">
	<thead><tr><?php
		if ($order == 'trans')
		{
			?><th class="rank">Rank</th><?php
		}
		?><th class="title">Language</th>
		<th class="translated">Translated</th>
		<th class="translated percent">%</th>
		<th class="fuzzy">Fuzzy</th>
		<th class="fuzzy percent">%</th>
		<th class="untranslated">Untranslated</th>
		<th class="untranslated percent">%</th>
		<th class="total">Total</th>
		<th class="graph">Graph</th>
	</tr></thead>
	<tbody>
	<?php

	$i = 0;
	$pos = 1;
	$oldstat = [ 0, 0, 0 ];

	foreach ($stats as $lang => $stat)
	{
		$total = $stat[1] + $stat[2] + $stat[3];

		if (cmp_translated($stat, $oldstat) != 0)
		{
			$pos = $i + 1;
		}

		?><tr><?php

		if ($order == 'trans')
		{
			?><td class="rank"><?php echo $pos ?></td><?php
		}

		?><td><?php

		if ($package == 'alloff' || $package == 'allun' || $package == 'all' || $package == 'allcore')
		{
			echo "<b><a href='index.lang.php?lang=$lang&amp;version=$version'>" . $langs[$lang] . '</a></b> (' . $lang . ')';
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
			?><td class="translated"><?php echo $stat[1] ?></td>
			<td class="percent"><?php printf("%0.2f", ($stat[1]*100)/$main_total); ?></td>
			<td class="fuzzy"><?php echo $stat[2] ?></td>
			<td class="percent"><?php printf("%0.2f", ($stat[2]*100)/$main_total); ?></td>
			<td class="untranslated"><?php echo ($main_total - $stat[1] - $stat[2]) ?></td>
			<td class="percent"><?php printf("%0.2f", (($main_total-$stat[1]-$stat[2])*100)/$main_total); ?></td>
			<td class="strcount"><?php echo $main_total ?></td><?php

			$graph_width = 240; // px

			$trans = sprintf("%d", ($stat[1] * $graph_width) / $main_total);
			$fuzzy = sprintf("%d", ($stat[2] * $graph_width) / $main_total);
			$untrans = $graph_width - $trans - $fuzzy;

			?><td class="graph"><span class="stats-bar green-bar" style="width:<?php echo $trans ?>px"></span><span class="stats-bar blue-bar" style="width:<?php echo $fuzzy ?>px"></span><span class="stats-bar red-bar" style="width:<?php echo $untrans ?>px"></span></td><?php
		}

		?></tr><?php

		++$i;
		$oldstat = $stat;
	}

	?><tr class="potstats"><?php

	if ($order == 'trans')
	{
		?><td></td><?php
	}

	?><td colspan="7"><?php

	if ($package == 'alloff' || $package == 'allun' || $package == 'all' || $package == 'allcore')
	{
		echo 'Template catalogs total';
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
	<td class="strcount" colspan="2"><?php echo $main_total ?></td>
	</tr>
	</tbody>

	</table><?php
}
else
{
	?><h2>No available stats for package <?php echo $package ?></h2><?php
}
?><div> <br/> </div><?php

wesmere_emit_footer();
