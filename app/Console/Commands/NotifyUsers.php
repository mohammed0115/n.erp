<?php

namespace App\Console\Commands;

use App\Notifications\ExpiredNotification;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notification;

class NotifyUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expierd:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        //
        $users = User::whereNotNull('expires_at')
            ->whereMonth('expires_at', Carbon::now()->month)
            ->get();
        foreach($users as $user) {
//            $diffInDays = $user->deadline_date->diff(Carbon::now())->days;
//            Notification::send()
            $user->notify(new ExpiredNotification());
//            $user->notify("Your deadline is in 4 day!");
        }
    }
}
