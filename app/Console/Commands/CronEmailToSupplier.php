<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use App\Mail\SendEmailToSupplier;
use Illuminate\Support\Facades\Mail;

class CronEmailToSupplier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CronEmailToSupplier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email to supplier';

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
        $user = User::find(11);

        Mail::to($user->email)->send(new SendEmailToSupplier($user));
    }
}
