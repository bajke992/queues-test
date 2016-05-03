<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Repositories\MatchIdRepositoryInterface;
use App\Repositories\MatchRepositoryInterface;
use App\Repositories\OddRepositoryInterface;
use App\Services\MatchHandler;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleMatches extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $matchHandler;
    protected $matches;
    protected $gamesBySport;
    protected $matchRepo;
    protected $oddRepo;
    protected $matchIdRepo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MatchHandler $matchHandler, $matches, $gamesBySport, MatchRepositoryInterface $matchRepo, OddRepositoryInterface $oddRepo, MatchIdRepositoryInterface $matchIdRepo)
    {
        $this->matchHandler = $matchHandler;
        $this->matches = $matches;
        $this->gamesBySport = $gamesBySport;
        $this->matchRepo = $matchRepo;
        $this->oddRepo = $oddRepo;
        $this->matchIdRepo = $matchIdRepo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->matchHandler->handle($this->matches, $this->gamesBySport, $this->matchRepo, $this->oddRepo, $this->matchIdRepo);
    }
}
