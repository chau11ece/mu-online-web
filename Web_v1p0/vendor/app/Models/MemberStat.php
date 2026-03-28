<?php
/**
 * MemberStat Model - Eloquent ORM for MuOnline MEMB_STAT table
 * 
 * Tracks member connection status and statistics.
 * 
 * @property string $memb___id   Account username
 * @property int    $ConnectStat Connection status (0=offline, 1+=online)
 * @property string $ConnectTM   Last connection time
 * @property string $DisConnectTM Last disconnect time
 * @property string $IP          IP address
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberStat extends Model
{
    protected $table = 'MEMB_STAT';
    protected $primaryKey = 'memb___id';
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * Get the member account
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'memb___id', 'memb___id');
    }
    
    /**
     * Check if member is online
     */
    public function isOnline(): bool
    {
        return $this->ConnectStat > 0;
    }
    
    /**
     * Scope for online members
     */
    public function scopeOnline($query)
    {
        return $query->where('ConnectStat', '>', 0);
    }
    
    /**
     * Scope for offline members
     */
    public static function getOnlineCount(): int
    {
        return static::where('ConnectStat', '>', 0)->count();
    }
    
    /**
     * Scope for offline members
     */
    public function scopeOffline($query)
    {
        return $query->where('ConnectStat', 0);
    }
}
