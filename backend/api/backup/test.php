<?php

public function test1111(){
     
      //ส่วนลด
     $discount_erp = 30;
        $total_fee    = 9600;
        
        $discount_sum = $total_fee - $discount_erp; // 9570
        $discFT       = round($discount_sum, 2);    // 9570.00
        
        // ปัด 7/107 เป็น 5 ตำแหน่งก่อน แล้วคูณ 100
        $vatRatio5   = round(7 / 107, 5);                           // 0.06542
        $MH_discT2   = number_format($vatRatio5 * 100, 5, '.', ''); // 6.54200
        
        
        $MH_discF2   = number_format($discFT * $vatRatio5, 2, '.', ''); // 626.07
        
        $discT1CF = $discount_erp * 100 / $total_fee;
        
        $params = [
            'test' => [
                "MH_discT1" => $discount_erp,      // 30
                "MH_discF1" => $discT1CF,          // 0.3125
                "MH_discT2" => round($MH_discT2,5),         // 6.54200
                "MH_discF2" => round($MH_discF2,5),         // 626.07
            ],
        ];
  
  
  var_dump($params);die;
 }