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
 * KodiDeviceFavourites Klasse für den Namespace Favourites der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 * @todo ProcessHookData für script und unknow erweitern
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
        $this->RegisterPropertyBoolean('showFavlist', true);
        $ID = @$this->GetIDForIdent('FavlistDesign');
        if ($ID == false)
            $ID = $this->RegisterScript('FavlistDesign', 'Favouriteslist Config', $this->CreateFavlistConfigScript(), -7);
        IPS_SetHidden($ID, true);
        $this->RegisterPropertyInteger("Favlistconfig", $ID);
        $this->RegisterPropertyInteger("ThumbSize", 100);
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function Destroy()
    {
        if (IPS_GetKernelRunlevel() <> KR_READY)
            return;
        $this->UnregisterHook('/hook/KodiFavlist' . $this->InstanceID);
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function ApplyChanges()
    {

        if ($this->ReadPropertyBoolean('showFavlist'))
        {
            $this->RegisterVariableString("Favlist", "Favourites", "~HTMLBox", 1);
            $sid = $this->RegisterScript("WebHookFavlist", "WebHookFavlist", '<? //Do not delete or modify.
if ((isset($_GET["Type"])) and (isset($_GET["Path"])))
    KODIFAV_ProcessHookdata(' . $this->InstanceID . ',$_GET);
', -8);
            IPS_SetHidden($sid, true);
            if (IPS_GetKernelRunlevel() == KR_READY)
                $this->RegisterHook('/hook/KodiFavlist' . $this->InstanceID, $sid);

            $ID = @$this->GetIDForIdent('FavlistDesign');
            if ($ID == false)
                $ID = $this->RegisterScript('FavlistDesign', 'Favouriteslist Config', $this->CreateFavlistConfigScript(), -7);
            IPS_SetHidden($ID, true);
            if (IPS_GetKernelRunlevel() == KR_READY)
                $this->RefreshFavouriteslist();
        }
        else
        {
            $this->UnregisterVariable("Favlist");
            $this->UnregisterScript("WebHookFavlist");
            if (IPS_GetKernelRunlevel() == KR_READY)
                $this->UnregisterHook('/hook/KodiFavlist' . $this->InstanceID);
        }

        parent::ApplyChanges();
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

    /**
     * Erzeugt aus der Liste der Favoriten eine HTML-Tabelle für eine ~HTMLBox-Variable.
     * 
     * @access private
     */
    private function RefreshFavouriteslist()
    {
        if (!$this->ReadPropertyBoolean('showFavlist'))
            return;
        $ScriptID = $this->ReadPropertyInteger('Favlistconfig');
        if ($ScriptID == 0)
            return;
        $result = IPS_RunScriptWaitEx($ScriptID, array('SENDER' => 'Kodi'));
        $Config = @unserialize($result);
        if (($Config === false) or ( !is_array($Config)))
            throw new Exception('Error on read Favlistconfig-Script');

        $AllFavs = $this->GetFavourites('all');
        $Data = array_filter($AllFavs, array($this, "FilterFav"), ARRAY_FILTER_USE_BOTH);
        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;
        $ParentID = $this->GetParent();
        if (count($Data) > 0)
        {
            foreach ($Data as $line)
            {
                $Line = array();
                foreach ($line as $key => $value)
                {
                    if (is_string($key))
                        $Line[ucfirst($key)] = $value;
                    else
                        $Line[$key] = $value; //$key is not a string
                }
                if (array_key_exists('Thumbnail', $Config["Spalten"]))
                {
                    $CoverRAW = false;
                    if ($ParentID !== false)
                        $CoverRAW = $this->GetThumbnail($ParentID, $Line['Thumbnail'], $this->ReadPropertyString("ThumbSize"), 0);
                    if ($CoverRAW === false)
                        $Line['Thumbnail'] = "";
                    else
                        $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                }
                if (!array_key_exists('Path', $Line))
                    if (array_key_exists('Windowparameter', $Line))
                        $Line['Path'] = $Line['Windowparameter'];
                    else
                        $Line['Path'] = "";

                $HTMLData .='<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '"
                        ' . $this->GetWebHookLink($Line) . '>';

                foreach ($Config['Spalten'] as $feldIndex => $value)
                {
                    if (!array_key_exists($feldIndex, $Line))
                        $Line[$feldIndex] = '';
                    if ($Line[$feldIndex] === -1)
                        $Line[$feldIndex] = '';
                    if (is_array($Line[$feldIndex]))
                        $Line[$feldIndex] = implode(', ', $Line[$feldIndex]);
                    $HTMLData .= '<td style="' . $Config['Style']['DF' . ($pos % 2 ? 'U' : 'G') . $feldIndex] . '">' . (string) $Line[$feldIndex] . '</td>';
                }
                $HTMLData .= '</tr>' . PHP_EOL;
                $pos++;
            }
        }
        $HTMLData .= $this->GetTableFooter();
        $this->SetValueString('Favlist', $HTMLData);
    }

    /**
     * Filter die aktuell nicht unterstützten Favoriten aus.
     * 
     * @param array $Fav Array mit allen Favoriten.
     * @return boolean True für behalten, False für verwerfen.
     */
    protected function FilterFav($Fav)
    {

        if (($Fav["type"] == "window") or ( $Fav["type"] == "media"))
            return true;
        return false;
    }

    /**
     * Liefert JS-Code für einen Webhook-Request.
     * 
     * @param array $Data Daten des Favoriten.
     * @return string JS-Code
     */
    private function GetWebHookLink(array $Data)
    {
        $Extra = "";
        switch ($Data['Type'])
        {
            case "media":
                $this->SendDebug('create media HOOK', $Data, 0);
                break;
            case "window":
                $this->SendDebug('create window HOOK', $Data, 0);
                $Extra = "&Window=" . $Data['Window'];
                break;
            case "script":
                $this->SendDebug('create script HOOK', $Data, 0);
                break;
            case "unknown":
                $this->SendDebug('create nknown HOOK', $Data, 0);
                break;
            default:
                $this->SendDebug('create illegal HOOK', $Data, 0);
                return "";
        }
        return 'onclick="window.xhrGet=function xhrGet(o) {var HTTP = new XMLHttpRequest();HTTP.open(\'GET\',o.url,true);HTTP.send();};window.xhrGet({ url: \'hook/KodiFavlist' . $this->InstanceID . '?Type=' . $Data['Type'] . '&Path=' . rawurlencode($Data['Path']) . $Extra . '\' })"';
    }

    /**
     * Gibt den Inhalt des PHP-Scriptes zurück, welche die Konfiguration und das Design der Favoriten-Tabelle enthält.
     * 
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreateFavlistConfigScript()
    {
        $Script = '<?
### Konfig ab Zeile 10 !!!

if ($_IPS["SENDER"] <> "Kodi")
{
	echo "Dieses Script kann nicht direkt ausgeführt werden!";
	return;
}
##########   KONFIGURATION
#### Tabellarische Ansicht
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die Favoriten dargestellt werden.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config["Spalten"] = array(
    "Thumbnail"=>"",
    "Title" =>"Name",
    "Path"=>"Path",
    "Type" => "Typ"
);
#### Mögliche Index-Felder
/*
        
| Index       | Typ     | Beschreibung                        |
| :---------: | :-----: | :---------------------------------: |
| Path        | string  | Path des Favoriten                  |
| Thumbnail   | string  | Vorschaubild des Favoriten          |
| Title       | string  | Name des Favoriten                  |
| Type        | string  | Art des Favoriten                   |
*/
// Breite der Spalten (Reihenfolge ist egal)
$Config["Breite"] = array(
    "Thumbnail"=>"100em",
    "Title"=>"300em",
    "Path"=>"600em",
    "Type" => "100em"
);
// Style Informationen der Tabelle
$Config["Style"] = array(
    // <table>-Tag:
    "T"    => "margin:0 auto; font-size:0.8em;",
    // <thead>-Tag:
    "H"    => "",
    // <tr>-Tag im thead-Bereich:
    "HR"   => "",
    // <th>-Tag Feld Thumbnail:
    "HFThumbnail"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Title:
    "HFTitle"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Path:
    "HFPath"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Type:
    "HFType"  => "color:#ffffff; width:35px; align:left;",
    // <tbody>-Tag:
    "B"    => "",
    // <tr>-Tag:
    "BRG"  => "background-color:#000000; color:#ffffff;",
    "BRU"  => "background-color:#080808; color:#ffffff;",
    // <td>-Tag Feld Thumbnail:
    "DFGThumbnail" => "text-align:center;",
    "DFUThumbnail" => "text-align:center;",
    // <td>-Tag Feld Title:
    "DFGTitle" => "text-align:center;",
    "DFUTitle" => "text-align:center;",
    // <td>-Tag Feld Path:
    "DFGPath" => "text-align:center;",
    "DFUPath" => "text-align:center;",
    // <td>-Tag Feld Type:
    "DFGType" => "text-align:center;",
    "DFUType" => "text-align:center;",
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade.
 );
 
### Konfig ENDE !!!
echo serialize($Config);
?>';
        return $Script;
    }

################## PUBLIC

    /**
     * IPS-Instanz-Funktion 'KODIFAV_ProcessHookdata'. Verarbeitet Daten aus dem Webhook.
     * 
     * @access public
     * @param array $HookData Daten des Webhook.
     */
    public function ProcessHookdata($HookData)
    {
        if (!((isset($HookData["Type"])) and ( isset($HookData["Path"]))))
            return;
        $Path = rawurldecode($HookData["Path"]);
        switch ($HookData['Type'])
        {
            case "media":
                $this->SendDebug('media HOOK', $Path, 0);
                $KodiData = new Kodi_RPC_Data('Player');
                $KodiData->Open(array("item" => array('file' => utf8_encode($Path))));
                $ret = $this->Send($KodiData);
                $this->SendDebug('media HOOK', $ret, 0);
                // ret = OK...aber wie Fehler ausgeben ?!
                break;
            case "window":
                $this->SendDebug('window HOOK', $Path, 0);
                $KodiData = new Kodi_RPC_Data('GUI');
                $KodiData->ActivateWindow(array('window' => $HookData['Window'], 'parameters' => array($Path)));
                $ret = $this->Send($KodiData);
                $this->SendDebug('window HOOK', $ret, 0);
                // ret = OK...aber wie Fehler ausgeben ?!
                break;
            case "script":
                $this->SendDebug('script HOOK', $Path, 0);
                break;
            case "unknown":
                $this->SendDebug('unknown HOOK', $Path, 0);
                break;
            default:
                $this->SendDebug('illegal HOOK', $HookData, 0);
                break;
        }
    }

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
        if (!in_array($Type, array("all", "media", "window", "script", "unknown")))
        {
            trigger_error('Type must be "all", "media", "window", "script" or "unknown".', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);

        if ($Type == "all")
            $KodiData->GetFavourites(array("properties" => static::$FavouriteItemList));
        else
            $KodiData->GetFavourites(array("type" => $Type, "properties" => static::$FavouriteItemList));

        $ret = $this->SendDirect($KodiData);
        if ($ret->limits->total > 0)
            return json_decode(json_encode($ret->favourites), true);
    }

}

/** @} */
?>