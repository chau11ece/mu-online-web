<?php
/**
 * Character Model - Eloquent ORM for MuOnline Character table
 * 
 * Represents a player character in the Mu Online game.
 * 
 * @property int    $AccountID    Player account ID
 * @property string $Name         Character name
 * @property int    $cLevel       Character level
 * @property int    $Class       Character class (0=DW, 16=DK, 32=ELF, etc.)
 * @property int    $Resets      Number of resets
 * @property int    $GrandResets Number of grand resets
 * @property int    $Money       Zen money
 * @property int    $PkCount     PK count
 * @property int    $CtlCode     Control code (0=normal, 1=banned, 8+=GM)
 * @property int    $MapNumber   Current map
 * @property string $QuestNumber Quest progress
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $table = 'Character';
    protected $primaryKey = 'Name';
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * Character class names based on Class field
     */
    public const CLASSES = [
        0 => 'Dark Wizard',
        1 => 'Soul Master',
        2 => 'Grand Master',
        16 => 'Dark Knight',
        17 => 'Blade Knight',
        18 => 'Dragon Knight',
        32 => 'Elf',
        33 => 'Muse Elf',
        48 => 'Magic Gladiator',
        49 => 'Duel Master',
        64 => 'Dark Lord',
        65 => 'Lord Emperor',
        80 => 'Summoner',
        81 => 'Bloody Summoner',
        82 => 'Dimension Master',
        96 => 'Fighter',
        98 => 'Rage Fighter',
    ];
    
    /**
     * Get the account associated with this character
     */
    public function account()
    {
        return $this->belongsTo(Member::class, 'AccountID', 'memb___id');
    }
    
    /**
     * Get guild membership
     */
    public function guild()
    {
        return $this->hasOne(GuildMember::class, 'Name', 'Name');
    }
    
    /**
     * Get class name
     */
    public function getClassNameAttribute(): string
    {
        return self::CLASSES[$this->Class] ?? 'Unknown';
    }
    
    /**
     * Scope for top characters by level
     */
    public function scopeTopByLevel($query, $limit = 10)
    {
        return $query->where('CtlCode', 0)
                     ->orderBy('cLevel', 'desc')
                     ->orderBy('Resets', 'desc')
                     ->limit($limit);
    }
    
    /**
     * Scope for top characters by resets
     */
    public function scopeTopByResets($query, $limit = 10)
    {
        return $query->where('CtlCode', 0)
                     ->orderBy('Resets', 'desc')
                     ->orderBy('cLevel', 'desc')
                     ->limit($limit);
    }
    
    /**
     * Scope for GM characters
     */
    public function scopeGMs($query)
    {
        return $query->whereBetween('CtlCode', [8, 32])
                     ->orderBy('CtlCode', 'desc');
    }
    
    /**
     * Check if character is online
     */
    public function isOnline(): bool
    {
        $stat = MemberStat::where('memb___id', $this->AccountID)->first();
        return $stat && $stat->ConnectStat > 0;
    }
}
