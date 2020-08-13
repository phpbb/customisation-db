<?php
/**
 *
 * This file is part of the phpBB Customisation Database package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\titania\controller;

use phpbb\titania\ext;
use Symfony\Component\HttpFoundation\JsonResponse;

class package_builder
{
	const COOKIE_NAME = 'cdb_package_builder';

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var string */
	protected $ext_root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template $template
	 * @param \phpbb\config\config $config
	 * @param \phpbb\user $user
	 * @param \phpbb\language\language $language
	 * @param \phpbb\titania\controller\helper $helper
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\request\request_interface $request
	 * @param \phpbb\titania\config\config $ext_config
	 * @param $ext_root_path
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\config\config $config, \phpbb\user $user, \phpbb\language\language $language, \phpbb\titania\controller\helper $helper, \phpbb\titania\cache\service $cache, \phpbb\request\request_interface $request, \phpbb\titania\config\config $ext_config, $ext_root_path)
	{
		$this->db = $db;
		$this->template = $template;
		$this->config = $config;
		$this->user = $user;
		$this->language = $language;
		$this->helper = $helper;
		$this->cache = $cache;
		$this->request = $request;
		$this->ext_config = $ext_config;
		$this->ext_root_path = $ext_root_path;
	}

	/**
	 * Get the cookie name
	 * @return string
	 */
	private function get_cookie_name()
	{
		return $this->config['cookie_name'] . '_' . self::COOKIE_NAME;
	}

	/**
	 * Save the cookie
	 * @param $cookie_value
	 */
	private function save_cookie($cookie_value)
	{
		// One hour expiry
		$this->user->set_cookie(self::COOKIE_NAME, $cookie_value, time() + (60 * 60));
	}

	/**
	 * Get the revisions and contributions already selected out of the cookie value
	 * @param $value
	 * @return array
	 */
	public static function split_cookie_values($value)
	{
		$results = [
			'contribs' => [],
			'revisions' => [],
		];

		$values = explode(',', $value);

		foreach ($values as $item)
		{
			if (strlen($item) > 0)
			{
				$split = explode('|', $item);

				$results['contribs'][] = $split[0];
				$results['revisions'][] = $split[1];
			}
		}

		return $results;
	}

	/**
	 * Create a json response
	 * @param $success
	 * @param $values
	 * @param string $message
	 * @return JsonResponse
	 */
	private function create_json_response($success, $values, $message = '')
	{
		$json = [
			'success' => $success,
			'values' => $values,
		];

		if (!empty($message))
		{
			$json['message'] = $message;
		}

		return new JsonResponse($json);
	}

	/**
	 * Clear the existing values saved in the cookie
	 */
	public function reset()
	{
		$this->save_cookie('');

		return $this->create_json_response(true, '');
	}

	/**
	 * Download the completed package
	 * @throws \phpbb\titania\entity\UnknownPropertyException
	 */
	public function download()
	{
		$versions = $this->cache->get_phpbb_versions();
		$latest_version = reset($versions);

		$phpbb_package = $this->ext_root_path . 'includes/phpbb_packages/phpBB-' . $latest_version . '.zip';
		$extract_path = $this->ext_config->__get('contrib_temp_path') . 'tmp/package_' . $this->user->data['user_id'];

		$zip = new \ZipArchive();

		if ($zip->open($phpbb_package))
		{
			// Unzip the revision to a temporary folder
			$zip->extractTo($extract_path);
			$zip->close();

			// Take the revisions from the cookies, and plug them into the phpBB3 instance
			// extensions to ext/, styles to style/ and language packs to language/
			$existing_cookie = $this->request->variable($this->get_cookie_name(), '', false, \phpbb\request\request_interface::COOKIE);
			$existing_values = self::split_cookie_values($existing_cookie);

			// Get the attachments
			$sql = 'SELECT r.revision_id, c.contrib_id, a.attachment_directory, a.physical_filename, c.contrib_type
					FROM ' . TITANIA_REVISIONS_TABLE . ' r, ' . TITANIA_ATTACHMENTS_TABLE . ' a, ' . TITANIA_CONTRIBS_TABLE . ' c
					WHERE r.attachment_id = a.attachment_id
						AND c.contrib_id = r.contrib_id
					AND ' . $this->db->sql_in_set('r.revision_id', $existing_values['revisions']);

			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				// TODO: Check that the revision ID hasn't been manipulated and this user has access to download the revision

				$contribution_archive = $this->ext_config->upload_path . $row['attachment_directory'] . '/' . $row['physical_filename'];

				$zip = new \ZipArchive();

				if ($zip->open($contribution_archive))
				{
					if ($row['contrib_type'] == ext::TITANIA_TYPE_EXTENSION)
					{
						$zip->extractTo($extract_path . '/phpBB3/ext');
					}

					if ($row['contrib_type'] == ext::TITANIA_TYPE_STYLE)
					{
						$zip->extractTo($extract_path . '/phpBB3/style');
					}

					if ($row['contrib_type'] == ext::TITANIA_TYPE_TRANSLATION)
					{
						// TODO: talk to Crizzo about where these need to end up being moved to
						$zip->extractTo($extract_path . '/phpBB3/language');
					}

					$zip->close();
				}
			}

			// TODO: Zip up the final package and send it to the browser
		}
	}

	/**
	 * Add a revision ID
	 * @param $contrib
	 * @param $revision
	 * @return JsonResponse
	 */
	public function add($contrib, $revision)
	{
		$json = null;

		$existing_cookie = $this->request->variable($this->get_cookie_name(), '', false, \phpbb\request\request_interface::COOKIE);

		// Validate whether it's already added
		$existing_values = self::split_cookie_values($existing_cookie);

		if (in_array($contrib, $existing_values['contribs']) || in_array($revision, $existing_values['revisions']))
		{
			// This contribution (or revision) has already been added
			$json = $this->create_json_response(false, $existing_values, $this->language->lang('PACKAGE_ALREADY_ADDED'));
		}

		else
		{
			// Cookies are my favourite food group, cookies taste nice. And in this case they are lightweight :D
			$value = $contrib . '|' . $revision;
			$cookie_value = (!empty($existing_cookie)) ? sprintf('%s,%s', $existing_cookie, $value) : $value;

			// Set the cookie value; expire after an hour (plenty of time for the user to download the package)
			$this->save_cookie($cookie_value);

			$updated_values = self::split_cookie_values($cookie_value);
			$download_link = sprintf('<a href="%s">%s</a>', $this->helper->route('phpbb.titania.package_builder.download'), $this->language->lang('PACKAGE_ADDED', count($updated_values['contribs'])));

			$json = $this->create_json_response(true, $updated_values, $download_link);
		}

		return $json;
	}
}
