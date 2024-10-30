<?php
/*
Plugin Name: Posts' Footer Manager
Plugin URI: https://intellywp.com/posts-footer-manager/
Description: Clean the mess after your content! Organize your post’s footer, insert what you want, order elements, create groups for specific categories.
Author: Data443
Author URI: https://data443.com/
Email: support@intellywp.com
Version: 2.0.5
*/
if(defined('IPFM_PLUGIN_NAME')) {
    function IPFM_FREE_admin_notices() {
        global $ipfm; ?>
        <div style="clear:both"></div>
        <div class="error iwp" style="padding:10px;">
            <?php $ipfm->Lang->P('PluginProAlreadyInstalled'); ?>
        </div>
        <div style="clear:both"></div>
    <?php }
    add_action('admin_notices', 'IPFM_FREE_admin_notices');
    return;
}
define('IPFM_PLUGIN_PREFIX', 'IPFM_');
define('IPFM_PLUGIN_FILE',__FILE__);
define('IPFM_PLUGIN_SLUG', 'intelly-posts-footer-manager');
define('IPFM_PLUGIN_NAME', 'Posts Footer Manager');
define('IPFM_PLUGIN_VERSION', '2.0.5');
define('IPFM_PLUGIN_DIR', dirname(__FILE__).'/');

define('IPFM_PLUGIN_URI', plugins_url('/', __FILE__));
define('IPFM_PLUGIN_ASSETS_URI', IPFM_PLUGIN_URI.'assets/');
define('IPFM_PLUGIN_IMAGES_URI', IPFM_PLUGIN_ASSETS_URI.'images/');
define('IPFM_PLUGIN_LANDING_URI', IPFM_PLUGIN_ASSETS_URI.'landing/');

//define('IPFM_LOGGER', FALSE);
define('IPFM_AUTOSAVE_LANG', TRUE);

define('IPFM_QUERY_POSTS_OF_TYPE', 1);
define('IPFM_QUERY_POST_TYPES', 2);
define('IPFM_QUERY_CATEGORIES', 3);
define('IPFM_QUERY_TAGS', 4);
define('IPFM_QUERY_TAXONOMY_TYPES', 5);
define('IPFM_QUERY_TAXONOMIES_OF_TYPE', 6);

define('IPFM_ENGINE_SEARCH_CATEGORIES_TAGS', 0);
define('IPFM_ENGINE_SEARCH_CATEGORIES', 1);
define('IPFM_ENGINE_SEARCH_TAGS', 2);

define('IPFM_INTELLYWP_SITE', 'https://intellywp.com/');
define('IPFM_INTELLYWP_ENDPOINT', IPFM_INTELLYWP_SITE.'wp-content/plugins/intellywp-manager/data.php');
define('IPFM_PAGE_FAQ', IPFM_INTELLYWP_SITE.IPFM_PLUGIN_SLUG);
define('IPFM_PAGE_PREMIUM', IPFM_INTELLYWP_SITE.IPFM_PLUGIN_SLUG);
define('IPFM_PAGE_HOME', admin_url().'options-general.php?page='.IPFM_PLUGIN_SLUG);

define('IPFM_TAB_PLUGINS', 'plugins');
define('IPFM_TAB_PLUGINS_URI', 'https://intellywp.com/plugins/');
define('IPFM_TAB_DOCS', 'docs');
define('IPFM_TAB_DOCS_URI', 'https://intellywp.com/docs/posts-footer-manager');
define('IPFM_TAB_SUPPORT', 'support');
define('IPFM_TAB_SUPPORT_URI', 'https://intellywp.com/contact/');
define('IPFM_TAB_PREMIUM_URI', 'https://intellywp.com/posts-footer-manager/');

define('IPFM_TAB_SETTINGS', 'settings');
define('IPFM_TAB_SETTINGS_URI', IPFM_PAGE_HOME.'&tab='.IPFM_TAB_SETTINGS);
define('IPFM_TAB_EDITOR', 'editor');
define('IPFM_TAB_EDITOR_URI', IPFM_PAGE_HOME.'&tab='.IPFM_TAB_EDITOR);
define('IPFM_TAB_MANAGER', 'manager');
define('IPFM_TAB_MANAGER_URI', IPFM_PAGE_HOME.'&tab='.IPFM_TAB_MANAGER);
define('IPFM_TAB_WHATS_NEW', 'whatsnew');
define('IPFM_TAB_WHATS_NEW_URI', IPFM_PAGE_HOME.'&tab='.IPFM_TAB_WHATS_NEW);
define('IPFM_TAB_HOW_IT_WORKS', 'howitworks');
define('IPFM_TAB_HOW_IT_WORKS_URI', IPFM_PAGE_HOME.'&tab='.IPFM_TAB_HOW_IT_WORKS);

define('IPFM_BLOG_URL', get_bloginfo('wpurl'));
define('IPFM_BLOG_EMAIL', get_bloginfo('admin_email'));

/*if (!function_exists('hex2bin')) {
    function hex2bin($str) {
        $result="";
        $len=strlen($str);
        for ($i=0; $i<$len; $i+=2) {
            $result.=pack("H*", substr($str, $i, 2));
        }
        return $result;
    }
}*/

include_once(dirname(__FILE__).'/autoload.php');
ipfm_include_php(dirname(__FILE__).'/includes/');

global $ipfm;
$ipfm=new IPFM_Singleton();
$ipfm->init();
