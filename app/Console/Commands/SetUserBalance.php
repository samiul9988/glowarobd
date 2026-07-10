<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:setuserbalance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set User Balance to 1.00';

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
        User::all()->each(function ($user) {
                $user->balance =  1.00;
                $user->save();
            });
    }
}
