<?php

namespace App\Console;

use App\Service\SchoolService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
//        $schedule->call(function (){
//            DB::table('roles')
//                ->insert([
//                    'role' => 'a',
//                    'description' => 'b'
//                ]);
//        });
//        $schedule->call(function (){
//            $money = SchoolService::getEcardBalance('2015060107012', '123456');
//            if ($money !== false){
//                if ($money <= 20){
//                    $app = app('wechat.official_account');
//                    $app->template_message->send([
//                        'touser' => 'o6CILwb4CdW3hYUPhwQuE0jxwNts',
//                        'template_id' => 'YJ4wMxfC76-SBss1g7t3E1nCrb3JEH2FbzAv9cJbthI',
//                        'url' => '',
//                        'data' => [
//                            'key1' => [$money, '#F00']
//                        ],
//                    ]);
//                }
//            }
//        });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
