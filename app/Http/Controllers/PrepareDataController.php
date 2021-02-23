<?php

namespace App\Http\Controllers;

use App\Models\healthhack;
use App\Models\ICD10;
use App\Models\ICD10S;
use App\Models\ICD10V;
use Illuminate\Http\Request;

class PrepareDataController extends Controller
{
    public function prepareDiag(){

        set_time_limit(0);

        $healths = healthhack::orderBy("id")->get();

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

        $healths = healthhack::orderBy("id","ASC")->get();

        $icdV = ICD10V::get();
        $icdS = ICD10S::get();

        $icdV2 = ICD10::where("diagcode", 'like', 'V%')->get();
        $icdS2 = ICD10::where("diagcode", 'like', 'S%')->get();

        $icdVs = [];
        foreach ($icdV as $v){
            $lowerD = $this->replaceCombo($v->diagename);
            $icdVs[$lowerD] = $v->diagcode;
        }

        foreach ($icdV2 as $v){
            $lowerD = $this->replaceCombo($v->diagename);
            $icdVs[$lowerD] = $v->diagcode;
        }

//        foreach ($icdVs as $key => $v){
//            echo $key."<br>";
//        }


        $icdSs = [];
        foreach ($icdS as $s){
            $lowerD = $this->replaceCombo($s->diagename);
            $icdSs[$lowerD] = $s->diagcode;
        }

        foreach ($icdS2 as $s){
            $lowerD = $this->replaceCombo($s->diagename);
            $icdSs[$lowerD] = $s->diagcode;
        }



        foreach ($healths as $health){

            $diags =  $health->diagnosis;
            $diags = $this->splitList($diags);

            $health->s0 = 0;
            $health->s1 = 0;
            $health->s2 = 0;
            $health->s3 = 0;
            $health->s4 = 0;
            $health->s5 = 0;
            $health->s6 = 0;
            $health->s7 = 0;
            $health->s8 = 0;
            $health->s9 = 0;
            $health->icd_s9_1 = null;
            $health->icd_s9_2 = null;

            foreach ($diags as $diag){

                $diag = $this->replaceCombo($diag);



               if (array_key_exists($diag,$icdVs)){
                        $diagV = $icdVs[$diag];

                        if ($diagV != null){
                            $health->icd_v = $diagV;
                            $health->save();
                        }

                }else if (array_key_exists($diag,$icdSs)){
                    $diagS= $icdSs[$diag];
                    $diagSCode = substr($diagS, 1,1);
                    if ($diagSCode == 9){
                        dd($diag,$diagS);
                    }

                    if ($diagSCode >= 0 && $diagS != null){
                        $col_diag = "icd_s{$diagSCode}_";
                        $col_diagS = "s{$diagSCode}";

                        $health->{$col_diagS} = 1;

                        if ( $health->{$col_diag."1"} == null){
                            $health->{$col_diag."1"} = $diagS;
                        }
                        else if ( $health->{$col_diag."2"} == null){
                            $health->{$col_diag."2"} = $diagS;
                        }
                    }
                }
            }
            $health->save();
        }
    }

    public function replaceCombo($lowerD){
        $lowerD = strtolower($lowerD);
        $lowerD = str_replace("zzz","",$lowerD);
        $lowerD = str_replace(" ","",$lowerD);
        $lowerD = str_replace(",","",$lowerD);
        $lowerD = str_replace(":","",$lowerD);
        $lowerD = str_replace("[","",$lowerD);
        $lowerD = str_replace("]","",$lowerD);
        return $lowerD;
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
