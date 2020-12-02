<?php

    $city = $country = $lat = $lon = "";

    // defult or user chosen city
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        $city = $_POST['search-city'];
        
        // vars for the api connection
        $basicURL = 'api.openweathermap.org/data/2.5/weather?';
        $key = '5220257ee6017dde1ec25a24f16dd993';
        $extendedURL = $basicURL . 'q=' . $city .'&appid=' . $key;
        
        // connecting to the api using CURL
        $ch = curl_init($extendedURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);        
        $js = json_decode($response,true);

        if($js['cod'] == 404)
        {
            $country = "Unknown Location";
        }
        else
        {
            $country = ($js['sys']['country']);
        }
    }
    else
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) 
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } 
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) 
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } 
        else 
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$ip"));
        $country = $geo["geoplugin_countryName"];
        $lat = $geo["geoplugin_latitude"];
        $lon = $geo["geoplugin_longitude"];
        $city = $geo["geoplugin_city"];

        //defult
        if($city=="")
        {
            $city = 'Tel Aviv, IL';
        }
    }

    // vars for the api connection
    $basicURL = 'http://api.openweathermap.org/data/2.5/forecast?';
    $key = '5220257ee6017dde1ec25a24f16dd993';
    $units = 'metric';
    
    if( $lat=="" || $lon=="" )
    {
        $extendedURL = $basicURL . 'q=' . $city . '&units='. $units .'&appid=' . $key;
    }
    else
    {
        $extendedURL = $basicURL . 'lat=' . $lat .'&lon='. $lon . '&units='. $units .'&appid=' . $key;
    }    
    // connecting to the api using CURL
    $ch = curl_init($extendedURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    $js = json_decode($response,true);

    if( $js['cod'] == 404 )
    {
        var_dump($js['cod']);
        $city = "Unknown Location";
        $currentTemp = 0;
        $windSpeed = 0;
        $feels = 0;
        $humidity = 0;
        $pressure = 0;

        for ($i=0; $i < 6; $i++) 
        { 
            $weaterMain[$i] = "unknown";
            $day[$i] = time();
            $avrTemp[$i] = 0;
        }      
    }
    else
    {
        //using the needed data from the api
        $feels = $js['list'][0]['main']['feels_like'];
        $pressure = $js['list'][0]['main']['pressure'];
        $humidity = $js['list'][0]['main']['humidity'];
        $windSpeed = $js['list'][0]['wind']['speed'];
        $currentTemp = $js['list'][0]['main']['temp'];

        $temp[] = array();
        $weaterMain[] = array();
        $time[] = array();
        $avrTemp[] = array();
        $avrTime[] = array();

        $day[] = $js['list'][1]['dt'];
        $weaterMain[] = $js['list'][1]['weather'][0]['main'];
        $avrTime[] =  date('d', $js['list'][1]['dt']);
        $avrTemp[] = $js['list'][1]['main']['temp_max'];
        $counter = 1;

        for ($x = 1; $x < 40; $x++)
        {
            if( date('d', $js['list'][$x]['dt']) == $avrTime[$counter])
            {
                $avrTemp[$counter] =  ($avrTemp[$counter] + $js['list'][$x]['main']['temp_max'])/2;
                $weaterMain[$counter] = $js['list'][$x]['weather'][0]['main'];
                $day[$counter] = $js['list'][$x]['dt'];
            }
            else
            {
                $day[] = $js['list'][$x]['dt'];
                $weaterMain[] = $js['list'][$x]['weather'][0]['main'];
                $avrTime[] =  date('d', $js['list'][$x]['dt']);
                $avrTemp[] = $js['list'][$x]['main']['temp_max'];
                $counter++;
            }
        }
    }
?>

<!DOCTYPE htmll>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style/main.css" />
    <link rel="stylesheet" type="text/css" href="style/toast.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" />
    <link type="text/json" href="scripts/cities.json" />
    <script src="scripts/autocomplete.js"></script>
    <script src="scripts/toast.js"></script>
    <title>weather - App</title>
</head>
<body>
    <div id="toast">Some text some message..</div>
    <form method="post" id="search-form" autocomplete="off" class="form" action="<?php echo ($_SERVER["REQUEST_URI"]);?>">
        <div class="autocomplete">
            <input type="text" id="search-city" class="search-input" name="search-city">
            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            <ul id="auto-field" class="autofield">

            </ul>
        </div> 

        <span class="location text">
            <h1><?php echo $city ?></h1>
        </span>
        <div class="data">
            <img class="img" src="static/<?php echo $weaterMain[1]; ?>.svg">
            <span class="main-status"> <?php echo $weaterMain[1]; ?> </span>
            <span class="date"> <?php echo date('d M Y'); ?> </span>
            <span class="deg"> <?php echo round($currentTemp); ?>° </span>
            <table class="data-table">
                <tr>
                    <td>
                        <span class="name">WIND</span>
                        <span class="contant"><?php echo $windSpeed ?>K</span>
                        <i class="logo fas fa-wind"></i>
                    </td>

                    <td>
                        <span class="name">FEELS LIKE</span>
                        <span class="contant"><?php echo $feels ?>°</span>
                        <i class="logo fas fa-thermometer-half"></i>
                    </td>
                </tr>
                <tr>
                    <td class>
                        <span class="name">HUMIDITY</span>
                        <span class="contant"><?php echo $humidity ?>%</span>
                        <i class="logo fas fa-tint"></i>
                    </td>
                    <td>
                        <span class="name">PRESSURE</span>
                        <span class="contant"><?php echo $pressure ?>bar</span>
                        <i class="logo fas fa-water"></i>
                    </td>
                </tr>
            </table>    
        </div>
        <div class="days">
            <div class="day">
                <span class="day-title"> Today </span>
                <img class="day-img" src="static/<?php echo $weaterMain[1]; ?>.svg">
                <span class="day-deg"><?php echo round($currentTemp); ?>° </span>
            </div>
            <div class="day">
                <span class="day-title"> <?php echo date('D', ($day[2])); ?> </span>
                <img class="day-img" src="static/<?php echo $weaterMain[2]; ?>.svg">
                <span class="day-deg"><?php echo round($avrTemp[2]); ?>° </span>
            </div>
            <div class="day">
                <span class="day-title"> <?php echo date('D', ($day[3])); ?> </span>
                <img class="day-img" src="static/<?php echo $weaterMain[3]; ?>.svg">
                <span class="day-deg"> <?php echo round($avrTemp[3]); ?>° </span>
            </div>
            <div class="day">
                <span class="day-title"> <?php echo date('D', ($day[4])); ?> </span>
                <img class="day-img" src="static/<?php echo $weaterMain[4]; ?>.svg">
                <span class="day-deg"> <?php echo round($avrTemp[4]); ?>° </span>
            </div>
            <div class="day">
                <span class="day-title"> <?php echo date('D', ($day[5])); ?> </span>
                <img class="day-img" src="static/<?php echo $weaterMain[5]; ?>.svg">
                <span class="day-deg"> <?php echo round($avrTemp[5]); ?>° </span>
            </div>
        </div>
    </form>
</body>
</html>