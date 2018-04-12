<?php

//vendor
//require_once "../../vendor/autoload.php";

//api key for google maps
$keymaps = "AIzaSyCsCBbEkLxSGhKjAsW4S0Q3LnNtvuxlliA";

//get the current location of the user with ip
$current_location = file_get_contents("http://ip-api.com/json");

if(isset($_GET['ajax']))
{
    if($_GET['ajax'] == "getmaps")
    {
        //check if it have a pagetoken
        if(isset($_GET['pagination']))
        {
            //search only with the pagination
            $url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?pagetoken='.$_GET['pagination'].'&key='.$keymaps;

            $apicall = file_get_contents($url);
            
            $arraywork = json_decode($apicall,true);

            if($arraywork['status'] == 'OK')
            {
                //return the json with the results
                echo $apicall;
            }
            else if($arraywork['status'] == 'ZERO_RESULTS')
            {
                //return that its an empty response
                $response = new stdClass();

                $response->meta = 2;
                $response->info = "Empty response";

                echo json_encode((array)$response);
            }
            else 
            {
                //return that its an empty response
                $response = new stdClass();

                $response->meta = 3;
                $response->info = $arraywork['status'];

                echo json_encode((array)$response);
            }
        }
        else
        {
            @$keyword = $_GET['keyword'];

            if(isset($_GET['radius']))
            {
                $radius = $_GET['radius'] * 1609.34;
            }
            else
            {
                $radius = 16093.4;
            }

            //separate with space
            $str = explode(' ',$keyword);

            //concat with + for the api call
            $search = implode('+',$str);

            //check the place
            if(!isset($_GET['place']) || $_GET['place'] == 1)
            {
                //use current location
                $currentlocationdecode = json_decode($current_location, true);
                $location = $currentlocationdecode['lat'].",".$currentlocationdecode['lon'];
            }
            else
            {
                //use the get of geocode
                //separate with space
                $str = explode(' ',$_GET['place']);

                //concat with + for the api call
                $geolocation = implode('+',$str);

                $urlgeocode = "https://maps.googleapis.com/maps/api/geocode/json?address=".$geolocation.'&key='.$keymaps;

                $apicallgeocode = file_get_contents($urlgeocode);

                //with the apicall get the location tab
                $geocodesearch = json_decode($apicallgeocode,true);

                if($geocodesearch['status'] == 'OK')
                {
                    $location = $geocodesearch['results'][0]['geometry']['location']['lat'].",".$geocodesearch['results'][0]['geometry']['location']['lng'];                    
                }
                else
                {
                    //return that its an empty response
                    $response = new stdClass();

                    $response->meta = 4;
                    $response->info = "error obtaining from info";

                    echo json_encode((array)$response);
                    return;
                }

            }

            $url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?location='.$location.'&radius='.$radius.'&keyword='.$search.'&key='.$keymaps;

            if(isset($_GET['category']))
            {
                $url = $url."&type=".$_GET['category'];
            }

            $apicall = file_get_contents($url);
            
            $arraywork = json_decode($apicall,true);

            if($arraywork['status'] == 'OK')
            {
                //return the json with the results
                echo $apicall;
            }
            else if($arraywork['status'] == 'ZERO_RESULTS')
            {
                //return that its an empty response
                $response = new stdClass();

                $response->meta = 2;
                $response->info = "Empty response";

                echo json_encode((array)$response);
            }
            else 
            {
                //return that its an empty response
                $response = new stdClass();

                $response->meta = 3;
                $response->info = $arraywork['status'];

                echo json_encode((array)$response);
            }
        }
    }
    else if($_GET['ajax'] == "getplace")
    {
        $placeid = $_GET['placeid'];
        $url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid=' . $placeid . '&key=' . $keymaps;

        $apicall = file_get_contents($url);

        echo $apicall;
    }
    else if($_GET['ajax'] == "yelpreview")
    {
        
    }
}

?>
