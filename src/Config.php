<?php

declare(strict_types=1);

namespace App;

/**
 * Centralized configuration management
 * Reads from environment variables with sensible defaults
 */
class Config
{
    private static ?Config $instance = null;
    private array $config = [];

    private function __construct()
    {
        $this->loadDefaults();
        $this->loadFromEnvironment();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get a configuration value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $instance = self::getInstance();
        return $instance->config[$key] ?? $default;
    }

    /**
     * Get all configuration as array
     */
    public static function all(): array
    {
        return self::getInstance()->config;
    }

    /**
     * Check if a feature is enabled
     */
    public static function feature(string $name): bool
    {
        return (bool) self::get('feature_' . $name, false);
    }

    /**
     * Load default configuration values
     */
    private function loadDefaults(): void
    {
        $this->config = [
            // Site Identity
            'site_name' => 'DocStack',
            'site_tagline' => 'Documentation made simple',
            'site_emoji' => '📚',
            'site_url' => '',

            // Branding - Images
            'logo_url' => '',
            'logo_width' => '120',
            'favicon_url' => '',
            'favicon_emoji' => '',
            'favicon_letter' => '',
            'favicon_show_letter' => true,

            // Branding - External Link (top-right)
            'external_link_name' => '',
            'external_link_url' => '',
            'external_link_logo' => '',

            // Branding - Footer
            'footer_text' => '© ' . date('Y') . ' DocStack. All Rights Reserved.',

            // Colors (CSS custom properties)
            'color_primary' => '#3b82f6',
            'color_primary_hover' => '#2563eb',

            // Security
            'docs_password' => '',
            'admin_password' => '',

            // Features
            'feature_editing' => true,
            'feature_dark_mode' => true,
            'feature_search' => false,
            'feature_toc' => true,

            // Content
            'content_dir' => 'content',
        ];
    }

    /**
     * Load configuration from environment variables
     */
    private function loadFromEnvironment(): void
    {
        $envMap = [
            // Site Identity
            'SITE_NAME' => 'site_name',
            'SITE_TAGLINE' => 'site_tagline',
            'SITE_EMOJI' => 'site_emoji',
            'SITE_URL' => 'site_url',

            // Branding - Images
            'LOGO_URL' => 'logo_url',
            'LOGO_WIDTH' => 'logo_width',
            'FAVICON_URL' => 'favicon_url',
            'FAVICON_EMOJI' => 'favicon_emoji',
            'FAVICON_LETTER' => 'favicon_letter',
            'FAVICON_SHOW_LETTER' => 'favicon_show_letter',

            // Branding - External Link
            'EXTERNAL_LINK_NAME' => 'external_link_name',
            'EXTERNAL_LINK_URL' => 'external_link_url',
            'EXTERNAL_LINK_LOGO' => 'external_link_logo',

            // Branding - Footer
            'FOOTER_TEXT' => 'footer_text',

            // Colors
            'COLOR_PRIMARY' => 'color_primary',
            'COLOR_PRIMARY_HOVER' => 'color_primary_hover',

            // Security
            'DOCS_PASSWORD' => 'docs_password',
            'ADMIN_PASSWORD' => 'admin_password',

            // Features
            'FEATURE_EDITING' => 'feature_editing',
            'FEATURE_DARK_MODE' => 'feature_dark_mode',
            'FEATURE_SEARCH' => 'feature_search',
            'FEATURE_TOC' => 'feature_toc',

            // Content
            'CONTENT_DIR' => 'content_dir',
        ];

        foreach ($envMap as $envKey => $configKey) {
            $value = $_ENV[$envKey] ?? getenv($envKey);

            if ($value !== false && $value !== '') {
                // Convert string booleans
                if ($value === 'true') {
                    $value = true;
                } elseif ($value === 'false') {
                    $value = false;
                }

                $this->config[$configKey] = $value;
            }
        }
    }

    /**
     * Get branding data for templates
     */
    public static function getBranding(): array
    {
        return [
            'site_name' => self::get('site_name'),
            'site_tagline' => self::get('site_tagline'),
            'site_emoji' => self::get('site_emoji'),
            'site_url' => self::get('site_url'),
            'logo_url' => self::get('logo_url'),
            'logo_width' => self::get('logo_width'),
            'favicon_url' => self::get('favicon_url'),
            'favicon_emoji' => self::get('favicon_emoji'),
            'favicon_letter' => self::get('favicon_letter'),
            'favicon_show_letter' => self::get('favicon_show_letter'),
            'external_link_name' => self::get('external_link_name'),
            'external_link_url' => self::get('external_link_url'),
            'external_link_logo' => self::get('external_link_logo'),
            'footer_text' => self::get('footer_text'),
            'color_primary' => self::get('color_primary'),
            'color_primary_hover' => self::get('color_primary_hover'),
        ];
    }
}
