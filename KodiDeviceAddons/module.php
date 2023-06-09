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
 * @version       3.00
 *
 */
require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceAddons Klasse für den Namespace Addons der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 * @property array $AddOnItemList Alle Properties eines Item
 */
class KodiDeviceAddons extends KodiBase
{
    public const PropertyShowAddonlist = 'showAddonlist';
    public const PropertyThumbSize = 'ThumbSize';
    public const ActionVisibleFormElementsAddon = 'showAddonlist';
    public const Hook = '/hook/KodiAddonlist';

    protected static $Namespace = 'Addons';
    protected static $Properties = [];
    protected static $AddOnItemList = [
        'name',
        'version',
        'summary',
        'description',
        'path',
        'author',
        'thumbnail',
        'disclaimer',
        'fanart',
        'dependencies',
        'broken',
        'extrainfo',
        'rating',
        'enabled'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyBoolean(self::PropertyShowAddonlist, true);
        $this->RegisterPropertyInteger(self::PropertyThumbSize, 100);

        // Todo 7.0 -> Style per Konfig-Formular
        $ID = @$this->GetIDForIdent('AddonlistDesign');
        if ($ID == false) {
            $ID = $this->RegisterScript('AddonlistDesign', 'AddonList Config', $this->CreateAddonlistConfigScript(), -7);
            IPS_SetHidden($ID, true);
        }
        $this->RegisterPropertyInteger('Addonlistconfig', $ID);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy(): void
    {
        if (IPS_GetKernelRunlevel() != KR_READY) {
            parent::Destroy();
            return;
        }
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterHook(self::Hook . $this->InstanceID);
        }

        parent::Destroy();
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        if ($this->ReadPropertyBoolean(self::PropertyShowAddonlist)) {
            $this->RegisterVariableString('Addonlist', 'Addons', '~HTMLBox', 1);
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook(self::Hook . $this->InstanceID);
            }

            $ID = @$this->GetIDForIdent('AddonlistDesign');
            if ($ID == false) {
                $ID = $this->RegisterScript('AddonlistDesign', 'AddonList Config', $this->CreateAddonlistConfigScript(), -7);
                IPS_SetHidden($ID, true);
            }
        } else {
            $this->UnregisterVariable('Addonlist');
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook(self::Hook . $this->InstanceID);
            }
        }
        $ScriptID = $this->ReadPropertyInteger('Addonlistconfig');
        if ($ScriptID > 0) {
            $this->RegisterReference($ScriptID);
        }
        parent::ApplyChanges();
    }
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Form['elements'][1]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowAddonlist);
        $Form['elements'][2]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowAddonlist);
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }
    ################## ActionHandler
    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     *
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    public function RequestAction(string $Ident, mixed $Value, bool &$done = false): void
    {
        parent::RequestAction($Ident, $Value, $done);
        if ($done) {
            return;
        }
        switch ($Ident) {
            case self::ActionVisibleFormElementsAddon:
                $this->UpdateFormField('HTMLRow', 'visible', (bool) $Value);
                $this->UpdateFormField('ThumbRow', 'visible', (bool) $Value);
                return;
            default:
                trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion '*_RequestState'. Frage eine oder mehrere Properties eines Namespace ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident): void
    {
        $this->RefreshAddonlist();
    }
    /**
     * IPS-Instanz-Funktion 'KODIADDONS_EnableAddon'. Aktiviert / Deaktiviert ein Addon
     *
     * @access public
     * @param string $AddonId Das zu aktivirende / deaktivierende Addon.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function EnableAddon(string $AddonId, bool $Value): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetAddonEnabled(['addonid' => $AddonId, 'enabled' => $Value]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->RefreshAddonlist();
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_ExecuteAddon'. Startet ein Addon
     *
     * @access public
     * @param string $AddonId Das zu startenden Addon.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function ExecuteAddon(string $AddonId): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAddon(['addonid' => $AddonId]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_ExecuteAddonWait'. Startet ein Addon und wartet auf die Ausführung.
     *
     * @access public
     * @param string $AddonId Das zu startenden Addon.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function ExecuteAddonWait(string $AddonId): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAddon(['addonid' => $AddonId, 'wait' => true]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_ExecuteAddonEx'. Startet ein Addon mit Parametern.
     *
     * @access public
     * @param string $AddonId Das zu startenden Addon.
     * @param string $Params Die zu übergebenden Parameter an das AddOn als JSON-String.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function ExecuteAddonEx(string $AddonId, string $Params): bool
    {
        $param = json_decode($Params, true);
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAddon(['addonid' => $AddonId, 'params' => $param]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_ExecuteAddonExWait'. Startet ein Addon mit Parametern und wartet auf die Ausführung.
     *
     * @access public
     * @param string $AddonId Das zu startenden Addon.
     * @param string $Params Die zu übergebenden Parameter an das AddOn als JSON-String.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function ExecuteAddonExWait(string $AddonId, string $Params): bool
    {
        $param = json_decode($Params, true);
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAddon(['addonid' => $AddonId, 'params' => $param, 'wait' => true]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_GetAddonDetails'. Liefert alle Details zu einem Addon.
     *
     * @access public
     * @param string $AddonId Addon welches gelesen werden soll.
     * @return array|bool Array mit den Eigenschaften des Addon oder false bei Fehler.
     */
    public function GetAddonDetails(string $AddonId): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetAddonDetails(['addonid' => $AddonId, 'properties' => static::$AddOnItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->addon);
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_GetAddons'. Liefert Informationen zu allen Addons.
     *
     * @access public
     * @return array|bool Array mit den Eigenschaften der Addons oder false bei Fehler.
     */
    public function GetAddons(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetAddons(['properties' => static::$AddOnItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }

        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->addons);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIADDONS_SetAddonEnabled'. Liefert alle Details eines Verzeichnisses.
     *
     * @access public
     * @param string $AddonId Addon welches aktiviert/deaktiviert werden soll.
     * @param bool $Value True zum aktivieren, false zum deaktivieren des Addon.
     * @return bool true bei Erfolg oder false bei Fehler.
     */
    public function SetAddonEnabled(string $AddonId, bool $Value): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetAddonEnabled(['addonid' => $AddonId, 'enabled' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    ################## PRIVATE

    /**
     * Keine Funktion.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
    }

    /**
     * Filter die Liste der Addons.
     *
     * @param array $Addon Array aller Addons
     * @return boolean True für behalten, False für verwerfen.
     */
    protected function FilterAddons(array $Addon): bool
    {
        if (($Addon['type'] == 'xbmc.python.pluginsource') || ($Addon['type'] == 'xbmc.python.script')) {
            return true;
        }
        return false;
    }

    ################## PUBLIC
    /**
     * Verarbeitet Daten aus dem Webhook.
     *
     * @access protected
     * @global array $_GET
     */
    protected function ProcessHookdata(): void
    {
        if ((!isset($_GET['Addonid'])) || (!isset($_GET['Action'])) || (!isset($_GET['Secret']))) {
            echo $this->Translate('Bad Request');
            return;
        }
        $CalcSecret = base64_encode(sha1($this->WebHookSecret . '0' . $_GET['Addonid'], true));
        if ($CalcSecret != rawurldecode($_GET['Secret'])) {
            echo $this->Translate('Access denied');
            return;
        }

        switch ($_GET['Action']) {
            case 'Execute':
                if ($this->ExecuteAddon($_GET['Addonid']) === true) {
                    echo 'OK';
                }
                break;
            case 'Enable':
                if ($this->EnableAddon($_GET['Addonid'], true) === true) {
                    echo 'OK';
                }
                break;
            case 'Disable':
                if ($this->EnableAddon($_GET['Addonid'], false) === true) {
                    echo 'OK';
                }
                break;
            default:
                echo $this->Translate('Bad Request');
                break;
        }
    }

    /**
     * Erzeugt aus der Liste der Addons eine HTML-Tabelle für eine ~HTMLBox-Variable.
     *
     * @access private
     */
    private function RefreshAddonlist(): void
    {
        if (!$this->ReadPropertyBoolean(self::PropertyShowAddonlist)) {
            return;
        }
        $ScriptID = $this->ReadPropertyInteger('Addonlistconfig');
        if ($ScriptID == 0) {
            return;
        }
        if (!IPS_ScriptExists($ScriptID)) {
            return;
        }

        $result = IPS_RunScriptWaitEx($ScriptID, ['SENDER' => 'Kodi']);
        $Config = @unserialize($result);
        if (($Config === false) || (!is_array($Config))) {
            trigger_error($this->Translate('Error on read Addonlistconfig-Script'));
            return;
        }
        $AllAddons = $this->GetAddons();
        if ($AllAddons === false) {
            $AllAddons = [];
        }
        $Data = array_filter($AllAddons, [$this, 'FilterAddons'], ARRAY_FILTER_USE_BOTH);

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
                        $CoverRAW = $this->GetThumbnail($Line['Thumbnail'], $this->ReadPropertyInteger(self::PropertyThumbSize), 0);
                        if ($CoverRAW === false) {
                            $Line['Thumbnail'] = '';
                        } else {
                            $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                        }
                    }
                }
                if ((array_key_exists('Fanart', $Config['Spalten'])) && ($Line['Fanart'] != '')) {
                    $CoverRAW = $this->GetThumbnail($Line['Fanart'], $this->ReadPropertyInteger(self::PropertyThumbSize), 0);
                    if ($CoverRAW === false) {
                        $Line['Fanart'] = '';
                    } else {
                        $Line['Fanart'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                    }
                }

                $Line['Enabled'] = $Line['Enabled'] == true ? '<div class="dodisabled" ' . $this->GetWebHookLink($Line['Addonid'], 'Disable', $NewSecret) . '>Aus</div><div class="isenabled" ' . $this->GetWebHookLink($Line['Addonid'], 'Enable', $NewSecret) . '>An</div>' : '<div class="isdisabled" ' . $this->GetWebHookLink($Line['Addonid'], 'Disable', $NewSecret) . '>Aus</div><div class="doenabled" ' . $this->GetWebHookLink($Line['Addonid'], 'Enable', $NewSecret) . '>An</div>';
                $Line['Execute'] = '<div class="execute" ' . $this->GetWebHookLink($Line['Addonid'], 'Execute', $NewSecret) . '>Ausführen</div>';

                $HTMLData .= '<tr style="' . $Config['Style']['BR' . ($pos % 2 ? 'U' : 'G')] . '">';
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
        $this->SetValueString('Addonlist', $HTMLData);
    }

    /**
     *  Erzeugt JS für einen Webhook-Request
     *
     * @param string $Addonid ID des Addon.
     * @param string $Action Die Aktion welche der Webhook auslösen soll.
     * @return string JS-Code
     */
    private function GetWebHookLink(string $Addonid, string $Action, string $NewSecret): string
    {
        //return 'onclick="window.xhrGet' . $this->InstanceID . '({ url: \'hook/KodiAddonlist' . $this->InstanceID . '?Addonid=' . $Addonid . '&Action=' . $Action . '\' })"';
        $LineSecret = rawurlencode(base64_encode(sha1($NewSecret . '0' . $Addonid, true)));
        return 'onclick="xhrGet' . $this->InstanceID . '({ url: \'hook/KodiAddonlist' . $this->InstanceID . '?Addonid=' . $Addonid . '&Action=' . $Action . '&Secret=' . $LineSecret . '\' })"';
    }

    /**
     * Gibt den Inhalt des PHP-Script zurück, welche die Konfiguration und das Design der Addon-Tabelle enthält.
     *
     * @access private
     * @return string Ein PHP-Script welche als Grundlage für die User dient.
     */
    private function CreateAddonlistConfigScript(): string
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
# Folgende Parameter bestimmen das Aussehen der HTML-Tabelle in der die Addons dargestellt werden.

// Reihenfolge und Überschriften der Tabelle. Der vordere Wert darf nicht verändert werden.
// Die Reihenfolge, der hintere Wert (Anzeigetext) und die Reihenfolge sind beliebig änderbar.
$Config["Spalten"] = array(
    "Thumbnail"=>"",
    "Enabled" =>"Aktiv",
    "Name"=>"Name",
    "Version" => "Version",
    "Summary"=>"Beschreibung",
//    "Description"=>"Beschreibung",
//    "Path"=>"Path",
//    "Author" => "Author",
//    "Disclaimer" => "Disclaimer", //meist leer
//    "Fanart" => "Fanart",         //meist leer
//    "Broken" => "Defekt",
//    "Rating"=>"Rating",
//    "Type" => "Typ",
//    "Addonid" => "Id",
    "Execute" =>"Starten"
);
#### Mögliche Index-Felder
/*
        
| Index       | Typ     | Beschreibung                        |
| :---------: | :-----: | :---------------------------------: |
| Addonid     | string  | Id des Addon                        |
| Author      | string  | Autor des Addon                     |
| Broken      | boolean | Zustand ob Addon defekt ist         |
| Description | string  | Beschreibung                        |
| Disclaimer  | string  | Haftungsausschluss                  |
| Enabled     | boolean | Addon aktiv (steuerbar)             |
| Execute     | boolean | Addon starten (steuerbar)           |
| Fanart      | string  | Fanart des Addon                    | 
| Name        | string  | Name des Addon                      |
| Path        | string  | Path zum Addon auf dem System       |
| Rating      | integer | Bewertung                           |
| Summary     | string  | Zusammenfassung                     |
| Thumbnail   | string  | Vorschaubild des Addon              |
| Type        | string  | Art des Addon                       |
| Version     | string  | Version des Addon                   |
*/
// Breite der Spalten (Reihenfolge ist egal)
$Config["Breite"] = array(
    "Thumbnail"=>"100em",
    "Enabled" =>"120em",
    "Name"=>"200em",
    "Version" => "100em",
    "Summary"=>"400em",
//    "Description"=>"400em",
//    "Path"=>"100em",
//    "Author" => "100em",
//    "Disclaimer" => "100em", //meist leer
//    "Fanart" => "100em",         //meist leer
//    "Broken" => "50em",
//    "Rating"=>"50em",
//    "Type" => "100em",
//    "Addonid" => "200em",
	"Execute" => "100em"      

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
    // <th>-Tag Feld Enabled:
    "HFEnabled"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Name:
    "HFName"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Version:
    "HFVersion"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Summary:
    "HFSummary"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Description:
    "HFDescription"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Path:
    "HFPath"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Author:
    "HFAuthor"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Disclaimer:
    "HFDisclaimer"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Fanart:
    "HFFanart"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Broken:
    "HFBroken"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Rating:
    "HFRating"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Type:
    "HFType"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Addonid:
    "HFAddonid"  => "color:#ffffff; width:35px; align:left;",
    // <th>-Tag Feld Execute:
    "HFExecute"  => "color:#ffffff; width:35px; align:left;",
    // <tbody>-Tag:
    "B"    => "",
    // <tr>-Tag:
    "BRG"  => "background-color:#000000; color:#ffffff;",
    "BRU"  => "background-color:#080808; color:#ffffff;",
    // <td>-Tag Feld Thumbnail:
    "DFGThumbnail" => "text-align:center;",
    "DFUThumbnail" => "text-align:center;",
    // <td>-Tag Feld Enabled:
    "DFGEnabled" => "text-align:center;",
    "DFUEnabled" => "text-align:center;",
    // <td>-Tag Feld Name:
    "DFGName" => "text-align:center;",
    "DFUName" => "text-align:center;",
    // <td>-Tag Feld Version:
    "DFGVersion" => "text-align:center;",
    "DFUVersion" => "text-align:center;",
    // <td>-Tag Feld Summary:
    "DFGSummary" => "text-align:center;",
    "DFUSummary" => "text-align:center;",
    // <td>-Tag Feld Description:
    "DFGDescription" => "text-align:center;",
    "DFUDescription" => "text-align:center;",
    // <td>-Tag Feld Path:
    "DFGPath" => "text-align:center;",
    "DFUPath" => "text-align:center;",
    // <td>-Tag Feld Author:
    "DFGAuthor" => "text-align:center;",
    "DFUAuthor" => "text-align:center;",
    // <td>-Tag Feld Disclaimer:
    "DFGDisclaimer" => "text-align:center;",
    "DFUDisclaimer" => "text-align:center;",
    // <td>-Tag Feld Fanart:
    "DFGFanart" => "text-align:center;",
    "DFUFanart" => "text-align:center;",
    // <td>-Tag Feld Broken:
    "DFGBroken" => "text-align:center;",
    "DFUBroken" => "text-align:center;",
    // <td>-Tag Feld Rating:
    "DFGRating" => "text-align:center;",
    "DFURating" => "text-align:center;",
    // <td>-Tag Feld Type:
    "DFGType" => "text-align:center;",
    "DFUType" => "text-align:center;",
    // <td>-Tag Feld Addonid:
    "DFGAddonid" => "text-align:center;",
    "DFUAddonid" => "text-align:center;",
    // <td>-Tag Feld Execute:
    "DFGExecute" => "text-align:center;",
    "DFUExecute" => "text-align:center;",
    // ^- Der Buchstabe "G" steht für gerade, "U" für ungerade.
 );
 
// CSS-Styles für die Klassen der Buttons.
// isenabled => Button für Addon ist aktivieren
// isdisabled => Button für Addon ist deaktivieren
// doenabled => Button für Addon aktivieren
// dodisabled => Button für Addon deaktivieren
//execute => Button für Addon ausführen
$Config["Button"]= array(
	"isenabled" => "
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(50% 0%, transparent 0px, rgba(0, 0, 0, 0.3) 28%, rgba(0, 0, 0, 0.3) 100%);
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
color: rgba(255, 255, 255, 0.3);
color: rgb(255, 255, 255);
background-color: rgba(255,255,255,0.1);
background-color: rgb(0, 255, 0);
width: 25%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;",

	"isdisabled"=> "
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-color: rgba(255, 255, 255, 0.3);
width: 25%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;",

	"doenabled" => "
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-color: rgba(0, 255, 0, 0.3);
width: 25%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;",

	"dodisabled"=> "
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-color: rgba(255,255,255,0.1);
color: rgba(255, 255, 255, 0.3);
width: 25%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;",
	"execute"=> "
background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);
background-color: rgb(0, 255, 0);
width: 80%;
display: inline-block;
margin: 2px 0px 1px 3px;
font-family: arial,sans-serif; 
font-size: 17px;
line-height: 28px;
border-color: transparent;
border-style: solid;
border-width: 1px 0px;
min-height: 28px;
padding: 0px 10px;
vertical-align: middle;",

);

### Konfig ENDE !!!
echo serialize($Config);
';
        return $Script;
    }
}

/** @} */
