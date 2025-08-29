<?php

if ( ! defined('ABSPATH') ) exit;

class CPO_Admin {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post_product', [$this, 'save_product_meta'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_notices', [$this, 'admin_notices']);
    }

    public function add_meta_box() {
        add_meta_box(
            'cpo_modal_box_v2',
            __('گزینه‌های سفارشی (مودال + تب‌ها)', 'cpo') . '<span class="woocommerce-help-tip" tabindex="1" data-tip="اینجا می‌توانید فیلدها، گروه‌ها (تب‌ها) و رنگ/تصویرها را برای محصول تعریف کنید. مشتری موقع خرید باید یکی از آنها را انتخاب کند"></span>' ,
            [$this, 'render_meta_box'],
            'product',
            'normal',
            'default'
        );
    }

    public function enqueue_assets($hook) {
        global $post;
        if ( ($hook === 'post-new.php' || $hook === 'post.php') && $post && $post->post_type === 'product' ) {
            wp_enqueue_media();
            wp_enqueue_style('dashicons');
            wp_enqueue_style('cpo-admin', CPO_BASE_URL . 'assets/css/admin.css', [], '1.3.0');
            wp_enqueue_script('cpo-admin', CPO_BASE_URL . 'assets/js/admin.js', ['jquery'], '1.3.0', true);
        }
    }

    public function admin_notices() {
        if ( ! empty($_GET['cpo_xor_error']) ) {
            echo '<div class="notice notice-error"><p>'
                . esc_html__('هر مقدار باید یا تصویر داشته باشد یا کد رنگ (نه هر دو). یک یا چند مقدار ذخیره نشد.', 'cpo')
                . '</p></div>';
        }
        if ( ! empty($_GET['cpo_key_error']) ) {
            echo '<div class="notice notice-error"><p>'
                . esc_html__('کلیدها باید فقط حروف انگلیسی، عدد، خط‌تیره (-) یا زیرخط (_) باشند و نمی‌توانند خالی باشند. برخی فیلدها/گروه‌ها/مقدارها ذخیره نشدند.', 'cpo')
                . '</p></div>';
        }
    }

    public function render_meta_box($post) {
        $fields = get_post_meta($post->ID, CPO_META_KEY, true);
        if (!is_array($fields)) $fields = [];
        wp_nonce_field('cpo_modal_save_v2', 'cpo_modal_nonce_v2');

        $key_pattern = '[A-Za-z0-9_-]+';
        ?>
        <div id="cpo-admin">
            <p class="desc"><?php echo esc_html__('فیلدها را اضافه کنید. داخل هر فیلد، گروه‌ها (تب‌ها) و داخل هر گروه مقدارها (رنگ/تصویر) را اضافه کنید. قیمت حذف شده است.', 'cpo'); ?></p>

            <div id="cpo-fields">
                <?php if (empty($fields)) { $fields = []; } ?>
                <?php foreach ($fields as $fi => $field):
                    $f_label = isset($field['label']) ? esc_attr($field['label']) : '';
                    $f_key   = isset($field['key']) ? esc_attr($field['key']) : '';
                    $f_req   = !empty($field['required']) ? 1 : 0;
                    $groups  = !empty($field['groups']) && is_array($field['groups']) ? $field['groups'] : [];
                    ?>
                    <div class="cpo-field" data-fi="<?php echo intval($fi); ?>">
                        <div class="cpo-field-head">
                            <div>
                                <label><?php esc_html_e('عنوان فیلد', 'cpo'); ?></label>
                                <input type="text" name="cpo[fields][<?php echo intval($fi); ?>][label]" value="<?php echo $f_label; ?>" required />
                            </div>
                            <div>
                                <label><?php esc_html_e('کلید فیلد (انگلیسی)', 'cpo'); ?></label>
                                <input type="text" class="cpo-key" pattern="<?php echo esc_attr($key_pattern); ?>" title="<?php esc_attr_e('فقط a-z, 0-9, -, _', 'cpo'); ?>" name="cpo[fields][<?php echo intval($fi); ?>][key]" value="<?php echo $f_key; ?>" required  placeholder="code-100"/>
                                <small class="cpo-error"></small>
                            </div>
                            <div class="cpo-inline">
                                <label>
                                    <input type="checkbox" name="cpo[fields][<?php echo intval($fi); ?>][required]" value="1" <?php checked($f_req,1); ?> />
                                    <?php esc_html_e('اجباری', 'cpo'); ?>
                                </label>
                            </div>
                            <button type="button" class="cpo-icon-btn cpo-remove-field" aria-label="<?php esc_attr_e('حذف فیلد', 'cpo'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>

                        <div class="cpo-groups">
                            <?php foreach ($groups as $gi => $group):
                                $g_label = isset($group['label']) ? esc_attr($group['label']) : '';
                                $g_key   = isset($group['key']) ? esc_attr($group['key']) : '';
                                $values  = !empty($group['values']) && is_array($group['values']) ? $group['values'] : [];
                                ?>
                                <div class="cpo-group" data-gi="<?php echo intval($gi); ?>">
                                    <div class="cpo-group-head">
                                        <div>
                                            <label><?php esc_html_e('لیبل گروه (تب)', 'cpo'); ?></label>
                                            <input type="text" name="cpo[fields][<?php echo intval($fi); ?>][groups][<?php echo intval($gi); ?>][label]" value="<?php echo $g_label; ?>" required />
                                        </div>
                                        <div>
                                            <label><?php esc_html_e('کلید گروه (انگلیسی)', 'cpo'); ?></label>
                                            <input type="text" class="cpo-key" placeholder="code-100"  name="cpo[fields][<?php echo intval($fi); ?>][groups][<?php echo intval($gi); ?>][key]" value="<?php echo $g_key; ?>" required />
                                            <small class="cpo-error"></small>
                                        </div>
                                        <button type="button" class="cpo-icon-btn cpo-remove-group" aria-label="<?php esc_attr_e('حذف گروه', 'cpo'); ?>">
                                            <span class="dashicons dashicons-no-alt"></span>
                                        </button>
                                    </div>

                                    <div class="cpo-values">
                                        <table class="widefat fixed">
                                            <thead>
                                            <tr>
                                                <th><?php esc_html_e('لیبل مقدار', 'cpo'); ?></th>
                                                <th><?php esc_html_e('کلید مقدار/کُد (انگلیسی)', 'cpo'); ?></th>
                                                <th><?php esc_html_e('تصویر', 'cpo'); ?></th>
                                                <th><?php esc_html_e('کد رنگ (HEX)', 'cpo'); ?></th>
                                                <th><?php esc_html_e('عملیات', 'cpo'); ?></th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($values as $vi => $val):
                                                $v_label = isset($val['label']) ? esc_attr($val['label']) : '';
                                                $v_key   = isset($val['key']) ? esc_attr($val['key']) : '';
                                                $img_id  = !empty($val['image_id']) ? intval($val['image_id']) : 0;
                                                $img_url = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : '';
                                                $color   = isset($val['color']) ? esc_attr($val['color']) : '';
                                                ?>
                                                <tr class="cpo-value" data-vi="<?php echo intval($vi); ?>">
                                                    <td><input type="text" name="cpo[fields][<?php echo intval($fi); ?>][groups][<?php echo intval($gi); ?>][values][<?php echo intval($vi); ?>][label]" value="<?php echo $v_label; ?>" required /></td>
                                                    <td>
                                                        <input type="text" class="cpo-key" pattern="<?php echo esc_attr($key_pattern); ?>" title="<?php esc_attr_e('فقط a-z, 0-9, -, _', 'cpo'); ?>" name="cpo[fields][<?php echo intval($fi); ?>][groups][<?php echo intval($gi); ?>][values][<?php echo intval($vi); ?>][key]" value="<?php echo $v_key; ?>" required placeholder="code-100" />
                                                        <small class="cpo-error"></small>
                                                    </td>
                                                    <td>
                                                        <div class="cpo-image">
                                                            <input type="hidden" class="cpo-image-id" name="cpo[fields][<?php echo intval($fi); ?>][groups][<?php echo intval($gi); ?>][values][<?php echo intval($vi); ?>][image_id]" value="<?php echo $img_id ? intval($img_id) : ''; ?>" />
                                                            <button type="button" class="button cpo-upload"><?php esc_html_e('انتخاب تصویر', 'cpo'); ?></button>
                                                            <div class="cpo-thumb"><?php if ($img_url) echo '<img src="'.esc_url($img_url).'" />'; ?></div>
                                                        </div>
                                                    </td>
                                                    <td><input type="text" class="cpo-color" name="cpo[fields][<?php echo intval($fi); ?>][groups][<?php echo intval($gi); ?>][values][<?php echo intval($vi); ?>][color]" value="<?php echo $color; ?>" placeholder="#RRGGBB" /></td>
                                                    <td>
                                                        <button type="button" class="cpo-icon-btn cpo-remove-value" aria-label="<?php esc_attr_e('حذف مقدار', 'cpo'); ?>">
                                                            <span class="dashicons dashicons-minus"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <p><button type="button" class="button cpo-add-value" data-fi="<?php echo intval($fi); ?>" data-gi="<?php echo intval($gi); ?>"><?php esc_html_e('افزودن مقدار', 'cpo'); ?></button></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <p><button type="button" class="button cpo-add-group" data-fi="<?php echo intval($fi); ?>"><?php esc_html_e('افزودن گروه (تب)', 'cpo'); ?></button></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <p><button type="button" class="button button-primary" id="cpo-add-field"><?php esc_html_e('افزودن فیلد', 'cpo'); ?></button></p>
            <p class="hint"><?php esc_html_e('هر مقدار باید یا تصویر داشته باشد یا کد رنگ (نه هر دو). کلیدها فقط انگلیسی/عدد/-/_ باشند.', 'cpo'); ?></p>
        </div>
        <?php
    }

    public function save_product_meta($post_id, $post) {
        if (!isset($_POST['cpo_modal_nonce_v2']) || !wp_verify_nonce($_POST['cpo_modal_nonce_v2'], 'cpo_modal_save_v2')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $input = isset($_POST['cpo']) ? $_POST['cpo'] : [];
        $fields_in = isset($input['fields']) ? $input['fields'] : [];
        $fields = [];
        $xor_error = false;
        $key_error = false;

        $valid_key = function($k){ return (is_string($k) && preg_match('/^[a-z0-9_-]+$/i', $k)); };

        foreach ((array)$fields_in as $fi => $f) {
            $label = isset($f['label']) ? sanitize_text_field($f['label']) : '';
            $key   = isset($f['key']) ? sanitize_text_field($f['key']) : '';
            $req   = !empty($f['required']) ? 1 : 0;

            if ($label === '' || $key === '' || ! $valid_key($key)) { $key_error = true; continue; }

            $groups = [];
            if (!empty($f['groups']) && is_array($f['groups'])) {
                foreach ($f['groups'] as $gi => $g) {
                    $g_label = isset($g['label']) ? sanitize_text_field($g['label']) : '';
                    $g_key   = isset($g['key']) ? sanitize_text_field($g['key']) : '';

                    if ($g_label === '' || $g_key === '' || ! $valid_key($g_key)) { $key_error = true; continue; }

                    $vals = [];
                    if (!empty($g['values']) && is_array($g['values'])) {
                        foreach ($g['values'] as $vi => $v) {
                            $v_label = isset($v['label']) ? sanitize_text_field($v['label']) : '';
                            $v_key   = isset($v['key']) ? sanitize_text_field($v['key']) : '';
                            $img_id  = !empty($v['image_id']) ? intval($v['image_id']) : 0;
                            $color   = isset($v['color']) ? sanitize_text_field($v['color']) : '';

                            if ($v_label === '' || $v_key === '' || ! $valid_key($v_key)) { $key_error = true; continue; }

                            if ($color && function_exists('sanitize_hex_color')) {
                                $color = sanitize_hex_color($color);
                                if (!$color) $color = '';
                            }

                            $has_img   = $img_id > 0;
                            $has_color = $color !== '';
                            if ($has_img && $has_color) { $xor_error = true; continue; }
                            if (!$has_img && !$has_color) { continue; }

                            $vals[] = [
                                'label'    => $v_label,
                                'key'      => $v_key,
                                'image_id' => $has_img ? $img_id : 0,
                                'color'    => $has_color ? $color : '',
                            ];
                        }
                    }

                    $groups[] = [
                        'label'  => $g_label,
                        'key'    => $g_key,
                        'values' => $vals,
                    ];
                }
            }

            $fields[] = [
                'label'    => $label,
                'key'      => $key,
                'required' => $req,
                'groups'   => $groups,
            ];
        }

        if (!empty($fields)) {
            update_post_meta($post_id, CPO_META_KEY, $fields);
        } else {
            delete_post_meta($post_id, CPO_META_KEY);
        }

        // ارجاع با پیام‌ها
        if ($xor_error || $key_error) {
            add_filter('redirect_post_location', function($location) use ($xor_error, $key_error) {
                if ($xor_error) $location = add_query_arg('cpo_xor_error', 1, $location);
                if ($key_error) $location = add_query_arg('cpo_key_error', 1, $location);
                return $location;
            });
        }
    }
}
