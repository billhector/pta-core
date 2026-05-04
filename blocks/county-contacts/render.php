<?php
defined('ABSPATH') || exit;

$contacts = \Pta\Core\Blocks\DataAccess::county_contacts();
$parts = [];

if (!empty($contacts['county_assessor_office_name']) ||
    !empty($contacts['county_assessor_office_phone']) ||
    !empty($contacts['county_assessor_office_website'])) {
    $a = '<div class="contact-item"><strong>Assessor Office:</strong><br>';
    if (!empty($contacts['county_assessor_office_name'])) {
        $a .= esc_html($contacts['county_assessor_office_name']) . '<br>';
    }
    if (!empty($contacts['county_assessor_office_phone'])) {
        $a .= 'Phone: ' . esc_html($contacts['county_assessor_office_phone']) . '<br>';
    }
    if (!empty($contacts['county_assessor_office_website'])) {
        $a .= '<a href="' . esc_url($contacts['county_assessor_office_website']) . '" target="_blank" rel="noopener">Visit Website</a>';
    }
    $a .= '</div>';
    $parts[] = $a;
}

if (!empty($contacts['county_appeal_board_name']) ||
    !empty($contacts['county_appeal_board_phone']) ||
    !empty($contacts['county_appeal_board_website'])) {
    $b = '<div class="contact-item"><strong>Appeal Board:</strong><br>';
    if (!empty($contacts['county_appeal_board_name'])) {
        $b .= esc_html($contacts['county_appeal_board_name']) . '<br>';
    }
    if (!empty($contacts['county_appeal_board_phone'])) {
        $b .= 'Phone: ' . esc_html($contacts['county_appeal_board_phone']) . '<br>';
    }
    if (!empty($contacts['county_appeal_board_website'])) {
        $b .= '<a href="' . esc_url($contacts['county_appeal_board_website']) . '" target="_blank" rel="noopener">Visit Website</a>';
    }
    $b .= '</div>';
    $parts[] = $b;
}

if (empty($parts)) {
    echo '<span class="county-contacts-error">Contact information not available.</span>';
    return;
}

$wrapper = function_exists('get_block_wrapper_attributes') ? get_block_wrapper_attributes() : '';
echo '<div ' . $wrapper . '>' . implode('', $parts) . '</div>';
