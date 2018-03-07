<?php
#
# Front-end UI/HTML generation functions
#
# Translation statistics Web interface (gettext.wesnoth.org)
# Part of the Battle for Wesnoth Project <https://www.wesnoth.org/>
#

if (!defined('IN_WESNOTH_LANGSTATS'))
{
	die(1);
}

/**
 * Returns the URL parameters portion for the current document.
 *
 * Any parameters that do not apply because of the current view mode are not
 * included in the result.
 *
 * @param $merge_with_ary (array) Add or replace parameters in the result.
 */
function clean_url_parameters($merge_with_ary = null)
{
	global $view, $version;

	$params_ary = [
		'view'			=> $view,
		'version'		=> $version,
	];

	// Merge the new view parameter first and handle it accordingly.
	if (isset($merge_with_ary['view']))
	{
		$params_ary['view'] = $merge_with_ary['view'];
		unset($merge_with_ary['view']);
	}

	if ($params_ary['view'] === 'langs')
	{
		global $lang;
		$params_ary = array_merge($params_ary, [
			'lang'			=> $lang,
		]);
	}
	else
	{
		global $package, $order;
		$params_ary = array_merge($params_ary, [
			'package'		=> $package,
			'order'			=> $order,
		]);
	}

	if (!empty($merge_with_ary))
	{
		$params_ary = array_merge($params_ary, $merge_with_ary);
	}

	$res = '';

	foreach ($params_ary as $key => $val)
	{
		if (!empty($val))
		{
			$res .= '&' . $key . '=' . $val;
		}
	}

	if (!empty($res))
	{
		$res[0] = '?';
	}

	return $res;
}

/**
 * Returns whether the specified textdomain is a core textdomain.
 */
function is_core_textdomain($textdomain)
{
	global $core_textdomains;
	return in_array($textdomain, $core_textdomains);
}

function ui_self_link($disable_condition, $text, $href)
{
	if ($disable_condition)
	{
		echo '<b>' . $text . '</b>';
	}
	else
	{
		echo '<a href="' . htmlspecialchars($href) . '">' . $text . '</a>';
	}
}

/**
 * Prints the timestamp element.
 */
function ui_last_update_timestamp($date)
{
	echo '<div id="lastmod">Last updated on ' . strftime('%F %R UTC%z', $date) . '</div>';
}

/**
 * Prints a box with the specified error message.
 */
function ui_error($message)
{
	echo '<div class="error-message"><span class="message-heading">Error</span><br />' . $message . '</div>';
}

/**
 * Prints a box with the specified instruction message.
 */
function ui_message($message)
{
	echo '<div class="ui-message">' . $message . '</div>';
}

function ui_catalog_link_internal($textdomain, $lang, &$lang_label, &$show_lang_code)
{
	$path_fragment = '';

	if ($lang === null)
	{
		$path_fragment = $textdomain . '.pot';

		if ($lang_label === null)
		{
			$lang_label = "Template catalog";
		}
	}
	else
	{
		$path_fragment = $lang . '.po';

		if ($lang_label === null)
		{
			$lang_label = "&lt;unspecified language <code>$lang</code>&lt;";
			$show_lang_code = false;
		}
	}

	return $path_fragment;
}

function ui_mainline_catalog_link($branch, $textdomain, $lang = null, $lang_label = null, $show_lang_code = false)
{
	global $mainline_file_url_prefix;

	$path = '/' . $branch . '/po/' . $textdomain . '/' .
	        ui_catalog_link_internal($textdomain, $lang, $lang_label, $show_lang_code);

	echo '<a class="textdomain-file" href="' . htmlspecialchars($mainline_file_url_prefix . $path) .
	     '">' . $lang_label . '</a>';

	if ($show_lang_code)
	{
		echo ' (<code>' . $lang . '</code>)';
	}
}

function ui_addon_catalog_link($repo, $textdomain, $lang = null, $lang_label = null, $show_lang_code = false)
{
	$path = '/' . $repo . '/master/po/' .
	        ui_catalog_link_internal($textdomain, $lang, $lang_label, $show_lang_code);

	echo '<a class="textdomain-file" href="' . htmlspecialchars('https://raw.github.com/wescamp' . $path) .
	     '">' . $lang_label . '</a>';

	if ($show_lang_code)
	{
		echo ' (<code>' . $lang . '</code>)';
	}
}

/**
 * Prints the statistics headers common to textdomain and team views.
 */
function ui_column_headers()
{
	?><th class="translated" scope="col">Translated</th>
	<th class="translated percent" scope="col">%</th>
	<th class="fuzzy" scope="col">Fuzzy</th>
	<th class="fuzzy percent" scope="col">%</th>
	<th class="untranslated" scope="col">Untranslated</th>
	<th class="untranslated percent" scope="col">%</th>
	<th class="strcount" scope="col">Total</th>
	<th class="graph" scope="col">Graph</th><?php
}

/**
 * Returns the number of statistics headers displayed by ui_column_headers().
 */
function ui_column_headers_count()
{
	return 8;
}

/**
 * Prints the statistics columns HTML.
 *
 * @param $strcount   (int) String count for this row.
 * @param $translated (int) Number of translated strings.
 * @param $fuzzy      (int) Number of fuzzy strings.
 * @param $pot_total  (int) Template string count. This is used instead of
 *                          $strcount to calculate percentages and the bar
 *                          graph if provided. Used for the language view
 *                          (because apparently people wanted to see the number
 *                          of translated + fuzzy strings on the Total column
 *                          for some reason?)
 */
function ui_stat_columns($strcount, $translated, $fuzzy, $pot_total = null)
{
	if ($pot_total === null)
	{
		$pot_total = $strcount;
	}

	$untranslated    = $strcount - $translated - $fuzzy;
	$pc_translated   = 100 * $translated / $pot_total;
	$pc_fuzzy        = 100 * $fuzzy / $pot_total;
	$pc_untranslated = 100 * $untranslated / $pot_total;

	$fmt = "%0.2f";

	echo '<td class="translated">'   . $translated . '</td>' .
	     '<td class="percent">'      . sprintf($fmt, $pc_translated) . '</td>' .
	     '<td class="fuzzy">'        . $fuzzy . '</td>' .
	     '<td class="percent">'      . sprintf($fmt, $pc_fuzzy) . '</td>' .
	     '<td class="untranslated">' . $untranslated . '</td>' .
	     '<td class="percent">'      . sprintf($fmt, $pc_untranslated) . '</td>' .
	     '<td class="strcount">'     . $strcount . '</td>' .
	     '<td class="graph">';

	$graph_width   = 240; // px
	$graph_trans   = sprintf("%d", $translated * $graph_width / $pot_total);
	$graph_fuzzy   = sprintf("%d", $fuzzy * $graph_width / $pot_total);
	$graph_untrans = $graph_width - $graph_trans - $graph_fuzzy;

	$graph_class_sections = [
		"green" => $graph_trans,
		"blue"  => $graph_fuzzy,
		"red"   => $graph_untrans,
	];

	foreach ($graph_class_sections as $class => $width)
	{
		if ($width > 0)
		{
			echo '<span class="stats-bar ' . $class . '-bar" style="width:' .
			     $width . 'px"></span>';
		}
	}

	echo '</td>';
}
