<?php

declare(strict_types = 1);
/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.0
 *
 */
require_once __DIR__ . '/../libs/BufferHelper.php';  // diverse Klassen
require_once __DIR__ . '/../libs/DebugHelper.php';  // diverse Klassen

/**
 * KodiDiscovery Klasse implementiert
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * @version       2.0
 *
 * @example <b>Ohne</b>
 * @property array $Devices
 */
class KodiDiscovery extends ipsmodule
{
    use DebugHelper,
        BufferHelper;
    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->Devices = [];
        $this->RegisterTimer('Discovery', 0, 'KODI_Discover($_IPS[\'TARGET\']);');
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges()
    {
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        $this->Devices = $this->DiscoverDevices();
        $this->SetTimerInterval('Discovery', 300000);
    }

    /**
     * Interne Funktion des SDK.
     * Verarbeitet alle Nachrichten auf die wir uns registriert haben.
     *
     * @param int       $TimeStamp
     * @param int       $SenderID
     * @param int       $Message
     * @param array|int $Data
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->Devices = $this->DiscoverDevices();
                break;
        }
    }

    private function GetIPSInstances(): array
    {
        $InstanceIDList = IPS_GetInstanceListByModuleID('{7B4F8B62-7AB4-4877-AD60-F3B294DDB43E}');
        $Devices = [];
        foreach ($InstanceIDList as $InstanceID) {
            $Splitter = IPS_GetInstance($InstanceID)['ConnectionID'];
            if ($Splitter > 0) {
                $IO = IPS_GetInstance($Splitter)['ConnectionID'];
                if ($IO > 0) {
                    $Devices[$InstanceID] = IPS_GetProperty($IO, 'Host');
                }
            }
        }
        $this->SendDebug('IPS Devices', $Devices, 0);
        return $Devices;
    }

    /**
     * Interne Funktion des SDK.
     */
    public function GetConfigurationForm()
    {
        $Devices = $this->DiscoverDevices();
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $IPSDevices = $this->GetIPSInstances();

        $Values = [];

        foreach ($Devices as $IPAddress => $Device) {
            $InstanceID = array_search($IPAddress, $IPSDevices);
            $AddValue = [
                'IPAddress'  => $IPAddress,
                'devicename' => $Device['devicename'],
                'name'       => $Device['devicename'],
                'version'      => $Device['version'],
                'instanceID' => 0
            ];
            if ($InstanceID !== false) {
                unset($IPSDevices[$InstanceID]);
                $AddValue['name'] = IPS_GetLocation($InstanceID);
                $AddValue['instanceID'] = $InstanceID;
            }
            $AddValue['create'] = [
                [
                    'moduleID'      => '{7B4F8B62-7AB4-4877-AD60-F3B294DDB43E}',
                    'configuration' => new stdClass()
                ],
                [
                    'moduleID'      => '{D2F106B5-4473-4C19-A48F-812E8BAA316C}',
                    'configuration' => [
                        'Webport' => $Device['WebPort']
                    ]
                ],
                [
                    'moduleID'      => '{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}',
                    'configuration' => [
                        'Host' => $IPAddress
                    ]
                /* [
                  'Host' => $IPAddress,
                  'Port' => $Device['port'],
                  'Open' => true
                  ] */
                ]
            ];
            $Values[] = $AddValue;
        }

        foreach ($IPSDevices as $InstanceID => $IPAddress) {
            $Values[] = [
                'IPAddress'  => $IPAddress,
                'version'      => '',
                'devicename' => '',
                'name'       => IPS_GetLocation($InstanceID),
                'instanceID' => $InstanceID
            ];
        }

        /* if (count($Values) > 0) {
          foreach ($Values as $key => $row) {
          $SortDevice[$key] = $row['device'];
          $SortType[$key] = $row['type'];
          }
          array_multisort($SortDevice, SORT_ASC, $SortType, SORT_ASC, $Values);
          } */

        $Form['actions'][1]['values'] = $Values;
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    private function parseHeader(string $Data): array
    {
        $Lines = explode("\r\n", $Data);
        array_shift($Lines);
        array_pop($Lines);
        $Header = [];
        foreach ($Lines as $Line) {
            $line_array = explode(':', $Line);
            $Header[strtoupper(trim(array_shift($line_array)))] = trim(implode(':', $line_array));
        }
        return $Header;
    }

    private function DiscoverDevices(): array
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$socket) {
            return [];
        }
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 2, "usec" => 100000));
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, '0.0.0.0', 0);
        $message = [
            'M-SEARCH * HTTP/1.1',
            'ST: upnp:rootdevice',
            'MAN: "ssdp:discover"',
            'MX: 5',
            'HOST: 239.255.255.250:1900',
            'Content-Length: 0'
        ];
        $SendData = implode("\r\n", $message) . "\r\n\r\n";
        $this->SendDebug('Serach', $SendData, 0);
        if (@socket_sendto($socket, $SendData, strlen($SendData), 0, '239.255.255.250', 1900) === false) {
            return [];
        }
        usleep(100000);
        $i = 50;
        $buf = '';
        $IPAddress = '';
        $Port = 0;
        $DeviceData = [];
        while ($i) {
            $ret = @socket_recvfrom($socket, $buf, 2048, 0, $IPAddress, $Port);
            if ($ret === false) {
                break;
            }
            if ($ret === 0) {
                $i--;
                continue;
            }
            $Data = $this->parseHeader($buf);
            if (!array_key_exists('SERVER', $Data)) {
                continue;
            }
            if (strpos($Data['SERVER'], 'Kodi') === false) {
                continue;
            }

            $this->SendDebug($IPAddress, $Data, 0);
            $DeviceData[$IPAddress] = $Data['LOCATION'];
        }
        socket_close($socket);
        $Kodi = [];
        foreach ($DeviceData as $IPAddress => $Url) {
            $XMLData = @Sys_GetURLContent($Url);
            $this->SendDebug('XML', $XMLData, 0);
            if ($XMLData === false) {
                continue;
            }
            $Xml = new SimpleXMLElement($XMLData);
            if ((string) $Xml->device->modelName = !'Kodi') {
                continue;
            }
            $Kodi[$IPAddress] = [
                'devicename' => (string) $Xml->device->friendlyName,
                'version'      => explode(' ', (string) $Xml->device->modelNumber)[0],
                'WebPort'    => (int) explode(':', (string) $Xml->device->presentationURL)[2],
                'RPCPort'    => 9090
            ];
        }

        return $Kodi;
    }

    public function Discover()
    {
        $this->LogMessage($this->Translate('Background discovery of Kodi devices'), KL_NOTIFY);
        $this->Devices = $this->DiscoverDevices();
        // Alt neu vergleich fehlt, sowie die Events an IPS senden wenn neues Gerät im Netz gefunden wurde.
    }
}

if (count(IPS_GetInstanceListByModuleID("{D297668E-5D80-4A77-9F37-A33AA35F4F4B}")) == 0) {
    $id = IPS_CreateInstance("{D297668E-5D80-4A77-9F37-A33AA35F4F4B}");
    IPS_SetName($id, "Kodi Discovery");
}


/* @} */
