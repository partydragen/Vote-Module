<?php
/*
 *	Made by Partydragen
 *  https://github.com/NamelessMC/Nameless/
 *
 *  License: MIT
 */

// Always define page name
define('PAGE', 'vote');
?>
<!DOCTYPE html>
<html lang="<?php echo (defined('HTML_LANG') ? HTML_LANG : 'en'); ?>">
  <head>
    <!-- Standard Meta -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- Site Properties -->
	<?php 
	$title = $vote_language->get('vote', 'vote');
	require('core/templates/header.php'); 
	?>
  
  </head>
  <body>
<div class="container" style="padding-top: 5rem;">
	<div class="card">
	<div class="card-block">
	<br />
	<?php 
	$vote_message = $queries->getWhere("vote_settings", array("name", "=", "vote_message"));
	$vote_message = $vote_message[0]->value;
	
	if(!empty($vote_message)){
	?>
	<div class="alert alert-info"><center><?php echo htmlspecialchars($vote_message); ?></center></div>
	<?php 
	}
	
	$sites = $queries->getWhere("vote_sites", array("id", "<>", 0));
	
	// How many rows/columns?
	$total = count($sites);
	
	for($i = 0; $i < $total; $i++){
		if($i % 4 == 0){
			// Determine number of columns in the row
			$remaining = count($sites);
			
			if($remaining >= 4)
				$col_type = 3;
			else {
				$end = $i + $remaining;
				
				switch($remaining){
					case 1:
						$col_type = 12;
					break;
					case 2:
						$col_type = 6;
					break;
					case 3:
						$col_type = 4;
					break;
				}
			}
			
			echo '<div class="row">';
		}
		
		echo '<div class="col-md-' . $col_type . '">';
		echo '<center><a class="btn btn-lg btn-block btn-primary" href="' . str_replace("&amp;", "&", htmlspecialchars($sites[$i]->site)) . '" target="_blank" role="button">' . htmlspecialchars($sites[$i]->name) . '</a></center>';
		echo '</div>';
		unset($sites[$i]);
		
		if(($i + 1) % 4 == 0)
			echo '</div><br />';
	}
	?>
    </div>
	</br>
</div>
</div>

    <?php 
	require('core/templates/navbar.php'); 
	require('core/templates/footer.php'); 
	
	$smarty->display('custom/templates/' . TEMPLATE . '/vote.tpl');

    require('core/templates/scripts.php'); ?>
  </body>
</html>