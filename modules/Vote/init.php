<?php 
/*
 *	Made by Partydragen
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-dev
 *
 *  License: MIT
 *
 *  Core initialisation file
 */

// Ensure module has been installed
$module_installed = $cache->retrieve('module_vote');

// Initialise forum language
$vote_language = new Language(ROOT_PATH . '/modules/Vote/language', LANGUAGE);

// Define URLs which belong to this module
$pages->add('Vote', '/vote', 'pages/vote.php');
$pages->add('Vote', '/admin/vote', 'pages/admin/vote.php');

// Add link to navbar
$navigation->add('vote', '<i class="fa fa-thumbs-up" aria-hidden="true"></i> ' . $vote_language->get('vote', 'vote'), URL::build('/vote'));

// Add link to admin sidebar
if(!isset($admin_sidebar)) $admin_sidebar = array();
$admin_sidebar['vote'] = array(
	'title' => $vote_language->get('vote', 'vote'),
	'url' => URL::build('/admin/vote')
);