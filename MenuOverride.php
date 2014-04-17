<?php
/*
Plugin Name: Menu Override
Plugin URI: http://phillipshipley.com/wordpress/menu-override
Description: Override page level menus with this plugin. On a page by page basis you can leave your navigation menu the default, choose different menus for each page, or have a page inherit from its parent. This plugin is particularly useful when you want to have section level navigation menus but your theme does not support it.
Version: 0.4.1
Author: fillup17
Author URI: http://phillipshipley.com/
License: GPL2
*/

/*

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('add_meta_boxes', array('MenuOverride','addMetabox'));
add_action('save_post', array('MenuOverride','saveMetabox'));
add_filter('wp_nav_menu_args', array('MenuOverride','filterMenu') );
class MenuOverride
{
    const formFieldPrefix = 'mo_menu_location_';
    
    public static function filterMenu($args = '')
    {
        global $post;
        // $post is global, re-setting this variable creates havoc.. we'll use our own
        $_post = $post;
        
        if($args['theme_location'] == ''){
            return $args;
        }
        
        // if the current page is the page_for_posts, use the page and not the last post
        if(is_home(get_option('page_for_posts'))){
            $_post = get_post(get_option('page_for_posts'));
        }
        
        $menuOverrideSelection = get_post_meta($_post->ID,'menuOverrideSelection',true);
        
        if(is_array($menuOverrideSelection) && in_array($args['theme_location'],array_keys($menuOverrideSelection))){
            $current = $menuOverrideSelection[$args['theme_location']];
            if($current == "PARENT"){
                $levels = 0;
                while($current == "PARENT" && $levels < 5){
                    $_post = get_post($_post->post_parent);
                    $menuOverrideSelection = get_post_meta($_post->ID,'menuOverrideSelection',true);
                    if(in_array($args['theme_location'],array_keys($menuOverrideSelection))){
                        $current = $menuOverrideSelection[$args['theme_location']];
                    }
                    $levels++;
                }
            }

            if($current == 'DEFAULT'){
                return $args;
            }
            
            //$args['theme_location'] = '';
            $args['menu'] = $current;
        }
        
        return $args;
    }
    
    public static function addMetabox()
    {
        add_meta_box('menuoverride-metabox',
                 'Menu Override',
                 array('MenuOverride','renderMetabox'),
                 'page',
                 'side',
                 'low'
                );
        add_meta_box('menuoverride-metabox',
                 'Menu Override',
                 array('MenuOverride','renderMetabox'),
                 'post',
                 'side',
                 'low'
                );
    }
    
    public static function renderMetabox()
    {
        global $post;
        $currentMenu = get_post_meta($post->ID,'menuOverrideSelection',true);
        $menus = self::getMenus();
        $locations = get_registered_nav_menus();
        
        wp_nonce_field('overridemenu_nonce', 'overridemenu_nonce' );
        ?>
        <strong>You may override any of the menus used on this page by selecting the location and the menu you wish to be displayed there:</strong><br />
        
        <?php
            foreach($locations as $location => $temp){
                $field = self::formFieldPrefix . preg_replace('/ /','___',$location);
        ?>
        <br /><br />Location: <i><?php echo $location; ?></i><br />
        <select name="<?php echo $field; ?>" id="menuLocation<?php echo $location; ?>">
            <option value="DEFAULT">Use Default Menu</option>
            <option value="PARENT" <?php if($currentMenu[$location] == 'PARENT'){ echo "selected='selected'"; }?>>Use Parent Page Menu</option>
            <?php
                foreach($menus as $menu){
            ?>
            <option value="<?php echo $menu->slug; ?>"
                    <?php if($menu->slug == $currentMenu[$location]){ echo " selected='selected' ";} ?>><?php echo esc_attr($menu->name);?></option>
            <?php
                }
            ?>
        </select>
        <?php
            }
    }
    
    public static function saveMetabox($post_id)
    {
        // Bail if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        // if our nonce isn't there, or we can't verify it, bail
        if( !isset( $_POST['overridemenu_nonce'] ) || !wp_verify_nonce( $_POST['overridemenu_nonce'], 'overridemenu_nonce' ) ) return;
        // if our current user can't edit this post, bail
        if( !current_user_can( 'edit_post' ) ) return;
        
        $menuOverrideSelection = array();
        foreach($_POST as $key => $value){
            if(preg_match('/'.self::formFieldPrefix.'(.*)/', $key, $match)){
                $locName = preg_replace('/___/', ' ', $match[1]);
                $menuOverrideSelection[$locName] = $value;
            }
        }

        update_post_meta($post_id,'menuOverrideSelection',$menuOverrideSelection);
    }
    
    public static function getMenus()
    {
        $terms = get_terms('nav_menu', array(
            'hide_empty' => true,
        ));
        if(is_wp_error($terms)){
            return false;
        } else {
            return $terms;
        }
    }
}
