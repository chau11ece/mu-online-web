<?php
/**
 * Guild Model - Eloquent ORM for MuOnline Guild table
 * 
 * Represents a guild in the Mu Online game.
 * 
 * @property string $G_Name    Guild name
 * @property string $G_Mark    Guild mark (emblem binary)
 * @property int    $G_Score   Guild score
 * @property string $G_Master  Guild master name
 * @property int    $G_Count   Member count
 * @property int    $G_Level   Guild level
 * @property int    $Resets    Guild resets
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $table = 'Guild';
    protected $primaryKey = 'G_Name';
    public $incrementing = false;
    public $timestamps = false;
    
    /**
     * Get all members of this guild
     */
    public function members()
    {
        return $this->hasMany(GuildMember::class, 'G_Name', 'G_Name');
    }
    
    /**
     * Get the guild master character
     */
    public function master()
    {
        return $this->hasOne(Character::class, 'Name', 'G_Master');
    }
    
    /**
     * Get top guilds by score
     */
    public function scopeTopByScore($query, $limit = 10)
    {
        return $query->orderBy('G_Score', 'desc')->limit($limit);
    }
    
    /**
     * Get guild logo URL (encoded)
     */
    public function getLogoUrlAttribute(): string
    {
        return urlencode(bin2hex($this->G_Mark));
    }
}
