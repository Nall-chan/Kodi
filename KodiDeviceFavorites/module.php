<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceFavorites Klasse für den Namespace Favorites der KODI-API.
 * Erweitert KodiBase.
 *
 * @project       KodiDeviceFavorites
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
 * @property array $FavouriteItemList Alle Properties eines Favoriten
 */
class KodiDeviceFavorites extends KodiBase
{
    public const PropertyShowFavlist = 'showFavlist';
    public const PropertyThumbSize = 'ThumbSize';
    public const ActionVisibleFormElementsFavProperties = 'showFavlist';
    public const Hook = 'KodiFavlist';
    /** Alte Eigenschaft mit der ID des Konfigurations-Skripts (bis 3.02), nur noch in Migrate vorhanden. */
    public const LegacyPropertyConfigScript = 'Favlistconfig';
    public const FormPanelDesign = 'HTMLDesign';

    /** Spalten der Favoriten-Tabelle: Schlüssel, Überschrift, Breite (em), sichtbar. */
    private const TableColumns = [
        ['Thumbnail', '', 100, true],
        ['Title', 'Name', 300, true],
        ['Path', 'Path', 600, true],
        ['Type', 'Type', 100, true]
    ];

    protected static $Namespace = 'Favourites';
    protected static $Properties = [];
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
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyBoolean(self::PropertyShowFavlist, true);
        $this->RegisterPropertyInteger(self::PropertyThumbSize, 100);
        $this->RegisterTableProperties('', $this->GetDefaultTableConfig(self::TableColumns));
        $this->RegisterTimer('RefreshFavs', 0, 'KODIFAV_RefreshFavoriteList(' . $this->InstanceID . ');');
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
        if ($this->MigrateTableScript($Data->configuration, self::LegacyPropertyConfigScript, '', $this->GetDefaultTableConfig(self::TableColumns))) {
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
        $this->UnregisterScript('WebHookFavlist');

        if ($this->ReadPropertyBoolean(self::PropertyShowFavlist)) {
            $this->RegisterVariableString('Favlist', $this->Translate('Favorites'), '~HTMLBox', 1);
            $this->RegisterHook(self::Hook . $this->InstanceID);
            $this->SetTimerInterval('RefreshFavs', 15 * 60 * 1000);
        } else {
            $this->UnregisterVariable('Favlist');
            $this->UnregisterHook(self::Hook . $this->InstanceID);
            $this->SetTimerInterval('RefreshFavs', 0);
        }
        parent::ApplyChanges();
    }

    /**
     * GetConfigurationForm
     *
     * @return string
     */
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Show = $this->ReadPropertyBoolean(self::PropertyShowFavlist);
        $this->FillTableFormPanel($Form['elements'], self::FormPanelDesign, '', $Show);
        $Form['elements'][2]['visible'] = $Show;
        return json_encode($Form);
    }
    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIFAV_LoadFavoriteMedia'. Lädt einen Media-Favoriten.
     *
     * @access public
     * @param string $Path Der Pfad des Favoriten.
     * @return bool True bei Erfolg, sonst false.
     */
    public function LoadFavoriteMedia(string $Path): bool
    {
        $KodiData = new Kodi_RPC_Data('Player');
        $KodiData->Open(['item' => ['file' => $Path]]);
        $ret = $this->Send($KodiData);
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_LoadFavoriteScript'. Startet einen Skript-Favoriten.
     *
     * @access public
     * @param string $Script Das Script des Favoriten.
     * @return bool True bei Erfolg, sonst false.
     */
    public function LoadFavoriteScript(string $Script): bool
    {
        $KodiData = new Kodi_RPC_Data('Addons');
        $KodiData->ExecuteAddon(['addonid' => $Script]);
        $ret = $this->SendDirect($KodiData);
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_LoadFavoriteWindow'. Lädt einen Fenster-Favoriten.
     *
     * @access public
     * @param string $Window Das Ziel-Fenster des Favoriten.
     * @param string $WindowParameter Die Parameter für das Ziel-Fenster des Favoriten.
     * @return bool True bei Erfolg, sonst false.
     */
    public function LoadFavoriteWindow(string $Window, string $WindowParameter): bool
    {
        $KodiData = new Kodi_RPC_Data('GUI');
        if ($WindowParameter == '') {
            $KodiData->ActivateWindow(['window' => $Window]);
        } else {
            $KodiData->ActivateWindow(['window' => $Window, 'parameters' => [$WindowParameter]]);
        }
        $ret = $this->Send($KodiData);
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_AddFavorite'. Legt einen Favoriten in Kodi an.
     * Achtung: Kodi schaltet um. Existiert der Favorit bereits, wird er entfernt.
     *
     * @access public
     * @param string $Title Name des Favoriten.
     * @param string $Type Typ des Favoriten: 'media', 'window', 'script' oder 'androidapp'.
     * @param string $Path Pfad zur Datei, ID des Skript-Addons bzw. der Android-App (Pflicht bei media, script, androidapp).
     * @param string $Window Ziel-Fenster (Pflicht bei window).
     * @param string $WindowParameter Parameter für das Fenster (optional, nur bei window).
     * @param string $Thumbnail Pfad zum Vorschaubild (optional).
     * @return bool True bei Erfolg, sonst false.
     */
    public function AddFavorite(string $Title, string $Type, string $Path, string $Window, string $WindowParameter, string $Thumbnail): bool
    {
        $Type = strtolower($Type);
        if (!in_array($Type, ['media', 'window', 'script', 'androidapp'], true)) {
            trigger_error($this->Translate('Type must be "media", "window", "script" or "androidapp".'), E_USER_NOTICE);
            return false;
        }
        if (trim($Title) === '') {
            trigger_error($this->Translate('Title must not be empty.'), E_USER_NOTICE);
            return false;
        }
        $Params = ['title' => $Title, 'type' => $Type];
        if ($Type == 'window') {
            if ($Window === '') {
                trigger_error($this->Translate('Window must not be empty.'), E_USER_NOTICE);
                return false;
            }
            $Params['window'] = $Window;
            if ($WindowParameter !== '') {
                $Params['windowparameter'] = $WindowParameter;
            }
        } else {
            if ($Path === '') {
                trigger_error($this->Translate('Path must not be empty.'), E_USER_NOTICE);
                return false;
            }
            $Params['path'] = $Path;
        }
        if ($Thumbnail !== '') {
            $Params['thumbnail'] = $Thumbnail;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->AddFavourite($Params);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret !== 'OK') {
            trigger_error($this->Translate('Error on add favorite.'), E_USER_NOTICE);
            return false;
        }
        $this->RefreshFavoriteList();
        return true;
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_GetFavorites'. Liefert die Favoriten.
     *
     * @access public
     * @param string $Type Der Typ der zu suchenden Favoriten.
     *   enum["media"=Media, "window"=Fenster, "script"=Skript, "unknown"=Unbekannt]
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetFavorites(string $Type): false|array
    {
        $Type = strtolower($Type);
        if (!in_array($Type, ['all', 'media', 'window', 'script', 'unknown'])) {
            trigger_error($this->Translate('Type must be "all", "media", "window", "script" or "unknown".'), E_USER_NOTICE);
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
     * IPS-Instanz-Funktion 'KODIFAV_RefreshFavoriteList'.
     * Erzeugt aus der Liste der Favoriten eine HTML-Tabelle für eine ~HTMLBox-Variable (wird auch vom Timer aufgerufen).
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function RefreshFavoriteList(): bool
    {
        if (!$this->ReadPropertyBoolean(self::PropertyShowFavlist)) {
            return false;
        }
        $Config = $this->GetTableConfig();
        $AllFavs = $this->GetFavorites('all');
        if ($AllFavs === false) {
            return false;
        }
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
                if (!array_key_exists('Path', $Line)) {
                    if (array_key_exists('Windowparameter', $Line)) {
                        $Line['Path'] = $Line['Windowparameter'];
                    } else {
                        $Line['Path'] = '';
                    }
                }

                $HTMLData .= $this->GetTableRow($Config, $pos % 2 ? \KodiBase\TableRow::Odd : \KodiBase\TableRow::Even, $Line, $this->GetWebHookLink($Line, $NewSecret));
                $pos++;
            }
        }
        $HTMLData .= $this->GetTableFooter();
        $this->SetValueString('Favlist', $HTMLData);
        return true;
    }

    ################## DEPRECATED (Entfernung in Version 3.20)
    /**
     * IPS-Instanz-Funktion 'KODIFAV_LoadFavouriteMedia'.
     *
     * @deprecated 3.10 Wird in Version 3.20 entfernt, ersetzt durch KODIFAV_LoadFavoriteMedia.
     */
    public function LoadFavouriteMedia(string $Path): bool
    {
        $this->DeprecatedFunction('KODIFAV_LoadFavouriteMedia', 'KODIFAV_LoadFavoriteMedia');
        return $this->LoadFavoriteMedia($Path);
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_LoadFavouriteScript'.
     *
     * @deprecated 3.10 Wird in Version 3.20 entfernt, ersetzt durch KODIFAV_LoadFavoriteScript.
     */
    public function LoadFavouriteScript(string $Script): bool
    {
        $this->DeprecatedFunction('KODIFAV_LoadFavouriteScript', 'KODIFAV_LoadFavoriteScript');
        return $this->LoadFavoriteScript($Script);
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_LoadFavouriteWindow'.
     *
     * @deprecated 3.10 Wird in Version 3.20 entfernt, ersetzt durch KODIFAV_LoadFavoriteWindow.
     */
    public function LoadFavouriteWindow(string $Window, string $WindowParameter): bool
    {
        $this->DeprecatedFunction('KODIFAV_LoadFavouriteWindow', 'KODIFAV_LoadFavoriteWindow');
        return $this->LoadFavoriteWindow($Window, $WindowParameter);
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_GetFavourites'.
     *
     * @deprecated 3.10 Wird in Version 3.20 entfernt, ersetzt durch KODIFAV_GetFavorites.
     */
    public function GetFavourites(string $Type): false|array
    {
        $this->DeprecatedFunction('KODIFAV_GetFavourites', 'KODIFAV_GetFavorites');
        return $this->GetFavorites($Type);
    }

    /**
     * IPS-Instanz-Funktion 'KODIFAV_RefreshFavouriteList'.
     *
     * @deprecated 3.10 Wird in Version 3.20 entfernt, ersetzt durch KODIFAV_RefreshFavoriteList.
     */
    public function RefreshFavouriteList(): bool
    {
        $this->DeprecatedFunction('KODIFAV_RefreshFavouriteList', 'KODIFAV_RefreshFavoriteList');
        return $this->RefreshFavoriteList();
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
            case self::ActionVisibleFormElementsFavProperties:
                $this->UpdateFormField(self::FormPanelDesign, 'visible', (bool) $Value);
                $this->UpdateFormField('ThumbRow', 'visible', (bool) $Value);
                return;
            default:
                trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
                return;
        }
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState(int $State): void
    {
        $this->SetTimerInterval('RefreshFavs', 0);
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE) {
            $this->RefreshFavoriteList();
            if ($this->ReadPropertyBoolean(self::PropertyShowFavlist)) {
                $this->SetTimerInterval('RefreshFavs', 15 * 60 * 1000);
            }
        }
    }

    ################## PRIVATE
    /**
     * Dekodiert die empfangenen Events.
     *
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
    }

    /**
     * Filter die aktuell nicht unterstützten Favoriten aus.
     *
     * @param array $Fav Array mit allen Favoriten.
     * @return boolean True für behalten, False für verwerfen.
     */
    protected function FilterFav(array $Fav): bool
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
    protected function ProcessHookdata(): void
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
                $KodiData->Open(['item' => ['file' => $Path]]);
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
     * Liefert JS-Code für einen Webhook-Request.
     *
     * @param array $Data Daten des Favoriten.
     * @return string JS-Code
     */
    private function GetWebHookLink(array $Data, string $NewSecret): string
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
}
