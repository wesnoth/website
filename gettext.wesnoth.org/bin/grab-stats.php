<?php
#
# gettext.wesnoth.org back-end statistics collection script
#
# Translation statistics Web interface (gettext.wesnoth.org)
# Part of the Battle for Wesnoth Project <https://www.wesnoth.org/>
#

if (php_sapi_name() !== "cli")
{
	die(1);
}

define('IN_WESNOTH_LANGSTATS', true);

include('includes/config.php');
include('includes/functions.php');

set_time_limit(0);

// Acquire a lock to prevent running more than one instance at a time.
if (is_locked())
{
	exit();
}
else
{
	register_shutdown_function('remove_lock');
	create_lock();
}

/**
 * Generates stats for the given branch and set of textdomains/add-on names.
 *
 * Stats files are written to files named stats/[textdomain]/[branch_id].stats
 * based on the current working directory.
 *
 * @param $branch_id (string)  Either 'master' or 'branch'.
 * @param $official  (boolean) Whether we're grabbing mainline or add-on
 *                             (WesCamp) stats.
 * @param $packages  (array)   List of package/textdomain names to scan. If
 *                             $official is true, these are textdomain names,
 *                             otherwise a list of add-on names is expected.
 */
function grab_stats($branch_id, $official, $packages) // master or branch, official (1) or extras (0), package array
{
	// These are defined in config.php.
	global $master_basedir, $branch_basedir;
	global $addons_dev_basedir, $addons_branch_basedir;
	global $ignore_langs;

	foreach ($packages as $package)
	{
		$stats = [];

		if ($official)
		{
			$basedir = ($branch_id == 'master') ? $master_basedir : $branch_basedir;
			$po_dir = $basedir . '/po/' . $package . '/';
			$domain = $package;
		}
		else // WesCamp
		{
			$basedir = ($branch_id == 'master') ? $addons_dev_basedir : $addons_branch_basedir;
			$po_dir = $basedir . '/' . $package . '/po/';
			$domain = getdomain($package);
		}

		// It can happen that the translation is broken in WesCamp, mainly
		// when there is no po/ folder with the file TEXTDOMAIN.pot.
		if (file_exists($po_dir . '/' . $domain . '.pot'))
		{
			$linguas_text = file_get_contents($po_dir . '/LINGUAS');
			$langs = explode(' ', substr($linguas_text, 0, strlen($linguas_text) - 1));

			echo "Getting stats for textdomain $domain\n";

			$stats['_pot'] = getstats($po_dir . '/' . $domain . '.pot');

			if (!file_exists('stats/' . $domain))
			{
				system('mkdir stats/' . $domain);
			}

			foreach ($langs as $lang)
			{
				//echo "Getting stats for language $lang\n";
				if (!in_array($lang, $ignore_langs))
				{
					$pofile = $po_dir . '/' . $lang . '.po';
					$stats[$lang] = getstats($pofile);
				}
			}
		
			$file = fopen('stats/' . $domain . '/' . $branch_id . 'stats', 'wb');
			fwrite($file, serialize($stats));
			fclose($file);
		}
	}
}

echo "Getting stats for master\n";
grab_stats('master', true,  $mainline_textdomains);
grab_stats('master', false, $addon_packages_dev);

echo "Getting stats for branch ($branch)\n";
grab_stats('branch', true,  $mainline_textdomains);
grab_stats('branch', false, $addon_packages_branch);
