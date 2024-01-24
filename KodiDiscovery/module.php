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
class KodiDiscovery extends IPSModuleStrict
{
    use \KodiBase\DebugHelper;

    /**
     * The maximum number of seconds that will be allowed for the discovery request.
     */
    public const WS_DISCOVERY_TIMEOUT = 2;

    /**
     * The multicast address to use in the socket for the discovery request.
     */
    public const WS_DISCOVERY_MULTICAST_ADDRESS = '239.255.255.250';
    public const WS_DISCOVERY_MULTICAST_ADDRESSV6 = '[ff02::c]';

    /**
     * The port that will be used in the socket for the discovery request.
     */
    public const WS_DISCOVERY_MULTICAST_PORT = 1900;

    /**
     * Interne Funktion des SDK.
     */
    public function Create(): void
    {
        parent::Create();
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
    }

    /**
     * Interne Funktion des SDK.
     */
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        if ($this->GetStatus() == IS_CREATING) {
            return json_encode($Form);
        }
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
        foreach ($Devices as $Device) {
            $AddValue = [
                'host'       => $Device['Hosts'][array_key_first($Device['Hosts'])],
                'devicename' => $Device['devicename'],
                'name'       => $Device['devicename'],
                'version'    => $Device['version'],
                'instanceID' => 0
            ];

            foreach ($Device['Hosts'] as $Host) {
                $InstanceIDConfigurator = array_search($Host, $IPSDevices);
                if ($InstanceIDConfigurator !== false) {
                    $AddValue['name'] = IPS_GetLocation($InstanceIDConfigurator);
                    $AddValue['instanceID'] = $InstanceIDConfigurator;
                    $AddValue['host'] = $Host;
                    unset($IPSDevices[$InstanceIDConfigurator]);
                }
                $AddValue['create'][$Host] = [
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
                            'Host' => $Host
                        ]
                    ]
                ];
            }
            $Values[] = $AddValue;
        }

        foreach ($IPSDevices as $InstanceID => $Host) {
            $Values[] = [
                'host'       => $Host,
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
        $Interfaces = $this->getIPAdresses();
        $DevicesData = [];
        $Kodi = [];
        $Index = 0;
        foreach ($Interfaces['ipv6'] as $IP => $Interface) {
            $socket = socket_create(AF_INET6, SOCK_DGRAM, SOL_UDP);
            if ($socket) {
                socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 100000]);
                socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
                socket_set_option($socket, IPPROTO_IPV6, IPV6_MULTICAST_HOPS, 4);
                socket_set_option($socket, IPPROTO_IPV6, IPV6_MULTICAST_IF, $Interface);
                socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
                if (@socket_bind($socket, $IP, self::WS_DISCOVERY_MULTICAST_PORT + 1) == false) {
                    continue;
                }
                $discoveryTimeout = time() + self::WS_DISCOVERY_TIMEOUT;
                $message = [
                    'M-SEARCH * HTTP/1.1',
                    'ST: upnp:rootdevice',
                    'MAN: "ssdp:discover"',
                    'MX: 5',
                    'HOST: ' . self::WS_DISCOVERY_MULTICAST_ADDRESSV6 . ':1900',
                    'Content-Length: 0'
                ];
                $SendData = implode("\r\n", $message) . "\r\n\r\n";
                $this->SendDebug('Start Discovery(' . $Interface . ')', $IP, 0);
                $this->SendDebug('Search', $SendData, 0);
                if (@socket_sendto($socket, $SendData, strlen($SendData), 0, self::WS_DISCOVERY_MULTICAST_ADDRESSV6, self::WS_DISCOVERY_MULTICAST_PORT) === false) {
                    $this->SendDebug('Error on send discovery message', $IP, 0);
                    @socket_close($socket);
                    continue;
                }
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
                    $USN = explode(':', $Data['USN'])[1];
                    $IPAddress = parse_url($Data['LOCATION'], PHP_URL_HOST);
                    $this->AddDiscoveryEntry($DevicesData, $USN, $Data['LOCATION'], $IPAddress, 20 + $Index);
                    $Host = gethostbyaddr(substr($IPAddress, 1, -1));
                    if ($Host != substr($IPAddress, 1, -1)) {
                        $this->AddDiscoveryEntry($DevicesData, $USN, str_replace($IPAddress, $Host, $Data['LOCATION']), $Host, 40 + $Index);
                    }
                    $this->SendDebug('Receive (' . explode(':', $Data['USN'])[1] . ')', ['SERVER' => $Data['SERVER'], 'HOST' => $Host], 0);
                    $Index++;
                } while (time() < $discoveryTimeout);
                socket_close($socket);
            } else {
                $this->SendDebug('Error on create Socket ipv6', $IP, 0);
            }
        }
        $Index = 0;
        foreach ($Interfaces['ipv4'] as $IP => $Interface) {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($socket) {
                socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 100000]);
                socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
                socket_set_option($socket, IPPROTO_IP, IP_MULTICAST_TTL, 4);
                socket_set_option($socket, IPPROTO_IP, IP_MULTICAST_IF, $Interface);
                socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
                if (@socket_bind($socket, $IP, self::WS_DISCOVERY_MULTICAST_PORT + 1) == false) {
                    continue;
                }
                $discoveryTimeout = time() + self::WS_DISCOVERY_TIMEOUT;
                $message = [
                    'M-SEARCH * HTTP/1.1',
                    'ST: upnp:rootdevice',
                    'MAN: "ssdp:discover"',
                    'MX: 5',
                    'HOST: ' . self::WS_DISCOVERY_MULTICAST_ADDRESS . ':1900',
                    'Content-Length: 0'
                ];
                $SendData = implode("\r\n", $message) . "\r\n\r\n";
                $this->SendDebug('Start Discovery(' . $Interface . ')', $IP, 0);
                $this->SendDebug('Search', $SendData, 0);
                if (@socket_sendto($socket, $SendData, strlen($SendData), 0, self::WS_DISCOVERY_MULTICAST_ADDRESS, self::WS_DISCOVERY_MULTICAST_PORT) === false) {
                    $this->SendDebug('Error on send discovery message', $IP, 0);
                    @socket_close($socket);
                    continue;
                }
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
                    $USN = explode(':', $Data['USN'])[1];
                    $IPAddress = parse_url($Data['LOCATION'], PHP_URL_HOST);
                    $this->AddDiscoveryEntry($DevicesData, $USN, $Data['LOCATION'], $IPAddress, 60 + $Index);
                    $Host = gethostbyaddr($IPAddress);
                    if ($Host != $IPAddress) {
                        $this->AddDiscoveryEntry($DevicesData, $USN, str_replace($IPAddress, $Host, $Data['LOCATION']), $Host, 40 + $Index);
                    }
                    $this->SendDebug('Receive (' . explode(':', $Data['USN'])[1] . ')', ['SERVER' => $Data['SERVER'], 'HOST' => $Host], 0);
                    $Index++;
                } while (time() < $discoveryTimeout);
                socket_close($socket);
            } else {
                $this->SendDebug('Error on create Socket ipv4', $IP, 0);
            }
        }

        $this->SendDebug('ParseLocations', $DevicesData, 0);

        foreach ($DevicesData as $USN => $Data) {
            ksort($Data['Location']);
            ksort($Data['Hosts']);
            $XMLData = '';
            foreach ($Data['Location'] as $Location) {
                $XMLData = @Sys_GetURLContent($Location);
                $this->SendDebug('XML', $XMLData, 0);
                if ($XMLData !== false) {
                    break;
                }
            }
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
            $Kodi[$USN] = [
                'devicename'  => (string) $Xml->device->friendlyName,
                'version'     => explode(' ', (string) $Xml->device->modelNumber)[0],
                'WebPort'     => $WebPort,
                'RPCPort'     => 9090,
                'Hosts'       => $Data['Hosts']
            ];
        }

        $this->SendDebug('Found', $Kodi, 0);
        return $Kodi;
    }

    private function AddDiscoveryEntry(&$DevicesData, $USN, $Location, $Host, $Index)
    {
        $DevicesData[$USN]['Hosts'][$Index] = strtolower($Host);
        $DevicesData[$USN]['Location'][$Index] = $Location;
    }

    private function getIPAdresses(): array
    {
        $Interfaces = SYS_GetNetworkInfo();
        $InterfaceDescriptions = array_column($Interfaces, 'Description', 'InterfaceIndex');
        $Networks = net_get_interfaces();
        $Addresses = [];
        $Addresses['ipv6'] = [];
        $Addresses['ipv4'] = [];
        foreach ($Networks as $InterfaceDescription => $Interface) {
            if (!$Interface['up']) {
                continue;
            }
            if (array_key_exists('description', $Interface)) {
                $InterfaceDescription = array_search($Interface['description'], $InterfaceDescriptions);
            }
            foreach ($Interface['unicast'] as $Address) {
                switch ($Address['family']) {
                    case AF_INET6:
                        if ($Address['address'] == '::1') {
                            continue 2;
                        }
                        $Address['address'] = '[' . $Address['address'] . ']';
                        $family = 'ipv6';
                        break;
                    case AF_INET:
                        if ($Address['address'] == '127.0.0.1') {
                            continue 2;
                        }
                        $family = 'ipv4';
                        break;
                    default:
                        continue 2;
                }
                $Addresses[$family][$Address['address']] = $InterfaceDescription;
            }
        }
        return $Addresses;
    }
}

/* @} */
