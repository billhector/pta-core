<?php
defined('ABSPATH') || exit;

$text           = $attributes['text']          ?? 'Get Guide';
$style          = $attributes['style']         ?? 'primary';
$size           = $attributes['size']          ?? '';
$show_price     = (bool) ($attributes['showPrice']     ?? true);
$price_position = $attributes['pricePosition'] ?? 'above';
$subtext        = $attributes['subtext']       ?? '';
$icon           = $attributes['icon']          ?? '';
$extra          = $attributes['extraClass']    ?? '';

$product = \Pta\Core\Blocks\DataAccess::county_guide_product_info();
if (!$product) {
    if (current_user_can('edit_posts')) {
        echo '<p style="color:#a00">[county-purchase: no EDD product linked]</p>';
    }
    return;
}

$btn_classes = 'elementor-button btn-' . $style;
if ($size)  $btn_classes .= ' btn-' . $size;
if ($icon)  $btn_classes .= ' btn-icon';
if ($extra) $btn_classes .= ' ' . $extra;

$out = ['<div class="county-guide-purchase">'];

if ($product['is_purchased']) {
    $out[] = '<a href="' . esc_url(edd_get_success_page_uri()) . '" class="elementor-button btn-secondary purchased-button">';
    $out[] = '<span class="elementor-button-content-wrapper">';
    if ($icon) $out[] = '<i class="' . esc_attr($icon) . ' elementor-button-icon"></i>';
    $out[] = '<span class="elementor-button-text">✓ Access Your Guide</span>';
    $out[] = '</span></a>';
} else {
    if ($show_price && $price_position === 'above') {
        $out[] = '<div class="product-price price-above">';
        if ($product['is_on_sale']) {
            $out[] = '<span class="regular-price">' . $product['formatted_regular_price'] . '</span> ';
            $out[] = '<span class="sale-price">' . $product['formatted_sale_price'] . '</span>';
        } else {
            $out[] = $product['formatted_current_price'];
        }
        $out[] = '</div>';
    }

    $out[] = '<a href="' . esc_url($product['purchase_url']) . '" class="' . esc_attr($btn_classes) . '">';
    $out[] = '<span class="elementor-button-content-wrapper">';

    if ($show_price && $price_position === 'inline') {
        if ($product['is_on_sale']) {
            $out[] = '<span class="price-badge"><span class="regular-price">' . $product['formatted_regular_price'] . '</span> ' . $product['formatted_sale_price'] . '</span>';
        } else {
            $out[] = '<span class="price-badge">' . $product['formatted_current_price'] . '</span>';
        }
    }

    if ($icon) $out[] = '<i class="' . esc_attr($icon) . ' elementor-button-icon"></i>';

    $out[] = $subtext
        ? '<span class="elementor-button-text">' . esc_html($text) . '<small class="button-subtext">' . esc_html($subtext) . '</small></span>'
        : '<span class="elementor-button-text">' . esc_html($text) . '</span>';

    $out[] = '</span></a>';

    if ($show_price && $price_position === 'below') {
        $out[] = '<div class="product-price price-below">';
        if ($product['is_on_sale']) {
            $out[] = '<span class="regular-price">' . $product['formatted_regular_price'] . '</span> ';
            $out[] = '<span class="sale-price">' . $product['formatted_sale_price'] . '</span>';
        } else {
            $out[] = $product['formatted_current_price'];
        }
        $out[] = '</div>';
    }
}

$out[] = '</div>';
echo implode('', $out);
