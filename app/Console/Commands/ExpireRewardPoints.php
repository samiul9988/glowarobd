<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RewardPointLog;

class ExpireRewardPoints extends Command
{
    protected $signature = 'reward:expire-points';

    protected $description = 'Expire reward points for users once the expiration time is reached';

    public function handle()
    {
        $users = User::where('reward_point_expires_at', '<', now()->startOfDay())->get();

        foreach ($users as $user) {
            $expiredPoints = $user->point_balance;

            if ($expiredPoints > 0) {
                $user->point_balance = 0;
                $user->save();

                $rewardlog                = new RewardPointLog();
                $rewardlog->user_id       = $user->id;
                $rewardlog->activity_type = 'Expired';
                $rewardlog->activity      = 'ExpiredTime';
                $rewardlog->earned        = 0;
                $rewardlog->spent         = $expiredPoints;
                $rewardlog->activity_str  = 'Expired ' . $expiredPoints . ' Reward Points for time over';
                $rewardlog->save();
            }
        }

        return Command::SUCCESS;
    }
}
