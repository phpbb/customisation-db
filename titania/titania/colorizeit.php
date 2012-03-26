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
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

phpbb::$user->add_lang('viewtopic');

// Get ColorizeIt configuration
if(!strlen(titania::$config->colorizeit)) clr_error('ERROR_NO_ATTACHMENT');
switch(titania::$config->colorizeit_auth)
{
    case 'HEADER':
        $var = strtoupper(str_replace('-', '_', titania::$config->colorizeit_var));
        if(!isset($_SERVER['HTTP_' . $var]) || $_SERVER['HTTP_' . $var] != titania::$config->colorizeit_value) clr_error('ERROR_NO_ATTACHMENT');
        break;
    case 'POST':
        $var = titania::$config->colorizeit_var;
        if(!isset($_POST[$var]) || $_POST[$var] != titania::$config->colorizeit_value) clr_error('ERROR_NO_ATTACHMENT');
}


/**
* Get attachment
**/
$download_id = request_var('id', 0);
if(!$download_id) clr_error('ERROR_NO_ATTACHMENT');

$sql = 'SELECT *
	FROM ' . TITANIA_ATTACHMENTS_TABLE . "
	WHERE attachment_id = $download_id";
$result = phpbb::$db->sql_query_limit($sql, 1);
$attachment = phpbb::$db->sql_fetchrow($result);
phpbb::$db->sql_freeresult($result);

if (!$attachment || $attachment['object_type'] != TITANIA_CONTRIB)
{
	clr_error('ERROR_NO_ATTACHMENT');
}

if((int) $attachment['object_type'] != TITANIA_CONTRIB || $attachment['is_orphan'] || $attachment['attachment_access'] < titania::$access_level)
{
	clr_error('ERROR_NO_ATTACHMENT');
}

/**
* Get revision and contribution
*/
$sql = 'SELECT revision_id, contrib_id, revision_status, revision_clr_options FROM ' . TITANIA_REVISIONS_TABLE . '
    WHERE  attachment_id = ' . $attachment['attachment_id'];
$result = phpbb::$db->sql_query($sql);
$revision = phpbb::$db->sql_fetchrow($result);
phpbb::$db->sql_freeresult($result);

$contrib = new titania_contribution;
if (!$contrib->load((int) $revision['contrib_id']) || $revision['revision_status'] != TITANIA_REVISION_APPROVED)
{
    clr_error('SORRY_AUTH_VIEW_ATTACH');
}

if (!$contrib->has_colorizeit())
{
    clr_error('CLR_ERROR_NOSAMPLE');
}

/**
* Get file options
*/
if (!strlen($revision['revision_clr_options']))
{
    $zip_file = titania::$config->upload_path . utf8_basename($attachment['attachment_directory']) . '/' . utf8_basename($attachment['physical_filename']);
    if(!@file_exists($zip_file))
    {
        clr_error('ERROR_NO_ATTACHMENT');
    }
    $new_dir_name = md5(serialize($_SERVER)) . '_' . microtime();
    $contrib_tools = new titania_contrib_tools($zip_file, $new_dir_name);
    $phpbb_data = clr_phpbb_data($contrib_tools->unzip_dir);
    $contrib_tools->remove_temp_files();
    // save data
    $revision['revision_clr_options'] = serialize($phpbb_data);
    $sql_ary = array(
        'revision_clr_options' 		=> $revision['revision_clr_options']
    );
    $sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . '
        SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
        WHERE revision_id = ' . $revision['revision_id'];
    phpbb::$db->sql_query($sql);
}

/**
* Build XML data
*/
$data = unserialize($revision['revision_clr_options']);
$base_url = titania::$config->titania_script_path . 'download/id_';
if(substr($base_url, 0, 1) !== '/') $base_url = '/' . $base_url;

$xml = '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n<colorizeit>\n";
$xml .= "\t<name>" . htmlspecialchars($contrib->contrib_name) . "</name>\n";
$url = titania_url::build_url('download', array('id' => $attachment['attachment_id']));
$xml .= "\t<file src=\"" . htmlspecialchars($base_url) . $attachment['attachment_id'] . '" created="' . $attachment['filetime'] . "\" />\n";
$xml .= "\t<sample src=\"" . htmlspecialchars($base_url) . $contrib->clr_sample['attachment_id'] . '" created="' . $contrib->clr_sample['filetime'] . "\" />\n";
$xml .= "\t<colors>" . htmlspecialchars($contrib->contrib_clr_colors) . "</colors>\n";

$parser = $data['parser'];
$xml .= "\t<parser>" . $parser . "</parser>\n";
if($data['options'])
{
    foreach($data as $key => $value)
    {
        if(substr($key, 0, strlen($parser)) == $parser)
        {
            $xml .= "\t<option name=\"" . htmlspecialchars($key) . "\">" . htmlspecialchars($value) . "</option>\n";
        }
    }
    if(isset($data['phpbb3_dir']))
    {
        $xml .= "\t<filename>" . htmlspecialchars($data['phpbb3_dir']) . ".zip</filename>\n";
    }
}

$xml .= '</colorizeit>';

echo $xml;

clr_gc();



/**
* Show error message and exit
*/
function clr_error($message)
{
    if(isset(phpbb::$user->lang[$message])) $message = phpbb::$user->lang[$message];
    echo '<?xml version="1.0" encoding="UTF-8"?>', "\n", '<error>', $message, '</error>';
    clr_gc();
}

/**
* Exit
*/
function clr_gc()
{
	if (!empty(phpbb::$cache))
	{
		phpbb::$cache->unload();
	}
	phpbb::$db->sql_close();
	exit;
}


/**
* Extract parser data from style. Based on colorizeit.com code
**/
function clr_phpbb_data($base_dir)
{
    $files = clr_find_cfg($base_dir, '');
    if(!count($files))
    {
        // no cfg files were found. not a phpBB style
        return array('parser' => 'default', 'options' => false);
    }
    // check all files
    $items = array();
    $languages = array();
    for($i=0; $i<count($files); $i++)
    {
        if($files[$i]['name'] == 'style.cfg')
        {
            $result = clr_phpbb_check_cfg(file_get_contents($base_dir . $files[$i]['dir'] . '/' . $files[$i]['name']));
            if($result === false) continue;
            $list = explode('/', $files[$i]['dir']);
            $dir = $list[count($list) - 1];
            $items['dir'][$dir] = $dir;
            $items['style'][$result['name']] = $result['name'];
        }
        elseif(in_array($files[$i]['name'], array('theme.cfg', 'template.cfg', 'imageset.cfg')))
        {
            $component = substr($files[$i]['name'], 0, strlen($files[$i]['name']) - 4);
            $list = explode('/', $files[$i]['dir']);
            if($list[count($list) - 1] != $component) continue;
            $result = clr_phpbb_check_cfg(file_get_contents($base_dir . $files[$i]['dir'] . '/' . $files[$i]['name']));
            if($result !== false)
            {
                $dir = $list[count($list) - 2];
                $items['dir'][$dir] = $dir;
                $items[$component][$result['name']] = $result['name'];
            }
            if($files[$i]['name'] == 'imageset.cfg')
            {
                // find all language packs
                $search = $files[$i]['dir'] . '/';
                for($j=0; $j<count($files); $j++)
                if($j != $i && $files[$j]['name'] == 'imageset.cfg' && strpos($files[$j]['dir'], $search) === 0)
                {
                    $img_dir = substr($files[$j]['dir'], strlen($search));
                    if(strlen($img_dir) && strpos($img_dir, '/') === false && !in_array($img_dir, $languages) && preg_match('/^[a-zA-Z0-9\-_]+$/', $img_dir))
                    {
                        $languages[] = $img_dir;
                    }
                }
            }
        }
    }
    if(!count($items))
    {
        // no phpbb style was found
        return array('parser' => 'default', 'options' => false);
    }
    // remove duplicates
    $result = array();
    if(isset($items['style']))
    {
        foreach(array('theme', 'template', 'imageset') as $component)
        if(isset($items[$component]))
        {
            foreach($items[$component] as $key)
            {
                if(isset($items['style'][$key]))
                {
                    unset($items[$component][$key]);
                }
            }
            if(!count($items[$component]))
            {
                unset($items[$component]);
            }
        }
    }
    
    // generate result
    $result = array(
        'parser'        => 'phpbb3',
        'options'       => true,
    );
    foreach(array('style', 'theme', 'template', 'imageset', 'dir') as $component)
    {
        if(isset($items[$component]))
        {
            $i = 0;
            foreach($items[$component] as $key)
            {
                $result['phpbb3_' . $component . ($i > 0 ? $i : '')] = $key;
                $i ++;
            }
        }
    }
    if(count($languages))
    {
        $result['phpbb3_lang'] = implode(',', $languages);
    }
    if(isset($items['style']) && count($items['style']) == 1)
    {
        foreach($items['style'] as $key) $result['style_name'] = $key;
    }
    return $result;
}

function clr_find_cfg($base_dir, $dir)
{
    $result = array();
    foreach (scandir($base_dir . $dir) as $item)
    {
        if ($item == '.' || $item == '..')
        {
            continue;
        }
        if (is_dir($base_dir . $dir . '/' . $item))
        {
            $result = array_merge($result, clr_find_cfg($base_dir, $dir . '/' . $item));
        }
        else if(substr($item, -4) == '.cfg')
        {
            $result[] = array(
                'dir'   => $dir,
                'name'  => $item
                );
        }
    }
    return $result;
}

function clr_phpbb_check_cfg($data)
{
    $list = explode("\n", $data);
    $name = false;
//    $version = false;
    for($i=0; $i<count($list); $i++)
    {
        $str = trim($list[$i]);
        if(strlen($str))
        {
            $list2 = explode(' =', $str);
            if(count($list2) != 2 && substr($str, 0, 1) != '#')
            {
                return false;
            }
            else
            {
                if($list2[0] == 'name') $name = trim($list2[1]);
//                if($list2[0] == 'version') $version = trim($list2[1]);
            }
        }
    }
    return $name === false ? false : array('name' => $name/*, 'version' => $version*/);
}
