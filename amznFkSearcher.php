<?php
// Connection with Database


include('simple_html_dom.php');
//Amazon

// Your AWS Access Key ID, as taken from the AWS Your Account page
$aws_access_key_id = "AKIAJQGGFXGOXLJKRBQA";

// Your AWS Secret Key corresponding to the above ID, as taken from the AWS Your Account page
$aws_secret_key = "T8TQ/nPINnhXe+k8F0xK2oV2t22OGd390s/54O+3";

// The region you are interested in
$endpoint = "webservices.amazon.in";

$uri = "/onca/xml";

$Keywords=$_POST["Keywords"];


$params = array(
    "Service" => "AWSECommerceService",
    "Operation" => "ItemSearch",
    "AWSAccessKeyId" => "AKIAJQGGFXGOXLJKRBQA",
    "AssociateTag" => "tedefine03-21",
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
//$html=new simple_html_dom();
//$KeywordsPaytm=str_replace(' ', '_', (string)$Keywords);
//
//$paytm_request_url='https://paytmmall.com/shop/search?q='.$KeywordsPaytm;
////echo $paytm_request_url;
//$html->load_file('https://paytmmall.com/shop/search?q='.$KeywordsPaytm);
//$extractCount=0;
////echo $html;
//foreach($html->find('._2i1r') as $link){
//    //To extract only top 10 products to avoid irrelevant results
//    if($extractCount<=10){
//    $extractCount+=1;
//    $title=$link->find('._2apC', 0);
//    $img=$link->find('img[role=presentation]', 0)->src;
//    $price= $link->find('span[class=_1kMS]', 0);
//    //extracting numerical price from Rs. *** and storing in array match
//    preg_match_all('/([\d]+)/', $price, $match);
//
//    $price= $match[0][5];
//
//    $url=$link->find('a[class=_8vVO]', 0)->href;
//    $url="https://paytmmall.com".$url;
//    array_push($productList,array('provider'=>'Paytm Mall','title'=>$title,'image_url'=>$img,'price'=>intval($price),'buy_url'=> $url));
//
//}
//}


//Sort by price
$productListUnsorted=$productList;
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

<DOCTYPE html>
<head>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="SearcherStylesheet.css">
    <link rel="stylesheet" href="jquery.mobile-1.4.5/jquery.mobile-1.4.5.css">
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="jquery.mobile-1.4.5/jquery.mobile-1.4.5.js"></script>
    <script>
        function changeOrder(){
           var toggleValue=$("#flip-1").val();
           
           if(toggleValue=="on"){
            //   $productList=$productListUnsorted;
              $('#productsDiv').load(document.URL +  ' #productsDiv');
           }
        }
        function check() {
            console.log("Checked amazon");
//            if(document.getElementById("Amazon").checked)
//            {
//                $.ajax({
//                    url: "amazonProducts.php?productList=<?php //echo $testing; ?>//",
//                    type: "GET",
//                    success: function (response) {
//
//                        // you will get response from your php page (what you echo or print)
//
//                        console.log(response);
//
//                    },
//                    error: function(jqXHR, textStatus, errorThrown) {
//                        console.log(textStatus, errorThrown);
//                    }
//
//
//                });
//            }
        }





    </script>
</head>
<body>

<div class="container">
<div
style="float:right; margin-right:-70px;">
<?php
// Initialize the session
// require_once 'index.php';
session_start();
 
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  
?>

<a class="btn btn-primary" href="login.php" role="button"
style="color:white"
data-ajax="false"
>Log In</a>
<?php }
else{
?>
 <h4
 style="color:#3b5998;"
 ><span class="glyphicon glyphicon-user">&nbsp;<?php echo $_SESSION['username']; ?></span></h4>
<a class="btn btn-primary" href="logout.php" role="button"
data-ajax="false"
style="color:white"
>Log Out</a>
<?php 
}
?>


</div>
    <div class="filterBar">
        <form>
                
                <select id="flip-1" name="flip-1" data-role="slider" onchange="changeOrder()" >
                        <option value="off">Price</option>
                        <option value="on">Relevance</option>
                    </select>
        </form>
        <form>
            <div data-role="rangeslider">
                <label for="range-1a">Price Range Rs.</label>
                <input type="range" name="range-1a" id="range-1a" min="1" max="9999" value="1" >
                <label for="range-1b">Rangeslider:</label>
                <input type="range" name="range-1b" id="range-1b" min="1" max="9999" value="9999">
            </div>
        </form>
        <form>
            <label><input type="checkbox" name="checkbox-0" id="Amazon" onclick="check()"/>Amazon</label>
            <label><input type="checkbox" name="checkbox-1" value="Flipkart"/>Flipkart</label>
            <label><input type="checkbox" name="checkbox-2" value="PaytmMall"/>Paytm Mall</label>
        </form>
    </div>
<div id="productsDiv" class="row">
    <?php

    foreach($productList as $product) {
        echo "<div class=\"col-lg-3 col-md-4 col-sm-6 col-xs-12 well well-lg productWell \" ><h4>"
            .$product[provider]."</h4>
            <h5 class='productInfo'>".$product[title]."</h5>
            <img class='productInfo' src= $product[image_url] />
            <h6 class='productInfoBot' id='price'>Rs. ".$product[price]."</h6>
            <a class='productInfoBot' id='buyBtn' href=$product[buy_url] target='_blank'>BUY</a>"."<br />
</div>";

    }
    ?>
    </div>
</div>


</body>
</html>
