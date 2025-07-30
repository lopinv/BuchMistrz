<?php
/*
Plugin Name: WordPress优化
Plugin URI: https://www.lopins.cn/
Description: 包含核心优化、登录界面美化、图片处理、内容优化、管理后台增强等功能。
Version: 1.0
Author: lopins
Author URI: https://www.lopins.cn/
License: WTFPL
Tips: 在 /wp-content/mu-plugins 目录中的文件会被自动执行。
*/

// 防止直接访问文件，增加安全性
if ( ! defined( 'ABSPATH' ) ) { exit; }
// date_default_timezone_set('Europe/Warsaw');
// date_default_timezone_set('Asia/Shanghai');
global $pagenow;
/* *********************************************** 核心优化 *********************************************** */
// 关闭核心更新
add_filter('automatic_updater_disabled', '__return_true'); // 彻底关闭自动更新
remove_action('init', 'wp_schedule_update_checks'); // 关闭更新检查定时作业
wp_clear_scheduled_hook('wp_version_check'); //  移除已有的版本检查定时作业
wp_clear_scheduled_hook('wp_maybe_auto_update'); // 移除已有的自动更新定时作业
// 移除后台内核更新检查
remove_action('admin_init', '_maybe_update_core');
add_filter('pre_site_transient_update_core', function ($a) {return null;});
// 关闭主题更新
wp_clear_scheduled_hook('wp_update_themes');
add_filter('auto_update_theme', '__return_false');
remove_action('load-themes.php', 'wp_update_themes');
remove_action('load-update.php', 'wp_update_themes');
remove_action('load-update-core.php', 'wp_update_themes');
remove_action('admin_init', '_maybe_update_themes');
add_filter('pre_set_site_transient_update_themes', function ($a) {return null;});
// 关闭插件更新
wp_clear_scheduled_hook('wp_update_plugins');
add_filter('auto_update_plugin', '__return_false');
add_filter('pre_site_transient_update_plugins', function ($a) {return null;});
remove_action('load-plugins.php', 'wp_update_plugins');
remove_action('load-update.php', 'wp_update_plugins');
remove_action('load-update-core.php', 'wp_update_plugins');
remove_action('admin_init', '_maybe_update_plugins');
// 禁用版本检测和更新
// add_filter('translations_api', '__return_true'); // 禁用自动翻译
add_filter('wp_check_php_version', '__return_true'); // 禁用PHP版本检查
remove_action('admin_init', 'wp_check_php_version');  // 禁用PHP版本检查
add_filter('wp_is_php_version_acceptable', function ($is_acceptable, $required_version) {return true;}, 10, 2); // 禁用PHP版本检查
add_filter('wp_check_browser_version', '__return_true'); //禁用浏览器版本检查
remove_action('admin_head', 'wp_check_browser_version'); //禁用浏览器版本检查
add_filter('current_screen', '__return_true'); //禁用与当前屏幕相关的某些插件或主题的功能检查

add_filter('pre_option_link_manager_enabled', '__return_true'); // 友情链接扩展
add_filter('show_admin_bar', '__return_false'); //禁用网站前台管理工具栏
// 禁用登陆界面后台语言选项并强制设置登录页面语言为简体中文
add_filter('login_display_language_dropdown', '__return_false'); 
add_filter('locale', function ($locale) {
    if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {return 'zh_CN'; }
    return $locale;
});

add_filter('run_wptexturize', '__return_false'); //禁止半角符号自动变全角
// 移除内容中自动添加的P标签
remove_filter ('the_excerpt' , 'wpautop');//移除摘要自动p标签
// remove_filter ('the_content' , 'wpautop');//移除文章自动p标签
// remove_filter('comment_text', 'wpautop', 30);//取消评论自动p标签
// 禁止自动把'WordPress'之类的变成'Wordpress'
remove_filter('the_content', 'capital_P_dangit', 11);
remove_filter('the_title', 'capital_P_dangit', 11);
remove_filter('wp_title', 'capital_P_dangit', 11);
remove_filter('document_title', 'capital_P_dangit', 11);
remove_filter('comment_text', 'capital_P_dangit', 31);
remove_filter('widget_text_content', 'capital_P_dangit', 11);
// 禁用古腾堡编辑器
add_filter('use_block_editor_for_post', '__return_false');
add_filter('gutenberg_use_widgets_block_editor', '__return_false');
add_filter('use_widgets_block_editor', '__return_false');
add_filter('use_block_editor_for_post_type', function ( $can_edit, $post_type ) {
    if ('product' === $post_type) {return false;}
    return $can_edit;
}, 10, 2 );
// 关闭自动保存和历史修订版本选项
add_filter('wp_revisions_to_keep', '__return_false');
add_action('admin_print_scripts', function () {wp_deregister_script('autosave');});

// 在文章编辑页面的[添加媒体]只显示用户自己上传的文件
add_action('pre_get_posts', function ($wp_query_obj) {
    global $current_user;
    if (!is_a($current_user, 'WP_User')) return;
    if ('admin-ajax.php' != $GLOBALS['pagenow'] || $_REQUEST['action'] != 'query-attachments') return;
    if (!current_user_can('manage_options') && !current_user_can('manage_media_library')) $wp_query_obj->set('author', $current_user->ID);
    return;
});
// 在[媒体库]只显示用户上传的文件
add_filter('parse_query', function ($wp_query) {
    if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/upload.php') !== false) {
        if (!current_user_can('manage_options') && !current_user_can('manage_media_library')) {
            global $current_user;
            $wp_query->set('author', $current_user->id);
        }
    }
});
// 图片处理
add_filter('intermediate_image_sizes_advanced', function () {return [];}); // 禁止生成图像尺寸
add_filter('big_image_size_threshold', '__return_false'); // 禁止缩放图片尺寸
// 禁止生成其它图像尺寸
add_action('init', function () {    
    // 禁止通过set_post_thumbnail_size()函数生成的图片尺寸
    remove_image_size('post-thumbnail'); 
    // 禁止添加其它图像尺寸
    remove_image_size('another-size');
});
add_filter('max_srcset_image_width', function (){return 1;}); //禁用响应式图片
// 去掉图片的宽高属性
add_filter('post_thumbnail_html', function ($html) {
    $html = preg_replace('/width="(\d*)"\s+height="(\d*)"\s+class=\"[^\"]*\"/', "", $html);
    $html = preg_replace('/  /', "", $html);
    return $html;
}, 10);
add_filter('image_send_to_editor', function ($html) {
    $html = preg_replace('/width="(\d*)"\s+height="(\d*)"\s+class=\"[^\"]*\"/', "", $html);
    $html = preg_replace('/  /', "", $html);
    return $html;
}, 10);
// 图片上传自动MD5重命名
add_filter('wp_handle_upload_prefilter', function ($file){
    $info = pathinfo($file['name']);
    $file['name'] = md5($file['name']).'.' . $info['extension'];
    return $file;
});
// 删除文章时删除图片附件
add_action('before_delete_post', function ($post_ID) {
    global $wpdb;
    //删除特色图片
    $thumbnails = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id' AND post_id = $post_ID");
    foreach ($thumbnails as $thumbnail) {
        wp_delete_attachment($thumbnail->meta_value, true);
    }
    //删除图片附件
    $attachments = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_parent = $post_ID AND post_type = 'attachment'");
    foreach ($attachments as $attachment) {
        wp_delete_attachment($attachment->ID, true);
    }
    $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id' AND post_id = $post_ID");
});
// 去掉图片外围P标签
add_filter('the_content', function($args) {
    return preg_replace('/<p>*?(<a .*?><img.*?><\\/a>|<img.*?>)*?<\\/p>/s', '$1', $args);
});
/*
// 前端图片加载优化
add_filter('the_content', function ($content) {
    // 确保只在前端并且不是管理界面时运行
    if ( ! is_admin() ) {
        $content = preg_replace_callback( '/(<\s*img[^>]+?src\s*=\s*[\'"])(.*?)([\'"][^>]*?>)/i', function( $matches ) {
            // 如果图片标签中已经有 loading 属性，则不重复添加
            if ( strpos( $matches[0], 'loading=' ) !== false ) {
                return $matches[0];
            }
            // 在 img 标签中添加 loading="lazy" 属性
            return $matches[1] . $matches[2] . ' loading="lazy"' . $matches[3];
        }, $content );
    }
    return $content;
}, 99 );
*/

// 启用特色图功能
add_theme_support('post-thumbnails');
// 使用第一张图作为特色图
add_action('the_post', 'auto_set_featured');
add_action('save_post', 'auto_set_featured');
add_action('draft_to_publish', 'auto_set_featured');
add_action('new_to_publish', 'auto_set_featured');
add_action('pending_to_publish', 'auto_set_featured');
// 自动设置第一张图作为特色图
function auto_set_featured(){
    global $post;
    if(isset($post)){
        $post_has_thumb = has_post_thumbnail($post->ID);
        if(!$post_has_thumb)  {
            $attached_image = get_children("post_parent=".$post->ID."&post_type=attachment&post_mime_type=image&numberposts=1");
            if($attached_image){
                foreach($attached_image as $attachment_id => $attachment){
                    set_post_thumbnail($post->ID, $attachment_id);
                }
            }
        }
    }
}

// 移除链接中的 category 前缀
add_filter('user_trailingslashit', function ($string, $type)  {
    if ( $type != 'single' && $type == 'category' && ( strpos( $string, 'category' ) !== false ) ){ 
        $url_without_category = str_replace( "/category/", "/", $string );
        return trailingslashit( $url_without_category ); 
    }
    return $string;  
}, 100, 2);

// 禁止Ping自己的博客
add_action('pre_ping', function ( &$links ) {
    $home = get_option( 'home' );
    foreach ( $links as $l => $link ){
        if ( 0 === strpos( $link, $home ) ){
            unset($links[$l]);
        }
    }
}); 

// 删除无用小工具
add_action('widgets_init',function (){
    unregister_widget('WP_Widget_RSS');//RSS订阅
    unregister_widget('WP_Widget_Pages');//WordPress页面
    unregister_widget('WP_Widget_Calendar');//文章日程表
    unregister_widget('WP_Widget_Archives');//文章存档
    unregister_widget('WP_Widget_Links');//链接表
    unregister_widget('WP_Widget_Meta');//登入/登出，管理，Feed 和 WordPress 链接
    unregister_widget('WP_Widget_Search');//博客的搜索框
    unregister_widget('WP_Widget_Text');//任意的HTML文本
    unregister_widget('WP_Widget_Categories');//分类目录
    unregister_widget('WP_Widget_Recent_Posts');//近期文章
    unregister_widget('WP_Widget_Recent_Comments');//近期评论
    unregister_widget('WP_Widget_Tag_Cloud');//标签云
    unregister_widget('WP_Nav_Menu_Widget');//自定义菜单
    unregister_widget('Most_Viewed_Widget');//postviews挂件
});

// 移除不必要的存档页面
add_action('template_redirect', function () {
    global $wp_query, $post; 
    // 移除作者和日期页
    if (is_date() || is_author()) {
        $wp_query->set_404();
        status_header(404);
        get_template_part(404);
        exit;
    }
    // 禁用媒体附件页面
    if (is_attachment() && isset($post->post_parent) && is_numeric($post->post_parent) && ($post->post_parent != 0)) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        get_template_part(404);
        exit;
    } 
    //修改搜索结果的链接,只有一个搜索结果页时，自动跳转到内容页
    /*
    if (is_search()) {
        if ($wp_query->post_count == 1) {
            wp_redirect(get_permalink( $wp_query->posts['0']->ID));
        }
        if (!empty($_GET['s'])) {
            wp_redirect(home_url("/".get_option('site_search', 'search')."/").urlencode(get_query_var('s')));
            exit();
        }
    }
    */
});

// 添加自定义搜索规则
add_action('init', function () {
    $search_base = 'search'; // get_option('site_search', 'search'); // 使用您选项中的值
    if ($search_base !== 'search') { // 只为非默认的 'search' 添加规则
        add_rewrite_rule(
            '^' . $search_base . '/(.+?)/?$', // 例如 ^res/(.+?)/?$
            'index.php?s=$matches[1]',
            'top'
        );
    }
});

// 仪表盘“近期评论”显示完整评论内容和格式
add_filter('comment_excerpt', function ($excerpt) {
    global $comment;
    if (!is_admin()) return $excerpt;
    $content = wpautop($comment->comment_content);
    $content = substr($content, 3, -5); // 移除第一个 <p> 和最后一个 </p>
    $content = str_replace('<p>', '<p style="display:block; margin:1em 0">', $content);
    return $content;
});

// 后台文章列表根据文章状态添加不同背景色
add_action('admin_footer',function (){
    echo '<style>
            .status-draft{background: #FCE3F2 !important;/*草稿*/}
            .status-pending{background: #87C5D6 !important;/*待审核*/}
            .status-publish{/* 已发布，使用默认背景色，你也可以自己添加颜色 */}
            .status-future{background: #C6EBF5 !important;/*定时发布*/}
            .status-private{background:#F2D46F;/*私密日志*/}
            .post-password-required{background:#D874DE;/*密码保护*/}
        </style>';
});

// 头部精简优化
if (!is_admin()) {
    // 将后台设置代码加载到wp_head输出
    add_action('wp_head', function () {
        echo '<style>';
        // if(!empty(get_option("site_hcode"))) { echo htmlspecialchars_decode(get_option("site_hcode"), ENT_QUOTES); }
        echo '
        .woocommerce ul.products li.product a img {width: 100% !important;/* object-fit: cover !important;*/object-fit: contain !important;}
        .woocommerce ul.products li.product a {display: block;overflow: hidden;}
        h2.woocommerce-loop-product__title {height: 3em; display: -webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;overflow: hidden;}
        .woocommerce-Address-title {}
        .product-content h3{min-height: calc(1.5em * 2)}
        article h5.title{min-height: calc(1.5em * 2)}
        article div.conten p {height: calc(1.5em * 10); -webkit-line-clamp: 10; overflow: hidden}
        .wf_footer_middle{display:none}
        button.wf_uptop{bottom: 10rem}
        table.variations .label{width:30%}
        ';
        if(is_product()){
            echo '
            .woocommerce div.product form.cart .variations{text-align:left;}
            .woocommerce div.product form.cart .variations .reset_variations{!display:none;}
            .flex-control-nav.flex-control-thumbs{display:flex;overflow-x:auto;white-space:nowrap;list-style:none;padding:0;margin:0}.flex-control-nav.flex-control-thumbs li{flex-shrink:0;margin-right:10px}.flex-control-nav.flex-control-thumbs img{width:100px;height:100px;object-fit:cover;cursor:pointer;border:1px solid #ddd;border-radius:4px}.flex-control-nav.flex-control-thumbs::-webkit-scrollbar{display:none}.flex-control-nav.flex-control-thumbs{-ms-overflow-style:none;scrollbar-width:none}
            li.woocommerce-mini-cart-item dl.variation::before{content: none !important;}
            ';
        }
        echo '</style>';
    }, 10, 999);

    // 将后台设置代码加载到wp_footer输出
    add_action('wp_footer', function () {
        // 仅产品详情页加载
        if (is_product()) {

        }
        // 全局前端页面加载
        if (!is_admin()){
            if(!empty(get_option('site_stat_code'))) { echo htmlspecialchars_decode(get_option("site_stat_code"), ENT_QUOTES); }
        }
        
        //显示数据库查询次数、查询时间及内存占用的代码
        $stat = sprintf('基于 PHP%.2f%s 运行, %d 次查询 用时 %.3f 秒, 耗费了 %.2fMB 内存', PHP_VERSION, $cache_s = extension_loaded('memcached') ? ' + Memcached':'', get_num_queries(), timer_stop(0, 3), memory_get_peak_usage() / 1024 / 1024);
        echo "<!-- {$stat} -->";
    }, 10, 999);
    
    // 移除所有区块样式，移除古藤堡编辑器样式
    wp_styles()->remove('global-styles'); // 移除 THEME.JSON
    wp_styles()->remove('wp-block-library');
    wp_styles()->remove('wp-block-library-theme');
    wp_styles()->remove('wc-block-style'); // 移除WOO插件区块样式
    // 屏蔽global-styles-inline-css和classic-theme-styles-css
    wp_styles()->remove('global-styles-inline');
    wp_styles()->remove('classic-theme-styles');
    add_action('wp_enqueue_scripts', function () {
        wp_dequeue_style('global-styles');
        wp_deregister_style('global-styles');
        remove_action('wp_head', 'wp_global_styles_render_svg_filters');
    }, 20);
    
    // 移除菜单的多余CSS选择器
    add_filter('nav_menu_css_class', function ($var) {return is_array($var) ? [] : '';},100, 1);
    add_filter('nav_menu_item_id', function ($var) {return is_array($var) ? [] : '';},100, 1);
    add_filter('page_css_class', function ($var) {return is_array($var) ? [] : '';},100, 1);

    // 精简头部元素
    remove_action('wp_head', 'wp_generator'); //泄露WordPress版本号
    remove_action('wp_head', 'wp_resource_hints', 2); //头部加载s.w.org
    remove_action('admin_print_scripts', 'print_emoji_detection_script'); //头部emoji
    remove_action('admin_print_styles', 'print_emoji_styles'); //头部emoji
    remove_action('wp_head', 'print_emoji_detection_script', 7); //头部emoji
    remove_action('wp_print_styles', 'print_emoji_styles'); //头部emoji
    remove_action('embed_head', 'print_emoji_detection_script'); //头部emoji
    remove_filter('the_content_feed', 'wp_staticize_emoji'); //头部emoji
    remove_filter('comment_text_rss', 'wp_staticize_emoji'); //头部emoji
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email'); //头部emoji
    // 移除首页链接、前后文链接、第一篇文章链接和相邻文章链接
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'parent_post_rel_link', 10, 0 );
    remove_action('wp_head', 'start_post_rel_link', 10, 0 );
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 ); // 移除页面meta短链接
    remove_action('template_redirect', 'wp_shortlink_header', 11, 0 ); // 移除页面meta短链接
    // Feed/RSS订阅功能
    add_filter('the_generator', '__return_empty_string'); //头部加载rss
    remove_action('wp_head', 'feed_links', 2); //头部文章和评论feed
    remove_action('wp_head', 'feed_links_extra', 3); //头部分类等feed
    // 离线编辑器发布：以提供更好的编辑和发布博客文章的体验。
    remove_action('wp_head','rsd_link'); //移除head中的rel="EditURI"
    remove_action('wp_head','wlwmanifest_link'); //移除head中的rel="wlwmanifest"
    // 页头脚本移到页脚
    remove_action('wp_head', 'wp_print_scripts');
    remove_action('wp_head', 'wp_print_head_scripts', 9);
    // remove_action('wp_head', 'wp_enqueue_scripts', 1);
    add_action('wp_footer', 'wp_print_scripts', 5);
    add_action('wp_footer', 'wp_print_head_scripts', 5);
    // add_action('wp_footer', 'wp_enqueue_scripts', 5);
    // wp_deregister_script('l10n'); // 禁用本地化插件
    // remove_action('wp_head', [$wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style']); // 移除侧栏回复评论样式
    remove_action('wp_head', 'rest_output_link_wp_head', 10);  // 移除头部 wp-json 标签
    remove_action('template_redirect', 'rest_output_link_header', 11); // 移除头部 wp-json 标签
}

/*
// 外链转内链
add_filter('the_content', 'external_link_goto'); // 替换文章内容中的外链
add_filter('comment_text', 'external_link_goto'); // 替换评论内容中的外链
function external_link_goto($content){
    // 主题激活时自动复制 goto.html 到根目录
    if (!get_option('goto_file_copied')) {
        $source_file = get_template_directory() . '/plugins/goto.html';
        $destination_file = ABSPATH . 'goto.html';

        if (file_exists($source_file)) {
            if (copy($source_file, $destination_file)) {
                update_option('goto_file_copied', true); // 标记已复制，防止重复执行
            } else {
                // error_log('goto.html 复制失败，请检查权限');
            }
        } else {
            // error_log('plugins/goto.html 不存在于当前主题中');
        }
    }
    # 外链跳转逻辑
    preg_match_all('/<a(.*?)href="(.*?)"(.*?)>/', $content, $matches);
    if($matches){
        foreach($matches[2] as $val){
            if(strpos($val,'://')!==false && strpos($val,home_url())===false && !preg_match('/\.(bmp|jpg|jpeg|gif|png|ico|svg|webp)/i',$val) && !preg_match('/(tel|sms|mailto|weixin|mqq|alipay|taobao|sinaweibo|zhifu|ftp|ed2k|thunder|flashget|qqdl|magnet):\/\//i',$val)){
                $content = str_replace("href=\"$val\"", "href=\"".home_url()."/goto/".base64_encode($val)."\" rel=\"nofollow external\"", $content);
            }
        }
    }
    return $content;
}
*/

// 自动修改标签别名为：标签ID的MD5或者ID加密形式
add_action('created_term', function ($term_id, $tt_id, $taxonomy) {
    if ($taxonomy === 'post_tag') {
        $term = get_term($term_id, $taxonomy);
        wp_update_term($term_id, $taxonomy, array(
            // 'slug' => md5($term_id), // 更新标签的slug为MD5散列
            'slug' => $term_id, // 更新标签的slug为ID
        ));
    }
}, 10, 3);

// 自动为文章添加已使用过的标签
add_action('save_post', function (){
    if(is_single()) {
        $tags = get_tags(array('hide_empty' => false));
        $content = get_post(get_the_ID())->post_content;
        if ($tags) {
            $applied_tags = [];
            $i = 0;
            $arrs = object2array($tags);
            shuffle($arrs);
            $tags = array2object($arrs);// 打乱顺序
            foreach ($tags as $tag) {
                $tag_name = $tag->name;
                // 确保标签尚未应用，并且文章内容中存在该标签
                if (!in_array($tag_name, $applied_tags) && strpos($content, $tag_name) !== false) {
                    if ($i >= 10) { // 控制输出数量
                        break;
                    }
                    wp_set_post_tags(get_the_ID(), $tag_name, true);
                    $applied_tags[] = $tag_name; // 记录已应用的标签
                    $i++;
                }
            }
        }
    }
});

// 文章标签自动内链（精简代码，支持多词标签、各种边界情况，每个标签只替换一次，避开特定标签）
add_filter('the_content', function ($content) {
    $tags = get_the_tags();
    if (empty($tags)) return $content;

    usort($tags, function($a, $b) { return mb_strlen($b->name, 'UTF-8') - mb_strlen($a->name, 'UTF-8'); });
    $unique_tags = [];
    foreach ($tags as $tag) {
        $tag_name = trim($tag->name);
        if ($tag_name === '' || isset($unique_tags[$tag_name])) continue;
        $unique_tags[$tag_name] = $tag;
    }

    $protected = [];
    $content = preg_replace_callback('/(<a\b[^>]*>.*?<\/a>|<h[1-6]\b[^>]*>.*?<\/h[1-6]>|<img\b[^>]*>|<pre\b[^>]*>.*?<\/pre>|<code\b[^>]*>.*?<\/code>)/si', function ($m) use (&$protected) {
        $key = '%PROTECT_' . count($protected) . '%';
        $protected[$key] = $m[0];
        return $key;
    }, $content);

    $replace_map = [];
    $content_marker = $content;

    foreach ($unique_tags as $tag_name => $tag) {
        $pattern = '/(?<!>)(?<![>\p{L}\p{N}_])(' . preg_quote($tag_name, '/') . ')(?![<\p{L}\p{N}_])/iu';
        $content_marker = preg_replace_callback($pattern, function($m) use ($tag, &$replace_map, $tag_name) {
            $unique = '%TAGLINK_' . md5($tag_name . uniqid('', true)) . '%';
            $replace_map[$unique] = '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" rel="tag">' . $m[1] . '</a>';
            return $unique;
        }, $content_marker, 1);
    }

    if (!empty($replace_map)) $content_marker = strtr($content_marker, $replace_map);
    if (!empty($protected)) $content_marker = strtr($content_marker, $protected);
    return $content_marker;
});

// 发布文章时随机设置浏览量
// add_action('publish_post', function ($post_id) { update_post_meta($post_id, 'views', rand(1, 100));}, 10, 2);

// 增加媒体支持
add_filter('upload_mimes', function ($mimes) {
    $new_file_types = array(
        // 'zip' => 'application/zip',
        'mobi' => 'application/x-mobipocket-ebook',
        'pdf'  => 'application/pdf',
        'epub' => 'application/epub+zip',
    );
    return array_merge($mimes, $new_file_types);
});

// 依赖于用户相关函数（例如 current_user_can()、get_current_user_id() 等）的代码，
// 包裹在 WordPress 提供的动作钩子中，这些钩子会在用户和能力系统完全加载之后触发。
add_action('plugins_loaded', function() {
    // 压缩前端html代码
    if (!current_user_can('manage_options')) {
        add_action('init', function () {
            // 压缩代码逻辑
            function wp_compress_html_main($buffer)
            {
                $initial = strlen($buffer);
                // 检查内容是否为空
                if (strlen(trim($buffer)) === 0) {
                    return $buffer;
                }
                // 获取当前请求的 URI 和响应头
                $uri = $_SERVER['REQUEST_URI'] ?? '';
                $headers = headers_list();
                // 判断是否为 XML 或 JSON 内容
                foreach ($headers as $header) {
                    $header = strtolower($header);
                    if (strpos($header, 'content-type:') !== false) {
                        if (strpos($header, 'application/xml') !== false || strpos($header, 'text/xml') !== false) {
                            // 是 XML 类型，例如 sitemap.xml
                            return $buffer;
                        }
                        if (strpos($header, 'application/txt') !== false || strpos($header, 'text/plain') !== false) {
                            // 是 XML 类型，例如 sitemap.xml
                            return $buffer;
                        }
                        if (strpos($header, 'application/json') !== false) {
                            // 是 JSON 类型数据
                            return $buffer;
                        }
                    }
                }
                // 判断 URL 是否包含 /wp-json/
                if (false !== strpos($uri, '/wp-json/')) {
                    return $buffer;
                }
                // 判断内容是否是 XML 文档（比如 sitemap）
                if (false !== strpos($buffer, '<?xml') || false !== strpos(strtolower($buffer), '<urlset xmlns=')) {
                    return $buffer;
                }
                // 判断是否为登录页、注册页、特定页面
                if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))||in_array(get_post_field('post_name', get_post()) ?? '404', ['goto', '404', 'nofound'])) {
                    return $buffer;
                }
                
                $buffer = explode("<!--wp-compress-html-->", $buffer);
                $count  = count($buffer);
                for ($i = 0; $i < $count; $i++) {
                    if (stristr($buffer[$i], '<!--wp-compress-html no compression-->')) {
                        $buffer[$i] = (str_replace("<!--wp-compress-html no compression-->", " ", $buffer[$i]));
                    } else {
                        $buffer[$i] = (str_replace("\t", " ", $buffer[$i]));
                        $buffer[$i] = (str_replace("\n\n", "\n", $buffer[$i]));
                        $buffer[$i] = (str_replace("\n", "", $buffer[$i]));
                        $buffer[$i] = (str_replace("\r", "", $buffer[$i]));
                        while (stristr($buffer[$i], '  ')) {
                            $buffer[$i] = (str_replace("  ", " ", $buffer[$i]));
                        }
                    }
                    $buffer_out = isset($buffer_out) ? $buffer_out . $buffer[$i] : $buffer[$i];
                }
                
                $final   = strlen($buffer_out);
                $savings = ($initial - $final) / $initial * 100;
                $savings = round($savings, 2);
                $buffer_out .= "\n<!--压缩前的大小: $initial bytes; 压缩后的大小: $final bytes; 节约：$savings% -->";
                return $buffer_out;
            }
            // 后台不压缩
            if (!is_admin()) {
                ob_start("wp_compress_html_main");
            }
        });
        //当检测到文章内容中有代码标签时文章内容不会被压缩
        add_filter('the_content', function ($content) {
            if (preg_match_all('/(language-|<\/pre>)/i', $content, $matches) || preg_match_all('/(crayon-|<\/pre>)/i', $content, $matches)) {
                $content = '<!--wp-compress-html--><!--wp-compress-html no compression-->' . $content;
                $content .= '<!--wp-compress-html no compression--><!--wp-compress-html-->';
            }
            return $content;
        });
    }
    // 禁止admin账号
    add_filter('wp_authenticate', function ($user){if($user == 'admin'){ exit; }});
    add_filter('sanitize_user', function ($username, $raw_username, $strict){
        if($raw_username == 'admin' || $username == 'admin'){ exit; }
        return $username;
    },10,3);
    // 限制作者只读自己文章
    add_filter('rest_post_query', function ( $args, $request ) {
        $user_id = get_current_user_id();
        $args['author'] = $user_id;
        $args['author__in'] = array();           
        $args['author__not_in'] = array();
        return $args;
    }, 10, 2 );
});

// sitemap 和 robots
// CDN模式状态下xml无法访问使用txt替代
add_action('template_redirect', function () {
    $p_paths = ['/sitemap.txt', '/today.txt'];
    $r_path = parse_url(add_query_arg(array()), PHP_URL_PATH);
    if (!in_array($r_path, $p_paths)) return;
    status_header(200);
    header('Content-Type: text/plain; charset=utf-8');
    require_once(ABSPATH . 'wp-load.php');
    global $wpdb;
    if ($r_path === '/sitemap.txt') {
        $posts_to_show = 2000;
        // 文章
        $myposts = get_posts(array('numberposts' => $posts_to_show,'fields' => 'ids',));
        $post_links = array_map(function($postId) {return get_permalink($postId);}, $myposts);
        //页面
        // $mypages = get_pages(array('fields' => 'ids')); 所有页面（不排除任何页面）
        // 获取并排除特定页面的ID
        $excluded_slugs = array('hello-world', 'privacy-policy', 'goto', 'player', 'contact-us', 'request-a-quote'); // 页面slug列表
        $excluded_ids = array_map(function($slug) use ($wpdb) {
            return (int)$wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'page'", $slug));
        }, $excluded_slugs);
        $mypages = get_pages(array('exclude' => $excluded_ids,'fields' => 'ids',));
        $page_links = array_map('get_page_link', $mypages);
        echo implode("\n", array_merge($post_links, $page_links));
        exit;
    } else if ($r_path === '/today.txt') {
        // 获取今日发布的文章链接
        $myposts = get_posts(
        array(
            'date_query'  => array(array(
                'after' => date('Y-m-d 00:00:00'),
                'before' => date('Y-m-d 23:59:59'),
                'inclusive' => true
            )),
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        $post_links = array_map(function($postId) {return get_permalink($postId);}, $myposts);
        echo implode("\n", $post_links);
        exit;
    } else {
        // 其他逻辑
        exit;
    }
});

// 优化robots.txt
add_action('do_robotstxt', function () {
    header('Content-Type: text/plain');
    echo "# robots.txt for Search Bot\n";

    echo "User-agent: *\n";
    echo "Allow: /wp-content/themes/\n";
    echo "Allow: /wp-content/uploads/\n";
    echo "Allow: /wp-content/plugins/\n";
    echo "Allow: /uploads/\n";
    echo "Allow: /assets/\n";
    echo "Allow: /static/\n";
    echo "Allow: /*.html$\n";

    // Trackback、XML-RPC Links
    echo "Disallow: /*/trackback\n";
    echo "Disallow: /trackback/\n";
    echo "Disallow: /xmlrpc.php\n";

    // Redirect Links
    echo "Disallow: /goto/\n";
    echo "Disallow: /go/\n";
    echo "Disallow: /go.*\n";

    // Feed Links
    echo "Disallow: /feed\n";
    echo "Disallow: /*/feed\n";
    echo "Disallow: /comments/feed\n";
    echo "Disallow: /*/comments/feed\n";

    // Search Links
    echo "Disallow: /?s=*\n";
    echo "# Disallow: /*/?s=*\n";

    // Comment Links
    echo "Disallow: /?r=*\n";
    echo "Disallow: /*/comment-page-*\n";
    echo "Disallow: /*?replytocom*\n";

    // Other Links
    echo "Disallow: /?p=*\n";
    echo "Disallow: /uncategorized/\n";
    echo "Disallow: /*.html/attachment/*\n";
    echo "Disallow: /*.html/*?*\n";
    echo "Disallow: /tag/\n";
    echo "Disallow: /?tag\n";
    echo "Disallow: /?random\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /date/\n";
    echo "Disallow: /author/\n";
    echo "Disallow: /?p=*&preview=true\n";
    echo "Disallow: /?page_id=*&preview=true\n";
    echo "Disallow: /wp-*/\n";
    echo "Disallow: /page/1$\n";
    echo "Disallow: /*/page/1$\n";
    echo "Disallow: /home.php$\n";
    echo "Disallow: /index.php$\n";
    echo "Disallow: /attachment/\n";
    echo "Disallow: /*&lang=\n"; // 如果你使用多语言插件
    echo "Disallow: /*?fbclid=\n"; // Facebook 追踪参数
    echo "Disallow: /*?gclid=\n\n"; // Google Ads 参数
    
    // WooCommerce
    echo "Disallow: /checkout$\n\n";
    echo "Disallow: /wishlist$\n\n";
    echo "Disallow: /cart$\n\n";
    echo "Disallow: /compare$\n\n";
    echo "Disallow: /my-account\n\n";
    echo "Disallow: /*?attribute_\n\n";

    // Sitemap
    echo "Sitemap: " . site_url('sitemap.xml') . "\n";
    echo "Sitemap: " . site_url('sitemap.txt') . "\n";
    exit;
});

// 静态文件CDN加速
if ( !is_admin() ) {
    add_action('wp_loaded', function() {
        // 开启输出缓冲，并将 'cdn_domain_replace' 函数设置为回调
        ob_start('cdn_domain_replace');
    });
    function cdn_domain_replace($html){
        $local_host = $_SERVER['HTTP_HOST']; 
        $cdn_host = 'cdn.jsdelivr.net/gh/lopinv/BuchMistrz@main';
        $cdn_exts = 'css|js|png|jpg|jpeg|gif|ico';
        $cdn_dirs = 'wp-content'; // 'wp-content|wp-includes'
        $cdn_dirs = str_replace('-', '\-', $cdn_dirs);
        if ($cdn_dirs) {
            $regex = '/' . str_replace('/', '\/', $local_host) . '\/((' . $cdn_dirs . ')\/[^\s\?\\\'\"\;\>\<]{1,}.(' . $cdn_exts . '))([\"\\\'\s\?]{1})/';
            $html = preg_replace($regex, $cdn_host . '/$1$4', $html);
        } else {
            $regex = '/' . str_replace('/', '\/', $local_host) . '\/([^\s\?\\\'\"\;\>\<]{1,}.(' . $cdn_exts . '))([\"\\\'\s\?]{1})/';
            $html = preg_replace($regex, $cdn_host . '/$1$3', $html);
        }
        return $html;
    }
}

/* *********************************************** 后台美化 *********************************************** */
// 登录页样式美化
add_action('login_header', function (){
    echo '<div class="login-container">
    <div class="login-body">
        <div class="login-img shadow-lg position-relative flex-fill">
            <div class="img-bg position-absolute">
                <div class="login-info">
                    <!--
                    <h2>'. get_bloginfo('name') .'</h2>
                    <p>'. get_bloginfo('description') .'</p>
                    -->
                </div>
            </div> 
        </div>';
});
add_action('login_footer', function (){
    echo '</div><!--login-body END-->
    </div><!--login-container END-->';
});
add_action('login_head', function (){
    $login_color['color-l'] = '#F13F3F'; $login_color['color-r'] = '#F3F31F';
    echo '<style type="text/css">
    body{background:'.$login_color['color-l'].';background:-o-linear-gradient(45deg,'.$login_color['color-l'].','.$login_color['color-r'].');background:linear-gradient(45deg,'.$login_color['color-l'].','.$login_color['color-r'].');height:100vh}
    .login h1 a{background-image:url("https://cdn.jsdelivr.net/gh/lopinv/BuchMistrz@main/wp-content/uploads/2025/07/logo.png");width:180px;background-position:center center;background-size:160px;filter:invert(0.5) brightness(0)}
    .login-container{position:relative;display:flex;align-items:center;justify-content:center;height:100vh}
    .login-body{position:relative;display:flex;margin:0 1.5rem}
    .login-img{display:none}
    .img-bg{color:#fff;padding:2rem;bottom:-2rem;left:0;top:-2rem;right:0;border-radius:10px; background-image:url("https://cdn.jsdelivr.net/gh/lopinv/BuchMistrz@main/wp-content/uploads/bg102.jpg'. '");background-repeat:no-repeat;background-position:center center;background-size:cover}
    .img-bg h2{font-size:2rem;margin-bottom:1.25rem}
    #login{position:relative;background:#fff;border-radius:10px;padding:28px;width:300px;box-shadow:0 1rem 3rem rgba(0,0,0,.175)}
    .flex-fill{flex:1 1 auto}
    .position-relative{position:relative}
    .position-absolute{position:absolute}
    .shadow-lg{box-shadow:0 1rem 3rem rgba(0,0,0,.175)!important}
    #login form{-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;border-width:0;padding:0}
    #login form .forgetmenot{float:none}
    .login #login_error,.login .message,.login .success{border-left-color:#40b9f1;box-shadow:none;background:#d4eeff;border-radius:6px;color:#2e73b7}
    .login #login_error{border-left-color:#f1404b;background:#ffd4d6;color:#b72e37}
    #login form p.submit{padding:20px 0 0}
    #login form p.submit .button-primary{float:none;background-color:#f1404b;font-weight:bold;color:#fff;width:100%;height:40px;border-width:0;text-shadow:none!important;border-color:none;transition:.5s}
    #login form input{box-shadow:none!important;outline:none!important}
    #login form p.submit .button-primary:hover{background-color:#444}
    .login #backtoblog,.login #nav{padding:0}
    .login #backtoblog{display:none}
    @media screen and (min-width:768px){.login-body{width:1200px}.login-img{display:block}#login{margin-left:-60px;padding:40px}}
</style>';
});

//登录页面的LOGO链接为首页链接
add_filter('login_headerurl',function() {return esc_url(home_url());});

// 去掉登录界面的LOGO元素项
add_action('login_footer', function () {
  ?>
  <script>
      window.addEventListener('load', function () {
          // 所有资源加载完成后的处理逻辑
          jQuery('#login>h1:first-child').remove();
      });
  </script>
  <?php
});

// 去掉后台标题中的 " - WoroPress"
add_filter('admin_title', function ($admin_title, $title) {
  return $title . ' &lsaquo; ' . get_bloginfo('name');
}, 10, 2);

// 隐藏后台标题中的“WordPress”
add_filter('login_title', function ($login_title, $title) {
  return $title . ' &lsaquo; ' . get_bloginfo('name');
}, 10, 2);

// 自定义 WordPress 后台底部的版权和版本信息
add_filter('admin_footer_text', function ($text) {
  return get_bloginfo('name') . ' - ' . get_bloginfo('description');
}, 11);
add_filter('update_footer', function ($text) {
  return "&copy;2025 - " . date('Y') . " <a href='" . get_bloginfo('name') . "' target='_blank'>" . get_bloginfo('name') . "</a>";
}, 11);

// 网站后台文章新标签打开
if ($GLOBALS['pagenow'] == 'post.php') {
    //这里如果不加的话，就会后台一直 js 报错
    add_action('admin_footer', function () {
        echo '<script type="text/javascript">
        var postlink = document.getElementById("edit-slug-box").getElementsByTagName("a");
        for(var i=0;i<postlink.length;i++){ postlink[i].target = "_blank"; }
        </script>';
    });
}

// 文章发布时弹出确认发布对话框
add_action('admin_footer', function () {
    $c_message = '请再次确认是否要发布这篇文章？';
    global $c_message;
    echo '<script type="text/javascript"><!--
    var publish = document.getElementById("publish");
    if (publish !== null) publish.onclick = function(){
        return confirm("' . $c_message . '");
    };
    // --></script>';
});

//新标签打开顶部网站链接
add_action('admin_footer', function () {
    echo '<script type="text/javascript">
    var sitelink = document.getElementById("wp-admin-bar-site-name").getElementsByClassName("ab-item");
    for(var i=0;i<sitelink.length;i++){ sitelink[i].target = "_blank"; }
    </script>';
});

/*
// 禁用后台主题编辑器
add_action('init', function () { define('DISALLOW_FILE_EDIT', true); });

// 禁用后台主题自定义
add_filter('map_meta_cap', function ($caps, $cap) {
if ($cap == 'customize') return ['do_not_allow'];
  return $caps;
}, 10, 2);
*/

// 移除出现在每个已登录用户页面顶部的管理工具栏中的Logo菜单项
add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');
}, 0);

/* *********************************************** 邮件设置 *********************************************** */
// 增加设置文本框/文本域
add_action('admin_init', function () {
    add_settings_field('notice_smtp_args', 'SMTP设置', function () {
        echo '<textarea name="notice_smtp_args" rows="3" id="notice_smtp_args" class="large-text code" placeholder="格式：SMTP服务器|SMTP端口|SMTP协议|SMTP邮箱|SMTP密码|用户名|显示邮箱|测试邮箱">' . get_option('notice_smtp_args') . '</textarea>';
    }, 'general');
    add_settings_field('notice_webhook_args', 'Webhook', function () {
        echo '<textarea name="notice_webhook_args" rows="3" id="notice_webhook_args" class="large-text code" placeholder="钉钉/飞书/Telegram/Discord参数, 钉钉：webhook_url|app_secret|access_token，飞书：webhook_url|app_id|app_secret，Telegram：webhook_url|bot_token|chat_id">' . get_option('notice_webhook_args') . '</textarea>';
    }, 'general');
    add_settings_field('spam_search_keys', '搜索屏蔽', function () {
        echo '<textarea name="spam_search_keys" rows="3" id="spam_search_keys" class="large-text code" placeholder="格式：网盘,Q币,尼玛">' . get_option('spam_search_keys') . '</textarea>';
    }, 'general');
    add_settings_field('site_stat_code', '统计代码', function () {
        echo '<textarea name="site_stat_code" rows="3" id="site_stat_code" class="large-text code" placeholder="HTML代码">' . get_option('site_stat_code') . '</textarea>';
    }, 'general');

    register_setting('general','notice_smtp_args');
    register_setting('general','notice_webhook_args');
    register_setting('general','spam_search_keys');
    register_setting('general','site_stat_code');
});

// SMTP邮箱设置
// 格式：SMTP服务器|SMTP端口|SMTP协议|SMTP邮箱|SMTP密码|用户名|显示邮箱|测试邮箱
if(!empty(get_option('notice_smtp_args'))):
    $smtp_args = explode('|', get_option('notice_smtp_args'));
    if (count($smtp_args) == 8) {
        // 配置SMTP邮件服务器
        add_action('phpmailer_init', function ($phpmailer) use ($smtp_args) {
            // 注意: 某些SMTP服务器要求发件人地址 (From) 必须与认证的邮箱账号 (Username) 相同。
            // 如果遇到发送问题，强烈建议将下面一行修改为：
            $phpmailer->Host = $smtp_args[0] ?? 'smtp.qq.com'; // SMTP服务器地址
            $phpmailer->Port = $smtp_args[1] ?? 465; // SMTP邮件发送端口
            $phpmailer->SMTPSecure = $smtp_args[2] ?? 'ssl'; // SMTP加密方式(SSL/TLS), 没有为空即可
            $phpmailer->Username = $smtp_args[3] ?? get_bloginfo('admin_email'); // 邮箱帐号
            $phpmailer->Password = $smtp_args[4] ?? ''; // 邮箱密码
            $phpmailer->FromName = $smtp_args[5] ?? get_bloginfo('name'); // 发件人昵称
            $phpmailer->From = $smtp_args[6] ?? get_bloginfo('admin_email'); // 发件人地址
            $phpmailer->isSMTP(); // <-- 推荐使用小写 'i'
            $phpmailer->SMTPAuth = true; // 启用SMTP认证服务
        });

        // 增加SMTP邮件Ajax
        add_action('wp_ajax_send_test_email', function () use ($smtp_args) {
            if (!current_user_can('manage_options')) wp_die(-1);
            $email_send_result = wp_mail(
                $smtp_args[7] ?? get_option('admin_email'), 
                '一个SMTP配置测试邮件', 
                '<p>你好！，这是你的网站(' . get_option("blogname") . ')的一个SMTP配置测试邮件</p>' . 
                '<p>请确定是您自己的配置测试，以防别人攻击！当前测试信息如下：</p>' . 
                '<p>测试时间：' . date("Y-m-d H:i:s") .  '<p>' .
                '<p>测试IP：' . get_user_ipaddr() . '<p>', 
                "Content-Type: text/html; charset=\"utf-8\"\n"
            );
            if ($email_send_result) {
                echo '<p style="color:green;">发送邮件成功，请注意查收邮件！</p>';
            } else {
                echo '<p style="color:red;">发送邮件失败，请检查邮件设置！</p>';
            }
            wp_die();
        });

        //函数作用：有登录wp后台就会email通知博主
        add_action('wp_login', function ($username) use ($smtp_args) {
            if (!empty($username)) {
                // 检查登录用户是否具有管理员权限
                if (current_user_can('manage_options')) {
                    $subject = '你的网站登录成功提醒';
                    $message = '<p>你好！你的网站(' . get_option("blogname") . ')有登录！</p>' . 
                    '<p>请确定是您自己的登录，以防别人攻击！登录信息如下：</p>' . 
                    '<p>登录名：' . $_POST['log'] . '<p>' .
                    '<p>登录密码：' . $_POST['pwd'] .  '<p>' .
                    '<p>登录时间：' . date("Y-m-d H:i:s") .  '<p>' .
                    '<p>登录IP：' . $_SERVER['REMOTE_ADDR'] . '<p>' .
                    '<p style="float:right">————本邮件由系统发送，无需回复</p>';
                    $wp_email = 'noreply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
                    $from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
                    $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
                    wp_mail($smtp_args[7] ?? get_option('admin_email'), $subject, $message, $headers);
                }
            }
        });

        //函数作用：有错误登录wp后台就会email通知博主
        add_action('wp_login_failed', function ($username) use ($smtp_args) {
            if (!empty($username)) {
                // 检查登录用户是否具有管理员权限
                if (current_user_can('manage_options')) {
                    $subject = '你的网站登录错误警告';
                    $message = '<p>你好！你的网站(' . get_option("blogname") . ')有登录错误！</p>' . 
                    '<p>请确定是您自己的登录失误，以防别人攻击！登录信息如下：</p>' . 
                    '<p>登录名：' . $_POST['log'] . '<p>' .
                    '<p>登录密码：' . $_POST['pwd'] .  '<p>' .
                    '<p>登录时间：' . date("Y-m-d H:i:s") .  '<p>' .
                    '<p>登录IP：' . $_SERVER['REMOTE_ADDR'] . '<p>' .
                    '<p style="float:right">————本邮件由系统发送，无需回复</p>';
                    $wp_email = 'noreply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
                    $from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
                    $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
                    wp_mail($smtp_args[7] ?? get_option('admin_email'), $subject, $message, $headers);
                }
            }
        });
        
        // 测试邮件配置信息
        add_action('admin_footer', function () {?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                ($('#notice_smtp_args').closest('tr')).after('<tr><th scope="row"><label for="emailtest">邮件测试</label></th><td><button id="test-email-button" class="button button-primary">发送邮件</button></td><td id="email-test-result"></td></tr>');
                $('#test-email-button').click(function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'send_test_email',
                        },
                        success: function(response) {
                            $('#email-test-result').html(response);
                        },
                        error: function(errorThrown) {
                            console.log('错误: ' + errorThrown);
                        }
                    });
                });
            });
            </script>
        <?php });
    }
endif;

/* *********************************************** 商城优化 *********************************************** */
add_action( 'admin_menu', function () {
    global $menu;
    foreach ( $menu as $key => $value ) {
        if ( 'WooCommerce' === $value[0] ) {
            $menu[$key][0] = '商店'; // 将 'WooCommerce' 改为 '商店'
            return;
        }
    }
});
add_filter( 'product_type_options',  function ( $options ) {
    if ( isset( $options['virtual'] ) ) {
        unset( $options['virtual'] ); // 删除虚拟商品
    }
    if ( isset( $options['downloadable'] ) ) {
        unset( $options['downloadable'] ); // 删除可下载商品
    }
    return $options;
});

add_filter( 'wc_product_sku_enabled', '__return_false' ); // 隐藏SKU

// 后台首页设置为 WooCommerce 分析页面
add_action( 'current_screen', function () {
    if ( class_exists( 'WooCommerce' ) ) {
        $current_screen = get_current_screen();
        if ( $current_screen && 'dashboard' === $current_screen->base ) {
            $user = wp_get_current_user();
            if ( in_array( 'administrator', $user->roles ) ) {
                wp_redirect( admin_url( 'admin.php?page=wc-admin&path=/analytics/overview' ) );
                exit;
            }
        }
    }
});

// 强制禁用“配送到不同地址”复选框的选中状态，隐藏“配送到不同地址”的表单部分
add_action( 'wp_head', function () {
    if ( is_checkout() ) {
        ?>
        <style type="text/css">
            /* 隐藏“发送到其他地址”复选框 */
            #ship-to-different-address-checkbox {
                display: none !important;
            }
            /* 隐藏点击复选框后出现的配送地址区域 */
            .woocommerce-shipping-fields {
                display: none !important;
            }
        </style>
        <?php
    }
});

// 修改WooCommerce结算页面的默认收货地址字段，并添加自定义字段
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
    // --- 移除不需要的默认账单地址字段 ---
    // 根据您提供的代码，这些字段被移除了，保留了姓名、国家和电话、邮箱
    unset( $fields['billing']['billing_company'] );
    unset( $fields['billing']['billing_address_1'] );
    unset( $fields['billing']['billing_address_2'] );
    unset( $fields['billing']['billing_city'] );
    unset( $fields['billing']['billing_state'] );
    unset( $fields['billing']['billing_postcode'] );

    // --- 移除不需要的默认收货地址字段 ---
    // 根据您提供的代码，这些字段被移除了，保留了姓名和国家
    unset( $fields['shipping']['shipping_company'] );
    unset( $fields['shipping']['shipping_address_1'] );
    unset( $fields['shipping']['shipping_address_2'] );
    unset( $fields['shipping']['shipping_city'] );
    unset( $fields['shipping']['shipping_state'] );
    unset( $fields['shipping']['shipping_postcode'] );

    // --- 确保电话号码和邮箱号码存在且必填 ---
    // WooCommerce默认电话和邮箱是必填的，这里确保它们没有被意外移除
    $fields['billing']['billing_phone']['required'] = true;
    $fields['billing']['billing_email']['required'] = true;

    // --- 添加新的自定义字段 ---
    // InPost快递柜编号
    $fields['billing']['inpost_locker_number'] = array(
        'type'        => 'text',
        'class'       => array('form-row-wide'),
        'label'       => 'kod paczkomatuj Inpost',
        'placeholder' => 'InPost Paczkomat ID (np. WAW123A): WRO114M',
        'required'    => true, // 通常快递柜编号是必填的
        'priority'    => 40, // 调整显示顺序
    );

    // priority 确保排在电话号码之后
    $fields['billing']['whatsapp_number'] = array(
        'type'        => 'text',
        'class'       => array('form-row-wide'),
        'label'       => 'WhatsApp',
        'placeholder' => 'Podaj swój numer WhatsApp',
        'required'    => false, // 根据需要设置为true或false
        'priority'    => 41, // 调整显示顺序
    );

    // Telegram 号码
    // priority 确保排在 WhatsApp 号码之后
    $fields['billing']['telegram_number'] = array(
        'type'        => 'text',
        'class'       => array('form-row-wide'),
        'label'       => 'Telegram',
        'placeholder' => 'Podaj swój numer Telegram',
        'required'    => false, // 根据需要设置为true或false
        'priority'    => 42, // 调整显示顺序
    );

    return $fields;
});

/**
 * 强制禁用所有运费计算和收货地址选项
 * 这将确保结算页面不会出现物理地址相关的运费和收货地址部分
 */
// add_filter( 'woocommerce_cart_needs_shipping', '__return_false' );
// add_filter( 'woocommerce_checkout_needs_shipping', '__return_false' );

// 保存 InPost 快递柜编号到订单元数据
add_action( 'woocommerce_checkout_update_order_meta',  function ( $order_id ) {
    if ( ! empty( $_POST['inpost_locker_number'] ) ) {
        update_post_meta( $order_id, 'inpost_locker_number', sanitize_text_field( $_POST['inpost_locker_number'] ) );
    }
});

// 保存 WhatsApp/Telegram 号码到订单元数据
add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id ) {
    if ( ! empty( $_POST['whatsapp_number'] ) ) {
        update_post_meta( $order_id, 'whatsapp_number', sanitize_text_field( $_POST['whatsapp_number'] ) );
    }
    if ( ! empty( $_POST['telegram_number'] ) ) {
        update_post_meta( $order_id, 'telegram_number', sanitize_text_field( $_POST['telegram_number'] ) );
    }
});

// 在后台订单详情页的账单地址下方显示 InPost 快递柜编号
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
    $inpost_locker_number = get_post_meta( $order->get_id(), 'inpost_locker_number', true );
    if ( $inpost_locker_number ) {
        echo '<p><strong>' . __( 'Kod paczkomatuj Inpost', 'woocommerce' ) . ':</strong> ' . esc_html( $inpost_locker_number ) . '</p>';
    }
}, 10, 1 );

// 在后台订单详情页的账单地址下方显示 WhatsApp/Telegram 号码
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
    $whatsapp_number = get_post_meta( $order->get_id(), 'whatsapp_number', true );
    $telegram_number = get_post_meta( $order->get_id(), 'telegram_number', true );
    if ( $whatsapp_number ) {
        echo '<p><strong>' . __( 'WhatsApp', 'woocommerce' ) . ':</strong> ' . esc_html( $whatsapp_number ) . '</p>';
    }
    if ( $telegram_number ) {
        echo '<p><strong>' . __( 'Telegram', 'woocommerce' ) . ':</strong> ' . esc_html( $telegram_number ) . '</p>';
    }
}, 10, 1 );

// 在订单邮件中显示 InPost 快递柜编号
add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain_text, $email ) {
    $inpost_locker_number = get_post_meta( $order->get_id(), 'inpost_locker_number', true );
    if ( $inpost_locker_number ) {
        echo '<p><strong>' . __( 'Kod paczkomatuj Inpost', 'woocommerce' ) . ':</strong> ' . esc_html( $inpost_locker_number ) . '</p>';
    }
}, 20, 4 );

// 在订单邮件中显示 WhatsApp/Telegram 号码
add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain_text, $email ) {
    $whatsapp_number = get_post_meta( $order->get_id(), 'whatsapp_number', true );
    $telegram_number = get_post_meta( $order->get_id(), 'telegram_number', true );
    if ( $whatsapp_number ) {
        echo '<p><strong>' . __( 'WhatsApp', 'woocommerce' ) . ':</strong> ' . esc_html( $whatsapp_number ) . '</p>';
    }
    if ( $telegram_number ) {
        echo '<p><strong>' . __( 'Telegram', 'woocommerce' ) . ':</strong> ' . esc_html( $telegram_number ) . '</p>';
    }
}, 20, 4 );

// 验证 InPost 快递柜编号是否已填写
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
    // 如果 InPost 快递柜编号是必填的
    if ( empty( $_POST['inpost_locker_number'] ) ) {
        $errors->add( 'inpost_locker_number_error', '<strong>' . __( 'Kod paczkomatuj Inpost', 'woocommerce' ) . '</strong> ' . __( 'Wymagane', 'woocommerce' ) );
    }
}, 10, 2 );

// 在用户资料编辑页面显示自定义字段
add_action( 'show_user_profile', 'add_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'add_custom_user_profile_fields' );
add_action( 'woocommerce_edit_account_form', 'add_custom_user_profile_fields' ); // For My Account page
// 保存用户资料更新时自定义字段的数据
add_action( 'personal_options_update', 'save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_custom_user_profile_fields' );
add_action( 'woocommerce_save_account_details', 'save_custom_user_profile_fields' ); // For My Account page

function add_custom_user_profile_fields( $user_passed_as_arg ) {
    $user_id = 0; // 初始化用户ID
    $user_data_object = null; // 初始化用户数据对象

    // 1. 检查传入的参数是否已经是 WP_User 对象
    if ( $user_passed_as_arg instanceof WP_User ) {
        $user_data_object = $user_passed_as_arg;
        $user_id = $user_data_object->ID;
    }
    // 2. 如果传入的是数字 (用户ID)，则尝试获取 WP_User 对象
    elseif ( is_numeric( $user_passed_as_arg ) ) {
        $user_id = (int) $user_passed_as_arg; // 确保是整数
        $user_data_object = get_user_by( 'id', $user_id );
    }
    // 3. 如果传入的既不是 WP_User 对象也不是数字，或者获取不到有效的用户对象，则直接退出函数
    if ( ! $user_id || ! $user_data_object || ! ($user_data_object instanceof WP_User) ) {
        // Log the issue if you want to debug further, but don't output to frontend
        // error_log( 'Invalid user object passed to add_custom_user_profile_fields: ' . var_export($user_passed_as_arg, true) );
        return; // 提前退出，避免报错
    }

    // 现在，我们确保 $user_data_object 是一个有效的 WP_User 对象
    // 并且 $user_id 是其 ID。所有 get_user_meta 都将使用这个 $user_id。
    ?>
    <h3><?php esc_html_e('Informacje logistyczne', 'your-text-domain' ); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="whatsapp_number"><?php esc_html_e( 'WhatsApp', 'your-text-domain' ); ?></label></th>
            <td>
                <input type="text" name="whatsapp_number" id="whatsapp_number" value="<?php echo esc_attr( get_user_meta( $user_id, 'whatsapp_number', true ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php esc_html_e( 'Podaj swój numer WhatsApp', 'your-text-domain' ); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="telegram_number"><?php esc_html_e( 'Telegram', 'your-text-domain' ); ?></label></th>
            <td>
                <input type="text" name="telegram_number" id="telegram_number" value="<?php echo esc_attr( get_user_meta( $user_id, 'telegram_number', true ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php esc_html_e( 'Podaj swój numer Telegram', 'your-text-domain' ); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="inpost_locker_number"><?php esc_html_e( 'kod paczkomatuj Inpost', 'your-text-domain' ); ?></label></th>
            <td>
                <input type="text" name="inpost_locker_number" id="inpost_locker_number" value="<?php echo esc_attr( get_user_meta( $user_id, 'inpost_locker_number', true ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php esc_html_e( 'Podaj swój kod paczkomatuj Inpost', 'your-text-domain' ); ?></span>
            </td>
        </tr>
    </table>
    <?php
}
function save_custom_user_profile_fields( $user_id ) {
    // 检查当前用户是否有权限编辑该用户
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }

    // 保存 WhatsApp 号码
    if ( isset( $_POST['whatsapp_number'] ) ) {
        update_user_meta( $user_id, 'whatsapp_number', sanitize_text_field( $_POST['whatsapp_number'] ) );
    }

    // 保存 Telegram 号码
    if ( isset( $_POST['telegram_number'] ) ) {
        update_user_meta( $user_id, 'telegram_number', sanitize_text_field( $_POST['telegram_number'] ) );
    }

    // 保存 InPost 快递柜编号
    if ( isset( $_POST['inpost_locker_number'] ) ) {
        update_user_meta( $user_id, 'inpost_locker_number', sanitize_text_field( $_POST['inpost_locker_number'] ) );
    }
}

/* *********************************************** 增强函数 *********************************************** */
// 数组转对象
function array2object($array) { 
    if (is_array($array)) {
        $obj = new StdClass();
        foreach ($array as $key => $val){
            $obj->$key = $val;
        }
    }else{
        $obj = $array;
    }
    return $obj;
}
// 对象转数组
function object2array($object) { 
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    }else{
        $array = $object;
    }
    return $array;
}
// 获取用户IP
function get_user_ipaddr() {
    $ip = '';
    // CDN 支持列表
    $trusted_proxies = [
        'HTTP_CF_CONNECTING_IP',   // Cloudflare
        'HTTP_X_REAL_IP',          // Nginx Forwarded Real IP
        'HTTP_X_FORWARDED_FOR',    // CDN/Proxy 通用字段
        'X-Forwarded-For',
        'X-Real-IP',
        'X-Forwarded',
        'Forwarded-For',
        'Forwarded',
        'True-Client-IP',
        'Client-IP',
        'Ali-CDN-Real-IP',
        'CDN-Src-Ip',
        'CDN-Real-Ip',
        'CF-Connecting-IP',
        'X-Cluster-Client-IP',
        'WL-Proxy-Client-IP',
        'Proxy-Client-IP',
    ];

    // 尝试从 CDN 头部获取 IP
    foreach ($trusted_proxies as $proxy_header) {
        if (!empty($_SERVER[$proxy_header])) {
            $ipList = array_map('trim', explode(',', $_SERVER[$proxy_header]));
            foreach ($ipList as $proxyIp) {
                if (filter_var($proxyIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $proxyIp;
                }
            }
        }
    }

    // 如果启用了 CDN 并做了安全限制，则只允许 CDN 的 REMOTE_ADDR
    if (defined('USING_CDN') && constant('USING_CDN')) {
        $remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (is_cloudflare_ip($remote_ip)) { // 或其他 CDN 校验方式
            return $remote_ip;
        }
    } else {
        // 否则回退到本地 REMOTE_ADDR
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return null; // 如果无法获取公网 IP，返回 null
}