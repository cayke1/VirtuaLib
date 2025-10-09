<?php
/**
 * View Utility - Shared view rendering system for SOA services
 */

class View {
    private static $basePath = '';
    
    /**
     * Set the base path for views
     */
    public static function setBasePath($path) {
        self::$basePath = rtrim($path, '/') . '/';
    }
    
    /**
     * Render a view file with data
     */
    public static function render($viewFile, $data = []) {
        $viewPath = self::$basePath . $viewFile . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View file not found: $viewPath");
        }
        
        // Extract data variables for use in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        include $viewPath;
        
        // Get the content and clean the buffer
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * Render and echo a view
     */
    public static function display($viewFile, $data = []) {
        echo self::render($viewFile, $data);
    }
    
    /**
     * Escape HTML output
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format date
     */
    public static function formatDate($date, $format = 'd/m/Y H:i') {
        return date($format, strtotime($date));
    }
}
