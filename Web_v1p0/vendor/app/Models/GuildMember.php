<?php
/**
 * GuildMember Model - Eloquent ORM for MuOnline GuildMember table
 * 
 * Represents membership in a guild.
 * 
 * @property string $Name    Character name
 * @property string $G_Name  Guild name
 * @property int    $G_Status Member status (0=normal, 1=assistant, 2=leader)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuildMember extends Model
{
    protected $table = 'GuildMember';
    public $timestamps = false;
    
    /**
     * The primary key is not 'id'
     */
    protected $primaryKey = null;
    public $incrementing = false;
    
    /**
     * Get the guild
     */
    public function guild()
    {
        return $this->belongsTo(Guild::class, 'G_Name', 'G_Name');
    }
    
    /**
     * Get the character
     */
    public function character()
    {
        return $this->belongsTo(Character::class, 'Name', 'Name');
    }
    
    /**
     * Check if member is guild leader
     */
    public function isLeader(): bool
    {
        return $this->G_Status === 2;
    }
    
    /**
     * Check if member is assistant
     */
    public function isAssistant(): bool
    {
        return $this->G_Status === 1;
    }
}
