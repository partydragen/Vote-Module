<?php 
/*
 *	Made by Partydragen and Samerton
 *  https://github.com/partydragen/Vote-Module
 *  NamelessMC version 2.0.0-pr4
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
				'value' => 'You can manage this vote module in AdminCP -> Vote'
			));
			$queries->create('vote_sites', array(
				'site' => 'https://mcplanet.org/',
				'name' => 'MCPlanet (Example)'
			));
			$queries->create('vote_sites', array(
				'site' => 'http://planetminecraft.com/',
				'name' => 'PlanetMinecraft (Example)'
			));
		} catch(Exception $e){
			// Error
		}
	}
	
	// Update main admin group permissions
	$group = $queries->getWhere('groups', array('id', '=', 2));
	$group = $group[0];
	
	$group_permissions = json_decode($group->permissions, TRUE);
	$group_permissions['admincp.vote'] = 1;
	
	$group_permissions = json_encode($group_permissions);
	$queries->update('groups', 2, array('permissions' => $group_permissions));
	
	// Add to cache
	$cache->store('module_vote', 'true');
}

// Initialise vote language
$vote_language = new Language(ROOT_PATH . '/modules/Vote/language', LANGUAGE);

// AdminCP
PermissionHandler::registerPermissions('Vote', array(
    'admincp.vote' => $language->get('admin', 'admin_cp') . ' &raquo; ' . $vote_language->get('vote', 'vote')
));

// Define URLs which belong to this module
$pages->add('Vote', '/vote', 'pages/vote.php', 'vote', true);
$pages->add('Vote', '/admin/vote', 'pages/admin/vote.php');

// Add link to admin sidebar
if($user->hasPermission('admincp.vote')){
	if(!isset($admin_sidebar)) $admin_sidebar = array();
	$admin_sidebar['vote'] = array(
		'title' => $vote_language->get('vote', 'vote'),
		'url' => URL::build('/admin/vote')
	);
}

// Navigation link location
$cache->setCache('vote_module_cache');
if(!$cache->isCached('link_location')){
	$link_location = 1;
	$cache->store('link_location', '1');
} else {
	$link_location = $cache->retrieve('link_location');
}

// Navigation icon
$cache->setCache('navbar_icons');
if(!$cache->isCached('vote_icon')) {
	$icon = '';
} else {
	$icon = $cache->retrieve('vote_icon');
}


switch($link_location){
	case 1:
		// Navbar
		// Check cache for navbar link order
		$cache->setCache('navbar_order');
		if(!$cache->isCached('vote_order')){
			$vote_order = 4;
			$cache->store('vote_order', 4);
		} else {
			$vote_order = $cache->retrieve('vote_order');
		}
		$navigation->add('vote', $vote_language->get('vote', 'vote'), URL::build('/vote'), 'top', null, $vote_order, $icon);
	break;
	case 2:
		// "More" dropdown
		$navigation->addItemToDropdown('more_dropdown', 'vote', $icon . ' ' . $vote_language->get('vote', 'vote'), URL::build('/vote'), 'top', null);
	break;
	case 3:
		// Footer
		$navigation->add('vote', $icon . $vote_language->get('vote', 'vote'), URL::build('/vote'), 'footer', null);
	break;
}