<?php 
/*
 *	Made by Partydragen
 *  https://github.com/partydragen/Vote-Module
 *  https://partydragen.com
 *  NamelessMC version 2.0.0-pr13
 *
 *  License: MIT
 *
 *  Vote module - Panel vote page
 */

// Can the user view the panel?
if (!$user->handlePanelPageLoad('admincp.vote')) {
    require_once(ROOT_PATH . '/403.php');
    die();
}

define('PAGE', 'panel');
define('PARENT_PAGE', 'vote');
define('PANEL_PAGE', 'vote');
$page_title = $vote_language->get('vote', 'vote');
require_once(ROOT_PATH . '/core/templates/backend_init.php');

if (!isset($_GET['action'])) {
	// Deal with input
	if (Input::exists()) {
		$errors = [];

		if (Token::check(Input::get('token'))) {
			$validation = Validate::check($_POST, [
				'message' => [
					Validate::MAX => 2048
				],
				'link_location' => [
					Validate::REQUIRED => true,
				],
				'icon' => [
					Validate::MAX => 64
				]
            ])->messages([
                'message' => [
					Validate::MAX => $vote_language->get('vote', 'message_maximum')
				]
			]);

			if ($validation->passed()) {
				try {
					// Get link location
					if (isset($_POST['link_location'])) {
						switch ($_POST['link_location']) {
							case 1:
							case 2:
							case 3:
							case 4:
								$location = $_POST['link_location'];
								break;
						default:
						$location = 1;
						}
					} else
                        $location = 1;

					// Update Link location cache
					$cache->setCache('nav_location');
					$cache->store('vote_location', $location);

					// Update Icon cache
					$cache->setCache('navbar_icons');
					$cache->store('vote_icon', Input::get('icon'));

					// Update Vote Message
					$message_id = $queries->getWhere('vote_settings', ['name', '=', 'vote_message']);
					$message_id = $message_id[0]->id;
					$queries->update('vote_settings', $message_id, [
						'value' => Input::get('message'),
					]);

                    Session::flash('staff_vote', $language->get('admin', 'settings_updated_successfully'));
                    Redirect::to(URL::build('/panel/vote'));
				} catch (Exception $e) {
					$errors[] = $e->getMessage();
				}
			} else {
				// Validation Errors
                $errors = $validation->errors();
			}
		} else {
			$errors[] = $language->get('general', 'invalid_token');
		}
	}

	// Get vote sites from database
	$vote_sites = $queries->getWhere('vote_sites', ['id', '<>', 0]);
	$sites_array = [];
	if (count($vote_sites)) {
		foreach ($vote_sites as $site) {
			$sites_array[] = [
				'edit_link' => URL::build('/panel/vote/', 'action=edit&id=' . Output::getClean($site->id)),
				'title' => Output::getClean($site->name),
				'delete_link' => URL::build('/panel/vote/', 'action=delete&id=' . Output::getClean($site->id))
			];
		}
	}

	// Retrieve Link Location from cache
	$cache->setCache('nav_location');
	$link_location = $cache->retrieve('vote_location');

	// Retrieve Icon from cache
	$cache->setCache('navbar_icons');
	$icon = $cache->retrieve('vote_icon');

	// Get vote 
	$vote_message = $queries->getWhere('vote_settings', ['name', '=', "vote_message"]);
	$vote_message = htmlspecialchars($vote_message[0]->value);

	$smarty->assign([
		'NEW_SITE' => $vote_language->get('vote', 'new_site'),
		'NEW_SITE_LINK' => URL::build('/panel/vote/', 'action=new'),
		'LINK_LOCATION' => $vote_language->get('vote', 'link_location'),
		'LINK_LOCATION_VALUE' => $link_location,
		'LINK_NAVBAR' => $language->get('admin', 'page_link_navbar'),
		'LINK_MORE' => $language->get('admin', 'page_link_more'),
		'LINK_FOOTER' => $language->get('admin', 'page_link_footer'),
		'LINK_NONE' => $language->get('admin', 'page_link_none'),
		'ICON' => $vote_language->get('vote', 'icon'),
		'ICON_EXAMPLE' => htmlspecialchars($vote_language->get('vote', 'icon_example')),
		'ICON_VALUE' => Output::getClean(htmlspecialchars_decode($icon)),
		'SITE_LIST' => $sites_array,
		'NO_VOTE_SITES' => $vote_language->get('vote', 'no_vote_sites'),
		'MESSAGE' => $vote_language->get('vote', 'message'),
		'MESSAGE_VALUE' => $vote_message,
		'ARE_YOU_SURE' => $language->get('general', 'are_you_sure'),
		'CONFIRM_DELETE_SITE' => $vote_language->get('vote', 'delete_site'),
		'YES' => $language->get('general', 'yes'),
		'NO' => $language->get('general', 'no')
	]);

	$template_file = 'vote/vote.tpl';
} else {
	switch ($_GET['action']) {
		case 'new':
			if (Input::exists()) {
				$errors = [];

				if (Token::check(Input::get('token'))) {
					// process addition of site
					$validation = Validate::check($_POST, [
						'vote_site_name' => [
							Validate::REQUIRED => true,
							Validate::MIN => 2,
							Validate::MAX => 64
						],
						'vote_site_url' => [
							Validate::REQUIRED => true,
							Validate::MIN => 10,
							Validate::MAX => 255
						]
					])->messages([
						'vote_site_name' => [
							Validate::REQUIRED => $vote_language->get('vote', 'site_name_required'),
							Validate::MIN => $vote_language->get('vote', 'site_name_minimum'),
							Validate::MAX => $vote_language->get('vote', 'site_name_maximum')
						],
						'vote_site_url' => [
							Validate::REQUIRED => $vote_language->get('vote', 'site_url_required'),
							Validate::MIN => $vote_language->get('vote', 'site_url_minimum'),
							Validate::MAX => $vote_language->get('vote', 'site_url_maximum')
						]
                    ]);

					if ($validation->passed()) {
						// input into database
						try {
							$queries->create('vote_sites', [
								'site' => htmlspecialchars(Input::get('vote_site_url')),
								'name' => htmlspecialchars(Input::get('vote_site_name'))
							]);

							Session::flash('staff_vote', $vote_language->get('vote', 'site_created_successfully'));
							Redirect::to(URL::build('/panel/vote'));
						} catch (Exception $e) {
							$errors[] = $e->getMessage();
						}
					} else {
                        // Validation Errors
                        $errors = $validation->errors();
					}
				} else {
					$errors[] = $language->get('general', 'invalid_token');
				}
			}

			$smarty->assign([
				'NEW_SITE' => $vote_language->get('vote', 'new_site'),
				'BACK' => $language->get('general', 'back'),
				'BACK_LINK' => URL::build('/panel/vote'),
				'VOTE_SITE_NAME' => $vote_language->get('vote', 'vote_name'),
				'VOTE_SITE_URL' => $vote_language->get('vote', 'vote_url'),
			]);

			$template_file = 'vote/vote_new.tpl';
		break;
		case 'edit':
			// Get page
			if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
				Redirect::to(URL::build('/panel/vote'));
			}

			$site = $queries->getWhere('vote_sites', ['id', '=', $_GET['id']]);
			if (!count($site)) {
				Redirect::to(URL::build('/panel/vote'));
				die();
			}
			$site = $site[0];

			if (Input::exists()) {
				$errors = [];

				if (Token::check(Input::get('token'))) {
					// process addition of site
					$validation = Validate::check($_POST, [
						'vote_site_name' => [
							Validate::REQUIRED => true,
							Validate::MIN => 2,
							Validate::MAX => 64
						],
						'vote_site_url' => [
							Validate::REQUIRED => true,
							Validate::MIN => 10,
							Validate::MAX => 255
						]
					])->messages([
						'vote_site_name' => [
							Validate::REQUIRED => $vote_language->get('vote', 'site_name_required'),
							Validate::MIN => $vote_language->get('vote', 'site_name_minimum'),
							Validate::MAX => $vote_language->get('vote', 'site_name_maximum')
						],
						'vote_site_url' => [
							Validate::REQUIRED => $vote_language->get('vote', 'site_url_required'),
							Validate::MIN => $vote_language->get('vote', 'site_url_minimum'),
							Validate::MAX => $vote_language->get('vote', 'site_url_maximum')
						]
                    ]);

					if ($validation->passed()) {
						// input into database
						try {
							$queries->update('vote_sites', $site->id, [
								'site' => htmlspecialchars(Input::get('vote_site_url')),
								'name' => htmlspecialchars(Input::get('vote_site_name'))
							]);

							Session::flash('staff_vote', $vote_language->get('vote', 'site_updated_successfully'));
							Redirect::to(URL::build('/panel/vote'));
						} catch (Exception $e) {
							$errors[] = $e->getMessage();
						}
					} else {
                        // Validation Errors
                        $errors = $validation->errors();
					}
				} else {
					$errors[] = $language->get('general', 'invalid_token');
				}
			}

			$smarty->assign([
				'EDIT_SITE' => $vote_language->get('vote', 'edit_site'),
				'BACK' => $language->get('general', 'back'),
				'BACK_LINK' => URL::build('/panel/vote'),
				'VOTE_SITE_NAME' => $vote_language->get('vote', 'vote_name'),
				'VOTE_SITE_NAME_VALUE' => Output::getClean($site->name),
				'VOTE_SITE_URL' => $vote_language->get('vote', 'vote_url'),
				'VOTE_SITE_URL_VALUE' => Output::getClean($site->site),
			]);

			$template_file = 'vote/vote_edit.tpl';
		break;
		case 'delete':
			if (isset($_GET['id']) && is_numeric($_GET['id'])) {
				try {
					$queries->delete('vote_sites', ['id', '=', $_GET['id']]);
				} catch (Exception $e) {
					die($e->getMessage());
				}

				Session::flash('staff_vote', $vote_language->get('vote', 'site_deleted_successfully'));
				Redirect::to(URL::build('/panel/vote'));
			}
		break;
		default:
			Redirect::to(URL::build('/panel/vote'));
		break;
	}
}

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

if (Session::exists('staff_vote'))
	$success = Session::flash('staff_vote');

if (isset($success))
	$smarty->assign([
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	]);

if (isset($errors) && count($errors))
	$smarty->assign([
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	]);

$smarty->assign([
	'PARENT_PAGE' => PARENT_PAGE,
	'PAGE' => PANEL_PAGE,
	'DASHBOARD' => $language->get('admin', 'dashboard'),
	'VOTE' => $vote_language->get('vote', 'vote'),
	'TOKEN' => Token::get(),
	'SUBMIT' => $language->get('general', 'submit')
]);

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/panel_navbar.php');

// Display template
$template->displayTemplate($template_file, $smarty);