<?php
/*
 *	Made by Partydragen
 *  https://github.com/partydragen/Vote-Module
 *  https://partydragen.com
 *  NamelessMC version 2.0.0-pr13
 *
 *  License: MIT
 */

// Always define page name
define('PAGE', 'vote');
$page_title = $vote_language->get('vote', 'vote');
require_once(ROOT_PATH . '/core/templates/frontend_init.php');

// Get message
$vote_message = $queries->getWhere("vote_settings", ["name", "=", "vote_message"]);
$vote_message = $vote_message[0]->value;

// Is vote message empty?
if (!empty($vote_message)) {
	$message_enabled = true;
}

// Get sites from database
$sites = $queries->getWhere("vote_sites", ["id", "<>", 0]);

$sites_array = [];
foreach ($sites as $site) {
    $sites_array[] = [
        'name' => Output::getClean($site->name),
        'url' => Output::getClean($site->site),
    ];
}

// Assign Smarty variables
$smarty->assign([
	'VOTE_TITLE' => $vote_language->get('vote', 'vote'),
	'MESSAGE_ENABLED' => $message_enabled,
	'MESSAGE' => htmlspecialchars($vote_message),
	'SITES' => $sites_array,
]);

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

$template->onPageLoad();

$smarty->assign('WIDGETS_LEFT', $widgets->getWidgets('left'));
$smarty->assign('WIDGETS_RIGHT', $widgets->getWidgets('right'));

require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');

// Display template
$template->displayTemplate('vote.tpl', $smarty);