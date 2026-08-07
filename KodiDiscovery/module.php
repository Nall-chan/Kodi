<?php

declare(strict_types=1);

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

    public const GUID_mDNS = '{780B2D48-916C-4D59-AD35-5A429B2355A5}';
    public const GUID_Configurator = '{7B4F8B62-7AB4-4877-AD60-F3B294DDB43E}';
    public const GUID_Splitter = '{D2F106B5-4473-4C19-A48F-812E8BAA316C}';
    public const GUID_IO = '{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}';
    public const Username = 'Username';
    public const Password = 'Password';

    /**
     * Create
     *
     * @return void
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterAttributeString(self::Username, '');
        $this->RegisterAttributeString(self::Password, '');
    }

    /**
     * ApplyChanges
     *
     * @return void
     */
    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
    }

    /**
     * RequestAction
     *
     * @param  string $Ident
     * @param  mixed $Value
     * @return void
     */
    public function RequestAction(string $Ident, mixed $Value): void
    {
        if ($Ident == 'Save') {
            $Data = explode(':', $Value);
            $this->WriteAttributeString(self::Username, urldecode($Data[0]));
            $this->WriteAttributeString(self::Password, urldecode($Data[1]));
            $this->ReloadForm();
            return;
        }
    }

    /**
     * GetConfigurationForm
     *
     * @return string
     */
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Form['actions'][0]['items'][0]['items'][0]['value'] = $this->ReadAttributeString(self::Username);
        $Form['actions'][0]['items'][0]['items'][1]['value'] = $this->ReadAttributeString(self::Password);
        if ($this->GetStatus() == IS_CREATING) {
            return json_encode($Form);
        }
        if (IPS_GetOption('NATSupport') && str_contains(IPS_GetKernelPlatform(), 'Docker')) {
            // not supported. Docker cannot forward Multicast :(
            $Form['actions'][2]['popup']['items'][1]['caption'] = $this->Translate("The combination of Docker and NAT is not supported because Docker does not support multicast.\r\nPlease run the container in the host network.\r\nOr create and configure the required Kodi Configurator instance manually.");
            $Form['actions'][2]['visible'] = true;
            $this->SendDebug('FORM', json_encode($Form), 0);
            $this->SendDebug('FORM', json_last_error_msg(), 0);
            return json_encode($Form);
        }
        $Devices = $this->DiscoverKodiDevices();
        $IPSDevices = $this->GetIPSInstances();
        $Username = $this->ReadAttributeString(self::Username);
        $Password = $this->ReadAttributeString(self::Password);
        $Values = [];
        foreach ($Devices as $Device) {
            $AddValue = [
                'host'       => $Device['Hosts'][array_key_first($Device['Hosts'])],
                'devicename' => $Device['devicename'],
                'name'       => $Device['devicename'],
                'instanceID' => 0
            ];
            foreach ($Device['Hosts'] as $Host) {
                $AddValue['create'][$Host] = [
                    [
                        'moduleID'      => self::GUID_Configurator,
                        'configuration' => new stdClass()
                    ],
                    [
                        'moduleID'      => self::GUID_Splitter,
                        'configuration' => [
                            'Open'      => true,
                            'Port'      => $Device['RPCPort'],
                            'Webport'   => $Device['WebPort'],
                            'BasisAuth' => ($Username != '' && $Password != ''),
                            'Username'  => $Username,
                            'Password'  => $Password
                        ]
                    ],
                    [
                        'moduleID'      => self::GUID_IO,
                        'configuration' => [
                            'Host'      => $Host
                        ]
                    ]
                ];
                $InstanceIDConfigurator = array_search($Host, $IPSDevices);
                if ($InstanceIDConfigurator !== false) {
                    $AddValue['host'] = $Host;
                    $AddValue['name'] = IPS_GetLocation($InstanceIDConfigurator);
                    $AddValue['instanceID'] = $InstanceIDConfigurator;
                    $AddValue['create'] = $AddValue['create'][$Host]; //bei schon gefunden, nur einen AddValue im create zurückgeben
                    unset($IPSDevices[$InstanceIDConfigurator]);
                    $Values[] = $AddValue;
                    continue 2;
                }
            }
            $Values[] = $AddValue;
        }

        foreach ($IPSDevices as $InstanceID => $Host) {
            $Values[] = [
                'host'       => $Host,
                'devicename' => '',
                'name'       => IPS_GetLocation($InstanceID),
                'instanceID' => $InstanceID
            ];
        }
        $Form['actions'][1]['values'] = $Values;
        if (count($Devices) == 0) {
            $Form['actions'][2]['visible'] = true;
            $Form['actions'][2]['popup']['items'][1]['visible'] = false;
        }
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    private function GetIPSInstances(): array
    {
        $InstanceIDList = IPS_GetInstanceListByModuleID(self::GUID_Configurator);
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

    private function DiscoverKodiDevices(): array
    {
        $Devices = [];
        $mDNSInstanceIDs = IPS_GetInstanceListByModuleID(self::GUID_mDNS);
        if (count($mDNSInstanceIDs) == 0) {
            $this->SendDebug('mDNS', 'No mDNS instance found', 0);
            return $Devices;
        }
        $resultServiceTypes = ZC_QueryServiceType($mDNSInstanceIDs[0], '_xbmc-jsonrpc._tcp', 'local.');
        if (!$resultServiceTypes) {
            return $Devices;
        }
        $this->SendDebug('mDNS resultServiceTypes', $resultServiceTypes, 0);
        foreach ($resultServiceTypes as $device) {
            $this->SendDebug('mDNS QueryService', $device['Name'] . ' ' . $device['Type'] . ' ' . $device['Domain'] . '.', 0);
            $deviceInfo = ZC_QueryService($mDNSInstanceIDs[0], $device['Name'], '_xbmc-jsonrpc._tcp', 'local.');
            $deviceWebPort = ZC_QueryService($mDNSInstanceIDs[0], $device['Name'], '_xbmc-jsonrpc-h._tcp', 'local.');
            $this->SendDebug('mDNS QueryService Result', $deviceInfo, 0);
            if (empty($deviceInfo) || empty($deviceWebPort)) {
                continue;
            }
            foreach ($deviceInfo[0]['TXTRecords'] as $Line) {
                $Data = explode('=', $Line);
                $Typ = strtoupper(array_shift($Data));
                if ($Typ == 'UUID') {
                    $UUID = implode('=', $Data);
                }
            }
            $RPCPort = $deviceInfo[0]['Port'];
            $WebPort = $deviceWebPort[0]['Port'];
            $Hosts = [];
            if (empty($deviceInfo[0]['IPv4'])) { //IPv4 und IPv6 sind vertauscht
                $IPv4 = $deviceInfo[0]['IPv6'];
            } else {
                $IPv4 = $deviceInfo[0]['IPv4'];
                if (isset($deviceInfo[0]['IPv6'])) {
                    foreach ($deviceInfo[0]['IPv6'] as $Index => $ip) {
                        $Hostname = gethostbyaddr($ip);
                        if ($Hostname != $ip) {
                            $Hosts[$Index] = $Hostname;
                        }
                        $Hosts[20 + $Index] = '[' . $ip . ']';
                    }
                }
            }
            foreach ($IPv4 as $Index => $ip) {
                $Hostname = gethostbyaddr($ip);
                if ($Hostname != $ip) {
                    $Hosts[10 + $Index] = $Hostname;
                }
                $Hosts[(str_starts_with($ip, '169.254') ? 10 : 0) + 30 + $Index] = $ip;
            }
            ksort($Hosts);

            $Devices[$UUID] = [
                'devicename'      => $device['Name'],
                //'version'     => explode(' ', (string) $Xml->device->modelNumber)[0],
                'WebPort'     => $WebPort,
                'RPCPort'     => $RPCPort,
                'Hosts'       => array_unique($Hosts)
            ];
            $this->SendDebug('Device (' . $device['Name'] . ')', $Devices[$UUID], 0);
        }
        return $Devices;
    }

}
