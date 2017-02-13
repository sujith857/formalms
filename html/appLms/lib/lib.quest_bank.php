<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

class QuestBankMan {

	var $_table_category;

	var $last_error = '';

	function _query($query) {

		$re = sql_query($query);
		if(Get::sett('do_debug') == 'on' && isset($GLOBALS['page'])) $GLOBALS['page']->add('<!-- '.$query.' :: '.sql_error().' -->', 'debug');
		return $re;
	}

	function fetch($re) {

		return sql_fetch_row($re);
	}

	function num_rows($re) {

		return sql_num_rows($re);
	}

	function QuestBankMan() {
		$this->_table_category = $GLOBALS['prefix_lms'].'_quest_category';
		$this->_table_quest = $GLOBALS['prefix_lms'].'_testquest';
	}

	function getCategoryList($author = false) {

		$cat_list = array();
		$qtxt = "SELECT idCategory, name "
			."FROM ".$this->_table_category." ";
			//."WHERE author = 0 ";
		//if($author !== false) $qtxt .= " OR author = ".(int)$author." ";
		$re = $this->_query($qtxt);
		while(list($id_cat, $name) = sql_fetch_row($re)) {

			$cat_list[$id_cat] = $name;
		}
		return $cat_list;
	}


	function getQuestFromId($arr_quest) {

		$quests = array();
		$qtxt ="
		SELECT idQuest, type_quest
		FROM ".$GLOBALS['prefix_lms']."_testquest
		WHERE idTest = '0' AND idQuest IN (".implode(',', $arr_quest).")
		ORDER BY page, sequence";
		$re_quest = sql_query($qtxt);
		while(list($id_quest, $type_quest) = sql_fetch_row($re_quest)) {

			$quests[$id_quest] = $type_quest;
		}
		return $quests;
	}

	function resQuestList($quest_category = false, $quest_difficult = false, $type_quest = false, $start = false, $result = false) {

		$cat_list = array();
		$qtxt = "SELECT idQuest, idCategory, type_quest, title_quest, difficult, time_assigned "
			."FROM ".$this->_table_quest." "
			."WHERE idTest = 0 ";
		if($quest_category != false) 		$qtxt .= " AND idCategory = '$quest_category' ";
		if($quest_difficult != false) 	$qtxt .= " AND difficult = '$quest_difficult' ";
		if($type_quest != false) 		$qtxt .= " AND type_quest = '$type_quest' ";
		$qtxt .= "ORDER BY idCategory, title_quest";
		if($start !== false) $qtxt .= " LIMIT $start,$result";
		$re = $this->_query($qtxt);

		return $re;
	}

	function totalQuestList($quest_category = false, $quest_difficult = false, $type_quest = false) {

		$cat_list = array();
		$qtxt = "SELECT COUNT(*) "
			."FROM ".$this->_table_quest." "
			."WHERE idTest = 0 ";
		if($quest_category != false) 		$qtxt .= " AND idCategory = '$quest_category' ";
		if($quest_difficult != false) 	$qtxt .= " AND difficult = '$quest_difficult' ";
		if($type_quest != false) 		$qtxt .= " AND type_quest = '$type_quest' ";
		$re = $this->_query($qtxt);

		list($num) = sql_fetch_row($re);
		return $num;
	}

	function get_quest_instance($id_quest, $type_file = false, $type_class = false) {

		if($type_file == false || $type_class == false) {

			$re_quest = sql_query("
			SELECT type_quest
			FROM ".$this->_table_quest."
			WHERE idQuest = '".$id_quest."' AND idTest = 0 ");
			if(!sql_num_rows($re_quest)) {
				$this->last_error = 'quest_not_found';
				return false;
			}
			list($type_quest) = sql_fetch_row($re_quest);

			$re_quest = sql_query("
			SELECT type_file, type_class
			FROM ".$GLOBALS['prefix_lms']."_quest_type
			WHERE type_quest = '".$type_quest."'");
			if(!sql_num_rows($re_quest)) {
				$this->last_error = 'quest_not_found';
				return false;
			}
			list($type_file, $type_class) = sql_fetch_row($re_quest);
		}

		require_once( $GLOBALS['where_lms'].'/modules/question/'.$type_file);
		$quest_obj = new $type_class ( $id_quest );

		return $quest_obj;
	}

	function instanceQuestType($id_quest, $type_quest) {

		$re_quest = sql_query("
		SELECT type_file, type_class
		FROM ".$GLOBALS['prefix_lms']."_quest_type
		WHERE type_quest = '".$type_quest."'");
		if(!sql_num_rows($re_quest)) {
			$this->last_error = 'quest_not_found';
			return false;
		}
		list($type_file, $type_class) = sql_fetch_row($re_quest);


		require_once( $GLOBALS['where_lms'].'/modules/question/'.$type_file);
		$quest_obj = new $type_class ( $id_quest );

		return $quest_obj;
	}

	function delQuest($id_quest) {

		$this->last_error = '';

		$quest_obj = $this->get_quest_instance($id_quest);
		if(!$quest_obj) {
			$this->last_error = 'quest_not_found';
			return false;
		}

		if(!$quest_obj->del()) {
			$this->last_error = 'operation_error';
			return false;
		}

		return true;
	}

	function import_quest($file_lines, $file_format, $id_test = 0, $id_category = 0) {

		$result = array();
		switch($file_format) {
			case 0 : {	// gift format -------------------

				require_once($GLOBALS['where_lms'].'/modules/question/format.gift.php');

				$qgift = new  qformat_gift();
				$formatted = $qgift->readquestions($file_lines);

				foreach($formatted as $question) {

					if ((int)$id_category > 0 && is_object($question)) $question->id_category = (int)$id_category;

					$oQuest = $this->instanceQuestType(0, $question->qtype);
					$re = $oQuest->importFromRaw($question, $id_test);

					if($re) {
						if(isset($result[$question->qtype]['success'])) $result[$question->qtype]['success']++;
						else $result[$question->qtype]['success'] = 1;
					} else  {
						if(isset($result[$question->qtype]['fail'])) $result[$question->qtype]['fail']++;
						else $result[$question->qtype]['fail'] = 1;
					}
				}
			};break;
			case 1 : {	// xml moodle format -------------

			};break;
		}
		return $result;
	}

	function export_quest($quest_list, $file_format) {

		$quest_export = '';
		switch($file_format) {
			case 0 : {	// gift format -------------------

				require_once($GLOBALS['where_lms'].'/modules/question/format.gift.php');
				$qgift = new  qformat_gift();

				while(list($id_quest, $type_quest) = each($quest_list)) {

					$oQuest 	= $this->instanceQuestType($id_quest, $type_quest);
					if($oQuest) {
						$oRawQuest 	= $oQuest->exportToRaw($id_quest);

						$quest_export .= $qgift->writequestion($oRawQuest);
					} else {
						die($type_quest);
					}
				}
			};break;
			case 1 : {	// xml moodle format -------------

			};break;
		}
		return $quest_export;
	}

	function supported_format() {

		$formats = array(
		   //-1 => Lang::t('_NEW_TEST', 'test'),
		   0 => Lang::t('_GIFT', 'test')//,
		   //1 => Lang::t('_MOODLE_XML', 'test')
		);
		return $formats;
	}

}

class QuestBank_Selector {

	function QuestBank_Selector() {

		$this->lang =& DoceboLanguage::createInstance('test', 'lms');
		$this->form 	= new Form();
		$this->qb_man = new QuestBankMan();

		$this->all_category = $this->qb_man->getCategoryList(getLogUserId());
		//#2269 see it2.php.net/array_unshift#78238
		//array_unshift($this->all_category, $this->lang->def('_ALL_QUEST_CATEGORY'));
		$aany_cat=array(0=>$this->lang->def('_ALL_QUEST_CATEGORY'));
		$this->all_category = $aany_cat + $this->all_category;

		$this->all_difficult = array(
			0 => $this->lang->def('_ALL_DIFFICULT'),
			5 => $this->lang->def('_VERY_HARD'),
			4 => $this->lang->def('_HARD'),
			3 => $this->lang->def('_DIFFICULT_MEDIUM'),
			2 => $this->lang->def('_DIFFICULT_EASY'),
			1 => $this->lang->def('_DIFFICULT_VERYEASY')
		);

		$this->all_quest_type = array();
		$this->all_quest_type[0] = $this->lang->def('_ALL_QUEST_TYPE');
		$this->all_quest_type_long[0] = $this->lang->def('_ALL_QUEST_TYPE');
		$re_type = sql_query("
		SELECT type_quest
		FROM ".$GLOBALS['prefix_lms']."_quest_type
		ORDER BY sequence");
		while(list($type_quest) = sql_fetch_row($re_type)) {

			$this->all_quest_type[$type_quest] = $this->lang->def('_QUEST_ACRN_'.strtoupper($type_quest));
				//.' - '.$this->lang->def('_QUEST_'.strtoupper($type_quest));
			$this->all_quest_type_long[$type_quest] = $this->lang->def('_QUEST_ACRN_'.strtoupper($type_quest))
				.' - '.$this->lang->def('_QUEST_'.strtoupper($type_quest));
		}

		$this->mod_action = checkPerm('mod', true);		//TODO: check user permissions
	}

	function get_header() {

		$head = '';
		YuiLib::load('base,table');
		Util::get_js(Get::rel_path('base').'/lib/lib.elem_selector.js', true, true);
		Util::get_js(Get::rel_path('lms').'/modules/quest_bank/ajax.quest_bank.js', true, true);

		return $head;
	}

	function get_setup_js() {

		$str = 'var quest_per_page = '.(int)$this->item_per_page.';';

		$str .= 'var hidden_for_selection = "'.$this->selected_quest.'";';

		$str .= 'var use_mod_action = '.( $this->mod_action ? 'true' : 'false' ).';';

		$str .= 'var QB_PATHS = {'
				.'image:"'.getPathImage().'",'
				.'mod_link:"index.php?modname=quest_bank&op=modquest&id_quest='.'",'
				.'del_req2:"'.'modules/quest_bank/ajax.quest_bank.php?op=delquest'.'",'
				.'del_req:"'.'ajax.server.php?mn=quest_bank&op=delquest&plf=lms'.'"'
			.'};';

		$str .= 'var QB_DEF = {'
				.'checkbox_sel:"",'
				.'title_quest:"'.addslashes($this->lang->def('_TITLE')).'",'
				.'quest_category:"'.addslashes($this->lang->def('_TEST_QUEST_CATEGORY')).'",'
				.'difficult:"'.addslashes($this->lang->def('_DIFFICULTY')).'",'
				.'type_quest:"'.addslashes($this->lang->def('_TYPE')).'",'
				.'mod_quest:"'.addslashes($this->lang->def('_MOD')).'",'
				.'del_quest:"'.addslashes($this->lang->def('_DEL')).'",'
				.'mod_quest_img:"<img src=\"'.getPathImage().'/standard/edit.png\" alt=\"'.$this->lang->def('_MOD').'\" />",'
				.'del_quest_img:"<img src=\"'.getPathImage().'/standard/delete.png\" alt=\"'.$this->lang->def('_DEL').'\" />",'

				.'del_quest:"'.addslashes($this->lang->def('_DEL')).'",'
				.'del_confirm:"'.addslashes($this->lang->def('_AREYOUSURE')).'",'

				.'yes:"'.addslashes($this->lang->def('_YES')).'",'
				.'undo:"'.addslashes($this->lang->def('_UNDO')).'",'

				.'prev:"'.addslashes($this->lang->def('_PREV')).'",'
				.'next:"'.addslashes($this->lang->def('_NEXT')).'"'
			.'};';


		$str .= 'var QB_CATEGORIES = new Array();
			QB_CATEGORIES[0] = "'.addslashes($this->lang->def('_NONE')).'";
		';
		foreach($this->all_category as $idc => $namec) {
			if($idc != 0) $str .= "QB_CATEGORIES[".$idc."] = '".addslashes($namec)."'; ";
		}
		$str .= 'var QB_DIFFICULT = new Array(5);';
		foreach($this->all_difficult as $num => $trad) {
			if($num != 0) $str .= "QB_DIFFICULT[".$num."] = '".addslashes($trad)."'; ";
		}
		$str .= 'var QB_QTYPE = {';
		$first = true;
		foreach($this->all_quest_type as $type_quest => $phrase) {

			if($type_quest != '0') {
				$str .= ($first?'':',')." $type_quest: '".addslashes($phrase)."'  ";
				$first = false;
			}
		}
		$str .= '}';
		return $str;
	}

	function get_filter() {

		$str = $this->form->getOpenFieldset($this->lang->def('_SEARCH'), 'fieldset_search_quest')

			.$this->form->getDropdown($this->lang->def('_TEST_QUEST_CATEGORY'),
								'quest_category',
								'quest_category',
								$this->all_category,
								Get::req('quest_category', DOTY_INT) )

			.$this->form->getDropdown($this->lang->def('_DIFFICULTY'),
								'quest_difficult',
								'quest_difficult',
								$this->all_difficult,
								Get::req('quest_difficult', DOTY_ALPHANUM) )

			.$this->form->getDropdown($this->lang->def('_TYPE'),
								'quest_type',
								'quest_type',
								$this->all_quest_type_long,
								Get::req('quest_type', DOTY_INT) )

			.$this->form->openButtonSpace('search_button')
			.$this->form->getButton(	'quest_reset',
								'quest_reset',
								$this->lang->def('_UNDO'),
								false,
								' style="visibility: hidden;" ' )

			.$this->form->getButton(	'quest_search',
								'quest_search',
								$this->lang->def('_SEARCH') )
			.$this->form->closeButtonSpace()

			.$this->form->getCloseFieldset('');
		return $str;
	}

	function get_selector() {

		$str = '';
		$str .= '<div id="dialog_container"></div>';
		$str .= '<div id="paginator_head"></div>';
		$str .= '<div class="selector_options" style="position: relative;">'
			.'[ <a id="select_all" href="#">'.$this->lang->def('_SELECT_ALL').'</a>'
				.' | '.'<a id="select_page" href="#">'.$this->lang->def('_SELECT_PAGE').'</a> ]'

			.' [ <a id="deselect_all" href="#">'.$this->lang->def('_UNSELECT_ALL').'</a>'
				.' | '.'<a id="deselect_page" href="#">'.$this->lang->def('_DESELECT_PAGE').'</a> ]'

			.'<div class="current_selection" style="position:absolute; right: 10px; top:2px;">'
			.$this->lang->def('_CURRENT_SELECTION_COUNT').' : <span id="current_selected" href="#">0</span>'
			.'</div>'

			.'</div>';

		$str .= '<br/><div id="markup"></div><br/>';

		$str .= '<div id="paginator_foot"></div>';
		return $str;
	}

}

?>