<?php
#
# Translation statistics Web interface (gettext.wesnoth.org)
# Part of the Battle for Wesnoth Project <https://www.wesnoth.org/>
#

define('IN_WESNOTH_LANGSTATS', true);

include('includes/config.php');
include('includes/functions.php');
include('includes/functions-web.php');
include('includes/langs.php');
include('includes/wesmere.php');

$existing_packs         = $mainline_textdomains;
$existing_corepacks     = $core_textdomains;
$existing_extra_packs_t = $addon_packages_dev;
$existing_extra_packs_b = $addon_packages_branch;

$stats = [];

$nostats = true;

$last_update_timestamp = 0;

$current_textdomain_is_official = true;

//
// Process URL parameters
//

// The view mode (either unset/empty or 'langs').
$view = isset($_GET['view']) ? parameter_get('view') : '';

if (!empty($view) && $view !== 'langs')
{
	$view = '';
}

// Set the default starting point when calling gettext.wesnoth.org:
//   'branch': show stats from the current stable branch
//   'master': show stats from master
$version = isset($_GET['version']) ? parameter_get('version') : 'branch';

// Language (only used in 'langs' view mode).
$lang = isset($_GET['lang']) ? parameter_get('lang') : '';

// Textdomain/add-on (only used in 'textdomains' view mode).
$package = isset($_GET['package']) ? parameter_get('package') : 'alloff';

// Language sorting criterion (only used in 'textdomains' view mode).
$order = (!isset($_GET['order']) || $_GET['order'] != 'alpha')
         ? 'trans' : 'alpha';

//
// Comparison functions used to sort language stats in the textdomain view.
//

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

//
// Populate $stats global with information relevant to the current view mode.
//
switch ($view)
{
	//
	// Textdomain stats view.
	//
	case '':
		$nostats = false;

		// 'all' is the combination of the stats from 'alloff' and 'allun' together.
		$enum_packages = $package === 'all' ? [ 'alloff', 'allun' ] : [ $package ];

		foreach ($enum_packages as $p)
		{
			switch ($p)
			{
				case 'alloff':
				case 'allcore':
					$packs = ($p == 'alloff') ? $existing_packs : $existing_corepacks;
					foreach ($packs as $pack)
					{
						$statsfile = ($version == 'branch') ? 'branchstats' : 'masterstats';
						add_textdomain_stats('stats/' . $pack . '/' . $statsfile, $stats);
					}
					break;

				case 'allun':
					if (!$use_wescamp)
					{
						break;
					}

					$packs = ($version == 'master') ? $existing_extra_packs_t : $existing_extra_packs_b;
					foreach ($packs as $pack)
					{
						$pack = getdomain($pack);
						add_textdomain_stats('stats/' . $pack . '/' . $version . 'stats', $stats);
					}
					break;

				default:
					$statsfile = 'stats/' . $p . '/' . $version . 'stats';

					if (!file_exists($statsfile))
					{
						$nostats = true;
					}
					else
					{
						$serialized = file_get_contents($statsfile);
						$stats = unserialize($serialized);
					}
			}
		}

		if (!$nostats)
		{
			// get total number of strings
			$main_total = $stats['_pot'][1] + $stats['_pot'][2] + $stats['_pot'][3];
			unset($stats['_pot']);

			if ($order == 'trans')
			{
				uasort($stats, 'cmp_translated');
			}
			else
			{
				uksort($stats, 'cmp_alpha');
			}
		}

		break;

	//
	// Language stats view.
	//
	case 'langs':
		$nostats = true;

		if (empty($lang))
		{
			break;
		}

		foreach ([ true, false ] as $official)
		{
			if ($official)
			{
				$packs = $existing_packs;
			}
			else
			{
				$packs = $version == 'master' ? $existing_extra_packs_t : $existing_extra_packs_b;
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

		$nostats = empty($stats);

		break;

	default:
		// We're not supposed to be here.
		die(1);
}

if (!$nostats)
{
	$firstpack = $existing_packs[0];
	$last_update_timestamp = filemtime('stats/' . $firstpack . '/' . $version . 'stats');
}

wesmere_emit_header();

?>

<h1><a href="<?php echo clean_url_parameters() ?>">Translation Statistics</a></h1>

<div id="gettext-display-options"><?php

if (!$nostats)
{
	ui_last_update_timestamp($last_update_timestamp);
}

?><fieldset id="classification"><legend>Display</legend>

<dl id="version" class="display-options"><dt>Branch:</dt><dd>
	<ul class="gettext-switch"
		><li><?php
		ui_self_link($version == 'branch',
		             'Stable/' . $branch,
		             clean_url_parameters([ 'version' => 'branch' ]));
		?></li
		><li><?php
		ui_self_link($version == 'master',
		             'Development/master',
		             clean_url_parameters([ 'version' => 'master' ]));
		?></li
	></ul>
</dd></dl><?php

if ($view !== 'langs')
{
	?><dl id="orderby" class="display-options"><dt>Sort by:</dt><dd>
		<ul class="gettext-switch"
			><li><?php
			ui_self_link($order === 'trans',
			             'Translation progress',
			             clean_url_parameters([ 'order' => 'trans' ]));
			?></li
			><li><?php
			ui_self_link($order === 'alpha',
			             'Language',
			             clean_url_parameters([ 'order' => 'alpha' ]));
			?></li
		></ul>
	</dd></dl><?php
}

function ui_package_set_link($package_set, $label)
{
	global $package, $view;
	ui_self_link(($view !== 'langs' && $package == $package_set),
	             $label,
	             clean_url_parameters([ 'view' => '', 'package' => $package_set ]));
}

?><dl id="package-set" class="display-options"><dt>Textdomain groups:</dt><dd>
	<ul class="gettext-switch"
		><li><?php ui_package_set_link('alloff',  'All mainline')  ?></li
		><li><?php ui_package_set_link('allcore', 'Mainline core') ?></li<?php
		if ($use_wescamp)
		{
			?>><li><?php ui_package_set_link('allun',   'All add-ons') ?></li
			><li><?php   ui_package_set_link('all',     'All')         ?></li<?php
		}
		?>><li><?php ui_self_link($view === 'langs',
		                        'All by language',
		                        "?view=langs&version=$version") ?></li
	></ul>
</dd></dl>

</fieldset><!-- #classification -->

<?php

if ($view === 'langs')
{
	//
	// Print the list of languages to pick from.
	//
	?><fieldset id="language-teams"><legend>Language:</legend>
		<ul class="gettext-switch"><?php
			// Since $langs is pretty free-form and in all likelihood provided
			// in language code order, resort it by human-readable names
			// instead for readability.
			$sorted_langs = $langs;
			asort($sorted_langs);

			foreach ($sorted_langs as $code => $langname)
			{
				echo '<li>';
				ui_self_link($code == $lang,
				             $langname,
				             clean_url_parameters([ 'lang' => $code ]));
				echo '</li>';
			}
		?></ul>
	</fieldset><?php
}
else // $view !== 'langs'
{
	//
	// Print the list of textdomains to pick from.
	//
	foreach ([ true, false ] as $official)
	{
		if ($official)
		{
			$packs = $existing_packs;
			$group_class = 'mainline';
			$group_label = 'Mainline textdomains';
		}
		else if ($use_wescamp)
		{
			$packs = ($version == 'master') ? $existing_extra_packs_t : $existing_extra_packs_b;
			$group_class = 'umc';
			$group_label = 'Add-on textdomains';
		}
		else
		{
			continue;
		}

		echo '<fieldset id="textdomains-' . $group_class . '">' .
		     '<legend>' . $group_label . '</legend><ul class="gettext-switch">';

		foreach ($packs as $pack)
		{
			$packdisplay = $pack;
			if (!$official)
			{
				$pack = getdomain($pack);
			}

			echo '<li>';

			if ($pack == $package && !$official)
			{
				$current_textdomain_is_official = false;
			}

			ui_self_link($pack == $package,
			             $packdisplay,
			             clean_url_parameters([ 'package' => $pack ]));

			echo '</li>';
		}

		echo '</ul></fieldset>';
	}
}

?></div><!-- gettext-display-options --><?php

if (!$nostats)
{
	?><table class="gettext-stats">
	<thead><tr><?php
		if ($view !== 'langs')
		{
			if ($order == 'trans')
			{
				?><th class="rank">Rank</th><?php
			}
			?><th class="title">Language</th><?php
		}
		else
		{
			?><th class="title">Textdomain</th><?php
		}

		ui_column_headers();

	?></tr></thead>
	<tbody><?php
		if ($view !== 'langs')
		{
			$i = 0;
			$pos = 1;
			$oldstat = [ 0, 0, 0 ];

			// This offset is based on the language/textdomain name column, not the
			// actual first column.
			$strcount_column_offset = 8;

			/**
			 * Returns whether the package selection refers to a predefined
			 * set of packages as opposed to a singular package.
			 */
			function package_is_not_singular($package)
			{
				return in_array($package, [ 'alloff', 'allun', 'all', 'allcore' ], true);
			}

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

				?><td class="language-team"><?php

				$lang_code_html = "<code>$lang</code>";

				if (package_is_not_singular($package))
				{
					echo '<a class="language-stats-link" href="'. clean_url_parameters([ 'view' => 'langs', 'lang' => $lang ]) . '">' . $langs[$lang] . '</a> (' . $lang_code_html . ')';
				}
				else
				{
					if ($current_textdomain_is_official)
					{
						$repo = ($version == 'master') ? 'master' : $branch;
						ui_mainline_catalog_link($repo, $package, $lang, $langs[$lang], true);
					}
					else
					{
						$packname = getpackage($package);
						$repo = ($version == 'master') ? $wescamp_version_dev : $wescamp_version_branch;
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

			?></tbody>
			<tfoot>
				<tr class="potstats"><?php
					if ($order == 'trans')
					{
						?><td></td><?php
					}

					?><td colspan="<?php echo $strcount_column_offset - 1 ?>"><?php

					if (package_is_not_singular($package))
					{
						echo 'Template catalogs total';
					}
					else
					{
						if ($current_textdomain_is_official)
						{
							$repo = ($version == 'master') ? 'master' : $branch;
							ui_mainline_catalog_link($repo, $package);
						}
						else
						{
							$packname = getpackage($package);
							$repo = ($version == 'master') ? $wescamp_version_dev : $wescamp_version_branch;
							$reponame = "$packname-$repo";
							ui_addon_catalog_link($reponame, $package);
						}
					}
					?></td>
					<td class="strcount"><?php echo $main_total ?></td>
					<td></td>
				</tr>
			</tfoot><?php
		}
		else // $view === 'langs'
		{
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
							$repo = ($version == 'master') ? $wescamp_version_dev : $wescamp_version_branch;
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
			</tfoot><?php
		}
	?></table><?php
}
else // $nostats
{
	if ($view !== 'langs')
	{
		ui_error("There are no statistics available for the selected textdomain on the selected branch");
	}
	else if (!empty($lang))
	{
		ui_error("There are no statistics available for the selected language on the selected branch");
	}
	else
	{
		ui_message("Choose a language above to see statistics by textdomain");
	}
}

wesmere_emit_footer();
