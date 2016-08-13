<?

require_once(__DIR__ . "/../KodiClass.php");  // diverse Klassen
/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger
 *
 */

/**
 * KodiDeviceFavourites Klasse für den Namespace Favourites der KODI-API.
 * Erweitert KodiBase.
 *
 */
class KodiDeviceFavourites extends KodiBase
{

    /**
     * RPC-Namespace
     * 
     * @access private
     *  @var string
     * @value 'Favourites'
     */
    static $Namespace = 'Favourites';

    /**
     * Alle Properties des RPC-Namespace
     * 
     * @access private
     *  @var array 
     */
    static $Properties = array(
    );

    /**
     * Alle Eigenschaften eines Favoriten.
     * 
     * @access private
     *  @var array 
     */
    static $FavouriteItemList = array(
        "window",
        "windowparameter",
        "thumbnail",
        "path"
    );

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
    }

################## PRIVATE     

    /**
     * Dekodiert die empfangenen Events.
     *
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        return;
    }

################## PUBLIC

    /**
     * IPS-Instanz-Funktion 'KODIFAV_GetFavourites'. Liefert die Favoriten.
     *
     * @access public
     * @param string $Type Der Typ der zu suchenden Favoriten.
     *   enum["media"=Media, "window"=Fenster, "script"=Skript, "unknown"=Unbekannt]
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetFavourites(string $Type)
    {
        if (!is_string($Type))
        {
            trigger_error('Type must be string', E_USER_NOTICE);
            return false;
        }

        $Type = strtolower($Type);
        if (!in_array($Type, array("media", "window", "script", "unknown")))
        {
            trigger_error('Type must be "media", "window", "script" or "unknown".', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetFavourites(array("type" => $Type, "properties" => static::$FavouriteItemList));
        $ret = $this->SendDirect($KodiData);
        if ($ret->limits->total > 0)
            return json_decode(json_encode($ret->favourites), true);
    }

}

/** @} */
?>