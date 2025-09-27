<?php
/**
 * File Handler Class
 *
 * Handles file uploads, management, and security
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_File_Handler {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Upload base directory
     */
    private $upload_base_dir;

    /**
     * Upload base URL
     */
    private $upload_base_url;

    /**
     * Allowed file types
     */
    private $allowed_file_types;

    /**
     * Max file sizes by type (in bytes)
     */
    private $max_file_sizes;

    /**
     * Get instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->upload_base_dir = trailingslashit($upload_dir['basedir']) . 'spelling-bee-pro/';
        $this->upload_base_url = trailingslashit($upload_dir['baseurl']) . 'spelling-bee-pro/';

        $this->setupAllowedFileTypes();
        $this->setupMaxFileSizes();
        $this->setupHooks();
    }

    /**
     * Setup allowed file types
     */
    private function setupAllowedFileTypes() {
        $this->allowed_file_types = array(
            'documents' => array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'),
            'videos' => array('mp4', 'mov', 'avi', 'wmv', 'flv'),
            'images' => array('jpg', 'jpeg', 'png', 'gif', 'webp'),
            'flyers' => array('jpg', 'jpeg', 'png', 'pdf', 'gif')
        );

        // Allow customization via filter
        $this->allowed_file_types = apply_filters('osb_allowed_file_types', $this->allowed_file_types);
    }

    /**
     * Setup maximum file sizes
     */
    private function setupMaxFileSizes() {
        $this->max_file_sizes = array(
            'documents' => 5 * MB_IN_BYTES, // 5MB
            'videos' => 100 * MB_IN_BYTES,  // 100MB
            'images' => 2 * MB_IN_BYTES,    // 2MB
            'flyers' => 3 * MB_IN_BYTES     // 3MB
        );

        // Allow customization via filter
        $this->max_file_sizes = apply_filters('osb_max_file_sizes', $this->max_file_sizes);
    }

    /**
     * Setup WordPress hooks
     */
    private function setupHooks() {
        add_action('wp_ajax_osb_upload_file', array($this, 'handleAjaxUpload'));
        add_action('wp_ajax_nopriv_osb_upload_file', array($this, 'handleAjaxUpload'));
        add_action('wp_ajax_osb_delete_file', array($this, 'handleAjaxDelete'));
        add_filter('upload_mimes', array($this, 'addCustomMimeTypes'));
    }

    /**
     * Upload file
     */
    public function uploadFile($file_data, $type = 'documents', $subfolder = '', $custom_name = '') {
        // Validate file type
        if (!$this->isValidFileType($file_data['name'], $type)) {
            return new WP_Error('invalid_file_type', __('Invalid file type for this upload category.', 'spelling-bee-pro'));
        }

        // Validate file size
        if (!$this->isValidFileSize($file_data['size'], $type)) {
            return new WP_Error('file_too_large', sprintf(
                __('File size exceeds the maximum limit of %s.', 'spelling-bee-pro'),
                size_format($this->max_file_sizes[$type])
            ));
        }

        // Setup upload directory
        $upload_dir = $this->getUploadDir($type, $subfolder);
        if (!$this->ensureDirectoryExists($upload_dir)) {
            return new WP_Error('directory_error', __('Could not create upload directory.', 'spelling-bee-pro'));
        }

        // Generate secure filename
        $filename = $this->generateSecureFilename($file_data['name'], $custom_name);
        $filepath = $upload_dir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file_data['tmp_name'], $filepath)) {
            return new WP_Error('upload_failed', __('Failed to upload file.', 'spelling-bee-pro'));
        }

        // Set proper file permissions
        chmod($filepath, 0644);

        // Get file URL
        $file_url = $this->getFileUrl($type, $subfolder, $filename);

        // Log file upload
        $this->logFileAction('upload', $filepath, get_current_user_id());

        return array(
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => $file_url,
            'size' => filesize($filepath),
            'type' => $this->getFileExtension($filename)
        );
    }

    /**
     * Handle AJAX file upload
     */
    public function handleAjaxUpload() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_upload_nonce')) {
            wp_die(__('Security check failed.', 'spelling-bee-pro'));
        }

        // Check user permissions
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have permission to upload files.', 'spelling-bee-pro'));
        }

        $type = sanitize_text_field($_POST['type']);
        $subfolder = sanitize_text_field($_POST['subfolder']);
        $custom_name = sanitize_text_field($_POST['custom_name']);

        if (empty($_FILES['file'])) {
            wp_send_json_error(__('No file selected.', 'spelling-bee-pro'));
        }

        $result = $this->uploadFile($_FILES['file'], $type, $subfolder, $custom_name);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success($result);
    }

    /**
     * Delete file
     */
    public function deleteFile($filepath) {
        // Security check - ensure file is within plugin directory
        if (strpos(realpath($filepath), realpath($this->upload_base_dir)) !== 0) {
            return new WP_Error('security_error', __('Invalid file path.', 'spelling-bee-pro'));
        }

        if (!file_exists($filepath)) {
            return new WP_Error('file_not_found', __('File not found.', 'spelling-bee-pro'));
        }

        // Log file deletion
        $this->logFileAction('delete', $filepath, get_current_user_id());

        if (unlink($filepath)) {
            return true;
        }

        return new WP_Error('delete_failed', __('Failed to delete file.', 'spelling-bee-pro'));
    }

    /**
     * Handle AJAX file deletion
     */
    public function handleAjaxDelete() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_delete_nonce')) {
            wp_die(__('Security check failed.', 'spelling-bee-pro'));
        }

        // Check user permissions
        if (!current_user_can('delete_files')) {
            wp_die(__('You do not have permission to delete files.', 'spelling-bee-pro'));
        }

        $filepath = sanitize_text_field($_POST['filepath']);
        $result = $this->deleteFile($filepath);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(__('File deleted successfully.', 'spelling-bee-pro'));
    }

    /**
     * Get file information
     */
    public function getFileInfo($filepath) {
        if (!file_exists($filepath)) {
            return false;
        }

        return array(
            'filename' => basename($filepath),
            'filepath' => $filepath,
            'size' => filesize($filepath),
            'modified' => filemtime($filepath),
            'extension' => $this->getFileExtension($filepath),
            'mime_type' => wp_check_filetype($filepath)['type']
        );
    }

    /**
     * Get upload directory for type
     */
    private function getUploadDir($type, $subfolder = '') {
        $dir = $this->upload_base_dir . $type . '/';

        if (!empty($subfolder)) {
            $dir .= trailingslashit(sanitize_file_name($subfolder));
        }

        return $dir;
    }

    /**
     * Get file URL
     */
    private function getFileUrl($type, $subfolder = '', $filename = '') {
        $url = $this->upload_base_url . $type . '/';

        if (!empty($subfolder)) {
            $url .= trailingslashit(sanitize_file_name($subfolder));
        }

        if (!empty($filename)) {
            $url .= $filename;
        }

        return $url;
    }

    /**
     * Ensure directory exists and is secure
     */
    private function ensureDirectoryExists($dir) {
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }

        // Create .htaccess for security
        $htaccess_file = $dir . '.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "# Protect files from direct access\n";
            $htaccess_content .= "<Files *>\n";
            $htaccess_content .= "    Order Deny,Allow\n";
            $htaccess_content .= "    Deny from all\n";
            $htaccess_content .= "</Files>\n\n";
            $htaccess_content .= "# Allow specific file types\n";
            $htaccess_content .= "<FilesMatch \"\\.(pdf|doc|docx|jpg|jpeg|png|gif|webp)$\">\n";
            $htaccess_content .= "    Order Allow,Deny\n";
            $htaccess_content .= "    Allow from all\n";
            $htaccess_content .= "</FilesMatch>\n";

            file_put_contents($htaccess_file, $htaccess_content);
        }

        // Create index.php for additional protection
        $index_file = $dir . 'index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden');
        }

        return is_dir($dir) && is_writable($dir);
    }

    /**
     * Generate secure filename
     */
    private function generateSecureFilename($original_name, $custom_name = '') {
        $extension = $this->getFileExtension($original_name);

        if (!empty($custom_name)) {
            $base_name = sanitize_file_name($custom_name);
        } else {
            $base_name = sanitize_file_name(pathinfo($original_name, PATHINFO_FILENAME));
        }

        // Add timestamp and random string for uniqueness
        $timestamp = current_time('timestamp');
        $random = wp_generate_password(8, false);

        return $base_name . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Validate file type
     */
    private function isValidFileType($filename, $type) {
        $extension = $this->getFileExtension($filename);

        if (!isset($this->allowed_file_types[$type])) {
            return false;
        }

        return in_array($extension, $this->allowed_file_types[$type]);
    }

    /**
     * Validate file size
     */
    private function isValidFileSize($size, $type) {
        if (!isset($this->max_file_sizes[$type])) {
            return false;
        }

        return $size <= $this->max_file_sizes[$type];
    }

    /**
     * Get file extension
     */
    private function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Add custom MIME types
     */
    public function addCustomMimeTypes($mimes) {
        $mimes['doc'] = 'application/msword';
        $mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        return $mimes;
    }

    /**
     * Log file action
     */
    private function logFileAction($action, $filepath, $user_id) {
        // For now, we'll disable logging until we implement proper document tracking
        // The current documents table is designed for file attachments, not action logging
        return true;

        // TODO: Create a separate file_actions table for logging if needed
        /*
        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'file_actions';

        $data = array(
            'user_id' => $user_id,
            'action' => $action,
            'file_path' => $filepath,
            'file_name' => basename($filepath),
            'created_at' => current_time('mysql')
        );

        $wpdb->insert($table_name, $data, array('%d', '%s', '%s', '%s', '%s'));
        */
    }

    /**
     * Get files by type and subfolder
     */
    public function getFiles($type, $subfolder = '') {
        $dir = $this->getUploadDir($type, $subfolder);

        if (!is_dir($dir)) {
            return array();
        }

        $files = array();
        $iterator = new DirectoryIterator($dir);

        foreach ($iterator as $file) {
            if ($file->isFile() && !$file->isDot() && $file->getFilename() !== 'index.php' && $file->getFilename() !== '.htaccess') {
                $files[] = array(
                    'filename' => $file->getFilename(),
                    'filepath' => $file->getPathname(),
                    'url' => $this->getFileUrl($type, $subfolder, $file->getFilename()),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                    'extension' => $this->getFileExtension($file->getFilename())
                );
            }
        }

        // Sort by modified time (newest first)
        usort($files, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $files;
    }

    /**
     * Clean up old files
     */
    public function cleanupOldFiles($days_old = 30) {
        $cutoff_time = time() - ($days_old * 24 * 60 * 60);
        $deleted_count = 0;

        foreach ($this->allowed_file_types as $type => $extensions) {
            $type_dir = $this->getUploadDir($type);

            if (!is_dir($type_dir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($type_dir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if ($file->isFile() &&
                    $file->getFilename() !== 'index.php' &&
                    $file->getFilename() !== '.htaccess' &&
                    $file->getMTime() < $cutoff_time) {

                    if (unlink($file->getPathname())) {
                        $deleted_count++;
                        $this->logFileAction('cleanup', $file->getPathname(), 0);
                    }
                }
            }
        }

        return $deleted_count;
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats() {
        $stats = array(
            'total_files' => 0,
            'total_size' => 0,
            'by_type' => array()
        );

        foreach ($this->allowed_file_types as $type => $extensions) {
            $type_dir = $this->getUploadDir($type);
            $type_stats = array('files' => 0, 'size' => 0);

            if (is_dir($type_dir)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($type_dir),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($iterator as $file) {
                    if ($file->isFile() &&
                        $file->getFilename() !== 'index.php' &&
                        $file->getFilename() !== '.htaccess') {

                        $type_stats['files']++;
                        $type_stats['size'] += $file->getSize();
                    }
                }
            }

            $stats['by_type'][$type] = $type_stats;
            $stats['total_files'] += $type_stats['files'];
            $stats['total_size'] += $type_stats['size'];
        }

        return $stats;
    }
}