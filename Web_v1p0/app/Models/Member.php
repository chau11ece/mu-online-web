<?php
/**
 * Member Model - Eloquent ORM for MuOnline MEMB_INFO table
 * 
 * Represents a player account in the Mu Online game.
 * 
 * @property string $memb___id    Account username
 * @property string $memb__pwd    Account password (hashed)
 * @property string $memb_name    Real name
 * @property string $sno__numb    Personal ID
 * @property string $mail_addr    Email address
 * @property int    $bloc_code    Block status (0=active, 1=banned)
 * @property int    $ctl1_code    Control code
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'MEMB_INFO';
    protected $primaryKey = 'memb___id';
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * Get member statistics (login status, connection info)
     */
    public function stat()
    {
        return $this->hasOne(MemberStat::class, 'memb___id', 'memb___id');
    }
    
    /**
     * Get account credits
     */
    public function credits()
    {
        return $this->hasOne(MemberCredits::class, 'memb___id', 'memb___id');
    }
    
    /**
     * Get all characters on this account
     */
    public function characters()
    {
        return $this->hasMany(Character::class, 'AccountID', 'memb___id');
    }
    
    /**
     * Get web account data (DTweb)
     */
    public function webAccount()
    {
        return $this->hasOne(WebAccount::class, 'memb___id', 'memb___id');
    }
    
    /**
     * Check if account is banned
     */
    public function isBanned(): bool
    {
        return $this->bloc_code == 1;
    }
    
    /**
     * Check if account is online
     */
    public function isOnline(): bool
    {
        return $this->stat && $this->stat->ConnectStat > 0;
    }
    
    /**
     * Scope for active (non-banned) accounts
     */
    public function scopeActive($query)
    {
        return $query->where('bloc_code', 0);
    }
    
    /**
     * Scope for banned accounts
     */
    public function scopeBanned($query)
    {
        return $query->where('bloc_code', 1);
    }
}
