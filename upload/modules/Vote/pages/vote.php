<?php
/*
 *	Made by Partydragen and Samerton
 *  https://github.com/partydragen/Vote-Module
 *  NamelessMC version 2.0.0-pr4
 *
 *  License: MIT
 */

// Always define page name
define('PAGE', 'vote');
?>
<!DOCTYPE html>
<html<?php if(defined('HTML_CLASS')) echo ' class="' . HTML_CLASS . '"'; ?> lang="<?php echo (defined('HTML_LANG') ? HTML_LANG : 'en'); ?>" <?php if(defined('HTML_RTL') && HTML_RTL === true) echo ' dir="rtl"'; ?>>
  <head>
    <!-- Standard Meta -->
    <meta charset="<?php echo (defined('LANG_CHARSET') ? LANG_CHARSET : 'utf-8'); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- Site Properties -->
	<?php 
	$title = $vote_language->get('vote', 'vote');
	require(ROOT_PATH . '/core/templates/header.php'); 
	?>
  
  </head>
  <body>
    <?php 
	require(ROOT_PATH . '/core/templates/navbar.php'); 
	require(ROOT_PATH . '/core/templates/footer.php');
	
	// Get message
	$vote_message = $queries->getWhere("vote_settings", array("name", "=", "vote_message"));
	$vote_message = $vote_message[0]->value;
	
	// Is vote message empty?
	if(!empty($vote_message)){
		$message_enabled = true;
	}
	
	// Get sites from database
	$sites = $queries->getWhere("vote_sites", array("id", "<>", 0));
	
	$sites_array = array();
	foreach($sites as $site){
        $sites_array[] = array(
            'name' => Output::getClean($site->name),
            'url' => Output::getClean($site->site),
        );
	}
	
	// Assign Smarty variables
	$smarty->assign(array(
		'MESSAGE_ENABLED' => $message_enabled,
		'MESSAGE' => htmlspecialchars($vote_message),
		'SITES' => $sites_array,
	));
	
	// Display vote template
	$smarty->display(ROOT_PATH . '/custom/templates/' . TEMPLATE . '/vote.tpl');

    require(ROOT_PATH . '/core/templates/scripts.php'); ?>
  </body>
</html>
