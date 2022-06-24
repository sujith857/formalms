<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

if (!defined('PEOPLEVIEW_TAB')) {
    define('DIRECTORY_TAB', 'DIRECTORY_TAB');
    define('PEOPLEVIEW_TAB', 'PEOPLEVIEW_TAB');
    define('GROUPVIEW_TAB', 'GROUPVIEW_TAB');
    define('ORGVIEW_TAB', 'ORGVIEW_TAB');
    define('DIRECTORY_ID', 'directory_id');
}

class UserSelector
{
    public $show_user_selector = true;
    public $show_group_selector = true;
    public $show_orgchart_selector = true;
    public $show_orgchart_simple_selector = false;
    public $show_fncrole_selector = true;

    public $requested_tab = false;
    public $group_filter = [];
    public $user_filter = [];
    public $learning_filter = 'none';
    public $selection = [];
    public $_extra_form = [];
    public $nFields = false;

    public $use_suspended = false;
    public $id_org = null;
    public $org_type = null;

    public function __construct()
    {
        $this->id_org = null;
    }

    public function setPageTitle($title)
    {
    }

    public function isParseDataAvailable($arrayState)
    {
        return isset($arrayState[DIRECTORY_ID]);
    }

    /**
     * Display the user/group/orgchart/fncrole selector.
     *
     * @param string $url           the url of the page, used for the form
     * @param string $title         the main title for the page (will be passed to a getTitleArea function
     * @param string $text          extra text to display
     * @param bool   $selector_mode if true the main div and page title will be drawed by the selector
     * @param string $id            the id for the form that will contain the selector
     */
    public function loadSelector($url, $title = false, $text = '', $selector_mode = true, $id = false)
    {
        $res = '';
        $id = (empty($id) ? 'main_selector' : $id);
        $us_util = new UserSelectorUtil();

        if ($selector_mode && $title != false) {
            $res .= getTitleArea($title);
            $res .= '<div class="std_block">';
        }
        $res .= Form::openForm($id . '_form', $url);

        if (is_array($this->_extra_form) && !empty($this->_extra_form)) {
            $res .= implode("\n", $this->_extra_form);
        }

        $res .= Util::widget('userselector', [
            'id' => $id,
            'id_org' => $this->id_org,
            'org_type' => $this->org_type,
            'show_user_selector' => $this->show_user_selector,
            'show_group_selector' => $this->show_group_selector,
            'show_orgchart_selector' => $this->show_orgchart_selector,
            'show_orgchart_simple_selector' => $this->show_orgchart_simple_selector,
            'show_fncrole_selector' => $this->show_fncrole_selector,
            'initial_selection' => $this->selection,
            'admin_filter' => true,
            'learning_filter' => $this->learning_filter,
            'use_suspended' => $this->use_suspended,
            'nFields' => $this->nFields !== false ? $this->nFields : 3,
        ], true);

        $res .= Form::openButtonSpace();
        $res .= Form::getButton('okselector', 'okselector', Lang::t('_SAVE', 'standard'));
        $res .= Form::getButton('cancelselector', 'cancelselector', Lang::t('_UNDO', 'standard'));
        $res .= Form::closeButtonSpace();

        $res .= Form::closeForm();
        if ($selector_mode) {
            $res .= '</div>';
        }

        cout($res, 'content');
    }

    /**
     * Resolve the selection generated by the selector.
     *
     * @param array  $arrayData   the array with the data for the selections, $_POST is the standard selections
     * @param string $selector_id the id of the main array with the selections in it
     *
     * @return array the idsts selected
     */
    public function getSelection($arrayData = [], $selector_id = false)
    {
        $selector_id = (empty($selector_id) ? 'main_selector' : $selector_id);
        $userselector_input_post = FormaLms\lib\Get::pReq('userselector_input', DOTY_MIXED, []);
        $userselector_input_get = FormaLms\lib\Get::gReq('userselector_input', DOTY_MIXED, []);
        $userselector_input = array_merge($userselector_input_post, $userselector_input_get);
        if (is_array($userselector_input) && isset($userselector_input[$selector_id])) {
            if (!empty($userselector_input[$selector_id])) {
                $this->selection = explode(',', $userselector_input[$selector_id]);
            } else {
                $this->selection = [];
            }
        }

        return $this->selection;
    }

    public function resetSelection($array_selection = null, $array_selection_alt = null)
    {
        $this->selection = $array_selection;
    }

    /**
     * Set filters for user data retriever.
     *
     * @param string $filter_type one of the following:
     *                            - "platform": retrieve only user of the platforms
     *                            given in $filter_arg array
     *                            - "group": retrieve only user members of the
     *                            groups given in $filter_arg array
     *                            - "exclude": exclude users with idst passed in
     *                            $filter_arg array
     * @param array  $filter_arg  an array of platforms or an array of groups or
     *                            an array of idst (see $filter_type)
     *
     * @return null
     * */
    public function setUserFilter($filter_type, $filter_arg)
    {
        switch ($filter_type) {
            case 'platform': $this->user_filter['platform'] = $filter_arg;
                break;
            case 'user': $this->user_filter['user'] = $filter_arg;
                break;
            case 'group': $this->user_filter['group'] = $filter_arg;
                break;
            case 'exclude': $this->user_filter['exclude'] = $filter_arg;
                break;
        }

        return;
    }

    public function setGroupFilter($filter_type, $filter_arg)
    {
        switch ($filter_type) {
            case 'platform': $this->group_filter['platform'] = $filter_arg; break;
            case 'group': $this->group_filter['group'] = $filter_arg; break;
            case 'path': $this->group_filter['path'] = $filter_arg; break;
        }

        return;
    }

    public function addFormInfo($string)
    {
        $this->_extra_form[] = $string;
    }

    public function resetFormInfo()
    {
        $this->_extra_form = [];
    }

    public function setNFields($nFields)
    {
        $this->nFields = $nFields;
    }
}

class UserSelectorUtil
{
    protected $db;

    public function __construct()
    {
        $this->db = DbConn::getInstance();
    }

    /**
     * Returns an array with the data ready to be used as
     * 'initial_selection' parameter in the userselector widget.
     *
     * @param array $array
     *
     * @return array multidimensional array with data organized by type
     */
    public function getInitialSelFromIdst($array)
    {
        $res = [
            'user' => [],
            'group' => [],
            'orgchart' => [],
            'fncrole' => [],
        ];

        if (empty($array)) {
            return $res;
        }

        $qtxt = "SELECT idst,'group' as item_type,groupid as itemid, hidden  FROM core_group
			WHERE idst IN (" . implode(',', $array) . ")
			UNION
			SELECT idst,'user' as item_type,userid as itemid, 'false' as hidden FROM core_user
			WHERE idst IN (" . implode(',', $array) . ')';
        $q = $this->db->query($qtxt);

        while ($row = $this->db->fetch_assoc($q)) {
            $item_id = $row['itemid'];
            $type = $row['item_type'];

            if ($type == 'user') {
                $res['user'][] = $row['idst'];
            } elseif ($type == 'group') {
                $match = [];
                if (preg_match('/^\\/oc([d])*_(\d)/', $item_id, $match) && $row['hidden'] == 'true') {
                    // org chart
                    $res['orgchart'][] = $row['idst'];
                } else {
                    // normal group
                    if (strpos($item_id, '/fncroles/') === 0) {
                        $res['fncrole'][] = $row['idst'];
                    } else {
                        $res['group'][] = $row['idst'];
                    }
                }
            }
        }

        return $res;
    }

    /**
     * Returns an array with all the idst of the given orgchart items
     * (groupid names).
     *
     * @param string $oc_list comma separed string
     *
     * @return array
     */
    public function getOrgChartIdst($oc_list)
    {
        $res = [];
        $oc_id_arr = [];
        $oc_arr = explode(',', $oc_list);

        foreach ($oc_arr as $oc) {
            if (substr($oc, -1) == 'd') {
                $oc_id_arr[] = '/ocd_' . substr($oc, 0, -1);
            } else {
                $oc_id_arr[] = '/oc_' . $oc;
            }
        }

        $acl_man = Docebo::user()->getACLManager();
        $res = $acl_man->getArrGroupST($oc_id_arr);

        return $res;
    }

    /**
     * @param <type> $old_sel
     * @param <type> $new_sel
     *
     * @return array
     */
    public function getSelectionDiff($old_sel, $new_sel)
    {
        $res = [
            'new' => [],
            'eql' => [],
            'rem' => [],
        ];

        return $res;
    }
}
