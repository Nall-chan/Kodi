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
 * KodiDeviceInput Klasse für den Namespace Input der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 *
 * @todo Input.ShowPlayerProcessInfo ab v8
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
    public const Hook = '/hook/KodiRemote';

    protected static $Namespace = 'Input';
    protected static $Properties = [];
    protected static $ExecuteAction = [
        'left',
        'right',
        'up',
        'down',
        'pageup',
        'pagedown',
        'select',
        'highlight',
        'parentdir',
        'parentfolder',
        'back',
        'previousmenu',
        'info',
        'pause',
        'stop',
        'skipnext',
        'skipprevious',
        'fullscreen',
        'aspectratio',
        'stepforward',
        'stepback',
        'bigstepforward',
        'bigstepback',
        'chapterorbigstepforward',
        'chapterorbigstepback',
        'osd',
        'showsubtitles',
        'nextsubtitle',
        'cyclesubtitle',
        'codecinfo',
        'nextpicture',
        'previouspicture',
        'zoomout',
        'zoomin',
        'playlist',
        'queue',
        'zoomnormal',
        'zoomlevel1',
        'zoomlevel2',
        'zoomlevel3',
        'zoomlevel4',
        'zoomlevel5',
        'zoomlevel6',
        'zoomlevel7',
        'zoomlevel8',
        'zoomlevel9',
        'nextcalibration',
        'resetcalibration',
        'analogmove',
        'analogmovex',
        'analogmovey',
        'rotate',
        'rotateccw',
        'close',
        'subtitledelayminus',
        'subtitledelay',
        'subtitledelayplus',
        'audiodelayminus',
        'audiodelay',
        'audiodelayplus',
        'subtitleshiftup',
        'subtitleshiftdown',
        'subtitlealign',
        'audionextlanguage',
        'verticalshiftup',
        'verticalshiftdown',
        'nextresolution',
        'audiotoggledigital',
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
        'osdleft',
        'osdright',
        'osdup',
        'osddown',
        'osdselect',
        'osdvalueplus',
        'osdvalueminus',
        'smallstepback',
        'fastforward',
        'rewind',
        'play',
        'playpause',
        'switchplayer',
        'delete',
        'copy',
        'move',
        'mplayerosd',
        'hidesubmenu',
        'screenshot',
        'rename',
        'togglewatched',
        'scanitem',
        'reloadkeymaps',
        'volumeup',
        'volumedown',
        'mute',
        'backspace',
        'scrollup',
        'scrolldown',
        'analogfastforward',
        'analogrewind',
        'moveitemup',
        'moveitemdown',
        'contextmenu',
        'shift',
        'symbols',
        'cursorleft',
        'cursorright',
        'showtime',
        'analogseekforward',
        'analogseekback',
        'showpreset',
        'nextpreset',
        'previouspreset',
        'lockpreset',
        'randompreset',
        'increasevisrating',
        'decreasevisrating',
        'showvideomenu',
        'enter',
        'increaserating',
        'decreaserating',
        'togglefullscreen',
        'nextscene',
        'previousscene',
        'nextletter',
        'prevletter',
        'jumpsms2',
        'jumpsms3',
        'jumpsms4',
        'jumpsms5',
        'jumpsms6',
        'jumpsms7',
        'jumpsms8',
        'jumpsms9',
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
        'lastpage',
        'guiprofile',
        'red',
        'green',
        'yellow',
        'blue',
        'increasepar',
        'decreasepar',
        'volampup',
        'volampdown',
        'volumeamplification',
        'createbookmark',
        'createepisodebookmark',
        'settingsreset',
        'settingslevelchange',
        'stereomode',
        'nextstereomode',
        'previousstereomode',
        'togglestereomode',
        'stereomodetomono',
        'channelup',
        'channeldown',
        'previouschannelgroup',
        'nextchannelgroup',
        'playpvr',
        'playpvrtv',
        'playpvrradio',
        'record',
        'leftclick',
        'rightclick',
        'middleclick',
        'doubleclick',
        'longclick',
        'wheelup',
        'wheeldown',
        'mousedrag',
        'mousemove',
        'tap',
        'longpress',
        'pangesture',
        'zoomgesture',
        'rotategesture',
        'swipeleft',
        'swiperight',
        'swipeup',
        'swipedown',
        'error',
        'noop'
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
        $this->UnregisterScript('WebHookRemote');

        if ($this->ReadPropertyBoolean(self::PropertyShowSVGRemote)) {
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->RegisterHook(self::Hook . $this->InstanceID);
            }
            if (@$this->GetIDForIdent('Remote') == false) {
                $remoteID = $this->RegisterVariableString('Remote', $this->Translate('Remote'), '~HTMLBox', 1);
                /* @var $remote string */
                include 'generateRemote' . ($this->ReadPropertyInteger('RemoteId')) . '.php';
                $this->SetValue('Remote', $remote);
            }
        } else {
            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->UnregisterHook(self::Hook . $this->InstanceID);
            }
            $this->UnregisterVariable('Remote');
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
            $this->UnregisterVariable('inputrequested');
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
                        trigger_error('Invalid Value.', E_USER_NOTICE);
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
                        trigger_error('Invalid Value.', E_USER_NOTICE);
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
                trigger_error('Invalid Ident.', E_USER_NOTICE);
                return;
        }
        if (!$ret) {
            trigger_error($this->Translate('Error on execute action.'), E_USER_NOTICE);
        }
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
     * IPS-Instanz-Funktion 'KODIINPUT_ExecuteAction'. Als Parameter übergebenen Tastendruck ausführen.
     *
     * @access public
     * @param string $Action Auszuführende Aktion.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function ExecuteAction(string $Action): bool
    {
        if (!in_array($Action, self::$ExecuteAction)) {
            trigger_error('Unknown action.', E_USER_NOTICE);
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

/** @} */
