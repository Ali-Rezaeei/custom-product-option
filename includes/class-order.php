<?php
if ( ! defined('ABSPATH') ) exit;

class CPO_Order {

    public function __construct() {
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_cart_item_data'], 10, 3);
        add_filter('woocommerce_get_item_data',       [$this, 'display_cart_item_data'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_order_item_meta'], 10, 4);

        add_filter('woocommerce_cart_item_name', [$this, 'inject_cart_details_data'], 10, 3);
    }

    public function add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        $fields = get_post_meta($product_id, CPO_META_KEY, true);
        if (empty($fields) || !is_array($fields)) return $cart_item_data;

        $posted_val  = isset($_POST['cpo_choice']) ? (array) $_POST['cpo_choice'] : [];
        $posted_group= isset($_POST['cpo_choice_group']) ? (array) $_POST['cpo_choice_group'] : [];

        $selected = [];
        $map = [];

        foreach ($fields as $field) {
            $f_key = $field['key'];
            $required = !empty($field['required']);
            $val_key  = isset($posted_val[$f_key]) ? sanitize_text_field($posted_val[$f_key]) : '';
            $grp_key  = isset($posted_group[$f_key]) ? sanitize_text_field($posted_group[$f_key]) : '';

            if ($required && $val_key === '') {
                wc_add_notice(sprintf(__('Please choose: %s', 'cpo'), esc_html($field['label'])), 'error');
            }

            if ($val_key === '') continue;

            $found = null; $group_label = ''; $value_label = ''; $color = ''; $image_id = 0;
            if (!empty($field['groups'])) {
                foreach ($field['groups'] as $g) {
                    if ($grp_key && $g['key'] !== $grp_key) continue; // اگر group_key ارسال شده، فیلترش کن
                    $group_label = $g['label'];
                    if (!empty($g['values'])) {
                        foreach ($g['values'] as $v) {
                            if ($v['key'] === $val_key) {
                                $found = $v; $value_label = $v['label'];
                                $color = !empty($v['color']) ? $v['color'] : '';
                                $image_id = !empty($v['image_id']) ? intval($v['image_id']) : 0;
                                break 2;
                            }
                        }
                    }
                }
            }
            if (!$found) continue;

            $selected[$f_key] = $val_key;
            $map[$f_key] = [
                'field_label' => $field['label'],
                'group_label' => $group_label,
                'value_label' => $value_label,
                'code'        => $val_key,
                'image_id'    => $image_id,
                'color'       => $color,
            ];
        }

        if (!empty($selected)) {
            $cart_item_data['cpo_selected'] = $selected;
            $cart_item_data['cpo_map'] = $map;
            $cart_item_data['unique_key'] = md5(serialize($selected) . microtime(true));
        }
        return $cart_item_data;
    }

    public function display_cart_item_data($item_data, $cart_item) {
        if (isset($cart_item['cpo_map'])) {
            foreach ($cart_item['cpo_map'] as $f_key => $info) {
                $label  = esc_html($info['field_label']);
                $glabel = $info['group_label'] ? ' — ' . esc_html($info['group_label']) : '';
                $vlabel = esc_html($info['value_label']);
                $code   = $info['code'] ? ' (کد: '. esc_html($info['code']) .')' : '';
                $display = $vlabel . $code;

                if (!empty($info['color'])) {
                    $display .= ' <span class="cpo-swatch-mini" style="display:inline-block;width:12px;height:12px;border:1px solid #ccc;vertical-align:middle;margin-left:6px;background:'.esc_attr($info['color']).'"></span>';
                } elseif (!empty($info['image_id'])) {
                    $thumb = wp_get_attachment_image_url(intval($info['image_id']), 'thumbnail');
                    if ($thumb) {
                        $display .= ' <img src="'.esc_url($thumb).'" alt="'.$vlabel.'" style="width:16px;height:16px;vertical-align:middle;margin-left:6px" />';
                    }
                }

                $item_data[] = [
                    'key'     => $label . $glabel,
                    'value'   => wp_kses_post($display),
                    'display' => wp_kses_post($display),
                ];
            }
        }
        return $item_data;
    }

    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['cpo_map'])) {
            foreach ($values['cpo_map'] as $f_key => $info) {
                $label = $info['field_label'] . ($info['group_label'] ? ' — ' . $info['group_label'] : '');
                $val   = $info['value_label'] . ($info['code'] ? ' (کد: '.$info['code'].')' : '');
                $item->add_meta_data($label, $val);
            }
        }
    }

    public function inject_cart_details_data($name, $cart_item, $cart_item_key) {
        if (isset($cart_item['cpo_map']) && is_array($cart_item['cpo_map'])) {
            $out = [];
            foreach ($cart_item['cpo_map'] as $f_key => $info) {
                $out[] = [
                    'field_label' => $info['field_label'],
                    'group_label' => $info['group_label'],
                    'value_label' => $info['value_label'],
                    'code'        => $info['code'],
                    'color'       => !empty($info['color']) ? $info['color'] : '',
                    'thumb'       => (!empty($info['image_id']) ? wp_get_attachment_image_url(intval($info['image_id']), 'thumbnail') : ''),
                ];
            }
            $json = wp_json_encode($out);
            $name .= '<span class="cpo-details-json" style="display:none" data-cpo=\''.esc_attr($json).'\'></span>';
        }
        return $name;
    }
}
