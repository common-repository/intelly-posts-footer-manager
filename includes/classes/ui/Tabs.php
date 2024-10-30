<?php
//from Settings_API_Tabs_Demo_Plugin
class IPFM_Tabs {
    private $tabs=array();

    function init() {
        global $ipfm;
        add_filter('wp_enqueue_scripts', array(&$this, 'siteEnqueueScripts'));
        if($ipfm->Utils->isAdminUser()) {
            add_action('admin_menu', array(&$this, 'attachMenu'));
            add_filter('plugin_action_links', array(&$this, 'pluginActions'), 10, 2);
            if($ipfm->Utils->isPluginPage()) {
                add_action('admin_enqueue_scripts', array(&$this, 'adminEnqueueScripts'), 9999);
            }
        }
    }

    function attachMenu() {
        global $ipfm;
        if($ipfm->Utils->isAdminUser()) {
            add_submenu_page('options-general.php'
                , IPFM_PLUGIN_NAME, IPFM_PLUGIN_NAME
                , 'manage_options', IPFM_PLUGIN_SLUG, array(&$this, 'showTabPage'));
        }
    }
    function pluginActions($links, $file) {
        global $ipfm;
        if($file==IPFM_PLUGIN_SLUG.'/index.php'){
            $settings=array();
            $settings[] = "<a href='".IPFM_TAB_MANAGER_URI."'>Settings</a>";
            $settings[] = "<a href='".IPFM_TAB_PREMIUM_URI."'>PREMIUM</a>";
            $links=array_merge($settings, $links);
        }
        return $links;
    }
    function siteEnqueueScripts() {
        wp_enqueue_script('jquery');

        $this->wpEnqueueScript('assets/js/library.js');
    }
    function adminEnqueueScripts() {
        global $ipfm;
        $ipfm->Utils->dequeueScripts('select2|woocommerce|page-expiration-robot');
        $ipfm->Utils->dequeueStyles('select2|woocommerce|page-expiration-robot');

        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');

        //wp_enqueue_script('jquery-ui-autocomplete');
        //wp_enqueue_script('suggest');

        wp_enqueue_script('jQuery');
        wp_enqueue_script('jquery-ui-sortable');

        $uri='//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css';
        wp_enqueue_style('font-awesome', $uri);

        $this->wpEnqueueStyle('assets/css/theme.css');
        $this->wpEnqueueStyle('assets/css/admin-forms.css');
        $this->wpEnqueueStyle('assets/css/all-themes.css');
        //$this->wpEnqueueStyle('assets/css/style.css');
        $this->wpEnqueueScript('assets/deps/starrr/starrr.js');
        //$this->wpEnqueueScript('assets/deps/qtip/jquery.qtip.min.js');

        $this->wpEnqueueStyle('assets/deps/select2/css/core.css');
        $this->wpEnqueueScript('assets/deps/select2/select2.min.js');

        $this->wpEnqueueScript('assets/deps/qtip/jquery.qtip.min.js');
        $this->wpEnqueueStyle('assets/deps/magnific/magnific-popup.css');
        $this->wpEnqueueScript('assets/deps/magnific/jquery.magnific-popup.js');

        $this->wpEnqueueScript('assets/deps/moment/moment.js');

        $this->wpEnqueueStyle('assets/deps/datepicker/css/bootstrap-datetimepicker.css');
        $this->wpEnqueueScript('assets/deps/datepicker/js/bootstrap-datetimepicker.js');

        $this->wpEnqueueStyle('assets/deps/colorpicker/css/bootstrap-colorpicker.min.css');
        $this->wpEnqueueScript('assets/deps/colorpicker/js/bootstrap-colorpicker.min.js');

        $this->wpEnqueueScript('assets/js/utility.js');
        $this->wpEnqueueScript('assets/js/library.js');
        $this->wpEnqueueScript('assets/js/plugin.js');
    }
    function wpEnqueueStyle($uri, $name='') {
        if($name=='') {
            $name=explode('/', $uri);
            $name=$name[count($name)-1];
            $dot=strrpos($name, '.');
            if($dot!==FALSE) {
                $name=substr($name, 0, $dot);
            }
            $name=IPFM_PLUGIN_PREFIX.'_'.$name;
        }

        $v='?v='.IPFM_PLUGIN_VERSION;
        wp_enqueue_style($name, IPFM_PLUGIN_URI.$uri.$v);
    }
    function wpEnqueueScript($uri, $name='', $version=FALSE) {
        if($name=='') {
            $name=explode('/', $uri);
            $name=$name[count($name)-1];
            $dot=strrpos($name, '.');
            if($dot!==FALSE) {
                $name=substr($name, 0, $dot);
            }
            $name=IPFM_PLUGIN_PREFIX.'_'.$name;
        }

        $v='?v='.IPFM_PLUGIN_VERSION;
        $deps=array();
        wp_enqueue_script($name, IPFM_PLUGIN_URI.$uri.$v, $deps, $version, FALSE);
    }

    function showTabPage() {
        global $ipfm;

        $page=$ipfm->Utils->qs('page');
        if($ipfm->Utils->startsWith($page, IPFM_PLUGIN_SLUG) && $page!=IPFM_PLUGIN_SLUG) {
            $_POST['page']=IPFM_PLUGIN_SLUG;
            $_GET['page']=IPFM_PLUGIN_SLUG;
            $tab=substr($page, strlen(IPFM_PLUGIN_SLUG)+1);
            $_POST['tab']=$tab;
            $_GET['tab']=$tab;
        }

        $id=$ipfm->Utils->iqs('id', 0);
        $defaultTab=IPFM_TAB_MANAGER;
        if($ipfm->Options->isShowWhatsNew()) {
            $tab=IPFM_TAB_WHATS_NEW;
            $defaultTab=$tab;
            $this->tabs[IPFM_TAB_WHATS_NEW]=$ipfm->Lang->L('What\'s New');
            //$this->tabs[TCM_TAB_MANAGER]=$tcm->Lang->L('Start using the plugin!');
        } else {
            $tab=$ipfm->Utils->qs('tab', $defaultTab);
            $uri='';
            switch ($tab) {
                case IPFM_TAB_DOCS:
                    $uri=IPFM_TAB_DOCS_URI;
                    break;
                case IPFM_TAB_PLUGINS:
                    $uri=IPFM_TAB_PLUGINS_URI;
                    break;
                case IPFM_TAB_SUPPORT:
                    $uri=IPFM_TAB_SUPPORT_URI;
                    break;
            }
            if($uri!='') {
                $ipfm->Utils->redirect($uri);
            }

			if($tab==IPFM_TAB_DOCS) {
				$ipfm->Utils->redirect(IPFM_TAB_DOCS);
			}

            $class=$ipfm->Options->getFooterClass();
            $this->tabs[IPFM_TAB_EDITOR]=$ipfm->Lang->L($id>0 && $tab==IPFM_TAB_EDITOR ? 'Edit'.$class : 'New'.$class);
            $this->tabs[IPFM_TAB_MANAGER]=$ipfm->Lang->L('Manager'.$class);
            $this->tabs[IPFM_TAB_SETTINGS] = 'Settings';
            $this->tabs[IPFM_TAB_HOW_IT_WORKS]=$ipfm->Lang->L('HowItWorks');
            $this->tabs[IPFM_TAB_DOCS] = 'FAQ & Docs';
        }

        ?>
        <div class="wrap" style="margin:5px;">
            <?php
            $this->showTabs($defaultTab);
            $header='';
            switch ($tab) {
                case IPFM_TAB_EDITOR:
                    $header=($id>0 ? 'Edit' : 'Add');
                    break;
                case IPFM_TAB_MANAGER:
                    $header='Manager';
                    break;
                case IPFM_TAB_SETTINGS:
                    $header='Settings';
                    break;
                case IPFM_TAB_HOW_IT_WORKS:
                    $header='HowItWorks';
                    break;
                case IPFM_TAB_WHATS_NEW:
                    $header='';
                    break;
            }
            if ($ipfm->Lang->H($header . 'Title')) { ?>
                <h2><?php $ipfm->Lang->P($header . 'Title', IPFM_PLUGIN_VERSION) ?></h2>
                <?php if ($ipfm->Lang->H($header . 'Subtitle')) { ?>
                    <div><?php $ipfm->Lang->P($header . 'Subtitle') ?></div>
                <?php } ?>
                <br />
                <div style="clear:both;"></div>
            <?php }
            if ($tab != IPFM_TAB_WHATS_NEW) {
                ipfm_ui_first_time();
            } ?>
            <div style="float:left; margin:5px;">
                <div id="ipfm-page" style="float:left; margin-right:20px; width:750px">
                    <?php
                    switch ($tab) {
                        case IPFM_TAB_WHATS_NEW:
                            ipfm_ui_whats_new();
                            break;
                        case IPFM_TAB_EDITOR:
                            ipfm_ui_editor();
                            break;
                        case IPFM_TAB_MANAGER:
                            ipfm_ui_manager();
                            break;
                        case IPFM_TAB_SETTINGS:
                            ipfm_ui_settings();
                            break;
                        case IPFM_TAB_HOW_IT_WORKS:
                            ipfm_ui_how_it_works();
                            break;
                    } ?>
                </div>
                <?php
                if ($ipfm->Options->isShowWhatsNew()) {
                    $ipfm->Options->setShowWhatsNew(FALSE);
                }
                if ($tab != IPFM_TAB_WHATS_NEW) { ?>
                    <div id="ipfm-sidebar" style="float:left; max-width:250px;">
                        <?php
                        $count = $this->getPluginsCount();
                        $plugins = array();
                        while (count($plugins) < 2) {
                            $id = rand(1, $count);
                            if (!isset($plugins[$id])) {
                                $plugins[$id] = $id;
                            }
                        }
                        $this->drawContactUsWidget();
                        foreach ($plugins as $id) {
                            $this->drawPluginWidget($id);
                        } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php }

    function showTabs($defaultTab) {
        global $ipfm;
        $tab=$ipfm->Check->of('tab', $defaultTab);
        if($tab==IPFM_TAB_DOCS) {
            $ipfm->Utils->redirect(IPFM_TAB_DOCS_URI);
        }
        ?>
        <h2 class="nav-tab-wrapper" style="float:left; width:97%;">
            <?php
            foreach ($this->tabs as $k=>$v) {
                $active = ($tab==$k ? 'nav-tab-active' : '');
                $target='_self';

                $styles=array();
                $styles[]='float:left';
                $styles[]='margin-left:10px';
                if($k==IPFM_TAB_DOCS) {
                    $target='_blank';
                    $styles[]='background-color:#F2E49B';
                }
                if($k==IPFM_TAB_HOW_IT_WORKS) {
                    $styles[]='background-color: #F8F5A6';
                }
                $styles=implode(';', $styles);
                ?>
                <a target="<?php echo $target ?>"  style="<?php echo $styles?>" class="nav-tab <?php echo $active?>" href="?page=<?php echo IPFM_PLUGIN_SLUG?>&tab=<?php echo $k?>"><?php echo $v?></a>
            <?php
            }
            ?>
            <style>
                .starrr {display:inline-block}
                .starrr i{font-size:16px;padding:0 1px;cursor:pointer;color:#2ea2cc;}
            </style>
            <div style="float:right; display:none;" id="rate-box">
                <span style="font-weight:700; font-size:13px; color:#555;">Rate us</span>
                <div id="ipfm-rate" class="starrr" data-connected-input="ipfm-rate-rank"></div>
                <input type="hidden" id="ipfm-rate-rank" name="ipfm-rate-rank" value="5" />
                <?php  $ipfm->Utils->twitter('data443risk') ?>
            </div>

            <script>
                jQuery(function() {
                    jQuery(".starrr").starrr();
                    jQuery('#ipfm-rate').on('starrr:change', function(e, value){
                        var url='https://wordpress.org/support/view/plugin-reviews/intelly-posts-footer-manager?rate=5#postform';
                        window.open(url);
                    });
                    jQuery('#rate-box').show();
                });
            </script>
        </h2>
        <div style="clear:both;"></div>
    <?php }

    function getPluginsCount() {
        global $ipfm;
        $index=1;
        while($ipfm->Lang->H('Plugin'.$index.'.Name')) {
            $index++;
        }
        return $index-1;
    }
    function drawPluginWidget($id) {
        global $ipfm;
        ?>
        <div class="ipfm-plugin-widget">
            <b><?php $ipfm->Lang->P('Plugin'.$id.'.Name') ?></b>
            <br>
            <i><?php $ipfm->Lang->P('Plugin'.$id.'.Subtitle') ?></i>
            <br>
            <ul style="list-style: circle;">
                <?php
                $index=1;
                while($ipfm->Lang->H('Plugin'.$id.'.Feature'.$index)) { ?>
                    <li><?php $ipfm->Lang->P('Plugin'.$id.'.Feature'.$index) ?></li>
                    <?php $index++;
                } ?>
            </ul>
            <a style="float:right;" class="button-primary" href="<?php $ipfm->Lang->P('Plugin'.$id.'.Permalink') ?>" target="_blank">
                <?php $ipfm->Lang->P('PluginCTA')?>
            </a>
            <div style="clear:both"></div>
        </div>
        <br>
    <?php }
    function drawContactUsWidget() {
        global $ipfm;
        ?>
        <b><?php $ipfm->Lang->P('Sidebar.Title') ?></b>
        <ul style="list-style: circle;">
            <?php
            $index=1;
            while($ipfm->Lang->H('Sidebar'.$index.'.Name')) { ?>
                <li>
                    <a href="<?php $ipfm->Lang->P('Sidebar'.$index.'.Url')?>" target="_blank">
                        <?php $ipfm->Lang->P('Sidebar'.$index.'.Name')?>
                    </a>
                </li>
                <?php $index++;
            } ?>
        </ul>
    <?php }
	
}