<?php namespace App\Services;

use App\Jobs\HandleMatch;
use App\Models\Match;
use App\Models\MatchId;
use App\Models\Odd;
use App\Repositories\MatchIdRepositoryInterface;
use App\Repositories\MatchRepositoryInterface;
use App\Repositories\OddRepositoryInterface;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class MatchHandler
{
    
    use DispatchesJobs;

    /**
     * @param array                      $matches
     * @param array                      $categories
     * @param MatchRepositoryInterface   $matchRepo
     * @param OddRepositoryInterface     $oddRepo
     * @param MatchIdRepositoryInterface $matchIdRepo
     */
    public function handle(array $matches, array $categories, MatchRepositoryInterface $matchRepo, OddRepositoryInterface $oddRepo, MatchIdRepositoryInterface $matchIdRepo)
    {
        foreach ($matches as $match) {
            if ($matchIdRepo->findByMatchId($match->matchId) || !Odd::hasAllOdds($match->odds)) {
                continue;
            }

            $odds = $this->getOdds($match, $categories);
            
            $job = new HandleMatch($odds, $matchRepo, $this, $matchIdRepo, $match, $oddRepo);
            $this->dispatch($job);
        }
    }

    /**
     * @param $match
     * @param $categories
     *
     * @return array
     */
    public function getOdds($match, $categories)
    {
        $result = [];
        foreach ($match->odds as $odds) {
            if (Odd::checkOdd($odds->id)) {
                foreach ($odds->subgames as $subgame) {
                    if (Odd::checkSubGame($odds->id, $subgame->id)) {
                        $result[] = [
                            'name'      => Odd::$ODD_NAMES[$odds->id][$subgame->id],
                            'category'  => Odd::getNameByCategory($categories, $odds->id - 1),
                            'value'     => property_exists($subgame, 'value') ? $subgame->value : null,
                            'winStatus' => property_exists($subgame, 'winStatus') ? $subgame->winStatus : null
                        ];
                    }
                }
            }
        }

        return $result;
    }

    public function getOddsSearch($match)
    {
        $result = [];
        foreach ($match->odds as $odds) {
            if (Odd::checkOdd($odds->id)) {
                foreach ($odds->subgames as $subgame) {
                    if (Odd::checkSubGame($odds->id, $subgame->id)) {
                        $result[] = [
                            'name'      => Odd::$ODD_NAMES[$odds->id][$subgame->id],
                            'value'     => property_exists($subgame, 'value') ? $subgame->value : null,
                            'winStatus' => property_exists($subgame, 'winStatus') ? $subgame->winStatus : null
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param       $odds
     * @param Match $match
     */
    public function incrementWinOdds($odds, Match $match)
    {
//        $tmp_odds = $match->odds->slice(0, 6);
        $tmp_odds = $match->odds;

        foreach ($tmp_odds as $k => $tmp_odd) {
            if (array_key_exists($k, $odds) && $odds[$k]['winStatus'] === "WIN") {
                $tmp_odd->incrementWinCount();
                $tmp_odd->save();
            }
        }
    }

    /**
     * @param Match                  $match
     * @param                        $odds
     * @param OddRepositoryInterface $oddRepo
     */
    public function makeOdds(Match $match, $odds, OddRepositoryInterface $oddRepo)
    {
        foreach ($odds as $odd) {
            $odd_tmp = Odd::make($odd['name'], $odd['category'], $odd['value']);
            $oddRepo->saveOddToMatch($match, $odd_tmp);
        }
    }
}