<?php
include('simple_html_dom.php');
//Amazon

// Your AWS Access Key ID, as taken from the AWS Your Account page
$aws_access_key_id = "AKIAIMZHQX2UDRYL6JYA";

// Your AWS Secret Key corresponding to the above ID, as taken from the AWS Your Account page
$aws_secret_key = "0zsoj/bt1G1SCHcXlRNFkEj1sxovqvUXxTBscg+S";

// The region you are interested in
$endpoint = "webservices.amazon.in";

$uri = "/onca/xml";

$Keywords=$_POST["Keywords"];
//$_POST["Keywords"];

$params = array(
    "Service" => "AWSECommerceService",
    "Operation" => "ItemSearch",
    "AWSAccessKeyId" => "AKIAIMZHQX2UDRYL6JYA",
    "AssociateTag" => "tedefine-20",
    "SearchIndex" => "All",
    "Keywords" => $Keywords,
    "ResponseGroup" => "Images,ItemAttributes,Offers"
);

// Set current timestamp if not set
if (!isset($params["Timestamp"])) {
    $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
}

// Sort the parameters by key
ksort($params);

$pairs = array();

foreach ($params as $key => $value) {
    array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
}

// Generate the canonical query
$canonical_query_string = join("&", $pairs);

// Generate the string to be signed
$string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;

// Generate the signature required by the Product Advertising API
$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $aws_secret_key, true));

// Generate the signed URL
$request_url = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

$response = file_get_contents($request_url);
$parsed_xml = simplexml_load_string($response);

$productList=array();

$productCount=0;
foreach($parsed_xml->Items->Item as $item) {
    //Limit product count to 5 instead of 10
    if ($productCount <= 5) {
        array_push($productList, array('provider' => 'Amazon', 'title' => $item->ItemAttributes->Title, 'image_url' => $item->LargeImage->URL, 'price' => substr($item->Offers->Offer->OfferListing->Price->Amount, 0, -2), 'buy_url' => $url = $item->DetailPageURL));
        $productCount += 1;
    }
}

//Display products
/*
foreach($parsed_xml->Items->Item as $item)
{
    // echo "BOOK : ".$book->attributes()->id."<br />";
    echo "Amazon"."<br />";
    $productTitle=$item->ItemAttributes->Title;
    $image_url=$item->MediumImage->URL;
    $price=$item->Offers->Offer->OfferListing->Price->Amount;
    $url=$item->DetailPageURL;
    echo $productTitle."<br />";
    echo "<img src=$image_url />"."<br />";
    echo $price."<br />";
    echo "<a href=$url target=\"_blank\">BUY</a>"."<br />";

    echo "<hr/>";
}
*/

//Flipkart


$urlEncode = urlencode($_POST["Keywords"]);
$nexturl = 'https://affiliate-api.flipkart.net/affiliate/1.0/search.xml?query='.$urlEncode.'&resultCount=5';


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$nexturl);


$headers = array();
$headers[] = 'Fk-Affiliate-Id: anurag1011';
$headers[] = 'Fk-Affiliate-Token: c44de9a5e6a4445d9e667ef0935f447c';


curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
curl_close ($ch);

$products = simplexml_load_string($server_output);

foreach($products->products->productInfoList as $product){
    array_push($productList,array('provider'=>'Flipkart','title'=>$product->productBaseInfoV1->title,'image_url'=>$product->productBaseInfoV1->imageUrls->entry[1]->value,'price'=>intval($product->productBaseInfoV1->flipkartSpecialPrice->amount),'buy_url'=> $product->productBaseInfoV1->productUrl));
}

//Paytm Mall

//Scraper
$html=new simple_html_dom();
$KeywordsPaytm=str_replace(' ', '_', (string)$Keywords);
//echo $KeywordsPaytm;
$html->load_file('https://paytmmall.com/shop/search?q='.$KeywordsPaytm);
foreach($html->find('._2i1r') as $link){
    $title=$link->find('._2apC', 0);
    $img=$link->find('img[role=presentation]', 0)->src;
    $price= $link->find('span[class=_1kMS]', 0);
    //extracting numerical price from Rs. *** and storing in array match
    preg_match_all('/([\d]+)/', $price, $match);
    $price= $match[0][2];
    $url=$link->find('a[class=_8vVO]', 0)->href;
    $url="https://paytmmall.com".$url;
    array_push($productList,array('provider'=>'Paytm Mall','title'=>$title,'image_url'=>$img,'price'=>intval($price),'buy_url'=> $url));
}


//Sort by price

uasort($productList, 'sort_by_order');
function sort_by_order($a, $b)
{
    return $a['price'] - $b['price'];
}

//foreach($productList as $product) {
//    echo ($product['provider']) . "<br />";
//    echo ($product['title']) . "<br />";
//    echo "<img src= $product[image_url]  />"."<br />";
//    echo ($product['price']) . "<br />";
//    echo"<a href=$product[buy_url] target=\"_blank\">BUY</a>"."<br />";
//    echo "<hr />";
//}


?>

<!--Display page-->

<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="col-lg-12 ">
<div class="row">

    <?php
    foreach($productList as $product) {
        echo "<div class=\"col-lg-3  well \" style='text-align: center; height: 400px; width: 25%' ><h4>"
            .$product['provider']."</h4>
            <h5>".$product['title']."</h5>
            <img src= $product[image_url] style='max-width: 100px' />
            <h6>Rs. ".$product['price']."</h6>
            <a href=$product[buy_url] target='_blank'>BUY</a>"."<br />
</div>";

    }
    ?>
    </div>
</div>
</div>

</body>
</html>
