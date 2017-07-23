<?php

define('IN_WESNOTH_LANGSTATS', true);

include('config.php');
include('functions.php');
include('functions-web.php');
include('langs.php');
include('wesmere.php');

$existing_packs         = $mainline_textdomains;
$existing_corepacks     = $core_textdomains;
$existing_extra_packs_t = $addon_packages_dev;
$existing_extra_packs_b = $addon_packages_branch;

$stats = [];

//
// Process URL parameters
//

// Set the default starting point when calling gettext.wesnoth.org:
//   'branch': show stats from the current stable branch
//   'master': show stats from master
$version = isset($_GET['version']) ? parameter_get('version') : 'branch';

$lang = isset($_GET['lang']) ? parameter_get('lang') : '';

if (!empty($lang))
{
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
			if (!$official)
			{
				$pack = getdomain($pack);
			}

			$statsfile = 'stats/' . $pack . '/' . $version . 'stats';

			if (!file_exists($statsfile))
			{
				continue;
			}

			$serialized = file_get_contents($statsfile);
			$tmpstats = unserialize($serialized);

			if (!isset($tmpstats[$lang]))
			{
				continue;
			}

			$stat = $tmpstats[$lang];
			$stats[] = [
				$stat[0],	// errors
				$stat[1],	// translated
				$stat[2],	// fuzzy
				$stat[3],	// untranslated
				$pack,		// textdomain name
				$tmpstats['_pot'][1] + $tmpstats['_pot'][2] + $tmpstats['_pot'][3],
				$official,	// is official
			];
		}
	}
}

wesmere_emit_header();

?>

<h1>Translation Statistics</h1>

<div id="gettext-display-options"><?php

if (!empty($stats))
{
	$firstpack = $existing_packs[0];
	$filestat = stat('stats/' . $firstpack . '/' . $version . 'stats');

	ui_last_update_timestamp($filestat[9]);
}

?><div id="version">Branch:
	<ul class="gettext-switch"
		><li><?php ui_self_link($version == 'branch', 'Stable/' . $branch, "?version=branch&lang=$lang") ?></li
		><li><?php ui_self_link($version != 'branch', 'Development/master', "?version=master&lang=$lang") ?></li
	></ul>
</div>

<?php
function ui_package_set_link($package_set, $label)
{
	global $version;

	echo '<a href="' . htmlspecialchars("index.php?package=$package_set&order=trans&version=$version") . '">' . $label . '</a>';
}

?><div id="package-set">Show:
	<ul class="gettext-switch"
		><li><?php ui_package_set_link('alloff',  'All mainline textdomains')   ?></li
		><li><?php ui_package_set_link('allcore', 'Mainline core textdomains')  ?></li
		><li><?php ui_package_set_link('all',     'All textdomains')            ?></li
		><li><?php ui_package_set_link('allun',   'All unofficial textdomains') ?></li
		><li><b>By language</b></li
	></ul>
</div>

<div id="language-teams">Language:
	<ul class="gettext-switch"><?php
		$sorted_langs = $langs;
		asort($sorted_langs);

		foreach ($sorted_langs as $code => $langname)
		{
			echo '<li>';
			ui_self_link($code == $lang, $langname, "?lang=$code&version=$version");
			echo '</li>';
		}
	?></ul>
</div>

</div><!-- gettext-display-options --><?php

if (!empty($stats))
{
	?><table class="gettext-stats">
	<thead><tr>
		<th class="title">Textdomain</th><?php

		ui_column_headers();

	?></tr></thead>
	<tbody><?php

	$sumstat = [ 0, 0, 0, 0, 0, 0 ];
	$official = true;

	foreach ($stats as $stat)
	{
		$oldofficial = $official;
		$official = $stat[6];

		$sumstat[1] += $stat[1];
		$sumstat[2] += $stat[2];
		$sumstat[3] += $stat[3];
		$sumstat[5] += $stat[5];

		$total = $stat[1] + $stat[2] + $stat[3];

		if ($oldofficial != $official)
		{
			?><tr class="officialness-separator"><td colspan="9"></td></tr><?php
		}

		?><tr>
			<td class="textdomain-name"><?php
				if ($official)
				{
					$repo = ($version == 'master') ? 'master' : $branch;
					ui_mainline_catalog_link($repo, $stat[4], $lang, $stat[4]);
				}
				else
				{
					$packname = getpackage($stat[4]);
					$repo = ($version == 'master') ? $wescamptrunkversion : $wescampbranchversion;
					$reponame = "$packname-$repo";
					ui_addon_catalog_link($reponame, $package, $lang, $stat[4]);
				}
			?></td><?php

			if (($stat[0] == 1) || ($total == 0) || ($stat[5] == 0))
			{
				?><td class="invalidstats" colspan="8">Could not read translation file</td><?php
			}
			else
			{
				ui_stat_columns($total, $stat[1], $stat[2], $stat[5]);
			}

		?></tr><?php
	}
	?></tbody>
	<tfoot>
		<tr class="teamstats">
			<th class="title">Total</th>
			<td class="translated"><?php echo $sumstat[1] ?></td>
			<td></td>
			<td class="fuzzy"><?php echo $sumstat[2] ?></td>
			<td></td>
			<td class="untranslated"><?php echo $sumstat[3] ?></td>
			<td></td>
			<td class="strcount"><?php echo $sumstat[5] ?></td>
			<td></td>
		</tr>
	</tfoot>
	</table><?php
}
else
{
	if (!empty($lang))
	{
		ui_error("There are no statistics available for the selected language");
	}
	else
	{
		ui_message("Choose a language above to see statistics by textdomain");
	}
}

wesmere_emit_footer();
