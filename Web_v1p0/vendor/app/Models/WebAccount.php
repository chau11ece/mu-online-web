<?php
/**
 * WebAccount Model - Eloquent ORM for DTweb_GM_Accounts table
 * 
 * Stores web admin/GM account data.
 * 
 * @property string $name     Account username
 * @property int    $gm_level GM level (666=super admin, 8+=GM)
 * @property string $ip       Allowed IP address
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebAccount extends Model
{
    protected $table = 'DTweb_GM_Accounts';
    protected $primaryKey = 'name';
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * Get the member account
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'name', 'memb___id');
    }
    
    /**
     * Check if IP is allowed
     */
    public function isIpAllowed(string $ip): bool
    {
        // Wildcard allows all IPs
        if ($this->ip === '%' || $this->ip === '*') {
            return true;
        }
        
        return $this->ip === $ip;
    }
    
    /**
     * Check if account is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->gm_level === 666;
    }
    
    /**
     * Check if account is GM
     */
    public function isGM(): bool
    {
        return $this->gm_level >= 8;
    }
    
    /**
     * Scope for admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('gm_level', 666);
    }
    
    /**
     * Scope for GMs
     */
    public function scopeGMs($query)
    {
        return $query->where('gm_level', '>=', 8);
    }
}
