<?php
// 从分类改为品牌
$shopire_product_cat_options_hide_show = get_theme_mod('shopire_product_cat_options_hide_show','1'); 
$shopire_product_cat_ttl = get_theme_mod('shopire_product_cat_ttl','Popular Categories');
$shopire_product_cat_btn_lbl = get_theme_mod('shopire_product_cat_btn_lbl','See All Deals');
$shopire_product_cat_btn_url = get_theme_mod('shopire_product_cat_btn_url','#');
$shopire_product_cat_column = get_theme_mod('shopire_product_cat_column','6');
if($shopire_product_cat_options_hide_show=='1'):
?>  
<section id="wf_product_category" class="wf_product_category wf_product_category_one wf-py-default front-product-cat">
    <div class="wf-container">
         <?php if ( ! empty( $shopire_product_cat_ttl )  || ! empty( $shopire_product_cat_btn_lbl )) : ?>
            <div class="wf-row align-items-center wf-mb-5">
                <div class="wf-col-lg-6 wf-col-md-8">
                    <div class="section-title wf-text-md-left wf-text-center">
                         <?php if ( ! empty( $shopire_product_cat_ttl )) : ?>
                            <h3 class="title"><?php echo wp_kses_post( $shopire_product_cat_ttl); ?></h3>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="wf-col-lg-6 wf-col-md-4">
                    <div class="wf-text-md-right wf-text-center wf-md-0 wf-mt-2">
                         <?php if (! empty( $shopire_product_cat_btn_lbl )) : ?>
                            <a href="<?php echo esc_url( $shopire_product_cat_btn_url); ?>" class="more-link">
                                <?php echo wp_kses_post( $shopire_product_cat_btn_lbl); ?>
                                <i class="far fa-arrow-right wf-ml-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif;    
        if ( class_exists( 'woocommerce' ) ) {
            // 获取所有品牌
            $brands = get_terms([
                'taxonomy' => 'product_brand',
                'hide_empty' => false,
            ]);
            if ( !empty($brands) && !is_wp_error($brands) ):
        ?>
            <div class="wf-row wf-g-4">
            <div class="wf-col-12 wf_owl_carousel owl-theme owl-carousel slider" data-owl-options='{
                "loop": true,
                "animateOut": "fadeOut",
                "animateIn": "fadeIn",
                "autoplay": true,
                "autoplayTimeout": 5500,
                "smartSpeed": 1200,
                "nav": false,
                "dots": true,
                "margin": 0,
                "responsive": {
                    "0": {
                        "items": 1
                    },
                    "576": {
                        "items": 3
                    },
                    "992": {
                        "items": <?php echo esc_attr($shopire_product_cat_column); ?>
                    }
                }
                }'>
                <?php 
                    foreach ( $brands as $brand ) { 
                        $thumbnail_id = get_term_meta( $brand->term_id, 'thumbnail_id', true );
                        $image = wp_get_attachment_url( $thumbnail_id );
                        // 统计品牌下产品总数
                        $product_count = wc_get_products([
                            'status'    => 'publish',
                            'limit'     => -1,
                            'tax_query' => [
                                [
                                    'taxonomy' => 'product_brand',
                                    'field'    => 'term_id',
                                    'terms'    => $brand->term_id,
                                ],
                            ],
                            'return'    => 'ids',
                        ]);
                        $total = count($product_count);
                ?>
                <div class="product-category">
                    <div class="category-inner">
                        <div class="category-image">
                            <a href="<?php echo esc_url(get_term_link( $brand->term_id, 'product_brand' )); ?>" aria-label="<?php  echo esc_attr($brand->name); ?>">
                                <img src="<?php echo esc_url($image); ?>" alt="<?php  echo esc_attr($brand->name); ?>"/>
                            </a>
                        </div>
                        <div class="category-mask">
                            <h3 class="title"><?php  echo esc_html($brand->name); ?></h3>
                            <!--<div class="count"><a href="#"><?php echo esc_html($total); ?> <?php esc_html_e('products','fable-extra'); ?></a></div>-->
                        </div>
                        <a href="<?php echo esc_url(get_term_link( $brand->term_id, 'product_brand' )); ?>" class="category-link" aria-label="<?php  echo esc_attr($brand->name); ?>"></a>
                    </div>
                </div>
                <?php } ?>  
            </div>
            </div>
            <?php endif; ?>
        <?php } ?>
    </div>
</section>
<?php endif; ?>