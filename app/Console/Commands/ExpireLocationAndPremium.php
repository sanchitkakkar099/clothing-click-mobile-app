<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\location;

class ExpireLocationAndPremium extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expire:locationandpremium';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire location and premium subscription';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $currentDate = date('Y-m-d H:i:s');

        $premiumSubscriptions = User::where([['is_premium_user',1],['premium_end_at','<=',$currentDate],['is_active',1],['is_delete',0]])->get();

        if($premiumSubscriptions->count() > 0){
            foreach($premiumSubscriptions as $subscription){
                User::find($subscription->id)->update(['is_premium_user' => 0]);
            }
        }

        $locations = location::where([['is_delete',0],['is_paid',1],['expire_at','<=',$currentDate]])->whereNotnull('expire_at')->get();

        if($locations->count() > 0){
            foreach($locations as $location){
                location::find($location->id)->update(['is_paid' => 0]);
            }
        }

        return 0;
    }
}
