<?php

use PHPUnit\Framework\TestCase;

class TenancyContractViewTest extends TestCase
{
    public function testContractDatesAreDatePickersAndSupportSpacedPreview(): void
    {
        $view = file_get_contents(__DIR__ . '/../public/views/tenancy-contracts.php');

        $this->assertStringContainsString('name="today_date"', $view, 'today_date input must exist');
        $this->assertStringContainsString('type="date"', $view, 'today_date should be a date picker');
        $this->assertStringContainsString('data-preview="spaced"', $view, 'today_date should opt into spaced preview');

        $this->assertStringContainsString('name="contract_from"', $view, 'contract_from input must exist');
        $this->assertStringContainsString('type="date"', $view, 'contract_from should be a date picker');
        $this->assertStringContainsString('data-preview="spaced"', $view, 'contract_from should opt into spaced preview');

        $this->assertStringContainsString('name="contract_to"', $view, 'contract_to input must exist');
        $this->assertStringContainsString('type="date"', $view, 'contract_to should be a date picker');
        $this->assertStringContainsString('data-preview="spaced"', $view, 'contract_to should opt into spaced preview');

        // JS: ensure tenancyContracts.js supports spaced preview and the contract date keys
        $js = file_get_contents(__DIR__ . '/../public/assets/js/tenancyContracts.js');
        $this->assertStringContainsString("formatSpacedPairs", $js, 'JS should include formatSpacedPairs helper');
        $this->assertStringContainsString("key === 'contract_from'", $js, 'JS should handle contract_from formatting');

        // JS: ensure preview shows slashed dd/mm/yyyy for contract period
        $this->assertStringContainsString("ms = val.match", $js, 'JS should special-case slashed preview for contract period');
        $this->assertStringContainsString("'/' + ms[2] + '/' + ms[1]", $js, 'JS should format contract dates with slashes in preview');

        // Controller: ensure spaced representations still exist, and slashed representations are written for the contract period
        $controller = file_get_contents(__DIR__ . '/../src/controllers/TenancyContractController.php');
        $this->assertStringContainsString('$contractFromOutSpaced', $controller, 'Controller should build spaced representation for contract_from');
        $this->assertStringContainsString('$contractToOutSpaced', $controller, 'Controller should build spaced representation for contract_to');
        $this->assertStringContainsString('$contractFromOutSlashed', $controller, 'Controller should build slashed representation for contract_from');
        $this->assertStringContainsString('$contractToOutSlashed', $controller, 'Controller should build slashed representation for contract_to');
        $this->assertMatchesRegularExpression('/\\$pdf->Write\([^\)]*contractToOutSlashed[\s\S]*contractFromOutSlashed/', $controller);
    }
}
