<?php namespace App\Repositories;

use App\Models\Match;
use App\Models\Odd;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentOddRepository implements OddRepositoryInterface
{
    /**
     * @var Odd
     */
    private $odd;

    /**
     * EloquentOddRepository constructor.
     *
     * @param Odd $odd
     */
    public function __construct(Odd $odd)
    {
        $this->odd = $odd;
    }

    /**
     * @return Collection|Odd[]
     */
    public function getAll()
    {
        return $this->odd->all();
    }

    /**
     * @param Match $match
     *
     * @return Collection|Odd[]
     */
    public function getAllForMatch(Match $match)
    {
        return $match->odds;
    }

    /**
     * @param Odd $odd
     */
    public function save(Odd $odd)
    {
        $odd->save();
    }

    /**
     * @param Match $match
     * @param Odd   $odd
     */
    public function saveOddToMatch(Match $match, Odd $odd)
    {
        $match->odds()->save($odd);
    }

    /**
     * @param Odd $odd
     */
    public function delete(Odd $odd)
    {
        $odd->delete();
    }

    /**
     * @param   mixed $args
     * @return  Odd     $odd
     */
    public function search($args)
    {
        $query1 = $this->odd->query();

        foreach($args as $k => $arg){
            $query1->OrWhere('name', $k);
            $query1->where('value', $arg);
        }

        $query1->take(1);
        /** @var Odd $result */
        $result = $query1->get()->first();

        if($result === null) {
            return [];
        }

        $query2 = $this->odd->query();
        $query2->where('match_id', $result->getMatchId());
        $result2 = $query2->get();

        return $result2;
    }

    public function searchMatch($odds)
    {
        $query1 = $this->odd->query();

        foreach($odds as $odd) {
            $query1->orWhere('name', $odd['name']);
            $query1->where('value', $odd['value']);
        }
        
        $query1->take(1);
        $result = $query1->get()->first();

        if($result === null) {
            return null;
        }
        
        return $result->match;
        
    }
}