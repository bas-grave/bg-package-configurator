<div class="package-overview">
    <p class="shop-filter__title">
        <?php _e( 'Chosen products', 'bgpk' ); ?>        
        <span id="package-product-count" style="display: none;">
            <span id="product-count-number">0</span> <?= __('selected', 'bgpk'); ?>
        </span>
    </p>
    <ul id="package-selected-products"></ul>
    <input type="hidden" id="package-products-field" name="package-products-field">
    <button id="request-quote-button" data-fancybox data-src="#request-package-quote-popup" class="button" title="<?= __('Request quote', 'bgpk'); ?>" style="display: none;">
        <?= __('Request quote', 'bgpk'); ?>
    </button>
</div>

<div class="global-popup" id="request-package-quote-popup">
    <p class="h3"><?= __('Request a quote', 'bgpk'); ?></p>
    <p><?= __('Fill in the form below and we will get back to you as soon as possible.', 'bgpk'); ?></p>
    <?= do_shortcode('[gravityform id="4" title="false" description="false" ajax="true"]'); ?>
</div>
