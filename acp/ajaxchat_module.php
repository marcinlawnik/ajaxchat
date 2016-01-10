<?php

/**
 *
 * Ajax Chat extension for phpBB.
 *
 * @copyright (c) 2015 spaceace <http://www.livemembersonly.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace spaceace\ajaxchat\acp;

class ajaxchat_module
{

	/** @var string The currenct action */
	public $u_action;

	/** @var \phpbb\config\config */
	public $new_config = [];

	/** @var string form key */
	public $form_key;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\request\request */
	protected $request;

	public function main($id, $mode)
	{
		global $phpbb_container, $table_prefix;

		if (!defined('CHAT_TABLE'))
		{
			$chat_table = $table_prefix . 'ajax_chat';
			define('CHAT_TABLE', $chat_table);
		}
		if (!defined('CHAT_RULES'))
		{
			$chat_rules = $table_prefix . 'ajax_chat_rules';
			define('CHAT_RULES', $chat_rules);
		}
		$this->id		 = $id;
		$this->mode		 = $mode;
		// Initialization
		$this->auth		 = $phpbb_container->get('auth');
		$this->config	 = $phpbb_container->get('config');
		$this->db		 = $phpbb_container->get('dbal.conn');
		$this->user		 = $phpbb_container->get('user');
		$this->template	 = $phpbb_container->get('template');
		$this->request	 = $phpbb_container->get('request');

		$this->u_action = $this->request->variable('action', '', true);

		$submit			 = ($this->request->is_set_post('submit')) ? true : false;
		$this->form_key	 = 'acp_ajax_chat';
		add_form_key($this->form_key);

		$display_vars = [
			'title'	 => 'ACP_AJAX_CHAT',
			'vars'	 => [
				'legend1'						=> 'AJAX_CHAT_SETTINGS',
				'display_ajax_chat'				=> ['lang' => 'DISPLAY_AJAX_CHAT', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => false],
				'index_display_ajax_chat'		=> ['lang' => 'INDEX_DISPLAY_AJAX_CHAT', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => true],
				'whois_chatting'				=> ['lang' => 'WHOIS_CHATTING', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => true],
				'ajax_chat_archive_amount'		=> ['lang' => 'ARCHIVE_AMOUNT_AJAX_CHAT', 'validate' => 'int', 'type' => 'number:5:500', 'explain' => true],
				'ajax_chat_popup_amount'		=> ['lang' => 'POPUP_AMOUNT_AJAX_CHAT', 'validate' => 'int', 'type' => 'number:5:150', 'explain' => true],
				'ajax_chat_index_amount'		=> ['lang' => 'INDEX_AMOUNT_AJAX_CHAT', 'validate' => 'int', 'type' => 'number:5:150', 'explain' => true],
				'ajax_chat_chat_amount'			=> ['lang' => 'CHAT_AMOUNT_AJAX_CHAT', 'validate' => 'int', 'type' => 'number:5:150', 'explain' => true],
				'ajax_chat_time_setting'		=> ['lang' => 'TIME_SETTING_AJAX_CHAT', 'validate' => 'string', 'type' => 'text:10:20', 'explain' => true],
				'refresh_online_chat'			=> ['lang' => 'REFRESH_ONLINE_CHAT', 'validate' => 'int', 'type' => 'number:0:9999', 'explain' => true],
				'refresh_idle_chat'				=> ['lang' => 'REFRESH_IDLE_CHAT', 'validate' => 'int', 'type' => 'number:0:9999', 'explain' => true],
				'refresh_offline_chat'			=> ['lang' => 'REFRESH_OFFLINE_CHAT', 'validate' => 'int', 'type' => 'number:0:9999', 'explain' => true],
				'status_online_chat'			=> ['lang' => 'STATUS_ONLINE_CHAT', 'validate' => 'int', 'type' => 'number:0:9999', 'explain' => true],
				'status_idle_chat'				=> ['lang' => 'STATUS_IDLE_CHAT', 'validate' => 'int', 'type' => 'number:0:9999', 'explain' => true],
				'status_offline_chat'			=> ['lang' => 'STATUS_OFFLINE_CHAT', 'validate' => 'int', 'type' => 'number:0:9999', 'explain' => true],
				'legend2'						=> 'AJAX_CHAT_RULES',
				'rule_ajax_chat'				=> ['lang' => 'RULES_AJAX_CHAT', 'validate' => 'string', 'type' => 'textarea:4:70', 'explain' => true],
				'legend3'						=> 'AJAX_CHAT_LOCATION',
				'location_ajax_chat_override'	=> ['lang' => 'LOCATION_AJAX_CHAT_OVERRIDE', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => true],
				'location_ajax_chat'			=> ['lang' => 'LOCATION_AJAX_CHAT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
				'legend4'						=> 'AJAX_CHAT_POSTS',
				'ajax_chat_forum_posts'			=> ['lang' => 'FORUM_POSTS_AJAX_CHAT', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => false],
				'ajax_chat_forum_topic'			=> ['lang' => 'FORUM_POSTS_AJAX_CHAT_TOPIC', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => false],
				'ajax_chat_forum_reply'			=> ['lang' => 'FORUM_POSTS_AJAX_CHAT_REPLY', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => false],
				'ajax_chat_forum_edit'			=> ['lang' => 'FORUM_POSTS_AJAX_CHAT_EDIT', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => false],
				'ajax_chat_forum_quote'			=> ['lang' => 'FORUM_POSTS_AJAX_CHAT_QUOTE', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => false],
				'legend5'						=> 'AJAX_CHAT_PRUNE',
				'prune_ajax_chat'				=> ['lang' => 'PRUNE_AJAX_CHAT', 'validate' => 'bool', 'type' => 'radio:enabled_enabled', 'explain' => true],
				'prune_keep_ajax_chat'			=> ['lang' => 'PRUNE_KEEP_AJAX_CHAT', 'validate' => 'int', 'type' => 'number', 'explain' => false],
				'prune_now'						=> ['lang' => 'PRUNE_NOW', 'validate' => 'bool', 'type' => 'custom', 'explain' => false, 'method' => 'prune_chat'],
				'truncate_now'					=> ['lang' => 'TRUNCATE_NOW', 'validate' => 'bool', 'type' => 'custom', 'explain' => false, 'method' => 'truncate_chat'],
				'ajax_chat_counter'				=> ['lang' => 'CHAT_COUNTER', 'validate' => 'bool', 'type' => 'custom', 'explain' => false, 'method' => 'chat_counter'],
				'legend6'						=> 'ACP_SUBMIT_CHANGES'
			],
		];

		#region Submit
		if ($submit)
		{
			$submit = $this->do_submit_stuff($display_vars);

			// If the submit was valid, so still submitted
			if ($submit)
			{
				trigger_error($this->user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action), E_USER_NOTICE);
			}
		}
		#endregion

		$this->generate_stuff_for_cfg_template($display_vars);

		// Output page template file
		$this->tpl_name		 = 'ajax_chat';
		$this->page_title	 = $this->user->lang($display_vars['title']);
	}

	/**
	 * Chat rule.
	 */
	public function chat_rules()
	{
		$chat_rules = $this->request->variable('chat_rules', '', true);
		$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
		$allow_bbcode = $this->request->variable('rules_parse_bbcode', true);
		$allow_smilies = $this->request->variable('rules_parse_smilies', true);
		$allow_urls = $this->request->variable('rules_parse_urls', true);
		generate_text_for_storage($chat_rules, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

		$sql_ary = array(
			'chat_rules'		=> $chat_rules,
			'bbcode_uid'		=> $uid,
			'bbcode_bitfield'	=> $bitfield,
			'bbcode_options'	=> $options,
		);

		$sql = 'INSERT INTO ' . CHAT_RULES . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);
		$chat_rule  = '<dd><textarea id="chat_rules" name="config[rule_ajax_chat]" rows="4" cols="70" >' . $chat_rules . '</textarea></dd>
			<dd><label><input type="checkbox" class="radio" name="chat_rules_parse_bbcode" checked="checked" /> ' . $this->user->lang['PARSE_BBCODE'] . '</label>
			<label><input type="checkbox" class="radio" name="chat_rules_parse_smilies" checked="checked" /> ' . $this->user->lang['PARSE_SMILIES'] . '</label>
			<label><input type="checkbox" class="radio" name="chat_rules_parse_urls" checked="checked" /> ' . $this->user->lang['PARSE_URLS'] . '</label></dd>';
		return $chat_rule;
	}

	/**
	 * Counter for Ajax Chat messages.
	 */
	public function chat_counter()
	{
		$sql = 'SELECT COUNT(*)
			FROM ' . CHAT_TABLE . '';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$chat_counter	 = '<div style="width: 75px; height: auto; border: 1px solid #CCCCCC; text-align: right; padding: 0 2px; word-wrap: initial; overflow: hidden;">' . $row['COUNT(*)'] . '</div>';
		return $chat_counter;
	}

	/**
	 * Prune chat table function.
	 *
	 * @param string $value The value
	 * @param string $key The key
	 * @return string The formatted string of this item
	 */
	public function prune_chat($value, $key)
	{
		if (!confirm_box(true))
		{
			if ($this->u_action === 'prune_chat')
			{
				confirm_box(false, $this->user->lang['CONFIRM_PRUNE_AJAXCHAT'], build_hidden_fields([
					'i'		 => $this->id,
					'mode'	 => $this->mode,
					'action' => $this->u_action,
				]));
			}
		}
		else
		{
			if (!$this->auth->acl_get('a_board'))
			{
				trigger_error($this->user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			if ($this->u_action === 'prune_chat')
			{
				$sql	 = 'SELECT message_id 
							FROM ' . CHAT_TABLE . ' 
							ORDER BY message_id DESC ';
				$result	 = $this->db->sql_query_limit($sql, $this->config['prune_keep_ajax_chat'], 1);
				$row	 = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);
				$sql1 = 'DELETE FROM ' . CHAT_TABLE . '
						 WHERE `message_id` < ' . (int) $row['message_id'] . '';
				$this->db->sql_query($sql1);

				add_log('admin', 'PRUNE_LOG_AJAXCHAT');

				if ($this->request->is_ajax())
				{
					trigger_error($this->user->lang['PRUNE_CHAT_SUCCESS']);
				}
			}
		}
		$this->id = str_replace("\\", "-", $this->id);
		return '<a href="' . append_sid('?i=' . $this->id . '&mode=' . $this->mode . '&action=prune_chat') . '" data-ajax="true"><input class="button2" type="submit" id="' . $key . '_enable" name="' . $key . '_enable" value="' . $this->user->lang['PRUNE_NOW'] . '" /></a>';
	}

	/**
	 * Truncate chat table function.
	 *
	 * @param string $value The value
	 * @param string $key The key
	 * @return string The formatted string of this item
	 */
	public function truncate_chat($value, $key)
	{
		if (!confirm_box(true))
		{
			if ($this->u_action === 'truncate_chat')
			{
				confirm_box(false, $this->user->lang['CONFIRM_TRUNCATE_AJAXCHAT'], build_hidden_fields([
					'i'		 => $this->id,
					'mode'	 => $this->mode,
					'action' => $this->u_action,
				]));
			}
		}
		else
		{
			if (!$this->auth->acl_get('a_board'))
			{
				trigger_error($this->user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			if ($this->u_action === 'truncate_chat')
			{
				$sql1 = 'TRUNCATE ' . CHAT_TABLE . '';
				$this->db->sql_query($sql1);

				add_log('admin', 'TRUNCATE_LOG_AJAXCHAT');

				if ($this->request->is_ajax())
				{
					trigger_error($this->user->lang['TRUNCATE_CHAT_SUCCESS']);
				}
			}
		}
		$this->id	 = str_replace("\\", "-", $this->id);
		$action		 = append_sid('?i=' . $this->id . '&mode=' . $this->mode . '&action=truncate_chat');
		return '<a href="' . $action . '" data-ajax="true"><input class="button2" type="submit" id="' . $key . '_enable" name="' . $key . '_enable" value="' . $this->user->lang['TRUNCATE_NOW'] . '" /></a>';
	}

	/**
	 * Abstracted method to do the submit part of the acp. Checks values, saves them
	 * and displays the message.
	 * If error happens, Error is shown and config not saved. (so this method quits and returns false.
	 *
	 * @param array $display_vars The display vars for this acp site
	 * @param array $special_functions Assoziative Array with config values where special functions should run on submit instead of simply save the config value. Array should contain 'config_value' => function ($this) { function code here }, or 'config_value' => null if no function should run.
	 * @return bool Submit valid or not.
	 */
	protected function do_submit_stuff($display_vars, $special_functions = [])
	{
		$this->new_config	 = $this->config;
		$cfg_array			 = ($this->request->is_set('config')) ? $this->request->variable('config', ['' => ''], true) : $this->new_config;
		$error				 = isset($error) ? $error : [];

		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if (!check_form_key($this->form_key))
		{
			$error[] = $this->user->lang['FORM_INVALID'];
		}

		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
			return false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			// We want to skip that, or run the function. (We do this before checking if there is a request value set for it,
			// cause maybe we want to run a function anyway, based on whatever. We can check stuff manually inside this function)
			if (is_array($special_functions) && array_key_exists($config_name, $special_functions))
			{
				$func = $special_functions[$config_name];
				if (isset($func) && is_callable($func))
				{
					$func();
				}
				continue;
			}
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}
			// Sets the config value
			$this->new_config[$config_name] = $cfg_array[$config_name];
			$this->config->set($config_name, $cfg_array[$config_name]);
		}
		return true;
	}

	/**
	 * Abstracted method to generate acp configuration pages out of a list of display vars, using
	 * the function build_cfg_template().
	 * Build configuration template for acp configuration pages
	 *
	 * @param array $display_vars The display vars for this acp site
	 */
	protected function generate_stuff_for_cfg_template($display_vars)
	{
		$this->new_config	 = $this->config;
		$cfg_array			 = ($this->request->is_set('config')) ? $this->request->variable('config', ['' => ''], true) : $this->new_config;
		$error				 = isset($error) ? $error : [];

		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$this->template->assign_block_vars('options', [
					'S_LEGEND'	 => true,
					'LEGEND'	 => (isset($this->user->lang[$vars])) ? $this->user->lang[$vars] : $vars]
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($this->user->lang[$vars['lang_explain']])) ? $this->user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($this->user->lang[$vars['lang'] . '_EXPLAIN'])) ? $this->user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$this->template->assign_block_vars('options', [
				'KEY'			 => $config_key,
				'TITLE'			 => (isset($this->user->lang[$vars['lang']])) ? $this->user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		 => $vars['explain'],
				'TITLE_EXPLAIN'	 => $l_explain,
				'CONTENT'		 => $content,
			]);

			//unset($display_vars['vars'][$config_key]);
		}

		$this->template->assign_vars([
			'S_ERROR'	 => (sizeof($error)) ? true : false,
			'ERROR_MSG'	 => implode('<br />', $error),
			'U_ACTION'	 => $this->u_action]
		);
	}
}
