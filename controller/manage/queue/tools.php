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

namespace phpbb\titania\controller\manage\queue;

use phpbb\titania\ext;

class tools
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \titania_revision */
	protected $revision;

	/** @var \titania_contribution */
	protected $contrib;

	/** @var \phpbb\titania\attachment\operator */
	protected $attachments;

	/** @var \phpbb\titania\attachment\attachment */
	protected $attachment;

	/** @var \phpbb\titania\entity\package */
	protected $package;

	/** @var \titania_queue */
	protected $queue;

	/** @var int */
	protected $id;

	/**
	* Constructor
	*
	* @param \phpbb\user $user
	* @param \phpbb\template\template $template
	* @param \phpbb\request\request_interface $request
	* @param \phpbb\titania\controller\helper $helper
	* @param \phpbb\titania\config\config $ext_config
	* @param \phpbb\titania\attachment\operator $attachments
	*/
	public function __construct(\phpbb\user $user, \phpbb\template\template $template, \phpbb\request\request_interface $request, \phpbb\titania\controller\helper $helper, \phpbb\titania\config\config $ext_config, \phpbb\titania\attachment\operator $attachments)
	{
		$this->user = $user;
		$this->template = $template;
		$this->request = $request;
		$this->helper = $helper;
		$this->ext_config = $ext_config;
		$this->attachments = $attachments;

		$this->user->add_lang_ext('phpbb/titania', array('contributions', 'manage'));
		$this->user->add_lang('viewtopic');
	}

	/**
	* Run requested tool.
	*
	* @param string $tool		Tool.
	* @param int $id			Revision id.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function run_tool($tool, $id)
	{
		if ($tool != 'epv')
		{
			return $this->helper->error('INVALID_TOOL', 404);
		}

		// Check the hash first to avoid unnecessary queries.
		if (!check_link_hash($this->request->variable('hash', ''), 'queue_tool'))
		{
			return $this->helper->error('PAGE_REQUEST_INVALID');
		}

		$this->load_objects($id);

		if (!$this->contrib->type->acl_get('view'))
		{
			return $this->helper->needs_auth();
		}

		return $this->{$tool}();
	}

	/**
	* Run Extension PreValidator.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function epv()
	{
		if (!$this->contrib->type->epv_test)
		{
			return $this->helper->error('INVALID_TOOL');
		}
		$this->package->ensure_extracted();

		$results = $this->contrib->type->get_prevalidator()
			->run_epv($this->package->get_temp_path());

		$results = $this->get_result_post('VALIDATION_PV', $results);
		$post = $this->queue->topic_reply($results);

		$this->package->cleanup();

		redirect($post->get_url());
	}

	/**
	* Load objects needed to run a tool.
	*
	* @param int $id		Revision id.
	* @return void
	*/
	protected function load_objects($id)
	{
		$this->load_revision($id);
		$this->load_contrib();
		$this->load_queue();
		$this->load_attachment();
		$this->load_package();
	}

	/**
	* Load revision.
	*
	* @throws \Exception Throws exception if no revision found.
	* @return void
	*/
	protected function load_revision($id)
	{
		$this->id = (int) $id;
		$this->revision = new \titania_revision(false, $this->id);

		if (!$this->id || !$this->revision->load())
		{
			throw new \Exception($this->user->lang['NO_REVISION']);
		}
	}

	/**
	* Load revision's parent contribution.
	*
	* @throws \Exception Throws exception if no contrib found.
	* @return void
	*/
	protected function load_contrib()
	{
		$this->contrib = new \titania_contribution;

		if (!$this->contrib->load($this->revision->contrib_id) || !$this->contrib->is_visible())
		{
			throw new \Exception($this->user->lang['CONTRIB_NOT_FOUND']);
		}
		$this->revision->contrib = $this->contrib;
	}

	/**
	* Load revision's corresponding queue item.
	*
	* @return void
	*/
	protected function load_queue()
	{
		$this->queue = $this->revision->get_queue();
	}

	/**
	* Load revision attachment.
	*
	* @throws \Exception Throws exception if no attachment found.
	* @return void
	*/
	protected function load_attachment()
	{
		$this->attachments
			->configure(ext::TITANIA_CONTRIB, $this->contrib->contrib_id)
			->load(array($this->revision->attachment_id))
		;
		$this->attachment = $this->attachments->get($this->revision->attachment_id);

		if (!$this->attachment)
		{
			throw new \Exception($this->user->lang['ERROR_NO_ATTACHMENT']);
		}
	}

	/**
	 * Load revision package.
	 */
	protected function load_package()
	{
		$this->package = new \phpbb\titania\entity\package;
		$this->package
			->set_source($this->attachment->get_filepath())
			->set_temp_path($this->ext_config->__get('contrib_temp_path'), true)
		;
	}

	/**
	* Get formatted result to insert into a post.
	*
	* @param string $title		Title language key.
	* @param string $result		Tool results.
	*
	* @return string
	*/
	protected function get_result_post($title, $result)
	{
		$title = $this->user->lang($title);

		return "$title\n[quote=&quot;$title&quot;]{$result}[/quote]\n";
	}
}
