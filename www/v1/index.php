<?php

//vendor
require_once "../../vendor/autoload.php";

//api key for google maps
$keymaps = "AIzaSyAmGAnDU2nGdKJNUakIeLv7C2Stu_evfUE";

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
                $location = $_GET['coordinates'];
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
        //first search the info of the place again
        $placeid = $_GET['placeid'];
        $url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid=' . $placeid . '&key=' . $keymaps;

        $apicall = json_decode(file_get_contents($url), true);

        //with the results foreach of the address components
        foreach ($apicall['result']['address_components'] as $value) 
        {
            //its the city
            if($value['types'][0] == 'locality')
            {
                //separate with space
                $str = explode(' ',$value['long_name']);

                //concat with %20 for the api call
                $city = implode('%20',$str);
            }
            //its the state
            else if($value['types'][0] == 'administrative_area_level_1')
            {
                //separate with space
                $str = explode(' ',$value['short_name']);

                //concat with %20 for the api call
                $state = implode('%20',$str);
            }
            //its the country
            else if($value['types'][0] == 'country')
            {
                //separate with space
                $str = explode(' ',$value['short_name']);

                //concat with %20 for the api call
                $country = implode('%20',$str);
            }
        }

        //separate with space
        $str = explode(' ',$apicall['result']['name']);

        //concat with %20 for the api call
        $name = implode('%20',$str);

        //finally the same with the address
        $str = explode(' ',$apicall['result']['formatted_address']);

        //concat with %20 for the api call
        $address = implode('%20',$str);

        //i need the current place selected, just address component to select the info
        $url = 'https://api.yelp.com/v3/businesses/matches/best';

        //get params from the array
        $data = array (
            'name' => $name,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'address1' => $address
            );
            
        $params = '';

        //add the & to add it to the URL
        foreach($data as $key=>$value)
        {
            $params .= $key.'='.$value.'&';
        }
             
        $params = trim($params, '&');

        //create the final url
        $url = $url.'?'.$params;

        $ch = curl_init($url);

        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER  => array('Authorization: Bearer GRPzYDaNMFOoZ9TTltEmyUmBfdkHzAnqXWz5yG96xO6Cq5ZDtWs5ypctSfrkTDUbl1Mv0JHdbD2P7OTUOZRl-61_EzoxbYIk3-GMMD55LIBL8OG1SpSTrDHAwI3TWnYx'),
            CURLOPT_RETURNTRANSFER  =>true,
            CURLOPT_VERBOSE     => 1
        ));

        $resultmatch = curl_exec($ch);
        curl_close($ch);

        //with the results of the best match we search the info of review
        $bestmatch = json_decode($resultmatch, true);

        if(!empty($bestmatch['businesses']))
        {   
            $url = 'https://api.yelp.com/v3/businesses/'.$bestmatch['businesses'][0]['id'].'/reviews';

            $ch = curl_init($url);

            curl_setopt_array($ch, array(
                CURLOPT_HTTPHEADER  => array('Authorization: Bearer GRPzYDaNMFOoZ9TTltEmyUmBfdkHzAnqXWz5yG96xO6Cq5ZDtWs5ypctSfrkTDUbl1Mv0JHdbD2P7OTUOZRl-61_EzoxbYIk3-GMMD55LIBL8OG1SpSTrDHAwI3TWnYx'),
                CURLOPT_RETURNTRANSFER  =>true,
                CURLOPT_VERBOSE     => 1
            ));

            $resultmatch = curl_exec($ch);
            curl_close($ch);

            echo $resultmatch;
            return;
        }
        else
        {
            //return that its an empty response
            $response = new stdClass();

            $response->meta = 5;
            $response->info = "no match found";

            echo json_encode((array)$response);
            return;
        }
    }
}

?>
