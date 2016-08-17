<?

require_once(__DIR__ . "/../KodiClass.php");  // diverse Klassen

/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 */

/**
 * KodiDeviceAddons Klasse für den Namespace Addons der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 */
class KodiDeviceAddons extends KodiBase
{

    /**
     * RPC-Namespace
     * 
     * @access private
     *  @var string
     * @value 'Addons'
     */
    static $Namespace = 'Addons';

    /**
     * Alle Properties des RPC-Namespace
     * 
     * @access private
     *  @var array 
     */
    static $Properties = array(
    );

    /**
     * Alle Properties eines Item
     * 
     * @access private
     *  @var array 
     */
    static $AddOnItemList = array(
        "name",
        "version",
        "summary",
        "description",
        "path",
        "author",
        "thumbnail",
        "disclaimer",
        "fanart",
        "dependencies",
        "broken",
        "extrainfo",
        "rating",
        "enabled"
    );

################## PRIVATE     

    /**
     * Keine Funktion.
     * 
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        return;
    }

################## PUBLIC

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_ExecuteAddon'. Startet ein Addon
     * 
     * @access public
     * @param string $AddonId Das zu startenden Addon.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function ExecuteAddon(string $AddonId)
    {
        if (!is_string($AddonId))
        {
            trigger_error('AddonId must be string', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAddon(array("addonid" => $AddonId));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return ($ret == "OK");
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_ExecuteAddonWait'. Startet ein Addon und wartet auf die Ausführung.
     * 
     * @access public
     * @param string $AddonId Das zu startenden Addon.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function ExecuteAddonWait(string $AddonId)
    {
        if (!is_string($AddonId))
        {
            trigger_error('AddonId must be string', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAddon(array("addonid" => $AddonId, "wait" => true));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return ($ret == "OK");
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_ExecuteAddonEx'. Startet ein Addon mit Parametern.
     * 
     * @access public
     * @param string $AddonId Das zu startenden Addon.
     * @param string $Params Die zu übergebenden Parameter an das AddOn als JSON-String.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function ExecuteAddonEx(string $AddonId, string $Params)
    {
        if (!is_string($AddonId))
        {
            trigger_error('AddonId must be string', E_USER_NOTICE);
            return false;
        }
        if (!is_string($Params))
        {
            trigger_error('Params must be string', E_USER_NOTICE);
            return false;
        }
        $param = json_decode($Params, true);
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAddon(array("addonid" => $AddonId, "params" => $param));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return ($ret == "OK");
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_ExecuteAddonExWait'. Startet ein Addon mit Parametern und wartet auf die Ausführung.
     * 
     * @access public
     * @param string $AddonId Das zu startenden Addon.
     * @param string $Params Die zu übergebenden Parameter an das AddOn als JSON-String.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function ExecuteAddonExWait(string $AddonId, string $Params)
    {
        if (!is_string($AddonId))
        {
            trigger_error('AddonId must be string', E_USER_NOTICE);
            return false;
        }
        if (!is_string($Params))
        {
            trigger_error('Params must be string', E_USER_NOTICE);
            return false;
        }
        $param = json_decode($Params, true);
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAddon(array("addonid" => $AddonId, "params" => $param, "wait" => true));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return ($ret == "OK");
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_GetAddonDetails'. Liefert alle Details zu einem Addon.
     * 
     * @access public
     * @param string $AddonId Addon welches gelesen werden soll.
     * @return array|bool Array mit den Eigenschaften des Addon oder false bei Fehler.
     */
    public function GetAddonDetails(string $AddonId)
    {
        if (!is_string($AddonId))
        {
            trigger_error('AddonId must be string', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetAddonDetails(array("addonid" => $AddonId, "properties" => static::$AddOnItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        return json_decode(json_encode($ret->addon), true);
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_GetAddons'. Liefert Informationen zu allen Addons.
     * 
     * @access public
     * @return array|bool Array mit den Eigenschaften der Addons oder false bei Fehler.
     */
    public function GetAddons()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetAddons(array( "properties" => static::$AddOnItemList));
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;

        if ($ret->limits->total > 0)
            return json_decode(json_encode($ret->addons ), true);
        return array();
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_SetAddonEnabled'. Liefert alle Details eines Verzeichnisses.
     * 
     * @access public
     * @param string $AddonId Addon welches aktiviert/deaktiviert werden soll.
     * @param bool $Value True zum aktivieren, false zum deaktivieren des Addon.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function SetAddonEnabled(string $AddonId, bool $Value)
    {
        if (!is_string($AddonId))
        {
            trigger_error('AddonId must be string', E_USER_NOTICE);
            return false;
        }
        if (!is_bool($Value))
        {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace); 
        $KodiData->SetAddonEnabled(array("addonid" => $AddonId, "enabled" => $Value));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        return ($ret == "OK");
    }

}

/** @} */
?>