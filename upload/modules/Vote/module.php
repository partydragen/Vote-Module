<?php 
/*
 *	Made by Partydragen
 *  https://github.com/partydragen/Vote-Module
 *  https://partydragen.com
 *  NamelessMC version 2.0.0-pr13
 *
 *  License: MIT
 *
 *  Vote module info file
 */

class Vote_Module extends Module {
	private $_vote_language;

	public function __construct($vote_language, $pages, $cache) {
		$this->_vote_language = $vote_language;

		$name = 'Vote';
		$author = '<a href="https://partydragen.com" target="_blank" rel="nofollow noopener">Partydragen</a>';
		$module_version = '2.3.1';
		$nameless_version = '2.0.0-pr13';

		parent::__construct($this, $name, $author, $module_version, $nameless_version);

		// Define URLs which belong to this module
		$pages->add('Vote', '/vote', 'pages/vote.php', 'vote', true);
		$pages->add('Vote', '/panel/vote', 'pages/panel/vote.php');

		// Check if module version changed
		$cache->setCache('vote_module_cache');
		if (!$cache->isCached('module_version')) {
			$cache->store('module_version', $module_version);
		} else {
			if ($module_version != $cache->retrieve('module_version')) {
				// Version have changed, Perform actions
				$cache->store('module_version', $module_version);

				if ($cache->isCached('update_check')) {
                    $cache->erase('update_check');
                }
			}
		}
	}

	public function onInstall() {
        // Initialise
        $this->initialise();
	}

	public function onUninstall() {
		// No actions necessary
	}

	public function onEnable() {
        // Check if we need to initialise again
        $this->initialise();
	}

	public function onDisable() {
		// No actions necessary
	}

	public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template) {
		// AdminCP
		PermissionHandler::registerPermissions('Vote', [
			'admincp.vote' => $this->_vote_language->get('vote', 'vote')
		]);

		// navigation link location
		$cache->setCache('nav_location');
		if (!$cache->isCached('vote_location')) {
			$link_location = 1;
			$cache->store('vote_location', 1);
		} else {
			$link_location = $cache->retrieve('vote_location');
		}

		// Navigation icon
		$cache->setCache('navbar_icons');
		if (!$cache->isCached('vote_icon')) {
			$icon = '';
		} else {
			$icon = $cache->retrieve('vote_icon');
		}

		// Navigation order
		$cache->setCache('navbar_order');
		if (!$cache->isCached('vote_order')) {
			// Create cache entry now
			$vote_order = 3;
			$cache->store('vote_order', 3);
		} else {
			$vote_order = $cache->retrieve('vote_order');
		}

		switch($link_location) {
			case 1:
				// Navbar
				$navs[0]->add('vote', $this->_vote_language->get('vote', 'vote'), URL::build('/vote'), 'top', null, $vote_order, $icon);
			break;
			case 2:
				// "More" dropdown
				$navs[0]->addItemToDropdown('more_dropdown', 'vote', $this->_vote_language->get('vote', 'vote'), URL::build('/vote'), 'top', null, $icon, $vote_order);
			break;
			case 3:
				// Footer
				$navs[0]->add('vote', $this->_vote_language->get('vote', 'vote'), URL::build('/vote'), 'footer', null, $vote_order, $icon);
			break;
		}

		if (defined('BACK_END')) {
			if ($user->hasPermission('admincp.vote')) {
				$cache->setCache('panel_sidebar');
				if (!$cache->isCached('vote_order')) {
					$order = 20;
					$cache->store('vote_order', 20);
				} else {
					$order = $cache->retrieve('vote_order');
				}

				if (!$cache->isCached('vote_icon')) {
					$icon = '<i class="nav-icon fas fa-cogs"></i>';
					$cache->store('vote_icon', $icon);
				} else {
					$icon = $cache->retrieve('vote_icon');
				}
				
				$navs[2]->add('vote_divider', mb_strtoupper($this->_vote_language->get('vote', 'vote'), 'UTF-8'), 'divider', 'top', null, $order, '');
				$navs[2]->add('vote', $this->_vote_language->get('vote', 'vote'), URL::build('/panel/vote'), 'top', null, $order + 0.1, $icon);
			}
		}

		// Check for module updates
        if (isset($_GET['route']) && $user->isLoggedIn() && $user->hasPermission('admincp.update')) {
            // Page belong to this module?
            $page = $pages->getActivePage();
            if ($page['module'] == 'Vote') {

                $cache->setCache('vote_module_cache');
                if ($cache->isCached('update_check')) {
                    $update_check = $cache->retrieve('update_check');
                } else {
					require_once(ROOT_PATH . '/modules/Vote/classes/Vote.php');
                    $update_check = Vote::updateCheck();
                    $cache->store('update_check', $update_check, 3600);
                }

                $update_check = json_decode($update_check);
                if (!isset($update_check->error) && !isset($update_check->no_update) && isset($update_check->new_version)) {  
                    $smarty->assign([
                        'NEW_UPDATE' => (isset($update_check->urgent) && $update_check->urgent == 'true') ? $this->_vote_language->get('vote', 'new_urgent_update_available_x', ['module' => $this->getName()]) : $this->_vote_language->get('vote', 'new_update_available_x', ['module' => $this->getName()]),
                        'NEW_UPDATE_URGENT' => (isset($update_check->urgent) && $update_check->urgent == 'true'),
                        'CURRENT_VERSION' => $this->_vote_language->get('vote', 'current_version_x', ['version' => Output::getClean($this->getVersion())]),
                        'NEW_VERSION' => $this->_vote_language->get('vote', 'new_version_x', ['new_version' => Output::getClean($update_check->new_version)]),
                        'UPDATE' => $this->_vote_language->get('vote', 'view_resource'),
                        'UPDATE_LINK' => Output::getClean($update_check->link)
                    ]);
                }
            }
        }
	}

    public function getDebugInfo(): array {
    }

    private function initialise() {
        // Generate tables
        try {
            $engine = Config::get('mysql/engine');
            $charset = Config::get('mysql/charset');
        } catch (Exception $e) {
            $engine = 'InnoDB';
            $charset = 'utf8mb4';
        }
        if (!$engine || is_array($engine))
            $engine = 'InnoDB';
        if (!$charset || is_array($charset))
            $charset = 'utf8mb4';

        $queries = new Queries();

		try {
            if (!$queries->tableExists('vote_sites')) {
                $queries->createTable("vote_sites", " `id` int(11) NOT NULL AUTO_INCREMENT, `site` varchar(512) NOT NULL, `name` varchar(64) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=$engine DEFAULT CHARSET=$charset");

                $queries->create('vote_sites', [
                    'site' => 'https://mc-server-list.com/',
                    'name' => 'MC-Server-List (Example)'
                ]);
                $queries->create('vote_sites', [
                    'site' => 'http://planetminecraft.com/',
                    'name' => 'PlanetMinecraft (Example)'
                ]);
            }
		} catch (Exception $e) {
			// Error
		}

        try {
            if (!$queries->tableExists('vote_settings')) {
                $queries->createTable("vote_settings", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(20) NOT NULL, `value` varchar(2048) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=$engine DEFAULT CHARSET=$charset");

                // Insert data
                $queries->create('vote_settings', [
                    'name' => 'vote_message',
                    'value' => 'You can manage this vote module in StaffCP -> Vote'
                ]);
            }
        } catch (Exception $e) {
            // Error
        }

		try {
			// Update main admin group permissions
			$group = $queries->getWhere('groups', ['id', '=', 2]);
			$group = $group[0];

			$group_permissions = json_decode($group->permissions, TRUE);
			$group_permissions['admincp.vote'] = 1;

			$group_permissions = json_encode($group_permissions);
			$queries->update('groups', 2, ['permissions' => $group_permissions]);
		} catch (Exception $e) {
			// Error
		}
    }
}
