<?php
/**
 * todo:
 * User: guning
 * DateTime: 2017-11-13 10:22
 */
include_once '../env.php';
include_once '../loader.php';
include_once './priceCountry.php';

use core\Params;
use core\DBreport;
use core\DBadn;
use core\Config;
use tool\excel\myPHPExcel;

try {
    $params = Params::getParams(['startDay', 'endDay']);
} catch (Exception $e) {
    var_dump($e->getMessage());
    exit;
}

echo "offer_sync process running on " . $params['startDay'] . " to " . $params['endDay'] . "\n";
$configs = Config::getConfig();
//sheet revenue
echo "revenue\n";
$revenue = [];
$revenue[] = ['advertiser_id', 'advertiser_name', '日均revenue'];
$sql = "select advertiser_id,sum(original_money) as om 
        from report_date8 
        where 
        advertiser_id not in (823,822,770,744,738,646,599,457,790,903) 
        and date BETWEEN " . $params['startDay'] . " and " . $params['endDay']
        . "and campaign_id!=0
        GROUP by advertiser_id ORDER BY advertiser_id";
$res = DBreport::getInstance()->query($sql);

$revenue0Ids = [];
foreach ($res as $row) {
    if ($row['om'] == 0) {
        $revenue0Ids[] = $row['advertiser_id'];
    }
}
$sql = "select id, name from advertiser_list";

$res2 = DBadn::getInstance()->query($sql);
$map = [];
foreach ($res2 as $row) {
    $map[$row['id']] = $row['name'];
}

foreach ($res as $row) {
    $revenue[] = [$row['advertiser_id'], $map[$row['advertiser_id']], $row['om']/7000];
}


//sheet revenue=0
echo "revenue=0\n";
$revenue0 = [];
$revenue0[] = ['advertiser_id', 'advertiser_name', '前两周revenue', '前两周offer数'];

$strIds = implode(',', $revenue0Ids);
$startDay = date('Ymd', strtotime($params['startDay']) - 7 * 86400);
$endDay = $params['endDay'];
$sql1 = "select advertiser_id, sum(original_money) as om from report_date8
        where date between $startDay and $endDay and advertiser_id in ($strIds) GROUP by advertiser_id";
$sql2 = "select advertiser_id, count(0) as num from campaign_active2
        where date between $startDay and $endDay and advertiser_id in ($strIds) GROUP by advertiser_id";
$sql = "select a.advertiser_id, a.om, b.num 
        from ($sql1) a 
        LEFT JOIN 
        ($sql2) b 
        on a.advertiser_id=b.advertiser_id";
$res3 = DBreport::getInstance()->query($sql);
foreach ($res3 as $row) {
    $revenue0[] = [$row['advertiser_id'], $map[$row['advertiser_id']], $row['om']/1000, $row['num']];
}

//sheet advertiser_id维度offer数量&变动
echo "advertiser_id维度offer数量&变动\n";
$adOfferNum = [];
$adOfferNum[] = ['advertiser_id', 'advertiser_name', '日均offer数', '活跃天数', '日均offer变动数', 'abs'];

$sql = "select advertiser_id,date,count(0) as num 
        from campaign_active2 
        where date between " . $params['startDay'] . " and " . $params['endDay']
        . "group by advertiser_id,date order by advertiser_id,date";
$res4 = DBreport::getInstance()->query($sql);

$tmp = [];
foreach ($res4 as $row) {
    $tmp[$row['advertiser_id']][] = $row['num'];
}

foreach ($tmp as $advertiserId => $v) {
    $activeDay = count($tmp[$advertiserId]);
    $changeOffer = 0;
    $sum = 0;
    $before = null;
    foreach ($v as $num) {
        if ($before !== null) {
            $changeOffer += $num - $before;
        } else {
            $changeOffer = 0;
        }
        $sum += $num;
        $before = $num;
    }
    $adOfferNum[] = [
        $advertiserId,
        $map[$advertiserId],
        $sum/$activeDay,
        $activeDay,
        $changeOffer,
        abs($changeOffer)
    ];
}

$res = getPriceCountry($params['startDay'], $params['endDay']);
$map = ["0-0.2", "0.2-0.4", "0.4-0.6", "0.6-0.8",
        "0.8-1", "1-1.2", "1.2-1.4", "1.4-1.6",
        "1.6-1.8", "1.8-2", "2-3", "3-4",
        "4-5","5-10","10-20","20+"];
$price[] = ['区间', 'offer数'];
foreach ($res[0] as $key => $value) {
    $price[] = [$map[$key-1], $value];
}
$country = $res[1];

echo "to excel";
$obj = new myPHPExcel();
$obj->saveData($revenue, 0, 'revenue');
$obj->saveData($revenue0, 1, 'revenue=0');
$obj->saveData($adOfferNum, 2, 'advertiser_id维度offer数量&变动');
$obj->saveData($price, 3, '价格区间offer数');
$obj->saveData($country, 4, '国家维度offer数据');
$obj->toExcel($configs['save_path'] . date('Ymd'));
echo "done\n";



