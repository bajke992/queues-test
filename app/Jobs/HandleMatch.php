<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Match;
use App\Models\MatchId;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleMatch extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    
    protected $sample, $odds, $matchRepo, $matchHandler, $matchIdRepo, $match, $oddRepo;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sample, $odds, $matchRepo, $matchHandler, $matchIdRepo, $match, $oddRepo)
    {
        $this->sample = $sample;
        $this->odds = $odds;
        $this->matchRepo = $matchRepo;
        $this->matchHandler = $matchHandler;
        $this->matchIdRepo = $matchIdRepo;
        $this->match = $match;
        $this->oddRepo = $oddRepo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->sample !== null) {
//                   dd($sample);
            $this->sample->incrementCount();
            $this->matchRepo->save($this->sample);
            $this->matchHandler->incrementWinOdds($this->odds, $this->sample);
        } else {
            $this->sample = Match::make();
            $this->matchRepo->save($this->sample);
            $this->matchHandler->makeOdds($this->sample, $this->odds, $this->oddRepo);
            $this->matchHandler->incrementWinOdds($this->odds, $this->sample);
        }

        $matchId = MatchId::make($this->match->matchId);
        $this->matchIdRepo->save($matchId);

//        $job->delete();
    }
}
