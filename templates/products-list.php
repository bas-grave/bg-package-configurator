<?php

$configurator_children = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true, 
    'parent'     => 44,   
]);

if (!empty($configurator_children) && !is_wp_error($configurator_children)) :
    foreach ($configurator_children as $child_cat) :
        $child_cat_id = $child_cat->term_id;
        $child_cat_name = $child_cat->name;
        $child_cat_link = get_term_link($child_cat);

        $query = new WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $child_cat_id, 
                ],
            ],
        ]);

        if ($query->have_posts()) :
?>

    <div class="package-category" id="package-category-<?php echo esc_attr($child_cat_id); ?>">

        <h2 class="package-category-title h3">
            <?php echo esc_html($child_cat_name); ?>
        </h2>

        <div class="package-products">
            <?php while ($query->have_posts()) : $query->the_post();
                global $product;
                $product_id    = $product->get_id();
                $product_title = get_the_title();
                $product_type  = $product->get_type();
                $product_img   = get_the_post_thumbnail_url($product_id, 'small'); 
            ?>
                <div class="package-product-item" 
                    data-product-id="<?php echo esc_attr($product_id); ?>" 
                    data-product-name="<?php echo esc_attr($product_title); ?>" 
                    data-product-type="<?php echo esc_attr($product_type); ?>">

                    <div class="package-product-item__inner">
                        <?php if ($product_img) : ?>
                            <figure class="package-product-item__image wrap-1-1">
                                <img src="<?php echo esc_url($product_img); ?>" alt="<?php echo esc_attr($product_title); ?>">
                            </figure>
                        <?php endif; ?>

                        <div class="package-product-item__info">
                            <p class="package-product-item__title"><?php echo esc_html($product_title); ?></p>

                            <?php if ($product_type === 'variable') : ?>
                                <div style="display: none;" id="package-variation-box-<?php echo esc_attr($product_id); ?>">
                                    <h3><?php printf(esc_html__('Select variation of %s', 'bgpk'), esc_html($product_title)); ?></h3>
                                    <?php
                                    $available_variations = $product->get_available_variations();
                                    if (!empty($available_variations)) :
                                        foreach ($available_variations as $variation) :
                                            $variation_id = $variation['variation_id'];
                                            $variation_attr = wc_get_formatted_variation(new WC_Product_Variation($variation_id), true);
                                            $variation_image = !empty($variation['image']['url']) ? esc_url($variation['image']['url']) : esc_url($product_img);
                                    ?>
                                            <button class="package-select-variation" 
                                                    data-variation-id="<?php echo esc_attr($variation_id); ?>" 
                                                    data-product-id="<?php echo esc_attr($product_id); ?>" 
                                                    data-product-name="<?php echo esc_attr($product_title . ' - ' . $variation_attr); ?>"
                                                    data-product-image="<?php echo esc_attr($variation_image); ?>">
                                                <?php echo esc_html($variation_attr); ?>
                                            </button><br>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div> <!-- .package-products -->

    </div> <!-- .package-category -->

<?php
        endif; // End product loop
        wp_reset_postdata();
    endforeach;
else :
?>
    <p><?php _e('No categories found inside configurator.', 'bgpk'); ?></p>
<?php endif; ?>
