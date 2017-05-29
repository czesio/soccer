<?php
namespace App\Handler;

class GameHandler
{
    /**
     * array of all players
     * @var array
     */
    protected $players = array();

    public function getPlayers()
    {
        return $this->players;
    }

    public function __construct($players)
    {
        $this->players = $players;
    }

    /**
     *
     * @param $array
     * @return array - all combination for players
     */
    protected function getCombinationsList($array) {
        $results = array(array( ));
        foreach ($array as $element)
            foreach ($results as $combination)
                array_push($results, array_merge(array($element), $combination));

        return $results;
    }

    /**
     * @param $combinationsElementsNo
     * @return array - all combinations for selected tem player number
     */
    protected function getPlayersCombinationsByElementsNo($combinationsElementsNo)
    {
        $arrayRes = array();
        foreach ($this->getCombinationsList($this->getPlayers()) as $combination) {
            if ($combinationsElementsNo == count($combination)) {
                $arrayRes[] = $combination;
            }
        }
        return $arrayRes;
    }

    /**
     * @param $combinationsList
     * @return array - all games combination (without replicated records)
     */
    protected function getGamesCombination($combinationsList)
    {
        $arrayCommon = array();
        $counter = 1;
        foreach ($combinationsList as $key => $value) {
            $selectedAr = $value;
            foreach ($combinationsList AS $k => $v) {
                if ($key <> $k) {
                    if (!in_array($v[0], $selectedAr) && !in_array($v[1], $selectedAr)) {
                        $break = false;
                        foreach ($arrayCommon AS $k1 => $v1) {
                            if(($v == $v1[0] || $v1[0] == $selectedAr) && ($v1[1] == $v || $v1[1] == $selectedAr)) {
                                $break = true;
                                break;
                            }
                        }
                        if ($break) {
                            continue;
                        }
                        $arrayCommon[$counter][] = $selectedAr;
                        $arrayCommon[$counter][] = $v;
                        ++$counter;
                    }
                }
            }
        }
        return $arrayCommon;
    }

    /**
     * Get all games variants for selected players number in team
     *
     * @param int $teamPlayersNo
     * @return array
     */
    public function makeGamesForPlayersNo($teamPlayersNo = 2)
    {
        $combinations = $this->getPlayersCombinationsByElementsNo($teamPlayersNo);
        $playersCombinations = $this->getGamesCombination($combinations);
        return $playersCombinations;
    }
}