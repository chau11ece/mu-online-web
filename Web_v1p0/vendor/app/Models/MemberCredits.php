<?php
/**
 * MemberCredits Model - Eloquent ORM for MuOnline MEMB_CREDITS table
 * 
 * Tracks member account credits (premium currency).
 * 
 * @property string $memb___id Account username
 * @property int     $Credits   Number of credits
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCredits extends Model
{
    protected $table = 'MEMB_CREDITS';
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
     * Add credits to account
     */
    public function addCredits(int $amount): bool
    {
        $this->Credits += $amount;
        return $this->save();
    }
    
    /**
     * Remove credits from account
     */
    public function removeCredits(int $amount): bool
    {
        if ($this->Credits < $amount) {
            return false;
        }
        $this->Credits -= $amount;
        return $this->save();
    }
}
