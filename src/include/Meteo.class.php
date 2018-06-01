<?php
/**
 * Récupère les infos de l'API Météo de Yahoo pour la stocker en BDD
 * Permet ensuite la récupération depuis la BDD limitant les appels
 * à l'API (nombre d'appels limités
 *
 * Ne prends en charge que les villes et pays présent en base de données
 * Si nécessité d'avoir l'ensemble des pays et villes du monde, voir GeoNames
 *
 * PHP Version 7.0.9
 *
 * @category Meteo
 * @package  Meteo
 * @author   Damien GAIGA <dgaiga@interact.lu>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://interact.lu
 */

require 'cMyPDO.class.php';
//Test
//var_dump(Meteo::getData("CPH"));

/**
 * Classe de récupération, enregistrement et retour météo depuis API yahoo
 *
 * @category Meteo
 * @package  Meteo
 * @author   Damien GAIGA <dgaiga@interact.lu>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://interact.lu
 */
class Meteo
{

    /**
     * Texte de l'API Yahoo en minuscule espaces en par des underscrores
     *
     * @param string $comment Commentaires issu de la BDD
     *
     * @return string
     */
    protected static function formatComments($comment)
    {
        $temp = strtolower($comment);
        $temp = strtr($temp, "(", "");
        $temp = strtr($temp, ")", "");
        $temp = strtr($temp, " ", "_");

        return $temp;
    }

    /**
     * Ajout code Yahoo en DB - Fonction support
     *
     * @param int $codeAPIYahoo Code météo Yahoo issu de l'API
     *
     * @return null
     */
    protected static function addCodeYahoo($codeAPIYahoo)
    {
        foreach ($codeAPIYahoo as $key => $value) {
            // Réalisé manuellement à cause du PDO::PARAM_INT
            $pdo = cPDO::getPDO();
            $sql = 'REPLACE INTO weather (code, comment) VALUES (:code, :comment);';
            $requete = $pdo->prepare($sql);
            $requete->bindParam(':code', $key, PDO::PARAM_INT);
            $requete->bindParam(':comment', $value, PDO::PARAM_STR);
            $requete->execute();
        }
    }

    /**
     * Vérifie si le code aéroport existe en BDD
     *
     * @param string $value Code aéroport
     *
     * @return booleen
     */
    protected static function checkIfCodeIataExists($value)
    {
        $pdo = cPDO::getPDO();
        $sql = 'SELECT * FROM `iata` WHERE `abbr`= :value';
        $result = $pdo->Read($sql, [], true);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Récupère les données depuis le site de Yahoo
     *
     * @param string $city         Nom de la ville
     * @param string $code_country Abréviation du pays
     *
     * @return array $phpObj  Tableau complet météo
     */
    protected static function getWeatherDataFromYahoo($city, $code_country)
    {

        // API Public de Yahoo
        // YQL (Yahoo Forecast)
        // select item.forecast from weather.forecast where woeid in
        // (select woeid from geo.places(1) where text="metz, fr")
        $BASE_URL = "http://query.yahooapis.com/v1/public/yql";
        $yql_query = 'select item.forecast from weather.forecast where woeid in 
        (select woeid from geo.places(1) where text=".'
        .$city.', '.$code_country.'")';
        $yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&format=json";
        // Make call with cURL
        $session = curl_init($yql_query_url);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($session);
        // Convert JSON to PHP object and return object
        $phpObj = json_decode($json);

        return $phpObj;
    }

    /**
     * Récupère les données en base de données
     *
     * @param string $airport Code IATA
     *
     * @return array $result
     */
    protected static function getWeatherDataFromLocal($airport)
    {
        $pdo = cPDO::GetPDO();

        $sql = 'SELECT iata.abbr, city.cityfr, iata.airport, timedate.checktime, 
        timedate.tempmax, timedate.tempmin, weather.comment
        FROM city, iata, country, timedate, weather
        WHERE iata.abbr = :airport
        AND timedate.checktime >= CURRENT_DATE()
        AND iata.cityid = city.id
        AND city.countryid = country.id
        AND timedate.cityid = city.id
        AND timedate.weatherid = weather.id
        ORDER BY timedate.checktime ASC
        LIMIT 0, 3';

        $result = $pdo->Read(
            $sql,
            [
            'airport' => $airport,
            ]
        );

        return $result;
    }

    /**
     * Convertir les dégrés Farhenheit de yahoo en Celsius
     *
     * @param int $farhenheit Température en Farhenheit
     *
     * @return int Température en Celsius
     */
    public static function convertFarhenheit($farhenheit)
    {
        return ceil(($farhenheit - 32) / 1.8);
    }

    /**
     * Vérifier si la base de données dispose des dernières valeurs
     *
     * @param string $airport Code IATA
     *
     * @return array $result
     */
    protected static function getAllData($airport)
    {
        // On récupère les valeurs pour la requête demandée
        $result = Meteo::getWeatherDataFromLocal($airport);
        $nbResult = count($result);

        // Si on à un tableau d'une longueur de 3 éléments, nous disposons de
        // tous les résultats
        if ($nbResult == 3) {
            return $result;
        } else {
            // Autrement, on interroge Yahoo
            // Correspondance ville/pays;
            $pdo = cPDO::GetPDO();
            // On tente de récupérer le nom de la ville et
            // le pays associé suivant le code IATA fourni
            $sql = 'SELECT city.cityfr, country.abbr
            FROM city, iata, country
            WHERE iata.abbr =  :airport
            AND iata.cityid = city.id
            AND city.countryid = country.id';
            
            $result = $pdo->Read(
                $sql,
                [
                'airport' => $airport,
                ]
            )[0];

            // Si la ville n'existe pas en BDD, on doit la rajouter
            // manuellement, en attendant, on revoie un code erreur
            if (empty($result)) {
                http_response_code(500);
                exit;
            } else {
                // Si la ville existe, on ajoute les éléments en BDD
                $city = $result['cityfr'];
                $abbr_country = $result['abbr'];

                $phpObj = Meteo::getWeatherDataFromYahoo($city, $abbr_country);

                // Mise en BDD des résultats
                Meteo::saveResultInDatabase($phpObj, $city);
                // On rappelle la fonction pour récupérer depuis la BDD
                // pour renvoyer dans un format connu
                $result = Meteo::getWeatherDataFromLocal($airport, 3);
                return $result;
            }
        }
    }

    /**
     * Vérifier si la base de données dispose des dernières valeurs
     *
     * @param string $airport Code IATA
     * @param string $offset  Jour souhaité (0:Aujourd'hui,1:Demain,2:Après-demain)
     *
     * @return array
     */
    public static function getData($airport, $offset = 0)
    {
        $temp = Meteo::getAllData($airport);
        $temp[$offset]["comment"] = Meteo::getRealImage($temp[$offset]["comment"]);
        return $temp[$offset];
    }


    /**
     * Enregistre les résultats de Yahoo en base de données
     *
     * @param array  $phpObj Tableau de résultats à stocker
     * @param string $city   Ville
     *
     * @return null
     */
    protected static function saveResultInDatabase($phpObj, $city)
    {
        for ($i=0; $i < count($phpObj->query->results->channel); $i++) {
            // Et on stocke les informations en BDD
            // On récupère les éléments de l'objet fourni via l'API.
            if (isset($phpObj->query->results)) {
                if (is_numeric($i) && ($i >= 0) && ($i < 6)) {
                    $code = $phpObj->query->results->channel[$i]
                        ->item->forecast->code;
                    $date = $phpObj->query->results->channel[$i]
                        ->item->forecast->date;
                    $day = $phpObj->query->results->channel[$i]
                        ->item->forecast->day;
                    $high = Meteo::convertFarhenheit(
                        $phpObj->query->results->channel[$i]->item->forecast->high
                    );
                    $low = Meteo::convertFarhenheit(
                        $phpObj->query->results->channel[$i]->item->forecast->low
                    );
                    // $comment (facultatif, pas utile pour le moment)
                    $comment = Meteo::formatComments(
                        $phpObj->query->results->channel[$i]->item->forecast->text
                    );

                    $date = strtotime($date);
                    $temp = date("Y-m-d H:i:s", $date);

                    $pdo = cPDO::GetPDO();
                    $sql = 'REPLACE INTO timedate (checktime, tempmax, tempmin,
                cityid,
                weatherid,
                dayid)
                VALUES (:timedate, :tempmax, :tempmin,
                    (SELECT id FROM city WHERE cityfr = :city),
                    (SELECT id FROM weather WHERE code = :code),
                    (SELECT id FROM dayname WHERE abbr = :abbrday)
                );';

                    $pdo->Write( 
                        $sql,
                        [
                        'timedate' => $temp,
                        'tempmax'=> $high,
                        'tempmin'=> $low,
                        'city'=> $city,
                        'code'=> $code,
                        'abbrday'=> $day,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Récupère le nom de l'image correspondante suivant valeur reçue
     *
     * @param string $value Nom météo suivant API Yahoo
     *
     * @return string $result nom de l'image correspondante
     */
    protected static function getRealImage($value)
    {
        // Pour info, code API Yahoo
        $correspondanceYahoo = array(
        "tornado"=>"tornado.png",
        "tropical_storm"=>"tornado.png",
        "hurricane"=>"tornado.png",
        "severe_thunderstorms"=>"thunderstorms.png",
        "thunderstorms"=>"thunderstorms.png",
        "mixed_rain_and_snow"=>"snow.png",
        "mixed_rain_and_sleet"=>"showers.png",
        "mixed_snow_and_sleet"=>"showers.png",
        "freezing_drizzle"=>"foggy.png",
        "drizzle"=>"foggy.png",
        "freezing_rain"=>"showers.png",
        "showers"=>"showers.png",
        "showers"=>"showers.png",
        "snow_flurries"=>"snow.png",
        "light_snow_showers"=>"snow.png",
        "blowing_snow"=>"snow.png",
        "snow"=>"snow.png",
        "hail"=>"hail.png",
        "sleet"=>"showers.png",
        "dust"=>"foggy.png",
        "foggy"=>"foggy.png",
        "haze"=>"foggy.png",
        "smoky"=>"foggy.png",
        "blustery"=>"windy.png",
        "windy"=>"windy.png",
        "cold"=>"windy.png",
        "cloudy"=>"cloudy.png",
        "mostly_cloudy_night"=>"cloudy.png",
        "mostly_cloudy_day"=>"cloudy.png",
        "partly_cloudy_night"=>"partly-cloudy.png",
        "partly_cloudy_day"=>"partly-cloudy.png",
        "clear_night"=>"sunny.png",
        "sunny"=>"sunny.png",
        "fair_night"=>"sunny.png",
        "fair_day"=>"sunny.png",
        "mixed_rain_and_hail"=>"showers.png",
        "hot"=>"sunny.png",
        "isolated_thunderstorms"=>"thunderstorms.png",
        "scattered_thunderstorms"=>"thunderstorms.png",
        "scattered_thunderstorm"=>"thunderstorms.png",
        "scattered_showers"=>"showers.png",
        "heavy_snow"=>"snow.png",
        "scattered_snow_showers"=>"snow.png",
        "heavy_snow"=>"snow.png",
        "partly_cloudy"=>"partly-cloudy.png",
        "thundershowers"=>"showers.png",
        "snow_showers"=>"snow.png",
        "isolated_thundershowers"=>"showers.png",
        "not_available"=>""
        );
        
        $result = $correspondanceYahoo[$value];
        return $result;
    }
}
