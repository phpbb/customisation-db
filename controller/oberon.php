<?php
/**
*
* @package Customisation DB
* @copyright (c) 2025 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\oberon\controller;

/**
 * Controller
 */
class oberon
{
	/* @var string $root_path */
	protected $root_path;

	/* @var string $php_ext */
	protected $php_ext;

	/* @var \phpbb\request\request $request */
	protected $request;

	/* @var \phpbb\config\config $config */
	protected $config;

	/* @var \phpbb\controller\helper $helper */
	protected $helper;

	/* @var \phpbb\template\template $template */
	protected $template;

	/* @var \phpbb\user $user */
	protected $user;

	/* @var \phpbb\pagination $pagination */
	protected $pagination;

	/* @var \phpbb\language\language $language */
	protected $language;

	/* @var \phpbb\oberon\manager\manager $manager */
	protected $manager;

	/**
	* Constructor
	*
	* @param \phpbb\reqquest\request			$request
	* @param \phpbb\config\config				$config
	* @param \phpbb\controller\helper			$helper
	* @param \phpbb\template\template			$template
	* @param \phpbb\user						$user
	* @param \phpbb\language\language           $language
	* @param \phpbb\oberon\manager 		    	$manager
	*/
	public function __construct(string $root_path, string $php_ext, \phpbb\request\request $request, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\pagination $pagination, \phpbb\language\language $language, \phpbb\oberon\manager\manager $manager)
	{
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->request = $request;
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->pagination = $pagination;
		$this->language = $language;
		$this->manager = $manager;

		if (false)
		{
			throw new \phpbb\exception\http_exception(401, 'CUSTDB_NOT_ENABLED');
		}
	}

	/**
	* Add contribution/revision
	*
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function add()
	{
		// Check if form submitted
        if ($this->request->is_set_post('submit'))
        {
            // Validate form token for CSRF
            if (!check_form_key('custdb_add_contribution'))
            {
                trigger_error('FORM_INVALID');
            }

            $contribution_name = $this->request->variable('contribution_name', '', true);
            $version_number = $this->request->variable('version_number', '', true);

            // TODO: Validation logic here
            if (empty($contribution_name) || empty($version_number))
            {
                trigger_error('MISSING_REQUIRED_FIELDS');
            }

            // TODO: Save to DB here
            // Example: Insert into phpbb_customisations table

            meta_refresh(3, $this->helper->route('custdb_index'));
            trigger_error($this->user->lang('CONTRIBUTION_ADDED_SUCCESSFULLY'));
        }

        // Generate CSRF token
        add_form_key('custdb_add_contribution');

		$this->template->assign_vars([
			'U_ACTION'				=> $this->helper->route('custdb_add_contribution'),

			'TYPE_EXTENSIONS'		=> $this->manager::TYPE_EXTENSIONS,
			'TYPE_STYLES'			=> $this->manager::TYPE_STYLES,
			'TYPE_TRANSLATIONS'		=> $this->manager::TYPE_TRANSLATIONS,
			'TYPE_BBCODES'			=> $this->manager::TYPE_BBCODES,
			'TYPE_TOOLS'			=> $this->manager::TYPE_TOOLS,
			'TYPE_ARCHIVE'			=> $this->manager::TYPE_ARCHIVE,
		]); 

   		return $this->helper->render('custdb_add_contribution_body.html', $this->user->lang('CUSTDB_ADD_CONTRIBUTION'));     
    }

	/**
	* Index page
	*
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function index()
	{
		$type = $this->request->variable('type', 0);
		$status = $this->request->variable('status', 0);
		$sort = $this->request->variable('sort', 0);

        $contributions = $this->manager->get_contributions_for_index($type, $status, $sort);
       
        foreach ($contributions as $contribution)
        {
            $this->template->assign_block_vars('contributions', [
                'CONTRIBUTION_NAME' => $contribution['contribution_name'],
                'CONTRIBUTION_DESCRIPTION' => $contribution['contribution_description'],
            ]);
        }

        $this->template->assign_vars([
			'U_NEW_CONTRIBUTION' 	=> $this->helper->route('custdb_add_contribution'),

			// Filter options
			'STATUS_APPROVED'		=> $this->manager::STATUS_APPROVED,
			'STATUS_DENIED'			=> $this->manager::STATUS_DENIED,
			'STATUS_UNVALIDATED'	=> $this->manager::STATUS_UNVALIDATED,

			// Sort options
			'SORT_NAME'				=> $this->manager::SORT_NAME,
			'SORT_DATE'				=> $this->manager::SORT_DATE,

			// Sidebar links
			'TYPE'					=> $type,
			'TYPE_EXTENSIONS'		=> $this->manager::TYPE_EXTENSIONS,
			'TYPE_STYLES'			=> $this->manager::TYPE_STYLES,
			'TYPE_TRANSLATIONS'		=> $this->manager::TYPE_TRANSLATIONS,
			'TYPE_BBCODES'			=> $this->manager::TYPE_BBCODES,
			'TYPE_TOOLS'			=> $this->manager::TYPE_TOOLS,
			'TYPE_ARCHIVE'			=> $this->manager::TYPE_ARCHIVE,

			'U_TYPE_EXTENSIONS' 	=> $this->sidebar_route($this->manager::TYPE_EXTENSIONS),
			'U_TYPE_STYLES' 		=> $this->sidebar_route($this->manager::TYPE_STYLES),
			'U_TYPE_TRANSLATIONS' 	=> $this->sidebar_route($this->manager::TYPE_TRANSLATIONS),
			'U_TYPE_BBCODES' 		=> $this->sidebar_route($this->manager::TYPE_BBCODES),
			'U_TYPE_TOOLS' 			=> $this->sidebar_route($this->manager::TYPE_TOOLS),
			'U_TYPE_ARCHIVE' 		=> $this->sidebar_route($this->manager::TYPE_ARCHIVE),
		]); 

		
        /*
		// Pagination
		$per_page = (int) 10;
		$this->pagination->generate_template_pagination(
			$this->helper->route('custdb_index'), 
			'pagination', 
			'start', 
			$TOTAL, 
			$per_page, 
			$start
		);
		*/

		return $this->helper->render('custdb_index_body.html', $this->user->lang('CUSTDB_INDEX'));
	}

	/**
	 * Create sidebar route url
	 */
	private function sidebar_route(int $type)
	{
		return $this->helper->route('custdb_index', ['type' => $type]);
	}

    /**
	* Deliver response (for AJAX)
	*
	* @param int $id
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
    public function answer(int $id = 0)
	{
		// We will send a JSON response back
		$json_data = [];
		$json_data['error'] = 'xxx';

		// Send a json response back to the submit page
		$json_response = new \phpbb\json_response;
		return $json_response->send($json_data);
	}
}