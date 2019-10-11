<?php

namespace App\Jobs;

use App\Standing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateStandingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $standing;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($standing)
    {
        $this->standing = $standing;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $newStanding = Standing::updateOrCreate(
            ['team' => $this->standing['team']],
            $this->standing
        );
    }
}
