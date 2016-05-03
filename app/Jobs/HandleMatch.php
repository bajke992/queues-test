<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Match;
use App\Models\MatchId;
use App\Models\Odd;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleMatch extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    
    protected $sample, $odds, $matchRepo, $matchHandler, $matchIdRepo, $match, $oddRepo;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(/*$sample, */$odds, $matchRepo, $matchHandler, $matchIdRepo, $match, $oddRepo)
    {
//        $this->sample = $sample;
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
        $date = date('Y-m-d H:i:s');
        $rand = rand(0, 9999);
        if ($this->matchIdRepo->findByMatchId($this->match->matchId) || !Odd::hasAllOdds($this->match->odds)) {
//            Log::info($date . " - " . $rand . " HandleMatch exit!");
            $this->delete();
            return;
        }
        $this->sample = $this->matchRepo->matchOdds(array_slice($this->odds, 0, 6));

        if ($this->sample !== null) {
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
        
        $this->delete();
    }
}
