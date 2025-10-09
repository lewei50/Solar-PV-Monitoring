<?php
/**
 * Plugin Name: IAMMETER
 * Plugin URI: http://wordpress.org/plugins/iammeter/
 * Description: Get meter data from IAMMETER Cloud and display it in WordPress posts. Supports single-phase and three-phase meters, configurable unlimited number of meter devices.
 * Version: 1.0.1
 * Author: IAMMETER Team
 * Author URI: https://www.iammeter.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('IAMMETER_VERSION', '1.0.1');
define('IAMMETER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IAMMETER_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Main plugin class
 */
class IAMMETERPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Register shortcode
        add_shortcode('iammeter', array($this, 'render_shortcode'));
        
        // Add styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        
        // Add admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // Add AJAX handlers
        add_action('wp_ajax_iammeter_dismiss_notice', array($this, 'dismiss_notice'));
    }
    
    /**
     * Execute when plugin is activated
     */
    public function activate() {
        // Check if options already exist, if not set default options
        $existing_options = get_option('iammeter_options');
        if ($existing_options === false) {
            // Set default options - new dynamic structure
            $default_options = array(
                'cache_duration' => 300, // 5 minutes cache
                'debug_mode' => false, // Debug mode default off
                'phase_names' => array(
                    'phase1' => 'Phase A',
                    'phase2' => 'Phase B', 
                    'phase3' => 'Phase C'
                ),
                'meters' => array(
                    array(
                        'name' => 'Solar Meter',
                        'sn' => '33U8FSRE', 
                        'token' => '',
                        'offset' => 0,
                        'color' => '#4CAF50',
                        'enabled' => true,
                        'type' => 'single' // single or three_phase
                    ),
                    array(
                        'name' => 'Grid Meter',
                        'sn' => 'C39394F1',
                        'token' => '',
                        'offset' => 0,
                        'color' => '#2196F3',
                        'enabled' => true,
                        'type' => 'three_phase'
                    )
                )
            );
            
            add_option('iammeter_options', $default_options);
        } else {
            // If upgrading from old version, migrate data
            $this->migrate_old_options($existing_options);
        }
    }
    
    /**
     * Migrate old version options to new structure
     */
    private function migrate_old_options($old_options) {
        // Check if it's already the new structure
        if (isset($old_options['meters']) && is_array($old_options['meters'])) {
            // Even if it's new structure, check if new fields are missing
            $updated = false;
            
            // Add type field and phase names for each meter (if not exists)
            foreach ($old_options['meters'] as $index => $meter) {
                if (!isset($meter['type'])) {
                    $old_options['meters'][$index]['type'] = 'single'; // Default to single phase
                    $updated = true;
                }
                if (!isset($meter['phase_names'])) {
                    $old_options['meters'][$index]['phase_names'] = array(
                        'phase1' => 'Phase A',
                        'phase2' => 'Phase B',
                        'phase3' => 'Phase C'
                    );
                    $updated = true;
                }
            }
            
            // If updated, save options
            if ($updated) {
                update_option('iammeter_options', $old_options);
            }
            
            return; // Already new structure, migration complete
        }
        
        // Build new options structure (migrate from old legacy format)
        $new_options = array(
            'cache_duration' => isset($old_options['cache_duration']) ? $old_options['cache_duration'] : 300,
            'debug_mode' => isset($old_options['debug_mode']) ? $old_options['debug_mode'] : false,
            'meters' => array()
        );
        
        // Migrate solar meter data
        if (isset($old_options['solar_token']) || isset($old_options['solar_sn'])) {
            $new_options['meters'][] = array(
                'name' => 'Solar Meter',
                'sn' => isset($old_options['solar_sn']) ? $old_options['solar_sn'] : '33U8FSRE',
                'token' => isset($old_options['solar_token']) ? $old_options['solar_token'] : '',
                'offset' => isset($old_options['solar_offset']) ? $old_options['solar_offset'] : 0,
                'color' => '#4CAF50',
                'enabled' => isset($old_options['show_solar']) ? $old_options['show_solar'] : true,
                'type' => 'single', // Default to single phase
                'phase_names' => array(
                    'phase1' => 'Phase A',
                    'phase2' => 'Phase B',
                    'phase3' => 'Phase C'
                )
            );
        }
        
        // Migrate grid meter data
        if (isset($old_options['grid_token']) || isset($old_options['grid_sn'])) {
            $new_options['meters'][] = array(
                'name' => 'Grid Meter',
                'sn' => isset($old_options['grid_sn']) ? $old_options['grid_sn'] : 'C39394F1',
                'token' => isset($old_options['grid_token']) ? $old_options['grid_token'] : '',
                'offset' => isset($old_options['grid_offset']) ? $old_options['grid_offset'] : 0,
                'color' => '#2196F3',
                'enabled' => isset($old_options['show_grid']) ? $old_options['show_grid'] : true,
                'type' => 'three_phase', // Assume grid meter is three phase
                'phase_names' => array(
                    'phase1' => 'Phase A',
                    'phase2' => 'Phase B',
                    'phase3' => 'Phase C'
                )
            );
        }
        
        // Update options
        update_option('iammeter_options', $new_options);
    }
    
    /**
     * Execute when plugin is deactivated
     */
    public function deactivate() {
        // Clear cache
        wp_cache_delete('iammeter_solar_data');
        wp_cache_delete('iammeter_grid_data');
    }
    
    /**
     * Safely get plugin options
     */
    private function get_plugin_options() {
        $options = get_option('iammeter_options');
        
        // If options don't exist, return default options
        if ($options === false) {
            return array(
                'cache_duration' => 300,
                'debug_mode' => false,
                'meters' => array(
                    array(
                        'name' => 'Solar Meter',
                        'sn' => '11223344',
                        'token' => '',
                        'offset' => 0,
                        'color' => '#4CAF50',
                        'enabled' => true,
                        'type' => 'single',
                        'phase_names' => array(
                            'phase1' => 'Phase A',
                            'phase2' => 'Phase B',
                            'phase3' => 'Phase C'
                        )
                    ),
                    array(
                        'name' => 'Grid Meter',
                        'sn' => '22334455',
                        'token' => '',
                        'offset' => 0,
                        'color' => '#2196F3',
                        'enabled' => true,
                        'type' => 'three_phase',
                        'phase_names' => array(
                            'phase1' => 'Phase A',
                            'phase2' => 'Phase B',
                            'phase3' => 'Phase C'
                        )
                    )
                )
            );
        }
        
        return $options;
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        // Only show on admin pages
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if configuration reminder needs to be shown
        $options = get_option('iammeter_options');
        $show_notice = false;
        
        if ($options === false || 
            (empty($options['solar_token']) && isset($options['show_solar']) && $options['show_solar']) ||
            (empty($options['grid_token']) && isset($options['show_grid']) && $options['show_grid'])) {
            $show_notice = true;
        }
        
        // Check if user has already hidden this notification
        if ($show_notice && !get_user_meta(get_current_user_id(), 'iammeter_hide_config_notice', true)) {
            ?>
            <div class="notice notice-warning is-dismissible" data-notice="iammeter-config">
                <h3>🔌 IAMMETER Plugin Configuration Required</h3>
                <p>
                    Thank you for installing the IAMMETER plugin! To display meter data, you need to configure the API Token.
                </p>
                <p>
                    <a href="<?php echo admin_url('options-general.php?page=iammeter'); ?>" class="button button-primary">
                        Configure Now
                    </a>
                    <a href="#" class="button iammeter-dismiss-notice" data-nonce="<?php echo wp_create_nonce('iammeter_dismiss_notice'); ?>">
                        Remind Later
                    </a>
                </p>
            </div>
            <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '.iammeter-dismiss-notice', function(e) {
                    e.preventDefault();
                    var nonce = $(this).data('nonce');
                    $.post(ajaxurl, {
                        action: 'iammeter_dismiss_notice',
                        nonce: nonce
                    });
                    $(this).closest('.notice').fadeOut();
                });
            });
            </script>
            <?php
        }
    }
    
    /**
     * Handle notification hide AJAX request
     */
    public function dismiss_notice() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'iammeter_dismiss_notice')) {
            wp_die('Security verification failed');
        }
        
        // Verify user permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Set user meta to hide notification for 7 days
        update_user_meta(get_current_user_id(), 'iammeter_hide_config_notice', time() + (7 * 24 * 60 * 60));
        
        wp_die(); // End AJAX request
    }
    
    /**
     * Test API connection AJAX handler
     */
    public function test_api_connection() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'iammeter_test_connection')) {
            wp_send_json_error('Security verification failed');
            return;
        }
        
        // Verify user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        // Get parameters
        $sn = sanitize_text_field($_POST['sn']);
        $token = sanitize_text_field($_POST['token']);
        $name = sanitize_text_field($_POST['name']);
        
        if (empty($sn) || empty($token)) {
            wp_send_json_error('Serial number and Token cannot be empty');
            return;
        }
        
        // Test API connection (without cache)
        $cache_key = 'test_' . md5($sn . $token . time());
        $result = $this->get_meter_data($sn, $token, $cache_key);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'api_success' => true,
                'meter_data' => $result['data'],
                'message' => sprintf('Meter "%s" connected successfully', $name)
            ));
        } else {
            wp_send_json_success(array(
                'api_success' => false,
                'error' => $result['error'],
                'debug' => isset($result['debug']) ? $result['debug'] : null,
                'message' => sprintf('Meter "%s" connection failed', $name)
            ));
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'IAMMETER Settings',
            'IAMMETER',
            'manage_options',
            'iammeter',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('iammeter_options', 'iammeter_options', array($this, 'sanitize_options'));
        
        // Add AJAX handlers
        add_action('wp_ajax_iammeter_add_meter', array($this, 'add_meter'));
        add_action('wp_ajax_iammeter_delete_meter', array($this, 'delete_meter'));
        add_action('wp_ajax_iammeter_test_connection', array($this, 'test_api_connection'));
        
        add_settings_section(
            'iammeter_main',
            'Meter Management',
            array($this, 'section_callback'),
            'iammeter'
        );
        
        // Meter list settings
        add_settings_field(
            'meters_list',
            'Meter Configuration',
            array($this, 'meters_list_callback'),
            'iammeter',
            'iammeter_main'
        );
        
        // Cache settings
        add_settings_field(
            'cache_duration',
            'Cache Duration (seconds)',
            array($this, 'cache_duration_callback'),
            'iammeter',
            'iammeter_main'
        );
        
        // Debug mode settings
        add_settings_field(
            'debug_mode',
            'Debug Mode',
            array($this, 'debug_mode_callback'),
            'iammeter',
            'iammeter_main'
        );
    }
    
    /**
     * Settings section callback
     */
    public function section_callback() {
        echo '<div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-bottom: 20px;">';
        echo '<h4 style="margin-top: 0; color: #495057;">📋 Configuration Guide</h4>';
        echo '<ol style="margin-bottom: 10px;">';
        echo '<li>Visit <a href="https://www.iammeter.com/" target="_blank">IAMMETER Cloud</a> to get your API Token</li>';
        echo '<li>Add your meter devices, set name, serial number and Token for each meter</li>';
        echo '<li>Use the "Test Connection" button to verify the configuration is correct</li>';
        echo '<li>If needed, you can set offset to calibrate readings and customize colors</li>';
        echo '<li>After saving settings, use the <code>[iammeter]</code> shortcode in posts to display data</li>';
        echo '</ol>';
        
        // Debug mode tips
        $options = $this->get_plugin_options();
        $debug_enabled = isset($options['debug_mode']) ? $options['debug_mode'] : false;
        
        if ($debug_enabled) {
            echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px; padding: 10px; margin-top: 10px;">';
            echo '<strong>🐛 Plugin debug mode is enabled</strong><br>';
            echo 'When API errors occur, detailed debugging information will be displayed on the frontend to help diagnose issues (only visible to administrators).';
            echo '</div>';
        } else {
            echo '<div style="background: #e7f3ff; border: 1px solid #b8daff; border-radius: 3px; padding: 10px; margin-top: 10px;">';
            echo '<strong>💡 Debug Tips</strong><br>';
            echo 'If you encounter API connection issues, you can enable debug mode below to get detailed error information.';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Meter list callback function
     */
    public function meters_list_callback() {
        $options = $this->get_plugin_options();
        $meters = isset($options['meters']) ? $options['meters'] : array();
        ?>
        <div id="iammeter-meters-container">
            <style>
            .meter-item {
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 15px;
                margin-bottom: 15px;
                background: #fff;
            }
            .meter-item h4 {
                margin-top: 0;
                color: #333;
            }
            .meter-field {
                margin-bottom: 10px;
            }
            .meter-field label {
                display: inline-block;
                width: 120px;
                font-weight: bold;
            }
            .meter-field input[type="text"], 
            .meter-field input[type="password"],
            .meter-field input[type="number"] {
                width: 250px;
                padding: 5px;
            }
            .meter-field input[type="color"] {
                width: 50px;
                height: 30px;
            }
            .delete-meter {
                color: #dc3545;
                text-decoration: none;
                float: right;
                font-weight: bold;
            }
            .delete-meter:hover {
                color: #c82333;
            }
            #add-meter-btn {
                background: #007cba;
                color: white;
                padding: 10px 15px;
                border: none;
                border-radius: 3px;
                cursor: pointer;
                margin-top: 10px;
            }
            #add-meter-btn:hover {
                background: #005a87;
            }
            </style>
            
            <?php foreach ($meters as $index => $meter): ?>
            <div class="meter-item" data-index="<?php echo $index; ?>">
                <h4>
                    <?php echo esc_html($meter['name']); ?>
                    <a href="#" class="delete-meter" data-index="<?php echo $index; ?>">✕ Delete</a>
                </h4>
                
                <div class="meter-field">
                    <label>Name:</label>
                    <input type="text" name="iammeter_options[meters][<?php echo $index; ?>][name]" 
                           value="<?php echo esc_attr($meter['name']); ?>" />
                </div>
                
                <div class="meter-field">
                    <label>Serial Number:</label>
                    <input type="text" name="iammeter_options[meters][<?php echo $index; ?>][sn]" 
                           value="<?php echo esc_attr($meter['sn']); ?>" />
                </div>
                
                <div class="meter-field">
                    <label>API Token:</label>
                    <input type="password" name="iammeter_options[meters][<?php echo $index; ?>][token]" 
                           value="<?php echo esc_attr($meter['token']); ?>" />
                </div>
                
                <div class="meter-field">
                    <label>Offset (kWh):</label>
                    <input type="number" step="0.01" name="iammeter_options[meters][<?php echo $index; ?>][offset]" 
                           value="<?php echo esc_attr($meter['offset']); ?>" />
                </div>
                
                <div class="meter-field">
                    <label>Color:</label>
                    <input type="color" name="iammeter_options[meters][<?php echo $index; ?>][color]" 
                           value="<?php echo esc_attr($meter['color']); ?>" />
                </div>
                
                <div class="meter-field">
                    <label>Meter Type:</label>
                    <select name="iammeter_options[meters][<?php echo $index; ?>][type]" style="width: 120px; padding: 5px;" onchange="togglePhaseNames(<?php echo $index; ?>)">
                        <option value="single" <?php selected(isset($meter['type']) ? $meter['type'] : 'single', 'single'); ?>>Single Phase</option>
                        <option value="three_phase" <?php selected(isset($meter['type']) ? $meter['type'] : 'single', 'three_phase'); ?>>Three Phase</option>
                    </select>
                </div>
                
                <!-- Three-phase meter phase name configuration -->
                <div class="phase-names-config" id="phase-names-<?php echo $index; ?>" style="<?php echo (isset($meter['type']) && $meter['type'] === 'three_phase') ? '' : 'display: none;'; ?> background: #f9f9f9; padding: 10px; border: 1px solid #ddd; border-radius: 3px; margin: 10px 0;">
                    <label style="font-weight: bold; margin-bottom: 8px; display: block;">Three Phase Name Configuration:</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <div>
                            <label style="font-size: 12px; color: #666;">Phase 1:</label>
                            <input type="text" name="iammeter_options[meters][<?php echo $index; ?>][phase_names][phase1]" 
                                   value="<?php echo esc_attr(isset($meter['phase_names']['phase1']) ? $meter['phase_names']['phase1'] : 'Phase A'); ?>" 
                                   style="width: 80px; padding: 3px;" placeholder="Phase A" />
                        </div>
                        <div>
                            <label style="font-size: 12px; color: #666;">Phase 2:</label>
                            <input type="text" name="iammeter_options[meters][<?php echo $index; ?>][phase_names][phase2]" 
                                   value="<?php echo esc_attr(isset($meter['phase_names']['phase2']) ? $meter['phase_names']['phase2'] : 'Phase B'); ?>" 
                                   style="width: 80px; padding: 3px;" placeholder="Phase B" />
                        </div>
                        <div>
                            <label style="font-size: 12px; color: #666;">Phase 3:</label>
                            <input type="text" name="iammeter_options[meters][<?php echo $index; ?>][phase_names][phase3]" 
                                   value="<?php echo esc_attr(isset($meter['phase_names']['phase3']) ? $meter['phase_names']['phase3'] : 'Phase C'); ?>" 
                                   style="width: 80px; padding: 3px;" placeholder="Phase C" />
                        </div>
                    </div>
                    <small style="color: #666; font-style: italic;">For example: Grid, Load, Solar</small>
                </div>
                
                <div class="meter-field">
                    <label>
                        <input type="checkbox" name="iammeter_options[meters][<?php echo $index; ?>][enabled]" 
                               value="1" <?php checked(isset($meter['enabled']) ? $meter['enabled'] : true, true); ?> />
                        Enable this meter
                    </label>
                </div>
                
                <div class="meter-field">
                    <button type="button" class="test-connection" data-index="<?php echo $index; ?>" 
                            style="background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 3px; cursor: pointer;">
                        🔗 Test Connection
                    </button>
                    <div class="test-result" id="test-result-<?php echo $index; ?>" style="margin-top: 5px;"></div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <button type="button" id="add-meter-btn">+ Add New Meter</button>
        </div>
        
        <script>
        // Control the display/hide of phase name configuration
        function togglePhaseNames(index) {
            var select = document.querySelector('select[name="iammeter_options[meters][' + index + '][type]"]');
            var phaseDiv = document.getElementById('phase-names-' + index);
            if (select && phaseDiv) {
                if (select.value === 'three_phase') {
                    phaseDiv.style.display = 'block';
                } else {
                    phaseDiv.style.display = 'none';
                }
            }
        }
        
        jQuery(document).ready(function($) {
            // Add new meter
            $('#add-meter-btn').click(function() {
                var container = $('#iammeter-meters-container');
                var index = $('.meter-item').length;
                var colors = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#00BCD4'];
                var defaultColor = colors[index % colors.length];
                
                var html = '<div class="meter-item" data-index="' + index + '">' +
                    '<h4>New Meter <a href="#" class="delete-meter" data-index="' + index + '">✕ Delete</a></h4>' +
                    '<div class="meter-field">' +
                        '<label>Name:</label>' +
                        '<input type="text" name="iammeter_options[meters][' + index + '][name]" value="New Meter" />' +
                    '</div>' +
                    '<div class="meter-field">' +
                        '<label>Serial Number:</label>' +
                        '<input type="text" name="iammeter_options[meters][' + index + '][sn]" value="" />' +
                    '</div>' +
                    '<div class="meter-field">' +
                        '<label>API Token:</label>' +
                        '<input type="password" name="iammeter_options[meters][' + index + '][token]" value="" />' +
                    '</div>' +
                    '<div class="meter-field">' +
                        '<label>Offset (kWh):</label>' +
                        '<input type="number" step="0.01" name="iammeter_options[meters][' + index + '][offset]" value="0" />' +
                    '</div>' +
                    '<div class="meter-field">' +
                        '<label>Color:</label>' +
                        '<input type="color" name="iammeter_options[meters][' + index + '][color]" value="' + defaultColor + '" />' +
                    '</div>' +
                    '<div class="meter-field">' +
                        '<label>Meter Type:</label>' +
                        '<select name="iammeter_options[meters][' + index + '][type]" style="width: 120px; padding: 5px;" onchange="togglePhaseNames(' + index + ')">' +
                            '<option value="single">Single Phase</option>' +
                            '<option value="three_phase">Three Phase</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="phase-names-config" id="phase-names-' + index + '" style="display: none; background: #f9f9f9; padding: 10px; border: 1px solid #ddd; border-radius: 3px; margin: 10px 0;">' +
                        '<label style="font-weight: bold; margin-bottom: 8px; display: block;">Three Phase Name Configuration:</label>' +
                        '<div style="display: flex; gap: 10px; align-items: center;">' +
                            '<div>' +
                                '<label style="font-size: 12px; color: #666;">Phase 1:</label>' +
                                '<input type="text" name="iammeter_options[meters][' + index + '][phase_names][phase1]" value="Phase A" style="width: 80px; padding: 3px;" placeholder="Phase A" />' +
                            '</div>' +
                            '<div>' +
                                '<label style="font-size: 12px; color: #666;">Phase 2:</label>' +
                                '<input type="text" name="iammeter_options[meters][' + index + '][phase_names][phase2]" value="Phase B" style="width: 80px; padding: 3px;" placeholder="Phase B" />' +
                            '</div>' +
                            '<div>' +
                                '<label style="font-size: 12px; color: #666;">Phase 3:</label>' +
                                '<input type="text" name="iammeter_options[meters][' + index + '][phase_names][phase3]" value="Phase C" style="width: 80px; padding: 3px;" placeholder="Phase C" />' +
                            '</div>' +
                        '</div>' +
                        '<small style="color: #666; font-style: italic;">For example: Grid, Load, Solar</small>' +
                    '</div>' +
                    '<div class="meter-field">' +
                        '<label>' +
                            '<input type="checkbox" name="iammeter_options[meters][' + index + '][enabled]" value="1" checked />' +
                            'Enable this meter' +
                        '</label>' +
                    '</div>' +
                '</div>';
                
                $(html).insertBefore('#add-meter-btn');
                
                // Initialize new meter onChange events
                togglePhaseNames(index);
            });
            
            // Delete meter
            $(document).on('click', '.delete-meter', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this meter?')) {
                    $(this).closest('.meter-item').remove();
                    // Reindex
                    $('.meter-item').each(function(index) {
                        $(this).attr('data-index', index);
                        $(this).find('input, select').each(function() {
                            var name = $(this).attr('name');
                            if (name) {
                                // Handle complex field names, including nested phase_names
                                name = name.replace(/\[meters\]\[\d+\]/, '[meters][' + index + ']');
                                $(this).attr('name', name);
                            }
                        });
                        
                        // Update phase name configuration div id
                        var phaseDiv = $(this).find('.phase-names-config');
                        if (phaseDiv.length > 0) {
                            phaseDiv.attr('id', 'phase-names-' + index);
                        }
                        
                        // Update select onchange event
                        $(this).find('select[name*="[type]"]').attr('onchange', 'togglePhaseNames(' + index + ')');
                    });
                }
            });
            
            // Test API connection
            $(document).on('click', '.test-connection', function(e) {
                e.preventDefault();
                var index = $(this).data('index');
                var resultDiv = $('#test-result-' + index);
                var button = $(this);
                
                // Get current meter configuration
                var meter = $('.meter-item[data-index="' + index + '"]');
                var sn = meter.find('input[name*="[sn]"]').val();
                var token = meter.find('input[name*="[token]"]').val();
                var name = meter.find('input[name*="[name]"]').val();
                
                if (!sn || !token) {
                    resultDiv.html('<span style="color: red;">❌ Please fill in Serial Number and Token first</span>');
                    return;
                }
                
                // Show loading state
                button.prop('disabled', true).text('Testing...');
                resultDiv.html('<span style="color: #666;">🔄 Testing connection...</span>');
                
                // Send AJAX request
                $.post(ajaxurl, {
                    action: 'iammeter_test_connection',
                    sn: sn,
                    token: token,
                    name: name,
                    nonce: '<?php echo wp_create_nonce('iammeter_test_connection'); ?>'
                }, function(response) {
                    button.prop('disabled', false).text('🔗 Test Connection');
                    
                    if (response.success) {
                        if (response.data.api_success) {
                            var data = response.data.meter_data;
                            var html = '<div style="color: green; font-size: 12px; margin-top: 5px;">';
                            html += '✅ <strong>Connection successful!</strong><br>';
                            html += '<pre style="background: #f0f0f0; padding: 8px; margin: 5px 0; font-size: 11px; border-radius: 3px; overflow-x: auto;">';
                            html += JSON.stringify(data, null, 2);
                            html += '</pre>';
                            html += '</div>';
                            resultDiv.html(html);
                        } else {
                            resultDiv.html('<div style="color: red; font-size: 12px; margin-top: 5px;">❌ <strong>Connection failed:</strong> ' + response.data.error + '</div>');
                        }
                    } else {
                        resultDiv.html('<div style="color: red; font-size: 12px; margin-top: 5px;">❌ <strong>Test failed:</strong> ' + response.data + '</div>');
                    }
                }).fail(function() {
                    button.prop('disabled', false).text('🔗 Test Connection');
                    resultDiv.html('<div style="color: red; font-size: 12px; margin-top: 5px;">❌ <strong>Network Error</strong></div>');
                });
            });
            
            // Initialize phase name configuration display for all meters when page loads
            $('.meter-item').each(function(index) {
                togglePhaseNames(index);
            });
        });
        </script>
        <?php
    }
    
    /**
     * Cache duration callback function
     */
    public function cache_duration_callback() {
        $options = $this->get_plugin_options();
        $value = isset($options['cache_duration']) ? $options['cache_duration'] : 300;
        echo '<input type="number" id="cache_duration" name="iammeter_options[cache_duration]" value="' . esc_attr($value) . '" class="small-text" min="30" />';
        echo '<p class="description">API data cache time, recommended no less than 30 seconds</p>';
    }
    
    /**
     * Debug mode callback function
     */
    public function debug_mode_callback() {
        $options = $this->get_plugin_options();
        $value = isset($options['debug_mode']) ? $options['debug_mode'] : false;
        echo '<label>';
        echo '<input type="checkbox" id="debug_mode" name="iammeter_options[debug_mode]" value="1" ' . checked($value, true, false) . ' />';
        echo 'Enable debug mode';
        echo '</label>';
        echo '<p class="description">When enabled, detailed API error information and debug data will be displayed on the frontend (only visible to administrators)</p>';
    }
    
    /**
     * Token field callback
     */
    public function token_field_callback($args) {
        $options = get_option('iammeter_options');
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        echo '<input type="password" id="' . $field . '" name="iammeter_options[' . $field . ']" value="' . esc_attr($value) . '" class="regular-text" />';
    }
    
    /**
     * Text field callback
     */
    public function text_field_callback($args) {
        $options = get_option('iammeter_options');
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        echo '<input type="text" id="' . $field . '" name="iammeter_options[' . $field . ']" value="' . esc_attr($value) . '" class="regular-text" />';
    }
    
    /**
     * Number field callback
     */
    public function number_field_callback($args) {
        $options = get_option('iammeter_options');
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : 0;
        echo '<input type="number" id="' . $field . '" name="iammeter_options[' . $field . ']" value="' . esc_attr($value) . '" class="small-text" step="0.01" />';
    }
    
    /**
     * Checkbox field callback
     */
    public function checkbox_field_callback($args) {
        $options = get_option('iammeter_options');
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : false;
        echo '<input type="checkbox" id="' . $field . '" name="iammeter_options[' . $field . ']" value="1" ' . checked(1, $value, false) . ' />';
    }
    
    /**
     * Sanitize options
     */
    public function sanitize_options($input) {
        $sanitized = array();
        
        // Handle cache settings
        $sanitized['cache_duration'] = isset($input['cache_duration']) ? max(30, absint($input['cache_duration'])) : 300;
        
        // Handle debug mode settings
        $sanitized['debug_mode'] = isset($input['debug_mode']) ? true : false;
        
        // Process meter array
        $sanitized['meters'] = array();
        if (isset($input['meters']) && is_array($input['meters'])) {
            foreach ($input['meters'] as $meter) {
                if (!empty($meter['name']) || !empty($meter['sn'])) { // At least need name or serial number
                    $sanitized_meter = array(
                        'name' => sanitize_text_field($meter['name']),
                        'sn' => sanitize_text_field($meter['sn']),
                        'token' => sanitize_text_field($meter['token']),
                        'offset' => isset($meter['offset']) ? floatval($meter['offset']) : 0,
                        'color' => sanitize_hex_color($meter['color']) ? $meter['color'] : '#4CAF50',
                        'enabled' => isset($meter['enabled']) ? true : false,
                        'type' => isset($meter['type']) && in_array($meter['type'], array('single', 'three_phase')) ? $meter['type'] : 'single'
                    );
                    
                    // Handle phase names
                    if (isset($meter['phase_names']) && is_array($meter['phase_names'])) {
                        $sanitized_meter['phase_names'] = array(
                            'phase1' => isset($meter['phase_names']['phase1']) ? sanitize_text_field($meter['phase_names']['phase1']) : 'Phase A',
                            'phase2' => isset($meter['phase_names']['phase2']) ? sanitize_text_field($meter['phase_names']['phase2']) : 'Phase B',
                            'phase3' => isset($meter['phase_names']['phase3']) ? sanitize_text_field($meter['phase_names']['phase3']) : 'Phase C'
                        );
                    } else {
                        // If no phase names, use default values
                        $sanitized_meter['phase_names'] = array(
                            'phase1' => 'Phase A',
                            'phase2' => 'Phase B',
                            'phase3' => 'Phase C'
                        );
                    }
                    
                    $sanitized['meters'][] = $sanitized_meter;
                }
            }
        }
        
        // If no meters, add default two meters
        if (empty($sanitized['meters'])) {
            $sanitized['meters'] = array(
                array(
                    'name' => 'Solar Meter',
                    'sn' => '11223344',
                    'token' => '',
                    'offset' => 0,
                    'color' => '#4CAF50',
                    'enabled' => true,
                    'type' => 'single',
                    'phase_names' => array(
                        'phase1' => 'Phase A',
                        'phase2' => 'Phase B',
                        'phase3' => 'Phase C'
                    )
                ),
                array(
                    'name' => 'Grid Meter',
                    'sn' => '22334455',
                    'token' => '',
                    'offset' => 0,
                    'color' => '#2196F3',
                    'enabled' => true,
                    'type' => 'three_phase',
                    'phase_names' => array(
                        'phase1' => 'Phase A',
                        'phase2' => 'Phase B',
                        'phase3' => 'Phase C'
                    )
                )
            );
        }
        
        return $sanitized;
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('iammeter_options');
                do_settings_sections('iammeter');
                submit_button('Save Settings');
                ?>
            </form>
            
            <div class="card">
                <h2>Usage</h2>
                <p>Use the following shortcode in your posts or pages to display meter data:</p>
                
                <h3>Basic Usage</h3>
                <p><code>[iammeter]</code> - Display all enabled meter data</p>
                
                <h3>Advanced Usage</h3>
                <ul>
                    <li><code>[iammeter name="Solar Meter"]</code> - Display only the meter with specified name</li>
                    <li><code>[iammeter limit="2"]</code> - Limit the number of meters displayed</li>
                    <li><code>[iammeter name="Solar Meter" limit="1"]</code> - Combine parameters</li>
                </ul>
                
                <h3>Meter Management</h3>
                <ul>
                    <li>✅ Support adding unlimited meter devices</li>
                    <li>🎨 Each meter can have custom name and color</li>
                    <li>⚙️ Independent serial number, Token and offset configuration</li>
                    <li>🔄 Enable/disable meter display</li>
                    <li>🔗 Built-in connection test function</li>
                    <li>🐛 Optional debug mode (visible to administrators only)</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Load styles
     */
    public function enqueue_styles() {
        wp_enqueue_style('iammeter-style', IAMMETER_PLUGIN_URL . 'assets/style.css', array(), IAMMETER_VERSION);
    }
    
    /**
     * Get data from IAMMETER API
     */
    public function get_meter_data($sn, $token, $cache_key) {
        // Check cache
        $cached_data = wp_cache_get($cache_key);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // Build API URL
        $url = 'https://www.iammeter.com/api/v1/site/meterdata/' . urlencode($sn);
        
        // Set request parameters
        $args = array(
            'headers' => array(
                'token' => $token,
                'User-Agent' => 'IAMMETER ' . IAMMETER_VERSION
            ),
            'timeout' => 15,
            'sslverify' => false
        );
        
        // Send API request
        $response = wp_remote_get($url, $args);
        
        // Check for errors
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return array(
                'success' => false,
                'error' => sprintf('API returned error code: %d', $response_code)
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'error' => 'Failed to parse API response: ' . json_last_error_msg(),
                'debug' => array(
                    'response_body' => $body,
                    'json_error' => json_last_error_msg()
                )
            );
        }
        
        // Improved data format check
        if (!is_array($data)) {
            return array(
                'success' => false,
                'error' => 'API response is not a valid array format',
                'debug' => array(
                    'response_body' => $body,
                    'decoded_data' => $data
                )
            );
        }
        
        // Check data structure
        if (!isset($data['data'])) {
            return array(
                'success' => false,
                'error' => 'API response missing data field',
                'debug' => array(
                    'response_structure' => array_keys($data),
                    'full_response' => $data
                )
            );
        }
        
        if (!isset($data['data']['values']) || !is_array($data['data']['values'])) {
            return array(
                'success' => false,
                'error' => 'API response missing values array',
                'debug' => array(
                    'data_structure' => array_keys($data['data']),
                    'values_data' => isset($data['data']['values']) ? $data['data']['values'] : null
                )
            );
        }
        
        if (empty($data['data']['values']) || !isset($data['data']['values'][0])) {
            return array(
                'success' => false,
                'error' => 'API response values array is empty or missing first element',
                'debug' => array(
                    'values_count' => count($data['data']['values']),
                    'values_data' => $data['data']['values']
                )
            );
        }
        
        $result = array(
            'success' => true,
            'data' => $data
        );
        
        // Cache data
        $options = $this->get_plugin_options();
        $cache_duration = $options['cache_duration'];
        wp_cache_set($cache_key, $result, '', $cache_duration);
        
        return $result;
    }
    
    /**
     * Format meter data as HTML output
     */
    public function format_meter_data($meter, $data) {
        $options = $this->get_plugin_options();
        
        // Get meter phase name configuration, use defaults if none exist
        $phase_names = isset($meter['phase_names']) ? $meter['phase_names'] : array(
            'phase1' => 'Phase A',
            'phase2' => 'Phase B',
            'phase3' => 'Phase C'
        );
        
        $local_time = isset($data['data']['localTime']) ? $data['data']['localTime'] : '';
        $meter_type = isset($meter['type']) ? $meter['type'] : 'single';
        
        // Check if it's three-phase meter data
        // Three-phase data format: values is an array containing 3 sub-arrays, each sub-array contains 7 values
        // Single-phase data format: values is an array containing 1 sub-array, sub-array contains 7 values
        $values_array = $data['data']['values'];
        $is_three_phase_data = is_array($values_array) && count($values_array) == 3 && 
                              is_array($values_array[0]) && is_array($values_array[1]) && is_array($values_array[2]) &&
                              count($values_array[0]) >= 6 && count($values_array[1]) >= 6 && count($values_array[2]) >= 6;
        
        // Debug information (only shown in debug mode)
        $debug_enabled = isset($options['debug_mode']) ? $options['debug_mode'] : false;
        $debug_info = '';
        if ($debug_enabled) {
            $debug_info = '<div style="background: #f0f0f0; color: #333; padding: 10px; margin: 5px 0; font-size: 0.8em; border-radius: 3px;">';
            $debug_info .= '<strong>Debug Information:</strong><br>';
            $debug_info .= 'Meter type setting: ' . $meter_type . '<br>';
            $debug_info .= 'Data structure detection: ' . ($is_three_phase_data ? 'Three-phase data' : 'Single-phase data') . '<br>';
            
            // Show complete raw API response structure
            if (isset($data['data']['values'])) {
                $debug_info .= 'API values structure: ' . json_encode($data['data']['values']) . '<br>';
                $debug_info .= 'Values array length: ' . count($data['data']['values']) . '<br>';
                
                // More detailed data structure analysis
                if (count($data['data']['values']) == 1) {
                    $debug_info .= '✅ Matches single-phase data format (1 sub-array)<br>';
                } elseif (count($data['data']['values']) == 3) {
                    $debug_info .= '✅ Matches three-phase data format (3 sub-arrays)<br>';
                } else {
                    $debug_info .= '❌ Unknown data format (' . count($data['data']['values']) . ' sub-arrays)<br>';
                }
            }
            
            // Show information of the first values element (for previous display logic)
            if (isset($data['data']['values'][0])) {
                $raw_values = $data['data']['values'][0];
                $debug_info .= 'First values element type: ' . gettype($raw_values) . '<br>';
                if (is_array($raw_values)) {
                    $debug_info .= 'First values element length: ' . count($raw_values) . '<br>';
                    $debug_info .= 'First values content: [' . implode(', ', array_slice($raw_values, 0, 3)) . '...]<br>';
                }
            }
            
            $debug_info .= 'Final choice: ' . (($meter_type === 'three_phase' && $is_three_phase_data) ? 'Three-phase format' : 'Single-phase format') . '<br>';
            $debug_info .= '</div>';
        }
        
        if ($meter_type === 'three_phase' && $is_three_phase_data) {
            if ($debug_enabled) {
                $debug_info .= '<div style="background: #d4edda; color: #155724; padding: 5px; margin: 5px 0; border-radius: 3px;">✅ Using three-phase display format</div>';
                return $debug_info . $this->format_three_phase_data($meter, $data, $phase_names, $local_time);
            } else {
                return $this->format_three_phase_data($meter, $data, $phase_names, $local_time);
            }
        } else {
            if ($debug_enabled) {
                // Check if this is normal single-phase meter display
                if (($meter_type === 'single' || $meter_type !== 'three_phase') && !$is_three_phase_data) {
                    $debug_info .= '<div style="background: #d4edda; color: #155724; padding: 5px; margin: 5px 0; border-radius: 3px;">✅ Using single-phase display format (normal)</div>';
                } else {
                    // Only show error message when configuration is wrong
                    $reason = '';
                    if ($meter_type === 'three_phase' && !$is_three_phase_data) {
                        $reason = 'Meter type set to three_phase but data is single-phase format';
                    } else if ($meter_type !== 'three_phase' && $is_three_phase_data) {
                        $reason = 'Meter type set to single-phase but data is three-phase format';
                    }
                    if (!empty($reason)) {
                        $debug_info .= '<div style="background: #f8d7da; color: #721c24; padding: 5px; margin: 5px 0; border-radius: 3px;">⚠️ Configuration mismatch: ' . $reason . '</div>';
                    }
                }
                return $debug_info . $this->format_single_phase_data($meter, $data, $local_time);
            } else {
                return $this->format_single_phase_data($meter, $data, $local_time);
            }
        }
    }
    
    /**
     * Format single-phase meter data
     */
    private function format_single_phase_data($meter, $data, $local_time) {
        $values = $data['data']['values'][0];
        
        // Get data
        $voltage = isset($values[0]) ? number_format($values[0], 1) . ' V' : 'n/a';
        $current = isset($values[1]) ? number_format($values[1], 2) . ' A' : 'n/a';
        $power = isset($values[2]) ? number_format($values[2], 0) . ' W' : 'n/a';
        $import_energy = isset($values[3]) ? number_format($values[3] + $meter['offset'], 2) . ' kWh' : 'n/a';
        $export_energy = isset($values[4]) ? number_format($values[4], 2) . ' kWh' : 'n/a';
        $power_factor = isset($values[6]) ? number_format($values[6], 3) : 'n/a';
        
        // Build HTML output
        $html = '<div class="iammeter-block single-phase" style="border: 1px solid #ccc; padding: 12px; flex: 0 1 auto; min-width: 280px; max-width: 380px; background-color: ' . esc_attr($meter['color']) . '; color: white; border-radius: 8px;">';
        $html .= '<h3 style="margin: 0 0 12px 0; color: white; font-size: 1.1em;">' . esc_html($meter['name']) . '</h3>';
        $html .= '<table class="iammeter-data" style="border-collapse: collapse; width: 100%; color: white; font-size: 0.8em;">';
        $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); width: 60%;">⚡ Voltage</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($voltage) . '</td></tr>';
        $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">🔌 Current</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($current) . '</td></tr>';
        $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">☀️ Power</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; color: yellow; white-space: nowrap;">' . esc_html($power) . '</td></tr>';
        $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">📐 Power Factor</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($power_factor) . '</td></tr>';
        $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">🏠 Import Energy</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($import_energy) . '</td></tr>';
        $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">🌐 Export Energy</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($export_energy) . '</td></tr>';
        $html .= '<tr><td colspan="2" style="font-size: 0.75em; text-align: center; padding-top: 8px; color: rgba(255,255,255,0.8); border-top: 1px solid rgba(255,255,255,0.3);">' . sprintf('Update Time: %s', esc_html($local_time)) . '</td></tr>';
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Format three-phase meter data
     */
    private function format_three_phase_data($meter, $data, $phase_names, $local_time) {
        // Three-phase data in values array, each element is data for one phase
        $phases_data = $data['data']['values'];
        
        $html = '';
        
        // Generate independent cards for each phase
        $phase_configs = array(
            array('key' => 'phase1', 'index' => 0),
            array('key' => 'phase2', 'index' => 1),
            array('key' => 'phase3', 'index' => 2)
        );
        
        foreach ($phase_configs as $phase_config) {
            $phase_values = isset($phases_data[$phase_config['index']]) ? $phases_data[$phase_config['index']] : array();
            $phase_name = isset($phase_names[$phase_config['key']]) ? $phase_names[$phase_config['key']] : $phase_config['key'];
            
            // Get data
            $voltage = isset($phase_values[0]) ? number_format($phase_values[0], 1) . ' V' : 'n/a';
            $current = isset($phase_values[1]) ? number_format($phase_values[1], 2) . ' A' : 'n/a';
            $power = isset($phase_values[2]) ? number_format($phase_values[2], 0) . ' W' : 'n/a';
            $import_energy = isset($phase_values[3]) ? number_format($phase_values[3] + ($phase_config['index'] == 0 ? $meter['offset'] : 0), 2) . ' kWh' : 'n/a';
            $export_energy = isset($phase_values[4]) ? number_format($phase_values[4], 2) . ' kWh' : 'n/a';
            $power_factor = isset($phase_values[6]) ? number_format($phase_values[6], 3) : 'n/a';
            
            // Build HTML output identical to single-phase meter
            $html .= '<div class="iammeter-block single-phase" style="border: 1px solid #ccc; padding: 12px; flex: 0 1 auto; min-width: 280px; max-width: 380px; background-color: ' . esc_attr($meter['color']) . '; color: white; border-radius: 8px;">';
            $html .= '<h3 style="margin: 0 0 12px 0; color: white; font-size: 1.1em;">' . esc_html($meter['name']) . '-' . esc_html($phase_name) . '</h3>';
            $html .= '<table class="iammeter-data" style="border-collapse: collapse; width: 100%; color: white; font-size: 0.8em;">';
            $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); width: 60%;">⚡ Voltage</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($voltage) . '</td></tr>';
            $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">🔌 Current</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($current) . '</td></tr>';
            $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">☀️ Power</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; color: yellow; white-space: nowrap;">' . esc_html($power) . '</td></tr>';
            $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">📐 Power Factor</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($power_factor) . '</td></tr>';
            $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">🏠 Import Energy</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($import_energy) . '</td></tr>';
            $html .= '<tr><td style="padding: 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2);">🌐 Export Energy</td><td style="padding: 5px 8px 5px 3px; border-bottom: 1px solid rgba(255,255,255,0.2); text-align: right; font-weight: bold; white-space: nowrap;">' . esc_html($export_energy) . '</td></tr>';
            $html .= '<tr><td colspan="2" style="font-size: 0.75em; text-align: center; padding-top: 8px; color: rgba(255,255,255,0.8); border-top: 1px solid rgba(255,255,255,0.3);">' . sprintf('Update Time: %s', esc_html($local_time)) . '</td></tr>';
            $html .= '</table>';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'name' => '', // Specify particular meter name, empty means show all enabled meters
            'limit' => 0  // Limit the number of meters displayed, 0 means no limit
        ), $atts, 'iammeter');
        
        $options = $this->get_plugin_options();
        $meters = isset($options['meters']) ? $options['meters'] : array();
        $output = '<div class="iammeter-container" style="display: flex; flex-wrap: wrap; gap: 20px;">';
        
        $displayed_count = 0;
        $has_data = false;
        
        foreach ($meters as $index => $meter) {
            // Check if enabled
            if (!isset($meter['enabled']) || !$meter['enabled']) {
                continue;
            }
            
            // Check name filter
            if (!empty($atts['name']) && $meter['name'] !== $atts['name']) {
                continue;
            }
            
            // Check quantity limit
            if ($atts['limit'] > 0 && $displayed_count >= $atts['limit']) {
                break;
            }
            
            // Check required configuration
            if (empty($meter['sn']) || empty($meter['token'])) {
                $output .= '<div class="iammeter-error" style="color: red; padding: 15px; border: 1px solid red; margin: 10px; border-radius: 5px; background-color: #fff5f5;">';
                $output .= '<strong>' . sprintf('⚠️ %s cannot be displayed', esc_html($meter['name'])) . '</strong><br>';
                $output .= 'Please complete the configuration in the backend first (serial number and API Token).';
                if (current_user_can('manage_options')) {
                    $output .= '<br><a href="' . admin_url('options-general.php?page=iammeter') . '" style="color: #0073aa; text-decoration: none;">';
                    $output .= '👉 Click here to configure</a>';
                }
                $output .= '</div>';
                $displayed_count++;
                continue;
            }
            
            // Get data
            $cache_key = 'iammeter_data_' . md5($meter['sn'] . $meter['token']);
            $meter_data = $this->get_meter_data($meter['sn'], $meter['token'], $cache_key);
            
            if ($meter_data['success']) {
                $output .= $this->format_meter_data($meter, $meter_data['data']);
                $has_data = true;
            } else {
                $output .= '<div class="iammeter-error" style="color: red; padding: 15px; border: 1px solid red; margin: 10px; border-radius: 5px; background-color: #fff5f5;">';
                $output .= '<strong>' . sprintf('%s data acquisition failed', esc_html($meter['name'])) . '</strong><br>';
                $output .= '<strong>Error message：</strong>' . esc_html($meter_data['error']);
                
                // Show detailed information in debug mode
                $options = $this->get_plugin_options();
                $debug_enabled = isset($options['debug_mode']) ? $options['debug_mode'] : false;
                
                if ($debug_enabled && current_user_can('manage_options')) {
                    if (isset($meter_data['debug'])) {
                        $output .= '<br><br><details style="margin-top: 10px;">';
                        $output .= '<summary style="cursor: pointer; color: #0073aa;"><strong>Debug Information (click to view)</strong></summary>';
                        $output .= '<div style="margin-top: 10px; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 3px; font-family: monospace; font-size: 12px; overflow-x: auto;">';
                        $output .= '<strong>Serial Number:</strong> ' . esc_html($meter['sn']) . '<br>';
                        $output .= '<strong>Token:</strong> ' . esc_html(substr($meter['token'], 0, 10)) . '...<br>';
                        $output .= '<strong>Debug Data:</strong><br>';
                        $output .= '<pre style="white-space: pre-wrap; color: #333; background: #fff; padding: 5px; border-radius: 3px; margin-top: 5px;">';
                        $output .= esc_html(print_r($meter_data['debug'], true));
                        $output .= '</pre>';
                        $output .= '</div>';
                        $output .= '</details>';
                    }
                }
                
                if (current_user_can('manage_options')) {
                    $output .= '<br><a href="' . admin_url('options-general.php?page=iammeter') . '" style="color: #0073aa; text-decoration: none;">';
                    $output .= '👉 Check Configuration</a>';
                }
                $output .= '</div>';
            }
            
            $displayed_count++;
        }
        
        // If no data to display
        if (!$has_data && $displayed_count === 0) {
            $output .= '<div class="iammeter-error" style="color: #856404; padding: 15px; border: 1px solid #ffeaa7; margin: 10px; border-radius: 5px; background-color: #fff3cd;">';
            $output .= '<strong>📊 No meter data to display</strong><br>';
            $output .= 'Please add and configure at least one meter in the backend.';
            if (current_user_can('manage_options')) {
                $output .= '<br><a href="' . admin_url('options-general.php?page=iammeter') . '" style="color: #0073aa; text-decoration: none;">';
                $output .= '👉 Click here to add meter</a>';
            }
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }
}

// Initialize plugin
new IAMMETERPlugin();