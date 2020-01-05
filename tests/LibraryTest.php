<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class LibraryValidationTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateConfigurator(): void
    {
        $this->validateModule(__DIR__ . '/../KodiConfigurator');
    }

    public function testValidateDiscovery(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDiscovery');
    }

    public function testValidateSplitter(): void
    {
        $this->validateModule(__DIR__ . '/../KodiSplitter');
    }

    public function testValidateKodiDeviceAddons(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDeviceAddons');
    }
    public function testValidateKodiDeviceApplication(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDeviceApplication');
    }
    public function testValidateKodiDeviceAudioLibrary(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDeviceAudioLibrary');
    }
    public function testValidateKodiDeviceFavourites(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDeviceFavourites');
    }
    public function testValidateKodiDeviceFiles(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDeviceFiles');
    }
    public function testValidateKodiDeviceGUI(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDeviceGUI');
    }
    public function testValidateKodiDeviceInput(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDeviceInput');
    }
    public function testValidateKodiDevicePlayer(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDevicePlayer');
    }
    public function testValidateKodiDevicePlaylist(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDevicePlaylist');
    }
    public function testValidateKodiDevicePVR(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDevicePVR');
    }
    public function testValidateKodiDeviceSystem(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDeviceSystem');
    }
    public function testValidateKodiDeviceVideoLibrary(): void
    {
        $this->validateModule(__DIR__ . '/../KodiDeviceVideoLibrary');
    }
}