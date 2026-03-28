<?php
/**
 * Twig Template Engine Initialization for Aion Theme
 * 
 * This file initializes Twig for the Aion theme and provides
 * helper functions for template rendering.
 * 
 * Usage:
 *   require_once __DIR__ . '/twig_init.php';
 *   $twig = get_twig();
 *   echo $twig->render('template.html.twig', $data);
 */

require_once '/var/www/html/vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Twig\Extension\DebugExtension;

/**
 * Get or create the Twig environment
 * 
 * @param bool $debug Enable debug mode
 * @return Environment
 */
function get_twig($debug = false) {
    static $twig = null;
    
    if ($twig === null) {
        $loader = new FilesystemLoader(__DIR__ . '/templates');
        
    $twig = new Environment($loader, [
        'cache' => false, // Disable cache for development
        'auto_reload' => true, // Auto-reload templates
        'strict_variables' => false, // Don't throw on undefined variables
        'autoescape' => false, // Disable autoescape - we handle it manually
        'debug' => $debug,
    ]);
        
        if ($debug) {
            $twig->addExtension(new DebugExtension());
        }
        
        // Register custom functions
        register_twig_functions($twig);
    }
    
    return $twig;
}

/**
 * Register custom Twig functions
 * 
 * @param Environment $twig
 */
function register_twig_functions(Environment $twig) {
    // Asset URL helper
    $twig->addFunction(new TwigFunction('asset', function($path) {
        return 'themes/Aion/' . ltrim($path, '/');
    }));
    
    // URL helper for generating links
    $twig->addFunction(new TwigFunction('url', function($page = '', $params = []) {
        $url = '?p=' . urlencode($page);
        foreach ($params as $key => $value) {
            $url .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        return $url;
    }));
    
    // Check if current page is active
    $twig->addFunction(new TwigFunction('is_active', function($page, $currentPage) {
        return $page === $currentPage ? 'active' : '';
    }));
    
    // Translate phrase (get language variable)
    $twig->addFunction(new TwigFunction('phrase', function($phraseName) {
        global $$phraseName;
        return isset($$phraseName) ? $$phraseName : $phraseName;
    }));
    
    // Raw output (for already-escaped HTML)
    $twig->addFunction(new TwigFunction('raw', function($content) {
        return $content;
    }, ['is_safe' => ['html']]));
    
    // Theme URL
    $twig->addFunction(new TwigFunction('theme_url', function() {
        return 'themes/Aion';
    }));
}

/**
 * Render a Twig template
 * 
 * @param string $template Template name (without .html.twig extension)
 * @param array $data Data to pass to the template
 * @return string
 */
function render_template($template, $data = []) {
    $twig = get_twig();
    
    // Add global variables
    $twig->addGlobal('theme_url', 'themes/Aion');
    
    return $twig->render($template . '.html.twig', $data);
}

/**
 * Capture output of a PHP include and return as string
 * 
 * @param string $file File path to include
 * @param array $vars Variables to extract for the include
 * @return string
 */
function capture_include($file, $vars = []) {
    if (!empty($vars)) {
        extract($vars, EXTR_SKIP);
    }
    
    ob_start();
    include $file;
    return ob_get_clean();
}
