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
	* Validate revision
	*
	* @param int $contribution_id The ID of the contribution to validate.
	* @param int $queue_id ID of the queue item
	*/
	public function validate(int $contribution_id, int $queue_id)
	{
		if ($this->manager->is_team_member())
		{
			// Check if form submitted
			if ($this->request->is_set_post('submit'))
			{
				// Validate form token for CSRF
				if (!check_form_key('custdb_view_revision'))
				{
					trigger_error('FORM_INVALID');
				}

				// Gather status and comment
				$contribution_validation_status = $this->request->variable('validation_status', 0, true);
				$contribution_validation_comment = $this->request->variable('validation_comment', '', true);

				// Ensure the private validation topic exists and store it
				$contribution = $this->manager->get_contribution_with_revision($contribution_id);
				
				// Get old and new status names for status change message
				// $old_status_name = $this->manager->get_external_status($contribution['contribution_status']);
				$new_status_name = $this->manager->get_external_status($contribution_validation_status);
				
				// Prepend status change message to the validation comment
				$status_change_message = $this->language->lang(
					'CUSTDB_STATUS_MANUAL_CHANGE',
					$new_status_name
				);

				$validation_comment_with_status = $status_change_message . "\n\n" . $contribution_validation_comment;

				// Update status
				$this->manager->update_external_validation_status($contribution_id, $contribution_validation_status);

				// Create a topic for the validation comments (or if it already exists, just add a post to it)
				$private_topic_id = $this->manager->create_or_append_forum_comment($contribution_id, $this->manager->get_settings()['private.contribution.validation.forum.id']['default'], $contribution['contribution_validation_topic_id'], $contribution['contribution_name'], $validation_comment_with_status);
				
				// If there is no pre-existing validation topic, update the value associated with the contribution record
				if (isset($contribution['contribution_validation_topic_id']) && $contribution['contribution_validation_topic_id'] == 0 && $private_topic_id > 0)
				{
					$this->manager->update_contribution_validation_topic_id($contribution_id, $private_topic_id);
				}

				// This confirms a manual approval
				if ($contribution_validation_status === $this->manager::STATUS_APPROVED)
				{
					$this->manager->update_internal_queue_status($queue_id, $this->manager::INTERNAL_STATUS_APPROVED);

					// Now update the release topic! TODO: hard coded URL needs to change?
					$download_url = '/files/contributions/' . basename($contribution['revision_attachment']);

					$release_comment = $this->language->lang(
						'CUSTDB_CONTRIBUTION_APPROVED', 
						$contribution['contribution_name'],
						$download_url,
						$contribution_validation_comment // TODO: maybe have a public release comment here instead? Or no comment at all?
					);

					$public_topic_id = $this->manager->create_or_append_forum_comment($contribution_id, $this->manager->get_settings()['public.contribution.release.forum.id']['default'], $contribution['contribution_release_topic_id'], $contribution['contribution_name'], $release_comment);
					
					// If there is no release topic, update the value associated with the contribution record
					if (isset($contribution['contribution_release_topic_id']) && $contribution['contribution_release_topic_id'] == 0 && $public_topic_id > 0)
					{
						$this->manager->update_contribution_release_topic_id($contribution_id, $public_topic_id);
					}
				}

				else if ($contribution_validation_status === $this->manager::STATUS_DENIED) 
				{
					// Update the internal status
					$this->manager->update_internal_queue_status($queue_id, $this->manager::INTERNAL_STATUS_DENIED);
				}
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
	public function view_contribution(int $contribution_id)
	{
		// Get contribution data via manager
		// TODO: get the contribution, then get the revision
		$contribution = $this->manager->get_contribution_with_revision($contribution_id, true);

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
			'CONTRIBUTION_TYPE'			=> $this->manager->contribution_type_mapping()[$contribution['contribution_type']],
			'CONTRIBUTION_NAME'     	=> $contribution['contribution_name'],
			'CONTRIBUTION_DESCRIPTION'  => $contribution['contribution_description'],
			'AUTHORS'               	=> $contribution['author_name'],
			'VERSION_NUMBER'        	=> $contribution['revision_version'],
			'DEMO_LINK'             	=> $contribution['contribution_demo_link'],
			'EXTERNAL_STATUS'       	=> $contribution['external_status_label'],

			// First screenshot or empty string
			'CONTRIBUTION_IMAGE'    => !empty($contribution['screenshots'][0])
				? $ext_path . '/' . $contribution['screenshots'][0]
				: '',

			// Links
			'U_NEW_REVISION'		=> $can_add_revision ? $this->helper->route('custdb_add_revision', ['contribution_id' => $contribution_id]) : false,

			// Actions
			'U_EDIT_CONTRIBUTION'   => '', //$this->helper->route('phpbb_oberon_edit_contribution', ['id' => $contribution_id]),
			'VALIDATION_STATUS' 		=> $contribution['contribution_status'], // This is the publicly seen status (unvalidated, approved, denied)
			'VALIDATE_UNVALIDATED'		=> $this->manager::STATUS_UNVALIDATED,
			'VALIDATE_APPROVED'			=> $this->manager::STATUS_APPROVED,
			'VALIDATE_DENIED'			=> $this->manager::STATUS_DENIED,

			'S_IS_TEAM_MEMBER'	=> $this->manager->is_team_member(), // TODO: Could this be availble everywhere in Oberon templates??
		]);

		add_form_key('custdb_view_contribution');

		// Render the template
		$revisions = $this->manager->get_revisions_for_contribution($contribution_id);
		$revision_list = [];

		foreach ($revisions as $revision)
		{
			$this->template->assign_block_vars('revisions', [
				'REVISION_NAME' 		=> $revision['revision_name'],
				'REVISION_VERSION' 		=> $revision['revision_version'],
				'REVISION_DESCRIPTION' 	=> $revision['revision_description'],

				'REVISION_UNVALIDATED' 	=> $this->manager->is_team_member() && $revision['revision_status'] === $this->manager::STATUS_UNVALIDATED,
				'REVISION_DENIED' 		=> $this->manager->is_team_member() && $revision['revision_status'] === $this->manager::STATUS_DENIED,

				'U_VIEW_REVISION' 		=> $this->helper->route('custdb_view_revision', ['contribution_id' => $contribution_id, 'revision_id' => $revision['revision_id']]),
			]);
		}

		$this->template->assign_vars([
			'U_VIEW_CONTRIBUTION'   => $this->helper->route('custdb_view_contribution', ['contribution_id' => $contribution_id]),
			'U_VALIDATE_CONTRIBUTION' => $this->helper->route('custdb_validate_contribution', ['contribution_id' => $contribution_id]),

			'VALIDATION_STATUS'     => $contribution['contribution_status'], // This is the publicly seen status (unvalidated, approved, denied)
			'VALIDATE_UNVALIDATED'  => $this->manager::STATUS_UNVALIDATED,
			'VALIDATE_APPROVED'     => $this->manager::STATUS_APPROVED,
			'VALIDATE_DENIED'       => $this->manager::STATUS_DENIED,

			'S_IS_TEAM_MEMBER'      => $this->manager->is_team_member(), // TODO: Could this be availble everywhere in Oberon templates??
			'REVISIONS'             => $revision_list,
		]);

		// Breadcrumbs: Board Index -> Customisation Database
		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME' => $this->user->lang('CUSTDB_INDEX'),
			'U_BREADCRUMB'   => $this->helper->route('custdb_index'),
		]);

		return $this->helper->render('custdb_view_contribution_body.html', $this->user->lang('CUSTDB_VIEW_CONTRIBUTION'));
	}

	/**
	 * View a specific revision.
	 *
	 * @param int $contribution_id The ID of the contribution to which the revision belongs.
	 * @param int $revision_id The ID of the revision to view.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function view_revision(int $contribution_id, int $revision_id)
	{
		$revision = $this->manager->get_contribution_with_revision($contribution_id, false, $revision_id);

		if (!$revision)
		{
			trigger_error('CUSTDB_CONTRIBUTION_NOT_FOUND');
		}

		// Determine if the viewer may validate the revision
		$can_validate = $this->manager->is_team_member();

		// TODO: upload to the ext folder instead in the future
		//$ext_path = $this->manager->get_ext_manager()->get_extension_path('phpbb/oberon', true);
		$ext_path = $this->config['script_path'] . 'files/contributions';
		$screenshot_urls = [];

		foreach ($revision['screenshots'] as $screenshot)
		{
			if (!empty($screenshot))
			{
				$screenshot_urls[] = $ext_path . '/' . $screenshot;
			}
		}

		$this->template->assign_vars([
			'CONTRIBUTION_ID'        	=> $revision['contribution_id'],
			'CONTRIBUTION_NAME'      	=> $revision['contribution_name'],
			'CONTRIBUTION_DESCRIPTION' 	=> $revision['contribution_description'],
			'AUTHORS'               	=> $revision['author_name'],
			'CONTRIBUTION_IMAGE'     	=> !empty($screenshot_urls[0]) ? $screenshot_urls[0] : '',
			'CONTRIBUTION_DEMO_LINK' 	=> $revision['contribution_demo_link'],

			'U_VIEW_CONTRIBUTION'   		=> $this->helper->route('custdb_view_contribution', ['contribution_id' => $revision['contribution_id']]),
			'U_VALIDATE_CONTRIBUTION' 		=> $this->helper->route('custdb_validate_contribution', ['contribution_id' => $revision['contribution_id'], 'queue_id' => $revision['queue_id']]),
			'U_INTERNAL_VALIDATION_TOPIC'	=> (int) $revision['contribution_validation_topic_id'] ? append_sid('/viewtopic.php', 't=' . (int) $revision['contribution_validation_topic_id']) : '', //TODO: route for viewtopic?

			'REVISION_ID'           	=> $revision['revision_id'],
			'REVISION_NAME'         	=> $revision['revision_name'],
			'REVISION_DATE'				=> $this->user->format_date($revision['submission_time']),
			'REVISION_VERSION'      	=> $revision['revision_version'],
			'REVISION_DESCRIPTION'  	=> $revision['revision_description'],
			'REVISION_ATTACHMENT'   	=> $revision['revision_attachment'],
			'REVISION_ATTACHMENT_URL' 	=> !empty($revision['revision_attachment']) ? $ext_path . '/' . $revision['revision_attachment'] : '',
			'REVISION_SCREENSHOTS'  	=> $screenshot_urls,

			'REVISION_STATUS'       	=> $revision['revision_status_label'],
			'INTERNAL_STATUS'       	=> $revision['internal_status_label'],

			'VALIDATION_STATUS'     => $revision['revision_status'],
			'VALIDATE_UNVALIDATED'  => $this->manager::STATUS_UNVALIDATED,
			'VALIDATE_APPROVED'     => $this->manager::STATUS_APPROVED,
			'VALIDATE_DENIED'       => $this->manager::STATUS_DENIED,

			'S_IS_TEAM_MEMBER'      => $can_validate,
		]);

		add_form_key('custdb_view_revision');

		// Breadcrumbs: Board Index -> Customisation Database -> Contribution
		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME' => $this->user->lang('CUSTDB_INDEX'),
			'U_BREADCRUMB'   => $this->helper->route('custdb_index'),
		]);
		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME' => $revision['contribution_name'],
			'U_BREADCRUMB'   => $this->helper->route('custdb_view_contribution', ['contribution_id' => $revision['contribution_id']]),
		]);

		return $this->helper->render('custdb_view_revision_body.html', $this->user->lang('CUSTDB_VIEW_REVISION'));
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

		// Breadcrumbs: Board Index -> Customisation Database
		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME' => $this->user->lang('CUSTDB_INDEX'),
			'U_BREADCRUMB'   => $this->helper->route('custdb_index'),
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
		$contribution = $this->manager->get_contribution_with_revision($contribution_id, true);

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

		// Breadcrumbs: Board Index -> Customisation Database -> Contribution
		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME' => $this->user->lang('CUSTDB_INDEX'),
			'U_BREADCRUMB'   => $this->helper->route('custdb_index'),
		]);

		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME' => $contribution['contribution_name'],
			'U_BREADCRUMB'   => $this->helper->route('custdb_view_contribution', ['contribution_id' => $contribution_id]),
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
			$authors_input              = $this->request->variable('authors', '', true);
			
			// TODO: AI generated... check this!
			// Parse authors field to get user_id
			// If no authors provided, use the current user
			$user_id = (int) $this->user->data['user_id'];
			if (!empty($authors_input))
			{
				// Take the first author from the comma-separated list
				$author_names = array_map('trim', explode(',', $authors_input));
				if (!empty($author_names[0]))
				{
					// Look up the user by username
					$author_data = $this->manager->get_user_by_username($author_names[0]);
					if ($author_data)
					{
						$user_id = (int) $author_data['user_id'];
					}
				}
			}

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
				'revision_status'    		=> $this->manager::REVISION_STATUS_MAP[$this->manager::INTERNAL_STATUS_UNVALIDATED], // This is one of two places the revision status is changed
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

			'SUPPORTED_PHPBB_VERSIONS'	=> $this->manager->get_settings()['supported.phpbb.versions'],
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

		// Approved, unvalidated, denied - note that this will filter by the contribution status, not the revision status
		// So we could have new, unvalidated revisions for a previously validated contribution come up a different colour when filtering by denied or approved, for example.
		$status = $this->request->variable('status', $this->manager::STATUS_APPROVED);

		$sort = $this->request->variable('sort', 0); // By date, by name, etc
		$search_query = $this->request->variable('q', '', true);

		// Pagination
		$settings = $this->manager->get_settings();
		$per_page = (int) $settings['per.page'];
		$start = $this->request->variable('start', 0);

        $result = $this->manager->get_contributions_for_index($type, $status, $sort, $search_query, $start, $per_page);

        $total = $result['total'];
        $contributions = $result['contributions'];
 
        foreach ($contributions as $contribution)
        {
            $this->template->assign_block_vars('contributions', [
				'U_VIEW_CONTRIBUTION' 		=> $this->helper->route('custdb_view_contribution', ['contribution_id' => $contribution['contribution_id']]),
                
				'CONTRIBUTION_NAME' 		=> $contribution['contribution_name'],
                'CONTRIBUTION_DESCRIPTION' 	=> $contribution['contribution_description'],
				'CONTRIBUTION_TYPE'			=> $this->manager->contribution_type_mapping()[$contribution['contribution_type']],

				// Get the *latest* revision status so we can colour code for attracting attention in a simple way
				'CONTRIBUTION_UNVALIDATED' 	=> $this->manager->is_team_member() && $contribution['revision_status'] === $this->manager::STATUS_UNVALIDATED,
				'CONTRIBUTION_DENIED' 		=> $this->manager->is_team_member() && $contribution['revision_status'] === $this->manager::STATUS_DENIED,
            ]);
        }
   
		// Generate pagination
		$this->pagination->generate_template_pagination(
			$this->helper->route('custdb_index', ['type' => $type, 'status' => $status, 'sort' => $sort, 'q' => $search_query]), 
			'pagination', 
			'start', 
			$total, 
			$per_page, 
			$start
		);

        $this->template->assign_vars([
			'U_CUSTDB_INDEX'		=> $this->helper->route('custdb_index'),
			'U_NEW_CONTRIBUTION' 	=> $this->helper->route('custdb_add_contribution'),

			// Filter options
			'STATUS_APPROVED'		=> $this->manager::STATUS_APPROVED,
			'STATUS_DENIED'			=> $this->manager::STATUS_DENIED,
			'STATUS_UNVALIDATED'	=> $this->manager::STATUS_UNVALIDATED,
			'CURRENT_STATUS'		=> $status,

			// Sort options
			'SORT_NAME'			=> $this->manager::SORT_NAME,
			'SORT_DATE'			=> $this->manager::SORT_DATE,
			'CURRENT_SORT'		=> $sort,

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