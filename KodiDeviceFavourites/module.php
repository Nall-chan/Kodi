<?php

declare(strict_types=1);

/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.98
 *
 */
require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceFavourites Klasse für den Namespace Favourites der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.98
 * @example <b>Ohne</b>
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
    protected static $Namespace = 'Favourites';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     *  @var array
     */
    protected static $Properties = [];

    /**
     * Alle Eigenschaften eines Favoriten.
     *
     * @access private
     *  @var array
     */
    protected static $FavouriteItemList = [
        'window',
        'windowparameter',
        'thumbnail',
        'path'
    ];

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
        if ($ID == false) {
            $ID = $this->RegisterScript('FavlistDesign', 'Favouriteslist Config', $this->CreateFavlistConfigScript(), -7);
            IPS_SetHidden($ID, true);
        }
        $this->RegisterPropertyInteger('Favlistconfig', $ID);
        $this->RegisterPropertyInteger('ThumbSize', 100);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy()
    {
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return parent::Destroy();
        }
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterHook('/hook/KodiFavlist' . $this->InstanceID);
        }
        parent::Destroy();
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        $this->UnregisterScript('WebHookFavlist');

        if ($this->ReadPropertyBoolean('showFavlist')) {
            $this->RegisterVariableString('Favlist', $this->Translate('Favorites'), '~HTMLBox', 1);
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook('/hook/KodiFavlist' . $this->InstanceID);
            }

            $ID = @$this->GetIDForIdent('FavlistDesign');
            if ($ID == false) {
                $ID = $this->RegisterScript('FavlistDesign', 'Favouriteslist Config', $this->CreateFavlistConfigScript(), -7);
                IPS_SetHidden($ID, true);
            }
        } else {
            $this->UnregisterVariable('Favlist');
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook('/hook/KodiFavlist' . $this->InstanceID);
            }
        }
        $ScriptID = $this->ReadPropertyInteger('Favlistconfig');
        if ($ScriptID > 0) {
            $this->RegisterReference($ScriptID);
        }

        parent::ApplyChanges();
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIFAV_LoadFavouriteMedia'. Lädt einen Media Favoriten.
     *
     * @access public
     * @param string $Path Der Path des Favoriten.
     */
    public function LoadFavouriteMedia(string $Path)
    {
        if (!is_string($Path)) {
            trigger_error('Path must be string', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data('Player');
        $KodiData->Open(['item' => ['file' => utf8_encode(rawurlencode($Path))]]);
        $ret = $this->Send($KodiData);
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_LoadFavouriteScript'. Lädt einen Media Favoriten.
     *
     * @access public
     * @param string $Script Das Script des Favoriten.
     */
    public function LoadFavouriteScript(string $Script)
    {
        if (!is_string($Script)) {
            trigger_error('Script must be string', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data('Addons');
        $KodiData->ExecuteAddon(['addonid' => rawurlencode($Script)]);
        $ret = $this->SendDirect($KodiData);
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_LoadFavouriteWindow'. Lädt einen Window Favoriten.
     *
     * @access public
     * @param string $Window Das Ziel-Fenster des Favoriten.
     * @param string $WindowParameter Die Parameter für das Ziel-Fenster des Favoriten.
     */
    public function LoadFavouriteWindow(string $Window, string $WindowParameter)
    {
        if (!is_string($Window)) {
            trigger_error('Window must be string', E_USER_NOTICE);
            return false;
        }
        if (!is_string($WindowParameter)) {
            trigger_error('WindowParameter must be string', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data('GUI');
        $KodiData->ActivateWindow(['window' => $Window, 'parameters' => [rawurlencode($WindowParameter)]]);
        $ret = $this->Send($KodiData);
        return $ret === 'OK';
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
        if (!is_string($Type)) {
            trigger_error('Type must be string', E_USER_NOTICE);
            return false;
        }

        $Type = strtolower($Type);
        if (!in_array($Type, ['all', 'media', 'window', 'script', 'unknown'])) {
            trigger_error('Type must be "all", "media", "window", "script" or "unknown".', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);

        if ($Type == 'all') {
            $KodiData->GetFavourites(['properties' => static::$FavouriteItemList]);
        } else {
            $KodiData->GetFavourites(['type' => $Type, 'properties' => static::$FavouriteItemList]);
        }

        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->favourites);
        }
        return [];
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState($State)
    {
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE) {
            $this->RefreshFavouriteslist();
        }
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
     * Filter die aktuell nicht unterstützten Favoriten aus.
     *
     * @param array $Fav Array mit allen Favoriten.
     * @return boolean True für behalten, False für verwerfen.
     */
    protected function FilterFav($Fav)
    {
        if (($Fav['type'] == 'window') || ($Fav['type'] == 'media') || ($Fav['type'] == 'script')) {
            return true;
        }
        return false;
    }

    /**
     * Verarbeitet Daten aus dem Webhook.
     *
     * @access protected
     * @global array $_GET
     */
    protected function ProcessHookdata()
    {
        if ((!isset($_GET['Type'])) || (!isset($_GET['Path'])) || (!isset($_GET['Secret']))) {
            echo $this->Translate('Bad Request');
            return;
        }
        $CalcSecret = base64_encode(sha1($this->WebHookSecret . '0' . $_GET['Path'], true));
        if ($CalcSecret != rawurldecode($_GET['Secret'])) {
            echo $this->Translate('Access denied');
            return;
        }
        $Path = rawurldecode($_GET['Path']);
        switch ($_GET['Type']) {
            case 'media':
                $this->SendDebug('media HOOK', $Path, 0);
                $KodiData = new Kodi_RPC_Data('Player');
                $KodiData->Open(['item' => ['file' => utf8_encode($Path)]]);
                $ret = $this->Send($KodiData);
                $this->SendDebug('media HOOK', $ret, 0);
                echo $ret;
                break;
            case 'window':
                $this->SendDebug('window HOOK', $Path, 0);
                $KodiData = new Kodi_RPC_Data('GUI');
                $KodiData->ActivateWindow(['window' => $_GET['Window'], 'parameters' => [$Path]]);
                $ret = $this->Send($KodiData);
                $this->SendDebug('window HOOK', $ret, 0);
                echo $ret;
                break;
            case 'script':
                $this->SendDebug('script HOOK', $Path, 0);
                $KodiData = new Kodi_RPC_Data('Addons');
                $KodiData->ExecuteAddon(['addonid' => $Path]);
                $ret = $this->SendDirect($KodiData);
                $this->SendDebug('script HOOK', $ret, 0);
                echo $ret;
                break;
            case 'unknown':
                $this->SendDebug('unknown HOOK', $_GET, 0);
                echo $this->Translate('unknown Hook');
                break;
            default:
                echo $this->Translate('Bad Request');
                break;
        }
    }

    /**
     * Erzeugt aus der Liste der Favoriten eine HTML-Tabelle für eine ~HTMLBox-Variable.
     *
     * @access private
     */
    private function RefreshFavouriteslist()
    {
        if (!$this->ReadPropertyBoolean('showFavlist')) {
            return;
        }
        $ScriptID = $this->ReadPropertyInteger('Favlistconfig');
        if ($ScriptID == 0) {
            return;
        }
        if (!IPS_ScriptExists($ScriptID)) {
            return;
        }

        $result = IPS_RunScriptWaitEx($ScriptID, ['SENDER' => 'Kodi']);
        $Config = @unserialize($result);
        if (($Config === false) || (!is_array($Config))) {
            trigger_error($this->Translate('Error on read Favlistconfig-Script'));
            return;
        }
        $AllFavs = $this->GetFavourites('all');
        $Data = array_filter($AllFavs, [$this, 'FilterFav'], ARRAY_FILTER_USE_BOTH);

        $NewSecret = base64_encode(openssl_random_pseudo_bytes(12));
        $this->WebHookSecret = $NewSecret;

        $HTMLData = $this->GetTableHeader($Config);
        $pos = 0;

        if (count($Data) > 0) {
            foreach ($Data as $line) {
                $Line = [];
                foreach ($line as $key => $value) {
                    if (is_string($key)) {
                        $Line[ucfirst($key)] = $value;
                    } else {
                        $Line[$key] = $value;
                    } //$key is not a string
                }
                if (array_key_exists('Thumbnail', $Config['Spalten'])) {
                    if ($Line['Thumbnail'] != '') {
                        $CoverRAW = $this->GetThumbnail($Line['Thumbnail'], $this->ReadPropertyInteger('ThumbSize'), 0);
                        if ($CoverRAW === false) {
                            $Line['Thumbnail'] = '';
                        } else {
                            $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                        }
                    }
                }
                if (!array_key_exists('Path', $Line)) {
                    if (array_key_exists('Windowparameter', $Line)) {
                        $Line['Path'] = $Line['Windowparameter'];
                    } else {
                        $Line['Path'] = '';
                    }
                }

                $HTMLData .= '<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '"
                        ' . $this->GetWebHookLink($Line, $NewSecret) . '>';

                foreach ($Config['Spalten'] as $feldIndex => $value) {
                    if (!array_key_exists($feldIndex, $Line)) {
                        $Line[$feldIndex] = '';
                    }
                    if ($Line[$feldIndex] === -1) {
                        $Line[$feldIndex] = '';
                    }
                    if (is_array($Line[$feldIndex])) {
                        $Line[$feldIndex] = implode(', ', $Line[$feldIndex]);
                    }
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
     * Liefert JS-Code für einen Webhook-Request.
     *
     * @param array $Data Daten des Favoriten.
     * @return string JS-Code
     */
    private function GetWebHookLink(array $Data, string $NewSecret)
    {
        $Extra = '';
        switch ($Data['Type']) {
            case 'media':
                $this->SendDebug('create media HOOK', $Data, 0);
                break;
            case 'window':
                $this->SendDebug('create window HOOK', $Data, 0);
                $Extra = '&Window=' . $Data['Window'];
                break;
            case 'script':
                $this->SendDebug('create script HOOK', $Data, 0);
                break;
            case 'unknown':
                $this->SendDebug('create unknown HOOK', $Data, 0);
                break;
            default:
                $this->SendDebug('create illegal HOOK', $Data, 0);
                return '';
        }
        $LineSecret = rawurlencode(base64_encode(sha1($NewSecret . '0' . $Data['Path'], true)));
        return 'onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiFavlist' . $this->InstanceID . '?Type=' . $Data['Type'] . '&Path=' . rawurlencode($Data['Path']) . '&Secret=' . $LineSecret . $Extra . '\' })"';
    }

    /**
     * Gibt den Inhalt des PHP-Script zurück, welche die Konfiguration und das Design der Favoriten-Tabelle enthält.
     *
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreateFavlistConfigScript()
    {
        $Script = '<?php
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
';
        return $Script;
    }
}

/** @} */
