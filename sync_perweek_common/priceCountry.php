<?php
/**
 * todo:
 * User: guning
 * DateTime: 2017-11-13 17:53
 */
use core\DBreport;
use core\DBadn;
function getPriceCountry()
{
    echo "get campaign_id \n";
    $sql = "SELECT distinct(campaign_id) from campaign_active2 where date between {$startDate} and {$endDate}";
    $res = DBreport::getInstance()->query($sql);

    $id = [];
    if ($res) {
        foreach ($res as $row) {
            $id[] = $row['campaign_id'];
        }
    } else {
        echo "query fail:{$sql}\n";
        exit;
    }

    $all = count($id);//总id数量
    $allpage = ceil($all / 100000);
    $pricefeild = array();
    for ($i = 1; $i <= 16; $i++) {
        $pricefeild[$i] = 0;
    }
    $countryData = array();
    $countryCampaignIds = array();
    for ($i = 0; $i < $allpage; $i++) {
        sleep(2);
        echo $i . "\n";
        echo "get prices\n";
        $tmpId = array_slice($id, $i, 100000);
        $str_tmpId = implode(',', $tmpId);
        $sql = "select prices,count(0) as num from (select 
			case when (price>0 and price<=0.2) then 1
			when (price>0.2 and price<=0.4) then 2
			when (price>0.4 and price<=0.6) then 3
			when (price>0.6 and price<=0.8) then 4
			when (price>0.8 and price<=1) then 5
			when (price>1 and price<=1.2) then 6
			when (price>1.2 and price<=1.4) then 7
			when (price>1.4 and price<=1.6) then 8
			when (price>1.6 and price<=1.8) then 9
			when (price>1.8 and price<=2) then 10
			when (price>2 and price<=3) then 11
			when (price>3 and price<=4) then 12
			when (price>4 and price<=5) then 13
			when (price>5 and price<=10) then 14
			when (price>10 and price<=20) then 15
			when (price>20) then 16 end as prices
			from campaign_list where id in ({$str_tmpId})) as tmptable group by prices order by prices;";

        $res = DBadn::getInstance()->query($sql);
        if ($res) {
            foreach ($res as $row) {
                $pricefeild[$row['prices']] += $row['num'];
            }
        } else {
            echo "query fail:{$sql}\n";
            exit;
        }
        sleep(2);
        echo "get country data\n";
        $sql = "select id,country from campaign_list where id in ({$str_tmpId});";
        $res = DBadn::getInstance()->query($sql);
        if ($res) {
            foreach ($res as $row) {
                $arrRow = json_decode($row['country'], true);
                if (is_array($arrRow)) {
                    foreach ($arrRow as $v) {
                        if (isset($countryData[$v])) {
                            $countryData[$v]++;
                        } else {
                            $countryData[$v] = 1;
                        }
                        $countryCampaignIds[$v][] = $row['id'];
                    }
                } else {
                    var_dump($arrRow);
                }
            }
        }
    }
    $countryRevenue = array();
    echo "calculate all country revenue\n";
    foreach ($countryCampaignIds as $country => $ids) {
        echo $country . "-" . count($ids) . "\n";
        $strIds = implode(',', $ids);
        $sql = "select sum(original_money) as om from report_date8 where date between {$startDate} and {$endDate} and campaign_id in ({$strIds})";
        $res = DBreport::getInstance()->query($sql);
        if ($res) {
            foreach ($res as $row) {
                $countryRevenue[$country] = $row['om'];
            }
        }

    }

    $savePrices = $pricefeild;
    $saveCountry = [];
    foreach ($countryData as $key => $value) {
        $saveCountry[] = [$key, $value, $countryRevenue[$key]/1000];
    }
    return [$savePrices, $saveCountry];
}