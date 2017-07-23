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

/**
 * Get statistics from a .po file by running msgfmt on it.
 *
 * The return value is a 0-indexed array containing the following values:
 *
 *   [0] - 0 if msgfmt succeeded, 1 if it failed.
 *   [1] - The fully translated strings count.
 *   [2] - The fuzzied strings count.
 *   [3] - The untranslated strings count.
 */
function getstats($file)
{
	global $msgfmt;

	$translated = 0;
	$untranslated = 0;
	$fuzzy = 0;
	$error = 0;

	$escfile = escapeshellarg($file);
	@exec("$msgfmt -o /dev/null --statistics $escfile 2>&1", $output, $ret);

	if ($ret == 0)
	{
		// new version of msgfmt make life harder :-/
		if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*fuzzy[^\d]+(\d+)\s*untranslated/", $output[0], $m))
		{
			$m[3] = 0;
		}
		else if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*fuzzy[^\d]/", $output[0], $m))
		{
			$m[3] = 0;
		}
		else if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*untranslated[^\d]/", $output[0], $m))
		{
			$m[3] = $m[2];
			$m[2] = 0;
		}
		else if (preg_match("/^\s*(\d+)\s*translated[^\d]+/", $output[0], $m))
		{
			$m[2] = $m[3] = 0;
		}
		else
		{
			return [ 1, 0, 0, 0 ];
		}

		$translated = $m[1] + 0;
		$fuzzy = $m[2] + 0;
		$untranslated = $m[3] + 0;
	}
	else
	{
		$error = 1;
	}

	return [ $error, $translated, $fuzzy, $untranslated ];
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
 * Adds the textdomain stats from the specified file to the count array.
 *
 * @param $file       (string) Textdomain stats file.
 * @param &$stats_ary (array)  Cumulative stats array.
 *
 * As with the return value of getstats(), &$stats_ary consists of a 0-indexed
 * array containing at least the following fields (any additional fields are
 * ignored and left intact):
 *
 *   [0] - The failed files count.
 *   [1] - The fully translated strings count.
 *   [2] - The fuzzied strings count.
 *   [3] - The untranslated strings count.
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
			$stats_ary[$lang] = [ 0, 0, 0, 0 ];
		}

		for ($i = 0; $i < 4; ++$i)
		{
			$stats_ary[$lang][$i] += $lang_stats[$i];
		}
	}
}
