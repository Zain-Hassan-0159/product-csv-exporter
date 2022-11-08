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
$array[0] = array('ean', 'locale', 'kaufland_category', 'title', 'short_description', 'description', 'picture', 'manufacturer', 'content_volume', 'cosmetics_ingredients', 'weight');

//if(isset($_GET['csv']) && $_GET['csv'] === "full_columns" ){
  foreach($products as $product){
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
      $short_Desc = $short_Desc === "" ? periodAfterLimit(strip_tags(my_fix_content($product->description))) : $short_Desc;

      // description
      $desc = strip_tags(my_fix_content($product->description));
  
  
      // picture
      $image_id = $product->image_id;
      $imgurldesktop = wp_get_attachment_image_url( $image_id, '' );
      // manufacturer
      $manfacturer = "Nutracosmetic GmbH";
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
          $array[$counter][] = get_post_meta( $variation['variation_id'], '_ts_gtin', true );
          $array[$counter][] = $locale;
          $array[$counter][] = $category;
          $array[$counter][] = $title;
          $array[$counter][] = $short_Desc;
          $array[$counter][] = $desc;
          $array[$counter][] = $imgurldesktop;
          $array[$counter][] = $manfacturer;
          $volumes = array_shift(woocommerce_get_product_terms($product->id, 'pa_gre', 'names'));
          if($volumes){
            switch ($volumes) {
              case str_contains($volumes, ' g '):
                $p1 = strpos($volumes, ' g ');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'g';
                $value = $int . $unit;
                $array[$counter][] = $value;
                break;
                case str_contains($volumes, '1000 g'):
                  $p1 = strpos($volumes, ' g');
                  $p2 = $p1 - 4;
                  $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                  $unit = 'g';
                  $value = $int . $unit;
                  $array[$counter][] = $value;
                  break;
                case str_contains($volumes, '100 g'):
                  $p1 = strpos($volumes, ' g');
                  $p2 = $p1 - 4;
                  $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                  $unit = 'g';
                  $value = $int . $unit;
                  $array[$counter][] = $value;
                  break;
                case str_contains($volumes, '50 g'):
                  $p1 = strpos($volumes, ' g');
                  $p2 = $p1 - 4;
                  $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                  $unit = 'g';
                  $value = $int . $unit;
                  $array[$counter][] = $value;
                  break;
                case str_contains($volumes, '50g'):
                  $p1 = strpos($volumes, '0g');
                  $p2 = $p1 - 4;
                  $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 5), FILTER_SANITIZE_NUMBER_INT));
                  $unit = 'g';
                  $value = $int . $unit;
                  $array[$counter][] = $value;
                  break;
              case str_contains($volumes, ' g) '):
                $p1 = strpos($volumes, ' g) ');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'g';
                $value = $int . $unit;
                $array[$counter][] = $value;
                break;
              case str_contains($volumes, ' ml '):
                $p1 = strpos($volumes, ' ml ');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'ml';
                $value = $int . $unit;
                $array[$counter][] = $value;
                break;
              case str_contains($volumes, 'ml '):
                $p1 = strpos($volumes, 'ml ');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'ml';
                $value = $int . $unit;
                $array[$counter][] = $value;
                break;
              case str_contains($volumes, '10 ml'):
                $p1 = strpos($volumes, ' ml');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'ml';
                $value = $int . $unit;
                $array[$counter][] = $value;
                break;
              case str_contains($volumes, '30 ml'):
                $p1 = strpos($volumes, ' ml');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'ml';
                $value = $int . $unit;
                $array[$counter][] = $value;
                break;
              case str_contains($volumes, '200 ml'):
                $p1 = strpos($volumes, ' ml');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'ml';
                $value = $int . $unit;
                $array[$counter][] = $value;
                break;
                default:
                $array[$counter][] = '';
            }
          }else{
            $array[$counter][] = array_shift(woocommerce_get_product_terms($product->id, 'pa_gre', 'names'));
          }
          
          $array[$counter][] = $cosmetic_ingredients;
          if($volumes){
            switch ($volumes) {
              case str_contains($volumes, ' g '):
                $p1 = strpos($volumes, ' g ');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'kg';
                $value = $int/1000;
                $value = $value + 0.100;
                $array[$counter][] = $value . $unit;
                break;
                case str_contains($volumes, '1000 g'):
                  $p1 = strpos($volumes, ' g');
                  $p2 = $p1 - 4;
                  $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                  $unit = 'kg';
                  $value = $int/1000;
                  $value = $value + 0.100;
                  $array[$counter][] = $value . $unit;
                  break;
                case str_contains($volumes, '100 g'):
                  $p1 = strpos($volumes, ' g');
                  $p2 = $p1 - 4;
                  $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                  $unit = 'kg';
                  $value = $int/1000;
                  $value = $value + 0.100;
                  $array[$counter][] = $value . $unit;
                  break;
                case str_contains($volumes, '50 g'):
                  $p1 = strpos($volumes, ' g');
                  $p2 = $p1 - 4;
                  $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                  $unit = 'kg';
                  $value = $int/1000;
                  $value = $value + 0.100;
                  $array[$counter][] = $value . $unit;
                  break;
                case str_contains($volumes, '50g'):
                  $p1 = strpos($volumes, '0g');
                  $p2 = $p1 - 4;
                  $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 5), FILTER_SANITIZE_NUMBER_INT));
                  $unit = 'kg';
                  $value = $int/1000;
                  $value = $value + 0.100;
                  $array[$counter][] = $value . $unit;
                  break;
              case str_contains($volumes, ' g) '):
                $p1 = strpos($volumes, ' g) ');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'kg';
                $value = $int/1000;
                $value = $value + 0.100;
                $array[$counter][] = $value . $unit;
                break;
              case str_contains($volumes, ' ml '):
                $p1 = strpos($volumes, ' ml ');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'kg';
                $value = $int/1000;
                $value = $value + 0.100;
                $array[$counter][] = $value . $unit;
                break;
              case str_contains($volumes, 'ml '):
                $p1 = strpos($volumes, 'ml ');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'kg';
                $value = $int/1000;
                $value = $value + 0.100;
                $array[$counter][] = $value . $unit;
                break;
              case str_contains($volumes, '10 ml'):
                $p1 = strpos($volumes, ' ml');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'kg';
                $value = $int/1000;
                $value = $value + 0.100;
                $array[$counter][] = $value . $unit;
                break;
              case str_contains($volumes, '30 ml'):
                $p1 = strpos($volumes, ' ml');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'kg';
                $value = $int/1000;
                $value = $value + 0.100;
                $array[$counter][] = $value . $unit;
                break;
              case str_contains($volumes, '200 ml'):
                $p1 = strpos($volumes, ' ml');
                $p2 = $p1 - 4;
                $int = (int) str_replace("-", "", filter_var(substr($volumes, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
                $unit = 'kg';
                $value = $int/1000;
                $value = $value + 0.100;
                $array[$counter][] = $value . $unit;
                break;
                default:
                $array[$counter][] = '';
            }
          }else{
            $array[$counter][] = $weight;
          }
          $counter++;
        }      
      }elseif( $product->is_type( 'simple' ) ){
        $array[$counter][] = get_post_meta( $product->get_id(), '_ts_gtin', true );
        $array[$counter][] = $locale;
        $array[$counter][] = $category;
        $array[$counter][] = $title;
        $array[$counter][] = $short_Desc;
        $array[$counter][] = $desc;
        $array[$counter][] = $imgurldesktop;
        $array[$counter][] = $manfacturer;
        switch ($title) {
          case str_contains($title, ' g '):
            $p1 = strpos($title, ' g ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'g';
            $value = $int . $unit;
            $array[$counter][] = $value;
            break;
            case str_contains($title, '1000 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'g';
              $value = $int . $unit;
              $array[$counter][] = $value;
              break;
            case str_contains($title, '100 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'g';
              $value = $int . $unit;
              $array[$counter][] = $value;
              break;
            case str_contains($title, '50 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'g';
              $value = $int . $unit;
              $array[$counter][] = $value;
              break;
            case str_contains($title, '50g'):
              $p1 = strpos($title, '0g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 5), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'g';
              $value = $int . $unit;
              $array[$counter][] = $value;
              break;
          case str_contains($title, ' g) '):
            $p1 = strpos($title, ' g) ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'g';
            $value = $int . $unit;
            $array[$counter][] = $value;
            break;
          case str_contains($title, ' ml '):
            $p1 = strpos($title, ' ml ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$counter][] = $value;
            break;
          case str_contains($title, 'ml '):
            $p1 = strpos($title, 'ml ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$counter][] = $value;
            break;
          case str_contains($title, '10 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$counter][] = $value;
            break;
          case str_contains($title, '30 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$counter][] = $value;
            break;
          case str_contains($title, '200 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'ml';
            $value = $int . $unit;
            $array[$counter][] = $value;
            break;
            default:
            $array[$counter][] = '';
        }
 
        $array[$counter][] = $cosmetic_ingredients;
        // Weight
        switch ($title) {
          case str_contains($title, ' g '):
            $p1 = strpos($title, ' g ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$counter][] = $value . $unit;
            break;
            case str_contains($title, '1000 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'kg';
              $value = $int/1000;
              $value = $value + 0.100;
              $array[$counter][] = $value . $unit;
              break;
            case str_contains($title, '100 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'kg';
              $value = $int/1000;
              $value = $value + 0.100;
              $array[$counter][] = $value . $unit;
              break;
            case str_contains($title, '50 g'):
              $p1 = strpos($title, ' g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'kg';
              $value = $int/1000;
              $value = $value + 0.100;
              $array[$counter][] = $value . $unit;
              break;
            case str_contains($title, '50g'):
              $p1 = strpos($title, '0g');
              $p2 = $p1 - 4;
              $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 5), FILTER_SANITIZE_NUMBER_INT));
              $unit = 'kg';
              $value = $int/1000;
              $value = $value + 0.100;
              $array[$counter][] = $value . $unit;
              break;
          case str_contains($title, ' g) '):
            $p1 = strpos($title, ' g) ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$counter][] = $value . $unit;
            break;
          case str_contains($title, ' ml '):
            $p1 = strpos($title, ' ml ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$counter][] = $value . $unit;
            break;
          case str_contains($title, 'ml '):
            $p1 = strpos($title, 'ml ');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$counter][] = $value . $unit;
            break;
          case str_contains($title, '10 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$counter][] = $value . $unit;
            break;
          case str_contains($title, '30 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$counter][] = $value . $unit;
            break;
          case str_contains($title, '200 ml'):
            $p1 = strpos($title, ' ml');
            $p2 = $p1 - 4;
            $int = (int) str_replace("-", "", filter_var(substr($title, $p2, 4), FILTER_SANITIZE_NUMBER_INT));
            $unit = 'kg';
            $value = $int/1000;
            $value = $value + 0.100;
            $array[$counter][] = $value . $unit;
            break;
            default:
            $array[$counter][] = '';
        }
      }
    $counter++;
  }
//}


   
if(isset($_GET['csv']) && $_GET['csv'] === "small_columns"){
  foreach($products as $product){

    $ean = '';
    $condition = 100;
    $price = str_replace(".", '', $product->price);
    $price_cs = $product->price;
    $currency = 'EUR';
    $handling_time = 2;
    $count = 30;
    // EAn for each variation
    if ( $product->is_type( 'variable' ) ) {
      $variations = $product->get_available_variations();
      foreach($variations as $variation){
        $array[$counter][] = get_post_meta( $variation['variation_id'], '_ts_gtin', true );
        $array[$counter][] = $condition;
        $array[$counter][] = $price;
        $array[$counter][] = $price_cs;
        $array[$counter][] = $currency;
        $array[$counter][] = $handling_time;
        $array[$counter][] = $count;
        $counter++;
      }      
    }elseif( $product->is_type( 'simple' ) ){
      $array[$counter][] = get_post_meta( $product->get_id(), '_ts_gtin', true );
      $array[$counter][] = $condition;
      $array[$counter][] = $price;
      $array[$counter][] = $price_cs;
      $array[$counter][] = $currency;
      $array[$counter][] = $handling_time;
      $array[$counter][] = $count;
    }
    
    $counter++;
  }
}



function array2csv($array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_end_clean();
   ob_start();
   $df = fopen("php://output", 'w');
   foreach ($array as $row) {
      fputcsv($df, $row);
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
  header("Content-Type: application/force-download");
  header("Content-Type: application/octet-stream");
  header("Content-Type: application/download");

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
  echo array2csv($array);
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