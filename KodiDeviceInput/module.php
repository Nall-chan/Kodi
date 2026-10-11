<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceInput Klasse für den Namespace Input der KODI-API.
 * Erweitert KodiBase.
 *
 * @project       KodiDeviceInput
 * @file          module.php
 * @package       Kodi
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       3.10
 * @example <b>Ohne</b>
 *
 * @todo RemoteId raus aus Property und in Aktions-Bereich
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 * @property array $ExecuteAction Alle Aktionen der RPC-Methode ExecuteAction
 */
class KodiDeviceInput extends KodiBase
{
    public const PropertyShowSVGRemote = 'showSVGRemote';
    public const PropertyShowNavigationButtons = 'showNavigationButtons';
    public const PropertyShowControlButtons = 'showControlButtons';
    public const PropertyShowInputRequested = 'showInputRequested';
    public const PropertyShowTextInput = 'showTextInput';
    public const ActionVisibleFormElementsSVGRemoteProperties = 'showSVGRemote';
    public const Hook = 'KodiRemote';

    protected static $Namespace = 'Input';
    protected static $Properties = [];
    /** Aktionen der RPC-Methode Input.ExecuteAction (Kodi 21, aus xbmc/input/actions/ActionTranslator.cpp). */
    protected static $ExecuteAction = [
        'analogfastforward',
        'analogmove',
        'analogmovexleft',
        'analogmovexright',
        'analogmoveydown',
        'analogmoveyup',
        'analogrewind',
        'analogseekback',
        'analogseekforward',
        'aspectratio',
        'audiodelay',
        'audiodelayminus',
        'audiodelayplus',
        'audionextlanguage',
        'audiotoggledigital',
        'back',
        'backspace',
        'bigstepback',
        'bigstepforward',
        'blue',
        'browsesubtitle',
        'channeldown',
        'channelnumberseparator',
        'channelup',
        'chapterorbigstepback',
        'chapterorbigstepforward',
        'close',
        'codecinfo',
        'contextmenu',
        'copy',
        'createbookmark',
        'createepisodebookmark',
        'cursorleft',
        'cursorright',
        'cyclesubtitle',
        'cycletonemapmethod',
        'decreasepar',
        'decreaserating',
        'decreasevisrating',
        'delete',
        'doubleclick',
        'down',
        'enter',
        'error',
        'fastforward',
        'filter',
        'filterclear',
        'filtersms2',
        'filtersms3',
        'filtersms4',
        'filtersms5',
        'filtersms6',
        'filtersms7',
        'filtersms8',
        'filtersms9',
        'firstpage',
        'fullscreen',
        'green',
        'guiprofile',
        'hdrtoggle',
        'highlight',
        'increasepar',
        'increaserating',
        'increasevisrating',
        'info',
        'jumpsms2',
        'jumpsms3',
        'jumpsms4',
        'jumpsms5',
        'jumpsms6',
        'jumpsms7',
        'jumpsms8',
        'jumpsms9',
        'lastpage',
        'left',
        'leftclick',
        'lockpreset',
        'longclick',
        'longpress',
        'menu',
        'middleclick',
        'mousedrag',
        'mousedragend',
        'mousemove',
        'move',
        'moveitemdown',
        'moveitemup',
        'mute',
        'nextcalibration',
        'nextchannelgroup',
        'nextletter',
        'nextpicture',
        'nextpreset',
        'nextresolution',
        'nextscene',
        'nextstereomode',
        'nextsubtitle',
        'noop',
        'number0',
        'number1',
        'number2',
        'number3',
        'number4',
        'number5',
        'number6',
        'number7',
        'number8',
        'number9',
        'osd',
        'pagedown',
        'pageup',
        'pangesture',
        'parentdir',
        'parentfolder',
        'pause',
        'play',
        'playerdebug',
        'playerdebugvideo',
        'playerprocessinfo',
        'playerprogramselect',
        'playerresolutionselect',
        'playlist',
        'playnext',
        'playpause',
        'playpvr',
        'playpvrradio',
        'playpvrtv',
        'previouschannelgroup',
        'previousmenu',
        'previouspicture',
        'previouspreset',
        'previousscene',
        'previousstereomode',
        'prevletter',
        'queue',
        'randompreset',
        'record',
        'red',
        'reloadkeymaps',
        'rename',
        'resetcalibration',
        'rewind',
        'right',
        'rightclick',
        'rotate',
        'rotateccw',
        'rotategesture',
        'scanitem',
        'screenshot',
        'scrolldown',
        'scrollup',
        'select',
        'setrating',
        'settingslevelchange',
        'settingsreset',
        'shift',
        'showpreset',
        'showsubtitles',
        'showtime',
        'showtimerrule',
        'showvideomenu',
        'skipnext',
        'skipprevious',
        'smallstepback',
        'stepback',
        'stepforward',
        'stereomode',
        'stereomodetomono',
        'stop',
        'subtitlealign',
        'subtitledelay',
        'subtitledelayminus',
        'subtitledelayplus',
        'subtitleshiftdown',
        'subtitleshiftup',
        'swipedown',
        'swipeleft',
        'swiperight',
        'swipeup',
        'switchplayer',
        'symbols',
        'tap',
        'tempodown',
        'tempoup',
        'togglecommskip',
        'togglefont',
        'togglefullscreen',
        'togglestereomode',
        'togglewatched',
        'up',
        'verticalshiftdown',
        'verticalshiftup',
        'videonextstream',
        'voicerecognizer',
        'volampdown',
        'volampup',
        'volumeamplification',
        'volumedown',
        'volumeup',
        'wheeldown',
        'wheelup',
        'yellow',
        'zoomgesture',
        'zoomin',
        'zoomlevel1',
        'zoomlevel2',
        'zoomlevel3',
        'zoomlevel4',
        'zoomlevel5',
        'zoomlevel6',
        'zoomlevel7',
        'zoomlevel8',
        'zoomlevel9',
        'zoomnormal',
        'zoomout'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyBoolean(self::PropertyShowSVGRemote, true);
        $this->RegisterPropertyInteger('RemoteId', 1);
        $this->RegisterPropertyBoolean(self::PropertyShowNavigationButtons, true);
        $this->RegisterPropertyBoolean(self::PropertyShowControlButtons, true);
        $this->RegisterPropertyBoolean(self::PropertyShowInputRequested, true);
        $this->RegisterPropertyBoolean(self::PropertyShowTextInput, true);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        $this->UnregisterScript('WebHookRemote');

        if ($this->ReadPropertyBoolean(self::PropertyShowSVGRemote)) {
            $this->RegisterHook(self::Hook . $this->InstanceID);
            $this->RegisterVariableString('Remote', $this->Translate('Remote'), '~HTMLBox', 1);
            include 'generateRemote' . ($this->ReadPropertyInteger('RemoteId')) . '.php';
            $this->SetValue('Remote', $remote);
        } else {
            $this->UnregisterVariable('Remote');
            $this->UnregisterHook(self::Hook . $this->InstanceID);
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowNavigationButtons)) {
            $this->RegisterProfileIntegerEx('Navigation.Kodi', '', '', '', [
                [1, '<', '', -1],
                [2, '>', '', -1],
                [3, '^', '', -1],
                [4, 'v', '', -1],
                [5, 'OK', '', -1],
                [6, $this->Translate('Back'), '', -1],
                [7, 'Home', '', -1]
            ]);
            $this->RegisterVariableInteger('navremote', 'Navigation', 'Navigation.Kodi', 2);
            $this->EnableAction('navremote');
        } else {
            $this->UnregisterVariable('navremote');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowControlButtons)) {
            $this->RegisterProfileIntegerEx('Control.Kodi', '', '', '', [
                [1, '<<', '', -1],
                [2, $this->Translate('Menu'), '', -1],
                [3, 'Play', '', -1],
                [4, 'Pause', '', -1],
                [5, 'Stop', '', -1],
                [6, '>>', '', -1]
            ]);
            $this->RegisterVariableInteger('ctrlremote', $this->Translate('Control'), 'Control.Kodi', 3);
            $this->EnableAction('ctrlremote');
        } else {
            $this->UnregisterVariable('ctrlremote');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowInputRequested)) {
            $this->RegisterVariableBoolean('inputrequested', $this->Translate('Input expected'), '', 4);
            if (IPS_GetKernelRunlevel() == KR_INIT) {
                $this->SetValueBoolean('inputrequested', false);
            }
        } else {
            $this->UnregisterVariable('inputrequested');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowTextInput)) {
            $this->RegisterVariableString('inputtext', $this->Translate('Send input'), '', 5);
            $this->EnableAction('inputtext');
        } else {
            $this->UnregisterVariable('inputtext');
        }

        parent::ApplyChanges();
    }
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Form['elements'][1]['visible'] = $this->ReadPropertyBoolean(self::PropertyShowSVGRemote);
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Up'. Tastendruck 'Hoch' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Up(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Up();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Down'. Tastendruck 'Runter' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Down(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Down();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Left'. Tastendruck 'Links' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Left(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Left();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Right'. Tastendruck 'Rechts' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Right(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Right();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Back'. Tastendruck 'Zurück' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Back(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Back();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ContextMenu'. Tastendruck 'ContextMenu' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ContextMenu(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ContextMenu();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Home'. Tastendruck 'Home' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Home(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Home();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Info'. Tastendruck 'Info' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Info(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Info();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_Select'. Tastendruck 'Select' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Select(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Select();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ShowOSD'. Tastendruck 'ShowOSD' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ShowOSD(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ShowOSD();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ShowCodec'. Tastendruck 'ShowCodec' ausführen.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ShowCodec(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ShowCodec();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ShowPlayerProcessInfo'. Zeigt die Informationen zur Wiedergabe (Decoder, Bildformat usw.) an.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ShowPlayerProcessInfo(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ShowPlayerProcessInfo();
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ExecuteAction'. Als Parameter übergebenen Tastendruck ausführen.
     *
     * @access public
     * @param string $Action Auszuführende Aktion.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ExecuteAction(string $Action): bool
    {
        if (!in_array($Action, self::$ExecuteAction)) {
            trigger_error($this->Translate('Unknown action.'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ExecuteAction(['action' => $Action]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_ButtonEvent'. Simuliert einen Tastendruck einer Tastatur oder Fernbedienung.
     * Ausgeführt wird die Funktion, welche in der Tastenbelegung (Keymap) von Kodi für die Taste hinterlegt ist.
     *
     * @access public
     * @param string $Button Name der Taste aus der Keymap von Kodi (z.B. 'up', 'enter', 'escape', 'f1').
     * @param string $Keymap Tastenbelegung: 'KB' (Tastatur), 'XG' (Xbox-Gamepad), 'R1' oder 'R2' (Fernbedienungen).
     * @param int $HoldTime Dauer des Tastendrucks in Millisekunden (0 = kurzer Druck).
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ButtonEvent(string $Button, string $Keymap, int $HoldTime): bool
    {
        if (!in_array($Keymap, ['KB', 'XG', 'R1', 'R2'])) {
            trigger_error($this->Translate('Keymap must be "KB", "XG", "R1" or "R2".'), E_USER_NOTICE);
            return false;
        }
        if ($HoldTime < 0) {
            trigger_error($this->Translate('Hold time must not be negative.'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->ButtonEvent(['button' => $Button, 'keymap' => $Keymap, 'holdtime' => $HoldTime]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIINPUT_SendText'. Als Parameter übergebenen Text senden.
     *
     * @access public
     * @param string $Text Der zu sendende Text.
     * @param bool $Done True wenn die Eingabe beendet werden soll, sonst false.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SendText(string $Text, bool $Done): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SendText(['text' => $Text, 'done' => $Done]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
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
            case 'navremote':
                switch ($Value) {
                    case 1:
                        $ret = $this->Left();
                        break;
                    case 2:
                        $ret = $this->Right();
                        break;
                    case 3:
                        $ret = $this->Up();
                        break;
                    case 4:
                        $ret = $this->Down();
                        break;
                    case 5:
                        $ret = $this->Select();
                        break;
                    case 6:
                        $ret = $this->Back();
                        break;
                    case 7:
                        $ret = $this->Home();
                        break;
                    default:
                        trigger_error($this->Translate('Invalid Value.'), E_USER_NOTICE);
                        return;
                }
                break;
            case 'ctrlremote':
                switch ($Value) {
                    case 1:
                        $ret = $this->ExecuteAction('rewind');
                        break;
                    case 2:
                        $ret = $this->ExecuteAction('mplayerosd');
                        break;
                    case 3:
                        $ret = $this->ExecuteAction('play');
                        break;
                    case 4:
                        $ret = $this->ExecuteAction('pause');
                        break;
                    case 5:
                        $ret = $this->ExecuteAction('stop');
                        break;
                    case 6:
                        $ret = $this->ExecuteAction('fastforward');
                        break;
                    default:
                        trigger_error($this->Translate('Invalid Value.'), E_USER_NOTICE);
                        return;
                }
                break;
            case 'inputtext':
                $ret = $this->SendText((string) $Value, true);
                break;
            case self::ActionVisibleFormElementsSVGRemoteProperties:
                $this->UpdateFormField('RemoteId', 'visible', (bool) $Value);
                return;
            default:
                trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
                return;
        }
        if (!$ret) {
            trigger_error($this->Translate('Error on execute action.'), E_USER_NOTICE);
        }
    }

    ################## PRIVATE
    /**
     * Verarbeitet Daten aus dem Webhook.
     *
     * @access protected
     * @global array $_GET
     */
    protected function ProcessHookdata(): void
    {
        if (isset($_GET['button'])) {
            if ($this->ExecuteAction($_GET['button']) === true) {
                echo 'OK';
            }
        } else {
            $this->SendDebug('illegal HOOK', $_GET, 0);
            echo $this->Translate('Bad Request');
        }
    }

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
            case 'OnInputRequested':
                $this->SetValueBoolean('inputrequested', true);
                break;
            case 'OnInputFinished':
                $this->SetValueBoolean('inputrequested', true);
                break;
        }
    }
}
