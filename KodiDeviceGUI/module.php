<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceGUI Klasse für den Namespace GUI der KODI-API.
 * Erweitert KodiBase.
 *
 * @project       KodiDeviceGUI
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
 */
class KodiDeviceGUI extends KodiBase
{
    public const PropertyShowCurrentWindow = 'showCurrentWindow';
    public const PropertyShowCurrentControl = 'showCurrentControl';
    public const PropertyShowSkin = 'showSkin';
    public const PropertyShowFullscreen = 'showFullscreen';
    public const PropertyShowScreensaver = 'showScreensaver';

    protected static $Namespace = 'GUI';
    protected static $Properties = [
        'currentwindow',
        'currentcontrol',
        'skin',
        'fullscreen',
        'stereoscopicmode'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyBoolean(self::PropertyShowCurrentWindow, true);
        $this->RegisterPropertyBoolean(self::PropertyShowCurrentControl, true);
        $this->RegisterPropertyBoolean(self::PropertyShowSkin, true);
        $this->RegisterPropertyBoolean(self::PropertyShowFullscreen, true);
        $this->RegisterPropertyBoolean(self::PropertyShowScreensaver, true);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        if ($this->ReadPropertyBoolean(self::PropertyShowCurrentWindow)) {
            $this->RegisterVariableString('currentwindow', $this->Translate('Current window'), '', 0);
            $this->RegisterVariableInteger('_currentwindowid', $this->Translate('Current window (id)'), '', 0);
        } else {
            $this->UnregisterVariable('currentwindow');
            $this->UnregisterVariable('_currentwindowid');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowCurrentControl)) {
            $this->RegisterVariableString('currentcontrol', $this->Translate('Current control'), '', 1);
        } else {
            $this->UnregisterVariable('currentcontrol');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowSkin)) {
            $this->RegisterVariableString('skin', $this->Translate('Current skin'), '', 2);
            $this->RegisterVariableString('_skinid', $this->Translate('Current skin (id)'), '', 2);
        } else {
            $this->UnregisterVariable('skin');
            $this->UnregisterVariable('_skinid');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowFullscreen)) {
            $this->RegisterVariableBoolean('fullscreen', $this->Translate('Full screen'), '~Switch', 3);
            $this->EnableAction('fullscreen');
        } else {
            $this->UnregisterVariable('fullscreen');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowScreensaver)) {
            $this->RegisterVariableBoolean('screensaver', $this->Translate('Screensaver'), '~Switch', 4);
            $this->EnableAction('screensaver');
        } else {
            $this->UnregisterVariable('screensaver');
        }

        parent::ApplyChanges();
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIGUI_ActivateScreenSaver'.
     * Startet den Bildschirmschoner von Kodi.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ActivateScreenSaver(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ActivateScreenSaver();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_DeactivateScreenSaver'.
     * Beendet den Bildschirmschoner von Kodi. Kodi kennt dafür keine eigene Methode,
     * jede Eingabe beendet ihn. Gesendet wird die Eingabeaktion 'noop', welche sonst nichts auslöst.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function DeactivateScreenSaver(): bool
    {
        $KodiData = new Kodi_RPC_Data('Input');
        $KodiData->ExecuteAction(['action' => 'noop']);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }
    /**
     * IPS-Instanz-Funktion 'KODIGUI_SetFullscreen'.
     * De-/Aktiviert den Vollbildmodus.
     *
     * @access public
     * @param bool $Value True für Vollbild aktiv, False bei inaktiv.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SetFullscreen(bool $Value): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetFullscreen(['fullscreen' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->SetValueBoolean('fullscreen', $ret);
        if ($ret === $Value) {
            return true;
        }
        trigger_error($this->Translate('Error set full screen mode.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_ShowNotification'.
     * Erzeugt eine Benachrichtigung
     *
     * @access public
     * @param string $Title
     * @param string $Message
     * @param string $Image
     * @param int $Timeout
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ShowNotification(string $Title, string $Message, string $Image, int $Timeout): bool
    {
        $Data = ['title' => $Title, 'message' => $Message];

        if ($Image != '') {
            $Data['image'] = $Image;
        }
        if ($Timeout != 0) {
            $Data['displaytime'] = $Timeout;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ShowNotification($Data);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_ActivateWindow'.
     * Aktiviert ein Fenster
     *
     * @access public
     * @param string $Window Das zu aktivierende Fenster
     * @return bool true bei Erfolg, sonst false.
     */
    public function ActivateWindow(string $Window): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ActivateWindow(['window' => $Window]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_ActivateWindowEx'.
     * Aktiviert ein Fenster mit einem Parameter (z.B. einem Pfad oder einer Datenbank-URL wie musicdb://albums/).
     *
     * @access public
     * @param string $Window Das zu aktivierende Fenster
     * @param string $Parameter Der Parameter für das Fenster
     * @return bool true bei Erfolg, sonst false.
     */
    public function ActivateWindowEx(string $Window, string $Parameter): bool
    {
        if ($Parameter == '') {
            return $this->ActivateWindow($Window);
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ActivateWindow(['window' => $Window, 'parameters' => [$Parameter]]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_GetInfoLabels'.
     * Liest InfoLabels von Kodi aus (z.B. 'System.BuildVersion', 'Player.Title', 'Weather.Temperature').
     *
     * @access public
     * @param array $Labels Liste der InfoLabels.
     * @return array|bool Array InfoLabel => Wert (String) oder false bei Fehler.
     */
    public function GetInfoLabels(array $Labels): false|array
    {
        return $this->GetInfo('GetInfoLabels', 'labels', $Labels);
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_GetInfoBooleans'.
     * Liest boolesche Bedingungen von Kodi aus (z.B. 'Player.Playing', 'System.ScreenSaverActive').
     *
     * @access public
     * @param array $Booleans Liste der Bedingungen.
     * @return array|bool Array Bedingung => Wert oder false bei Fehler.
     */
    public function GetInfoBooleans(array $Booleans): false|array
    {
        return $this->GetInfo('GetInfoBooleans', 'booleans', $Booleans);
    }

    /**
     * IPS-Instanz-Funktion 'KODIGUI_RequestState'. Frage eine oder mehrere Properties ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident): bool
    {
        return parent::RequestState($Ident);
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
            case 'fullscreen':
                $this->SetFullscreen((bool) $Value);
                return;
            case 'screensaver':
                if ((bool) $Value) {
                    $this->ActivateScreenSaver();
                } else {
                    $this->DeactivateScreenSaver();
                }
                return;
            default:
                trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
                break;
        }
    }

    ################## PRIVATE
    /**
     * Dekodiert die empfangenen Events und Antworten auf 'GetProperties'.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
        switch ($Method) {
            case 'GetProperties':
                foreach ($KodiPayload as $param => $value) {
                    switch ($param) {
                        case 'currentcontrol':
                            $this->SetValueString('currentcontrol', $value->label);
                            break;
                        case 'currentwindow':
                            $this->SetValueString('currentwindow', $value->label);
                            $this->SetValueInteger('_currentwindowid', $value->id);
                            break;
                        case 'fullscreen':
                            $this->SetValueBoolean('fullscreen', $value);
                            break;
                        case 'skin':
                            $this->SetValueString('skin', $value->name);
                            $this->SetValueString('_skinid', $value->id);
                            break;
                    }
                }
                break;
            case 'OnScreensaverDeactivated':
                $this->SetValueBoolean('screensaver', false);
                break;
            case 'OnScreensaverActivated':
                $this->SetValueBoolean('screensaver', true);
                break;
        }
    }

    /**
     * Fragt InfoLabels oder InfoBooleans über den Namespace XBMC ab.
     *
     * @param string $Method 'GetInfoLabels' oder 'GetInfoBooleans'.
     * @param string $Param Name des Parameters ('labels' oder 'booleans').
     * @param array $Items Die abzufragenden Einträge.
     * @return array|bool Array Eintrag => Wert oder false bei Fehler.
     */
    private function GetInfo(string $Method, string $Param, array $Items): false|array
    {
        $Items = array_values(array_filter($Items, fn ($Item) => is_string($Item) && (trim($Item) !== '')));
        if (count($Items) == 0) {
            trigger_error($this->Translate('No entries to query.'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data('XBMC', $Method, [$Param => $Items]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret);
    }
}

