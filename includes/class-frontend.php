<?php
if ( ! defined('ABSPATH') ) exit;

class CPO_Frontend {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('woocommerce_before_add_to_cart_button', [$this, 'render_field_rows'], 5);
        add_action('woocommerce_before_add_to_cart_button', [$this, 'render_hidden_inputs'], 6);
        add_action('wp_footer', [$this, 'render_modal_markup']);
    }

    public function enqueue_assets() {
        if (is_product()) {
            wp_enqueue_style('cpo-frontend', CPO_BASE_URL . 'assets/css/frontend.css', [], '1.2.3');
            wp_enqueue_script('cpo-frontend', CPO_BASE_URL . 'assets/js/frontend.js', ['jquery'], '1.2.3', true);
        }
    }
    public function render_field_rows() {
        global $product;
        if (!$product) return;

        $fields = get_post_meta($product->get_id(), CPO_META_KEY, true);
        if (empty($fields) || !is_array($fields)) return;

        echo '<div class="cpo-inline-panel">';
        foreach ($fields as $field) {
            $f_label  = esc_html($field['label']);
            $f_key    = esc_attr($field['key']);
            echo '<div class="cpo-inline-row" data-key="'.$f_key.'">';
            echo '  <button type="button" class="button cpo-open-modal" data-key="'.$f_key.'" data-title="'.$f_label.'" >'.$f_label.'</button>';
            echo '  <div class="cpo-preview">';
            echo '      <span class="cpo-preview-swatch" aria-hidden="true"></span>';
            echo '      <span class="cpo-preview-code">'.esc_html__('کد: انتخاب کنید', 'cpo').'</span>';
            echo '  </div>';
            echo '</div>';
        }
        echo '</div>';
    }

    public function render_hidden_inputs() {
        global $product;
        if (!$product) return;
        $fields = get_post_meta($product->get_id(), CPO_META_KEY, true);
        if (empty($fields) || !is_array($fields)) return;

        echo '<div class="cpo-hidden">';
        $safe = [];
        foreach ($fields as $f) {
            $f_key = esc_attr($f['key']);
            echo '<input type="hidden" name="cpo_choice['.$f_key.']" value="" />';
            echo '<input type="hidden" name="cpo_choice_group['.$f_key.']" value="" />';
            $gs = [];
            if (!empty($f['groups'])) {
                foreach ($f['groups'] as $g) {
                    $vals = [];
                    if (!empty($g['values'])) {
                        foreach ($g['values'] as $v) {
                            $vals[] = [
                                'label' => $v['label'],
                                'key'   => $v['key'],
                                'thumb' => !empty($v['image_id']) ? wp_get_attachment_image_url(intval($v['image_id']), 'thumbnail') : '',
                                'color' => !empty($v['color']) ? $v['color'] : '',
                            ];
                        }
                    }
                    $gs[] = [
                        'label'  => $g['label'],
                        'key'    => $g['key'],
                        'values' => $vals,
                    ];
                }
            }
            $safe[$f['key']] = [
                'label'    => $f['label'],
                'required' => !empty($f['required']) ? 1 : 0,
                'groups'   => $gs,
            ];
        }
        echo '<script type="application/json" id="cpo-data-json">'.wp_json_encode($safe).'</script>';
        echo '</div>';
    }

    public function render_modal_markup() {
        if ( ! is_product() ) return;
        echo '<div class="cpo-modal" style="display:none" aria-hidden="true">'
            .   '<div class="cpo-modal-backdrop"></div>'
            .   '<div class="cpo-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="cpoModalTitle">'
            .     '<div class="cpo-modal-header">'
            .       '<h3 id="cpoModalTitle"></h3>'
            .       '<button type="button" class="cpo-close" aria-label="بستن">X</button>'
            .     '</div>'
            .     '<div class="cpo-modal-tabs"></div>'
            .     '<div class="cpo-modal-body"><div class="cpo-options-grid"></div></div>'
            .   '</div>'
            . '</div>'
            . '<div class="cpo-image-preview" style="display:none">'
            .   '<div class="cpo-image-backdrop"></div>'
            .   '<button type="button" class="cpo-image-close" aria-label="بستن">X</button>'
            .   '<img src="" alt="">'
            . '</div>';
    }
}
