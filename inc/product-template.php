<?php
if ( !is_user_logged_in() ) {
  die("<h1>Please Login First to access this page!</h1>");
} 

$args = array(
    'limit' => -1,
    'status'  => 'publish',
    'tax_query' => array( array(
        'taxonomy' => 'product_cat',
        'field' => 'id',
        'terms' => array( 314 ), // HERE the product category to exclude
        'operator' => 'NOT IN',
    ) ),
);
$products = wc_get_products($args);

function my_fix_content( $content ) {
  $content = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $content);
  $content = preg_replace('~(?:\[/?).*?"]~s', '', $content);
  $content = preg_replace('(\\[([^[]|)*])', '', $content );
  $content = preg_replace('/\[(.*?)\]/', '', $content );
  return $content;
}

function periodAfterLimit($short_Desc){
  $shortest_Desc = '';
  foreach(explode(".", $short_Desc) as $lines){
    if(strlen($shortest_Desc) < 200){
      $shortest_Desc = $shortest_Desc . ' ' . $lines . ".";
    }else{
      break;
    }
  }
  return $shortest_Desc;
}



$counter = 1;
$array = [];
$heading = [];
if(isset($_GET['csv']) && $_GET['csv'] === "full_columns" ){
  $heading = array('ean', 'locale', 'category', 'title', 'short_description', 'description', 'picture', 'manufacturer', 'content_volume', 'cosmetics_ingredients', 'weight');
}elseif(isset($_GET['csv']) && $_GET['csv'] === "small_columns" ){
  $heading = array('ean', 'condition', 'price', 'price_cs', 'currency', 'handling_time', 'count');
}


if(isset($_GET['csv']) && $_GET['csv'] === "full_columns" ){
  foreach($products as $product){
    if(!empty(woocommerce_get_product_terms($product->id, 'pa_kaufland-category', 'names'))){
      // ean
      $ean = '';
      // locale
      $locale = 'de-DE';
      // category
      // echo "<pre>";
      // print_r(woocommerce_get_product_terms($product->id, 'pa_kaufland-category', 'names'));
      // exit;
      $category = '';
      $terms = woocommerce_get_product_terms($product->id, 'pa_kaufland-category', 'names'); // array
      if(!empty($terms)){
          foreach($terms as $term){
              $category =  $term. "," .$category;
          }
      }
      $category = substr_replace($category ,"",-1);
      // title
      $title = $product->name;
      // short_description
      $short_Desc = $product->short_description;
      $short_Desc = $short_Desc === "" ? periodAfterLimit(strip_tags(my_fix_content($product->description))) : strip_tags($short_Desc);
      $short_Desc = str_replace(array("\r","\n"),"",$short_Desc);
      $short_Desc = strval(trim($short_Desc));

      // description
      $desc = strip_tags(my_fix_content($product->description));
      $desc = str_replace(array("\r","\n"),"",$desc);
      $desc = strval(trim($desc));


      // picture
      $image_id = $product->image_id;
      $imgurldesktop = wp_get_attachment_image_url( $image_id, '' );
      // manufacturer
      $manfacturer = "Atlantis Wellness Duft Natur";
      // content_volume
      $content_volume = "";
      // cosmetics_ingredients
      $cosmetic_ingredients = get_field('ingredients', $product->id);
      // weight
      $weight = "";

      // EAn for each variation
      if ( $product->is_type( 'variable' ) ) {
        $variations = $product->get_available_variations();
        foreach($variations as $variation){
          $array[$category."_".$counter][] = get_post_meta( $variation['variation_id'], '_ts_gtin', true );
          $array[$category."_".$counter][] = $locale;
          $array[$category."_".$counter][] = $category;
          $array[$category."_".$counter][] = $title;
          $array[$category."_".$counter][] = $short_Desc;
          $array[$category."_".$counter][] = $desc;
          $array[$category."_".$counter][] = $imgurldesktop;
          $array[$category."_".$counter][] = $manfacturer;
          $unit = get_post_meta( $product->id, '_unit', true );
          if($unit){
            switch ($unit) {
              case 'kg':
                $int = get_post_meta( $variation['variation_id'], '_weight', true ) * 1000;
                $value = $int . 'g';
                $array[$category."_".$counter][] = $value;
              break;
              case 'g':
                $int = get_post_meta( $variation['variation_id'], '_weight', true );
                $value = $int . 'g';
                $array[$category."_".$counter][] = $value;
              break;
              case 'ml':
                $int = get_post_meta( $variation['variation_id'], '_weight', true );
                $value = $int . 'ml';
                $array[$category."_".$counter][] = $value;
              break;
              case 'l':
                $int = (int) get_post_meta( $variation['variation_id'], '_weight', true ) * 1000;
                $value = $int . 'ml';
                $array[$category."_".$counter][] = $value;
              break;
            }
            // $array[$category."_".$counter][] = get_post_meta( $variation['variation_id'], '_weight', true );
            // $array[$category."_".$counter][] = get_post_meta( $product->id, '_unit', true );
          }else{
            $array[$category."_".$counter][] = "";
          }
          
          $array[$category."_".$counter][] = $cosmetic_ingredients;
          if($unit){
            switch ($unit) {
              case 'kg':
                $int = get_post_meta( $variation['variation_id'], '_weight', true );
                $value = $int . 'kg';
                $array[$category."_".$counter][] = $value;
              break;
              case 'g':
                $int = get_post_meta( $variation['variation_id'], '_weight', true )/1000;
                $value = $int . 'kg';
                $array[$category."_".$counter][] = $value;
              break;
              case 'ml':
                $int = get_post_meta( $variation['variation_id'], '_weight', true )/1000;
                $value = $int . 'kg';
                $array[$category."_".$counter][] = $value;
              break;
              case 'l':
                $int = get_post_meta( $variation['variation_id'], '_weight', true );
                $value = $int . 'kg';
                $array[$category."_".$counter][] = $value;
              break;
            }
          }else{
            $array[$category."_".$counter][] = '';
          }
          $counter++;
        }      
      }elseif( $product->is_type( 'simple' ) ){
        $array[$category."_".$counter][] = get_post_meta( $product->get_id(), '_ts_gtin', true );
        $array[$category."_".$counter][] = $locale;
        $array[$category."_".$counter][] = $category;
        $array[$category."_".$counter][] = $title;
        $array[$category."_".$counter][] = $short_Desc;
        $array[$category."_".$counter][] = $desc;
        $array[$category."_".$counter][] = $imgurldesktop;
        $array[$category."_".$counter][] = $manfacturer;
        switch ($title) {
          case str_contains($title, ' g '):
            $p1 = strpos($title, ' g ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'g';
            $value = $int . $unit;
            $array[$category."_".$counter][] = $value;
            break;
            case str_contains($title, '1000 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'g';
              $value = $int . $unit;
              $array[$category."_".$counter][] = $value;
              break;
            case str_contains($title, '100 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'g';
              $value = $int . $unit;
              $array[$category."_".$counter][] = $value;
              break;
            case str_contains($title, '50 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'g';
              $value = $int . $unit;
              $array[$category."_".$counter][] = $value;
              break;
            case str_contains($title, '50g'):
              $p1 = strpos($title, '0g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 5), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'g';
              $value = $int . $unit;
              $array[$category."_".$counter][] = $value;
              break;
          case str_contains($title, ' g) '):
            $p1 = strpos($title, ' g) ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'g';
            $value = $int . $unit;
            $array[$category."_".$counter][] = $value;
            break;
          case str_contains($title, ' ml '):
            $p1 = strpos($title, ' ml ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$category."_".$counter][] = $value;
            break;
          case str_contains($title, 'ml '):
            $p1 = strpos($title, 'ml ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$category."_".$counter][] = $value;
            break;
          case str_contains($title, '10 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$category."_".$counter][] = $value;
            break;
          case str_contains($title, '30 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$category."_".$counter][] = $value;
            break;
          case str_contains($title, '200 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$category."_".$counter][] = $value;
            break;
            default:
            $array[$category."_".$counter][] = '';
        }

        $array[$category."_".$counter][] = $cosmetic_ingredients;
        // Weight
        switch ($title) {
          case str_contains($title, ' g '):
            $p1 = strpos($title, ' g ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$category."_".$counter][] = $value . $unit;
            break;
            case str_contains($title, '1000 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'kg';
              $value = $int/1000;
              $value = $value + 0.100;
              $array[$category."_".$counter][] = $value . $unit;
              break;
            case str_contains($title, '100 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'kg';
              $value = $int/1000;
              $value = $value + 0.100;
              $array[$category."_".$counter][] = $value . $unit;
              break;
            case str_contains($title, '50 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'kg';
              $value = $int/1000;
              $value = $value + 0.100;
              $array[$category."_".$counter][] = $value . $unit;
              break;
            case str_contains($title, '50g'):
              $p1 = strpos($title, '0g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 5), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'kg';
              $value = $int/1000;
              $value = $value + 0.100;
              $array[$category."_".$counter][] = $value . $unit;
              break;
          case str_contains($title, ' g) '):
            $p1 = strpos($title, ' g) ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$category."_".$counter][] = $value . $unit;
            break;
          case str_contains($title, ' ml '):
            $p1 = strpos($title, ' ml ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$category."_".$counter][] = $value . $unit;
            break;
          case str_contains($title, 'ml '):
            $p1 = strpos($title, 'ml ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$category."_".$counter][] = $value . $unit;
            break;
          case str_contains($title, '10 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$category."_".$counter][] = $value . $unit;
            break;
          case str_contains($title, '30 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$category."_".$counter][] = $value . $unit;
            break;
          case str_contains($title, '200 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$category."_".$counter][] = $value . $unit;
            break;
            default:
            $array[$category."_".$counter][] = '';
        }
      }
    }
    $counter++;
  }
}


   
if(isset($_GET['csv']) && $_GET['csv'] === "small_columns"){
  foreach($products as $product){
    if(!empty(woocommerce_get_product_terms($product->id, 'pa_kaufland-category', 'names'))){
      $category = '';
      $terms = woocommerce_get_product_terms($product->id, 'pa_kaufland-category', 'names'); // array
      if(!empty($terms)){
          foreach($terms as $term){
              $category =  $term. "," .$category;
          }
      }
      $category = substr_replace($category ,"",-1);
      $ean = '';
      $condition = 100;
      $price = str_replace(".", '', $product->price);
      $price_cs = $product->price;
      $currency = 'EUR';
      $handling_time = 2;
      $count = 50;
      // EAn for each variation
      if ( $product->is_type( 'variable' ) ) {
        $variations = $product->get_available_variations();
        foreach($variations as $variation){
          $array[$category."_".$counter][] = get_post_meta( $variation['variation_id'], '_ts_gtin', true );
          $array[$category."_".$counter][] = $condition;
          $array[$category."_".$counter][] = $price;
          $array[$category."_".$counter][] = $price_cs;
          $array[$category."_".$counter][] = $currency;
          $array[$category."_".$counter][] = $handling_time;
          $array[$category."_".$counter][] = $count;
          $counter++;
        }      
      }elseif( $product->is_type( 'simple' ) ){
        $array[$category."_".$counter][] = get_post_meta( $product->get_id(), '_ts_gtin', true );
        $array[$category."_".$counter][] = $condition;
        $array[$category."_".$counter][] = $price;
        $array[$category."_".$counter][] = $price_cs;
        $array[$category."_".$counter][] = $currency;
        $array[$category."_".$counter][] = $handling_time;
        $array[$category."_".$counter][] = $count;
      }
    }
    
    $counter++;
  }
}


ksort($array);
function array2csv($array, $heading)
{
   if (count($array) == 0) {
     return null;
   }
   ob_end_clean();
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, $heading, ',');
   foreach ($array as $row) {
      fputcsv($df, $row, ',');
   }
   fclose($df);
   return ob_get_clean();
}

function download_send_headers($filename) {
  // disable caching
  $now = gmdate("D, d M Y H:i:s");
  header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
  header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
  header("Last-Modified: {$now} GMT");

  // force download  
  header('Content-Type: application/csv');

  // disposition / encoding on response body
  header("Content-Disposition: attachment;filename={$filename}");
  header("Content-Transfer-Encoding: binary");
}

if(isset($_GET['csv']) && ($_GET['csv'] === "full_columns" || $_GET['csv'] === "small_columns")){
  if($_GET['csv'] === "full_columns"){
    download_send_headers("data_export_full_" . date("Y-m-d") . ".csv");
  }
  if($_GET['csv'] === "small_columns"){
    download_send_headers("data_export_small_" . date("Y-m-d") . ".csv");
  }
  echo array2csv($array, $heading);
  die();
}

// echo "<pre>";
// print_r($array);

?>


<div style="text-align: center; padding: 40px 0;">
  <a style="padding: 10px 20px; margin: 10px; background-color: #434b89; color: white; text-decoration: none;" href="/wp-admin/admin.php?page=csv-exporter&csv=full_columns">Marketing File</a>
  <a style="padding: 10px 20px; margin: 10px; background-color: #434b89; color: white; text-decoration: none;" href="/wp-admin/admin.php?page=csv-exporter&csv=small_columns">Inventory File</a>
</div>

<?php