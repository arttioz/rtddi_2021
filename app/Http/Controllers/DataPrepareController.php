<?php

namespace App\Http\Controllers;

use App\Models\DeathData;
use App\Models\EclaimData;
use App\Models\PolisData;
use App\Models\UnionData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isNull;

class DataPrepareController extends Controller
{
    public function __construct()
    {

    }

    public function checkDeathRTIDataQuality(){

    }

    public function unionData(){

        $death = DeathData::all();
        $polis = PolisData::all();
        $eclaim = EclaimData::all();

        $this->copyToUnion($death,"Death");
        $this->copyToUnion($polis,"Polis");
        $this->copyToUnion($eclaim,"Eclaim");

    }
    public function copyToUnion($data, $basename){
        foreach ($data as $row){
            $uData = new UnionData();
            $uData->db_id        = $row->db_id       ;
            $uData->cid          = $row->cid         ;
            $uData->firstname    = $row->firstname   ;
            $uData->lastname     = $row->lastname    ;
            $uData->birthdate    = $row->birthdate   ;
            $uData->deathdate    = $row->deathdate   ;
            $uData->accprov      = $row->accprov     ;
            $uData->year         = $row->year        ;
            $uData->accdatetime  = $row->accdatetime ;
            $uData->age          = $row->age         ;
            $uData->basename = $basename;

            if ($basename == "Death"){
                $uData->isDeathCert = 1;
                $uData->icd10        = $row->icd10       ;
            }else if ($basename == "Polis"){
                $uData->isPolis = 1;
            }else if ($basename == "Eclaim"){
                $uData->isEclaim = 1;
            }

            $uData->save();
        }
    }

    public function prepareDeathCertDataForJoin(){

    }

    public function prepareEclaimDataForJoin(){

    }

    public function preparePolisDataForJoin(){

    }

    public function joinDataLogic(){

        set_time_limit(5000);

        $dataMain = UnionData::get();
        $dataMainArr = [];

        foreach ($dataMain as $row   ) {
            $dataMainArr[$row->id] = $row;
        }

         $count = count($dataMain);
        for($id = 1; $id < $count; $id++){
            $matchResults = [];
            $row_1 = $dataMainArr[$id];

            for($nextID = $id+1; $nextID < $count; $nextID++){

                $row_2 = $dataMainArr[$nextID];

                $matchResult = $this->checkMatch($row_1,$row_2);
                if(count($matchResult) > 0){
                    $matchResults[] = $matchResult;

                    if(isNull($row_2->match_result) ){
                        $matchChild = [];
                    }else{
                        $matchChild = json_decode( $row_2->match_result);
                    }

                    $matchChild[] = $matchResult;
                    $row_2->match_result = json_encode($matchChild) ;
                    $row_2->save();
                }
            }
            $row_1->match_result = json_encode($matchResults) ;
            $row_1->save();
        }

    }

    public function checkMatch($row_1, $row_2){
        //1.1 ID และ วันเกิดเหตุ/ตาย
        //1.2 ชื่อ-สกุล และ วันเกิดเหตุ/ตาย และ จังหวัดเกิดเหตุ/ตาย
        //1.3 ชื่อ-สกุล และ วันเกิดเหตุ/ตาย
        //1.4 ชื่อ-สกุล และ จังหวัด
        //1.5 ID
        //1.6 ชื่อ-สกุล

        $matchResult = [];

        $deathDateMatch = false;
        $IDMatch = false;
        $nameMatch = false;
        $provMatch = false;


        if ( !isNull($row_1->deathdate) & !isNull($row_2->deathdate)){
            $difDate = Carbon::parse( $row_1->deathdate )->diffInDays( $row_2->deathdate );
            if ($difDate == 0){
                $deathDateMatch = true;
            }
        }

        if (!isNull($row_1->cid) & !isNull($row_2->cid)){

            if ( $row_1->cid == $row_2->cid )
            {
                $IDMatch = true;
            }
        }

        if (!isNull($row_1->firstname) & !isNull($row_2->firstname)  &
            !isNull($row_1->lastname) & !isNull($row_2->lastname) ){

            if ( $row_1->firstname == $row_2->firstname &
                $row_1->lastname == $row_2->lastname)
            {
                $nameMatch = true;
            }
        }

        if (!isNull($row_1->accprov) & !isNull($row_2->accprov)){

            if ( $row_1->accprov == $row_2->accprov )
            {
                $provMatch = true;
            }
        }



        // 1.1 ID และ วันเกิดเหตุ/ตาย
        if ( $IDMatch && $deathDateMatch  ){
                $matchResult['protocal'] = "1 ID และ วันเกิดเหตุ/ตาย";
                $matchResult['id'] = $row_2->id;

        }

        // 1.2 ชื่อ-สกุล และ วันเกิดเหตุ/ตาย และ จังหวัดเกิดเหตุ/ตาย
       else  if ( $nameMatch && $deathDateMatch && $provMatch ){

            $matchResult['protocal'] = "2 ชื่อ-สกุล และ วันเกิดเหตุ/ตาย และ จังหวัดเกิดเหตุ/ตาย";
            $matchResult['id'] = $row_2->id;
        }

        // 1.3 ชื่อ-สกุล และ วันเกิดเหตุ/ตาย
       else if ( $nameMatch && $deathDateMatch ){

            $matchResult['protocal'] = "ชื่อ-สกุล และ วันเกิดเหตุ/ตาย ";
           $matchResult['id'] = $row_2->id;
        }

        // 1.4 ชื่อ-สกุล และ จังหวัด
       else if ( $nameMatch && $provMatch ){

            $matchResult['protocal'] = "ชื่อ-สกุล และ จังหวัด ";
           $matchResult['id'] = $row_2->id;
        }

        // 1.5 ID
       else if ( $IDMatch){

            $matchResult['protocal'] = "ID";
           $matchResult['id'] = $row_2->id;
        }

        // 1.6 ชื่อ-สกุล
       else  if ( $nameMatch){

            $matchResult['protocal'] = "ชื่อ-สกุล";
           $matchResult['id'] = $row_2->id;
        }

       return $matchResult;

    }
}
