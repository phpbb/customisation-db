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

namespace phpbb\titania\controller\manage\tool;


use phpbb\auth\auth;
use phpbb\exception\http_exception;
use phpbb\request\request_interface;
use phpbb\titania\controller\helper;
use phpbb\titania\display;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;

class tool
{
	/** @var request_interface */
	protected $request;

	/** @var auth */
	protected $auth;

	/** @var user */
	protected $user;

	/** @var helper */
	protected $helper;

	/** @var display */
	protected $display;

	/** @var object */
	protected $tool;

	/**
	 * Constructor
	 *
	 * @param request_interface $request
	 * @param auth $auth
	 * @param user $user
	 * @param helper $helper
	 * @param display $display
	 */
	public function __construct(request_interface $request, auth $auth, user $user, helper $helper, display $display)
	{
		$this->request = $request;
		$this->auth = $auth;
		$this->user = $user;
		$this->helper = $helper;
		$this->display = $display;
	}

	/**
	 * Set tool instance.
	 *
	 * @param object $tool
	 */
	public function set_tool($tool)
	{
		$this->tool = $tool;
	}

	/**
	 * Handle running tool.
	 *
	 * @return JsonResponse
	 */
	public function handle()
	{
		$this->user->add_lang_ext('phpbb/titania', 'manage_tools');
		$this->check_auth();

		$result = $this->run();
		$result['message'] = $this->user->lang($result['message']);

		$next_call = null;

		if ($result['next_batch'] !== false)
		{
			$params = $this->get_next_params($result);
			$next_call = $this->helper->route($this->tool->get_route(), $params);
		}

		if ($this->request->is_ajax())
		{
			return new JsonResponse(array_merge($result, array(
				'next_call'	=> str_replace('&amp;', '&', $next_call),
			)));
		}

		if ($next_call)
		{
			meta_refresh(3, $next_call);
		}
		return $this->helper->message($result['message']);
	}

	/**
	 * Get parameters for next call.
	 *
	 * @param array $result Tool run result array.
	 * @return array
	 */
	protected function get_next_params(array $result)
	{
		$params = array(
			'start'		=> $result['next_batch'],
			'hash'		=> generate_link_hash('titania_manage'),
			'submit'	=> 1,
		);

		if ($result['next_step'])
		{
			$params['step'] = $result['next_step'];
		}
		return $params;
	}

	/**
	 * Run tool.
	 *
	 * @return array
	 */
	protected function run()
	{
		$start = $this->request->variable('start', 0);
		$step = $this->request->variable('step', '');

		return $this->tool
			->set_start($start)
			->set_step($step)
			->run()
		;
	}

	/**
	 * Check auth.
	 *
	 * @throws http_exception If not authorized
	 */
	protected function check_auth()
	{
		if (!$this->auth->acl_get('u_titania_admin'))
		{
			throw new http_exception(403, 'NO_AUTH');
		}
		if (!$this->confirm_action())
		{
			throw new http_exception(200, 'PAGE_REQUEST_INVALID');
		}
	}

	/**
	 * Confirm tool action.
	 *
	 * @return bool Returns true if confirmed, false otherwise.
	 */
	protected function confirm_action()
	{
		$submit = $this->request->is_set('submit');
		$hash = $this->request->variable('hash', '');

		if (confirm_box(true) || ($submit && check_link_hash($hash, 'titania_manage')))
		{
			return true;
		}
		else
		{
			confirm_box(false, $this->user->lang('CONFIRM_TOOL_ACTION'));
			return false;
		}
	}
}
