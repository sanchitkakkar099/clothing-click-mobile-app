<?php 
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RestrictedUsername implements Rule
{
    protected $restricted = [];

    public function __construct()
    {
        // Fetch all restricted usernames from the database as lowercase for better matching
        $this->restricted = DB::table('restricted_usernames')->pluck('names')->map(function ($name) {
            return strtolower($name);
        })->toArray();
    }

    public function passes($attribute, $value)
    {
        // Convert input username to lowercase for case-insensitive checking
        $username = strtolower($value);

        // Check if the username contains any restricted word
        foreach ($this->restricted as $word) {
            if (!empty($word) && strpos($username, $word) !== false) {
                return false; // ❌ Username contains a restricted word
            }
        }
        return true; // ✅ Username is allowed
    }

    public function message()
    {
        return 'The :attribute contains a restricted word and is not allowed.';
    }
}
