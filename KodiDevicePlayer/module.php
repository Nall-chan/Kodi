<?

require_once(__DIR__ . "/../KodiClass.php");  // diverse Klassen

class KodiDevicePlayer extends KodiBase
{

    static $Namespace = 'Player';
    static $Properties = array(
        "type",
        "partymode",
        "speed",
        "time",
        "percentage",
        "totaltime",
        "playlistid",
        "position",
        "repeat",
        "shuffled",
        "canseek",
        "canchangespeed",
        "canmove",
        "canzoom",
        "canrotate",
        "canshuffle",
        "canrepeat",
        "currentaudiostream",
        "audiostreams",
        "subtitleenabled",
        "currentsubtitle",
        "subtitles",
        "live"
    );
    private $PlayerId = null;

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyInteger('PlayerID', 0);
    }

    public function ApplyChanges()
    {
        $this->RegisterProfileIntegerEx("Status.Kodi", "Information", "", "", Array(
            //Array(0, "Prev", "", -1),
            Array(1, "Stop", "", -1),
            Array(2, "Play", "", -1),
            Array(3, "Pause", "", -1)
                //Array(4, "Next", "", -1)
        ));
        $this->RegisterProfileInteger("Intensity.Kodi", "Intensity", "", " %", 0, 100, 1);
        $this->RegisterProfileInteger("AudioTracks." . $this->InstanceID . ".Kodi", "", "", "", 1, 1, 1);

        $this->RegisterVariableInteger("Status", "Status", "Status.Kodi", 3);
        $this->EnableAction("Status");

        $this->RegisterVariableString("label", "Titel", "", 20);
        $this->RegisterVariableString("type", "Typ", "", 21);

        $this->RegisterVariableString("totaltime", "Dauer", "", 24);
        $this->RegisterVariableString("time", "Spielzeit", "", 25);
        $this->RegisterVariableInteger("percentage", "Position", "Intensity.Kodi", 26);
        $this->RegisterVariableInteger("audioindex", "Aktueller Audiotrack", "AudioTracks." . $this->InstanceID . ".Kodi", 30);
        $this->RegisterVariableString("audiolanguage", "Sprache", "", 31);
        $this->RegisterVariableInteger("audiochannels", "Audiokanäle", "", 32);
        $this->RegisterVariableString("audiocodec", "Audio Codec", "", 23);
        $this->RegisterVariableInteger("audiobitrate", "Audio Bitrate", "", 34);

        $this->RegisterVariableInteger("audiostreams", "Anzahl Audiotracks", "", 35);


        $this->EnableAction("percentage");

//        $this->RegisterProfileIntegerEx("Action.Kodi", "", "", "", Array(
//            Array(0, "Ausführen", "", -1)
//        ));
//        $this->RegisterVariableString("name", "Name", "", 0);
//        $this->RegisterVariableString("version", "Version", "", 1);
//        $this->RegisterVariableInteger("quit", "Kodi beenden", "Action.Kodi", 2);
//        $this->EnableAction("quit");
//        $this->RegisterVariableBoolean("mute", "Mute", "~Switch", 3);
//        $this->EnableAction("mute");
//        $this->RegisterVariableInteger("volume", "Volume", "~Intensity.100", 4);
//        $this->EnableAction("volume");
        //Never delete this line!
        parent::ApplyChanges();
        $this->RegisterTimer('PlayerStatus', 0, 'KODIPLAYER_RequestState($_IPS[\'TARGET\'],"ALL");');
    }

################## PRIVATE     

    private function Init()
    {
        if (is_null($this->PlayerId))
            $this->PlayerId = $this->ReadPropertyInteger('PlayerID');
    }

    protected function RequestProperties(array $Params)
    {
        $this->Init();
        $Params = array_merge($Params, array("playerid" => $this->PlayerId));
        parent::RequestProperties($Params);
    }

    protected function Decode($Method, $KodiPayload)
    {
        $this->Init();
        if (property_exists($KodiPayload, 'player')
                and ( $KodiPayload->player->playerid <> $this->PlayerId))
            return false;
        switch ($Method)
        {
            case 'GetProperties':
            case 'OnPropertyChanged':
                foreach ($KodiPayload as $param => $value)
                {
                    switch ($param)
                    {
                        case "percentage":
                            $this->SetValueInteger('percentage', (int) $value);
                            break;
                        case "totaltime":
                        case "time":
                            $this->SetValueString($param, $this->ConvertTime($value));
                            break;
                        case "audiostreams":
                            $this->SetValueInteger($param, count($value));
                            break;
                        case "currentaudiostream":
                            if (is_null($value))
                            {
                                $this->SetValueInteger('audiobitrate', 0);
                                $this->SetValueInteger('audiochannels', 0);
                                $this->SetValueInteger('audioindex', 0);
                                $this->SetValueString('audiolanguage', "");
                                $this->SetValueString('audiocodec', "");
                            }
                            else
                            {
                                $this->SetValueInteger('audiobitrate', (int) $value->bitrate);
                                $this->SetValueInteger('audiochannels', (int) $value->channels);
                                $this->SetValueInteger('audioindex', (int) $value->index);
                                $this->SetValueString('audiolanguage', (string) $value->language);
                                $this->SetValueString('audiocodec', (string) $value->name);
                            }
                            break;

                        /*    {"audiostreams":[],"canchangespeed":true,"canmove":false,"canrepeat":true,"canrotate":false,"canseek":false,"canshuffle":true,"canzoom":false,
                          "currentaudiostream":null,"currentsubtitle":null,
                          "live":false,"partymode":false,"percentage":0,"playlistid":1,"position":-1,
                          "repeat":"off","shuffled":false,"speed":1,
                          "subtitleenabled":false,"subtitles":[],
                         */


                        /*                        case "subtitleenabled":
                          break;
                          case "currentsubtitle":
                          break;
                          case "repeat": //off
                          break;
                          case "shuffeld":
                          break;
                          case "speed":
                          break; */
                        default:
                            IPS_LogMessage($param, print_r($value, true));
                            break;
                    }
                }
                break;
            case 'OnStop':
                $this->SetTimerInterval('PlayerStatus', 0);
                $this->SetValueInteger('Status', 1);
                $this->SetValueString('totaltime', '');
                $this->SetValueString('time', '');
                $this->SetValueInteger('percentage', 0);
                IPS_RunScriptText('<? KODIPLAYER_GetItem(' . $this->InstanceID . ');');

                break;
            case 'OnPlay':
                $this->SetValueInteger('Status', 2);
                $this->SetTimerInterval('PlayerStatus', 2);
                IPS_RunScriptText('<? KODIPLAYER_GetItem(' . $this->InstanceID . ');');
                break;
            case 'OnPause':
                $this->SetTimerInterval('PlayerStatus', 0);
                $this->SetValueInteger('Status', 3);
                break;
            case 'OnSeek':
                $this->SetValueString('time', $this->ConvertTime($KodiPayload->player->time));
                break;
            case 'OnSpeedChanged':
                break;
            default:
                IPS_LogMessage($Method, print_r($KodiPayload, true));
                break;
        }
    }

################## ActionHandler

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident)
        {
            case "Status":
                switch ($Value)
                {
                    /*                    case 0: //Prev
                      //$this->PreviousButton();
                      $result = $this->PreviousTrack();
                      break; */
                    case 1: //Stop
                        $result = $this->Stop();
                        break;
                    case 2: //Play
                        $result = $this->Play();
                        break;
                    case 3: //Pause
                        $result = $this->Pause();
                        break;
                    /*                    case 4: //Next
                      //$this->NextButton();
                      $result = $this->NextTrack();
                      break; */
                }
                break;
//            case "mute":
//                return $this->Mute($Value);
//            case "volume":
//                return $this->Volume($Value);
//            case "quit":
//                return $this->Quit();
//            default:
//                return trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

################## PUBLIC
    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:
     */

    public function RawSend(string $Namespace, string $Method, $Params)
    {
        return parent::RawSend($Namespace, $Method, $Params);
    }

    public function GetItem()
    {
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace, 'GetItem', array('playerid' => $this->PlayerId));
        $ret = $this->Send($KodiData);
        $this->SetValueString('label', $ret->item->label);
        $this->SetValueString('type', $ret->item->type);
        var_dump($ret);
    }

    public function Play()
    {
        $this->Init();

        $KodiData = new Kodi_RPC_Data(self::$Namespace, 'PlayPause', array("playerid" => $this->PlayerId, "play" => true));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->speed === 1)
        {
            $this->SetValueInteger("Status", 2);
            return true;
        }
        return false;
    }

    public function Pause()
    {
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace, 'PlayPause', array("playerid" => $this->PlayerId, "play" => false));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret->speed === 0)
        {
            $this->SetValueInteger("Status", 3);
            return true;
        }
        return false;
    }

    public function Stop()
    {
        $this->Init();
        $KodiData = new Kodi_RPC_Data(self::$Namespace, 'Stop', array("playerid" => $this->PlayerId));
        $ret = $this->Send($KodiData);
        if (is_null($ret))
            return false;
        if ($ret === "OK")
        {
            $this->SetValueInteger("Status", 1);
            return true;
        }
        return false;
    }

//    public function Volume(integer $Value)
//    {
//        if (!is_int($Value))
//        {
//            trigger_error('Value must be integer', E_USER_NOTICE);
//            return false;
//        }
////        $KodiData = new Kodi_RPC_Data(self::$Namespace, 'SetVolume', array("volume" => $Value));
//        $KodiData = new Kodi_RPC_Data(self::$Namespace);
//        $KodiData->SetVolume(array("volume" => $Value));
//        $ret = $this->Send($KodiData);
//        if (is_null($ret))
//            return false;
//        $this->SetValueInteger("volume", $ret);
//        return $ret['volume'] === $Value;
//    }
//
//    public function Quit()
//    {
//        $KodiData = new Kodi_RPC_Data(self::$Namespace, 'Quit');
//        $ret = $this->Send($KodiData);
//        if (is_null($ret))
//            return false;
//        return true;
//    }

    public function RequestState(string $Ident)
    {
        return parent::RequestState($Ident);
    }

    /*
      public function Pause()
      {

      }

      public function Stop()
      {

      }

     */
################## Datapoints

    public function ReceiveData($JSONString)
    {
        return parent::ReceiveData($JSONString);
    }

    /*
      protected function Send(Kodi_RPC_Data $KodiData)
      {
      return parent::Send($KodiData);
      }

      protected function SendDataToParent($Data)
      {
      return parent::SendDataToParent($Data);
      }
     */
}

?>