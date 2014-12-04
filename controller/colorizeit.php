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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use phpbb\request\request_interface;
use phpbb\titania\contribution\style\colorizeit_helper;

class colorizeit
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_inteface */
	protected $request;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\user $user
	* @param \phpbb\request\request_inteface $request
	* @param \phpbb\titania\controller\helper $helper
	* @param \phpbb\titania\config\config $ext_config
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\titania\controller\helper $helper, \phpbb\titania\config\config $ext_config)
	{
		$this->db = $db;
		$this->user = $user;
		$this->request = $request;
		$this->helper = $helper;
		$this->ext_config = $ext_config;

		$this->user->add_lang('viewtopic');
	}

	/**
	* Output ColorizeIt data for a given revision in XML format.
	*
	* @param int $id		Attachment id.
	* @return Symfony\Component\HttpFoundation\Response
	*/
	public function colorizeit_data($id)
	{
		if (!strlen($this->ext_config->colorizeit) || !$this->check_auth())
		{
			return $this->error('ERROR_NO_ATTACHMENT');
		}

		try
		{
			$this->load($id);
		}
		catch (\Exception $e)
		{
			return $this->error($e->getMessage());
		}

		if (!$this->contrib->has_colorizeit())
		{
			return $this->error('CLR_ERROR_NOSAMPLE');
		}

		if (!empty($this->revision->revision_clr_options))
		{
			$options = unserialize($this->revision->revision_clr_options);
		}
		else
		{
			$colorizeit_helper = new colorizeit_helper;

			try
			{
				$options = $colorizeit_helper->generate_options($this->attachment->get_filepath());
			}
			catch (\Exception $e)
			{
				return $this->error($e->getMessage());
			}
			$colorizeit_helper->submit_options($options, $this->revision->revision_id, $this->db);
		}

		return new Response($this->get_xml_output($options), 200);
	}

	/**
	* Check authorization.
	*
	* @return bool Returns true if user is authorized to access page.
	*/
	protected function check_auth()
	{
		$var_name = $this->ext_config->colorizeit_var;

		if ($this->ext_config->colorizeit_auth == 'HEADER')
		{
			$var_name = 'HTTP_' . strtoupper(str_replace('-', '_', $var_name));
			$var_value = $this->request->server($var_name);
		}
		else
		{
			$var_value = $this->request->variable($var_name, '', false, request_interface::POST);
		}
		return $var_value === $this->ext_config->colorizeit_value;
	}

	/**
	* Get error response.
	*
	* @param string $error		Error to output
	* @return Symfony\Component\HttpFoundation\Response
	*/
	protected function error($error)
	{
		$error = $this->user->lang($error);
		$response = '<?xml version="1.0" encoding="UTF-8"?>' . "\n<error>$error</error>";

		return new Response($response);
	}

	/**
	* Load attachment, revision, and contribution needed to output
	* ColorizeIt info.
	*
	* @param int $attachment_id		Attachment id.
	* @return null
	*/
	protected function load($attachment_id)
	{
		$this->load_attachment($attachment_id);
		$this->load_revision();
		$this->load_contribution();
	}

	/**
	* Load attachment.
	*
	* @param int $id		Attachment id.
	* @throws \Exception	Throws exception if attachment does not exist
	*	or cannot be accessed.
	* @return null
	*/
	protected function load_attachment($id)
	{
		$id = (int) $id;
		$this->attachment = new \titania_attachment(TITANIA_CONTRIB);

		if (!$id || !$this->attachment->load($id) || !$this->check_attachment_auth())
		{
			throw new \Exception('ERROR_NO_ATTACHMENT');
		}
	}

	/**
	* Check whether user can access attachment.
	*
	* @return bool
	*/
	protected function check_attachment_auth()
	{
		return $this->attachment->object_type == TITANIA_CONTRIB &&
			!$this->attachment->is_orphan &&
			$this->attachment->attachment_access == TITANIA_ACCESS_PUBLIC;
	}

	/**
	* Load revision.
	*
	* @throws \Exception	Throws \Exception if revision cannot be loaded.
	* @return null
	*/
	protected function load_revision()
	{
		$sql = 'SELECT revision_id, contrib_id, revision_status, revision_clr_options
			FROM ' . TITANIA_REVISIONS_TABLE . '
    		WHERE  attachment_id = ' . (int) $this->attachment->attachment_id;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult();

		if (!$data || $data['revision_status'] != TITANIA_REVISION_APPROVED)
		{
			throw new \Exception('NO_REVISION');
		}
		$this->revision = new \titania_revision(false);
		$this->revision->__set_array($data);
	}

	/**
	* Load contribution.
	*
	* @throws \Exception	Throws \Exception if contribution cannot be loaded.
	* @return null
	*/
	protected function load_contribution()
	{
		$this->contrib = new \titania_contribution;

		if (!$this->contrib->load($this->revision->contrib_id) || !$this->contrib->is_visible())
		{
			throw new \Exception('NO_CONTRIB');
		}
	}

	/**
	* Generate XML data ouput.
	*
	* @param array $data
	* @return string
	*/
	protected function get_xml_output($data)
	{
		$tab = "\t";
		$newline = "\n";
		$properties = array(
			'name'				=> $this->contrib->contrib_name,
			'file_src'			=> $this->get_file_source($this->attachment->attachment_id),
			'file_created'		=> $this->attachment->filetime,
			'sample_src'		=> $this->get_file_source($this->contrib->clr_sample['attachment_id']),
			'sample_created'	=> $this->contrib->clr_sample['filetime'],
			'colors'			=> $this->contrib->contrib_clr_colors,
			'parser'			=> $data['parser'],
		);

		$properties = array_map('htmlspecialchars', $properties);

		$xml =
			'<?xml version="1.0" encoding="UTF-8"?>'		. $newline .
			'<colorizeit>'									. $newline .
			$tab . '<name>%1$s</name>'						. $newline .
			$tab . '<file src="%2$s" created="%3$s" />'		. $newline .
			$tab . '<sample src="%4$s" created="%5$s" />'	. $newline .
			$tab . '<colors>%6$s</colors>'					. $newline .
			$tab . '<parser>%7$s</parser>'					. $newline
		;
		$xml = vsprintf($xml, $properties);

		if ($data['options'])
		{
			foreach($data as $key => $value)
			{
				if ($this->is_valid_option($data['parser'], $key))
				{
					$xml .= $tab . '<option name="' . htmlspecialchars($key) . '">' . htmlspecialchars($value) . '</option>' . $newline;
				}
			}

			if (isset($data['phpbb3_dir']))
			{
				$xml .= $tab . '<filename>' . htmlspecialchars($data['phpbb3_dir']) . '.zip</filename>' . $newline;
			}
		}

		$xml .= '</colorizeit>';

		return $xml;
	}

	/**
	* Get absolute path to an attachment.
	*
	* @param int $id		Attachment id.
	* @return string
	*/
	protected function get_file_source($id)
	{
		return $this->helper->route(
			'phpbb.titania.download',
			array('id' => $id),
			true,
			false,
			UrlGeneratorInterface::ABSOLUTE_PATH
		);
	}

	/**
	* Check whether the given option is valid for the parser.
	*
	* @param string $parser
	* @param string $option
	*
	* @return bool
	*/
	protected function is_valid_option($parser, $option)
	{
		return strpos($option, $parser) === 0;
	}
}
