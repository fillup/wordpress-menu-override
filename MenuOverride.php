<?php
/*
Plugin Name: Menu Override
Plugin URI: http://phillipshipley.com/wordpress/menu-override
Description: Override page level menus with this plugin. On a page by page basis you can leave your navigation menu the default, choose different menus for each page, or have a page inherit from its parent. This plugin is particularly useful when you want to have section level navigation menus but your theme does not support it.
Version: 0.1
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
    public static function filterMenu($args = '')
    {
        global $post;
        $current = get_post_meta($post->ID,'selectedMenu',true);
        if($current == "PARENT"){
            $levels = 0;
            while($current == "PARENT" && $levels < 5){
                $post = get_post($post->post_parent);
                $current = get_post_meta($post->ID,'selectedMenu',true);
                $levels++;
            }
        }
        
        if($current == 'DEFAULT'){
            return $args;
        }
        
        $args['theme_location'] = '';
        $args['menu'] = $current;
        
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
    }
    
    public static function renderMetabox()
    {
        global $post;
        $current = get_post_meta($post->ID,'selectedMenu',true);
        $menus = self::getMenus();
        wp_nonce_field('overridemenu_nonce', 'overridemenu_nonce' );
        ?>
        <strong>You may override the menu used on this page by changing this dropdown:</strong><br />
        <select name="selectedMenu" id="selectedMenu">
            <option value="DEFAULT">Use Default Menu</option>
            <option value="PARENT" <?php if($current == 'PARENT'){ echo "selected='selected'"; }?>>Use Parent Page Menu</option>
            <?php
                foreach($menus as $menu){
            ?>
            <option value="<?php echo $menu->slug; ?>"
                    <?php if($menu->slug == $current){ echo " selected='selected' ";} ?>><?php echo esc_attr($menu->name);?></option>
            <?php
                }
            ?>
        </select>
        <?php
    }
    
    public static function saveMetabox($post_id)
    {
        // Bail if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        // if our nonce isn't there, or we can't verify it, bail
        if( !isset( $_POST['overridemenu_nonce'] ) || !wp_verify_nonce( $_POST['overridemenu_nonce'], 'overridemenu_nonce' ) ) return;
        // if our current user can't edit this post, bail
        if( !current_user_can( 'edit_post' ) ) return;

        update_post_meta($post_id,'selectedMenu',$_POST['selectedMenu']);
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