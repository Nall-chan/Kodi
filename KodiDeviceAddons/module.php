<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceAddons Klasse für den Namespace Addons der KODI-API.
 * Erweitert KodiBase.
 *
 * @project       KodiDeviceAddons
 * @file          module.php
 * @package       Kodi
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       3.10
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
    public const Hook = 'KodiAddonlist';
    /** Alte Eigenschaft mit der ID des Konfigurations-Skripts (bis 3.02), nur noch in Migrate vorhanden. */
    public const LegacyPropertyConfigScript = 'Addonlistconfig';
    public const FormPanelDesign = 'HTMLDesign';

    /** Spalten der Addon-Tabelle: Schlüssel, Überschrift, Breite (em), sichtbar. */
    private const TableColumns = [
        ['Thumbnail', '', 100, true],
        ['Enabled', 'Active', 120, true],
        ['Name', 'Name', 200, true],
        ['Version', 'Version', 100, true],
        ['Summary', 'Summary', 400, true],
        ['Execute', 'Start', 100, true],
        ['Description', 'Description', 400, false],
        ['Path', 'Path', 100, false],
        ['Author', 'Author', 100, false],
        ['Disclaimer', 'Disclaimer', 100, false],
        ['Fanart', 'Fanart', 100, false],
        ['Broken', 'Broken', 50, false],
        ['Rating', 'Rating', 50, false],
        ['Type', 'Type', 100, false],
        ['Addonid', 'Addon ID', 200, false]
    ];

    /** Gemeinsamer Teil der Button-Styles. */
    private const ButtonBaseStyle = 'display:inline-block; margin:2px 0px 1px 3px; font-family:arial,sans-serif; font-size:17px; line-height:28px; border-color:transparent; border-style:solid; border-width:1px 0px; min-height:28px; padding:0px 10px; vertical-align:middle; background-image:linear-gradient(to bottom,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 28%,rgba(0,0,0,0.3) 100%);';

    /** CSS-Klassen der Buttons: isenabled/isdisabled = Zustand, doenabled/dodisabled = schalten, execute = ausführen. */
    private const TableButtons = [
        'isenabled'  => 'color:rgb(255,255,255); background-color:rgb(0,255,0); width:25%;',
        'isdisabled' => 'background-color:rgba(255,255,255,0.3); width:25%;',
        'doenabled'  => 'background-color:rgba(0,255,0,0.3); width:25%;',
        'dodisabled' => 'color:rgba(255,255,255,0.3); background-color:rgba(255,255,255,0.1); width:25%;',
        'execute'    => 'background-color:rgb(0,255,0); width:80%;'
    ];

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
        $this->RegisterTableProperties('', $this->GetDefaultAddonTable());
    }

    /**
     * Interne Funktion des SDK.
     * Überträgt das Design aus dem alten Konfigurations-Skript in die Eigenschaften.
     *
     * @access public
     */
    public function Migrate(string $JSONData): string
    {
        $Data = json_decode($JSONData);
        if ($this->MigrateTableScript($Data->configuration, self::LegacyPropertyConfigScript, '', $this->GetDefaultAddonTable())) {
            return json_encode($Data);
        }
        return '';
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
            $this->RegisterHook(self::Hook . $this->InstanceID);
        } else {
            $this->UnregisterVariable('Addonlist');
            $this->UnregisterHook(self::Hook . $this->InstanceID);
        }
        parent::ApplyChanges();
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Show = $this->ReadPropertyBoolean(self::PropertyShowAddonlist);
        $this->FillTableFormPanel($Form['elements'], self::FormPanelDesign, '', $Show, true);
        $Form['elements'][2]['visible'] = $Show;
        return json_encode($Form);
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion '*_RequestState'. Frage eine oder mehrere Properties eines Namespace ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident): bool
    {
        return $this->RefreshAddonlist();
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
     * IPS-Instanz-Funktion 'KODIADDONS_SetAddonEnabled'. Aktiviert oder deaktiviert ein Addon und erneuert danach die Addon-Liste.
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
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->RefreshAddonlist();
        return $ret === 'OK';
    }

    ################## DEPRECATED (Entfernung in Version 3.20)
    /**
     * IPS-Instanz-Funktion 'KODIADDONS_EnableAddon'.
     *
     * @deprecated 3.10 Wird in Version 3.20 entfernt, ersetzt durch KODIADDONS_SetAddonEnabled.
     */
    public function EnableAddon(string $AddonId, bool $Value): bool
    {
        $this->DeprecatedFunction('KODIADDONS_EnableAddon', 'KODIADDONS_SetAddonEnabled');
        return $this->SetAddonEnabled($AddonId, $Value);
    }
    ################## ActionHandler
    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     *
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    protected function ActionHandler(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case self::ActionVisibleFormElementsAddon:
                $this->UpdateFormField(self::FormPanelDesign, 'visible', (bool) $Value);
                $this->UpdateFormField('ThumbRow', 'visible', (bool) $Value);
                return;
            default:
                trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
        }
    }

    ################## PRIVATE

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * Erzeugt die Addon-Liste neu, sobald Kodi verbunden ist.
     *
     * @access protected
     */
    protected function IOChangeState(int $State): void
    {
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE) {
            $this->RefreshAddonlist();
        }
    }

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
                if ($this->SetAddonEnabled($_GET['Addonid'], true) === true) {
                    echo 'OK';
                }
                break;
            case 'Disable':
                if ($this->SetAddonEnabled($_GET['Addonid'], false) === true) {
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
     * @return bool False, wenn die Addons nicht gelesen werden konnten.
     */
    private function RefreshAddonlist(): bool
    {
        if (!$this->ReadPropertyBoolean(self::PropertyShowAddonlist)) {
            return true;
        }
        $Config = $this->GetTableConfig('', true);
        $AllAddons = $this->GetAddons();
        $Result = ($AllAddons !== false);
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
                if (isset($Config['Columns']['Thumbnail'])) {
                    if (($Line['Thumbnail'] ?? '') != '') {
                        $CoverRAW = $this->GetThumbnail($Line['Thumbnail'], $this->ReadPropertyInteger(self::PropertyThumbSize), 0);
                        if ($CoverRAW === false) {
                            $Line['Thumbnail'] = '';
                        } else {
                            $Line['Thumbnail'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                        }
                    }
                }
                if (isset($Config['Columns']['Fanart']) && (($Line['Fanart'] ?? '') != '')) {
                    $CoverRAW = $this->GetThumbnail($Line['Fanart'], $this->ReadPropertyInteger(self::PropertyThumbSize), 0);
                    if ($CoverRAW === false) {
                        $Line['Fanart'] = '';
                    } else {
                        $Line['Fanart'] = '<img src="data:image/png;base64,' . base64_encode($CoverRAW) . '" />';
                    }
                }

                $Line['Enabled'] = $Line['Enabled'] == true ? '<div class="dodisabled" ' . $this->GetWebHookLink($Line['Addonid'], 'Disable', $NewSecret) . '>' . $this->Translate('Off') . '</div><div class="isenabled" ' . $this->GetWebHookLink($Line['Addonid'], 'Enable', $NewSecret) . '>' . $this->Translate('On') . '</div>' : '<div class="isdisabled" ' . $this->GetWebHookLink($Line['Addonid'], 'Disable', $NewSecret) . '>' . $this->Translate('Off') . '</div><div class="doenabled" ' . $this->GetWebHookLink($Line['Addonid'], 'Enable', $NewSecret) . '>' . $this->Translate('On') . '</div>';
                $Line['Execute'] = '<div class="execute" ' . $this->GetWebHookLink($Line['Addonid'], 'Execute', $NewSecret) . '>' . $this->Translate('Execute') . '</div>';

                $HTMLData .= $this->GetTableRow($Config, $pos % 2 ? \KodiBase\TableRow::Odd : \KodiBase\TableRow::Even, $Line);
                $pos++;
            }
        }
        $HTMLData .= $this->GetTableFooter();
        $this->SetValueString('Addonlist', $HTMLData);
        return $Result;
    }

    /**
     * Liefert die Standardkonfiguration der Addon-Tabelle.
     *
     * @access private
     * @return array Konfiguration (Table, Columns, Rows, Buttons).
     */
    private function GetDefaultAddonTable(): array
    {
        $Buttons = [];
        foreach (self::TableButtons as $Class => $Style) {
            $Buttons[] = ['class' => $Class, 'style' => self::ButtonBaseStyle . ' ' . $Style];
        }
        return $this->GetDefaultTableConfig(self::TableColumns, false, $Buttons);
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
}
