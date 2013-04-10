<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));

// Report all errors, except notices
error_reporting(E_ALL ^ E_NOTICE);

if (!isset($_GET['style']))
{
	die('No Style');
}

$style = preg_replace('#[^A-Za-z0-9_]#', '', $_GET['style']);

if (file_exists(TITANIA_ROOT . 'styles/' . $style . '/theme/stylesheet.css'))
{
	$stylesheet = file_get_contents(TITANIA_ROOT . 'styles/' . $style . '/theme/stylesheet.css');
}
else
{
	die('No Style');
}

// Match CSS imports
$matches = array();
preg_match_all('/@import url\(["\'](.*)["\']\);/i', $stylesheet, $matches);

if (sizeof($matches))
{
	foreach ($matches[0] as $idx => $match)
	{
		$stylesheet = str_replace($match, file_get_contents(TITANIA_ROOT . 'styles/' . $style . '/theme/' . $matches[1][$idx]), $stylesheet);
	}
}

$path = TITANIA_ROOT;

// adjust paths
$stylesheet = str_replace('./', $path . 'styles/' . $style . '/theme/', $stylesheet);

$expire_time = 86400;
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $expire_time));
header('Content-type: text/css; charset=UTF-8');

// Parse Theme Data
$replace = array(
	'{T_THEME_PATH}'			=> $path . "styles/$style/theme",
	'{T_TEMPLATE_PATH}'			=> $path . "styles/$style/template",
	'{T_IMAGESET_PATH}'			=> $path . "styles/$style/imageset",
	'{T_IMAGES_PATH}'			=> $path . "images",
);
$stylesheet = str_replace(array_keys($replace), array_values($replace), $stylesheet);

$matches = array();
preg_match_all('#\{IMG_([A-Za-z0-9_]*?)_(WIDTH|HEIGHT|SRC)\}#', $stylesheet, $matches);

$imgs = $find = $replace = array();
if (isset($matches[0]) && sizeof($matches[0]))
{
	foreach ($matches[1] as $i => $img)
	{
		$img = strtolower($img);
		$find[] = $matches[0][$i];

		if (!isset($img_array[$img]))
		{
			$replace[] = '';
			continue;
		}

		if (!isset($imgs[$img]))
		{
			$img_data = &$img_array[$img];
			$imgsrc = ($img_data['image_lang'] ? $img_data['image_lang'] . '/' : '') . $img_data['image_filename'];
			$imgs[$img] = array(
				'src'		=> $path . 'styles/' . $style . '/imageset/' . $imgsrc,
				'width'		=> $img_data['image_width'],
				'height'	=> $img_data['image_height'],
			);
		}

		switch ($matches[2][$i])
		{
			case 'SRC':
				$replace[] = $imgs[$img]['src'];
			break;

			case 'WIDTH':
				$replace[] = $imgs[$img]['width'];
			break;

			case 'HEIGHT':
				$replace[] = $imgs[$img]['height'];
			break;

			default:
				continue;
		}
	}

	if (sizeof($find))
	{
		$stylesheet = str_replace($find, $replace, $stylesheet);
	}
}

echo $stylesheet;
