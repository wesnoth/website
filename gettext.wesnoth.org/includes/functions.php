<?php
#
# Common functions
#
# Translation statistics Web interface (gettext.wesnoth.org)
# Part of the Battle for Wesnoth Project <https://www.wesnoth.org/>
#

if (!defined('IN_WESNOTH_LANGSTATS'))
{
	die(1);
}

$prog = 'grab-stats';

/**
 * Creates the lock file
 */
function create_lock()
{
	global $prog;
	@touch("/tmp/$prog.lock");
}

/**
 * Removes the lock file
 */
function remove_lock() {
	global $prog;
	@unlink("/tmp/$prog.lock");
}

/**
 * Checks if the lock file exists
 */
function is_locked()
{
	global $prog;
	if (@is_file("/tmp/$prog.lock"))
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

function update($basedir, $lang, $package)
{
	$pofile = $basedir . '/po/' . $lang . '/' . $package . '.po';
	$potfile = $basedir . '/po/' . $package . '.pot';
	echo 'msgmerge --update ' . $pofile . ' ' . $potfile;
	@exec('msgmerge --update ' . $pofile . ' ' . $potfile);
}

$stat_fields = [ 'error', 'translated', 'fuzzy', 'untranslated', 'total' ];

function make_stats_array($error = 0)
{
	global $stat_fields;

	$res = array_fill_keys($stat_fields, 0);
	$res['error'] = $error;

	return $res;
}

/**
 * Get language statistics from a .po file by running msgfmt on it.
 *
 * The return value is an array containing the following entries:
 *
 *   'error'        - 0 if msgfmt succeeded, 1 if it failed
 *   'translated'   - Translated strings count (not including fuzzies)
 *   'fuzzy'        - Fuzzy strings count
 *   'untranslated' - Untranslated strings count
 *   'total'        - Total string count (translated + fuzzy + untranslated)
 */
function getstats($file)
{
	global $msgfmt;

	$translated = 0;
	$untranslated = 0;
	$fuzzy = 0;
	$error = 0;
	$total = 0;

	$escfile = escapeshellarg($file);
	@exec("$msgfmt -o /dev/null --statistics $escfile 2>&1", $output, $ret);

	if ($ret == 0)
	{
		if (preg_match('/(\d+)\s*translated/', $output[0], $m))
		{
			$translated += $m[1];
		}

		if (preg_match('/(\d+)\s*fuzzy/', $output[0], $m))
		{
			$fuzzy += $m[1];
		}

		if (preg_match('/(\d+)\s*untranslated/', $output[0], $m))
		{
			$untranslated += $m[1];
		}

		$total = $translated + $untranslated + $fuzzy;
	}

	if ($total == 0)
	{
		$error = 1;
	}

	$res = [
		'error'        => $error,
		'translated'   => $translated,
		'fuzzy'        => $fuzzy,
		'untranslated' => $untranslated,
		'total'        => $total,
	];

	return $res;
}

/**
 * Retrieves the add-on textdomain name from a WesCamp package name.
 */
function getdomain($string)
{
	return 'wesnoth-' . str_replace('-po', '', $string);
}

/**
 * Retrieves the WesCamp package name from an add-on textdomain name.
 */
function getpackage($string)
{
	return str_replace('wesnoth-', '', $string);
}

/**
 * Retrieves a GET variable cleaned up to avoid XSS exploits.
 */
function parameter_get($name)
{
	return htmlspecialchars($_GET[$name], ENT_QUOTES, 'UTF-8');
}

/**
 * Adds catalogue stats to an overall stats array.
 *
 * As with the return value of getstats(), &$overall_stats consists of an array
 * containing the fields described therein (any additional fields are ignored
 * and left intact).
 */
function increment_catalogue_stats(&$overall_stats, $catalogue_stats)
{
	global $stat_fields;
	foreach($stat_fields as $k)
	{
		$overall_stats[$k] += $catalogue_stats[$k];
	}
}

/**
 * Adds the textdomain stats for all languages recorded in the specified file.
 *
 * @param $file       (string) Textdomain stats file.
 * @param &$stats_ary (array)  Cumulative stats array.
 *
 * As with the return value of getstats(), &$stats_ary consists of an array
 * containing the fields described therein (any additional fields are ignored
 * and left intact).
 */
function add_textdomain_stats($file, &$stats_ary)
{
	if (!file_exists($file))
	{
		return;
	}

	$raw_td_stats = unserialize(file_get_contents($file));

	foreach ($raw_td_stats as $lang => $lang_stats)
	{
		if (!isset($stats_ary[$lang]))
		{
			$stats_ary[$lang] = make_stats_array();
		}

		increment_catalogue_stats($stats_ary[$lang], $lang_stats);
	}
}
