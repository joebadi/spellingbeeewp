<?php
/**
 * Plugin Activator Class
 *
 * Handles plugin activation, database setup, and initial data
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Activator {

    /**
     * Plugin activation hook
     */
    public static function activate() {
        // Create custom database tables
        self::createDatabaseTables();

        // Upgrade database schema if needed
        self::upgradeDatabaseSchema();

        // Run database migrations
        OSB_Database_Migration::migrate();

        // Create custom user roles
        self::createUserRoles();

        // Set default plugin options
        self::setDefaultOptions();

        // Create upload directories
        self::createUploadDirectories();

        // Set plugin version
        update_option('osb_plugin_version', OSB_PLUGIN_VERSION);

        // Set activation timestamp
        update_option('osb_activation_time', current_time('timestamp'));

        // Flush rewrite rules
        flush_rewrite_rules();

        // Log activation
        error_log('Omafuru Spelling Bee Plugin activated successfully');
    }

    /**
     * Create custom database tables
     */
    private static function createDatabaseTables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $sql = array();

        // Events table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}events (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            title varchar(255) NOT NULL,
            description text DEFAULT NULL,
            year year NOT NULL,
            start_date date NOT NULL,
            start_time time DEFAULT NULL,
            event_date date NOT NULL,
            event_time time DEFAULT NULL,
            venue varchar(255) DEFAULT NULL,
            venue_name varchar(255) DEFAULT NULL,
            venue_address text DEFAULT NULL,
            status enum('upcoming','live','completed','cancelled') DEFAULT 'upcoming',
            registration_start_date date DEFAULT NULL,
            registration_end_date date DEFAULT NULL,
            registration_deadline date DEFAULT NULL,
            max_schools int(11) DEFAULT NULL,
            max_students_per_school int(11) DEFAULT 5,
            prize_fund_goal decimal(10,2) DEFAULT 0.00,
            base_prize_first decimal(10,2) DEFAULT 0.00,
            base_prize_second decimal(10,2) DEFAULT 0.00,
            base_prize_third decimal(10,2) DEFAULT 0.00,
            total_donations decimal(10,2) DEFAULT 0.00,
            flyer_url varchar(500) DEFAULT NULL,
            created_by int(11) NOT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_year (year),
            KEY idx_status (status),
            KEY idx_event_date (event_date),
            KEY idx_start_date (start_date)
        ) $charset_collate;";

        // Event videos table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}event_videos (
            id int(11) NOT NULL AUTO_INCREMENT,
            event_id int(11) NOT NULL,
            video_type enum('flyer','live_stream','highlight','archive') NOT NULL,
            title varchar(255) NOT NULL,
            youtube_url varchar(500) DEFAULT NULL,
            flyer_image varchar(500) DEFAULT NULL,
            description text DEFAULT NULL,
            sort_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY fk_event_videos_event (event_id),
            KEY idx_video_type (video_type),
            KEY idx_active (is_active),
            CONSTRAINT fk_event_videos_event FOREIGN KEY (event_id) REFERENCES {$table_prefix}events (id) ON DELETE CASCADE
        ) $charset_collate;";

        // Schools table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}schools (
            id int(11) NOT NULL AUTO_INCREMENT,
            wp_user_id bigint(20) unsigned NOT NULL,
            school_name varchar(255) NOT NULL,
            school_type enum('public','private','federal','state') NOT NULL,
            state varchar(100) NOT NULL,
            address text NOT NULL,
            phone varchar(20) DEFAULT NULL,
            contact_person varchar(255) NOT NULL,
            contact_email varchar(255) NOT NULL,
            logo_url varchar(500) DEFAULT NULL,
            previous_participation text DEFAULT NULL COMMENT 'JSON array of years participated',
            status enum('pending','documents_submitted','under_review','approved','rejected','confirmed') DEFAULT 'pending',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY fk_schools_user (wp_user_id),
            KEY idx_status (status),
            KEY idx_contact_email (contact_email),
            CONSTRAINT fk_schools_user FOREIGN KEY (wp_user_id) REFERENCES {$wpdb->users} (ID) ON DELETE CASCADE
        ) $charset_collate;";

        // Students table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}students (
            id int(11) NOT NULL AUTO_INCREMENT,
            school_id int(11) NOT NULL,
            wp_user_id bigint(20) unsigned DEFAULT NULL,
            parent_wp_user_id bigint(20) unsigned DEFAULT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) DEFAULT NULL,
            phone varchar(20) DEFAULT NULL,
            birth_date date NOT NULL,
            age int(11) NOT NULL,
            parent_email varchar(255) NOT NULL,
            parent_phone varchar(20) NOT NULL,
            parent_name varchar(255) NOT NULL,
            parent_relationship enum('father','mother','guardian','other') DEFAULT 'father',
            emergency_contact varchar(255) DEFAULT NULL,
            emergency_phone varchar(20) DEFAULT NULL,
            medical_info text DEFAULT NULL,
            consent_given tinyint(1) DEFAULT 0,
            documents_complete tinyint(1) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY fk_students_school (school_id),
            KEY fk_students_user (wp_user_id),
            KEY fk_students_parent (parent_wp_user_id),
            KEY idx_email (email),
            KEY idx_parent_email (parent_email),
            CONSTRAINT fk_students_school FOREIGN KEY (school_id) REFERENCES {$table_prefix}schools (id) ON DELETE CASCADE,
            CONSTRAINT fk_students_user FOREIGN KEY (wp_user_id) REFERENCES {$wpdb->users} (ID) ON DELETE SET NULL,
            CONSTRAINT fk_students_parent FOREIGN KEY (parent_wp_user_id) REFERENCES {$wpdb->users} (ID) ON DELETE SET NULL
        ) $charset_collate;";

        // Registrations table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}registrations (
            id int(11) NOT NULL AUTO_INCREMENT,
            event_id int(11) NOT NULL,
            school_id int(11) NOT NULL,
            registration_token varchar(32) NOT NULL COMMENT 'Unique token for document upload',
            status enum('pending','documents_submitted','under_review','approved','rejected','confirmed') DEFAULT 'pending',
            student_count int(11) DEFAULT 0,
            documents_required text DEFAULT NULL COMMENT 'JSON array of required documents',
            documents_uploaded text DEFAULT NULL COMMENT 'JSON array of uploaded documents',
            admin_notes text DEFAULT NULL,
            submitted_at timestamp NULL DEFAULT NULL,
            reviewed_at timestamp NULL DEFAULT NULL,
            reviewed_by int(11) DEFAULT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_registration (event_id, school_id),
            UNIQUE KEY unique_token (registration_token),
            KEY fk_registrations_event (event_id),
            KEY fk_registrations_school (school_id),
            KEY idx_status (status),
            CONSTRAINT fk_registrations_event FOREIGN KEY (event_id) REFERENCES {$table_prefix}events (id) ON DELETE CASCADE,
            CONSTRAINT fk_registrations_school FOREIGN KEY (school_id) REFERENCES {$table_prefix}schools (id) ON DELETE CASCADE
        ) $charset_collate;";

        // Documents table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}documents (
            id int(11) NOT NULL AUTO_INCREMENT,
            registration_id int(11) NOT NULL,
            student_id int(11) DEFAULT NULL,
            document_type enum('school_certificate','endorsement_letter','birth_certificate','parent_consent','medical_clearance','other') NOT NULL,
            file_name varchar(255) NOT NULL,
            file_path varchar(500) NOT NULL,
            file_size int(11) NOT NULL,
            mime_type varchar(100) NOT NULL,
            upload_date timestamp DEFAULT CURRENT_TIMESTAMP,
            uploaded_by int(11) DEFAULT NULL,
            is_verified tinyint(1) DEFAULT 0,
            verified_by int(11) DEFAULT NULL,
            verified_at timestamp NULL DEFAULT NULL,
            notes text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY fk_documents_registration (registration_id),
            KEY fk_documents_student (student_id),
            KEY idx_document_type (document_type),
            KEY idx_verified (is_verified),
            CONSTRAINT fk_documents_registration FOREIGN KEY (registration_id) REFERENCES {$table_prefix}registrations (id) ON DELETE CASCADE,
            CONSTRAINT fk_documents_student FOREIGN KEY (student_id) REFERENCES {$table_prefix}students (id) ON DELETE CASCADE
        ) $charset_collate;";

        // Donations table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}donations (
            id int(11) NOT NULL AUTO_INCREMENT,
            event_id int(11) NOT NULL,
            donor_name varchar(255) DEFAULT NULL,
            donor_email varchar(255) DEFAULT NULL,
            donor_phone varchar(20) DEFAULT NULL,
            amount decimal(10,2) NOT NULL,
            donation_type enum('general','sponsor_gold','sponsor_silver','sponsor_bronze') DEFAULT 'general',
            payment_method varchar(50) DEFAULT NULL,
            payment_reference varchar(255) DEFAULT NULL,
            status enum('pending','completed','failed','refunded') DEFAULT 'pending',
            is_anonymous tinyint(1) DEFAULT 0,
            show_on_website tinyint(1) DEFAULT 1,
            notes text DEFAULT NULL,
            donated_at timestamp DEFAULT CURRENT_TIMESTAMP,
            processed_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY fk_donations_event (event_id),
            KEY idx_status (status),
            KEY idx_amount (amount),
            KEY idx_donation_type (donation_type),
            CONSTRAINT fk_donations_event FOREIGN KEY (event_id) REFERENCES {$table_prefix}events (id) ON DELETE CASCADE
        ) $charset_collate;";

        // Sponsors table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}sponsors (
            id int(11) NOT NULL AUTO_INCREMENT,
            event_id int(11) DEFAULT NULL,
            sponsor_name varchar(255) NOT NULL,
            sponsor_email varchar(255) DEFAULT NULL,
            sponsor_phone varchar(20) DEFAULT NULL,
            sponsor_website varchar(255) DEFAULT NULL,
            logo_url varchar(500) DEFAULT NULL,
            tier enum('gold','silver','bronze','community') NOT NULL,
            amount_contributed decimal(10,2) DEFAULT 0.00,
            description text DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            show_logo tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY fk_sponsors_event (event_id),
            KEY idx_tier (tier),
            KEY idx_active (is_active),
            CONSTRAINT fk_sponsors_event FOREIGN KEY (event_id) REFERENCES {$table_prefix}events (id) ON DELETE SET NULL
        ) $charset_collate;";

        // User conflicts table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}user_conflicts (
            id int(11) NOT NULL AUTO_INCREMENT,
            registration_id int(11) DEFAULT NULL,
            existing_user_id bigint(20) unsigned NOT NULL,
            new_user_data text NOT NULL COMMENT 'JSON of new registration data',
            conflict_type enum('email_match','name_phone_match','manual_review') NOT NULL,
            confidence_score decimal(3,2) DEFAULT 0.50,
            conflict_fields text DEFAULT NULL COMMENT 'JSON array of conflicting fields',
            status enum('pending','approved','rejected','merged') DEFAULT 'pending',
            admin_notes text DEFAULT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            resolved_at timestamp NULL DEFAULT NULL,
            resolved_by int(11) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY fk_conflicts_registration (registration_id),
            KEY fk_conflicts_user (existing_user_id),
            KEY idx_status (status),
            KEY idx_conflict_type (conflict_type),
            CONSTRAINT fk_conflicts_registration FOREIGN KEY (registration_id) REFERENCES {$table_prefix}registrations (id) ON DELETE SET NULL,
            CONSTRAINT fk_conflicts_user FOREIGN KEY (existing_user_id) REFERENCES {$wpdb->users} (ID) ON DELETE CASCADE
        ) $charset_collate;";

        // Communications table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}communications (
            id int(11) NOT NULL AUTO_INCREMENT,
            recipient_user_id bigint(20) unsigned NOT NULL,
            sender_user_id bigint(20) unsigned DEFAULT NULL,
            event_id int(11) DEFAULT NULL,
            communication_type enum('email','sms','system_notification') DEFAULT 'email',
            subject varchar(255) DEFAULT NULL,
            message text NOT NULL,
            template_used varchar(100) DEFAULT NULL,
            status enum('pending','sent','failed','delivered','opened') DEFAULT 'pending',
            sent_at timestamp NULL DEFAULT NULL,
            opened_at timestamp NULL DEFAULT NULL,
            error_message text DEFAULT NULL,
            metadata text DEFAULT NULL COMMENT 'JSON metadata',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY fk_communications_recipient (recipient_user_id),
            KEY fk_communications_sender (sender_user_id),
            KEY fk_communications_event (event_id),
            KEY idx_type (communication_type),
            KEY idx_status (status),
            KEY idx_sent_at (sent_at),
            CONSTRAINT fk_communications_recipient FOREIGN KEY (recipient_user_id) REFERENCES {$wpdb->users} (ID) ON DELETE CASCADE,
            CONSTRAINT fk_communications_sender FOREIGN KEY (sender_user_id) REFERENCES {$wpdb->users} (ID) ON DELETE SET NULL,
            CONSTRAINT fk_communications_event FOREIGN KEY (event_id) REFERENCES {$table_prefix}events (id) ON DELETE SET NULL
        ) $charset_collate;";

        // System settings table
        $sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}system_settings (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value text DEFAULT NULL,
            setting_type enum('string','number','boolean','json','html') DEFAULT 'string',
            description text DEFAULT NULL,
            is_public tinyint(1) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_setting_key (setting_key)
        ) $charset_collate;";

        // Execute all SQL statements
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        foreach ($sql as $statement) {
            dbDelta($statement);
        }
    }

    /**
     * Upgrade database schema if needed
     */
    public static function upgradeDatabaseSchema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'osb_events';

        // Check if we need to add missing columns
        $columns = $wpdb->get_col("DESCRIBE {$table_name}", 0);

        $required_columns = array(
            'name' => "ALTER TABLE {$table_name} ADD COLUMN name varchar(255) NOT NULL DEFAULT '' AFTER id",
            'start_date' => "ALTER TABLE {$table_name} ADD COLUMN start_date date DEFAULT NULL AFTER year",
            'start_time' => "ALTER TABLE {$table_name} ADD COLUMN start_time time DEFAULT NULL AFTER start_date",
            'event_time' => "ALTER TABLE {$table_name} ADD COLUMN event_time time DEFAULT NULL AFTER event_date",
            'venue_name' => "ALTER TABLE {$table_name} ADD COLUMN venue_name varchar(255) DEFAULT NULL AFTER venue",
            'venue_address' => "ALTER TABLE {$table_name} ADD COLUMN venue_address text DEFAULT NULL AFTER venue_name",
            'registration_deadline' => "ALTER TABLE {$table_name} ADD COLUMN registration_deadline date DEFAULT NULL AFTER registration_end_date",
            'prize_fund_goal' => "ALTER TABLE {$table_name} ADD COLUMN prize_fund_goal decimal(10,2) DEFAULT 0.00 AFTER max_students_per_school",
            'flyer_url' => "ALTER TABLE {$table_name} ADD COLUMN flyer_url varchar(500) DEFAULT NULL AFTER total_donations"
        );

        // Add missing columns
        foreach ($required_columns as $column => $sql) {
            if (!in_array($column, $columns)) {
                $wpdb->query($sql);
            }
        }

        // Update status enum to include 'cancelled'
        $wpdb->query("ALTER TABLE {$table_name} MODIFY COLUMN status enum('upcoming','live','completed','cancelled') DEFAULT 'upcoming'");
    }

    /**
     * Create custom user roles
     */
    private static function createUserRoles() {
        // School Representative role
        add_role('school_representative', __('School Representative', 'omafuru-spelling-bee'), array(
            'read' => true,
            'osb_manage_school' => true,
            'osb_register_students' => true,
            'osb_upload_documents' => true,
            'osb_view_school_data' => true,
        ));

        // Student role
        add_role('student', __('Student', 'omafuru-spelling-bee'), array(
            'read' => true,
            'osb_view_competition_info' => true,
            'osb_view_own_data' => true,
        ));

        // Parent Guardian role
        add_role('parent_guardian', __('Parent Guardian', 'omafuru-spelling-bee'), array(
            'read' => true,
            'osb_view_children_data' => true,
            'osb_provide_consent' => true,
            'osb_update_student_info' => true,
        ));

        // Add capabilities to administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_capabilities = array(
                'osb_manage_events',
                'osb_manage_schools',
                'osb_manage_students',
                'osb_manage_registrations',
                'osb_manage_documents',
                'osb_manage_donations',
                'osb_manage_sponsors',
                'osb_view_reports',
                'osb_manage_settings',
                'osb_resolve_conflicts',
                'osb_send_communications'
            );

            foreach ($admin_capabilities as $capability) {
                $admin_role->add_cap($capability);
            }
        }
    }

    /**
     * Set default plugin options
     */
    private static function setDefaultOptions() {
        $default_options = array(
            'osb_registration_enabled' => 1,
            'osb_max_students_per_school' => 5,
            'osb_min_students_per_school' => 3,
            'osb_require_parent_consent' => 1,
            'osb_auto_approve_schools' => 0,
            'osb_email_notifications_enabled' => 1,
            'osb_donation_enabled' => 1,
            'osb_prize_distribution' => json_encode(array('first' => 50, 'second' => 30, 'third' => 20)),
            'osb_contact_email' => get_option('admin_email'),
            'osb_organization_name' => 'Omafuru Foundation',
        );

        foreach ($default_options as $option_name => $option_value) {
            if (!get_option($option_name)) {
                update_option($option_name, $option_value);
            }
        }
    }

    /**
     * Create upload directories
     */
    private static function createUploadDirectories() {
        $upload_dirs = array(
            'documents',
            'school-logos',
            'flyers',
            'certificates'
        );

        $upload_base = wp_upload_dir()['basedir'] . '/omafuru-spelling-bee/';

        foreach ($upload_dirs as $dir) {
            $full_path = $upload_base . $dir;
            if (!wp_mkdir_p($full_path)) {
                error_log("Failed to create directory: {$full_path}");
            }

            // Create .htaccess file for security
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<Files ~ \"\\.(php|pl|py|jsp|asp|sh|cgi)$\">\n";
            $htaccess_content .= "Order allow,deny\n";
            $htaccess_content .= "Deny from all\n";
            $htaccess_content .= "</Files>\n";

            file_put_contents($full_path . '/.htaccess', $htaccess_content);
        }
    }
}