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
require_once __DIR__ . '/../libs/DebugHelper.php';  // diverse Klassen

/**
 * KodiDiscovery Klasse implementiert
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 */
class KodiDiscovery extends ipsmodule
{
    use \KodiBase\DebugHelper;

    /**
     * The maximum number of seconds that will be allowed for the discovery request.
     */
    const WS_DISCOVERY_TIMEOUT = 3;

    /**
     * The multicast address to use in the socket for the discovery request.
     */
    const WS_DISCOVERY_MULTICAST_ADDRESS = '239.255.255.250';

    /**
     * The port that will be used in the socket for the discovery request.
     */
    const WS_DISCOVERY_MULTICAST_PORT = 1900;

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    /**
     * Interne Funktion des SDK.
     */
    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        if (IPS_GetOption('NATSupport') && strpos(IPS_GetKernelPlatform(), 'Docker')) {
            // not supported. Docker cannot forward Multicast :(
            $Form['actions'][2]['popup']['items'][1]['caption'] = $this->Translate("The combination of Docker and NAT is not supported because Docker does not support multicast.\r\nPlease run the container in the host network.\r\nOr create and configure the required Kodi Configurator instance manually.");
            $Form['actions'][2]['visible'] = true;
            $this->SendDebug('FORM', json_encode($Form), 0);
            $this->SendDebug('FORM', json_last_error_msg(), 0);
            return json_encode($Form);
        }

        $Devices = $this->DiscoverDevices();
        $IPSDevices = $this->GetIPSInstances();
        $Values = [];
        foreach ($Devices as $IPAddress => $Device) {
            $AddValue = [
                'IPAddress'  => $Device['Host'],
                'devicename' => $Device['devicename'],
                'name'       => $Device['devicename'],
                'version'    => $Device['version'],
                'instanceID' => 0
            ];
            $InstanceID = array_search($IPAddress, $IPSDevices);
            if ($InstanceID === false) {
                $InstanceID = array_search(strtolower($Device['Host']), $IPSDevices);
            }
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
                        'Host' => $Device['Host']
                    ]
                ]
            ];
            $Values[] = $AddValue;
        }

        foreach ($IPSDevices as $InstanceID => $IPAddress) {
            $Values[] = [
                'IPAddress'  => $IPAddress,
                'version'    => '',
                'devicename' => '',
                'name'       => IPS_GetLocation($InstanceID),
                'instanceID' => $InstanceID
            ];
        }
        $Form['actions'][1]['values'] = $Values;
        if (count($Devices) == 0) {
            $Form['actions'][2]['visible'] = true;
        }
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
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
                    $Devices[$InstanceID] = strtolower(IPS_GetProperty($IO, 'Host'));
                }
            }
        }
        $this->SendDebug('IPS Devices', $Devices, 0);
        return $Devices;
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
        $DeviceData = [];
        $Kodi = [];
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$socket) {
            return $Kodi;
        }

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 100000]);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($socket, IPPROTO_IP, IP_MULTICAST_TTL, 4);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
        socket_bind($socket, '0', 1901);
        $discoveryTimeout = time() + self::WS_DISCOVERY_TIMEOUT;
        $message = [
            'M-SEARCH * HTTP/1.1',
            'ST: upnp:rootdevice',
            'MAN: "ssdp:discover"',
            'MX: 5',
            'HOST: 239.255.255.250:1900',
            'Content-Length: 0'
        ];
        $SendData = implode("\r\n", $message) . "\r\n\r\n";
        $this->SendDebug('Search', $SendData, 0);
        if (@socket_sendto($socket, $SendData, strlen($SendData), 0, self::WS_DISCOVERY_MULTICAST_ADDRESS, self::WS_DISCOVERY_MULTICAST_PORT) === false) {
            return $Kodi;
        }
        usleep(100000);
        $response = '';
        $IPAddress = '';
        $Port = 0;
        do {
            if (0 == @socket_recvfrom($socket, $response, 2048, 0, $IPAddress, $Port)) {
                continue;
            }
            $this->SendDebug('Receive (' . $IPAddress . ')', $response, 0);
            $Data = $this->parseHeader($response);
            if (!array_key_exists('SERVER', $Data)) {
                continue;
            }
            if (strpos($Data['SERVER'], 'Kodi') === false) {
                continue;
            }

            $this->SendDebug($IPAddress, $Data, 0);
            $DeviceData[$IPAddress] = $Data['LOCATION'];
        } while (time() < $discoveryTimeout);
        socket_close($socket);

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
            $presentationURL = explode(':', (string) $Xml->device->presentationURL);
            if (count($presentationURL) < 3) {
                $WebPort = 80;
            } else {
                $WebPort = (int) $presentationURL[2];
            }
            $Kodi[$IPAddress] = [
                'devicename' => (string) $Xml->device->friendlyName,
                'version'    => explode(' ', (string) $Xml->device->modelNumber)[0],
                'WebPort'    => $WebPort,
                'RPCPort'    => 9090,
                'Host'       => strtolower(gethostbyaddr($IPAddress))
            ];
        }
        $this->SendDebug('Found', $Kodi, 0);
        return $Kodi;
    }
}

/* @} */
