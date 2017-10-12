<?php 
/*
 *	Made by Partydragen and Samerton
 *  https://github.com/partydragen/Vote-Module
 *  NamelessMC version 2.0.0-pr3
 *
 *  License: MIT
 *
 *  Vote module initialisation file
 */

// Ensure module has been installed
$cache->setCache('module_cache');
$module_installed = $cache->retrieve('module_vote');
if(!$module_installed){
	// Hasn't been installed
	// Need to run the installer
	
	// Database stuff
	$exists = $queries->tableExists('vote_settings');
	if(empty($exists)){
		// Create tables
		try {
			$data = $queries->createTable("vote_settings", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(20) NOT NULL, `value` varchar(2048) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			$data = $queries->createTable("vote_sites", " `id` int(11) NOT NULL AUTO_INCREMENT, `site` varchar(512) NOT NULL, `name` varchar(64) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
			
		// Insert data
		$queries->create('vote_settings', array(
			'name' => 'vote_message',
			'value' => ''
		));
		} catch(Exception $e){
			// Error
		}
	}
	
	// Add to cache
	$cache->store('module_vote', 'true');
	
}

// Initialise vote language
$vote_language = new Language(ROOT_PATH . '/modules/Vote/language', LANGUAGE);

// Define URLs which belong to this module
$pages->add('Vote', '/vote', 'pages/vote.php');
$pages->add('Vote', '/admin/vote', 'pages/admin/vote.php');

// Check cache for navbar link order
$cache->setCache('navbar_order');
if(!$cache->isCached('vote_order')){
    $vote_order = 4;
    $cache->store('vote_order', 4);
} else {
    $vote_order = $cache->retrieve('vote_order');
}
$navigation->add('vote', $vote_language->get('vote', 'vote'), URL::build('/vote'), 'top', null, $vote_order);

// Add link to admin sidebar
if(!isset($admin_sidebar)) $admin_sidebar = array();
$admin_sidebar['vote'] = array(
	'title' => $vote_language->get('vote', 'vote'),
	'url' => URL::build('/admin/vote')
);