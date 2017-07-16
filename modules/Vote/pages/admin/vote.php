<?php 
/*
 *	Made by Partydragen
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr2
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
    <div class="container">	
	  <?php require('modules/Core/pages/admin/navbar.php'); ?>
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
			<h3 style="display:inline;">Vote Sites</h3>
			<span class="pull-right">
			  <a href="/admin/vote/?action=new" class="btn btn-primary">New Vote Site</a>
			</span>
			<br /><br />
			<?php
				// Get vote sites from database
				$vote_sites = $queries->getWhere('vote_sites', array('id', '<>', 0));
				if(!count($vote_sites)){
					// No sites defined
			?>
			<strong>No vote sites defined</strong>
			<?php
				} else {
					$n = 0;
			?>
			<div class="panel panel-info">
				<div class="panel-heading">
					Vote Sites
				</div>
				<div class="panel-body">
					<?php 
					// Loop through each vote site
					foreach($vote_sites as $site){
					?>
					<div class="row">
						<div class="col-md-10">
							<?php echo '<a href="/admin/vote/?action=edit&vid=' . $site->id . '">' . htmlspecialchars($site->name) . '</a>'; ?>
						</div>
						<div class="col-md-2">
							<span class="pull-right">
								<a href="/admin/vote/?action=delete&amp;vid=<?php echo $site->id;?>" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to delete this site?');"><span class="fa fa-trash"></span></a>
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
						)
					));
					
					if($validation->passed()){			
						try {
							$queries->update('vote_settings', 1, array(
								'value' => Input::get('message')
							));
							echo '<script>window.location.replace("/admin/vote/");</script>';
							die();
						} catch(Exception $e){
							die($e->getMessage());
						}
					} else {
					?>
					<div class="alert alert-danger">Your vote message must be a maximum of 2048 characters</div>
					<?php 
					}
				} else {
					echo '<div class="alert alert-warning">' . $admin_language['invalid_token'] . '</div>';
				}
			}
			// Get vote message
			$vote_message = $queries->getWhere('vote_settings', array('id', '=', 1));
			$vote_message = htmlspecialchars($vote_message[0]->value);
			?>
			<hr />
			<form action="" method="post">
			  <div class="form-group">
				<label for="InputMessage">Message to display at top of Vote page <em>(This can be left blank)</em></label><br />
				<textarea name="message" rows="3" id="InputMessage" class="form-control"><?php echo htmlspecialchars($vote_message); ?></textarea>
			  </div>  
			  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			  <input type="submit" value="Submit" class="btn btn-primary">
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
										echo '<script>window.location.replace("/admin/vote");</script>';
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
				<h3>New Vote Site</h3>
				<form action="" method="post">
				  <div class="form-group">
					<label for="InputVoteName">Vote Site Name</label>
					<input type="text" id="InputVoteName" placeholder="Vote site name" name="vote_site_name" class="form-control">
				  </div>
				  <div class="form-group">
					<label for="InputVoteURL">Vote Site URL <em>(With preceding http://)</em></label>
					<input type="text" id="InputVoteURL" placeholder="Vote site URL" name="vote_site_url" class="form-control">
				  </div>
				  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
				  <input type="submit" value="Submit" class="btn btn-primary">
				  <a href="/admin/vote" class="btn btn-warning">Back</a>
				</form>
					<?php
					} else if($_GET['action'] == 'edit'){
						// Edit page
						if(!is_numeric($_GET["vid"])){
							echo '<script>window.location.replace("/admin/vote");</script>';
							die();
						} else {
							$site = $queries->getWhere("vote_sites", array("id", "=", $_GET["vid"]));
							if(!count($site)){
								echo '<script>window.location.replace("/admin/vote");</script>';
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
										echo '<script>window.location.replace("/admin/vote");</script>';
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
				<h3>Editing site</h3>
				<form role="form" action="" method="post">
				  <div class="form-group">
					<label for="InputName">Vote Site Name</label>
					<input type="text" name="vote_name" class="form-control" id="InputName" placeholder="Vote site name" value="<?php echo htmlspecialchars($site->name); ?>">
				  </div>
				  <div class="form-group">
					<label for="InputURL">Vote Site URL <em>(With preceding http://)</em></label>
					<input type="text" name="vote_url" id="InputURL" placeholder="Vote site URL" class="form-control" value="<?php echo htmlspecialchars($site->site); ?>">
				  </div>
				  <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
				  <input type="submit" value="Submit" class="btn btn-primary">
				  <a href="/admin/vote" class="btn btn-warning">Back</a>
				</form>
					<?php
					} else if($_GET['action'] == 'delete'){
						// Delete a site
						if(!isset($_GET["vid"]) || !is_numeric($_GET["vid"])){
							echo '<script>window.location.replace("/admin/vote");</script>';
							die();
						}
						try {
							$queries->delete('vote_sites', array('id', '=' , $_GET["vid"]));
							echo '<script>window.location.replace("/admin/vote");</script>';
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