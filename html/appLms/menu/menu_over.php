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

if(!Docebo::user()->isAnonymous()) {
    YuiLib::load('base,menu');
    require_once(_lms_.'/lib/lib.middlearea.php');
    
   require_once('../widget/lms_block/lib.lms_block_menu.php');
   require_once(_lms_.'/lib/lib.course.php');
   $widget = new  Lms_BlockWidget_menu() ;

   //** GESTIONE AREA PROFILO UTENTE **
   require_once (_lib_ . '/lib.user_profile.php');
   $profile = new UserProfile(getLogUserId());
   $profile->init('profile', 'framework', 'index.php?r='._after_login_, 'ap');
   $profile_box  = $profile->homeUserProfile('normal', false, false);
   $photo = $profile->homePhotoProfile('normal', false, false);
   
   $credits = $widget->credits();
   $career = $widget->career();
   $subscribe_course = $widget->subscribe_course();
   $news = $widget->news();
   
   
    $ma = new Man_MiddleArea();

    $user_level = Docebo::user()->getUserLevelId();

    $query_menu = "
    SELECT mo.idModule, mo.module_name, mo.default_op, mo.mvc_path, mo.default_name, mo.token_associated, mo.module_info
    FROM ".$GLOBALS['prefix_lms']."_module AS mo
        JOIN ".$GLOBALS['prefix_lms']."_menucourse_under AS under
            ON ( mo.idModule = under.idModule)
    WHERE module_info IN ('all', 'user', 'public_admin')   and mo.idModule not in(7,34)
    ORDER BY module_info, under.sequence ";

                 
   // echo $query_menu;
   //  die();

    $menu = array();
    $re_menu_voice = sql_query($query_menu);
    while(list($id_m, $module_name, $def_op, $mvc_path, $default_name, $token, $m_info) = sql_fetch_row($re_menu_voice)) {

        
        if($ma->currentCanAccessObj('mo_'.$id_m) && checkPerm($token, true, $module_name,  true)) {


            // if e-learning tab disabled, show classroom courses
            if ($module_name ==='course' && !$ma->currentCanAccessObj('tb_elearning'))
                $mvc_path = 'lms/classroom/show';
            
            $menu[$m_info][$id_m] = array(
                'index.php?'.( $mvc_path ? 'r='.$mvc_path : 'modname='.$module_name.'&amp;op='.$def_op ).'&amp;sop=unregistercourse',
                Lang::t($default_name, 'menu_over'),
                false ,
                $id_m
            );
        }
    }
    if(isset($menu['all'])) $menu_i = count($menu['all'])-1;
    else $menu_i = -1;
    $setup_menu = '';
    // Menu for the public admin
    /*if(!empty($menu['user'])) {
        $menu['all'][] = array(
            '#',
            Lang::t('_MY_AREA', 'menu_over'),
            'user'
        );
        $menu_i++;
    }*/

    // Menu for messages
      /*
    if($ma->currentCanAccessObj('mo_47')) {
        require_once($GLOBALS['where_framework'].'/lib/lib.message.php');
        $menu['all'][] = array(
            'index.php?r=lms/catalog/show',
            Lang::t('_CATALOGUE', 'menu_over').( $unread_num ? '' : '' ),
            false
        );
        $menu_i++;
    }
     */
     
    // Customer help
    if ($ma->currentCanAccessObj('mo_help')) {

        $help_email = trim( Get::sett('customer_help_email', '') );
        $can_send_emails = !empty( $help_email ) ? true : false;
        $can_admin_settings = checkRole('/framework/admin/setting/view', true);

        $strTxtHelp = Lang::t('_CUSTOMER_HELP', 'customer_help')."";
        $strHelp = "<span class='glyphicon glyphicon-question-sign'></span>";
        
        if ($can_send_emails) {

            cout(Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true), 'scripts');
            cout(Util::get_js(Get::rel_path('lms').'/modules/customer_help/customer_help.js', true), 'scripts');

            cout('<script type="text/javascript">'.
                ' var CUSTOMER_HELP_AJAX_URL = "ajax.server.php?mn=customer_help&plf=lms&op=getdialog"; '.
                ' var ICON_LOADING = "'.Get::tmpl_path().'images/standard/loadbar.gif"; '.
                ' var LANG = new LanguageManager({'.
                '    _CONFIRM: "'.Lang::t('_CONFIRM').'",'.
                '    _UNDO: "'.Lang::t('_UNDO').'",'.
                '    _COURSE_NAME: "'.Lang::t('_COURSE_NAME', 'course').'",'.
                '    _VAL_COURSE_NAME: "'.(isset($GLOBALS['course_descriptor']) ? $GLOBALS['course_descriptor']->getValue('name') : "").'",'.
                '    _DLG_TITLE: "'.Lang::t('_CUSTOMER_HELP', 'customer_help').'",'.
                '    _LOADING: "'.Lang::t('_LOADING').'"'.
                '}); '
                .'</script>'
            , 'scripts');

            $menu['all'][] = array(
                '#inline',
                $strHelp,
                'modalbox'
            );
            $customer_help = ++$menu_i;
            $setup_menu .= " oMenuBar.getItem($customer_help).subscribe('click', CustomerHelpShowPopUp);";

        } else {

            if ($can_admin_settings) {
                $menu['all'][] = array(
                    '../appCore/index.php?r=adm/setting/show',
                    '<i>('.$strHelp.': '.Lang::t('_SET', 'standard').')</i>',
                    false
                );
            }

        }
    }

    
    
    
    
    
    
    // Menu for the public admin
    if($user_level == ADMIN_GROUP_PUBLICADMIN && !empty($menu['public_admin'])) {
        $menu['all'][] = array(
            '#',
            Lang::t('_PUBLIC_ADMIN_AREA', 'menu_over'),
            'public_admin'
        );
        $menu_i++;
    }

    // Link for the administration
    if($user_level == ADMIN_GROUP_GODADMIN || $user_level == ADMIN_GROUP_ADMIN ) {
        $menu['all'][] = array(
            Get::rel_path('adm'),
            Lang::t('_GO_TO_FRAMEWORK', 'menu_over'),
            false
        );
        $menu_i++;
    }

    
//** DEV: LR - creato un menu_over  responsive  attraverso bootstrap **
cout('
           
           <header class="header white-bg">

      <!-- Static navbar -->

      <nav> 

        <div class="row-fluid" id="lms_menu_container" >

        
          <div class="navbar-header" >

            <a class="navbar-brand" href="#"><img class="left_logo" width="120" src="'. Layout::path().'/images/company_logo.png" alt="logo di sinistra"/></a> 

            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                      
              <span  class="glyphicon glyphicon-align-justify"></span>
            </button>  

          </div>        
        
          <div id="navbar" class="navbar-collapse collapse " >   
            
                    
                ','menu_over');         
         
                
                    
         cout('
         

         
         
            <div>
         
            <ul class="nav navbar-nav"  >','menu_over');
         
                foreach ($menu['all'] as $row) {
                    
                    $active = "";
                    if(strrpos($row[0], $_GET['r'])>0 || strrpos($row[0], $_GET['modname'])>0) $active = " class='active'";
                    
                    if( isset($_GET['id_cat']) && strpos($row[0], "catalog")>0)  $active = " class='active'";
         
                         // ADMIN
                     if(strrpos($row[0], 'appCore')>0 ){
                        cout( '<li  ><a href="'.$row[0].'" title="'.$row[1].'" title="'.Lang::t('_GO_TO_FRAMEWORK', 'menu_over').'"><span class="glyphicon glyphicon-cog"></span></a></li> ','menu_over'); 
                     } else{
                          // HELP DESK
                         if(strrpos($row[1], 'sign')>0 ){
                                cout( '<li '.$active.'   ><a href="'.$row[0].'" class="'.$row[2].'" title="'.Lang::t('_CUSTOMER_HELP', 'customer_help').'"  >'.$row[1].'</a></li>','menu_over');
                         }else{
                            cout( '<li '.$active.'   ><a href="'.$row[0].'" class="'.$row[2].'" title="'.$row[1].'"  >'.$row[1].'</a></li>','menu_over');
                         }
                         
                         
                     }
                            
                        if($row[2] !== false) {

                                cout('<div id="submenu_'.$id_m.'" >'
                                    .'<div class="bd"><ul class="first-of-type">', 'menu_over');
                                while(list($id_m, $s_voice) = each($menu[ $row[2] ])) {

                                    cout(''
                                        .'<a  href="'.$s_voice[0].'"">'
                                        .''.$s_voice[1].''
                                        .'</a> &nbsp; '
                                        .'', 'menu_over');
                                }
                                cout('</div>'
                                    .'</div>', 'menu_over');
                            }             
      
                }  
               
               
               
               // CARRELLO SPESA 
              // cout( '<li  ><a href="" title="'.Lang::t("_CART", "cart").'"><span class="glyphicon glyphicon-shopping-cart"></span></a></li> ','menu_over'); 
              require_once(_lms_.'/lib/lib.cart.php');
              Learning_Cart::init();
              $num_item = Learning_Cart::cartItemCount();
              if($num_item>0){
                cout('<li><a href="index.php?r=cart/show" id="cart_action" title="'.Lang::t("_CART", "cart").'"><span  class="glyphicon glyphicon-shopping-cart"><sub id="cart_element" class="num_notify_bar">'.Learning_Cart::cartItemCount().'</sub></span></a></li>' ,'menu_over')  ;
              } 
               
               cout('
                                     <li>                                
                                    <div id="o-wrapper" class="o-wrapper">
                                            <button id="c-button--slide-right" class="c-button" >
                                            
                                             <a data-toggle="dropdown"  href="#" title="'.Lang::t('_PROFILE', 'menu_course').'">
                                            <table><tr><td>'. $photo.'  &nbsp;</td><td><span class="username"> '.Docebo::user()->getUserName().'</span></td></tr></table>                                                        
                                              </a>
                                            </button>
                                    </div><!-- /o-wrapper -->


                                    <nav id="c-menu--slide-right" class="c-menu c-menu--slide-right">
                                    
                                      <button class="c-menu__close">'.Lang::t('_HIDETREE', 'organization').'</button>
                                      
                                                                                 
                                      
                                      <ul class="c-menu__items">
                                          <li class="c-menu__item">
                                          
                                           <div class="col-md-12">
                                           <br>
                                                             <table width="10%" border="0">
                                                                <tr align="left">
                                                                    <td><span class="select-language">'. Layout::change_lang().'</span></td>
                                                                   <td align="center">                                                                                                                                                                                   
                                                                    </td>
                                                            </tr></table>
                                                            
                                                            <p align=right>
                                                            
                                                                    <a href="index.php?r=lms/profile/show" title="'.Lang::t('_PROFILE', 'profile').'">
                                                                     <span class="glyphicon glyphicon-pencil"></span>
                                                                    </a>
                                                                    &nbsp;
                                                                    <a title="'.Lang::t('_LOGOUT', 'standard').'" href="index.php?modname=login&amp;op=logout">
                                                                    
                                                                        <span class="glyphicon glyphicon-off"></span>
                                                                        </a>
                                                              
                                                            </p>

                                                            '.$profile_box.'                                               
                                                             <div >&nbsp;</div>   
                                                            '.$subscribe_course.'
                                                            '.$news.'
                                                            '.$credits.'
                                              
                                              </div>   
                                          
                                          <li>
                                      </ul>
                                      </nav><!-- /c-menu slide-right -->
                                 </ul>
                               
                                    </li>  
                                      
                <div id="c-mask" class="c-mask"></div><!-- /c-mask -->
                                                
                                        ','menu_over')   ; 
                                                
       
          cout('
                
                
          </div>','menu_over'); 
          cout('<!--/.nav-collapse -->
          
          
          
        </div><!--/.container-fluid -->    

        
      </nav>

          </header>
          <br><br><br><br>
      
','menu_over');        
    
        $idst = getLogUserId();
        $acl_man = Docebo::user()->getAclManager();
        $user_info = $acl_man->getUser($idst, false);
        $user_email = $user_info[ACL_INFO_EMAIL];
  
    cout('<!-- hidden inline form -->
            <div id="inline" >
                                
                <form id="contact" name="contact" action="#" method="post"  style="width: 470px;" role="form" style="display: block;">

                    <fieldset>

                              <!-- Form Name -->
                              <legend>'.Lang::t('_CUSTOMER_HELP', 'customer_help').'</legend>                
                
                      <input type="hidden" id="sendto" name="sendto" class="txt" value="'.Get::sett('customer_help_email').'" readonly>
                      <input type="hidden" id="authentic_request_newsletter" name="authentic_request" value="'.Util::getSignature().'" />
                      <input type="hidden" id="username" name="username" class="txt" value="'.Docebo::user()->getUserId().'" >
                      <input type="hidden" id="msg_ok" name="msg_ok" class="txt" value="'.Lang::t('_OPERATION_SUCCESSFUL', 'standard').'" >
                      
                      <input type="hidden" id="help_req_resolution" name="help_req_resolution"   >
                      <input type="hidden" id="help_req_flash_installed" name="help_req_flash_installed" >
           
                      
                 <table cellspacing=2 cellpaddin=2 width=98% border=0 > 
   

                 <tr>
                      <td width="27%"><label for="username">'.Lang::t('_USER', 'standard').'</label></td>
                      <td>
                      <div class="input-group">
                      <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
                      <input class="form-control" type="text" id="username" name="username" class="txt" value="'.Docebo::user()->getUserId().'" readonly>
                      </div>
                      </td>
                 </tr>   
                 
                 <tr>  
                        <td><label for="oggetto">'.Lang::t('_TITLE', 'menu').'</label></td>
                        <td>
                        <div class="input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-file"></span></span>
                        <input  class="form-control" type="oggetto" id="oggetto" name="oggetto" class="txt" placeholder="'.Lang::t('_CUSTOMER_HELP_SUBJ_PFX', 'configuration').'">
                        </div>
                        </td>
                </tr>
              
                <tr>
                      <td><label for="email">'.Lang::t('_EMAIL', 'menu').'</label> </td>
                      <td>
                      <div class="input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
                        <input class="form-control" type="email" id="email" name="email" class="txt" value="'.$user_email.'" placeholder="">
                      </div>
                      </td>
                </tr>    
              
                    <tr>
                  <td><label for="telefono">'.Lang::t('_PHONE', 'classroom').'</label></td>
                      <td>
                        <div class="input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-phone-alt"></span></span>
                        <input class="form-control" type="text" id="telefono" name="telefono" class="txt" placeholder="">
                      </div>
                      </td>
                    </tr>
                
                <tr>
                      <td><label for="msg">'.Lang::t('_TEXTOF', 'menu').'</label></td>
                      <td><textarea id="msg" name="msg" class="txtarea" placeholder="'.Lang::t('_WRITE_ASK_A_FRIEND', 'profile').'"></textarea></td>
                </tr>
                
                
                <tr>
                      <td><label for="copia">Invia una copia per conoscenza</label></td>
                      <td>   <input id="copia" name="copia" checked data-toggle="toggle" data-on="'.Lang::t('_GROUP_FIELD_NORMAL', 'admin_directory').'"  data-size="small" data-off="'.Lang::t('_NO', 'standard').'" data-onstyle="success" data-offstyle="danger" type="checkbox">
                   
                      </td>
                </tr>                
                
                 <tr>
                      <td><label for="priorita">'.Lang::t('_PRIORITY', 'message').'</label></td>
                      <td>
                       <input id="priorita" name="priorita" data-size="small" checked data-toggle="toggle" data-on="'.Lang::t('_NORMAL', 'message').'" data-off="'.Lang::t('_HIGH', 'message').'" data-onstyle="success" data-offstyle="danger" type="checkbox">

                    </td>
                 </tr>                  
                
                
                </table>
                
                   <br>
                
          
                      <table width=88% border=0 background="#ffcc99"> <tr><td align=center>
                        <button id="close">'.Lang::t('_CANCEL').'</button>   
                    </td><td align=center>
                       <button id="send">'.Lang::t('_CONFIRM').'</button>               
                    </td></tr>
                      </table>
                   
        </fieldset>
      </form>                    
                    
                </form>
</div>','menu_over')   ;

}    

     
?>


