<?php
/**
 * WebSettings Model - Eloquent ORM for DTweb_settings table
 * 
 * Stores website configuration.
 * 
 * @property int    $id              Settings ID
 * @property string $title           Website title (base64 encoded)
 * @property string $keywords         SEO keywords (base64 encoded)
 * @property string $meta            SEO description (base64 encoded)
 * @property string $theme           Active theme
 * @property string $server_name     Server name (base64 encoded)
 * @property string $server_ip        Server IP
 * @property string $server_port     Server port
 * @property string $server_version  Server version (e.g., "97d")
 * @property int    $server_exp       Experience rate
 * @property int    $server_max      Max level
 * @property int    $server_drop     Drop rate
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSettings extends Model
{
    protected $table = 'DTweb_settings';
    public $timestamps = false;
    
    /**
     * Decode title
     */
    public function getTitle(): string
    {
        return base64_decode($this->title) ?: 'MU Online';
    }
    
    /**
     * Decode keywords
     */
    public function getKeywords(): string
    {
        return base64_decode($this->keywords) ?: 'mu online, game server';
    }
    
    /**
     * Decode meta description
     */
    public function getMetaDescription(): string
    {
        return base64_decode($this->meta) ?: 'MuOnline Server';
    }
    
    /**
     * Decode server name
     */
    public function getServerName(): string
    {
        return base64_decode($this->server_name) ?: 'MU Online';
    }
    
    /**
     * Get current settings (singleton)
     */
    public static function getSettings(): ?self
    {
        return static::first();
    }
}
