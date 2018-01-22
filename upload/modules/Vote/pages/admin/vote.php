<?php 
/*
 *	Made by Partydragen and Samerton
 *  https://github.com/partydragen/Vote-Module
 *  NamelessMC version 2.0.0-pr4
 *
 *  License: MIT
 *
 *  Vote module - Admin vote page
 */

// Can the user view the AdminCP?
if($user->isLoggedIn()){
	if(!$user->canViewACP()){
		// No
		Redirect::to(URL::build('/'));
		die();
	} else {
		// Check the user has re-authenticated
		if(!$user->isAdmLoggedIn()){
			// They haven't, do so now
			Redirect::to(URL::build('/admin/auth'));
			die();
		}
	}
} else {
	// Not logged in
	Redirect::to(URL::build('/login'));
	die();
}
$page = 'admin';
$admin_page = 'vote';
?>
<!DOCTYPE html>
<html lang="<?php echo (defined('HTML_LANG') ? HTML_LANG : 'en'); ?>">
  <head>
    <!-- Standard Meta -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	
	<?php 
	$title = $language->get('admin', 'admin_cp');
	require('core/templates/admin_header.php'); 
	?>
  
	<!-- Custom style -->
	<style>
	textarea {
		resize: none;
	}
	</style>
	
	<link rel="stylesheet" href="<?php if(defined('CONFIG_PATH')) echo CONFIG_PATH . '/'; else echo '/'; ?>core/assets/plugins/switchery/switchery.min.css">
  
  </head>

  <body>
    <?php require('modules/Core/pages/admin/navbar.php'); ?>
    <div class="container">
	  <div class="row">
		<div class="col-md-3">
		  <?php require('modules/Core/pages/admin/sidebar.php'); ?>
		</div>
		<div class="col-md-9">
		  <div class="card">
		    <div class="card-block">
			<?php
			if(!isset($_GET['view'])){
				if(!isset($_GET['action']) && !isset($_GET['vote'])){
			?>
			<h3 style="display:inline;"><?php echo $vote_language->get('vote', 'vote_sites'); ?></h3>
			<span class="pull-right">
			  <a href="<?php echo URL::build('/admin/vote/', 'action=new'); ?>" class="btn btn-primary"><?php echo $vote_language->get('vote', 'new_site'); ?></a>
			</span>
			<br /><br />
			<?php
				// Get vote sites from database
				$vote_sites = $queries->getWhere('vote_sites', array('id', '<>', 0));
				if(!count($vote_sites)){
					// No sites defined
			?>
			<strong><?php echo $vote_language->get('vote', 'no_vote_sites'); ?></strong>
			<?php
				} else {
					$n = 0;
			?>
			<div class="panel panel-info">
				<div class="panel-heading">
					<?php echo $vote_language->get('vote', 'vote_sites'); ?>
				</div>
				<div class="panel-body">
					<?php 
					// Loop through each vote site
					foreach($vote_sites as $site){
					?>
					<div class="row">
						<div class="col-md-10">
							<?php echo '<a href="' . URL::build('/admin/vote/', 'action=edit&amp;vid=' . $site->id) . '">' . htmlspecialchars($site->name) . '</a>'; ?>
						</div>
						<div class="col-md-2">
							<span class="pull-right">
								<a href="<?php echo URL::build('/admin/vote/', 'action=delete&amp;vid=' . $site->id); ?>" class="btn btn-warning btn-sm" onclick="return confirm('<?php echo $vote_language->get('vote', 'delete_site'); ?>');"><span class="fa fa-trash"></span></a>
							</span>
						</div>
					</div>
					<?php 
						if(($n + 1) != count($vote_sites)) echo '<hr />';
						
						$n++;
					}
					?>

				</div>
			</div>
			<?php
				}
			// Deal with input
			if(Input::exists()){
				if(Token::check(Input::get('token'))){
					$validate = new Validate();
					$validation = $validate->check($_POST, array(
						'message' => array(
							'max' => 2048
						),
						'link_location' => array(
							'required' => true
						),
						'icon' => array(
							'max' => 64
						)
					));
					
					if($validation->passed()){			
						try {
							// Get link location
							if(isset($_POST['link_location'])){
								switch($_POST['link_location']){
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
							$cache->setCache('vote_module_cache');
							$cache->store('link_location', $location);
							
							// Update Icon cache
							$cache->setCache('vote_module_cache');
							$cache->store('icon', Output::getClean(Input::get('icon')));
							
                            // Update Vote Message
                            $message_id = $queries->getWhere('vote_settings', array('name', '=', 'vote_message'));
                            $message_id = $message_id[0]->id;
							$queries->update('vote_settings', $message_id, array(
								'value' => Input::get('message'),
							));

							echo '<script>window.location.replace("' . URL::build('/admin/vote/') . '");</script>';
							die();
						} catch(Exception $e){
							die($e->getMessage());
						}
					} else {
						$error = $vote_language->get('vote', 'message_maximum');
					}
				} else {
					$error = $language->get('general', 'invalid_token');
				}
			}
			
			// Retrive Link Location and Icon from cache
			$cache->setCache('vote_module_cache');
			$link_location = $cache->retrieve('link_location');
			$icon = $cache->retrieve('icon');

			// Get vote 
			$vote_message = $queries->getWhere('vote_settings', array('name', '=', "vote_message"));
			$vote_message = htmlspecialchars($vote_message[0]->value);
			?>
			<hr />
			<?php if(isset($error)) echo '<div class="alert alert-danger">' . $error . '</div>'; ?>
			<form action="" method="post">
              <div class="form-group">
                <label for="link_location"><?php echo $vote_language->get('vote', 'link_location'); ?></label>
                <select class="form-control" id="link_location" name="link_location">
                  <option value="1"<?php if($link_location == 1) echo ' selected'; ?>><?php echo $language->get('admin', 'page_link_navbar'); ?></option>
                  <option value="2"<?php if($link_location == 2) echo ' selected'; ?>><?php echo $language->get('admin', 'page_link_more'); ?></option>
                  <option value="3"<?php if($link_location == 3) echo ' selected'; ?>><?php echo $language->get('admin', 'page_link_footer'); ?></option>
                  <option value="4"<?php if($link_location == 4) echo ' selected'; ?>><?php echo $language->get('admin', 'page_link_none'); ?></option>
                </select>
              </div>
              <div class="form-group">
                <label for="inputIcon"><?php echo $vote_language->get('vote', 'icon'); ?></label>
                <input type="text" class="form-control" name="icon" id="inputIcon" placeholder="<?php echo htmlspecialchars($vote_language->get('vote', 'icon_example')); ?>" value="<?php echo Output::getClean(htmlspecialchars_decode($icon)); ?>">
              </div>
			  <div class="form-group">
				<label for="InputMessage"><?php echo $vote_language->get('vote', 'message'); ?></label><br />
				<textarea name="message" rows="3" id="InputMessage" class="form-control"><?php echo htmlspecialchars_decode($vote_message); ?></textarea>
			  </div>  
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="submit" value="<?php echo $language->get('general', 'submit'); ?>" class="btn btn-primary">
			</form>
				<?php 
				} else if(isset($_GET['action'])){
					if($_GET['action'] == 'new'){
						if(Input::exists()){
							if(Token::check(Input::get('token'))){
								// process addition of site
								$validate = new Validate();
								$validation = $validate->check($_POST, array(
									'vote_site_name' => array(
										'required' => true,
										'min' => 2,
										'max' => 64
									),
									'vote_site_url' => array(
										'required' => true,
										'min' => 2,
										'max' => 255
									)
								));
								
								if($validation->passed()){
									// input into database
									try {
										$queries->create('vote_sites', array(
											'site' => htmlspecialchars(Input::get('vote_site_url')),
											'name' => htmlspecialchars(Input::get('vote_site_name'))
										));
										echo '<script>window.location.replace("' . URL::build('/admin/vote') . '");</script>';
										die();
									} catch(Exception $e){
										die($e->getMessage());
									}
								} else {
									// validation failed
									echo '<div class="alert alert-danger">';
									foreach($validation->errors() as $error){
										echo str_replace("_", " ", ucfirst($error)), '<br />';
									}
									echo '</div>';
								}
							} else {
								echo '<div class="alert alert-warning">' . $admin_language['invalid_token'] . '</div>';
							}
						}
						?>
				<h3><?php echo $vote_language->get('vote', 'new_site'); ?></h3>
				<form action="" method="post">
				  <div class="form-group">
					<label for="InputVoteName"><?php echo $vote_language->get('vote', 'vote_name'); ?></label>
					<input type="text" id="InputVoteName" placeholder="Vote site name" name="vote_site_name" class="form-control">
				  </div>
				  <div class="form-group">
					<label for="InputVoteURL"><?php echo $vote_language->get('vote', 'vote_url'); ?></label>
					<input type="text" id="InputVoteURL" placeholder="Vote site URL" name="vote_site_url" class="form-control">
				  </div>
				  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
				  <input type="submit" value="<?php echo $language->get('general', 'submit'); ?>" class="btn btn-primary">
				  <a href="<?php echo URL::build('/admin/vote'); ?>" class="btn btn-warning"><?php echo $language->get('general', 'back'); ?></a>
				</form>
					<?php
					} else if($_GET['action'] == 'edit'){
						// Edit page
						if(!is_numeric($_GET["vid"])){
							echo '<script>window.location.replace("' . URL::build('/admin/vote') . '");</script>';
							die();
						} else {
							$site = $queries->getWhere("vote_sites", array("id", "=", $_GET["vid"]));
							if(!count($site)){
								echo '<script>window.location.replace("' . URL::build('/admin/vote') . '");</script>';
								die();
							}
							$site = $site[0];
						}
						if(Input::exists()){
							if(Token::check(Input::get('token'))){
								$validate = new Validate();
								$validation = $validate->check($_POST, array(
									'vote_name' => array(
										'required' => true,
										'min' => 2,
										'max' => 64
									),
									'vote_url' => array(
										'required' => true,
										'min' => 2,
										'max' => 255
									)
								));
								
								if($validation->passed()){
									// input into database
									try {
										$queries->update('vote_sites', $site->id, array(
											'site' => htmlspecialchars(Input::get('vote_url')),
											'name' => htmlspecialchars(Input::get('vote_name'))
										));
										echo '<script>window.location.replace("' . URL::build('/admin/vote') . '");</script>';
										die();
									} catch(Exception $e){
										die($e->getMessage());
									}
								} else {
									// validation failed
									echo '<div class="alert alert-danger">';
									foreach($validation->errors() as $error){
										echo str_replace("_", " ", ucfirst($error)), '<br />';
									}
									echo '</div>';
								}
							} else {
								echo '<div class="alert alert-warning">' . $admin_language['invalid_token'] . '</div>';
							}
						}
						?>
				<h3><?php echo $vote_language->get('vote', 'edit_site'); ?></h3>
				<form role="form" action="" method="post">
				  <div class="form-group">
					<label for="InputName"><?php echo $vote_language->get('vote', 'vote_name'); ?></label>
					<input type="text" name="vote_name" class="form-control" id="InputName" placeholder="Vote site name" value="<?php echo htmlspecialchars($site->name); ?>">
				  </div>
				  <div class="form-group">
					<label for="InputURL"><?php echo $vote_language->get('vote', 'vote_url'); ?></em></label>
					<input type="text" name="vote_url" id="InputURL" placeholder="Vote site URL" class="form-control" value="<?php echo htmlspecialchars($site->site); ?>">
				  </div>
				  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
				  <input type="submit" value="<?php echo $language->get('general', 'submit'); ?>" class="btn btn-primary">
				  <a href="<?php echo URL::build('/admin/vote'); ?>" class="btn btn-warning"><?php echo $language->get('general', 'back'); ?></a>
				</form>
					<?php
					} else if($_GET['action'] == 'delete'){
						// Delete a site
						if(!isset($_GET["vid"]) || !is_numeric($_GET["vid"])){
							echo '<script>window.location.replace("' . URL::build('/admin/vote') . '");</script>';
							die();
						}
						try {
							$queries->delete('vote_sites', array('id', '=' , $_GET["vid"]));
							echo '<script>window.location.replace("' . URL::build('/admin/vote') . '");</script>';
							die();
						} catch(Exception $e) {
							die($e->getMessage());
						}
					}
				}
			}
			?>
			</div>
		  </div>
		</div>
      </div>
    </div>
	<?php require('modules/Core/pages/admin/footer.php'); ?>

    <?php require('modules/Core/pages/admin/scripts.php'); ?>
	
	<script src="<?php if(defined('CONFIG_PATH')) echo CONFIG_PATH . '/'; else echo '/'; ?>core/assets/plugins/switchery/switchery.min.js"></script>
  </body>
</html>
