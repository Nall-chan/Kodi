<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/../libs/HTMLTableHelper.php';

/**
 * Tests der Migration der alten Konfigurations-Skripte (bis 3.02) der HTML-Tabellen.
 * Die Fixtures sind die vom Modul erzeugten Original-Skripte eines Testsystems.
 */
class HTMLTableLegacyTest extends TestCase
{
    private const Fixtures = __DIR__ . '/fixtures/LegacyConfig/';

    public function testExtractAllFixtures(): void
    {
        $Expected = [
            'Addons'        => ['Thumbnail', 'Enabled', 'Name', 'Version', 'Summary', 'Execute'],
            'Favorites'     => ['Thumbnail', 'Title', 'Path', 'Type'],
            'Playlist'      => ['Play', 'Position', 'Type', 'Title', 'Showtitle', 'Season', 'Episode', 'Album', 'Track', 'Disc', 'Genre', 'Artist', 'Year', 'Runtime'],
            'PVR_TV'        => ['Thumbnail', 'Label', 'Now', 'Next'],
            'PVR_Radio'     => ['Thumbnail', 'Label', 'Now', 'Next'],
            'PVR_Recording' => ['Thumbnail', 'Label', 'Runtime', 'Starttime', 'Channel']
        ];
        foreach ($Expected as $Name => $Columns) {
            $Config = \KodiBase\HTMLTableLegacy::ExtractConfig(file_get_contents(self::Fixtures . $Name . '.ips.php'));
            $this->assertIsArray($Config, $Name);
            $this->assertSame($Columns, array_keys($Config['Spalten']), $Name);
        }
    }

    public function testExtractShortOpenTag(): void
    {
        // Das alte Addon-Skript beginnt mit '<?' und läuft ohne short_open_tag nicht mehr.
        $Config = \KodiBase\HTMLTableLegacy::ExtractConfig(file_get_contents(self::Fixtures . 'Addons.ips.php'));
        $this->assertIsArray($Config);
        $this->assertSame(['isenabled', 'isdisabled', 'doenabled', 'dodisabled', 'execute'], array_keys($Config['Button']));
    }

    public function testExtractInvalid(): void
    {
        $this->assertFalse(\KodiBase\HTMLTableLegacy::ExtractConfig('<?php $Config["Spalten"] = [];'));
        $this->assertFalse(\KodiBase\HTMLTableLegacy::ExtractConfig("<?php\n\$Config[\"Spalten\"] = array(\n### Konfig ENDE !!!\n"));
        $this->assertFalse(\KodiBase\HTMLTableLegacy::ExtractConfig("<?php\n\$Foo = 1;\n### Konfig ENDE !!!\n"));
    }

    public function testConvertFavorites(): void
    {
        $Legacy = \KodiBase\HTMLTableLegacy::ExtractConfig(file_get_contents(self::Fixtures . 'Favorites.ips.php'));
        $New = \KodiBase\HTMLTableLegacy::Convert($Legacy, $this->Defaults(['Thumbnail', 'Title', 'Path', 'Type']));

        $this->assertSame('margin:0 auto; font-size:0.8em;', array_column($New['Table'], 'style', 'tag')['<table>']);
        $Columns = array_column($New['Columns'], null, 'key');
        $this->assertSame(['index' => 1, 'key' => 'Title', 'name' => 'Name', 'show' => true, 'width' => 300, 'color' => 0xffffff, 'align' => 'center', 'style' => ''], $Columns['Title']);
        $this->assertSame(600, $Columns['Path']['width']);
        $this->assertSame('Typ', $Columns['Type']['name']);
        $Rows = array_column($New['Rows'], null, 'row');
        $this->assertSame(0x000000, $Rows['even']['bgcolor']);
        $this->assertSame(0x080808, $Rows['odd']['bgcolor']);
        $this->assertSame(0xffffff, $Rows['odd']['color']);
        $this->assertSame('', $Rows['odd']['style']);
    }

    public function testConvertAddonsHiddenColumnsAndButtons(): void
    {
        $Legacy = \KodiBase\HTMLTableLegacy::ExtractConfig(file_get_contents(self::Fixtures . 'Addons.ips.php'));
        $Default = $this->Defaults(['Thumbnail', 'Enabled', 'Name', 'Version', 'Summary', 'Execute', 'Description', 'Path'], [['class' => 'execute', 'style' => 'x']]);
        $New = \KodiBase\HTMLTableLegacy::Convert($Legacy, $Default);
        $Columns = array_column($New['Columns'], null, 'key');
        $this->assertSame(5, $Columns['Execute']['index']);
        $this->assertTrue($Columns['Execute']['show']);
        $this->assertSame('Starten', $Columns['Execute']['name']);
        $this->assertFalse($Columns['Description']['show']);
        $this->assertGreaterThanOrEqual(6, $Columns['Description']['index']);
        $this->assertCount(5, $New['Buttons']);
        $this->assertSame('isenabled', $New['Buttons'][0]['class']);
        $this->assertStringContainsString('background-color: rgb(0, 255, 0);', $New['Buttons'][0]['style']);
    }

    public function testConvertPlaylistActiveRow(): void
    {
        $Legacy = \KodiBase\HTMLTableLegacy::ExtractConfig(file_get_contents(self::Fixtures . 'Playlist.ips.php'));
        $New = \KodiBase\HTMLTableLegacy::Convert($Legacy, $this->Defaults(['Play', 'Position', 'Title'], [], true));
        $Rows = array_column($New['Rows'], null, 'row');
        $this->assertSame(0x808000, $Rows['active']['bgcolor']);
        // Unbekannte Spalten aus dem Skript werden übernommen.
        $Columns = array_column($New['Columns'], null, 'key');
        $this->assertArrayHasKey('Runtime', $Columns);
        $this->assertSame('Dauer', $Columns['Runtime']['name']);
        $this->assertSame(13, $Columns['Runtime']['index']);
        $this->assertSame(100, $Columns['Runtime']['width']);
    }

    public function testConvertCustomStyles(): void
    {
        $Legacy = [
            'Spalten' => ['Name' => 'Titel'],
            'Breite'  => ['Name' => '250em'],
            'Style'   => [
                'T'      => 'width:100%;',
                'HR'     => 'height:20px;',
                'HFName' => 'color:#f00; font-weight:bold; width:35px; align:left;',
                'DFGName'=> 'text-align:left; padding:2px;',
                'BRG'    => 'background-color:rgba(0,0,0,0.5); color:#123456; border:1px solid red;'
            ]
        ];
        $New = \KodiBase\HTMLTableLegacy::Convert($Legacy, $this->Defaults(['Name', 'Path']));
        $Table = array_column($New['Table'], 'style', 'tag');
        $this->assertSame('width:100%;', $Table['<table>']);
        $this->assertSame('height:20px;', $Table['<thead><tr>']);
        $Columns = array_column($New['Columns'], null, 'key');
        $this->assertSame(0xff0000, $Columns['Name']['color']);
        $this->assertSame('left', $Columns['Name']['align']);
        $this->assertSame(250, $Columns['Name']['width']);
        $this->assertSame('font-weight:bold;padding:2px', $Columns['Name']['style']);
        $this->assertFalse($Columns['Path']['show']);
        $Rows = array_column($New['Rows'], null, 'row');
        $this->assertSame(0x000000, $Rows['even']['bgcolor']);
        $this->assertSame(0x123456, $Rows['even']['color']);
        $this->assertSame('background-color:rgba(0,0,0,0.5);border:1px solid red', $Rows['even']['style']);
    }

    public function testCssColorToInt(): void
    {
        $this->assertSame(0xffffff, \KodiBase\HTMLTableLegacy::CssColorToInt('#ffffff'));
        $this->assertSame(0x112233, \KodiBase\HTMLTableLegacy::CssColorToInt(' #123 '));
        $this->assertFalse(\KodiBase\HTMLTableLegacy::CssColorToInt('red'));
        $this->assertFalse(\KodiBase\HTMLTableLegacy::CssColorToInt('rgb(0,0,0)'));
    }

    /**
     * Standardkonfiguration wie HTMLTable::GetDefaultTableConfig, ohne Übersetzung.
     */
    private function Defaults(array $Keys, array $Buttons = [], bool $WithActiveRow = false): array
    {
        $Columns = [];
        foreach ($Keys as $Index => $Key) {
            $Columns[] = ['index' => $Index, 'key' => $Key, 'name' => $Key, 'show' => true, 'width' => 100, 'color' => 0xffffff, 'align' => 'center', 'style' => ''];
        }
        $Rows = [
            ['row' => 'even', 'name' => 'even', 'bgcolor' => 0x000000, 'color' => 0xffffff, 'style' => ''],
            ['row' => 'odd', 'name' => 'odd', 'bgcolor' => 0x080808, 'color' => 0xffffff, 'style' => '']
        ];
        if ($WithActiveRow) {
            $Rows[] = ['row' => 'active', 'name' => 'active', 'bgcolor' => 0x808000, 'color' => 0xffffff, 'style' => ''];
        }
        return [
            'Table' => [
                ['tag' => '<table>', 'style' => ''],
                ['tag' => '<thead>', 'style' => ''],
                ['tag' => '<thead><tr>', 'style' => ''],
                ['tag' => '<tbody>', 'style' => '']
            ],
            'Columns' => $Columns,
            'Rows'    => $Rows,
            'Buttons' => $Buttons
        ];
    }
}
