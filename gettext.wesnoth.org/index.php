<?php

define('IN_WESNOTH_LANGSTATS', true);

include('config.php');
include('functions.php');
include('functions-web.php');
include('langs.php');
include('wesmere.php');

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

$existing_packs         = explode(' ', $packages);
$existing_corepacks     = explode(' ', $corepackages);
$existing_extra_packs_t = explode(' ', $extratpackages);
$existing_extra_packs_b = explode(' ', $extrabpackages);

$firstpack = $existing_packs[0];

$stats = [];

$nostats = false;

//
// Process URL parameters
//

// Set the default starting point when calling gettext.wesnoth.org:
//   'branch': show stats from the current stable branch
//   'master': show stats from master
$version = isset($_GET['version']) ? parameter_get('version') : 'branch';

$package = isset($_GET['package']) ? parameter_get('package') : 'alloff';

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
			add_textdomain_stats('stats/' . $pack . '/' . $statsfile, $stats);
		}

		break;

	case 'all':
		for ($i = 0; $i < 2; ++$i)
		{
			$official = $i == 0;

			if ($official)
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

				if (!$official)
				{
					$pack = getdomain($pack);
				}

				add_textdomain_stats('stats/' . $pack . '/' . $statsfile, $stats);
			}
		}
		break;

	case 'allun':
		$packs = ($version == 'master') ? $existing_extra_packs_t : $existing_extra_packs_b;
		foreach ($packs as $pack)
		{
			$pack = getdomain($pack);
			$statsfile = $version . 'stats';

			add_textdomain_stats('stats/' . $pack . '/' . $statsfile, $stats);
		}
		break;

	default:
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
	ui_last_update_timestamp($date);
}

?><div id="orderby">Order by:
	<ul class="gettext-switch"
		><li><?php ui_self_link($order == 'trans', 'Translated strings count', "?order=trans&package=$package") ?></li
		><li><?php ui_self_link($order != 'trans', 'Language', "?order=alpha&package=$package") ?></li
	></ul>
</div>

<div id="version">Branch:
	<ul class="gettext-switch"
		><li><?php ui_self_link($version == 'branch', 'Stable/' . $branch, "?version=branch&package=$package") ?></li
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

$is_official_textdomain = true;

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
				$is_official_textdomain = false;
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
		?><th class="title">Language</th><?php

		ui_column_headers();

	?></tr></thead>
	<tbody><?php

	$i = 0;
	$pos = 1;
	$oldstat = [ 0, 0, 0 ];

	// The column count increases by 1 when including the completion ranking.
	$column_count = 9;
	// This offset is based on the language/textdomain name column, not the
	// actual first column.
	$strcount_column_offset = 8;

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
			++$column_count;
			?><td class="rank"><?php echo $pos ?></td><?php
		}

		?><td class="language-team"><?php

		$lang_code_html = "<code>$lang</code>";

		if ($package == 'alloff' || $package == 'allun' || $package == 'all' || $package == 'allcore')
		{
			echo "<a class='language-stats-link' href='index.lang.php?lang=$lang&amp;version=$version'>" . $langs[$lang] . '</a> (' . $lang_code_html . ')';
		}
		else
		{
			if ($is_official_textdomain)
			{
				$repo = ($version == 'master') ? 'master' : $branch;
				ui_mainline_catalog_link($repo, $package, $lang, $langs[$lang], true);
			}
			else
			{
				$packname = getpackage($package);
				$repo = ($version == 'master') ? $wescamptrunkversion : $wescampbranchversion;
				$reponame = "$packname-$repo";
				ui_addon_catalog_link($reponame, $package, $lang, $langs[$lang], true);
			}
		}
		?></td><?php

		if (($stat[0] == 1) || ($total == 0))
		{
			?><td class="invalidstats" colspan="0">Error in <?php echo $langs[$lang] . "(<code>$lang</code>)" ?> translation files</td><?php
		}
		else
		{
			ui_stat_columns($main_total, $stat[1], $stat[2]);
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

	?><td colspan="<?php echo $strcount_column_offset - 1 ?>"><?php

	if ($package == 'alloff' || $package == 'allun' || $package == 'all' || $package == 'allcore')
	{
		echo 'Template catalogs total';
	}
	else
	{
		if ($is_official_textdomain)
		{
			$repo = ($version == 'master') ? 'master' : $branch;
			ui_mainline_catalog_link($repo, $package);
		}
		else
		{
			$packname = getpackage($package);
			$repo = ($version == 'master') ? $wescamptrunkversion : $wescampbranchversion;
			$reponame = "$packname-$repo";
			ui_addon_catalog_link($reponame, $package);
		}
	}
	?></td>
	<td class="strcount"><?php echo $main_total ?></td>
	<td></td>
	</tr>
	</tbody>
	</table><?php
}
else
{
	ui_error("There are no statistics available for the selected textdomain on the selected branch");
}

wesmere_emit_footer();
