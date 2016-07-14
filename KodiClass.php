<?

if (@constant('IPS_BASE') == null) //Nur wenn Konstanten noch nicht bekannt sind.
{
// --- BASE MESSAGE
    define('IPS_BASE', 10000);                             //Base Message
    define('IPS_KERNELSHUTDOWN', IPS_BASE + 1);            //Pre Shutdown Message, Runlevel UNINIT Follows
    define('IPS_KERNELSTARTED', IPS_BASE + 2);             //Post Ready Message
// --- KERNEL
    define('IPS_KERNELMESSAGE', IPS_BASE + 100);           //Kernel Message
    define('KR_CREATE', IPS_KERNELMESSAGE + 1);            //Kernel is beeing created
    define('KR_INIT', IPS_KERNELMESSAGE + 2);              //Kernel Components are beeing initialised, Modules loaded, Settings read
    define('KR_READY', IPS_KERNELMESSAGE + 3);             //Kernel is ready and running
    define('KR_UNINIT', IPS_KERNELMESSAGE + 4);            //Got Shutdown Message, unloading all stuff
    define('KR_SHUTDOWN', IPS_KERNELMESSAGE + 5);          //Uninit Complete, Destroying Kernel Inteface
// --- KERNEL LOGMESSAGE
    define('IPS_LOGMESSAGE', IPS_BASE + 200);              //Logmessage Message
    define('KL_MESSAGE', IPS_LOGMESSAGE + 1);              //Normal Message                      | FG: Black | BG: White  | STLYE : NONE
    define('KL_SUCCESS', IPS_LOGMESSAGE + 2);              //Success Message                     | FG: Black | BG: Green  | STYLE : NONE
    define('KL_NOTIFY', IPS_LOGMESSAGE + 3);               //Notiy about Changes                 | FG: Black | BG: Blue   | STLYE : NONE
    define('KL_WARNING', IPS_LOGMESSAGE + 4);              //Warnings                            | FG: Black | BG: Yellow | STLYE : NONE
    define('KL_ERROR', IPS_LOGMESSAGE + 5);                //Error Message                       | FG: Black | BG: Red    | STLYE : BOLD
    define('KL_DEBUG', IPS_LOGMESSAGE + 6);                //Debug Informations + Script Results | FG: Grey  | BG: White  | STLYE : NONE
    define('KL_CUSTOM', IPS_LOGMESSAGE + 7);               //User Message                        | FG: Black | BG: White  | STLYE : NONE
// --- MODULE LOADER
    define('IPS_MODULEMESSAGE', IPS_BASE + 300);           //ModuleLoader Message
    define('ML_LOAD', IPS_MODULEMESSAGE + 1);              //Module loaded
    define('ML_UNLOAD', IPS_MODULEMESSAGE + 2);            //Module unloaded
// --- OBJECT MANAGER
    define('IPS_OBJECTMESSAGE', IPS_BASE + 400);
    define('OM_REGISTER', IPS_OBJECTMESSAGE + 1);          //Object was registered
    define('OM_UNREGISTER', IPS_OBJECTMESSAGE + 2);        //Object was unregistered
    define('OM_CHANGEPARENT', IPS_OBJECTMESSAGE + 3);      //Parent was Changed
    define('OM_CHANGENAME', IPS_OBJECTMESSAGE + 4);        //Name was Changed
    define('OM_CHANGEINFO', IPS_OBJECTMESSAGE + 5);        //Info was Changed
    define('OM_CHANGETYPE', IPS_OBJECTMESSAGE + 6);        //Type was Changed
    define('OM_CHANGESUMMARY', IPS_OBJECTMESSAGE + 7);     //Summary was Changed
    define('OM_CHANGEPOSITION', IPS_OBJECTMESSAGE + 8);    //Position was Changed
    define('OM_CHANGEREADONLY', IPS_OBJECTMESSAGE + 9);    //ReadOnly was Changed
    define('OM_CHANGEHIDDEN', IPS_OBJECTMESSAGE + 10);     //Hidden was Changed
    define('OM_CHANGEICON', IPS_OBJECTMESSAGE + 11);       //Icon was Changed
    define('OM_CHILDADDED', IPS_OBJECTMESSAGE + 12);       //Child for Object was added
    define('OM_CHILDREMOVED', IPS_OBJECTMESSAGE + 13);     //Child for Object was removed
    define('OM_CHANGEIDENT', IPS_OBJECTMESSAGE + 14);      //Ident was Changed
// --- INSTANCE MANAGER
    define('IPS_INSTANCEMESSAGE', IPS_BASE + 500);         //Instance Manager Message
    define('IM_CREATE', IPS_INSTANCEMESSAGE + 1);          //Instance created
    define('IM_DELETE', IPS_INSTANCEMESSAGE + 2);          //Instance deleted
    define('IM_CONNECT', IPS_INSTANCEMESSAGE + 3);         //Instance connectged
    define('IM_DISCONNECT', IPS_INSTANCEMESSAGE + 4);      //Instance disconncted
    define('IM_CHANGESTATUS', IPS_INSTANCEMESSAGE + 5);    //Status was Changed
    define('IM_CHANGESETTINGS', IPS_INSTANCEMESSAGE + 6);  //Settings were Changed
    define('IM_CHANGESEARCH', IPS_INSTANCEMESSAGE + 7);    //Searching was started/stopped
    define('IM_SEARCHUPDATE', IPS_INSTANCEMESSAGE + 8);    //Searching found new results
    define('IM_SEARCHPROGRESS', IPS_INSTANCEMESSAGE + 9);  //Searching progress in %
    define('IM_SEARCHCOMPLETE', IPS_INSTANCEMESSAGE + 10); //Searching is complete
// --- VARIABLE MANAGER
    define('IPS_VARIABLEMESSAGE', IPS_BASE + 600);              //Variable Manager Message
    define('VM_CREATE', IPS_VARIABLEMESSAGE + 1);               //Variable Created
    define('VM_DELETE', IPS_VARIABLEMESSAGE + 2);               //Variable Deleted
    define('VM_UPDATE', IPS_VARIABLEMESSAGE + 3);               //On Variable Update
    define('VM_CHANGEPROFILENAME', IPS_VARIABLEMESSAGE + 4);    //On Profile Name Change
    define('VM_CHANGEPROFILEACTION', IPS_VARIABLEMESSAGE + 5);  //On Profile Action Change
// --- SCRIPT MANAGER
    define('IPS_SCRIPTMESSAGE', IPS_BASE + 700);           //Script Manager Message
    define('SM_CREATE', IPS_SCRIPTMESSAGE + 1);            //On Script Create
    define('SM_DELETE', IPS_SCRIPTMESSAGE + 2);            //On Script Delete
    define('SM_CHANGEFILE', IPS_SCRIPTMESSAGE + 3);        //On Script File changed
    define('SM_BROKEN', IPS_SCRIPTMESSAGE + 4);            //Script Broken Status changed
// --- EVENT MANAGER
    define('IPS_EVENTMESSAGE', IPS_BASE + 800);             //Event Scripter Message
    define('EM_CREATE', IPS_EVENTMESSAGE + 1);             //On Event Create
    define('EM_DELETE', IPS_EVENTMESSAGE + 2);             //On Event Delete
    define('EM_UPDATE', IPS_EVENTMESSAGE + 3);
    define('EM_CHANGEACTIVE', IPS_EVENTMESSAGE + 4);
    define('EM_CHANGELIMIT', IPS_EVENTMESSAGE + 5);
    define('EM_CHANGESCRIPT', IPS_EVENTMESSAGE + 6);
    define('EM_CHANGETRIGGER', IPS_EVENTMESSAGE + 7);
    define('EM_CHANGETRIGGERVALUE', IPS_EVENTMESSAGE + 8);
    define('EM_CHANGETRIGGEREXECUTION', IPS_EVENTMESSAGE + 9);
    define('EM_CHANGECYCLIC', IPS_EVENTMESSAGE + 10);
    define('EM_CHANGECYCLICDATEFROM', IPS_EVENTMESSAGE + 11);
    define('EM_CHANGECYCLICDATETO', IPS_EVENTMESSAGE + 12);
    define('EM_CHANGECYCLICTIMEFROM', IPS_EVENTMESSAGE + 13);
    define('EM_CHANGECYCLICTIMETO', IPS_EVENTMESSAGE + 14);
// --- MEDIA MANAGER
    define('IPS_MEDIAMESSAGE', IPS_BASE + 900);           //Media Manager Message
    define('MM_CREATE', IPS_MEDIAMESSAGE + 1);             //On Media Create
    define('MM_DELETE', IPS_MEDIAMESSAGE + 2);             //On Media Delete
    define('MM_CHANGEFILE', IPS_MEDIAMESSAGE + 3);         //On Media File changed
    define('MM_AVAILABLE', IPS_MEDIAMESSAGE + 4);          //Media Available Status changed
    define('MM_UPDATE', IPS_MEDIAMESSAGE + 5);
// --- LINK MANAGER
    define('IPS_LINKMESSAGE', IPS_BASE + 1000);           //Link Manager Message
    define('LM_CREATE', IPS_LINKMESSAGE + 1);             //On Link Create
    define('LM_DELETE', IPS_LINKMESSAGE + 2);             //On Link Delete
    define('LM_CHANGETARGET', IPS_LINKMESSAGE + 3);       //On Link TargetID change
// --- DATA HANDLER
    define('IPS_DATAMESSAGE', IPS_BASE + 1100);             //Data Handler Message
    define('DM_CONNECT', IPS_DATAMESSAGE + 1);             //On Instance Connect
    define('DM_DISCONNECT', IPS_DATAMESSAGE + 2);          //On Instance Disconnect
// --- SCRIPT ENGINE
    define('IPS_ENGINEMESSAGE', IPS_BASE + 1200);           //Script Engine Message
    define('SE_UPDATE', IPS_ENGINEMESSAGE + 1);             //On Library Refresh
    define('SE_EXECUTE', IPS_ENGINEMESSAGE + 2);            //On Script Finished execution
    define('SE_RUNNING', IPS_ENGINEMESSAGE + 3);            //On Script Started execution
// --- PROFILE POOL
    define('IPS_PROFILEMESSAGE', IPS_BASE + 1300);
    define('PM_CREATE', IPS_PROFILEMESSAGE + 1);
    define('PM_DELETE', IPS_PROFILEMESSAGE + 2);
    define('PM_CHANGETEXT', IPS_PROFILEMESSAGE + 3);
    define('PM_CHANGEVALUES', IPS_PROFILEMESSAGE + 4);
    define('PM_CHANGEDIGITS', IPS_PROFILEMESSAGE + 5);
    define('PM_CHANGEICON', IPS_PROFILEMESSAGE + 6);
    define('PM_ASSOCIATIONADDED', IPS_PROFILEMESSAGE + 7);
    define('PM_ASSOCIATIONREMOVED', IPS_PROFILEMESSAGE + 8);
    define('PM_ASSOCIATIONCHANGED', IPS_PROFILEMESSAGE + 9);
// --- TIMER POOL
    define('IPS_TIMERMESSAGE', IPS_BASE + 1400);            //Timer Pool Message
    define('TM_REGISTER', IPS_TIMERMESSAGE + 1);
    define('TM_UNREGISTER', IPS_TIMERMESSAGE + 2);
    define('TM_SETINTERVAL', IPS_TIMERMESSAGE + 3);
    define('TM_UPDATE', IPS_TIMERMESSAGE + 4);
    define('TM_RUNNING', IPS_TIMERMESSAGE + 5);
// --- STATUS CODES
    define('IS_SBASE', 100);
    define('IS_CREATING', IS_SBASE + 1); //module is being created
    define('IS_ACTIVE', IS_SBASE + 2); //module created and running
    define('IS_DELETING', IS_SBASE + 3); //module us being deleted
    define('IS_INACTIVE', IS_SBASE + 4); //module is not beeing used
// --- ERROR CODES
    define('IS_EBASE', 200);          //default errorcode
    define('IS_NOTCREATED', IS_EBASE + 1); //instance could not be created
// --- Search Handling
    define('FOUND_UNKNOWN', 0);     //Undefined value
    define('FOUND_NEW', 1);         //Device is new and not configured yet
    define('FOUND_OLD', 2);         //Device is already configues (InstanceID should be set)
    define('FOUND_CURRENT', 3);     //Device is already configues (InstanceID is from the current/searching Instance)
    define('FOUND_UNSUPPORTED', 4); //Device is not supported by Module

    define('vtBoolean', 0);
    define('vtInteger', 1);
    define('vtFloat', 2);
    define('vtString', 3);
    define('vtArray', 8);
    define('vtObject', 9);
}
/* * @addtogroup kodi
 * @{
 *
 * @package Kodi
 * @file          KodiClass.php
 * @author        Michael Tröger
 *
 */

/**
 * Basisklasse für alle Kodi IPS-Instanzklassen.
 * Erweitert IPSModule.
 * 
 * @abstract
 */
abstract class KodiBase extends IPSModule
{

    /**
     * RPC-Namespace
     * 
     * @access private
     * @var string
     */
    static $Namespace;

    /**
     * Alle Properties des RPC-Namespace
     * 
     * @access private
     * @var array 
     */
    static $Properties;

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->ConnectParent("{D2F106B5-4473-4C19-A48F-812E8BAA316C}");
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->UnregisterVariable("_ReplyJSONData");
        if (IPS_GetKernelRunlevel() == KR_READY)
            if ($this->HasActiveParent())
                $this->RequestProperties(array("properties" => static::$Properties));
    }

################## PRIVATE     

    /**
     * Werte der Eigenschaften anfragen.
     * 
     * @access protected
     * @param array $Params Enthält den Index "properties", in welchen alle anzufragenden Eigenschaften als Array enthalten sind.
     * @return boolean true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    protected function RequestProperties(array $Params)
    {
        if (count($Params["properties"]) == 0)
            return true;
        $KodiData = new Kodi_RPC_Data(static::$Namespace, 'GetProperties', $Params);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret))
            return false;
        $this->Decode('GetProperties', $ret);
        return true;
    }

    /**
     * Muss überschieben werden. Dekodiert die empfangenen Events und Anworten auf 'GetProperties'.
     * 
     * @abstract
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    abstract protected function Decode($Method, $KodiPayload);

    /**
     * Erzeugt ein lesbares Zeitformat.
     * 
     * @access protected
     * @param object|integer $name Description $name Description object| $Time Die zu formatierende Zeit als Kodi-Objekt oder als Sekunden.
     * @return string Gibt die formatierte Zeit zurück.
     */
    protected function ConvertTime($Time)
    {
        if (is_object($Time))
        {
            $Time->minutes = str_pad($Time->minutes, 2, "00", STR_PAD_LEFT);
            $Time->seconds = str_pad($Time->seconds, 2, "00", STR_PAD_LEFT);
            if ($Time->hours > 0)
            {
                return $Time->hours . ":" . $Time->minutes . ":" . $Time->seconds;
            }
            return $Time->minutes . ":" . $Time->seconds;
        }
        if (is_int($Time))
        {
            if ($Time > 3600)
                return date("H:i:s", $Time);
            else
                return date("i:s", $Time);
        }
    }

################## PUBLIC

    /**
     * IPS-Instanz-Funktion '*_RequestState'. Frage eine oder mehrere Properties eines Namespace ab.
     *
     * @access public
     * @param string $Ident Enthält den Names des "properties" welches angefordert werden soll.
     * @return boolean true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(string $Ident)
    {
        if ($Ident == 'ALL')
            return $this->RequestProperties(array("properties" => static::$Properties));
        if ($Ident == 'PARTIAL')
            return $this->RequestProperties(array("properties" => static::$PartialProperties));
        if (!in_array($Ident, static::$Properties))
        {
            trigger_error('Property not found.');
            return false;
        }
        return $this->RequestProperties(array("properties" => array($Ident)));
    }

################## Datapoints

    /**
     * Interne SDK-Funktion. Empfängt Datenpakete vom KodiSplitter.
     *
     * @access public
     * @param string $JSONString Das Datenpaket als JSON formatierter String.
     * @return boolean true bei erfolgreicher Datenannahme, sonst false.
     */
    public function ReceiveData($JSONString)
    {
        $Data = json_decode($JSONString);
        if ($Data->DataID <> '{73249F91-710A-4D24-B1F1-A72F216C2BDC}')
            return false;

        $KodiData = new Kodi_RPC_Data();
        $KodiData->CreateFromGenericObject($Data);

        if ($KodiData->Typ <> Kodi_RPC_Data::$EventTyp)
            return false;

        $Event = $KodiData->GetEvent();
        if (is_array(static::$Namespace))
        {
            if (in_array($KodiData->Namespace, static::$Namespace))
            {
//                $this->SendDebug($KodiData->Method, $Event, 0);
                $this->Decode($KodiData->Method, $Event);
                return true;
            }
        }
        else
        {
            if ($KodiData->Namespace == static::$Namespace)
            {
  //              if ($KodiData->Method <> "Power")
    //                $this->SendDebug($KodiData->Method, $Event, 0);
                $this->Decode($KodiData->Method, $Event);
                return true;
            }
        }
        return false;
    }

    /**
     * Konvertiert $Data zu einem JSONString und versendet diese an den Splitter.
     *
     * @access protected
     * @param Kodi_RPC_Data $KodiData Zu versendende Daten.
     * @return Kodi_RPC_Data Objekt mit der Antwort. NULL im Fehlerfall.
     */
    protected function Send(Kodi_RPC_Data $KodiData)
    {
        $JSONData = $KodiData->ToJSONString('{0222A902-A6FA-4E94-94D3-D54AA4666321}');
        $anwser = $this->SendDataToParent($JSONData);
        if ($anwser === false)
            return NULL;
        $result = unserialize($anwser);
        $this->SendDebug('Kodi-Dev-Result', $result, 0);
        return $result;
    }

    /**
     * Konvertiert $Data zu einem JSONString und versendet diese an den Splitter zum Direktversand.
     *
     * @access protected
     * @param Kodi_RPC_Data $KodiData Zu versendende Daten.
     * @return Kodi_RPC_Data Objekt mit der Antwort. NULL im Fehlerfall.
     */
    protected function SendDirect(Kodi_RPC_Data $KodiData)
    {

        try
        {
            if (!$this->HasActiveParent())
                throw new Exception('Intance has no active parent.', E_USER_NOTICE);

            $instance = IPS_GetInstance($this->InstanceID);
            $Data = $KodiData->ToRawRPCJSONString();

            $URI = IPS_GetProperty($instance['ConnectionID'], "Host") . ":" . IPS_GetProperty($instance['ConnectionID'], "Webport") . "/jsonrpc";
            $header[] = "Accept: application/json";
            $header[] = "Cache-Control: max-age=0";
            $header[] = "Connection: close";
            $header[] = "Accept-Charset: UTF-8";
            $header[] = "Content-type: application/json;charset=\"UTF-8\"";
            $ch = curl_init('http://' . $URI);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $Data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1000);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 300000);
            $this->SendDebug("send", $Data, 0);
            $result = curl_exec($ch);
            curl_close($ch);

            if ($result === false)
            {
                IPS_SetInstanceStatus($instance['ConnectionID'],IS_EBASE+3);
                throw new Exception('Kodi unreachable', E_USER_NOTICE);
            }
            $this->SendDebug("receive", $result, 0);

            $ReplayKodiData = new Kodi_RPC_Data();
            $ReplayKodiData->CreateFromJSONString($result);
            $ret = $ReplayKodiData->GetResult();
            if (is_a($ret, 'KodiRPCException'))
            {
                throw $ret;
            }
            return $ret;
        }
        catch (KodiRPCException $ex)
        {
            trigger_error('Error (' . $ex->getCode() . '): ' . $ex->getMessage(), E_USER_NOTICE);
        }
        catch (Exception $ex)
        {
            trigger_error($ex->getMessage(), $ex->getCode());
        }
        return NULL;
    }

################## DUMMYS / WOARKAROUNDS - protected

    /**
     * Löscht eine Statusvariable, sofern vorhanden.
     *
     * @access private
     * @param integer $Ident Ident der Variable.
     */
    protected function UnregisterScript($Ident)
    {
        $sid = @IPS_GetObjectIDByIdent($Ident, $this->InstanceID);
        if ($sid === false)
            return;
        if (!IPS_ScriptExists($sid))
            return; //bail out
        IPS_DeleteScript($sid);
    }

    /**
     * Erstellt einen WebHook, wenn nicht schon vorhanden.
     *
     * @access private
     * @param string $WebHook URI des WebHook.
     * @param integer $TargetID Ziel-Script des WebHook.
     */
    protected function RegisterHook($WebHook, $TargetID)
    {
        $ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
        if (sizeof($ids) > 0)
        {
            $hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
            $found = false;
            foreach ($hooks as $index => $hook)
            {
                if ($hook['Hook'] == $WebHook)
                {
                    if ($hook['TargetID'] == $TargetID)
                        return;
                    $hooks[$index]['TargetID'] = $TargetID;
                    $found = true;
                }
            }
            if (!$found)
            {
                $hooks[] = Array("Hook" => $WebHook, "TargetID" => $TargetID);
            }
            IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
            IPS_ApplyChanges($ids[0]);
        }
    }

    /**
     * Löscht einen WebHook, wenn vorhanden.
     *
     * @access private
     * @param string $WebHook URI des WebHook.
     */
    protected function UnregisterHook($WebHook)
    {
        $ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
        if (sizeof($ids) > 0)
        {
            $hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
            $found = false;
            foreach ($hooks as $index => $hook)
            {
                if ($hook['Hook'] == $WebHook)
                {
                    unset($hooks[$index]);
                    $found = true;
                }
            }
            if ($found)
            {
                IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
                IPS_ApplyChanges($ids[0]);
            }
        }
    }

    /**
     * Formatiert eine DebugAusgabe und gibt sie an IPS weiter.
     *
     * @access protected
     * @param string $Message Nachrichten-Feld.
     * @param string|array|Kodi_RPC_Data $Data Daten-Feld.
     * @param integer $Format Ausgabe in Klartext(0) oder Hex(1)
     */
    protected function SendDebug($Message, $Data, $Format)
    {
        if (is_a($Data, 'Kodi_RPC_Data'))
        {
            parent::SendDebug($Message . " Method", $Data->Namespace . '.' . $Data->Method, 0);
            switch ($Data->Typ)
            {
                case Kodi_RPC_Data::$EventTyp:
                    $this->SendDebug($Message . " Event", $Data->GetEvent(), 0);
                    break;
                case Kodi_RPC_Data::$ResultTyp:
                    $this->SendDebug($Message . " Result", $Data->GetResult(), 0);
                    break;
                default:
                    $this->SendDebug($Message . " Params", $Data->Params, 0);
                    break;
            }
        }
        elseif (is_array($Data))
        {
            foreach ($Data as $Key => $DebugData)
            {
                $this->SendDebug($Message . ":" . $Key, $DebugData, 0);
            }
        }
        else if (is_object($Data))
        {
            foreach ($Data as $Key => $DebugData)
            {
                $this->SendDebug($Message . "." . $Key, $DebugData, 0);
            }
        }
        else
        {
            parent::SendDebug($Message, $Data, $Format);
        }
    }

    /**
     * Prüft den Parent auf vorhandensein und Status.
     * 
     * @return boolean True wenn Parent vorhanden und in Status 102, sonst false.
     */
    protected function HasActiveParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        if ($instance['ConnectionID'] > 0)
        {
            $parent = IPS_GetInstance($instance['ConnectionID']);
            if ($parent['InstanceStatus'] == 102)
                return true;
        }
        return false;
    }

    /**
     * Setzte eine IPS-Variable vom Typ boolean auf den Wert von $value
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param boolean $value Neuer Wert der Statusvariable.
     * @return boolean true wenn der neue Wert vom alten abweicht, sonst false.
     */
    protected function SetValueBoolean($Ident, $value)
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id === false)
            return false;
        if (GetValueBoolean($id) <> $value)
        {
            SetValueBoolean($id, $value);
            return true;
        }
        return false;
    }

    /**
     * Setzte eine IPS-Variable vom Typ integer auf den Wert von $value. Versteckt nicht benutzte Variablen anhand der Ident.
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param integer $value Neuer Wert der Statusvariable.
     * @return boolean true wenn der neue Wert vom alten abweicht, sonst false.
     */
    protected function SetValueInteger($Ident, $value)
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id === false)
            return false;
        if (GetValueInteger($id) <> $value)
        {
            if (!(($Ident[0] == "_") or ( $Ident == "speed") or ( $Ident == "repeat") or ( IPS_GetVariable($id)["VariableAction"] <> 0)))
            {
                if (($value <= 0) and ( !IPS_GetObject($id)["ObjectIsHidden"]))
                    IPS_SetHidden($id, true);
                if (($value > 0) and ( IPS_GetObject($id)["ObjectIsHidden"]))
                    IPS_SetHidden($id, false);
            }

            SetValueInteger($id, $value);
            return true;
        }
        return false;
    }

    /**
     * Setzte eine IPS-Variable vom Typ string auf den Wert von $value. Versteckt nicht benutzte Variablen anhand der Ident.
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param string $value Neuer Wert der Statusvariable.
     * @return boolean true wenn der neue Wert vom alten abweicht, sonst false.
     */
    protected function SetValueString($Ident, $value)
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id === false)
            return false;
        if (GetValueString($id) <> $value)
        {
            if ($Ident[0] <> "_")
            {
                if ((($value == "") or ( $value == "unknown")) and ( !IPS_GetObject($id)["ObjectIsHidden"]))
                    IPS_SetHidden($id, true);
                if ((($value <> "") and ( $value <> "unknown")) and ( IPS_GetObject($id)["ObjectIsHidden"]))
                    IPS_SetHidden($id, false);
            }
            SetValueString($id, $value);
            return true;
        }
        return false;
    }

    /**
     * Erstell und konfiguriert ein VariablenProfil für den Typ integer
     *
     * @access protected
     * @param string $Name Name des Profils.
     * @param string $Icon Name des Icon.
     * @param string $Prefix Prefix für die Darstellung.
     * @param string $Suffix Suffix für die Darstellung.
     * @param integer $MinValue Minimaler Wert.
     * @param integer $MaxValue Maximaler wert.
     * @param integer $StepSize Schrittweite
     */
    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
    {

        if (!IPS_VariableProfileExists($Name))
        {
            IPS_CreateVariableProfile($Name, 1);
        }
        else
        {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 1)
                throw new Exception("Variable profile type does not match for profile " . $Name, E_USER_WARNING);
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }

    /**
     * Erstell und konfiguriert ein VariablenProfil für den Typ integer mit Assoziationen
     *
     * @access protected
     * @param string $Name Name des Profils.
     * @param string $Icon Name des Icon.
     * @param string $Prefix Prefix für die Darstellung.
     * @param string $Suffix Suffix für die Darstellung.
     * @param array $Associations Assoziationen der Werte als Array.
     */
    protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        if (sizeof($Associations) === 0)
        {
            $MinValue = 0;
            $MaxValue = 0;
        }
        else
        {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations) - 1][0];
        }

        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

        foreach ($Associations as $Association)
        {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
    }

    /**
     * Löscht ein VariablenProfil.
     *
     * @access protected
     * @param string $Name Name des Profils.
     */
    protected function UnregisterProfile($Name)
    {
        if (IPS_VariableProfileExists($Name))
            IPS_DeleteVariableProfile($Name);
    }

    /**
     * Löscht ein Timer.
     *
     * @access protected
     * @param string $Name Name des Timer.
     */
    protected function UnregisterTimer($Name)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id > 0)
        {
            if (IPS_EventExists($id))
                IPS_DeleteEvent($id);
        }
    }

}

/**
 * Definiert eine KodiRPCException.
 */
class KodiRPCException extends Exception
{

    public function __construct($message, $code, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}

/**
 * Enthält einen Kodi-RPC Datensatz.
 * 
 * @method null ExecuteAddon
 * @method null GetAddons
 * @method null GetAddonDetails
 * @method null SetAddonEnabled
 * 
 * @method null SetVolume(array $Params (integer "volume" Neue Lautstärke)) Setzen der Lautstärke.
 * @method null SetMute(array $Params (boolean "mute" Neuer Wert der Stummschaltung)) Setzen der Stummschaltung.
 * @method null Quit(null) Beendet Kodi.
 * 
 * @method null Clean(null) Startet das bereinigen der Datenbank.
 * @method null Export(array $Params (array "options" (string "path" Ziel-Verzeichnis für den Export) (boolean "overwrite" Vorhandene Daten überschreiben.) (boolean "images" Bilder mit exportieren.)) Exportiert die Audio Datenbank.
 * @method null GetAlbumDetails(array $Params (string "albumid" AlbumID) (array "properties" Zu lesende Album-Eigenschaften) Liest die Eigenschaften eines Album aus.
 * @method null GetAlbums(null) Liest einen Teil der Eigenschaften aller Alben aus.
 * @method null GetArtistDetails (array $Params (string "artistid" ArtistID) (array "properties" Zu lesende Künstler-Eigenschaften) Liest die Eigenschaften eines Künstler aus.
 * @method null GetArtists(null) Liest einen Teil der Eigenschaften aller Künstler aus.
 * @method null GetGenres(null) Liest einen Teil der Eigenschaften aller Genres aus.
 * @method null GetRecentlyAddedAlbums(null) Liest die Eigenschaften der zuletzt hinzugefügten Alben aus.
 * @method null GetRecentlyAddedSongs(null) Liest die Eigenschaften der zuletzt hinzugefügten Songs aus.
 * @method null GetRecentlyPlayedAlbums(null) Liest die Eigenschaften der zuletzt abgespielten Alben aus.
 * @method null GetRecentlyPlayedSongs(null) Liest die Eigenschaften der zuletzt abgespielten Songs aus.
 * @method null GetSongDetails (array $Params (string "songid" SongID) (array "properties" Zu lesende Song-Eigenschaften) Liest die Eigenschaften eines Songs aus.
 * @method null GetSongs(null) Liest die Eigenschaften aller Songs aus.
 * @method null Scan(null) Startet das Scannen von PVR-Kanälen oder von Quellen für neue Einträge in der Datenbank.
 * @method null GetFavourites
 * @method null GetSources(array $Params (string "media"  enum["video", "music", "pictures", "files", "programs"])) Liest die Quellen.
 * @method null GetFileDetails(array $Params (string "file" Dateiname) (string "media"  enum["video", "music", "pictures", "files", "programs"]) (array "properties" Zu lesende Eigenschaften)) Liest die Quellen.
 * @method null GetDirectory(array $Params (string "directory" Verzeichnis welches gelesen werden soll.)) Liest ein Verzeichnis aus.
 * @method null SetFullscreen(array $Params (boolean "fullscreen"))
 * @method null ShowNotification($Data) ??? 
 * @method null ActivateWindow(array $Params (integer "window" ID des Fensters)) Aktiviert ein Fenster.
 * @method null Up(null) Tastendruck hoch.
 * @method null Down(null) Tastendruck runter.
 * @method null Left(null) Tastendruch links.
 * @method null Right(null) Tastendruck right.
 * @method null Back(null) Tastendruck zurück.
 * @method null ContextMenu(null) Tastendruck Context-Menü.
 * @method null Home(null) Tastendruck Home.
 * @method null Info(null) Tastendruck Info.
 * @method null Select(null) Tastendruck Select.
 * @method null ShowOSD(null) OSD Anzeigen.
 * @method null ShowCodec(null) Codec-Info anzeigen.
 * @method null ExecuteAction(array $Params (string "action" Die auszuführende Aktion)) Sendet eine Aktion.
 * @method null SendText(array $Params (string "text" Zu sender String) (boolean "done" True zum beenden der Eingabe)) Sendet einen Eingabetext.
 * 
 * @method null Record(array $Params (boolean "record" Starten/Stoppen) (string "channel" Kanal für die Aufnahme)) Startet/Beendet eine laufende Aufnahme.
 * 
 * @method null GetBroadcasts
 * @method null GetBroadcastDetails
 * @method null GetChannels
 * @method null GetChannelDetails
 * @method null GetChannelGroups
 * @method null GetChannelGroupDetails
 * @method null GetRecordings
 * @method null GetRecordingDetails
 * @method null GetTimers
 * @method null GetTimerDetails
 * 
 * @method null GetActivePlayers
 * @method null GetItem
 * @method null GetPlayers
 * @method null GetProperties
 * @method null GoTo
 * @method null Move
 * @method null Open
 * @method null PlayPause
 * @method null Rotate
 * @method null Seek
 * @method null SetAudioStream
 * @method null SetPartymode
 * @method null SetRepeat
 * @method null SetShuffle
 * @method null SetSpeed
 * @method null SetSubtitle
 * @method null Stop
 * @method null Zoom
 * 
 * @method null Add
 * @method null Clear
 * @method null GetItems
 * @method null GetPlaylists
 * @method null Insert
 * @method null Remove
 * @method null Swap
 * 
 * @method null Shutdown(null) Führt einen Shutdown auf Betriebssystemebene aus.
 * @method null Hibernate(null) Führt einen Hibernate auf Betriebssystemebene aus.
 * @method null Suspend(null) Führt einen Suspend auf Betriebssystemebene aus.
 * @method null Reboot(null) Führt einen Reboot auf Betriebssystemebene aus.
 * @method null EjectOpticalDrive(null) Öffnet das Optische Laufwerk.
 * @method null GetEpisodeDetails (array $Params (string "episodeid" EpisodeID) (array "properties" Zu lesende Episoden-Eigenschaften) Liest die Eigenschaften eine Episode aus.
 * @method null GetEpisodes(null) Liest die Eigenschaften aller Episoden aus.
 * @method null GetRecentlyAddedEpisodes(null) Liest die Eigenschaften der zuletzt hinzugefügten Episoden aus.
 * @method null GetMovieDetails (array $Params (string "movieid" MovieID) (array "properties" Zu lesende Films-Eigenschaften) Liest die Eigenschaften eines Film aus.
 * @method null GetMovies(null) Liest die Eigenschaften aller Filme aus.
 * @method null GetRecentlyAddedMovies(null) Liest die Eigenschaften der zuletzt hinzugefügten Filme aus.
 * @method null GetMovieSetDetails (array $Params (string "setid" SetID) (array "properties" Zu lesende Movie-Set-Eigenschaften) Liest die Eigenschaften eines Movie-Set aus.
 * @method null GetMovieSets (null) Liest die Eigenschaften alle Movie-Sets aus.
 * @method null GetMusicVideoDetails (array $Params (string "musicvideoid" MusicVideoID) (array "properties" Zu lesende Musikvideo-Eigenschaften) Liest die Eigenschaften eines Musikvideos aus.
 * @method null GetRecentlyAddedMusicVideos(null) Liest die Eigenschaften der zuletzt hinzugefügten Musikvideos aus.
 * @method null GetSeasons (array $Params (string "tvshowid" TVShowID) (array "properties" Zu lesende Season Eigenschaften) Liest die Eigenschaften einer Season aus.
 * @method null GetTVShowDetails (array $Params (string "tvshowid" TVShowID) (array "properties" Zu lesende TV-Serien Eigenschaften) Liest die Eigenschaften einer TV-Serie.
 * @method null GetTVShows (null) Liest die Eigenschaften alle TV-Serien.
 * @property-read integer $Id Id des RPC-Objektes
 * @property-read integer $Typ Typ des RPC-Objektes 
 * @property-read string $Namespace Namespace der RPC-Methode
 * @property-read string $Method RPC-Funktion
 */
class Kodi_RPC_Data extends stdClass
{

    static $EventTyp = 1;
//    static $ParamTyp = 2;
    static $ResultTyp = 2;

    /**
     * Typ der Daten
     * @access private
     * @var enum [ Kodi_RPC_Data::EventTyp, Kodi_RPC_Data::ParamTyp, Kodi_RPC_Data::ResultTyp]
     */
    private $Typ;

    /**
     * RPC-Namespace
     * @access private
     * @var string
     */
    private $Namespace;

    /**
     * Name der Methode
     * @access private
     * @var string
     */
    private $Method;

    /**
     * Enthält Fehlermeldungen der Methode
     * @access private
     * @var object
     */
    private $Error;

    /**
     * Parameter der Methode
     * @access private
     * @var object
     */
    private $Params;

    /**
     * Antwort der Methode
     * @access private
     * @var object
     */
    private $Result;

    /**
     * Enthält den Typ eines Event
     * @access private
     * @var object
     */
//    private $Event;

    /**
     * Id des RPC-Objektes
     * @access private
     * @var integer
     */
    private $Id;

    /**
     * 
     * @access public
     * @param string $name Propertyname
     * @return mixed Value of Name
     */
    public function __get($name)
    {
        return $this->{$name};
    }

    /**
     * Erstellt ein Kodi_RPC_Data Objekt.
     * 
     * @access public
     * @param string $Namespace [optional] Der RPC Namespace
     * @param string $Method [optional] RPC-Methode
     * @param object $Params [optional] Parameter der Methode
     * @param integer $Id [optional] Id des RPC-Objektes
     * @return Kodi_RPC_Data
     */
    public function __construct($Namespace = null, $Method = null, $Params = null, $Id = null)
    {
        if (!is_null($Namespace))
            $this->Namespace = $Namespace;
        if (!is_null($Method))
            $this->Method = $Method;
        if (is_array($Params))
            $this->Params = (object) $Params;
        if (is_object($Params))
            $this->Params = (object) $Params;
        if (is_null($Id))
        {
            $this->Id = round(explode(" ", microtime())[0] * 10000);
            $this->Typ = Kodi_RPC_Data::$ResultTyp;
        }
        else
        {
            if ($Id > 0)
            {
                $this->Id = $Id;
                $this->Typ = Kodi_RPC_Data::$ResultTyp;
            }
            else
                $this->Typ = Kodi_RPC_Data::$EventTyp;
        }
    }

    /**
     * Führt eine RPC-Methode aus.
     * 
     * 
     * @access public
     * @param string $name Auszuführende RPC-Methode
     * @param object|array $arguments Parameter der RPC-Methode.
     */
    public function __call($name, $arguments)
    {
        $this->Method = $name;
        if (count($arguments) == 0)
            $this->Params = new stdClass ();
        else
        {
            if (is_array($arguments[0]))
                $this->Params = (object) $arguments[0];
            if (is_object($arguments[0]))
                $this->Params = $arguments[0];
        }
        $this->Id = round(explode(" ", microtime())[0] * 10000);
//        $this->Typ = Kodi_RPC_Data::$ParamTyp;
    }

    /**
     * Gibt die RPC Antwort auf eine Anfrage zurück
     * 
     * 
     * @access public
     * @return array|object|mixed|KodiRPCException Enthält die Antwort des RPC-Server. Im Fehlerfall wird ein Objekt vom Typ KodiRPCException zurückgegeben.
     */
    public function GetResult()
    {
        if (!is_null($this->Error))
        {
            return $this->GetErrorObject();
        }
        if (!is_null($this->Result))
        {
            return $this->Result;
        }
        return array();
    }

    /**
     * Gibt die Daten eines RPC-Event zurück.
     * 
     * @access public
     * @return object|mixed  Enthält die Daten eines RPC-Event des RPC-Server.
     */
    public function GetEvent()
    {
        if (property_exists($this->Params, 'data'))
            return $this->Params->data;
        else
            return NULL;
    }

    /**
     * Gibt ein Objekt KodiRPCException mit den enthaltenen Fehlermeldung des RPC-Servers zurück.
     * 
     * @access private
     * @return KodiRPCException  Enthält die Daten der Fehlermeldung des RPC-Server.
     */
    private function GetErrorObject()
    {

        if (property_exists($this->Error, 'data'))
            if (property_exists($this->Error->data, 'stack'))
                if (property_exists($this->Error->data->stack, 'message'))
                    return new KodiRPCException((string) $this->Error->data->stack->message, (int) $this->Error->code);
                else
                    return new KodiRPCException((string) $this->Error->data->message . ':' . (string) $this->Error->data->stack->name, (int) $this->Error->code);
            else
                return new KodiRPCException($this->Error->data->message, (int) $this->Error->code);
        else
            return new KodiRPCException((string) $this->Error->message, (int) $this->Error->code);
    }

    /**
     * Schreibt die Daten aus $Data in das Kodi_RPC_Data-Objekt.
     * 
     * @access public
     * @param object $Data Muss ein Objekt sein, welche vom Kodi-Splitter erzeugt wurde.
     */
    public function CreateFromGenericObject($Data)
    {
        if (property_exists($Data, 'Error'))
            $this->Error = $Data->Error;
        if (property_exists($Data, 'Result'))
            $this->Result = $this->DecodeUTF8($Data->Result);
        if (property_exists($Data, 'Namespace'))
            $this->Namespace = $Data->Namespace;
        if (property_exists($Data, 'Method'))
            $this->Method = $Data->Method;
        if (property_exists($Data, 'Params'))
            $this->Params = $this->DecodeUTF8($Data->Params);
        if (property_exists($Data, 'Typ'))
            $this->Typ = $Data->Typ;
//            $this->Typ = Kodi_RPC_Data::$ParamTyp;
        /*        if (property_exists($Data, 'Event'))
          {
          $this->Event = $this->DecodeUTF8($Data->Event);
          $this->Typ = Kodi_RPC_Data::$EventTyp;
          } */
        if (property_exists($Data, 'Id'))
        {
            $this->Id = $Data->Id;
            $this->Typ = Kodi_RPC_Data::$ResultTyp;
        } else
          $this->Typ = Kodi_RPC_Data::$EventTyp;
            
    }

    /**
     * Erzeugt einen, mit der GUDI versehenen, JSON-kodierten String.
     * 
     * @access public
     * @param string $GUID Die Interface-GUID welche mit in den JSON-String integriert werden soll.
     * @return string JSON-kodierter String für IPS-Dateninterface.
     */
    public function ToJSONString($GUID)
    {
        $SendData = new stdClass();
        $SendData->DataID = $GUID;
        if (!is_null($this->Id))
            $SendData->Id = $this->Id;
        if (!is_null($this->Namespace))
            $SendData->Namespace = $this->Namespace;
        if (!is_null($this->Method))
            $SendData->Method = $this->Method;
        if (!is_null($this->Params))
            $SendData->Params = $this->EncodeUTF8($this->Params);
        if (!is_null($this->Error))
            $SendData->Error = $this->Error;
        if (!is_null($this->Result))
            $SendData->Result = $this->EncodeUTF8($this->Result);
        if (!is_null($this->Typ))
            $SendData->Typ = $this->Typ;

        /*        if (!is_null($this->Event))
          $SendData->Event = $this->EncodeUTF8($this->Event); */
        return json_encode($SendData);
    }

    /**
     * Schreibt die Daten aus $Data in das Kodi_RPC_Data-Objekt.
     * 
     * @access public
     * @param string $Data Ein JSON-kodierter RPC-String vom RPC-Server.
     */
    public function CreateFromJSONString($Data)
    {
        $Json = json_decode($Data);
        if (property_exists($Json, 'error'))
            $this->Error = $Json->error;
        if (property_exists($Json, 'method'))
        {
            $part = explode('.', $Json->method);
            $this->Namespace = $part[0];
            $this->Method = $part[1];
        }
        if (property_exists($Json, 'params'))
            $this->Params = $this->DecodeUTF8($Json->params);
        if (property_exists($Json, 'result'))
        {
            $this->Result = $this->DecodeUTF8($Json->result);
            $this->Typ = Kodi_RPC_Data::$ResultTyp;
        }
        if (property_exists($Json, 'id'))
            $this->Id = $Json->id;
        else
        {
            $this->Id = null;
            $this->Typ = Kodi_RPC_Data::$EventTyp;
        }
    }

    /**
     * Erzeugt einen, mit der GUDI versehenen, JSON-kodierten String zum versand an den RPC-Server.
     * 
     * @access public
     * @param string $GUID Die Interface-GUID welche mit in den JSON-String integriert werden soll.
     * @return string JSON-kodierter String für IPS-Dateninterface.
     */
    public function ToRPCJSONString($GUID)
    {
        $RPC = new stdClass();
        $RPC->jsonrpc = "2.0";
        $RPC->method = $this->Namespace . '.' . $this->Method;
        if (!is_null($this->Params))
            $RPC->params = $this->Params;
        $RPC->id = $this->Id;
        $SendData = new stdClass;
        $SendData->DataID = $GUID;
        $SendData->Buffer = utf8_encode(json_encode($RPC));
        return json_encode($SendData);
    }

    /**
     * Erzeugt einen, JSON-kodierten String zum versand an den RPC-Server.
     * 
     * @access public
     * @return string JSON-kodierter String.
     */
    public function ToRawRPCJSONString()
    {
        $RPC = new stdClass();
        $RPC->jsonrpc = "2.0";
        $RPC->method = $this->Namespace . '.' . $this->Method;
        if (!is_null($this->Params))
            $RPC->params = $this->Params;
        $RPC->id = $this->Id;
        return json_encode($RPC);
    }

    /**
     * Führt eine UTF8-Dekodierung für einen String oder ein Objekt durch (rekursiv)
     * 
     * @access private
     * @param string|object $item Zu dekodierene Daten.
     * @return string|object Dekodierte Daten.
     */
    private function DecodeUTF8($item)
    {
        if (is_string($item))
            $item = utf8_decode($item);
        else if (is_object($item))
        {
            //        $newObj = new stdClass();
            foreach ($item as $property => $value)
            {
                $item->{$property} = $this->DecodeUTF8($value);
            }
        }
        return $item;
    }

    /**
     * Führt eine UTF8-Enkodierung für einen String oder ein Objekt durch (rekursiv)
     * 
     * @access private
     * @param string|object $item Zu Enkodierene Daten.
     * @return string|object Enkodierte Daten.
     */
    private function EncodeUTF8($item)
    {
        if (is_string($item))
            $item = utf8_encode($item);
        else if (is_object($item))
        {
            //        $newObj = new stdClass();
            foreach ($item as $property => $value)
            {
                $item->{$property} = $this->EncodeUTF8($value);
            }
        }
        return $item;
    }

}

/** @} */
?>