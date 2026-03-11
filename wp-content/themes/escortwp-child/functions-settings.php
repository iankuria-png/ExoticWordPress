<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }


//
// Taxonomy / post type slugs & labels
//
$taxonomy_profile_name          = get_option("taxonomy_profile_name")          ?: "escort";
$taxonomy_profile_name_plural   = get_option("taxonomy_profile_name_plural")   ?: "escorts";
$taxonomy_profile_url           = get_option("taxonomy_profile_url")          ?: "escort";

$taxonomy_agency_name           = get_option("taxonomy_agency_name")          ?: "agency";
$taxonomy_agency_name_plural    = get_option("taxonomy_agency_name_plural")   ?: "agencies";
$taxonomy_agency_url            = get_option("taxonomy_agency_url")           ?: "agency";

$taxonomy_location_url          = get_option("taxonomy_location_url")         ?: "escorts-from";

$settings_theme_genders         = get_option("settings_theme_genders");

//
// Registration form fields configuration
// [key] => [label, showinreg, mandatory, useinsearch]
// showinreg / mandatory / useinsearch: 1=yes, 2=no, 3=yes but readonly, 4=no but readonly
//
$escortregfields = array(
    'user'               => array(__('Username','escortwp'),         '3','3','4'),
    'pass'               => array(__('Password','escortwp'),         '3','3','4'),
    'youremail'          => array(__('Your Email','escortwp'),       '3','3','4'),
    'yourname'           => array(__('Name','escortwp'),             '3','3','1'),
    'phone'              => array(__('Phone','escortwp'),            '1','1','4'),
    'website'            => array(__('Website','escortwp'),          '1','2','4'),
    'instagram'          => array(__('Instagram','escortwp'),        '1','2','4'),
    'snapchat'           => array(__('SnapChat','escortwp'),         '1','2','4'),
    'twitter'            => array(__('Twitter','escortwp'),          '1','2','4'),
    'facebook'           => array(__('Facebook','escortwp'),         '1','2','4'),
    'country'            => array(__('Country','escortwp'),          '3','3','3'),
    'state'              => array(__('State','escortwp'),            '2','3','3'),
    'city'               => array(__('City','escortwp'),             '3','3','3'),
    'gender'             => array(__('Gender','escortwp'),           '3','3','1'),
    'birth'              => array(__('Date of birth','escortwp'),    '3','3','4'),
    'ethnicity'          => array(__('Ethnicity','escortwp'),        '1','1','1'),
    'haircolor'          => array(__('Hair Color','escortwp'),       '1','1','1'),
    'hairlength'         => array(__('Hair length','escortwp'),      '1','1','1'),
    'bustsize'           => array(__('Bust size','escortwp'),        '1','1','1'),
    'height'             => array(__('Height','escortwp'),           '1','1','1'),
    'weight'             => array(__('Weight','escortwp'),           '1','1','1'),
    'build'              => array(__('Build','escortwp'),            '1','1','1'),
    'looks'              => array(__('Looks','escortwp'),            '1','1','1'),
    'availability'       => array(__('Availability','escortwp'),     '1','1','1'),
    'smoker'             => array(__('Smoker','escortwp'),           '1','1','1'),
    'aboutyou'           => array(__('About you','escortwp'),        '1','1','4'),
    'education'          => array(__('Education','escortwp'),        '1','2','4'),
    'sports'             => array(__('Sports','escortwp'),           '1','2','4'),
    'hobbies'            => array(__('Hobbies','escortwp'),          '1','2','4'),
    'zodiacsign'         => array(__('Zodiac sign','escortwp'),     '1','2','4'),
    'sexualorientation'  => array(__('Sexual orientation','escortwp'),'1','2','4'),
    'occupation'         => array(__('Occupation','escortwp'),       '1','2','4'),
    'language'           => array(__('Languages spoken','escortwp'), '1','2','4'),
    'rates'              => array(__('Rates','escortwp'),            '1','1','1'),
    'services'           => array(__('Services','escortwp'),         '1','1','1'),
    'extraservices'      => array(__('Extra services','escortwp'),   '1','2','4'),
);

//
// Lookup arrays
//
$height_a = array(
    "1"=>"128","2"=>"134","3"=>"140","4"=>"146","5"=>"152",
    "6"=>"155","7"=>"158","8"=>"162","9"=>"165","10"=>"168",
    "11"=>"171","12"=>"174","13"=>"177","14"=>"180","15"=>"183",
    "16"=>"189","17"=>"195","18"=>"201","19"=>"207","20"=>"213"
);
$gender_a = array(
    "1"=>__('Female','escortwp'),
    "2"=>__('Male','escortwp'),
    "3"=>__('Couple','escortwp'),
    "4"=>__('Gay','escortwp'),
    "5"=>__('Transsexual','escortwp')
);
$ethnicity_a = array(
    "1"=>__('Latin','escortwp'),
    "2"=>__('Caucasian','escortwp'),
    "3"=>__('Black','escortwp'),
    "4"=>__('White','escortwp'),
    "5"=>__('MiddleEast','escortwp'),
    "6"=>__('Asian','escortwp'),
    "7"=>__('Indian','escortwp'),
    "8"=>__('Aborigine','escortwp'),
    "9"=>__('Native American','escortwp'),
    "10"=>__('Other','escortwp')
);
$haircolor_a = array(
    "1"=>__('Black','escortwp'), "2"=>__('Blonde','escortwp'),
    "3"=>__('Brown','escortwp'), "4"=>__('Brunette','escortwp'),
    "5"=>__('Chestnut','escortwp'), "6"=>__('Auburn','escortwp'),
    "7"=>__('Dark-blonde','escortwp'), "8"=>__('Golden','escortwp'),
    "9"=>__('Red','escortwp'), "10"=>__('Grey','escortwp'),
    "11"=>__('Silver','escortwp'), "12"=>__('White','escortwp'),
    "13"=>__('Other','escortwp')
);
$hairlength_a = array(
    "1"=>__('Bald','escortwp'),
    "2"=>__('Short','escortwp'),
    "3"=>__('Shoulder','escortwp'),
    "4"=>__('Long','escortwp'),
    "5"=>__('Very Long','escortwp'),
);
$bustsize_a = array(
    "1"=>__('Very small','escortwp'),
    "2"=>__('Small(A)','escortwp'),
    "3"=>__('Medium(B)','escortwp'),
    "4"=>__('Large(C)','escortwp'),
    "5"=>__('Very Large(D)','escortwp'),
    "6"=>__('Enormous(E+)','escortwp'),
);
$build_a = array(
    "1"=>__('Skinny','escortwp'),
    "2"=>__('Slim','escortwp'),
    "3"=>__('Regular','escortwp'),
    "4"=>__('Curvy','escortwp'),
    "5"=>__('Fat','escortwp'),
);
$looks_a = array(
    "1"=>__('Nothing Special','escortwp'),
    "2"=>__('Average','escortwp'),
    "3"=>__('Sexy','escortwp'),
    "4"=>__('Ultra Sexy','escortwp'),
);
$smoker_a = array("1"=>__('Yes','escortwp'), "2"=>__('No','escortwp'));
$availability_a = array("1"=>__('Incall','escortwp'), "2"=>__('Outcall','escortwp'));
$languagelevel_a = array(
    "1"=>__('Minimal','escortwp'),
    "2"=>__('Conversational','escortwp'),
    "3"=>__('Fluent','escortwp'),
);

//
// --- Child-theme override of services list ---
//
$services_a = array(
    "1" => __('BDSM','escortwp'),
    "2" => __('Couples','escortwp'),
    "3" => __('Domination','escortwp'),
    "4" => __('Escort','escortwp'),
    "5" => __('Massage','escortwp'),
    "6" => __('Fetish','escortwp'),
    "7" => __('Mature','escortwp'),
    "8" => __('GFE','escortwp'),
);

//
// Thumbnail dimensions
//
$thumb_sizes = array(
    '1'=>array(280,415),
    '2'=>array(280,415),
    '3'=>array(280,415),
    '4'=>array(400,600),
    '5'=>array(170,206),
    '6'=>array(500,600),
);

add_image_size('listings-thumb',          250, 370, array('center','top'));
add_image_size('header-slider',           280, 415, array('center','top'));
add_image_size('profile-thumb',           280, 415, array('center','top'));
add_image_size('profile-thumb-mobile',    400, 490);
add_image_size('profile-thumb-mobile2',   170, 206);
add_image_size('profile-thumb-mobile3',   500, 600);
add_image_size('main-image-thumb',        400,1000);

//
// Extended currency list (child-theme additions included)
// [id] => [code, name, symbol_before, symbol_after]
//
$currency_a = array(
    "8"  => array("EUR", __("Euro",'escortwp'),            "", "&euro;"),
    "22" => array("USD", __("U.S. Dollar",'escortwp'),     "$", ""),
    "1"  => array("AUD", __("Australian Dollar",'escortwp'),"$", ""),
    "2"  => array("BGN", __("Bulgarian Lev",'escortwp'),    "", "&#1083;&#1074;"),
    "3"  => array("CAD", __("Canadian Dollar",'escortwp'),  "", "$"),
    "4"  => array("CHF", __("Swiss Franc",'escortwp'),      "", "fr"),
    "5"  => array("CZK", __("Czech Koruna",'escortwp'),    "", "K&#269;"),
    "6"  => array("DKK", __("Danish Krone",'escortwp'),    "", "kr"),
    "9"  => array("GBP", __("Pound Sterling",'escortwp'),  "&pound;", ""),
    "10" => array("HKD", __("Hong Kong Dollar",'escortwp'),"HK$", ""),
    "11" => array("HUF", __("Hungarian Forint",'escortwp'),"", "Ft"),
    "15" => array("MKD", __("Macedonian Denar",'escortwp'),"", "&#1076;&#1077;&#1085;"),
    "14" => array("MYR", __("Malaysian Ringgit",'escortwp'),"RM", ""),
    "16" => array("NOK", __("Norwegian Krone",'escortwp'),  "", "kr"),
    "17" => array("NZD", __("New Zealand Dollar",'escortwp'),"$",""),
    "18" => array("PLN", __("Polish Zloty",'escortwp'),    "", "z&#322;"),
    "19" => array("RON", __("Romanian New Leu",'escortwp'),"", "lei"),
    "20" => array("SEK", __("Swedish Krona",'escortwp'),   "", "kr"),
    // Additional child-theme currencies...
    "21" => array("DZA", __("Algeria (Dinar)",'escortwp'),  "", "DA"),
    "22" => array("AOA", __("Angola (Kwanza)",'escortwp'),  "", "KZ"),
    "23" => array("XOF", __("Benin Republic (CFA Franc)",'escortwp'), "", "XOF"),
	"24" => array("BWP", __("Botswana (Pula)",'escortwp'), "", "P"),
	"25" => array("BIF", __("Burundi (Burundi Franc)",'escortwp'), "", "FBu"),
	"26" => array("XOF", __("Burkina Faso ( CFA Franc)",'escortwp'), "", "CFA"),
	"27" => array("EGP", __("Egypt (Pound)",'escortwp'), "", "E£"),
	"28" => array("CDF", __("DR Congo (Francs)",'escortwp'), "", "FC"),
	"29" => array("DJF", __("Djibouti (Djibouti Franc)",'escortwp'), "", "Fdj"),
	"30" => array("DZA", __("Algeria (Dinar)",'escortwp'), "", "DA"),
	"31" => array("DZA", __("Algeria (Dinar)",'escortwp'), "", "DA"),
	"32" => array("XAF", __("Equatorial Guinea (CFA Franc BEAC)",'escortwp'), "", "FCFA"),
    "33" => array("XAF", __("Cameroon (CFA Franc BEAC)",'escortwp'), "", "FCFA"),
    "34" => array("CVE", __("Cape Verde (Cape Verde Escudo)",'escortwp'), "", "$"),
    "35" => array("XAF", __("Central African Republic (CFA Franc)",'escortwp'), "", "FCFA"),
    "36" => array("XAF", __("Chad  (CFA Franc)",'escortwp'), "", "FCFA"),
    "37" => array("KMF", __("Comoros (Comoros Franc)",'escortwp'), "", "CF"),
    "38" => array("XOF", __("Cote d’Ivoire (CFA Franc)",'escortwp'), "", "CFA"),
    "39" => array("ERN", __("Eritrea (Eritrean Nakfa)",'escortwp'), "", "Nkf"),
    "40" => array("ETB", __("Ethiopia (Birr)",'escortwp'), "", "Br"),
    "41" => array("XAF", __("Gabon (CFA Franc)",'escortwp'), "", "FCFA"),
    "42" => array("GMD", __("Gambia (Dalasi)",'escortwp'), "", "D"),
    "43" => array("GHS", __("Ghana (Cedi)",'escortwp'), "", "GH₵"),
    "44" => array("LYD", __("Libya (Dinar)",'escortwp'), "", "LD"),
    "45" => array("MGA", __("Madagascar (Malagasy ariary)",'escortwp'), "", "Ar"),
    "46" => array("MWK", __("Malawi (Kwacha)",'escortwp'), "", "K"),
    "47" => array("LRD", __("Liberia (Dollar)",'escortwp'), "", "LD$"),
    "48" => array("GWP", __("Guinea-Bissau (Guinea-Bissau Peso)",'escortwp'), "", "CFA"),
    "49" => array("GNF", __("Guinea (Franc)",'escortwp'), "", "FG"),
    "50" => array("KES", __("Kenya (Shillings )",'escortwp'), "", "KSh"),
    "51" => array("LSL", __("Lesotho (Loti)",'escortwp'), "", "L or M"),
    "52" => array("XOF", __("Mali (CFA Franc)",'escortwp'), "", "CFA"),
    "53" => array("MRO", __("Mauritania (Ouguiya)",'escortwp'), "", "MRO"),
    "54" => array("MUR", __("Mauritius (Rupees)",'escortwp'), "", "Rs"),
    "55" => array("MAD", __("Morocco (Dirham)",'escortwp'), "", "DH"),
    "56" => array("MZN", __("Mozambique (Metical)",'escortwp'), "", "MT"),
    "57" => array("NAD", __("Namibia (Dollar)",'escortwp'), "", "N$"),
    "58" => array("XOF", __("Niger (CFA Franc)",'escortwp'), "", "CFA"),
    "59" => array("NGN", __("Nigeria (Naira)",'escortwp'), "", "N"),
    "60" => array("XAF", __("Republic of the Congo (Franc)",'escortwp'), "", "FCFA"),
    "61" => array("ZWD", __("Zimbabwe (Dollar)",'escortwp'), "", "K"),
    "62" => array("SLL", __("Sierra Leone (Leone)",'escortwp'), "", "Sl"),
    "63" => array("SOS", __("Somalia (Shillings)",'escortwp'), "", "Sh"),
    "64" => array("SCR", __("Seychelles (Rupees)",'escortwp'), "", "SR"),
    "65" => array("XOF", __("Senegal (CFA Franc)",'escortwp'), "", "CFA"),
    "66" => array("STD", __("São Tomé and Principe (Dobra)",'escortwp'), "", "Db"),
    "67" => array("RWF", __("Rwanda (Franc)",'escortwp'), "", "R₣"),
    "68" => array("SSP", __("South Sudan (Pound)",'escortwp'), "", "SS£"),
    "69" => array("SDG", __("Sudan (Pound)",'escortwp'), "", "SDG"),
    "70" => array("SZL", __("Swaziland (Lilangeni)",'escortwp'), "", "SZL"),
    "71" => array("ZAR", __("South Africa (Rand)",'escortwp'), "", "R"),
    "72" => array("TZS", __("Tanzania (Shillings)",'escortwp'), "", "TSh"),
    "73" => array("XOF", __("Togo  (CFA Franc)",'escortwp'), "", "CFA"),
    "74" => array("TND", __("Tunisia (Dinar)",'escortwp'), "", "DT"),
    "75" => array("ZMW", __("Zambia ( Kwacha)",'escortwp'), "", "K"),
    "76" => array("UGX", __("Uganda (Shillings)",'escortwp'), "", "USh"),
);

