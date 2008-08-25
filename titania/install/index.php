<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* This file will contain hard-coded HTML and languages due to being an installation script.
*
*/

/**
* @ignore
*/
define('IN_TITANIA', true);
define('IN_INSTALL', true);

if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));

// Report all errors, except notices
error_reporting(E_ALL ^ E_NOTICE);

if (version_compare(PHP_VERSION, '5.2.0') < 0)
{
	die('You are running an unsupported PHP version. Please upgrade to PHP 5.2.0 or higher before trying to install the Titania customisation database');
}

$submit = isset($_POST['submit']) ? true : false;

class config_options
{
	public function content($input_type, $key, $default = '')
	{
		$string = '';

		$default = (isset($_POST[$key])) ? $_POST[$key] : $default;

		switch ($input_type)
		{
			case 'text':
				$string .= '<input type="text" size="30" name="' . $key . '" id="' . $key . '" value="' . $default . '" />';
			break;
		}

		return $string;
	}
}

$options = new config_options();

$config_values = array(
	array(
		'key'			=> 'phpbb_root_path',
		'title'			=> 'Enter phpBB Root Path',
		'title_explain'	=> 'Relative to Titania directory. :: ' . TITANIA_ROOT,
		'content'		=> $options->content('text', 'phpbb_root_path', '../community/'),
	),

	array(
		'key'			=> 'template_path',
		'title'			=> 'Enter custom template path',
		'title_explain'	=> 'Relative to Titania directory. :: ' . TITANIA_ROOT,
		'content'		=> $options->content('text', 'template_path', 'template'),
	),

	array(
		'key'			=> 'cdb_table_prefix',
		'title'			=> 'Customisation Database Table prefix',
		'title_explain'	=> 'Prefix used to install and access the Titania database tables.',
		'content'		=> $options->content('text', 'cdb_table_prefix', 'customisation_'),
	),
);

$error = array();

if ($submit)
{
	$phpbb_root_path = (isset($_POST['phpbb_root_path'])) ? TITANIA_ROOT . (string) $_POST['phpbb_root_path'] : '';

	/**
	 * @todo need some basic sanitisation for the phpbb_root_path before we continue.
	 * The file_exists does prevent basic remote file checking, but I'm not certain if it will work on all configurations.
	 */
	if ($phpbb_root_path && file_exists($phpbb_root_path . 'common.' . PHP_EXT))
	{
		define('IN_PHPBB', true);
		if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', $phpbb_root_path);
		if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
		$phpEx = PHP_EXT;

		include(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

		$user->session_begin();
		$auth->acl($user->data);

		if (!$user->data['is_registered'])
		{
			$user->setup();
			login_box('', 'You must be logged in and a founder to install Titania');
		}
		else if ($user->data['user_type'] != USER_FOUNDER)
		{
			trigger_error('NOT_AUTHORISED');
		}

		$config_data = "<?php\n";
		$config_data .= '/**
 * @' . "ignore
 */
if (!defined('IN_TITANIA'))
{
	exit;
}\n";
		$config_data .= "\n// Titania auto-generated configuration file\n";

		foreach ($config_values as $option)
		{
			$value = request_var($option['key'], '');
			$define_option = strtoupper($option['key']);
			$define_value = str_replace("'", "\\'", str_replace('\\', '\\\\', $value));

			// if the variable is the phpbb_root_path, we prepend TITANIA_ROOT...
			$define_value = ($option['key'] == 'phpbb_root_path') ? TITANIA_ROOT . $define_value : $define_value;

			$config_data .= "define('$define_option', '$define_value');\n";
		}

		$config_data .= "\n@define('TITANIA_INSTALLED', true);\n";
		$config_data .= '?' . '>';

		if ((file_exists(TITANIA_ROOT . 'config.' . PHP_EXT) && is_writable(TITANIA_ROOT . 'config.' . PHP_EXT)) || is_writable(TITANIA_ROOT))
		{
			$written = true;

			if (!($fp = @fopen(TITANIA_ROOT . 'config.' . PHP_EXT, 'w')))
			{
				$error[] = 'fopen failed on config.' . PHP_EXT;
				$written = false;
			}

			if (!(@fwrite($fp, $config_data)))
			{
				$error[] = 'fwrite failed on config.' . PHP_EXT;
				$written = false;
			}

			@fclose($fp);

			if ($written)
			{
				@chmod(TITANIA_ROOT . 'config.' . PHP_EXT, 0644);
				trigger_error('Titania config.' . PHP_EXT . ' successfully written, you may now proceed to install the SQL file located in the install directory');
			}
		}
	}
	else
	{
		$path = htmlspecialchars((string) $_POST['phpbb_root_path']);
		$error[] = $path . ' does not appear to contain a phpBB3 installation. [ ' . TITANIA_ROOT . $path . ' ]';
	}
}

if (!$submit || sizeof($error))
{
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
	echo '<head>';
	echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
	echo '<title>Install Titania Customisation Database</title>';
	echo '<link href="./../theme/install.css" rel="stylesheet" type="text/css" media="screen" />';
	echo '</head>';
	echo '<body id="errorpage">';
	echo '<div id="wrap">';
	echo '	<div id="page-header">';
	echo '	</div>';
	echo '	<div id="page-body">';
	echo '		<div id="acp">';
	echo '		<div class="panel">';
	echo '			<span class="corners-top"><span></span></span>';
	echo '			<div id="content">';
	echo '				<h1>Install Titania Customisation Database</h1>';
	echo '				<p>You will need to be a founder and logged in to phpBB3 to make these changes and install the Titania system</p><br /><br />';

	if (sizeof($error))
	{
		echo '			<div class="errorbox">';
		echo '				<h3>Error</h3>';
		echo '				<p>' . implode('<br />', $error) . "</p>";
		echo '			</div>';
	}

	echo '				<form method="post" action="./">';
	echo '				<fieldset>';
	echo '					<legend>Configuration Settings</legend>';

	foreach ($config_values as $option)
	{
		echo '				<dl>';
		echo '					<dt><label for="' . $option['key'] . '">' . $option['title'] . "</label><br />\n";
		echo '						<span class="explain">' . $option['title_explain'] . "</span></dt>\n";
		echo '					<dd>' . $option['content'] . "</dd>\n";
		echo '				</dl>';
	}

	echo '				</fieldset>';
	echo '				<fieldset class="submit-buttons">';
	echo '					<legend>Submit</legend>';
	echo '					<input class="button1" type="submit" id="submit" onclick="this.className = \'button1 disabled\';" name="submit" value="Submit" />';
	echo '				</fieldset>';
	echo '				</form>';
	echo '			</div>';
	echo '			<span class="corners-bottom"><span></span></span>';
	echo '		</div>';
	echo '		</div>';
	echo '	</div>';
	echo '	<div id="page-footer">';
	echo '		Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a>';
	echo '	</div>';
	echo '</div>';
	echo '</body>';
	echo '</html>';
}

/**
* Writes the config file to disk, or if unable to do so offers alternative methods
*/
function create_config_file($mode, $sub)
{
	global $lang, $template;

	$this->page_title = $lang['STAGE_CONFIG_FILE'];

	// Obtain any submitted data
	$data = $this->get_submitted_data();

	if ($data['dbms'] == '')
	{
		// Someone's been silly and tried calling this page direct
		// So we send them back to the start to do it again properly
		$this->p_master->redirect('index.' . PHP_EXT . '?mode=install');
	}

	$s_hidden_fields = ($data['img_imagick']) ? '<input type="hidden" name="img_imagick" value="' . addslashes($data['img_imagick']) . '" />' : '';
	$s_hidden_fields .= '<input type="hidden" name="language" value="' . $data['language'] . '" />';
	$written = false;

	// Create a list of any PHP modules we wish to have loaded
	$load_extensions = array();
	$available_dbms = get_available_dbms($data['dbms']);
	$check_exts = array_merge(array($available_dbms[$data['dbms']]['MODULE']), $this->php_dlls_other);

	foreach ($check_exts as $dll)
	{
		if (!@extension_loaded($dll))
		{
			if (!can_load_dll($dll))
			{
				continue;
			}

			$load_extensions[] = $dll . '.' . PHP_SHLIB_SUFFIX;
		}
	}

	// Create a lock file to indicate that there is an install in progress
	$fp = @fopen(PHPBB_ROOT_PATH . 'cache/install_lock', 'wb');
	if ($fp === false)
	{
		// We were unable to create the lock file - abort
		$this->p_master->error($lang['UNABLE_WRITE_LOCK'], __LINE__, __FILE__);
	}
	@fclose($fp);

	@chmod(PHPBB_ROOT_PATH . 'cache/install_lock', 0666);

	$load_extensions = implode(',', $load_extensions);

	// Time to convert the data provided into a config file
	$config_data = "<?php\n";
	$config_data .= "// phpBB 3.0.x auto-generated configuration file\n// Do not change anything in this file!\n";

	$config_data_array = array(
		'dbms'			=> $available_dbms[$data['dbms']]['DRIVER'],
		'dbhost'		=> $data['dbhost'],
		'dbport'		=> $data['dbport'],
		'dbname'		=> $data['dbname'],
		'dbuser'		=> $data['dbuser'],
		'dbpasswd'		=> htmlspecialchars_decode($data['dbpasswd']),
		'table_prefix'	=> $data['table_prefix'],
		'acm_type'		=> 'file',
		'load_extensions'	=> $load_extensions,
	);

	foreach ($config_data_array as $key => $value)
	{
		$config_data .= "\${$key} = '" . str_replace("'", "\\'", str_replace('\\', '\\\\', $value)) . "';\n";
	}
	unset($config_data_array);

	$config_data .= "\n// If you rename your admin folder, please change this value\n";
	$config_data .= "@define('CONFIG_ADM_FOLDER', 'adm');\n";
	$config_data .= "@define('PHPBB_INSTALLED', true);\n";
	$config_data .= "@define('DEBUG', true);\n";
	$config_data .= "@define('DEBUG_EXTRA', true);\n";
	$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!

	// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
	if ((file_exists(PHPBB_ROOT_PATH . 'config.' . PHP_EXT) && is_writable(PHPBB_ROOT_PATH . 'config.' . PHP_EXT)) || is_writable(PHPBB_ROOT_PATH))
	{
		// Assume it will work ... if nothing goes wrong below
		$written = true;

		if (!($fp = @fopen(PHPBB_ROOT_PATH . 'config.' . PHP_EXT, 'w')))
		{
			// Something went wrong ... so let's try another method
			$written = false;
		}

		if (!(@fwrite($fp, $config_data)))
		{
			// Something went wrong ... so let's try another method
			$written = false;
		}

		@fclose($fp);

		if ($written)
		{
			@chmod(PHPBB_ROOT_PATH . 'config.' . PHP_EXT, 0644);
		}
	}

	if (isset($_POST['dldone']))
	{
		// Do a basic check to make sure that the file has been uploaded
		// Note that all we check is that the file has _something_ in it
		// We don't compare the contents exactly - if they can't upload
		// a single file correctly, it's likely they will have other problems....
		if (filesize(PHPBB_ROOT_PATH . 'config.' . PHP_EXT) > 10)
		{
			$written = true;
		}
	}

	$config_options = array_merge($this->db_config_options, $this->admin_config_options);

	foreach ($config_options as $config_key => $vars)
	{
		if (!is_array($vars))
		{
			continue;
		}
		$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
	}

	if (!$written)
	{
		// OK, so it didn't work let's try the alternatives

		if (isset($_POST['dlconfig']))
		{
			// They want a copy of the file to download, so send the relevant headers and dump out the data
			header('Content-Type: text/x-delimtext; name="config.' . PHP_EXT . '"');
			header('Content-disposition: attachment; filename=config.' . PHP_EXT);
			echo $config_data;
			exit;
		}

		// The option to download the config file is always available, so output it here
		$template->assign_vars(array(
			'BODY'					=> $lang['CONFIG_FILE_UNABLE_WRITE'],
			'L_DL_CONFIG'			=> $lang['DL_CONFIG'],
			'L_DL_CONFIG_EXPLAIN'	=> $lang['DL_CONFIG_EXPLAIN'],
			'L_DL_DONE'				=> $lang['DONE'],
			'L_DL_DOWNLOAD'			=> $lang['DL_DOWNLOAD'],
			'S_HIDDEN'				=> $s_hidden_fields,
			'S_SHOW_DOWNLOAD'		=> true,
			'U_ACTION'				=> $this->p_master->module_url . "?mode=$mode&amp;sub=config_file",
		));
		return;
	}
	else
	{
		$template->assign_vars(array(
			'BODY'		=> $lang['CONFIG_FILE_WRITTEN'],
			'L_SUBMIT'	=> $lang['NEXT_STEP'],
			'S_HIDDEN'	=> $s_hidden_fields,
			'U_ACTION'	=> $this->p_master->module_url . "?mode=$mode&amp;sub=advanced",
		));
		return;
	}
}