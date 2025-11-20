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

	/* @var array */
	private $tables;

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

		/*if (false)
		{
			throw new \phpbb\exception\http_exception(401, 'CUSTDB_NOT_ENABLED');
		}*/

		$this->tables = $this->manager->get_tables();
	}

	/**
	* Validate contribution
	*
	* @param int $contribution_id The ID of the contribution to validate.
	*/
	public function validate(int $contribution_id)
	{
		if ($this->manager->is_team_member())
		{
			// Check if form submitted
			if ($this->request->is_set_post('submit'))
			{
				// Validate form token for CSRF
				if (!check_form_key('custdb_view_contribution'))
				{
					trigger_error('FORM_INVALID');
				}

				// Gather status and comment
				$contribution_validation_status = $this->request->variable('validation_status', '', true);
				$contribution_validation_comment = $this->request->variable('validation_comment', '', true);

				// Update status
				$this->manager->update_external_validation_status($contribution_id, $contribution_validation_status);

				// TODO: add post to validation topic !!!
				// ???
			}
		}

		$response = new \Symfony\Component\HttpFoundation\RedirectResponse($this->helper->route('custdb_view_contribution', ['contribution_id' => $contribution_id]), 301);
		$response->send();
	}

	/**
	* View contribution
	*
	* @param int $contribution_id The ID of the contribution to view.
	*
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function view(int $contribution_id)
	{
		// Get contribution data via manager
		// TODO: get the contribution, then get the revision
		$contribution = $this->manager->get_contribution_with_latest_revision($contribution_id);

		// If no contribution is found
		if (!$contribution)
		{
			trigger_error('CUSTDB_CONTRIBUTION_NOT_FOUND');
		}

		//TODO: upload to the ext folder instead in the future
		//$ext_path = $this->manager->get_ext_manager()->get_extension_path('phpbb/oberon', true);
		$ext_path = $this->config['script_path'] . 'files/contributions';

		// Team member or author
		$can_add_revision = $this->manager->is_team_member() || $this->manager->is_customisation_author($contribution_id);

		// Assign template variables
		$this->template->assign_vars([
			// Core contribution data
			'CONTRIBUTION_ID'       	=> $contribution['contribution_id'],
			'CONTRIBUTION_NAME'     	=> $contribution['contribution_name'],
			'CONTRIBUTION_DESCRIPTION'  => $contribution['contribution_description'],
			'AUTHORS'               	=> $contribution['author_name'],
			'VERSION_NUMBER'        	=> $contribution['revision_version'],
			'DEMO_LINK'             	=> $contribution['contribution_demo_link'],
			'EXTERNAL_STATUS'       	=> $contribution['external_status_label'],
			'INTERNAL_STATUS'       	=> $contribution['internal_status_label'],

			// First screenshot or empty string
			'CONTRIBUTION_IMAGE'    => !empty($contribution['screenshots'][0])
				? $ext_path . '/' . $contribution['screenshots'][0]
				: '',

			// Links
			'U_NEW_REVISION'		=> $can_add_revision ? $this->helper->route('custdb_add_revision', ['contribution_id' => $contribution_id]) : false,

			// Actions
			'U_EDIT_CONTRIBUTION'   => '', //$this->helper->route('phpbb_oberon_edit_contribution', ['id' => $contribution_id]),
			'U_VALIDATE_CONTRIBUTION' => $this->helper->route('phpbb_oberon_validate_contribution', ['contribution_id' => $contribution_id]),
			'VALIDATION_STATUS' 		=> $contribution['contribution_status'], // This is the publicly seen status (unvalidated, approved, denied)
			'VALIDATE_UNVALIDATED'		=> $this->manager::STATUS_UNVALIDATED,
			'VALIDATE_APPROVED'			=> $this->manager::STATUS_APPROVED,
			'VALIDATE_DENIED'			=> $this->manager::STATUS_DENIED,

			'S_IS_TEAM_MEMBER'	=> $this->manager->is_team_member(), // TODO: Could this be availble everywhere in Oberon templates??
		]);

		add_form_key('custdb_view_contribution');

		// Render the template
		return $this->helper->render('custdb_view_contribution_body.html', $this->user->lang('CUSTDB_VIEW_CONTRIBUTION'));
	}	

	/**
	* Add contribution
	*
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function add_contribution()
	{
		$this->generic_contribution_or_revision();

		$this->template->assign_vars([
			'U_ACTION'				=> $this->helper->route('custdb_add_contribution'),

			'S_IS_NEW_CONTRIBUTION' => true,

			// Page heading
			'L_PAGE_HEADING'       => $this->user->lang('CUSTDB_ADD_CONTRIBUTION'),
		]);

   		return $this->helper->render('custdb_add_contribution_body.html', $this->user->lang('CUSTDB_ADD_CONTRIBUTION'));     
    }

	/**
	* Add revision
	*
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function add_revision(int $contribution_id)
	{
		// Get the existing contribution details
		$contribution = $this->manager->get_contribution_with_latest_revision($contribution_id);

		$this->generic_contribution_or_revision();

		$this->template->assign_vars([
			'U_ACTION'				=> $this->helper->route('custdb_add_revision', ['contribution_id' => $contribution_id]),
			'S_IS_NEW_CONTRIBUTION' => false,

			// Page heading
			'L_PAGE_HEADING'       => $this->user->lang('CUSTDB_ADD_REVISION'),

			// Current contribution
			'CONTRIBUTION_NAME'		=> $contribution['contribution_name'],
			'CONTRIBUTION_ID'		=> $contribution_id,
		]);

		return $this->helper->render('custdb_add_contribution_body.html', $this->user->lang('CUSTDB_ADD_REVISION'));     
	}

	/**
	 * Can use similar functionality for adding both new contributions or revisions
	 */
	private function generic_contribution_or_revision()
	{
		// Check if form submitted
        if ($this->request->is_set_post('submit'))
        {
            // Validate form token for CSRF
            if (!check_form_key('custdb_add_contribution_or_revision'))
            {
                trigger_error('FORM_INVALID');
            }

            $contribution_name = $this->request->variable('contribution_name', '', true);
			$revision_name = $this->request->variable('revision_name', '', true);
            $version_number = $this->request->variable('version_number', '', true);

			$can_submit_revision = false;
			$contribution_id = $this->request->variable('contribution_id', 0);

			if ($contribution_id)
			{
				// If there is already a contribution id, then we are just adding a new revision.
				// Check that the author can do it though!
				if ($this->manager->is_customisation_author($contribution_id) || $this->manager->is_team_member())
				{
					$can_submit_revision = true;

					// Validation logic here for revision
					if (empty($version_number))
					{
						trigger_error('CUSTDB_MISSING_REQUIRED_FIELDS');
					}
				}
			}

			else 
			{
				// Validation logic here for new contribution
				if (empty($contribution_name) || empty($version_number))
				{
					trigger_error('CUSTDB_MISSING_REQUIRED_FIELDS');
				}
			}

            // Insert into customisations and revisions table, first gather form data
			$type              			= $this->request->variable('contribution_type', 0);
			$contribution_description 	= $this->request->variable('contribution_description', '', true);
			$revision_description 		= $this->request->variable('revision_description', '', true);
			$demo_link         			= $this->request->variable('demo_link', '', true);
			$version        			= $this->request->variable('version_number', '', true);
			$phpbb_version  			= $this->request->variable('phpbb_version', '');
			$user_id           			= (int) $this->user->data['user_id'];

			if (!$contribution_id)
			{
				$contribution_array = [
					'contribution_name'        => $contribution_name,
					'contribution_description' => $contribution_description,
					'contribution_type'        => $type,
					'contribution_status'      => $this->manager::STATUS_UNVALIDATED,
					'contribution_demo_link'   => $demo_link,
					'user_id'                  => $user_id,
					'submission_time'          => time(),
				];

				$contribution_id = (int) $this->manager->add_contribution($contribution_array);
			}

			$upload_path = $this->root_path . 'files/contributions/';
			$revision_file_name = '';
			$screenshot_file_names = [];

			// Handle contribution package uploads (TODO: this needs better validation)
			$contribution_file = $this->request->file('contribution_file');

			if (!empty($contribution_file['name']))
			{
				$revision_file_name = time() . '_' . basename($contribution_file['name']);

				if (!move_uploaded_file($contribution_file['tmp_name'], $upload_path . $revision_file_name))
				{
					trigger_error('CUSTDB_FILE_UPLOAD_FAILED');
				}
			}

			// Fetch multiple potential attachments
			$screenshots = $this->request->raw_variable('screenshots', [], \phpbb\request\request_interface::FILES);

			if (!empty($screenshots['name'][0])) // Check if at least one file was uploaded
			{
				foreach ($screenshots['name'] as $key => $name)
				{
					if (!empty($name))
					{
						$screenshot_name = time() . '_' . basename($name);

						if (move_uploaded_file($screenshots['tmp_name'][$key], $upload_path . $screenshot_name))
						{
							$screenshot_file_names[] = $screenshot_name;
						}

						else
						{
							trigger_error('CUSTDB_SCREENSHOT_UPLOAD_FAILED');
						}
					}
				}
			}

			// Store screenshots as comma-separated list
			$screenshot_list = implode(',', $screenshot_file_names);

			$revision_array = [
				'contribution_id'      		=> $contribution_id,
				'revision_name'        		=> $revision_name,
				'revision_version'     		=> $version,
				'revision_phpbb_version'	=> $phpbb_version,
				'revision_description' 		=> $revision_description,
				'revision_attachment'  		=> $revision_file_name,
				'revision_screenshots' 		=> $screenshot_list,
				'user_id'              		=> $user_id,
				'submission_time'      		=> time(),
			];

			$revision_id = $this->manager->add_revision($revision_array);

			// Add the revision to the queue
			$this->manager->add_revision_to_queue($revision_id);

			meta_refresh(3, $this->helper->route('custdb_index'));
			trigger_error($this->user->lang('CUSTDB_CONTRIBUTION_ADDED_SUCCESSFULLY'));
		}

        // Generate CSRF token
        add_form_key('custdb_add_contribution_or_revision');

		$this->template->assign_vars([
			'TYPE_EXTENSIONS'		=> $this->manager::TYPE_EXTENSIONS,
			'TYPE_STYLES'			=> $this->manager::TYPE_STYLES,
			'TYPE_TRANSLATIONS'		=> $this->manager::TYPE_TRANSLATIONS,
			'TYPE_BBCODES'			=> $this->manager::TYPE_BBCODES,
			'TYPE_TOOLS'			=> $this->manager::TYPE_TOOLS,
			'TYPE_ARCHIVE'			=> $this->manager::TYPE_ARCHIVE,

			'SUPPORTED_PHPBB_VERSIONS'	=> $this->manager::SUPPORTED_PHPBB_VERSIONS,
		]); 
	}

	/**
	* Index page
	*
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function index()
	{
		$type = $this->request->variable('type', 0); // Extension, style, translation, etc?
		$status = $this->request->variable('status', 0); // Approved, unvalidated, denied
		$sort = $this->request->variable('sort', 0); // By date, by name, etc
		$search_query = $this->request->variable('q', '', true);

        $contributions = $this->manager->get_contributions_for_index($type, $status, $sort, $search_query);
       
        foreach ($contributions as $contribution)
        {
            $this->template->assign_block_vars('contributions', [
				'U_VIEW_CONTRIBUTION' => $this->helper->route('custdb_view_contribution', ['id' => $contribution['contribution_id']]),
                
				'CONTRIBUTION_NAME' => $contribution['contribution_name'],
                'CONTRIBUTION_DESCRIPTION' => $contribution['contribution_description'],
				'CONTRIBUTION_UNVALIDATED' => $this->manager->is_team_member() && $contribution['revision_status'] < $this->manager::INTERNAL_STATUS_DENIED,
            ]);
        }

        $this->template->assign_vars([
			'U_CUSTDB_INDEX'		=> $this->helper->route('custdb_index'),
			'U_NEW_CONTRIBUTION' 	=> $this->helper->route('custdb_add_contribution'),

			// Filter options
			'STATUS_APPROVED'		=> $this->manager::STATUS_APPROVED,
			'STATUS_DENIED'			=> $this->manager::STATUS_DENIED,
			'STATUS_UNVALIDATED'	=> $this->manager::STATUS_UNVALIDATED,

			// Sort options
			'SORT_NAME'				=> $this->manager::SORT_NAME,
			'SORT_DATE'				=> $this->manager::SORT_DATE,

			'SEARCH_TERM'			=> $search_query,

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