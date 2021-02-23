<?php

namespace App\Http\Controllers;

use App\Models\healthhack;
use App\Models\ICD10;
use Illuminate\Http\Request;

class PrepareDataController extends Controller
{
    public function prepareDiag(){

        set_time_limit(0);

        $healths = healthhack::get();

        foreach ($healths as $health){

          $diags =  $health->diagnosis;
          $diags = $this->splitList($diags);

          $index = 1;
          foreach ($diags as $diag){
              $health->{"d".$index} = $diag;
              $index++;
          }

          $health->save();
        }

    }

    public function prepareICD10(){

        set_time_limit(0);

        $healths = healthhack::get();

        $icdV = ICD10::where("diagcode", 'like', 'V%')->get();
        $icdS = ICD10::where("diagcode", 'like', 'S%')->get();

        $icdVs = [];
        foreach ($icdV as $v){
            $lowerD = strtolower($v->diagename);

            $icdVs[$lowerD] = $v->diagcode;
        }

        $icdSs = [];
        foreach ($icdS as $s){
            $lowerD = strtolower($s->diagename);
            $icdSs[$lowerD] = $s->diagcode;
        }


        foreach ($healths as $health){

            $diags =  $health->diagnosis;
            $diags = $this->splitList($diags);

            foreach ($diags as $diag){
                $diag = trim($diag);
                $diag = strtolower($diag);

                if (array_key_exists($diag,$icdVs)){
                    $diagV = $icdVs[$diag];
                    $health->icd_v = $diagV;
                }

                if (array_key_exists($diag,$icdSs)){
                    $diagS= $icdSs[$diag];
                    $diagSCode = substr($diagS, 1,1);
                    $col_diag = "icd_s{$diagSCode}_";
                    if ( $health->{$col_diag."1"} == null){
                        $health->{$col_diag."1"} = $diagS;
                    }
                    else if ( $health->{$col_diag."2"} == null){
                        $health->{$col_diag."2"} = $diagS;
                    }
                }
            }
            $health->save();
        }
    }

    public function prepareVital(){

        set_time_limit(0);

        $healths = healthhack::get();

        foreach ($healths as $health){

            $vitals =  $health->diagnosis;

        }

    }


    public  function splitList($text){
        $text = str_replace('zzz',"",$text);
        $text = str_replace('ZZZ',"",$text);
        $text = str_replace('"',"",$text);
        $text = str_replace("[","",$text);
        $text = str_replace("]","",$text);
        $text = str_replace("',","***",$text);
        $text = str_replace("'","",$text);
        $arrList = explode("***",$text);

        foreach ($arrList as $data){
            $data = str_replace("'","",$data);
        }
        return $arrList;
    }

}
