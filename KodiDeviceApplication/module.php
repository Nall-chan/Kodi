<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceApplication Klasse für den Namespace Application der KODI-API.
 * Erweitert KodiBase.
 *
 * @project       KodiDeviceApplication
 * @file          module.php
 * @package       Kodi
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       3.02
 * @example <b>Ohne</b>
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 */
class KodiDeviceApplication extends KodiBase
{
    public const PropertyShowName = 'showName';
    public const PropertyShowVersion = 'showVersion';
    public const ProptertyShowExit = 'showExit';
    protected static $Namespace = 'Application';

    protected static $Properties = [
        'volume',
        'muted',
        'name',
        'version'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyBoolean(self::PropertyShowName, true);
        $this->RegisterPropertyBoolean(self::PropertyShowVersion, true);
        $this->RegisterPropertyBoolean(self::ProptertyShowExit, true);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        $this->RegisterProfileIntegerEx('Action.Kodi', '', '', '', [
            [0, $this->Translate('Execute'), '', -1]
        ]);

        if ($this->ReadPropertyBoolean(self::PropertyShowName)) {
            $this->RegisterVariableString('name', 'Name', '', 0);
        } else {
            $this->UnregisterVariable('name');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowVersion)) {
            $this->RegisterVariableString('version', 'Version', '', 1);
        } else {
            $this->UnregisterVariable('version');
        }

        if ($this->ReadPropertyBoolean(self::ProptertyShowExit)) {
            $this->RegisterVariableInteger('quit', $this->Translate('Quit Kodi'), 'Action.Kodi', 2);
            $this->EnableAction('quit');
        } else {
            $this->UnregisterVariable('quit');
        }

        $this->RegisterVariableBoolean('mute', $this->Translate('Mute'), '~Mute', 3);
        $this->EnableAction('mute');

        $this->RegisterVariableInteger('volume', 'Volume', '~Volume', 4);
        $this->EnableAction('volume');

        parent::ApplyChanges();
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIAPP_SetMute'. De-/Aktiviert die Stummschaltung
     *
     * @access public
     * @param bool $Value True für Stummschaltung aktiv, False bei inaktiv.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SetMute(bool $Value): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetMute(['mute' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->SetValueBoolean('mute', $ret);
        if ($ret === $Value) {
            return true;
        }
        trigger_error($this->Translate('Error on set mute.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_SetVolume'. Setzen der Lautstärke
     *
     * @access public
     * @param int $Value Neue Lautstärke
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SetVolume(int $Value): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetVolume(['volume' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->SetValueInteger('volume', $ret);
        if ($ret === $Value) {
            return true;
        }
        trigger_error($this->Translate('Error on set volume.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_VolumeUp'. Erhöht die Lautstärke um eine Stufe von Kodi.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function VolumeUp(): bool
    {
        return $this->ChangeVolume('increment');
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_VolumeDown'. Verringert die Lautstärke um eine Stufe von Kodi.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function VolumeDown(): bool
    {
        return $this->ChangeVolume('decrement');
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_Quit'. Beendet die Kodi-Anwendung
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Quit(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace); //, 'Quit');
        $KodiData->Quit();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on quit Kodi.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_GetProfiles'. Liefert alle Benutzerprofile von Kodi.
     *
     * @access public
     * @return array|bool Array mit den Profilen (label, thumbnail, lockmode) oder false bei Fehler.
     */
    public function GetProfiles(): false|array
    {
        $KodiData = new Kodi_RPC_Data('Profiles');
        $KodiData->GetProfiles(['properties' => ['thumbnail', 'lockmode']]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->profiles);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_GetCurrentProfile'. Liefert das aktive Benutzerprofil von Kodi.
     *
     * @access public
     * @return array|bool Array mit dem Profil (label, thumbnail, lockmode) oder false bei Fehler.
     */
    public function GetCurrentProfile(): false|array
    {
        $KodiData = new Kodi_RPC_Data('Profiles');
        $KodiData->GetCurrentProfile(['properties' => ['thumbnail', 'lockmode']]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret);
    }

    /**
     * IPS-Instanz-Funktion 'KODIAPP_RequestState'. Frage eine oder mehrere Properties ab.
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
            case 'mute':
                $this->SetMute((bool) $Value);
                return;
            case 'volume':
                $this->SetVolume((int) $Value);
                return;
            case 'quit':
                $this->Quit();
                return;
            default:
                trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
        }
    }

    ################## PRIVATE
    /**
     * Dekodiert die empfangenen Events und Antworten auf 'GetProperties'.
     *
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
        foreach ($KodiPayload as $param => $value) {
            switch ($param) {
                case 'mute':
                case 'muted':
                    $this->SetValueBoolean('mute', (bool) $value);
                    break;
                case 'volume':
                    $this->SetValueInteger('volume', (int) $value);
                    break;
                case 'name':
                    $this->SetValueString('name', (string) $value);
                    break;
                case 'version':
                    $this->SetValueString('version', (string) $value->major . '.' . (string) $value->minor);
                    break;
            }
        }
    }

    /**
     * Ändert die Lautstärke um eine Stufe.
     *
     * @param string $Direction 'increment' oder 'decrement'.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    private function ChangeVolume(string $Direction): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetVolume(['volume' => $Direction]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        $this->SetValueInteger('volume', (int) $ret);
        return true;
    }
}

